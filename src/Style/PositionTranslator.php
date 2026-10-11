<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class PositionTranslator {
    public function translate(Node $node,array $a) {
        $value = $a['positioning'] ?? $a['position_type'] ?? '';
        if ($value === '' || $value === 'relative') { return; }
        if (!in_array($value,['absolute','fixed'],true)) { throw new \UnexpectedValueException('Positioning needs manual review.'); }
        $node->styles->set($node->type === 'container' ? 'position' : '_position',$value);
        $unit = new \SuiteEdge\Migrator\Normalize\UnitNormalizer();
        foreach (['horizontal_offset'=>'_offset_x','vertical_offset'=>'_offset_y'] as $source=>$target) {
            foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) {
                if (isset($a[$source.$suffix])) { $node->styles->set($target,$unit->slider($a[$source.$suffix]),$device); }
            }
        }
        $node->styles->set('_offset_orientation_h','start'); $node->styles->set('_offset_orientation_v','start');
    }
}
