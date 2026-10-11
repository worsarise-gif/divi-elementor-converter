<?php
namespace SuiteEdge\Migrator\Reports;
class ReportBuilder {
    public function build(\SuiteEdge\Migrator\Jobs\Job $job) {
        $origin = $job->settings['source_origin'] ?? '';
        $profiles = [];
        foreach ($job->items as $item) {
            $kind = $item['target_kind'] ?? 'page';
            $profiles[$kind] = $job->settings['source_theme'] ?? (new \SuiteEdge\Migrator\Style\SourceThemeProfile())->recommended($origin,in_array($kind,['page','post'],true) ? 'page' : 'theme-builder');
        }
        $settings = ['input_source'=>$job->settings['input_source']??'json_upload','source_origin'=>$origin, 'resolve_source_links'=>!empty($job->settings['resolve_source_links']),'link_map'=>$job->settings['link_map'] ?? [],'blog_category_map'=>$job->settings['blog_category_map'] ?? [],'module_replacements'=>$job->settings['module_replacements'] ?? [],'assets'=>$job->settings['assets'] ?? [], 'source_theme'=>count($profiles) === 1 ? reset($profiles) : null, 'source_theme_by_target_kind'=>$profiles];
        $settings['direction']=$job->settings['direction'] ?? 'divi_to_elementor';
        if ($settings['direction']==='elementor_to_divi') { $settings['input_source']='elementor_site_page';$settings['source_theme']=null;$settings['source_theme_by_target_kind']=[]; }
        return ['job_id'=>$job->id,'status'=>$job->status,'dry_run'=>!empty($job->settings['dry_run']),'conversion_settings'=>$settings,'outcome'=>(new JobOutcome())->describe($job),'summary'=>$job->summary,'diagnostics'=>(new WarningReport())->diagnostics($job),'pages'=>$job->items];
    }
}
