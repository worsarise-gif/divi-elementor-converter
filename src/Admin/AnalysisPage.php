<?php
namespace SuiteEdge\Migrator\Admin;
class AnalysisPage {
    public function render() {
        if (!current_user_can('manage_options')) { wp_die('Unauthorized.'); }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation context; sanitize_key and strict comparison choose one of two fixed links. No write is performed.
        View::start(__('Module Coverage & Analysis','suiteedge-divi-elementor-migrator'),__('Review which modules appear in your exports. An available mapping still needs visual review.','suiteedge-divi-elementor-migrator'),sanitize_key(wp_unslash($_GET['templates'] ?? '')) === 'uae' ? 'templates' : 'pages',2);
        $files = get_transient('suiteedge_migrator_pending_files_' . get_current_user_id());
        if (!$files) { View::empty_state(__('No module scan yet','suiteedge-divi-elementor-migrator'),__('Upload a Divi export to see module coverage for your layouts.','suiteedge-divi-elementor-migrator'),'-import',__('Upload layouts','suiteedge-divi-elementor-migrator')); echo '</div>'; return; }
        foreach ($files as $file) {
            try {
                $source = (new \SuiteEdge\Migrator\Import\SourceRepository())->get($file['source_id']);
                $export = (new \SuiteEdge\Migrator\Import\DiviExportReader())->read_json($source['json']);
                $report = (new \SuiteEdge\Migrator\Divi\Scanner())->scan($export['layouts'][$file['layout_index']]['post_content']);
                echo '<section class="suiteedge-panel"><h2>' . esc_html($file['name']) . '</h2><div class="suiteedge-table-scroll" role="region" tabindex="0" aria-label="Module coverage"><table class="widefat striped"><thead><tr><th>Module</th><th>Instances</th><th>Mapping</th><th>Attributes</th></tr></thead><tbody>';
                foreach ($report['modules'] as $tag=>$module) { echo '<tr><td>'.esc_html($tag).'</td><td>'.(int)$module['count'].'</td><td>'.($module['native_mapping'] ? 'Available' : 'Shortcode fallback / review').'</td><td>'.esc_html(implode(', ',array_keys($module['attributes']))).'</td></tr>'; }
                echo '</tbody></table></div><p>Dynamic bindings: '.(int)$report['dynamic'].'. Global references: '.(int)$report['globals'].'. Image URLs: '.count($report['assets']).'.</p></section>';
            } catch (\Throwable $e) { echo '<p>' . esc_html($e->getMessage()) . '</p>'; }
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation context; sanitize_key and strict comparison choose one of two fixed links. No write is performed.
        $templates=sanitize_key(wp_unslash($_GET['templates'] ?? ''))==='uae';
        echo '<p><a class="button" href="?page='.($templates ? 'suiteedge-migrator-template-mapping' : 'suiteedge-migrator-mapping').'">'.($templates ? 'Map Header / Footer templates' : 'Map layouts').'</a></p></div>';
    }
}
