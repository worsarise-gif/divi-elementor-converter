<?php
namespace SuiteEdge\Migrator\Import;
class BatchImport {
    public function create(array $targets,array $settings,array $selected = null,$site_session = '') {
        if ($site_session!=='') { (new SitePageSelection())->files($site_session); }
        $lock = new \SuiteEdge\Migrator\Jobs\LockManager(); $lock->acquire($site_session!==''?'site-session:'.$site_session:'upload-session:'.get_current_user_id());
        try { return $this->create_selected($targets,$settings,$selected,$site_session); } finally { $lock->release(); }
    }
    private function create_selected(array $targets,array $settings,array $selected = null,$site_session = '') {
        if (!current_user_can('manage_options')) { throw new \RuntimeException('Unauthorized.'); }
        if (($settings['output_engine'] ?? 'classic') !== 'classic') { throw new \InvalidArgumentException('Only Classic output is available.'); }
        $pending = $site_session!==''?(new SitePageSelection())->files($site_session):get_transient('suiteedge_migrator_pending_files_' . get_current_user_id());
        if (!is_array($pending) || !$pending || ($selected === null && count($targets) !== count($pending))) { throw new \RuntimeException('Upload session expired or mapping is incomplete.'); }
        if ($selected === null) { $selected = array_keys($pending); }
        if (!$selected || count($selected) > count($pending)) { throw new \InvalidArgumentException('Select at least one available layout.'); }
        $indices = [];
        foreach ($selected as $index) {
            if (!is_scalar($index) || !ctype_digit((string)$index) || !isset($pending[(int)$index]) || isset($indices[(int)$index])) { throw new \InvalidArgumentException('Invalid or duplicate source selection.'); }
            $indices[(int)$index] = true;
        }
        $items = []; $seen = [];
        foreach ($pending as $index=>$file) {
            if (!isset($indices[$index])) { continue; }
            $record=(new SourceRepository())->get($file['source_id']);
            if ($site_session!=='') { if(empty($record['site_page'])){throw new \RuntimeException('Page selection has no local source snapshot.');}(new SitePageSource())->assert_current($record); }
            $target = $targets[$index] ?? null;
            $kind = 'page';
            if (is_string($target) && preg_match('/^(post|uae-header|uae-footer):(.+)$/D',$target,$typed)) { $kind = $typed[1];$target=$typed[2]; }
            (new \SuiteEdge\Migrator\Elementor\TargetResolver())->kind($kind);
            if ($site_session!=='' && $kind!=='page') { throw new \InvalidArgumentException('Direct page conversion requires a page destination.'); }
            if ($target === 'create_new') { $id = 0; }
            else {
                if (!is_scalar($target) || !ctype_digit((string)$target) || (int)$target < 1) { throw new \InvalidArgumentException('Invalid target page.'); }
                $id = (int)$target; (new \SuiteEdge\Migrator\Elementor\TargetResolver())->validate($id,$kind);
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                if (isset($seen[$id])) { throw new \InvalidArgumentException('Each layout must have a distinct target page.'); } $seen[$id] = true;
            }
            $items[] = ['source_id'=>$file['source_id'],'layout_index'=>$file['layout_index'],'name'=>$file['name'],'target_page_id'=>$id,'target_kind'=>$kind,'status'=>'pending'];
            if ($site_session!=='') { $items[count($items)-1]['input_source']='site_page';$items[count($items)-1]['source_page_id']=$record['site_page']['source_page_id']; }
        }
        $settings['menu_map'] = (new MenuMapping())->validate($settings['menu_map'] ?? []);
        $settings['blog_category_map'] = (new BlogCategoryMapping())->validate($settings['blog_category_map'] ?? []);
        $settings['input_source']=$site_session!==''?'site_page':'json_upload';
        $settings['same_site_source']=$site_session!=='';
        if ($site_session!=='') { $settings['source_origin']=(new \SuiteEdge\Migrator\Dynamic\SourceLinkSettings())->origin(home_url('/')); }
        $job = new \SuiteEdge\Migrator\Jobs\Job(); $job->items = $items; $job->settings = $settings; $job->summary['total'] = count($items);
        (new \SuiteEdge\Migrator\Jobs\JobRepository())->save($job);
        if ($site_session!=='') { (new SitePageSelection())->consume($site_session);return $job; }
        // Keep unchecked layouts available; preserve their indices so a repeated form
        // cannot accidentally convert a different layout using an old mapping.
        $remaining = array_diff_key($pending,$indices);
        if ($remaining) { set_transient('suiteedge_migrator_pending_files_' . get_current_user_id(),$remaining,HOUR_IN_SECONDS); }
        else { delete_transient('suiteedge_migrator_pending_files_' . get_current_user_id()); }
        return $job;
    }
}
