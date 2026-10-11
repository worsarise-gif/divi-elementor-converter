<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class SizingTranslator {
    public function translate(Node $node, array $a) {
        $unit = new \SuiteEdge\Migrator\Normalize\UnitNormalizer();
        foreach (['width'=>'width','min_height'=>'min_height'] as $source=>$target) {
            foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) {
                if (isset($a[$source.$suffix]) && $a[$source.$suffix] !== '') { $node->styles->set($target,$unit->slider($a[$source.$suffix]),$device); }
            }
        }
        if (isset($a['z_index'])) {
            if ($node->type === 'button') {
                // Divi button selectors target the anchor, not its full-width module wrapper.
                $node->advanced['scoped_css'][]=['selector'=>'{{WRAPPER}} .elementor-button','css'=>'position:relative;z-index:'.(int)$a['z_index'].';','device'=>'desktop'];
            } else { $node->styles->set('z_index',(int)$a['z_index']); }
        }
    }
}
