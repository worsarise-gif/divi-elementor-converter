<?php
namespace SuiteEdge\Migrator\Jobs;
class BatchRunner {
    private $repository;
    public function __construct(JobRepository $repository) { $this->repository = $repository; }
    private function response(Job $job) { $outcome = (new \SuiteEdge\Migrator\Reports\JobOutcome())->describe($job); return ['status'=>$job->status,'progress'=>count($job->items) ? 100 * $job->current_index / count($job->items) : 100,'summary'=>$job->summary,'dry_run'=>!empty($job->settings['dry_run']),'items'=>array_map(function ($i) { return array_intersect_key($i,array_flip(['status','error','target_page_id','name'])); },$job->items),'message'=>$outcome['message']]; }
    public function process_batch($job_id,$batch_size = 1) {
        if (!current_user_can('manage_options')) { throw new \RuntimeException('Unauthorized.'); }
        $lock = new LockManager(); $lock->acquire('job:' . $job_id);
        try {
            $job = $this->repository->get($job_id); if (!$job) { throw new \RuntimeException('Job not found.'); }
            if (in_array($job->status,['completed','completed_with_errors','rolled_back','rollback_failed','cancelled','needs_recovery'],true)) { return $this->response($job); }
            $job->status = 'running'; $batch_size = max(1,min(5,(int)$batch_size));
            for ($n = 0; $n < $batch_size && $job->current_index < count($job->items); $n++) {
                $index = $job->current_index; $item = $job->items[$index];
                if (!empty($job->settings['saved_apply']) && !in_array($item['status'] ?? '',['pending_saved','processing'],true)) { $job->current_index++; $this->repository->save($job); continue; }
                $page_lock = new LockManager();
                try {
                    if (!empty($item['target_page_id'])) { $page_lock->acquire('page:' . $item['target_page_id']); }
                    // Persist intent before mutation; a crash cannot silently repeat a page write.
                    if (($item['status'] ?? '') === 'processing') {
                        $state = !empty($item['target_page_id']) ? (new \SuiteEdge\Migrator\Import\BackupEngine())->interrupted_state($item['target_page_id'],$job->id) : 'unchanged';
                        if ($state === 'written') {
                            $job->items[$index]['status'] = 'converted';
                            $job->summary['completed']++; $job->summary['warnings'] += count($item['warnings'] ?? []);
                            $job->current_index++; $this->repository->save($job); continue;
                        }
                        if ($state === 'review') {
                            $job->status = 'needs_recovery'; $job->items[$index]['status'] = 'needs_recovery';
                            $job->items[$index]['recovery_hash'] = (new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($item['target_page_id']);
                            $job->items[$index]['error'] = 'Interrupted write or newer edits detected. Review the page and restore its backup explicitly before continuing.';
                            $this->repository->save($job); return $this->response($job);
                        }
                        throw new \RuntimeException('Interrupted item left the target unchanged. Re-upload to create a new job if needed.');
                    }
                    $job->items[$index]['status'] = 'processing'; $this->repository->save($job);
                    $result = $this->process_item($item,$job,$page_lock,function () use ($lock,$page_lock) { $lock->assert_owned(); $page_lock->assert_owned(); });
                    $job->items[$index] = array_merge($job->items[$index],$result,['status'=>empty($job->settings['dry_run']) ? 'converted' : 'analyzed']);
                    $job->summary['completed']++; $job->summary['warnings'] += count($result['warnings']);
                } catch (\Throwable $e) {
                    if ($e instanceof LockLostException) { throw $e; }
                    if ($e instanceof LockBusyException && ($job->items[$index]['status'] ?? '') !== 'processing') { $this->repository->save($job); return array_merge($this->response($job),['retry_after'=>2000,'message'=>'Target page is busy; this item remains pending.']); }
                    $lock->assert_owned(); $page_lock->assert_owned();
                    if (!empty($job->items[$index]['target_page_id'])) {
                        $backup = new \SuiteEdge\Migrator\Import\BackupEngine();
                        if ($backup->interrupted_state($job->items[$index]['target_page_id'],$job->id) === 'review') {
                            $job->status = 'needs_recovery'; $job->items[$index]['status'] = 'needs_recovery';
                            $job->items[$index]['recovery_hash'] = $backup->fingerprint($job->items[$index]['target_page_id']);
                            $job->items[$index]['error'] = $e->getMessage(); $this->repository->save($job); return $this->response($job);
                        }
                    }
                    $job->items[$index]['status'] = 'failed'; $job->items[$index]['error'] = $e->getMessage();
                    if (!empty($job->settings['saved_apply']) && empty($job->items[$index]['write_started'])) { $job->items[$index]['status'] = 'analyzed'; }
                    $job->summary['failed']++; $job->summary['errors'][] = $e->getMessage();
                    $job->summary['warnings'] += count($job->items[$index]['warnings'] ?? []);
                } finally { $page_lock->release(); }
                $job->current_index++; $this->repository->save($job);
            }
            if ($job->current_index >= count($job->items)) { $job->status = $job->summary['failed'] ? 'completed_with_errors' : 'completed'; }
            $this->repository->save($job); return $this->response($job);
        } finally { $lock->release(); }
    }
    private function process_item(array $item, Job $job, LockManager $page_lock, $guard) {
        $reverse = new \SuiteEdge\Migrator\Reverse\Service();
        if ($reverse->reverse($job)) { return $reverse->process($item,$job,$page_lock,$guard,$this->repository); }
        if (!empty($job->settings['saved_apply'])) { return $this->apply_saved_item($item,$job,$page_lock,$guard); }
        $kind=(new \SuiteEdge\Migrator\Elementor\TargetResolver())->kind($item['target_kind'] ?? 'page');
        if (($job->settings['output_engine'] ?? 'classic') !== 'classic') { throw new \RuntimeException('Atomic output is not supported.'); }
        $record = (new \SuiteEdge\Migrator\Import\SourceRepository())->get($item['source_id']);
        (new \SuiteEdge\Migrator\Import\SitePageSource())->assert_current($record);
        $export = (new \SuiteEdge\Migrator\Import\DiviExportReader())->read_json($record['json']);
        $layout = $export['layouts'][$item['layout_index']];
        $builder = new \SuiteEdge\Migrator\Elementor\DocumentBuilder();
        $same_site=isset($record['site_page']);
        $data = (new \SuiteEdge\Migrator\Elementor\Classic\Serializer())->serialize($builder->build($layout['post_content'],['layouts'=>$export['layouts'],'presets'=>$export['presets'],'link_map'=>$job->settings['link_map'] ?? [],'menu_map'=>$job->settings['menu_map'][$item['source_id']] ?? [],'blog_category_map'=>$job->settings['blog_category_map'][$item['source_id']] ?? [],'module_replacements'=>$job->settings['module_replacements'][$item['source_id']] ?? [],'preserve_menu_design'=>!empty($job->settings['preserve_menu_design']),'menu_region'=>$kind==='uae-footer'?'footer':'header','source_origin'=>$job->settings['source_origin'] ?? '', 'source_theme'=>$job->settings['source_theme'] ?? (new \SuiteEdge\Migrator\Style\SourceThemeProfile())->recommended($job->settings['source_origin'] ?? '',in_array($kind,['page','post'],true)?'page':'theme-builder'),'resolve_source_links'=>!empty($job->settings['resolve_source_links']),'same_site_source'=>$same_site,'same_site_assets'=>$same_site || (!empty($job->settings['source_origin']) && rtrim($job->settings['source_origin'],'/') === rtrim(home_url(),'/'))]));
        if ($same_site) { $builder->information[]='Site page scope: converts the saved Divi 4 page body, stored presets and readable referenced Divi Library globals on this site. Local Media Library IDs can be reused. Theme Builder headers/footers, global conditions and sitewide theme/custom CSS are separate; verify inherited Source theme defaults before Apply.';$job->items[$job->current_index]['source_capture']=$record['site_page']; }
        elseif (in_array($kind,['page','post'],true)) { $builder->information[] = 'Export scope: only layout content supplied in this JSON is converted. Sitewide Divi Theme Builder headers/footers and third-party chat overlays require separate migration or destination configuration. Use Headers & Footers with their own exports; existing destination template assignments are preserved. Confirm Source theme defaults for fonts inherited from the Divi Theme Customizer, which are not included in page JSON.'; }
        if ($kind === 'post') { $builder->information[]='Post layout scope: WordPress post title, slug, author, dates, excerpt, categories, tags, featured image, SEO and publication state remain unchanged. Layout JSON does not import these database records or single-post Theme Builder conditions. New targets are post drafts; configure their post details before publishing.'; }
        (new \SuiteEdge\Migrator\Elementor\Validator())->validate($data,true);
        $id = (int)($item['target_page_id'] ?? 0);
        if ($id) { (new \SuiteEdge\Migrator\Elementor\TargetResolver())->validate($id,$kind); }
        // Include known media omissions before both persistence and published-page protection.
        if ($export['images'] && !in_array('import_external',$job->settings['assets'] ?? [],true)) { $builder->warnings[] = 'Embedded images are available. Enable image import to copy them into the target Media Library.'; }
        $job->items[$job->current_index]['elementor_data'] = $data;
        $job->items[$job->current_index]['output_hash'] = hash('sha256',wp_json_encode($data));
        $job->items[$job->current_index]['conversion_target'] = $id;
        $job->items[$job->current_index]['conversion_target_kind'] = $kind;
        if ($id) { $job->items[$job->current_index]['target_hash'] = (new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($id); }
        $job->items[$job->current_index]['warnings'] = array_values(array_unique($builder->warnings));
        $job->items[$job->current_index]['information'] = array_values(array_unique($builder->information));
        $job->items[$job->current_index]['link_resolution'] = $builder->link_diagnostics;
        $job->items[$job->current_index]['source_hash'] = hash('sha256',$record['json']);
        $this->repository->save($job);
        if (empty($job->settings['dry_run'])) {
            // A shortcode fallback is useful in an analysis report, but is not a complete migration.
            $walk = function ($nodes) use (&$walk) {
                foreach ($nodes as $node) {
                    if (($node['widgetType'] ?? '') === 'shortcode') { throw new \UnexpectedValueException('Conversion blocked: unresolved Divi shortcode. Review this item\'s warnings for the source module or page ID, correct its link/replacement mapping, then run a fresh conversion. Applying saved output does not repair unresolved content.'); }
                    if (($node['widgetType'] ?? '') === 'form' && empty($node['settings']['submit_actions'])) { throw new \UnexpectedValueException('Conversion blocked: contact form delivery actions require configuration.'); }
                    $walk($node['elements']);
                }
            }; $walk($data); $guard();
            if ($id && get_post_status($id) === 'publish' && $builder->warnings && empty($job->settings['reviewed_warnings'])) { throw new \UnexpectedValueException('Published target is protected: analyze and review conversion warnings first, then explicitly enable applying reviewed differences.'); }
            if (!$id) {
                $id = (new \SuiteEdge\Migrator\Elementor\TargetResolver())->draft($layout['title'],$kind,$record['name']);
                if (is_wp_error($id) || !$id) { throw new \RuntimeException('Could not create draft page.'); }
                // Record ID before writing, including failed conversions, for audit/recovery.
                $job->items[$job->current_index]['target_page_id'] = $id;
                $job->items[$job->current_index]['created_target'] = true;
                $job->items[$job->current_index]['target_hash'] = (new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($id);
                $this->repository->save($job);
                $page_lock->acquire('page:'.$id);
            }
            (new \SuiteEdge\Migrator\Assets\AssetProcessor())->process($data,$id,$job->settings,$builder->warnings,$export['images'],$builder->information);
            $job->items[$job->current_index]['elementor_data'] = $data;
            $job->items[$job->current_index]['warnings'] = array_values(array_unique($builder->warnings));
            $this->repository->save($job);
            if (get_post_status($id) === 'publish' && $builder->warnings && empty($job->settings['reviewed_warnings'])) { throw new \UnexpectedValueException('Published target is protected: review media and link warnings before applying this conversion.'); }
            (new \SuiteEdge\Migrator\Elementor\Validator())->validate($data,true);
            $job->items[$job->current_index]['elementor_data'] = $data;
            $job->items[$job->current_index]['warnings'] = array_values(array_unique($builder->warnings));
            $this->repository->save($job);
            (new \SuiteEdge\Migrator\Import\SitePageSource())->assert_current($record);
            $job->items[$job->current_index]['reverse_origin']=(new \SuiteEdge\Migrator\Reverse\Origin())->record($id,$data,$record,$item['source_id'],$item['layout_index']);$this->repository->save($job);
            if($job->items[$job->current_index]['reverse_origin']){(new \SuiteEdge\Migrator\Reverse\Origin())->register($id,$job->id);}
            (new \SuiteEdge\Migrator\Elementor\PageWriter())->write($id,$data,$job->id,$guard,$kind,$job->items[$job->current_index]['target_hash']);
        }
        return ['target_page_id'=>$id,'warnings'=>array_values(array_unique($builder->warnings)),'information'=>array_values(array_unique($builder->information)),'elementor_data'=>$data];
    }
    private function apply_saved_item(array $item,Job $job,LockManager $page_lock,$guard) {
        if (!current_user_can('unfiltered_html')) { throw new \RuntimeException('Applying imported HTML requires unfiltered_html permission.'); }
        $data = $item['saved_output'] ?? []; $id = (int)($item['target_page_id'] ?? 0);
        $kind=(new \SuiteEdge\Migrator\Elementor\TargetResolver())->kind($item['target_kind'] ?? 'page');
        if ($kind !== ($item['conversion_target_kind'] ?? 'page')) { throw new \RuntimeException('Saved target type changed. Reconvert with the correct page, post or Header/Footer mapping.'); }
        if ($id !== (int)($item['conversion_target'] ?? -1) || empty($item['output_hash']) || !hash_equals($item['output_hash'],hash('sha256',wp_json_encode($data)))) { throw new \RuntimeException('Saved output or its page assignment changed. Application blocked.'); }
        (new SavedOutputApplier())->validate_output($data); $guard();
        if ($id) {
            (new \SuiteEdge\Migrator\Elementor\TargetResolver())->validate($id,$kind);
            if (empty($item['target_hash']) || !hash_equals($item['target_hash'],(new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($id))) { throw new \RuntimeException('Target changed after analysis or queueing. Newer edits are protected.'); }
        }
        // Read embedded media only; the stored Elementor tree is never converted again.
        $source = (new \SuiteEdge\Migrator\Import\SourceRepository())->get($item['source_id']);
        (new \SuiteEdge\Migrator\Import\SitePageSource())->assert_current($source);
        $job->items[$job->current_index]['reverse_origin']=(new \SuiteEdge\Migrator\Reverse\Origin())->record($id,$data,$source,$item['source_id'],$item['layout_index']);$this->repository->save($job);
        if($job->items[$job->current_index]['reverse_origin']){(new \SuiteEdge\Migrator\Reverse\Origin())->register($id,$job->id);}
        $export = (new \SuiteEdge\Migrator\Import\DiviExportReader())->read_json($source['json']);
        $warnings = $item['warnings'] ?? [];
        $information = $item['information'] ?? [];
        if ($id && get_post_status($id) === 'publish' && $warnings && empty($job->settings['reviewed_warnings'])) { throw new \RuntimeException('Published page requires acknowledgement of reviewed warnings.'); }
        if (!$id) {
            $id = (new \SuiteEdge\Migrator\Elementor\TargetResolver())->draft($export['layouts'][$item['layout_index']]['title'],$kind,$source['name']);
            if (is_wp_error($id) || !$id) { throw new \RuntimeException('Could not create mapped draft.'); }
            $job->items[$job->current_index]['target_page_id'] = $id; $job->items[$job->current_index]['created_target'] = true;
            $job->items[$job->current_index]['conversion_target'] = $id;
            $job->items[$job->current_index]['target_hash'] = (new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($id);
            $item['target_hash'] = $job->items[$job->current_index]['target_hash'];
            $this->repository->save($job); $page_lock->acquire('page:'.$id);
        }
        (new \SuiteEdge\Migrator\Assets\AssetProcessor())->process($data,$id,$job->settings,$warnings,$export['images'],$information);
        $job->items[$job->current_index]['elementor_data'] = $data; $job->items[$job->current_index]['warnings'] = array_values(array_unique($warnings)); $this->repository->save($job);
        if (get_post_status($id) === 'publish' && $warnings && empty($job->settings['reviewed_warnings'])) { throw new \RuntimeException('Review media and link warnings before applying to the published page.'); }
        // Media imports can take time; an editor need not honor migration advisory locks.
        if (!empty($item['target_hash']) && !hash_equals($item['target_hash'],(new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($id))) { throw new \RuntimeException('Target changed while media was being prepared. Newer edits are protected.'); }
        (new \SuiteEdge\Migrator\Import\SitePageSource())->assert_current($source);
        $job->items[$job->current_index]['write_started'] = true; $this->repository->save($job);
        (new \SuiteEdge\Migrator\Elementor\PageWriter())->write($id,$data,$job->id,$guard,$kind,$item['target_hash']);
        return ['target_page_id'=>$id,'elementor_data'=>$data,'warnings'=>array_values(array_unique($warnings)),'information'=>array_values(array_unique($information))];
    }
    public function recover($job_id) {
        if (!current_user_can('manage_options')) { throw new \RuntimeException('Unauthorized.'); }
        $lock = new LockManager(); $lock->acquire('job:'.$job_id); $page_lock = new LockManager();
        try {
            $job = $this->repository->get($job_id);
            if (!$job || $job->status !== 'needs_recovery') { throw new \RuntimeException('Job does not require recovery.'); }
            $index = $job->current_index; $item = $job->items[$index];
            $page_lock->acquire('page:'.$item['target_page_id']);
            $backup = new \SuiteEdge\Migrator\Import\BackupEngine();
            if (empty($item['recovery_hash']) || !hash_equals($item['recovery_hash'],$backup->fingerprint($item['target_page_id']))) { throw new \RuntimeException('Page changed since the recovery report. Refresh the analysis before restoring; newer edits are protected.'); }
            $lock->assert_owned(); $page_lock->assert_owned();
            $backup->restore_backup($item['target_page_id'],$job->id,true);
            $job->items[$index]['status'] = 'recovered'; $job->items[$index]['error'] = 'Interrupted item restored to its original backup; not retried.';
            $job->summary['failed']++; $job->current_index++;
            $job->status = $job->current_index >= count($job->items) ? 'completed_with_errors' : 'pending';
            $this->repository->save($job); return $this->response($job);
        } finally { $page_lock->release(); $lock->release(); }
    }
    public function review_recovery($job_id) {
        if (!current_user_can('manage_options')) { throw new \RuntimeException('Unauthorized.'); }
        $lock = new LockManager(); $lock->acquire('job:'.$job_id); $page_lock = new LockManager();
        try {
            $job = $this->repository->get($job_id);
            if (!$job || $job->status !== 'needs_recovery') { throw new \RuntimeException('Job does not require recovery.'); }
            $index = $job->current_index; $id = $job->items[$index]['target_page_id']; $page_lock->acquire('page:'.$id);
            $job->items[$index]['recovery_hash'] = (new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($id);
            $job->items[$index]['error'] = 'Recovery review refreshed. Inspect the current page and backup in the report before restoring.';
            $this->repository->save($job); return $this->response($job);
        } finally { $page_lock->release(); $lock->release(); }
    }
    public function cancel($job_id) {
        if (!current_user_can('manage_options')) { throw new \RuntimeException('Unauthorized.'); }
        $lock = new LockManager(); $lock->acquire('job:' . $job_id);
        try {
            $job = $this->repository->get($job_id);
            if (!$job || !in_array($job->status,['pending','running'],true)) { throw new \RuntimeException('Job cannot be cancelled.'); }
            $job->status = 'cancelled'; $this->repository->save($job); return $this->response($job);
        } finally { $lock->release(); }
    }
    public function rollback($job_id) {
        if (!current_user_can('manage_options')) { throw new \RuntimeException('Unauthorized.'); }
        $lock = new LockManager(); $lock->acquire('job:' . $job_id);
        try {
            $job = $this->repository->get($job_id);
            if (!$job || !in_array($job->status,['completed','completed_with_errors','rollback_failed','cancelled'],true) || !empty($job->settings['dry_run'])) { throw new \RuntimeException('Job cannot be rolled back.'); }
            $errors = []; $count = 0;
            foreach ($job->items as $index=>$item) {
                if (($item['status'] ?? '') !== 'converted') { continue; }
                $page_lock = new LockManager();
                try {
                    $page_lock->acquire('page:' . $item['target_page_id']);
                    (new \SuiteEdge\Migrator\Import\BackupEngine())->restore_backup($item['target_page_id'],$job->id);
                    $job->items[$index]['status'] = 'restored'; $count++;
                } catch (\Throwable $e) { $errors[] = $e->getMessage(); }
                finally { $page_lock->release(); }
            }
            $job->status = $errors ? 'rollback_failed' : 'rolled_back'; $job->summary['rollback_errors'] = $errors;
            $this->repository->save($job);
            return ['restored_count'=>$count,'status'=>$job->status,'errors'=>$errors,'message'=>'Restored ' . $count . ' pages. Newly created pages remain as drafts.'];
        } finally { $lock->release(); }
    }
}
