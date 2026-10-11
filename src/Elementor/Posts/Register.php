<?php
namespace SuiteEdge\Migrator\Elementor\Posts;

class Register {
    public function hooks() {
        add_action('elementor/widgets/register', [$this, 'widgets']);
        add_action('wp_enqueue_scripts', [$this, 'styles']);
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'styles']);
    }
    public function widgets($manager) { $manager->register(new GridWidget()); }
    public function styles() {
        wp_register_style('ace-migrator-post-grid', SUITEEDGE_MIGRATOR_PLUGIN_URL . 'assets/css/post-grid.css', [], SUITEEDGE_MIGRATOR_VERSION);
        wp_register_script('ace-migrator-post-grid', SUITEEDGE_MIGRATOR_PLUGIN_URL . 'assets/js/post-grid.js', [], SUITEEDGE_MIGRATOR_VERSION, true);
    }
}
