<?php
namespace SuiteEdge\Migrator\Mapping\Core;

use SuiteEdge\Migrator\Model\Node;
use SuiteEdge\Migrator\Style\SourceThemeProfile;

/** Route module surfaces to editable Free widgets without copying wrapper styles to children. */
abstract class BusinessComposition extends \SuiteEdge\Migrator\Mapping\BaseConverter {
    protected function get_target_type() { return 'container'; }
    public function convert(array $raw) {
        $node=parent::convert($raw);
        $handled=array_flip($node->advanced['handled_attributes'] ?? []);
        foreach ($raw['attributes'] ?? [] as $key=>$value) {
            if ($value === '' || isset($handled[$key]) || preg_match('/(?:_last_edited|__hover_enabled)$/D',$key)) { continue; }
            if (preg_match('/^(?:(?:background_|use_background_).+|custom_padding|background_layout)(?:_tablet|_phone|__hover)$/D',$key)
                && $key !== 'custom_padding_tablet' && $key !== 'custom_padding_phone'
                && !($raw['type']==='et_pb_team_member' && $key==='background_color__hover')) {
                throw new \UnexpectedValueException('Composition background/palette or hover-padding state requires review; original module retained instead of discarding that state.');
            }
            if (preg_match('/^(?:custom_css_(?:promo|member|title)|(?:border|box_shadow)_.+_image|button_)/',$key)) {
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Diagnostic is escaped by report consumers.
                throw new \UnexpectedValueException('Composition surface setting '.$key.' is not accepted yet; original module retained instead of discarding its style.');
            }
        }
        return $node;
    }
    protected function start(Node $node, array $raw, $padding) {
        $this->force_consume_children = true;
        if (!empty($raw['children'])) { throw new \UnexpectedValueException('Nested modules inside this composition require review; original content retained.'); }
        if (!empty($raw['attributes']['link_option_url'])) { throw new \UnexpectedValueException('Whole-module links require review to avoid nesting the button, biography or social links inside another anchor.'); }
        $node->content = $this->container($padding);
        $node->advanced['css_classes'][] = 'ace-business-composition';
        $node->advanced['handled_attributes'] = ['background_layout','text_orientation','text_orientation_tablet','text_orientation_phone','header_level','hover_enabled','sticky_enabled'];
        $layout = $raw['attributes']['background_layout'] ?? ($raw['type'] === 'et_pb_cta' ? 'dark' : 'light');
        if (!in_array($layout,['light','dark'],true)) { throw new \UnexpectedValueException('Unsupported composition text color mode.'); }
    }
    protected function container($padding = '0') {
        return ['content_width'=>'full','flex_direction'=>'column','padding'=>['unit'=>'px','top'=>$padding,'right'=>$padding,'bottom'=>$padding,'left'=>$padding,'isLinked'=>true],'flex_gap'=>['unit'=>'px','size'=>0,'column'=>'0','row'=>'0','isLinked'=>true]];
    }
    protected function surface(Node $owner, array $a, $prefix, $target_prefix) {
        $result = [];
        foreach ($a as $key=>$value) {
            if (preg_match('/^'.preg_quote($prefix,'/').'_(font|font_size|text_color|text_align|letter_spacing|line_height)(|_tablet|_phone|__hover)$/D',$key,$m)) {
                $result[$target_prefix.$m[1].$m[2]] = $value;
                $owner->advanced['handled_attributes'][] = $key;
            }
        }
        return $result;
    }
    protected function theme(array $raw) {
        return $raw['_source_theme'] ?? (new SourceThemeProfile())->recommended();
    }
    protected function font($font, $family, $weight) {
        $parts = explode('|',$font);
        if ($parts[0] === '' || strpos($parts[0],'--et_global_') === 0) { $parts[0] = $family; }
        if (($parts[1] ?? '') === '') { $parts[1] = (string)$weight; }
        return implode('|',$parts);
    }
    protected function color(array $raw, $heading = false) {
        $dark = ($raw['attributes']['background_layout'] ?? ($raw['type'] === 'et_pb_cta' ? 'dark' : 'light')) === 'dark';
        return $dark ? '#ffffff' : $this->theme($raw)[$heading ? 'heading_color' : 'body_color'];
    }
    protected function heading(Node $owner, array $raw, $text, $prefix = 'header', $default_level = 'h2') {
        $a = $raw['attributes']; $profile = $this->theme($raw);
        $mapped = $this->surface($owner,$a,$prefix,'title_');
        $level = $prefix === 'header' ? ($a['header_level'] ?? $default_level) : $default_level;
        $mapped += ['title'=>$text,'title_level'=>$level,'title_text_color'=>$this->color($raw,true),'title_line_height'=>'1em'];
        $mapped['title_font'] = $this->font($mapped['title_font'] ?? '',$profile[$prefix === 'position' ? 'body_font' : 'heading_font'],$prefix === 'position' ? $profile['body_weight'] : 500);
        $sizes = ['h1'=>30,'h2'=>26,'h3'=>22,'h4'=>18,'h5'=>16,'h6'=>14];
        if (!isset($sizes[$level])) { throw new \UnexpectedValueException('Unsupported composition heading level.'); }
        $mapped += ['title_font_size'=>$sizes[$level].'px'];
        foreach (['','_tablet','_phone'] as $suffix) { $mapped += ['title_text_align'.$suffix=>$a['text_orientation'.$suffix] ?? ($a['text_orientation'] ?? ($raw['type'] === 'et_pb_cta' ? 'center' : 'left'))]; }
        $this->font_states($mapped,'title_');
        $child = (new HeadingConverter())->convert($this->raw('et_pb_heading',$mapped));
        $this->typography_css($child,$mapped,'title_','.elementor-heading-title');
        $this->surface_css($owner,$child,$a,$prefix === 'header' ? ($raw['type'] === 'et_pb_cta' ? 'custom_css_promo_title' : 'custom_css_title') : 'custom_css_member_position','{{WRAPPER}} .elementor-heading-title');
        return $child;
    }
    protected function body(Node $owner, array $raw) {
        $a = $raw['attributes']; $profile = $this->theme($raw);
        $mapped = $this->surface($owner,$a,'body','text_');
        $mapped += ['text_font_size'=>$profile['body_size'].'px','text_line_height'=>($profile['body_size']*1.7).'px','text_text_color'=>$this->color($raw)];
        $mapped['text_font'] = $this->font($mapped['text_font'] ?? '',$profile['body_font'],$profile['body_weight']);
        foreach (['','_tablet','_phone'] as $suffix) { $mapped['text_orientation'.$suffix] = $mapped['text_text_align'.$suffix] ?? $a['text_orientation'.$suffix] ?? ($a['text_orientation'] ?? ($raw['type'] === 'et_pb_cta' ? 'center' : 'left')); unset($mapped['text_text_align'.$suffix]); }
        $this->font_states($mapped,'text_');
        foreach (['content_tablet','content_phone','link_text_color','link_text_color__hover'] as $key) { if (isset($a[$key])) { $mapped[$key]=$a[$key]; $owner->advanced['handled_attributes'][]=$key; } }
        $child = (new TextConverter())->convert($this->raw('et_pb_text',$mapped,$raw['text_content'] ?? ''));
        $this->typography_css($child,$mapped,'text_','.elementor-widget-container');
        return $child;
    }
    protected function raw($type, array $a, $text = '') { return ['type'=>$type,'attributes'=>$a,'text_content'=>$text,'raw_content'=>$text,'children'=>[],'original_shortcode'=>'']; }
    protected function font_states(array &$a, $prefix) {
        $base=explode('|',$a[$prefix.'font']);
        foreach (['_tablet','_phone','__hover'] as $suffix) {
            if (!isset($a[$prefix.'font'.$suffix]) || $a[$prefix.'font'.$suffix] === '') { continue; }
            $a[$prefix.'font'.$suffix]=$this->font($a[$prefix.'font'.$suffix],$base[0],$base[1] ?? '400');
        }
    }
    protected function typography_css(Node $child, array $a, $prefix, $selector) {
        $unit=new \SuiteEdge\Migrator\Normalize\UnitNormalizer();
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile','__hover'=>'desktop'] as $suffix=>$device) {
            $css='';
            if ($suffix !== '' && isset($a[$prefix.'text_color'.$suffix]) && $a[$prefix.'text_color'.$suffix] !== '') {
                $color=$a[$prefix.'text_color'.$suffix];
                if (!is_string($color) || !preg_match('/^(?:#[a-f0-9]{3,8}|transparent|inherit|currentcolor|rgba?\([0-9.,%\s]+\))$/iD',$color)) { throw new \UnexpectedValueException('Unsupported composition text color.'); }
                $css.='color:'.$color.';';
            }
            if ($suffix === '__hover') {
                foreach (['font_size'=>'font-size','letter_spacing'=>'letter-spacing','line_height'=>'line-height'] as $key=>$property) {
                    if (isset($a[$prefix.$key.$suffix]) && $a[$prefix.$key.$suffix] !== '') { $v=$unit->slider($a[$prefix.$key.$suffix],$key==='line_height'?'em':'px'); $css.=$property.':'.$v['size'].$v['unit'].';'; }
                }
            }
            if ($css !== '') { $child->advanced['scoped_css'][]=['selector'=>'{{WRAPPER}}.elementor-element'.($suffix==='__hover'&&$child->type!=='button'?':hover':'').' '.$selector.($suffix==='__hover'&&$child->type==='button'?':hover':''),'css'=>$css,'device'=>$device]; }
        }
    }
    protected function surface_css(Node $owner, Node $child, array $a, $key, $selector) {
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile','__hover'=>'desktop'] as $suffix=>$device) {
            if (!isset($a[$key.$suffix]) || $a[$key.$suffix] === '') { continue; }
            $css = str_replace('||',';',$a[$key.$suffix]);
            if (preg_match('/[<>{}@]/',$css)) { throw new \UnexpectedValueException('Composition surface CSS contains rules or markup requiring manual review.'); }
            $child->advanced['scoped_css'][] = ['selector'=>str_replace('{{WRAPPER}}','{{WRAPPER}}.elementor-element'.($suffix === '__hover' ? ':hover' : ''),$selector),'css'=>$css,'device'=>$device];
            $owner->advanced['handled_attributes'][] = $key.$suffix;
        }
    }
    protected function child(Node $owner, Node $child, $name) {
        $child->advanced['css_classes'][] = $name;
        if ($child->type !== 'button') {
            foreach ($child->advanced['scoped_css'] ?? [] as $index=>$rule) {
                if ($child->type==='container' && strpos($rule['selector'],'{{WRAPPER}}:hover ')===0) {
                    $owner->advanced['scoped_css'][]=$rule;unset($child->advanced['scoped_css'][$index]);continue;
                }
                if (preg_match('/^\{\{WRAPPER\}\}\.elementor-(?:element|widget):hover\s+(.+)$/D',$rule['selector'],$match)) {
                    $rule['selector']='{{WRAPPER}}:hover .'.$name.' '.$match[1];
                    $owner->advanced['scoped_css'][]=$rule;
                    unset($child->advanced['scoped_css'][$index]);
                }
            }
        }
        // Reset the destination widget gap before applying the source surface spacing.
        $child->advanced['layout_css']=$child->advanced['layout_css'] ?? [];
        array_unshift($child->advanced['layout_css'],['selector'=>'{{WRAPPER}}.elementor-element','css'=>'margin-bottom:0;','device'=>'desktop']);
        $owner->add_child($child);
        foreach ($child->advanced['warnings'] ?? [] as $note) { $owner->advanced['warnings'][]=$note; }
    }
}
