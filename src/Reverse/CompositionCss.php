<?php
namespace SuiteEdge\Migrator\Reverse;

/** In-memory source compiler: no CSS files, posts, kits or metadata are updated. */
class CompositionCss extends \Elementor\Core\Files\CSS\Post {
    private $tree;
    public function __construct(array $tree) { $this->tree=$tree;parent::__construct(0); }
    protected function get_data() { return $this->tree; }
    public function get_name() { return 'ace-reverse-composition'; }
    public function get_element_unique_selector(\Elementor\Element_Base $element) { return '.elementor .elementor-element'.$element->get_unique_selector(); }
    protected function is_global_parsing_supported() { return false; }
    public function compile_kit(array $settings) {
        $active=\Elementor\Plugin::$instance->kits_manager->get_active_kit();
        $kit=new \Elementor\Core\Kits\Documents\Kit(['post_id'=>$active->get_id(),'settings'=>$settings]);
        $values=$kit->get_settings();
        $this->add_controls_stack_style_rules($kit,$this->get_style_controls($kit,null,$values),$values,['{{WRAPPER}}'],['.ace-source-kit']);
    }
}
