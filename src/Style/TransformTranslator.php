<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class TransformTranslator {
    public function translate(Node $node,array $a) {
        $unit = new \SuiteEdge\Migrator\Normalize\UnitNormalizer();
        foreach (['transform_translate'=>['translateX','translateY'],'transform_rotate'=>['rotateX','rotateY','rotateZ'],'transform_skew'=>['skewX','skewY'],'transform_scale'=>['scaleX','scaleY']] as $source=>$axes) {
            foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) {
                if (!isset($a[$source.$suffix])) { continue; }
                $parts = explode('|',$a[$source.$suffix]);
                if (count($parts) !== count($axes)) { throw new \UnexpectedValueException('Unsupported transform format.'); }
                // Values alone do not activate Elementor's conditional CSS or
                // e-transform class. Enable the corresponding native group.
                $node->styles->set('_'.$source.'_popover','transform');
                foreach ($axes as $i=>$axis) {
                    $default = strpos($axis,'rotate') === 0 || strpos($axis,'skew') === 0 ? 'deg' : 'px';
                    $value = $unit->slider($parts[$i],$default);
                    if (strpos($axis,'scale') === 0) { if ($value['unit'] === '%') { $value['size'] /= 100; } $value['unit'] = 'px'; $node->styles->set('_transform_keep_proportions',''); }
                    if (in_array($axis,['rotateX','rotateY'],true) && $value['size'] !== 0.0) { $node->styles->set('_transform_rotate_3d','yes'); }
                    $node->styles->set('_transform_'.$axis.'_effect',$value,$device);
                }
            }
        }
    }
}
