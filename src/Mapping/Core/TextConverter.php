<?php
namespace SuiteEdge\Migrator\Mapping\Core;
class TextConverter extends \SuiteEdge\Migrator\Mapping\CommonConverter {
    protected function get_target_type() { return 'text-editor'; }
    protected function map_content(\SuiteEdge\Migrator\Model\Node $node,array $raw) {
        parent::map_content($node,$raw);
        // A Divi Text module is a block, even when its horizontal margins are auto.
        // Elementor flex items otherwise shrink to their text, losing caption surfaces.
        // SizingTranslator runs later and preserves every explicit source width.
        $node->styles->set('width',['unit'=>'%','size'=>100]);
    }
}
