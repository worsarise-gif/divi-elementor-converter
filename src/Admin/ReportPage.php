<?php
namespace SuiteEdge\Migrator\Admin;
class ReportPage {
    public function render() {
        if (!current_user_can('manage_options')) { wp_die('Unauthorized.'); }
        $repo = new \SuiteEdge\Migrator\Jobs\JobRepository();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only report selection; sanitized ID plus JobRepository owner validation. Downloads have their own nonce.
        $job = $repo->get(sanitize_text_field(wp_unslash($_GET['job'] ?? '')));
        View::start(__('Migration Report','suiteedge-divi-elementor-migrator'),__('Read the outcome and recommended actions before applying or publishing your result.','suiteedge-divi-elementor-migrator'),'jobs',3);
        if (!$job) { View::empty_state(__('Choose a migration report','suiteedge-divi-elementor-migrator'),__('Open Migration Jobs and select Report beside the job you want to review.','suiteedge-divi-elementor-migrator'),'-jobs',__('View migration jobs','suiteedge-divi-elementor-migrator')); echo '</div>'; return; }
        $outcome = (new \SuiteEdge\Migrator\Reports\JobOutcome())->describe($job);
        $report = (new \SuiteEdge\Migrator\Reports\ReportBuilder())->build($job);
        $diagnostics = $report['diagnostics'];
        $reverse=($job->settings['direction'] ?? '')==='elementor_to_divi';
        echo '<p><strong>'.esc_html__('Direction','suiteedge-divi-elementor-migrator').':</strong> '.esc_html($reverse?'Elementor → Divi 4':'Divi 4 → Elementor').'</p>';
        echo '<p><strong>'.esc_html__('Source site URL','suiteedge-divi-elementor-migrator').':</strong> '.esc_html($job->settings['source_origin'] ?? '').' <strong>'.esc_html__('Public page-link lookup','suiteedge-divi-elementor-migrator').':</strong> '.esc_html(!empty($job->settings['resolve_source_links']) ? __('Enabled','suiteedge-divi-elementor-migrator') : __('Disabled','suiteedge-divi-elementor-migrator')).'</p>';
        echo '<p>Job '.esc_html($job->id).' — '.esc_html($outcome['status_label']).'</p><p><strong>'.esc_html($outcome['mode']).'</strong>. '.esc_html($outcome['message']).'</p><p>Analyzed items: '.(int)$outcome['analyzed_items'].'; pages converted: '.(int)$outcome['converted_pages'].'; posts converted: '.(int)$outcome['converted_posts'].'; UAE templates converted: '.(int)$outcome['converted_templates'].'; blocking issues: '.(int)$outcome['blocking_modules'].'; failed: '.(int)$job->summary['failed'].'; actionable warnings: '.(int)$diagnostics['counts']['warning'].'; informational notes: '.(int)$diagnostics['counts']['info'].'.</p><table class="widefat striped"><thead><tr><th>Source</th><th>Target ID</th><th>Status</th><th>Recorded warnings / errors</th></tr></thead><tbody>';
        $download = add_query_arg(['action'=>'suiteedge_migrator_export','job'=>$job->id,'_wpnonce'=>wp_create_nonce('suiteedge_export')],admin_url('admin-post.php'));

        if ($job->status === 'needs_recovery') {
            $item = $job->items[$job->current_index]; $backup = get_option('suiteedge_backup_'.$job->id.'_'.(int)$item['target_page_id']);
            if (is_array($backup) && ($backup['owner'] ?? 0) === get_current_user_id()) {
                echo '</tbody></table><h2>Interrupted page recovery review</h2><p><a href="'.esc_url(get_edit_post_link($item['target_page_id'])).'">Inspect current page</a>. Restoring replaces the fields shown below with the original backup. Edits made after the recorded review snapshot are protected.</p>';
                foreach (['Original backup'=>$backup['state'],'Current page'=>(new \SuiteEdge\Migrator\Import\BackupEngine())->state($item['target_page_id'])] as $label=>$state) { echo '<h3>'.esc_html($label).'</h3><textarea readonly rows="10" style="width:100%">'.esc_textarea(wp_json_encode($state,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)).'</textarea>'; }
                echo '<table class="widefat striped"><tbody>';
            }
        }

        foreach ($job->items as $index=>$item) {
            if ($reverse) {
                echo '<tr><td>'.esc_html($item['name']).(!empty($item['divi_data']['content']) && empty($item['divi_data']['blockers']) ? '<br><a href="'.esc_url(add_query_arg('item',$index,$download)).'">'.esc_html__('Download Divi shortcodes','suiteedge-divi-elementor-migrator').'</a>' : '').'</td><td>'.(int)($item['target_page_id'] ?? 0).'</td><td>'.esc_html($item['status']).'</td><td>'.esc_html(implode("\n",array_merge($item['warnings'] ?? [],isset($item['error']) ? [$item['error']] : []))).'</td></tr>';continue;
            }
            echo '<tr><td>'.esc_html($item['name']).(isset($item['elementor_data']) ? '<br><a href="'.esc_url(add_query_arg('item',$index,$download)).'">Download Elementor template</a>' : '').'</td><td>'.(int)($item['target_page_id'] ?? 0).'</td><td>'.esc_html($item['status']).'</td><td>'.esc_html(implode("\n",array_merge($item['warnings'] ?? [],isset($item['error']) ? [$item['error']] : []))).'</td></tr>';
        }
        echo '</tbody></table><p><a class="button" href="'.esc_url($download).'">Download JSON report</a></p><h2>Causes and recommended actions</h2><table class="widefat striped"><thead><tr><th>Severity</th><th>Source</th><th>Cause</th><th>Recommended action</th></tr></thead><tbody>';
        foreach ($diagnostics['entries'] as $entry) { echo '<tr><td>'.esc_html($entry['severity']).'</td><td>'.esc_html($entry['source']).'</td><td>'.esc_html($entry['cause']).'</td><td>'.esc_html($entry['action']).'</td></tr>'; }
        if (!$diagnostics['entries']) { echo '<tr><td colspan="4">No conversion diagnostics recorded.</td></tr>'; }
        echo '</tbody></table>';
        // translators: 1: covered elements, 2: source elements, 3: blocking issues, 4: affected paths, 5: unvisited elements, 6: runtime failures.
        if ($reverse) { echo '<p>'.esc_html(sprintf(__('Source coverage: %1$d/%2$d elements; blocking issues: %3$d; affected paths: %4$d; unvisited elements: %5$d; runtime failures: %6$d.','suiteedge-divi-elementor-migrator'),$outcome['covered_elements'],$outcome['source_elements'],$outcome['blocking_issues'],$outcome['affected_paths'],$outcome['unvisited_elements'],$outcome['runtime_failures'])).'</p>';foreach($job->items as $item){foreach($item['divi_data']['information']??[] as $note){echo '<p>'.esc_html($note).'</p>';}}echo '<h2>'.esc_html__('Element and setting coverage','suiteedge-divi-elementor-migrator').'</h2><div class="suiteedge-table-scroll"><table class="widefat striped"><thead><tr><th>Element path</th><th>Source</th><th>Divi target</th><th>Disposition</th><th>Unmapped settings</th></tr></thead><tbody>';foreach($job->items as $item){foreach($item['divi_data']['coverage']??[] as $entry){echo '<tr><td>'.esc_html($entry['path']).'</td><td>'.esc_html($entry['source']).'</td><td>'.esc_html($entry['target']).'</td><td>'.esc_html($entry['disposition']??'legacy coverage').'</td><td>'.esc_html(implode(', ',$entry['unmapped_settings'])).'</td></tr>';}}echo '</tbody></table></div>'; }
        echo '<details><summary>'.esc_html__('Technical JSON report & converted data','suiteedge-divi-elementor-migrator').'</summary><p>'.esc_html__('This includes layout content and URLs. Redact private information before sharing a report.','suiteedge-divi-elementor-migrator').'</p><label class="screen-reader-text" for="suiteedge-report-json">'.esc_html__('JSON report','suiteedge-divi-elementor-migrator').'</label><textarea id="suiteedge-report-json" readonly rows="18" style="width:100%">'.esc_textarea(wp_json_encode($report,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)).'</textarea></details></div>';
    }
}
