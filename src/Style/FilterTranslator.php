<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class FilterTranslator {
    public function translate(Node $node, array $a) {
        $filters = ['blur'=>['px',0],'brightness'=>['%',100],'contrast'=>['%',100],'saturate'=>['%',100],'hue_rotate'=>['deg',0],'grayscale'=>['%',0],'invert'=>['%',0],'opacity'=>['%',100]];
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile','__hover'=>'hover'] as $suffix=>$device) {
            $active = false; $css = [];
            foreach ($filters as $filter=>$definition) {
                $value = $a['filter_'.$filter.$suffix] ?? ($device !== 'desktop' ? ($a['filter_'.$filter] ?? null) : null);
                if (isset($a['filter_'.$filter.$suffix]) && $a['filter_'.$filter.$suffix] !== '') { $active = true; }
                $v = $value === null || $value === '' ? ['unit'=>$definition[0],'size'=>$definition[1]] : (new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->slider($value,$definition[0]);
                if ($v['unit'] !== $definition[0] || ($filter !== 'hue_rotate' && $v['size'] < 0)) { throw new \UnexpectedValueException('Unsupported CSS filter unit or value.'); }
                $css[] = str_replace('_','-',$filter).'('.$v['size'].$v['unit'].')';
                if ($node->type === 'image' && in_array($device,['desktop','hover'],true) && in_array($filter,['blur','brightness','contrast','saturate','hue_rotate'],true) && isset($a['filter_'.$filter.$suffix])) {
                    $prefix = $device === 'hover' ? 'css_filters_hover_' : 'css_filters_';
                    $node->styles->set($prefix.'css_filter','custom');
                    // Elementor stores all CSS-filter slider units as px; selectors supply %/deg.
                    $node->styles->set($prefix.($filter === 'hue_rotate' ? 'hue' : $filter),['unit'=>'px','size'=>$v['size']]);
                }
            }
            if ($active) {
                $selector = '{{WRAPPER}}'.($device === 'hover' ? ':hover' : '').($node->type === 'image' ? ' img' : '');
                $node->advanced['scoped_css'][] = ['selector'=>$selector,'css'=>'filter:'.implode(' ',$css).';','device'=>$device === 'hover' ? 'desktop' : $device];
            }
        }
    }
}
