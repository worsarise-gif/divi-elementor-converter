<?php
/**
 * Plugin Name:       Ace Migrator for Divi and Elementor
 * Description:       Convert supported Divi 4 and Classic Elementor page layouts in both directions, with coverage reports, saved Apply and protected rollback.
 * Version:           1.8.0-rc.1
 * Requires PHP:      7.4
 * Requires at least: 6.5
 * Author:            Ace
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins:  elementor
 * Text Domain:       suiteedge-divi-elementor-migrator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants
define( 'SUITEEDGE_MIGRATOR_VERSION', '1.8.0-rc.1' );
define( 'SUITEEDGE_MIGRATOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SUITEEDGE_MIGRATOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Autoloader setup (PSR-4 via Composer if available, or fallback)
if ( file_exists( SUITEEDGE_MIGRATOR_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once SUITEEDGE_MIGRATOR_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Basic fallback autoloader for src directory
    spl_autoload_register(function ($class) {
        $prefix = 'SuiteEdge\\Migrator\\';
        $base_dir = SUITEEDGE_MIGRATOR_PLUGIN_DIR . 'src/';
        
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    });
}

// Initialize the plugin
function suiteedge_migrator_init() {
    // Ensure dependencies are met (e.g. Elementor is active)
    if ( class_exists( 'SuiteEdge\\Migrator\\Plugin' ) ) {
        $plugin = \SuiteEdge\Migrator\Plugin::get_instance();
        $plugin->run();
    }
}
add_action( 'plugins_loaded', 'suiteedge_migrator_init', 20 );
register_deactivation_hook(__FILE__,['SuiteEdge\\Migrator\\Storage\\Retention','deactivate']);
