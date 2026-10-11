<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class ShapeDividerTranslator {
    public function translate(Node $node,array $a) {
        if ($node->type !== 'container') { return; }
        foreach (['top','bottom'] as $side) {
            $style = $a[$side.'_divider_style'] ?? 'none';
            if ($style === 'none') { continue; }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            if ($style !== 'slant') { throw new \UnexpectedValueException('Unsupported section divider shape: '.$style); }
            $prefix = 'shape_divider_'.$side;
            $node->content[$prefix] = 'tilt';
            if (isset($a[$side.'_divider_color'])) { $node->content[$prefix.'_color'] = $a[$side.'_divider_color']; }
            if (($a[$side.'_divider_flip'] ?? '') === 'horizontal') { $node->content[$prefix.'_flip'] = 'yes'; }
            foreach ([''=>'','_tablet'=>'_tablet','_phone'=>'_mobile'] as $source=>$target) {
                if (isset($a[$side.'_divider_height'.$source])) { $node->content[$prefix.'_height'.$target] = (new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->slider($a[$side.'_divider_height'.$source]); }
            }
        }
    }
}
