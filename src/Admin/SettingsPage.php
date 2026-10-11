<?php
namespace SuiteEdge\Migrator\Admin;
class SettingsPage {
    public function render() {
        if (!current_user_can('manage_options')) { wp_die(esc_html__('You do not have permission to access this page.','suiteedge-divi-elementor-migrator')); }
        $saved = false;
        if (isset($_POST['suiteedge_save_settings'])) {
            check_admin_referer('suiteedge_settings');
            update_option('suiteedge_batch_size',max(1,min(5,(int)sanitize_text_field(wp_unslash($_POST['batch_size'] ?? 1)))),false);
            update_option('suiteedge_source_storage_mb',max(20,min(1024,(int)sanitize_text_field(wp_unslash($_POST['source_storage_mb'] ?? 256)))),false);
            foreach (['source'=>2,'job'=>30] as $kind=>$minimum) { $days=(int)sanitize_text_field(wp_unslash($_POST[$kind.'_retention_days'] ?? 0)); update_option('suiteedge_'.$kind.'_retention_days',$days===0 ? 0 : max($minimum,min(3650,$days)),false); }
            $saved = true;
        }
        View::start(__('Settings','suiteedge-divi-elementor-migrator'),__('Control processing and private storage. Conversion settings are chosen separately for each migration.','suiteedge-divi-elementor-migrator'),'settings');
        if ($saved) { echo '<div class="notice notice-success" role="status"><p>'.esc_html__('Settings saved.','suiteedge-divi-elementor-migrator').'</p></div>'; }
        echo '<form method="post">'; wp_nonce_field('suiteedge_settings');
        echo '<section class="suiteedge-panel"><h2>'.esc_html__('Processing','suiteedge-divi-elementor-migrator').'</h2>';
        self::field('batch_size',__('Layouts per request','suiteedge-divi-elementor-migrator'),__('Recommended: 1. Increase only when your hosting can handle larger batches. Allowed range: 1–5.','suiteedge-divi-elementor-migrator'),1,5,get_option('suiteedge_batch_size',1));
        echo '</section><section class="suiteedge-panel"><h2>'.esc_html__('Private storage & retention','suiteedge-divi-elementor-migrator').'</h2><div class="suiteedge-form-grid">';
        self::field('source_storage_mb',__('Source storage limit (MB)','suiteedge-divi-elementor-migrator'),__('Maximum space for private JSON exports. Recommended: 256 MB. Allowed range: 20–1024.','suiteedge-divi-elementor-migrator'),20,1024,get_option('suiteedge_source_storage_mb',256));
        self::field('source_retention_days',__('Unreferenced sources (days)','suiteedge-divi-elementor-migrator'),__('Default: 30. Set 0 to disable cleanup; enabled retention has a minimum of 2 days.','suiteedge-divi-elementor-migrator'),0,3650,get_option('suiteedge_source_retention_days',30));
        self::field('job_retention_days',__('Eligible jobs (days)','suiteedge-divi-elementor-migrator'),__('Default: 90. Set 0 to disable cleanup; enabled retention has a minimum of 30 days.','suiteedge-divi-elementor-migrator'),0,3650,get_option('suiteedge_job_retention_days',90));
        echo '</div><p class="suiteedge-note">'.esc_html__('Daily cleanup examines up to 100 records. Active jobs, interrupted items, created targets, and every backup are protected. A backup also protects its job and source indefinitely. WP-Cron must run for cleanup. Deactivation and deletion retain records.','suiteedge-divi-elementor-migrator').'</p></section>';
        submit_button(__('Save settings','suiteedge-divi-elementor-migrator'),'primary','suiteedge_save_settings');
        echo '</form></div>';
    }
    private static function field($name,$label,$description,$min,$max,$value) {
        echo '<div class="suiteedge-field"><label for="suiteedge-'.esc_attr($name).'">'.esc_html($label).'</label><input type="number" id="suiteedge-'.esc_attr($name).'" name="'.esc_attr($name).'" min="'.(int)$min.'" max="'.(int)$max.'" value="'.(int)$value.'" aria-describedby="suiteedge-'.esc_attr($name).'-help"><p class="description" id="suiteedge-'.esc_attr($name).'-help">'.esc_html($description).'</p></div>';
    }
}
