<?php
namespace SuiteEdge\Migrator\Mapping\Core;
use SuiteEdge\Migrator\Model\Node;

/** Divi 4 et_pb_cta is a composition; et_pb_promo is its CSS class, not its tag. */
class CallToActionConverter extends BusinessComposition {
    public function convert(array $raw) {
        $original=$raw['attributes'];
        $defaults=$this->default_padding($raw);
        $overrides=array_fill(0,4,null);
        foreach (['','_tablet','_phone'] as $suffix) {
            $key='custom_padding'.$suffix;
            $flags=[];
            if (isset($raw['attributes'][$key]) && $raw['attributes'][$key] !== '') {
                $parts=explode('|',$raw['attributes'][$key]);
                if(count($parts)===1){$parts=array_fill(0,4,$parts[0]);}
                if(count($parts)<4||count($parts)>6){throw new \UnexpectedValueException('Invalid CTA module padding.');}
                $flags=array_slice($parts,4);
                foreach (range(0,3) as $index) { if ($parts[$index] !== '') { $overrides[$index]=$parts[$index]; } }
            }
            $complete=[];foreach(range(0,3) as $index){$complete[]=$overrides[$index] ?? $defaults[$suffix][$index];}
            $raw['attributes'][$key]=implode('|',array_merge($complete,$flags));
        }
        $node=parent::convert($raw);$node->source_trace['attributes']=$original;return $node;
    }
    private function default_padding(array $raw) {
        $a=$raw['attributes'];$narrow=in_array($raw['_column_context']['column_type'] ?? '',['1_2','1_3','1_4','1_5','1_6','2_5','3_5'],true);
        $result=[];$background='';$enabled='';
        foreach (['','_tablet','_phone'] as $suffix) {
            $background=$a['background_color'.$suffix] ?? $background;$enabled=$a['background_enable_color'.$suffix] ?? $enabled;
            $has=$background!==''&&$enabled!=='off';
            $side=$suffix==='_phone'||$narrow?'40px':'60px';
            $result[$suffix]=$has?['40px',$side,'40px',$side]:['0px','0px','0px','0px'];
        }
        return $result;
    }
    protected function map_content(Node $node, array $raw) {
        $this->start($node,$raw,'0'); $a = $raw['attributes'];
        if (!isset($a['background_layout']) && empty($a['background_color']) && empty($a['background_image']) && empty($a['header_text_color']) && empty($a['body_text_color'])) {
            $node->advanced['warnings'][]='CTA export does not declare its background or text palette. The source dark palette was used; verify inherited Divi defaults against the original before publishing.';
        }
        $description = new Node('et_pb_cta_description','container');
        $description->content = $this->container();
        $description->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}}.elementor-element','css'=>'padding-bottom:20px;','device'=>'desktop'];
        if (($a['title'] ?? '') !== '') { $this->child($description,$this->heading($node,$raw,$a['title']),'ace-cta-title'); }
        if (trim($raw['text_content'] ?? '') !== '' || isset($a['content_tablet']) || isset($a['content_phone'])) { $this->child($description,$this->body($node,$raw),'ace-cta-body'); }
        if ($description->children) {
            $this->surface_css($node,$description,$a,'custom_css_promo_description','{{WRAPPER}}');
            $this->child($node,$description,'ace-cta-description');
        }
        $node->advanced['handled_attributes'] = array_merge($node->advanced['handled_attributes'],['title','button_text','button_url','url_new_window']);
        // Divi displays its button only when both label and destination are nonempty.
        if (($a['button_text'] ?? '') !== '' && ($a['button_url'] ?? '') !== '') {
            $button = []; $custom = $a['custom_button'] ?? 'off';
            if (!in_array($custom,['on','off'],true)) { throw new \UnexpectedValueException('Unsupported CTA custom-button flag.'); }
            foreach ($a as $key=>$value) {
                if (strpos($key,'button_') === 0) {
                    if ($custom === 'off') { $node->advanced['handled_attributes'][] = $key; continue; }
                    if (preg_match('/^button_(?:text|url|font|font_size|text_size|letter_spacing|line_height|text_color|alignment|bg_color|bg_enable_color|border_width|border_color|border_radius|use_icon|icon|icon_color|icon_placement|on_hover)(?:_tablet|_phone|__hover)?$/D',$key)) {
                        $node->advanced['handled_attributes'][] = $key;
                        $button[$key]=$value;
                    }
                }
            }
            $button['button_text']=$a['button_text']; $button['button_url']=$a['button_url'];
            $button += ['button_text_color'=>($a['background_layout'] ?? 'dark')==='light'?$this->theme($raw)['accent_color']:$this->color($raw),'button_bg_color'=>'rgba(0,0,0,0)','button_border_width'=>'2px','button_border_radius'=>'3px','button_text_size'=>'20px'];
            $button['button_font'] = $this->font($button['button_font'] ?? '',$this->theme($raw)['body_font'],500);
            $this->font_states($button,'button_');
            foreach (['','_tablet','_phone','__hover'] as $suffix) { if (($button['button_bg_enable_color'.$suffix] ?? '')==='off') { unset($button['button_bg_color'.$suffix]); } }
            $button['url_new_window'] = $a['url_new_window'] ?? 'off';
            foreach (['','_tablet','_phone'] as $suffix) { $button += ['button_alignment'.$suffix=>$a['text_orientation'.$suffix] ?? ($a['text_orientation'] ?? 'center')]; }
            $child=(new ButtonConverter())->convert($this->raw('et_pb_button',$button));
            foreach ($child->advanced['scoped_css'] ?? [] as $index=>$rule) { $child->advanced['scoped_css'][$index]['selector']=str_replace('{{WRAPPER}}.elementor-widget:hover a.elementor-button','{{WRAPPER}} a.elementor-button:hover',$rule['selector']); }
            $this->typography_css($child,$button,'button_','.elementor-button');
            foreach (['_tablet'=>'tablet','_phone'=>'mobile','__hover'=>'desktop'] as $suffix=>$device) {
                $css='';
                foreach (['border_width'=>'border-width','border_radius'=>'border-radius'] as $property=>$target) {
                    if (!isset($button['button_'.$property.$suffix]) || $button['button_'.$property.$suffix] === '') { continue; }
                    $v=(new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->slider($button['button_'.$property.$suffix]);
                    if ($v['size']<0 || !in_array($v['unit'],['px','em','rem','%'],true)) { throw new \UnexpectedValueException('Unsupported CTA button border size.'); }
                    $css.=$target.':'.$v['size'].$v['unit'].';';
                }
                foreach (['border_color'=>'border-color','bg_color'=>'background-color'] as $property=>$target) {
                    if (isset($button['button_'.$property.$suffix]) && $button['button_'.$property.$suffix] !== '') {
                        $value=$button['button_'.$property.$suffix];
                        if (!preg_match('/^(?:#[a-f0-9]{3,8}|transparent|inherit|currentcolor|rgba?\([0-9.,%\s]+\))$/iD',$value)) { throw new \UnexpectedValueException('Unsupported CTA button surface color.'); }
                        $css.=$target.':'.$value.';';
                    }
                }
                if ($css !== '') { $child->advanced['scoped_css'][]=['selector'=>'{{WRAPPER}}.elementor-element .elementor-button'.($suffix==='__hover'?':hover':''),'css'=>$css,'device'=>$device]; }
            }
            $this->surface_css($node,$child,$a,'custom_css_promo_button','{{WRAPPER}} .elementor-button');
            $this->child($node,$child,'ace-cta-button');
            if (!isset($button['button_use_icon']) && empty($button['button_icon'])) { $node->advanced['warnings'][]='CTA default Divi button arrow is not exported as a glyph. Choose a matching native icon in Elementor if the original button uses it.'; }
        } else {
            foreach (array_keys($a) as $key) { if (strpos($key,'button_')===0 || strpos($key,'custom_css_promo_button')===0) { $node->advanced['handled_attributes'][]=$key; } }
        }
        $node->advanced['handled_attributes'][]='custom_button';
        $node->advanced['information'][]='Call to Action converted to separate editable title, body and button surfaces; module padding stays on the outer container.';
    }
}
