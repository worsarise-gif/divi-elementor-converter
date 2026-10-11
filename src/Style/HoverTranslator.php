<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class HoverTranslator {
    public function translate(Node $node,array $a) {
        if (isset($a['background_color__hover'])) { $node->styles->set('background_hover_background','classic'); $node->styles->set('background_hover_color',$a['background_color__hover']); }
        if ($node->type === 'button' && isset($a['button_text_color__hover'])) { $node->styles->set('hover_color',$a['button_text_color__hover']); }
    }
}
