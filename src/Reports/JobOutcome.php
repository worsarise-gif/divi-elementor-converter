<?php
namespace SuiteEdge\Migrator\Reports;
use SuiteEdge\Migrator\Jobs\Job;
class JobOutcome {
    public function describe(Job $job) {
        $analyzed = 0; $converted = 0; $blocked = 0;$affected=[];$covered=0;$source_elements=0;
        $templates=0;$converted_templates=0;$posts=0;$converted_posts=0;
        $walk = function ($nodes) use (&$walk,&$blocked) {
            foreach ($nodes as $node) {
                if (($node['widgetType'] ?? '') === 'shortcode' || (($node['widgetType'] ?? '') === 'form' && empty($node['settings']['submit_actions']))) { $blocked++; }
                $walk($node['elements'] ?? []);
            }
        };
        foreach ($job->items as $item) {
            $kind=$item['target_kind']??'page';$template=strpos($kind,'uae-')===0;if ($template) {$templates++;}
            if($kind==='post'){$posts++;if(($item['status']??'')==='converted'){$converted_posts++;}}
            if (($item['status'] ?? '') === 'analyzed') { $analyzed++; }
            if (($item['status'] ?? '') === 'converted') { $converted++; }
            if ($template && ($item['status'] ?? '') === 'converted') {$converted_templates++;}
            $walk($item['elementor_data'] ?? []);
            $blocked += count($item['divi_data']['blockers'] ?? []);
            foreach($item['divi_data']['issues']??[] as $issue){if(!empty($issue['blocking'])){$affected[($item['name']??'').'/'.$issue['path']]=true;}}
            $covered+=(int)($item['divi_data']['covered_element_count']??0);$source_elements+=(int)($item['divi_data']['source_element_count']??0);
        }
        $dry = !empty($job->settings['dry_run']); $label = $job->status; $message = '';
        if (in_array($job->status,['completed','completed_with_errors'],true)) {
            if ($dry) {
                $label = $job->status === 'completed' ? 'Analysis completed — no page changes' : 'Analysis completed with errors — no page changes';
                $message = 'No pages or media were changed. Review the report, then use Apply to Page or Apply Selected to Pages on this completed job.';
                if ($blocked) { $label = 'Analysis completed — conversion blocked'; $message = 'No pages or media were changed. Resolve '.$blocked.' unsupported modules or unconfigured forms before applying.'; }
            } else {
                $label = $job->status === 'completed' ? 'Conversion completed' : 'Conversion completed with errors';
                $message = $converted.' '.($converted === 1 ? 'page' : 'pages').' converted. Review each page in Elementor and check its responsive appearance.';
                if (!empty($job->settings['saved_apply']) && $analyzed) { $label = $job->status === 'completed' ? 'Selected pages applied' : 'Selected pages applied with errors'; $message .= ' '.$analyzed.' saved results remain unapplied.'; }
                if (!$converted) { $label = 'Conversion not applied'; $message = 'No pages were converted. Resolve the errors in this report, then run a new analysis before applying.'; }
                if (!$converted && !empty($job->settings['saved_apply'])) { $message = 'Saved results were not applied. Review individual errors and warnings, then retry compatible results with Apply to Page. Changed targets require a new analysis.'; }
            }
        }
        if ($templates) {
            $label=str_replace(['pages','page'],['targets','target'],$label);
            $message=str_replace(['Apply to Page or Apply Selected to Pages','Apply to Page'],['Apply to UAE Template or bulk Apply','Apply to UAE Template'],$message);
            $message=str_replace(['No pages or media','No pages were','saved page','page results','pages','page'],['No targets or media','No targets were','saved target','target results','targets','target'],$message);
            $message.=' Existing UAE display rules were preserved. New templates remain drafts until configured and published in UAE.';
        }
        if($posts) {
            $label=str_replace(['pages','page'],['targets','target'],$label);
            if(!$templates){$message=str_replace(['Apply to Page or Apply Selected to Pages','Apply to Page'],['Apply to Post or Apply Selected','Apply to Post'],$message);$message=str_replace(['pages','page'],['targets','target'],$message);}
            $message.=' Blog post metadata and publication state are preserved. New post targets remain drafts.';
        }
        $mode=$templates && $templates===count($job->items)?'Apply to UAE templates':($posts && $posts===count($job->items)?'Apply to posts':($templates?'Apply to pages / posts / templates':($posts?'Apply to pages / posts':'Apply to pages')));
        if (($job->settings['direction'] ?? '')==='elementor_to_divi') { $mode='Elementor to Divi / Apply to pages';$message=str_replace('in Elementor','in Divi',$message);if($blocked){$label='Analysis completed — Divi conversion blocked';$message='No pages were changed. Resolve '.$blocked.' reported reverse-mapping blockers, then reconvert before applying.';} }
        return ['mode'=>$dry ? (($job->settings['direction']??'')==='elementor_to_divi'?'Elementor to Divi / Review':'Analyze only') : $mode,'status_label'=>$label,'message'=>$message,'analyzed_items'=>$analyzed,'converted_pages'=>$converted-$converted_templates-$converted_posts,'converted_posts'=>$converted_posts,'converted_templates'=>$converted_templates,'converted_targets'=>$converted,'blocking_modules'=>$blocked,'blocking_issues'=>$blocked,'affected_paths'=>count($affected),'source_elements'=>$source_elements,'covered_elements'=>$covered,'unvisited_elements'=>max(0,$source_elements-$covered),'runtime_failures'=>(int)($job->summary['failed']??0)];
    }
}
