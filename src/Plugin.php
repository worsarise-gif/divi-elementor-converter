<?php

namespace SuiteEdge\Migrator;

class Plugin {

    /**
     * @var Plugin
     */
    private static $instance = null;

    /**
     * @return Plugin
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialize components
    }

    public function run() {
        (new \SuiteEdge\Migrator\Reverse\Presentation())->register();
        (new \SuiteEdge\Migrator\Storage\Retention())->register();
        // Keep instructions and requirements available when Elementor is inactive.
        $this->setup_admin();
        if ( ! $this->meets_requirements() ) {
            return;
        }

        (new \SuiteEdge\Migrator\Elementor\ConvertedLinkStyles())->register();
        (new \SuiteEdge\Migrator\Elementor\Posts\Register())->hooks();
    }

    private function meets_requirements() {
        // Check for Elementor
        if ( (new Requirements())->check() ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
            return false;
        }
        return true;
    }

    public function admin_notice_missing_elementor() {
        
        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'suiteedge-divi-elementor-migrator' ),
            '<strong>' . esc_html__( 'Ace Migrator for Divi and Elementor', 'suiteedge-divi-elementor-migrator' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'suiteedge-divi-elementor-migrator' ) . '</strong>'
        );
        printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post($message) );
    }

    private function setup_admin() {
        if ( is_admin() ) {
            new \SuiteEdge\Migrator\Admin\RequestGuard();
            new \SuiteEdge\Migrator\Admin\Menu();
            new \SuiteEdge\Migrator\Admin\ExportHandler();
            
            if ( wp_doing_ajax() ) {
                new \SuiteEdge\Migrator\Admin\AjaxHandler();
            }
        }
    }
}
