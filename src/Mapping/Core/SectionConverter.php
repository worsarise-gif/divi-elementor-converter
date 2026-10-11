<?php
namespace SuiteEdge\Migrator\Mapping\Core;
class SectionConverter extends \SuiteEdge\Migrator\Mapping\CommonConverter {
    protected function get_target_type() { return 'container'; }
    protected function map_content(\SuiteEdge\Migrator\Model\Node $node,array $raw) {
        parent::map_content($node,$raw);
        // Divi sections have a white base in the theme, unlike transparent Elementor
        // containers. An inactive custom color falls back to that base. Active source
        // colors/images/gradients are applied by BackgroundTranslator afterward.
        $node->styles->set('background_background','classic');
        $node->styles->set('background_color','#ffffff');
    }
}
