<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class TypographyTranslator {
    public function translate(Node $node, array $a, $unused = '') {
        $unit = new \SuiteEdge\Migrator\Normalize\UnitNormalizer();
        $target = in_array($node->type,['button','text-editor','heading'],true) ? 'typography_' : '';
        if ($target === '') { return; }
        $source = $node->type === 'button' ? 'button_' : ($node->type === 'heading' ? 'title_' : 'text_');
        // Divi's button surface declares 1.7em; Elementor defaults to 1em.
        if ($node->type === 'button' && (!isset($a['button_line_height']) || $a['button_line_height'] === '')) { $a['button_line_height'] = '1.7em'; }
        foreach (['font_size','letter_spacing','line_height'] as $property) {
            foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) {
                $key = $source.$property.$suffix;
                if ($node->type === 'button' && $property === 'font_size' && isset($a['button_text_size'.$suffix])) { $key = 'button_text_size'.$suffix; }
                if (isset($a[$key]) && $a[$key] !== '') {
                    $node->styles->set($target.'typography','custom');
                    $node->styles->set($target.$property,$unit->slider($a[$key], $property === 'line_height' ? 'em' : 'px'),$device);
                }
            }
        }
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile','__hover'=>'desktop'] as $suffix=>$device) {
            if (empty($a[$source.'font'.$suffix])) { continue; }
            $font = $a[$source.'font'.$suffix];
            // Heading-only text modules need a native font control so Elementor loads the font.
            if ($node->type === 'text-editor' && preg_match('/^\s*<h([1-6])\b[^>]*>[\s\S]*<\/h\1>\s*$/i',$node->content['editor'] ?? '',$heading)) {
                $key = 'header_'.($heading[1] === '1' ? '' : $heading[1].'_').'font';
                if ($suffix === '' && !empty($a[$key])) { $font = $a[$key]; }
            } elseif ($suffix === '' && $node->type === 'text-editor' && isset($a['content_tablet']) && !empty($a['header_font'])) { $font = $a['header_font']; }
            $properties = FontFamily::properties($font);
            $node->styles->set($target.'typography','custom');
            if ($suffix === '') {
                foreach ($properties as $property=>$value) {
                    if ($property === 'font_weight' && !preg_match('/^[1-9]00$/D',$value)) { continue; }
                    if ($property === 'text_decoration' && strpos($value,' ') !== false) { continue; }
                    $node->styles->set($target.$property,$value);
                }
            }
            if ($suffix !== '' || !preg_match('/^[1-9]00$/D',$properties['font_weight']) || strpos($properties['text_decoration'],' ') !== false) {
                $selector = $node->type === 'button' ? 'a.elementor-button' : ($node->type === 'heading' ? '.elementor-heading-title' : '.elementor-widget-container');
                $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}}.elementor-widget'.($suffix === '__hover' ? ':hover' : '').' '.$selector,'css'=>FontFamily::declarations($properties),'device'=>$device];
            }
            $node->advanced['handled_attributes'][] = $source.'font'.$suffix;
        }
        $color = $a[$source.'text_color'] ?? null;
        if ($color !== null) { $node->styles->set($node->type === 'button' ? 'button_text_color' : ($node->type === 'heading' ? 'title_color' : 'text_color'),$color); }
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) {
            $align = $a[($node->type === 'button' ? 'button_alignment' : ($node->type === 'heading' ? 'title_text_align' : 'text_orientation')).$suffix] ?? '';
            if ($align !== '') { $node->styles->set('align',$align,$device); }
        }
        if ($node->type === 'heading') {
            foreach (array_keys($a) as $key) { if (preg_match('/^title_(?:font|font_size|letter_spacing|line_height|text_color|text_align)(?:_tablet|_phone)?$/D',$key)) {
                // Native typography's family/style/weight controls are desktop-only.
                if (preg_match('/^title_text_color_(tablet|phone)$/D',$key,$m)) {
                    if (!preg_match('/^(?:#[a-f0-9]{3}|#[a-f0-9]{4}|#[a-f0-9]{6}|#[a-f0-9]{8})$/iD',$a[$key])) { throw new \UnexpectedValueException('Unsupported responsive heading color.'); }
                    $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}}.elementor-widget .elementor-heading-title','css'=>'color:'.$a[$key].';','device'=>$m[1] === 'phone' ? 'mobile' : 'tablet'];
                }
                $node->advanced['handled_attributes'][] = $key;
            } }
        }
    }
}
