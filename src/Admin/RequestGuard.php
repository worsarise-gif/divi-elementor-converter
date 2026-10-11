<?php
namespace SuiteEdge\Migrator\Admin;
/** Authenticate form submissions before WordPress prints the admin header. */
class RequestGuard {
    public function __construct() { add_action('admin_init',[$this,'verify'],0); }
    public function verify() {
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        $forms = ['suiteedge-migrator-import'=>['submit_upload','suiteedge_import'],'suiteedge-migrator-jobs'=>['submit_conversion','suiteedge_create_job'],'suiteedge-migrator-settings'=>['suiteedge_save_settings','suiteedge_settings']];
        $forms['suiteedge-migrator-theme-builder']=['submit_upload','suiteedge_import'];
        $forms['suiteedge-migrator-site-page']=['prepare_site_page','suiteedge_site_page'];
        if ($page==='suiteedge-migrator-reverse' && (isset($_POST['prepare_reverse']) || isset($_POST['convert_reverse']))) { if (!current_user_can('manage_options')) { wp_die('Unauthorized.','',403); } check_admin_referer('ace_reverse_page'); return; }
        if (!isset($forms[$page]) || !isset($_POST[$forms[$page][0]])) { return; }
        if (!current_user_can('manage_options')) { wp_die('Unauthorized.','',403); }
        check_admin_referer($forms[$page][1]);
    }
}
