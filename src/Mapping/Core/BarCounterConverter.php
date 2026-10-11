<?php
namespace SuiteEdge\Migrator\Mapping\Core;

use SuiteEdge\Migrator\Model\Node;
use SuiteEdge\Migrator\Style\FontFamily;

/** A bar is a native Progress Bar; track and percentage styles are distinct surfaces. */
class BarCounterConverter extends \SuiteEdge\Migrator\Mapping\BaseConverter {
    protected function get_target_type() { return 'progress'; }
    public function convert(array $raw) {
        $original=$raw['attributes'];
        $raw['_bar_attributes']=$original;
        foreach (array_keys($raw['attributes']) as $key) {
            if (preg_match('/^(?:background_|use_background_|bar_|title_|percent_|use_percentages|height|border_radii)/',$key)) { unset($raw['attributes'][$key]); }
        }
        $node=parent::convert($raw); $node->source_trace['attributes']=$original;
        $remaining=array_diff_key($original,array_flip($node->advanced['handled_attributes'] ?? []));
        $unknown=(new \SuiteEdge\Migrator\Mapping\AttributeCoverage())->unknown($remaining);
        if ($unknown) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Diagnostic is escaped by report consumers.
            throw new \UnexpectedValueException('Unaccepted bar item settings: '.implode(', ',$unknown).'.');
        }
        return $node;
    }
    protected function map_content(Node $node,array $raw) {
        $this->force_consume_children=true;
        if (!empty($raw['children'])) { throw new \UnexpectedValueException('Nested modules inside a bar item require review.'); }
        $own=$raw['_bar_attributes']; $parent=$raw['_bar_parent'] ?? [];
        $a=array_merge($parent,$own);
        foreach ($own as $key=>$value) {
            if ($value === '') { unset($a[$key]); if(isset($parent[$key])){$a[$key]=$parent[$key];} }
            if (preg_match('/^custom_css_(?!main(?:_element)?$|before$|after$)/',$key) || ($key==='link_option_url' && $value!=='')) { throw new \UnexpectedValueException('Bar item links or surface CSS require manual migration.'); }
        }
        $a=array_filter($a,function($v){return $v!=='';});
        $value=$own['percent'] ?? '0';
        if (!is_string($value) || !preg_match('/^(?:\d+(?:\.\d+)?|\.\d+)$/D',$value) || !is_finite((float)$value) || (float)$value>100) { throw new \UnexpectedValueException('Bar percentage must be a finite number from 0 through 100.'); }
        $label=trim($raw['text_content'] ?? '');
        $node->content=['title'=>$label!==''?$label:'Progress','title_tag'=>'h3','title_display'=>$label!==''?'yes':'','inner_text'=>'','percent'=>['unit'=>'%','size'=>(float)$value],'display_percentage'=>'show'];
        $node->advanced['css_classes'][]='ace-bar-counter';
        $node->advanced['handled_attributes']=['percent','background_layout','text_orientation','text_orientation_tablet','text_orientation_phone'];
        $profile=$raw['_source_theme'] ?? (new \SuiteEdge\Migrator\Style\SourceThemeProfile())->recommended();
        foreach ($own as $key=>$v) {
            if (preg_match('/^(?:background_color|background_enable_color|bar_background_color|parallax|parallax_method|allow_player_pause)_default$/D',$key)) {
                $node->advanced['handled_attributes'][]=$key;
                if (in_array($key,['parallax_default','allow_player_pause_default'],true) && $v!=='off' && $v!=='') { throw new \UnexpectedValueException('Active inherited bar media/parallax state requires review.'); }
            }
        }
        foreach ($a as $key=>$v) {
            if (preg_match('/^(?:background_|use_background_)/',$key) && !preg_match('/^background_(?:color|enable_color)(?:_tablet|_phone|__hover)?$/D',$key) && substr($key,-8)!=='_default' && $v!=='' && !in_array($v,['off','none'],true)) { throw new \UnexpectedValueException('Bar track images, gradients, video, masks or patterns are not accepted yet.'); }
            if (preg_match('/^(?:background_|use_background_)/',$key) && in_array($v,['','off','none'],true)) { $node->advanced['handled_attributes'][]=$key; }
        }
        $track=$a['background_color'] ?? ($own['background_color_default'] ?? 'rgba(0,0,0,0.1)');
        $fill=$a['bar_bg_color'] ?? ($own['bar_background_color_default'] ?? $profile['accent_color']);
        $enabled=$a['background_enable_color'] ?? ($own['background_enable_color_default'] ?? 'on');
        $percent=$a['use_percentages'] ?? 'on';
        $height='22px';$effective=$a;$percent_states=[];
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile','__hover'=>'desktop'] as $suffix=>$device) {
            if($suffix!=='__hover' && $suffix!=='') { foreach($a as $key=>$v){if(substr($key,-strlen($suffix))===$suffix && $v!==''){$effective[substr($key,0,-strlen($suffix))]=$v;}} }
            $track=$a['background_color'.$suffix] ?? $track;
            $fill=$a['bar_bg_color'.$suffix] ?? $fill;
            $enabled=$a['background_enable_color'.$suffix] ?? $enabled;
            $percent=$a['use_percentages'.$suffix] ?? $percent;
            $percent_states[]=$percent;
            foreach ([$enabled,$percent] as $flag) { if(!in_array($flag,['on','off'],true)){throw new \UnexpectedValueException('Invalid bar display/background flag.');} }
            if(isset($own['height'.$suffix]) && $own['height'.$suffix]!==''){$height=$own['height'.$suffix];}
            $size=$this->size($height);
            $track_color=$this->color($enabled==='off'?'transparent':$track);
            $fill_color=$this->color($fill);
            $hover=$suffix==='__hover'?':hover':'';
            $selector='{{WRAPPER}}.elementor-widget'.$hover;
            if($suffix==='') {
                $node->content['bar_color']=$fill_color; $node->content['bar_bg_color']=$track_color;
                $node->content['display_percentage']=$percent==='on'?'show':'';
                if(in_array($size['unit'],['px','em','rem'],true)){$node->content['bar_height']=$size;}
            }
            // Do not emit inherited desktop states as hover states: hover must not erase tablet/mobile values.
            if($suffix==='__hover' && !array_filter(array_keys($a),function($key){return substr($key,-7)==='__hover';})) { continue; }
            if($suffix!=='__hover' || isset($a['background_color__hover']) || isset($a['background_enable_color__hover'])){$this->rule($node,$selector.' .elementor-progress-wrapper','background-color:'.$track_color.';',$device);}
            if($suffix!=='__hover' || isset($a['bar_bg_color__hover'])){$this->rule($node,$selector.' .elementor-progress-bar','background-color:'.$fill_color.';',$device);}
            if($suffix!=='__hover' || isset($own['height__hover'])) {
                $this->rule($node,$selector.' .elementor-progress-wrapper','height:'.$size['size'].$size['unit'].';',$device);
                $this->rule($node,$selector.' .elementor-progress-bar','height:'.$size['size'].$size['unit'].';min-width:0;',$device);
            }
            if($suffix!=='__hover' || isset($a['use_percentages__hover'])){$this->rule($node,$selector.' .elementor-progress-percentage','display:'.($percent==='on'?'block':'none').';padding:0 3%;margin-left:auto;',$device);}
            foreach (['background_color','background_enable_color','bar_bg_color','use_percentages','height'] as $key) { if(isset($a[$key.$suffix])){$node->advanced['handled_attributes'][]=$key.$suffix;} }
            if(isset($a['border_radii'.$suffix]) && $a['border_radii'.$suffix]!=='') {
                $parts=explode('|',$a['border_radii'.$suffix]);
                if(count($parts)!==5 || !in_array(array_shift($parts),['on','off'],true)){throw new \UnexpectedValueException('Invalid bar track radius.');}
                $css=[];foreach($parts as $part){$v=$this->size($part);$css[]=$v['size'].$v['unit'];}
                $this->rule($node,$selector.' .elementor-progress-wrapper','border-radius:'.implode(' ',$css).';overflow:hidden;',$device);
                $node->advanced['handled_attributes'][]='border_radii'.$suffix;
            }
            foreach(['title'=>'.elementor-title','percent'=>'.elementor-progress-percentage'] as $surface=>$target) {
                $this->typography($node,$suffix==='__hover'?$a:$effective,$profile,$surface,$suffix,$device,$selector.' '.$target);
            }
        }
        if(in_array('on',$percent_states,true)){$node->content['display_percentage']='show';}
        $node->advanced['layout_css'][]=['selector'=>'{{WRAPPER}}.elementor-widget .elementor-title','css'=>'display:block;margin:0;padding:0 0 10px;','device'=>'desktop'];
        $node->advanced['layout_css'][]=['selector'=>'{{WRAPPER}}.elementor-widget .elementor-progress-text','css'=>'display:none;','device'=>'desktop'];
        $node->advanced['layout_css'][]=['selector'=>'{{WRAPPER}}.elementor-widget .elementor-progress-bar','css'=>'transition:none;','device'=>'desktop'];
        $node->advanced['information'][]='Bar track and percentage use native Progress Bar with scoped surface styles. Native fill timing and the default 22px track geometry are adaptations; compare the original before publishing.';
    }
    private function typography(Node $node,array $a,array $profile,$surface,$suffix,$device,$selector) {
        $default=$surface==='title'?$profile['heading_font']:$profile['body_font'];
        $font=$a[$surface.'_font'.$suffix] ?? ($a[$surface.'_font'] ?? $default.'|400');
        $parts=explode('|',$font);if($parts[0]===''||strpos($parts[0],'--et_global_')===0){$parts[0]=$default;}
        $props=FontFamily::properties(implode('|',$parts)); $css=$suffix!=='__hover'||isset($a[$surface.'_font__hover'])?FontFamily::declarations($props):'';
        if($surface==='title' && $suffix==='') {
            $node->content['typography_typography']='custom';
            foreach($props as $key=>$v){if(($key==='font_weight'&&!preg_match('/^[1-9]00$/D',$v))||($key==='text_decoration'&&strpos($v,' ')!==false)){continue;}$node->content['typography_'.$key]=$v;}
        }
        foreach(['font_size'=>'font-size','letter_spacing'=>'letter-spacing','line_height'=>'line-height'] as $key=>$property) {
            if($suffix==='__hover' && !isset($a[$surface.'_'.$key.$suffix])){continue;}
            $v=$a[$surface.'_'.$key.$suffix] ?? ($a[$surface.'_'.$key] ?? ($key==='font_size'?'12px':($key==='line_height'?'1.6em':'0px')));
            $size=$this->size($v,$key==='line_height'?'em':'px',$key==='letter_spacing');
            $css.=$property.':'.$size['size'].$size['unit'].';';
            $native_units=$key==='font_size'?['px','em','rem','vw']:['px','em','rem'];
            if($surface==='title' && $suffix!=='__hover' && in_array($size['unit'],$native_units,true)){$node->styles->set('typography_typography','custom');$node->styles->set('typography_'.$key,$size,$device);}
        }
        $color=$a[$surface.'_text_color'.$suffix] ?? ($a[$surface.'_text_color'] ?? ($surface==='title'?($a['background_layout'] ?? 'light')==='dark'?'#ffffff':$profile['heading_color']:'#ffffff'));
        if($suffix!=='__hover'||isset($a[$surface.'_text_color__hover'])){$css.='color:'.$this->color($color).';';}
        $align=$a[$surface.'_text_align'.$suffix] ?? ($a['text_orientation'.$suffix] ?? ($a['text_orientation'] ?? 'left'));
        if(!in_array($align,['left','center','right','justify'],true)){throw new \UnexpectedValueException('Invalid bar text alignment.');}
        if($suffix!=='__hover'||isset($a[$surface.'_text_align__hover'])){$css.='text-align:'.$align.';';}
        if($css!==''){$this->rule($node,$selector,$css,$device);}
        foreach(['font','font_size','letter_spacing','line_height','text_color','text_align'] as $key){if(isset($a[$surface.'_'.$key.$suffix])){$node->advanced['handled_attributes'][]=$surface.'_'.$key.$suffix;}}
    }
    private function size($value,$unit='px',$negative=false) {
        $v=(new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->slider($value,$unit);
        if(!is_finite($v['size'])||!in_array($v['unit'],['px','em','rem','vw','vh','%'],true)||(!$negative&&$v['size']<0)){throw new \UnexpectedValueException('Unsupported bar surface size.');}return $v;
    }
    private function color($value) {
        if(!is_string($value)||!preg_match('/^(?:#[a-f0-9]{3}|#[a-f0-9]{4}|#[a-f0-9]{6}|#[a-f0-9]{8}|transparent|inherit|currentcolor|rgba?\([0-9.,%\s]+\))$/iD',$value)){throw new \UnexpectedValueException('Unsupported bar color.');}return $value;
    }
    private function rule(Node $node,$selector,$css,$device) { $node->advanced['scoped_css'][]=['selector'=>$selector,'css'=>$css,'device'=>$device]; }
}
