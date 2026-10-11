<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
/** Supplemental, element-scoped CSS stays inside the exported Elementor document. */
class ScopedCssTranslator {
    private function rule(Node $node,$selector,$declarations,$device = 'desktop') {
        if ($declarations === '') { return; }
        if (preg_match('/[<>{}@]/',$declarations)) { throw new \UnexpectedValueException('Custom CSS contains rules or markup requiring manual review.'); }
        $node->advanced['scoped_css'][] = ['selector'=>$selector,'css'=>$declarations,'device'=>$device];
    }
    public function translate(Node $node,array $a) {
        $unit = new \SuiteEdge\Migrator\Normalize\UnitNormalizer();
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) {
            foreach (['height','max_width'] as $key) {
                if (isset($a[$key.$suffix]) && $a[$key.$suffix] !== '') {
                    $v = $unit->slider($a[$key.$suffix]);
                    if ($node->type === 'image' && $key === 'height') {
                        $node->styles->set('height',$v,$device);
                        // Divi retains the aspect ratio when an image height is supplied.
                        $this->rule($node,'{{WRAPPER}}.elementor-widget-image img','width:auto;',$device);
                    } else {
                        // Elementor's flex widget defaults can outrank a two-class module
                        // selector. Explicit Divi caps must win without altering image-surface width.
                        $selector = $key === 'max_width' && $node->type !== 'container' ? '{{WRAPPER}}.elementor-element.elementor-element' : '{{WRAPPER}}';
                        $this->rule($node,$selector,str_replace('_','-',$key).':'.$v['size'].$v['unit'].';',$device);
                    }
                }
            }
            $main = $node->type === 'button' ? '{{WRAPPER}}.elementor-element .elementor-button' : '{{WRAPPER}}.elementor-element';
            foreach (['custom_css_main_element'=>$main,'custom_css_main'=>$main,'custom_css_before'=>$main.'::before','custom_css_after'=>$main.'::after'] as $key=>$selector) {
                if (!empty($a[$key.$suffix])) { $this->rule($node,$selector,str_replace('||',';', $a[$key.$suffix]),$device); }
            }
            if ($node->type === 'text-editor') {
                foreach ([1=>'header_',2=>'header_2_',3=>'header_3_',4=>'header_4_',5=>'header_5_',6=>'header_6_'] as $level=>$prefix) {
                    $css = '';
                    foreach (['font_size'=>'font-size','letter_spacing'=>'letter-spacing','line_height'=>'line-height'] as $source=>$property) {
                        if (!empty($a[$prefix.$source.$suffix])) { $v = $unit->slider($a[$prefix.$source.$suffix],$source === 'line_height' ? 'em' : 'px'); $css .= $property.':'.$v['size'].$v['unit'].';'; }
                    }
                    foreach (['text_color'=>'color','text_align'=>'text-align'] as $source=>$property) { if (!empty($a[$prefix.$source.$suffix])) { $css .= $property.':'.$a[$prefix.$source.$suffix].';'; } }
                    if (!empty($a[$prefix.'font'.$suffix])) { $css .= $this->font($a[$prefix.'font'.$suffix]); }
                    $this->rule($node,'{{WRAPPER}} h'.$level,$css,$device);
                }
                foreach ([''=>'','__hover'=>':hover'] as $state=>$pseudo) {
                    $key = 'link_text_color'.$suffix.$state;
                    if (!isset($a[$key]) || $a[$key] === '') { continue; }
                    $selector = '{{WRAPPER}}.elementor-widget-text-editor a'.$pseudo;
                    if ($pseudo !== '') { $selector .= ',{{WRAPPER}}.elementor-widget-text-editor a:focus-visible'; }
                    $this->rule($node,$selector,'color:'.$this->link_color($a[$key]).';',$device);
                    $node->advanced['handled_attributes'][] = $key;
                }
            }
            if (isset($a['module_alignment'.$suffix])) {
                $align = ['center'=>['auto','auto'],'left'=>['0','auto'],'right'=>['auto','0']][$a['module_alignment'.$suffix]] ?? null;
                if ($align !== null) { $this->rule($node,'{{WRAPPER}}.elementor-element.elementor-element','margin-left:'.$align[0].';margin-right:'.$align[1].';',$device); }
            }
            if ($node->type === 'image' && isset($a['align'.$suffix]) && !empty($a['width'.$suffix] ?? $a['width'] ?? '')) {
                $alignment = ['left'=>'flex-start','center'=>'center','right'=>'flex-end'][$a['align'.$suffix]] ?? null;
                if ($alignment !== null) { $this->rule($node,'{{WRAPPER}}','align-self:'.$alignment.';',$device); }
            }
            if (in_array($node->type,['image-box','icon-box'],true)) {
                foreach (['image_icon_custom_margin'=>'margin','image_icon_custom_padding'=>'padding'] as $source=>$property) {
                    if (isset($a[$source.$suffix])) {
                        $dims = $unit->dimensions($a[$source.$suffix]); $css = '';
                        foreach (['top','right','bottom','left'] as $side) { if ($dims[$side] !== '') { $css .= $property.'-'.$side.':'.$dims[$side].$dims['unit'].';'; } }
                        $this->rule($node,'{{WRAPPER}} .elementor-icon,{{WRAPPER}} .elementor-image-box-img',$css,$device);
                    }
                }
                $selector = '{{WRAPPER}} .elementor-icon-box-title,{{WRAPPER}} .elementor-image-box-title';
                $css = $this->font($a['header_font'.$suffix] ?? '');
                foreach (['header_font_size'=>'font-size','header_letter_spacing'=>'letter-spacing','header_line_height'=>'line-height'] as $key=>$property) {
                    if (!empty($a[$key.$suffix])) { $v = $unit->slider($a[$key.$suffix],strpos($key,'line_height') !== false ? 'em' : 'px'); $css .= $property.':'.$v['size'].$v['unit'].';'; }
                }
                if (!empty($a['header_text_color'.$suffix])) { $css .= 'color:'.$a['header_text_color'.$suffix].';'; }
                $this->rule($node,$selector,$css,$device);
                $css = $this->font($a['body_font'.$suffix] ?? $a['text_font'.$suffix] ?? '');
                foreach (['font_size'=>'font-size','line_height'=>'line-height','letter_spacing'=>'letter-spacing'] as $key=>$property) {
                    $value = $a['body_'.$key.$suffix] ?? $a['text_'.$key.$suffix] ?? '';
                    if ($value !== '') { $v = $unit->slider($value,$key === 'line_height' ? 'em' : 'px'); $css .= $property.':'.$v['size'].$v['unit'].';'; }
                }
                if (!empty($a['body_text_color'.$suffix] ?? $a['text_text_color'.$suffix] ?? '')) { $css .= 'color:'.($a['body_text_color'.$suffix] ?? $a['text_text_color'.$suffix]).';'; }
                $this->rule($node,'{{WRAPPER}} .elementor-icon-box-description,{{WRAPPER}} .elementor-image-box-description',$css,$device);
                if (isset($a['body_text_align'.$suffix])) { $this->rule($node,'{{WRAPPER}} .elementor-icon-box-description,{{WRAPPER}} .elementor-image-box-description','text-align:'.$a['body_text_align'.$suffix].';',$device); }
                foreach (array_keys($a) as $key) { if (preg_match('/^body_(?:font|font_size|line_height|letter_spacing|text_color|text_align)(?:_tablet|_phone)?$/D',$key)) { $node->advanced['handled_attributes'][] = $key; } }
            }
        }
        if ($node->type === 'video') {
            if (!empty($a['thumbnail_overlay_color'])) { $this->rule($node,'{{WRAPPER}} .elementor-custom-embed-image-overlay::before','content:"";position:absolute;inset:0;background:'.$a['thumbnail_overlay_color'].';'); }
            if (!empty($a['play_icon_color__hover'])) { $this->rule($node,'{{WRAPPER}}:hover .elementor-custom-embed-play svg','fill:'.$a['play_icon_color__hover'].';'); }
        }
        if (isset($a['transform_scale__hover'])) {
            $parts = explode('|',$a['transform_scale__hover']); if (count($parts) !== 2) { throw new \UnexpectedValueException('Unsupported hover scale.'); }
            $x = $unit->slider($parts[0],'%'); $y = $unit->slider($parts[1],'%');
            if ($x['unit'] !== '%' || $y['unit'] !== '%') { throw new \UnexpectedValueException('Unsupported hover scale unit.'); }
            $this->rule($node,'{{WRAPPER}}:hover','transform:scale('.($x['size']/100).','.($y['size']/100).');');
        }
        if (isset($a['box_shadow_color__hover']) && !empty($a['box_shadow_style']) && $a['box_shadow_style'] !== 'none') {
            $css = 'box-shadow:';
            foreach (['horizontal'=>0,'vertical'=>2,'blur'=>18,'spread'=>0] as $key=>$default) { $v = $unit->slider($a['box_shadow_'.$key] ?? $default); $css .= $v['size'].$v['unit'].' '; }
            $css .= $a['box_shadow_color__hover'].';'; $this->rule($node,$node->type === 'image' ? '{{WRAPPER}}:hover img' : '{{WRAPPER}}:hover',$css);
        }
    }
    private function link_color($value) {
        $number = '[+\\-]?(?:\\d+(?:\\.\\d+)?|\\.\\d+)%?';
        $channels = $number.'\\s*,\\s*'.$number.'\\s*,\\s*'.$number;
        if (!is_string($value) || !preg_match('/^(?:#[a-f0-9]{3}|#[a-f0-9]{4}|#[a-f0-9]{6}|#[a-f0-9]{8}|transparent|inherit|currentcolor|(?:rgb|hsl)\\(\\s*'.$channels.'\\s*\\)|(?:rgba|hsla)\\(\\s*'.$channels.'\\s*,\\s*'.$number.'\\s*\\))$/iD',$value)) { throw new \UnexpectedValueException('Unsupported text link color.'); }
        return $value;
    }
    private function font($font) {
        return $font === '' ? '' : FontFamily::declarations(FontFamily::properties($font));
    }
}
