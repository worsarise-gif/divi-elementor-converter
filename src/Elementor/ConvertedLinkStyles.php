<?php
namespace SuiteEdge\Migrator\Elementor;
/** Compatibility styling for templates exported before explicit link decoration. */
class ConvertedLinkStyles {
    public function register() {
        add_action('wp_enqueue_scripts',[$this,'enqueue']);
        add_action('elementor/preview/enqueue_styles',[$this,'enqueue']);
    }
    public function enqueue() {
        wp_enqueue_style('suiteedge-converted-links',SUITEEDGE_MIGRATOR_PLUGIN_URL.'assets/css/converted-links.css',[],SUITEEDGE_MIGRATOR_VERSION);
    }
}
