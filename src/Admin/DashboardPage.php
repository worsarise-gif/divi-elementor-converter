<?php
namespace SuiteEdge\Migrator\Admin;
class DashboardPage {
    public function render() {
        if (!current_user_can('manage_options')) { wp_die(esc_html__('You do not have permission to access this page.','suiteedge-divi-elementor-migrator')); }
        View::start(__('Move your layouts. Keep control.','suiteedge-divi-elementor-migrator'),__('Convert supported Divi 4 pages or exports into editable Elementor layouts. Review the result, then apply it to the destination you choose.','suiteedge-divi-elementor-migrator'));
        echo '<div class="suiteedge-overview-grid"><section class="suiteedge-panel suiteedge-start"><p class="suiteedge-eyebrow">'.esc_html__('YOUR FIRST MIGRATION','suiteedge-divi-elementor-migrator').'</p><h2>'.esc_html__('From Divi to editable Elementor page','suiteedge-divi-elementor-migrator').'</h2><ol class="suiteedge-start-steps">';
        $steps = [
            [__('Choose a source','suiteedge-divi-elementor-migrator'),__('Select a Divi page on this site, or upload Divi 4 exports for a batch or a different source site.','suiteedge-divi-elementor-migrator')],
            [__('Choose destinations and convert','suiteedge-divi-elementor-migrator'),__('Match each layout to a page or a new draft. Convert first to save an Elementor template and a report.','suiteedge-divi-elementor-migrator')],
            [__('Review, apply, and check','suiteedge-divi-elementor-migrator'),__('Resolve blockers, review warnings, and apply the saved result. Check desktop, tablet, mobile, and interactions in Elementor.','suiteedge-divi-elementor-migrator')],
        ];
        foreach ($steps as $step) { echo '<li><strong>'.esc_html($step[0]).'</strong><p>'.esc_html($step[1]).'</p></li>'; }
        echo '</ol><div class="suiteedge-actions"><a class="button button-primary" href="'.esc_url(View::url('-site-page')).'">'.esc_html__('Convert a site page','suiteedge-divi-elementor-migrator').'</a><a class="button" href="'.esc_url(View::url('-import')).'">'.esc_html__('Import JSON exports','suiteedge-divi-elementor-migrator').'</a></div></section><aside class="suiteedge-panel"><h2>'.esc_html__('Your migration environment','suiteedge-divi-elementor-migrator').'</h2><ul class="suiteedge-environment">';
        $environment = [
            'WordPress'=>get_bloginfo('version'), 'PHP'=>PHP_VERSION,
            __('Divi 4 reverse Apply','suiteedge-divi-elementor-migrator')=>defined('ET_BUILDER_VERSION') && preg_match('/^4\./',ET_BUILDER_VERSION) && shortcode_exists('et_pb_section') ? __('Available','suiteedge-divi-elementor-migrator') : __('Activate Divi 4 for reverse Apply','suiteedge-divi-elementor-migrator'),
            'Elementor'=>did_action('elementor/loaded') ? (defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : __('Active','suiteedge-divi-elementor-migrator')) : __('Activate Elementor','suiteedge-divi-elementor-migrator'),
            __('UAE headers / footers','suiteedge-divi-elementor-migrator')=>defined('HFE_VER') && post_type_exists('elementor-hf') ? __('Available','suiteedge-divi-elementor-migrator') : __('Optional plugin','suiteedge-divi-elementor-migrator'),
            __('Apply permission','suiteedge-divi-elementor-migrator')=>current_user_can('unfiltered_html') ? __('Available','suiteedge-divi-elementor-migrator') : __('Restricted','suiteedge-divi-elementor-migrator'),
        ];
        foreach ($environment as $label=>$value) { echo '<li><span>'.esc_html($label).'</span><strong>'.esc_html($value).'</strong></li>'; }
        echo '</ul><p class="suiteedge-note">'.esc_html__('Back up your site and run your first migration on staging. Module support varies; the report records differences that need your review.','suiteedge-divi-elementor-migrator').'</p><a href="'.esc_url(View::url('-guide').'#requirements').'">'.esc_html__('Requirements & supported scope','suiteedge-divi-elementor-migrator').' &rarr;</a></aside></div>';
        echo '<section class="suiteedge-panel suiteedge-template-intro"><div><p class="suiteedge-eyebrow">'.esc_html__('BEYOND PAGE CONTENT','suiteedge-divi-elementor-migrator').'</p><h2>'.esc_html__('Migrate your global header and footer','suiteedge-divi-elementor-migrator').'</h2><p>'.esc_html__('Divi Theme Builder exports use a separate workflow. Convert them to UAE Header or Footer templates and keep existing destination display rules.','suiteedge-divi-elementor-migrator').'</p></div><a class="button" href="'.esc_url(View::url('-theme-builder')).'">'.esc_html__('Open Header & Footer migrator','suiteedge-divi-elementor-migrator').'</a></section>';
        echo '<section class="suiteedge-history"><div><h2>'.esc_html__('Continue your work','suiteedge-divi-elementor-migrator').'</h2><p>';
        /* translators: %d: number of migration jobs belonging to the current user. */
        printf(esc_html__('You have %d migration jobs in your account. Open a report, apply a completed result, or review a rollback.','suiteedge-divi-elementor-migrator'),(int)(new \SuiteEdge\Migrator\Jobs\JobRepository())->count());
        echo '</p></div><a class="button" href="'.esc_url(View::url('-jobs')).'">'.esc_html__('View migration jobs','suiteedge-divi-elementor-migrator').'</a></section></div>';
    }
}
