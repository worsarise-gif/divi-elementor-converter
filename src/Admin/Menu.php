<?php
namespace SuiteEdge\Migrator\Admin;
class Menu {
    public function __construct() {
        add_action('admin_menu',[$this,'register_menus']);
        add_action('admin_enqueue_scripts',[$this,'admin_styles'],20);
        add_filter('plugin_action_links_suiteedge-divi-elementor-migrator/suiteedge-divi-elementor-migrator.php',[$this,'action_links']);
        add_filter('parent_file',[$this,'parent_file']);
    }
    public function admin_styles($hook) {
        if (!is_string($hook) || strpos($hook,'suiteedge-migrator') === false) { return; }
        wp_enqueue_style('suiteedge-migrator-admin',SUITEEDGE_MIGRATOR_PLUGIN_URL.'assets/css/admin.css',[],SUITEEDGE_MIGRATOR_VERSION);
        // Polling navigates repeatedly; disable cross-document transitions on our screens.
        if (wp_style_is('wp-view-transitions-admin','registered')) { wp_add_inline_style('wp-view-transitions-admin','@view-transition{navigation:none;}'); }
    }
    public function action_links($links) {
        array_unshift($links,'<a href="'.esc_url(View::url('')).'">'.esc_html__('Get started','suiteedge-divi-elementor-migrator').'</a>');
        $links[]='<a href="'.esc_url(View::url('-guide')).'">'.esc_html__('User guide','suiteedge-divi-elementor-migrator').'</a>';
        return $links;
    }
    public function parent_file($parent) {
        $screen=get_current_screen();
        return $screen && strpos($screen->id,'suiteedge-migrator') !== false ? 'suiteedge-migrator-menu' : $parent;
    }
    public function register_menus() {
        $parent='suiteedge-migrator-menu';
        add_menu_page(__('Ace Migrator','suiteedge-divi-elementor-migrator'),__('Ace Migrator','suiteedge-divi-elementor-migrator'),'manage_options',$parent,[$this,'render_dashboard'],'dashicons-randomize',30);
        add_submenu_page($parent,__('Overview','suiteedge-divi-elementor-migrator'),__('Overview','suiteedge-divi-elementor-migrator'),'manage_options','suiteedge-migrator',[$this,'render_dashboard']);
        add_submenu_page($parent,__('Pages & Posts','suiteedge-divi-elementor-migrator'),__('Pages & Posts','suiteedge-divi-elementor-migrator'),'manage_options','suiteedge-migrator-import',[(new ImportPage()),'render']);
        add_submenu_page($parent,__('Convert Site Page','suiteedge-divi-elementor-migrator'),__('Convert Site Page','suiteedge-divi-elementor-migrator'),'manage_options','suiteedge-migrator-site-page',[(new SitePagePage()),'render']);
        add_submenu_page($parent,__('Elementor to Divi','suiteedge-divi-elementor-migrator'),__('Elementor to Divi','suiteedge-divi-elementor-migrator'),'manage_options','suiteedge-migrator-reverse',[(new ReversePage()),'render']);
        add_submenu_page($parent,__('Headers & Footers','suiteedge-divi-elementor-migrator'),__('Headers & Footers','suiteedge-divi-elementor-migrator'),'manage_options','suiteedge-migrator-theme-builder',[(new ThemeBuilderPage()),'render']);
        add_submenu_page($parent,__('Migration Jobs','suiteedge-divi-elementor-migrator'),__('Migration Jobs','suiteedge-divi-elementor-migrator'),'manage_options','suiteedge-migrator-jobs',[(new JobsPage()),'render']);
        add_submenu_page($parent,__('User Guide','suiteedge-divi-elementor-migrator'),__('User Guide','suiteedge-divi-elementor-migrator'),'manage_options','suiteedge-migrator-guide',[(new GuidePage()),'render']);
        add_submenu_page($parent,__('Settings','suiteedge-divi-elementor-migrator'),__('Settings','suiteedge-divi-elementor-migrator'),'manage_options','suiteedge-migrator-settings',[(new SettingsPage()),'render']);
        // Contextual screens remain registered and accessible at their original URLs.
        add_submenu_page('',__('Module Coverage','suiteedge-divi-elementor-migrator'),__('Module Coverage','suiteedge-divi-elementor-migrator'),'manage_options','suiteedge-migrator-analysis',[(new AnalysisPage()),'render']);
        add_submenu_page('',__('Map & Convert','suiteedge-divi-elementor-migrator'),__('Map & Convert','suiteedge-divi-elementor-migrator'),'manage_options','suiteedge-migrator-mapping',[(new MappingPage()),'render']);
        add_submenu_page('',__('Template Mapping','suiteedge-divi-elementor-migrator'),__('Template Mapping','suiteedge-divi-elementor-migrator'),'manage_options','suiteedge-migrator-template-mapping',[(new ThemeBuilderPage()),'mapping']);
        add_submenu_page('',__('Migration Report','suiteedge-divi-elementor-migrator'),__('Migration Report','suiteedge-divi-elementor-migrator'),'manage_options','suiteedge-migrator-reports',[(new ReportPage()),'render']);
        remove_submenu_page($parent,$parent);
    }
    public function render_dashboard() { (new DashboardPage())->render(); }
}
