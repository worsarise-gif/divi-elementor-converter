<?php
namespace SuiteEdge\Migrator\Admin;
class ExportHandler {
    public function __construct() { add_action('admin_post_suiteedge_migrator_export',[$this,'download']); }
    public function download() {
        if (!current_user_can('manage_options')) { wp_die('Unauthorized.','',403); }
        check_admin_referer('suiteedge_export');
        $job = (new \SuiteEdge\Migrator\Jobs\JobRepository())->get(sanitize_text_field(wp_unslash($_GET['job'] ?? '')));
        if (!$job) { wp_die('Job unavailable.','',404); }
        $export = new \SuiteEdge\Migrator\Reports\ExportReport();
        $mime='application/json';
        if (isset($_GET['item'])) {
            $index = (int)$_GET['item']; $item = $job->items[$index] ?? null;
            if (($job->settings['direction'] ?? '')==='elementor_to_divi') {
                if (!$item || empty($item['divi_data'])) { wp_die('Converted Divi document unavailable.','',404); }
                try { (new \SuiteEdge\Migrator\Reverse\Shortcodes())->validate($item['divi_data']); } catch(\Throwable $e) { wp_die(esc_html($e->getMessage()),'',409); }
                $json=$item['divi_data']['content'];$name='divi-layout-'.$index.'.txt';$mime='text/plain';
            } else {
                if (!$item || !isset($item['elementor_data'])) { wp_die('Converted document unavailable.','',404); }
                $json = $export->template($item['elementor_data'],$item['name']); $name = 'elementor-template-'.$index.'.json';
            }
        } else { $json = $export->json($job); $name = 'migration-report-'.$job->id.'.json'; }
        nocache_headers(); header('Content-Type: '.$mime.'; charset=utf-8'); header('Content-Disposition: attachment; filename="'.$name.'"');
        // JSON attachment, not HTML. HTML escaping would corrupt the Elementor template.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ExportReport encodes valid JSON; response is an application/json attachment.
        echo $json; exit;
    }
}
