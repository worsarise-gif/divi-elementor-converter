<?php
namespace SuiteEdge\Migrator\Jobs;
/** Queue validated, completed outputs on their original job; never rebuild a layout. */
class SavedOutputApplier {
    public function eligible(Job $job) {
        $reverse = new \SuiteEdge\Migrator\Reverse\Service();
        if ($reverse->reverse($job)) { return $reverse->eligible($job); }
        if ($job->archived || !in_array($job->status,['completed','completed_with_errors'],true)) { return []; }
        return array_keys(array_filter($job->items,function ($item) { return ($item['status'] ?? '') === 'analyzed' && !empty($item['elementor_data']); }));
    }
    public function validate_output(array $data) {
        (new \SuiteEdge\Migrator\Elementor\Validator())->validate($data,true);
        $walk = function ($nodes) use (&$walk) {
            foreach ($nodes as $node) {
                if (($node['widgetType'] ?? '') === 'shortcode' || (($node['widgetType'] ?? '') === 'form' && empty($node['settings']['submit_actions']))) { throw new \UnexpectedValueException('Saved output contains unsupported modules or an unconfigured form. Resolve these before applying.'); }
                $walk($node['elements']);
            }
        }; $walk($data);
    }
    public function queue(array $selection,$reviewed = false) {
        if (!current_user_can('manage_options') || !current_user_can('unfiltered_html')) { throw new \RuntimeException('Applying saved HTML requires administrator and unfiltered_html permissions.'); }
        if (!$selection || count($selection) > 100) { throw new \InvalidArgumentException('Select between 1 and 100 jobs.'); }
        ksort($selection); $repo = new JobRepository(); $locks = []; $jobs = []; $targets = []; $queued = 0;
        try {
            // Validate every selected mapping before persisting any application intent.
            foreach ($selection as $job_id=>$indices) {
                if (!is_array($indices) || !$indices || count($indices) > 100 || count(array_unique($indices,SORT_REGULAR)) !== count($indices)) { throw new \InvalidArgumentException('Invalid or duplicate page selection.'); }
                $lock = new LockManager(); $lock->acquire('job:'.$job_id); $locks[] = $lock;
                $job = $repo->get($job_id);
                if (!$job || $job->archived || !in_array($job->status,['completed','completed_with_errors'],true)) { throw new \RuntimeException('Only completed, visible jobs can be applied.'); }
                foreach ($indices as $index) {
                    if (!is_scalar($index) || !ctype_digit((string)$index) || !isset($job->items[(int)$index])) { throw new \InvalidArgumentException('Invalid page selection.'); }
                    $index = (int)$index; $item = $job->items[$index];
                    if (($item['status'] ?? '') === 'converted') { continue; } // Repeated requests cannot write twice.
                    $reverse = new \SuiteEdge\Migrator\Reverse\Service(); $is_reverse = $reverse->reverse($job);
                    if (($item['status'] ?? '') !== 'analyzed' || empty($is_reverse ? $item['divi_data'] : $item['elementor_data'])) { throw new \RuntimeException('Selected page has no completed analysis output.'); }
                    if (($item['input_source'] ?? '') === 'site_page') {
                        $source=(new \SuiteEdge\Migrator\Import\SourceRepository())->get($item['source_id']);
                        (new \SuiteEdge\Migrator\Import\SitePageSource())->assert_current($source);
                    }
                    $output = $item['saved_output'] ?? ($is_reverse ? $item['divi_data'] : $item['elementor_data']);
                    if ($is_reverse) { $reverse->validate($item); (new \SuiteEdge\Migrator\Reverse\Writer())->requirements($output); }
                    else { $this->validate_output($output); }
                    $id = (int)($item['target_page_id'] ?? 0);
                    $kind=(new \SuiteEdge\Migrator\Elementor\TargetResolver())->kind($item['target_kind'] ?? 'page');
                    if ($kind !== ($item['conversion_target_kind'] ?? 'page')) { throw new \RuntimeException('Saved target type changed. Reconvert with the correct page, post or Header/Footer mapping.'); }
                    if (isset($item['conversion_target']) && (int)$item['conversion_target'] !== $id) { throw new \RuntimeException('The saved page assignment changed. Reconvert with the correct mapping.'); }
                    $hash = hash('sha256',wp_json_encode($output));
                    if (isset($item['output_hash']) && !hash_equals($item['output_hash'],$hash)) { throw new \RuntimeException('Saved conversion output changed. Reconvert before applying.'); }
                    if ($id) {
                        (new \SuiteEdge\Migrator\Elementor\TargetResolver())->validate($id,$kind);
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                        if (isset($targets[$id])) { throw new \RuntimeException('Selected results share a target page. Select only one result for each page.'); } $targets[$id] = true;
                        $current = (new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($id);
                        if (!empty($item['target_hash']) && !hash_equals($item['target_hash'],$current)) { throw new \RuntimeException('Target page changed since analysis. Its newer edits are protected; analyze it again.'); }
                        if (get_post_status($id) === 'publish' && !empty($item['warnings']) && !$reviewed) { throw new \RuntimeException('Review the report and acknowledge its differences before applying to a published page.'); }
                        $job->items[$index]['target_hash'] = $current;
                    }
                    $job->items[$index]['saved_output'] = $output;
                    $job->items[$index]['output_hash'] = $hash;
                    $job->items[$index]['conversion_target'] = $id;
                    $job->items[$index]['status'] = 'pending_saved'; $queued++;
                    unset($job->items[$index]['error']);
                }
                $jobs[] = $job;
            }
            foreach ($jobs as $job) {
                if (!array_filter($job->items,function ($i) { return ($i['status'] ?? '') === 'pending_saved'; })) { continue; }
                $job->settings['saved_apply'] = true; $job->settings['dry_run'] = false; $job->settings['reviewed_warnings'] = (bool)$reviewed;
                $job->status = 'pending'; $job->current_index = 0;
                $job->summary['completed'] = count(array_filter($job->items,function ($i) { return in_array($i['status'] ?? '',['converted','analyzed'],true); }));
                $job->summary['failed'] = count(array_filter($job->items,function ($i) { return in_array($i['status'] ?? '',['failed','recovered'],true); }));
                $job->summary['errors'] = [];
                foreach ($job->items as $item) { if (!empty($item['error'])) { $job->summary['errors'][] = $item['error']; } }
                $job->summary['warnings'] = 0;
                foreach ($job->items as $item) { if (($item['status'] ?? '') !== 'pending_saved') { $job->summary['warnings'] += count($item['warnings'] ?? []); } }
                $repo->save($job);
            }
            return ['queued'=>$queued,'message'=>$queued.' saved page results queued on their original jobs.'];
        } finally { foreach (array_reverse($locks) as $lock) { $lock->release(); } }
    }
    public function clear(array $ids,$restore = false) {
        if (!current_user_can('manage_options') || !$ids || count($ids) > 100) { throw new \RuntimeException('Select between 1 and 100 jobs as an administrator.'); }
        $repo = new JobRepository(); $result = [];
        foreach (array_unique($ids) as $id) {
            $lock = new LockManager();
            try {
                if (!is_string($id)) { throw new \InvalidArgumentException('Invalid job ID.'); }
                $lock->acquire('job:'.$id); $job = $repo->get($id);
                if (!$job) { throw new \RuntimeException('Job not found.'); }
                if (!$restore) {
                    if ($job->status === 'pending' && $job->current_index === 0 && !array_filter($job->items,function ($i) { return !in_array($i['status'] ?? '',['pending','pending_saved'],true); })) { $job->status = 'cancelled'; }
                    if (in_array($job->status,['pending','running','needs_recovery'],true) || array_filter($job->items,function ($i) { return in_array($i['status'] ?? '',['processing','needs_recovery'],true); })) { throw new \RuntimeException('Finish, cancel or recover this job before clearing it.'); }
                }
                $job->archived = !$restore; $repo->save($job);
                $result[] = ['job'=>$id,'cleared'=>!$restore,'success'=>true];
            } catch (\Throwable $e) { $result[] = ['job'=>is_string($id) ? $id : '', 'success'=>false,'message'=>$e->getMessage()]; }
            finally { $lock->release(); }
        }
        return ['results'=>$result,'message'=>$restore ? 'Jobs restored to the list.' : 'Selected jobs cleared from the list. Pages and rollback backups are preserved.'];
    }
}
