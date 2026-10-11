<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class SpacingTranslator {
    public function translate(Node $node, array $a) {
        $unit = new \SuiteEdge\Migrator\Normalize\UnitNormalizer();
        if ($node->type === 'button') { $this->button_padding($node,$a); }
        foreach (['custom_padding'=>'padding','custom_margin'=>'margin'] as $source=>$target) {
            if ($node->type === 'button' && $target === 'padding') { continue; }
            foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) {
                if (isset($a[$source.$suffix]) && $a[$source.$suffix] !== '') {
                    $dims = $unit->dimensions($a[$source.$suffix]);
                    if ($target === 'margin') {
                        // Divi builder margins override theme/default column CSS.
                        // Keep that priority, including partial and zero values.
                        $css = '';
                        foreach (['top','right','bottom','left'] as $side) { if ($dims[$side] !== '') { $css .= 'margin-'.$side.':'.$dims[$side].$dims['unit'].'!important;'; } }
                        if ($css !== '') { $node->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}}.elementor-element','css'=>$css,'device'=>$device]; }
                    }
                    // Elementor omits a dimensions declaration if any side is blank.
                    // Divi's blank sides inherit. Preserve specified sides individually.
                    if ($target !== 'margin' && in_array('',['top'=>$dims['top'],'right'=>$dims['right'],'bottom'=>$dims['bottom'],'left'=>$dims['left']],true)) {
                        $css = '';
                        foreach (['top','right','bottom','left'] as $side) { if ($dims[$side] !== '') { $css .= $target.'-'.$side.':'.$dims[$side].$dims['unit'].';'; } }
                        // Match Elementor's container widget reset specificity (margin-block-end:0).
                        if ($css !== '') { $node->advanced['layout_css'][] = ['selector'=>$node->type === 'button' && $target === 'padding' ? '{{WRAPPER}} .elementor-button' : '{{WRAPPER}}.elementor-element','css'=>$css,'device'=>$device]; }
                    }
                    if ($target === 'padding' && $node->type === 'container') {
                        foreach (['top','right','bottom','left'] as $side) { if ($dims[$side] === '') { $dims[$side] = '0'; } }
                    }
                    $node->styles->set($target,$dims,$device);
                }
            }
        }
    }
    private function button_padding(Node $node,array $a) {
        // Divi's unconfigured sides inherit its button surface, not Elementor's preset size.
        $node->content['text_padding'] = ['unit'=>'em','top'=>'0.3','right'=>'1','bottom'=>'0.3','left'=>'1','isLinked'=>false];
        $unit = new \SuiteEdge\Migrator\Normalize\UnitNormalizer();
        $rules = []; $needs_css = false;
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile','__hover'=>'hover'] as $suffix=>$device) {
            $key = 'custom_padding'.$suffix;
            if (!isset($a[$key]) || $a[$key] === '') { continue; }
            $parts = explode('|',$a[$key]); if (count($parts) === 1) { $parts = array_fill(0,4,$parts[0]); }
            if (count($parts) < 4 || count($parts) > 6) { throw new \UnexpectedValueException('Invalid button padding.'); }
            foreach (array_slice($parts,4) as $flag) { if (!in_array($flag,['','true','false','on','off'],true)) { throw new \UnexpectedValueException('Invalid button padding link flag.'); } }
            $css = ''; $units = []; $dims = ['isLinked'=>false]; $complete = true;
            foreach (['top','right','bottom','left'] as $index=>$side) {
                if ($parts[$index] === '') { $complete = false; continue; }
                $v = $unit->slider($parts[$index]);
                if (!in_array($v['unit'],['px','em','rem','%','vh','vw'],true) || $v['size'] < 0 || !is_finite($v['size'])) { throw new \UnexpectedValueException('Unsupported button padding.'); }
                $units[] = $v['unit']; $dims[$side] = (string)$v['size']; $css .= 'padding-'.$side.':'.$v['size'].$v['unit'].';';
            }
            $node->advanced['handled_attributes'][] = $key;
            if ($complete && count(array_unique($units)) === 1 && in_array($units[0],['px','em','%'],true) && $device !== 'hover') {
                $dims['unit'] = $units[0]; $node->styles->set('padding',$dims,$device);
            } else { $needs_css = true; }
            if ($css !== '') {
                $selector = $device === 'hover' ? '{{WRAPPER}}.elementor-element .elementor-button:hover,{{WRAPPER}}.elementor-element .elementor-button:focus-visible' : '{{WRAPPER}}.elementor-element .elementor-button';
                $rules[] = ['selector'=>$selector,'css'=>$css,'device'=>$device === 'hover' ? 'desktop' : $device];
            }
        }
        if ($needs_css) { $node->advanced['scoped_css'] = array_merge($node->advanced['scoped_css'] ?? [],$rules); }
    }
}
