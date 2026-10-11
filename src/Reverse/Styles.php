<?php
namespace SuiteEdge\Migrator\Reverse;

/** Native Divi properties, with no substitution of authored values by defaults. */
class Styles {
    private $diagnostics;
    public function __construct(Diagnostics $diagnostics) { $this->diagnostics = $diagnostics; }
    public function globals(array $settings, array $kit, $path) {
        foreach (($settings['__globals__'] ?? []) as $key => $reference) {
            if ($reference === '' || $reference === null) { continue; }
            if (!is_string($reference) || !preg_match('~^globals/(colors|typography)\?id=([a-zA-Z0-9_-]+)$~D', $reference, $match)) { $this->diagnostics->add($path, 'Unresolved global style ' . $key, true); continue; }
            $records = array_merge($kit['system_' . $match[1]] ?? [], $kit['custom_' . $match[1]] ?? []);
            $found = false;
            foreach ($records as $record) {
                if (($record['_id'] ?? '') !== $match[2]) { continue; }
                if ($match[1] === 'colors') { $settings[$key] = $record['color'] ?? ''; }
                else {
                    $prefix = preg_replace('/typography$/', '', $key);
                    foreach ($record as $property => $value) { if (strpos($property, 'typography_') === 0) { $settings[$prefix . substr($property, 11)] = $value; } }
                }
                $found = true; break;
            }
            if (!$found) { $this->diagnostics->add($path, 'Global style ' . $reference . ' is missing from the source kit.', true); }
        }
        return $settings;
    }
    public function slider($value, $unit = 'px') {
        if (is_array($value)) { $unit = $value['unit'] ?? $unit; $value = $value['size'] ?? ''; }
        if ($value === '') { return ''; }
        if (!in_array($unit, ['px','%','em','rem','vh','vw'], true) || !is_numeric($value) || !is_finite((float)$value) || abs((float)$value)>100000) { throw new \UnexpectedValueException('Unsupported dimension or unit.'); }
        return (string)$value . $unit;
    }
    public function dimensions($value) {
        if (!is_array($value)) { throw new \UnexpectedValueException('Spacing must have four sides.'); }
        $parts=[];
        foreach (['top','right','bottom','left'] as $side) { $parts[] = $this->slider(['size'=>$value[$side] ?? '', 'unit'=>$value['unit'] ?? 'px']); }
        return implode('|',$parts);
    }
    public function translate(array $s, $type, array &$used, $path) {
        $a=[];
        $used['__globals__']=true;
        if (!empty($s['__dynamic__'])) { $this->diagnostics->add($path, 'Dynamic tags need an explicit Divi binding; their resolved text is not a portable mapping.', true); }
        foreach (['_element_id'=>'module_id','css_classes'=>'module_class','_css_classes'=>'module_class'] as $key=>$target) {
            if (isset($s[$key])) { if (!is_string($s[$key]) || preg_match('/[<>\[\]"\x00-\x1f]/', $s[$key])) { throw new \UnexpectedValueException('Invalid element ID or class.'); } $a[$target]=trim(($a[$target] ?? '').' '.$s[$key]); $used[$key]=true; }
        }
        foreach ([''=>'','_tablet'=>'_tablet','_mobile'=>'_phone'] as $device=>$suffix) {
            foreach (['_margin'=>'custom_margin','margin'=>'custom_margin','_padding'=>'custom_padding','padding'=>'custom_padding'] as $key=>$target) {
                if (isset($s[$key.$device])) { $a[$target.$suffix]=$this->dimensions($s[$key.$device]);$used[$key.$device]=true;if($suffix){$a[$target.'_last_edited']='on|desktop';} }
            }
            if ($type==='button' && isset($s['text_padding'.$device])) { $a['custom_button_padding'.$suffix]=$this->dimensions($s['text_padding'.$device]);$a['custom_button']='on';$used['text_padding'.$device]=true;if($suffix){$a['custom_button_padding_last_edited']='on|desktop';} }
            foreach (['width'=>'width','height'=>'height','min_height'=>'min_height','max_width'=>'max_width','_z_index'=>'z_index','z_index'=>'z_index'] as $key=>$target) {
                if (isset($s[$key.$device]) && $s[$key.$device]!=='') { if(in_array($key,['_z_index','z_index'],true)&&(!is_scalar($s[$key.$device])||!preg_match('/^-?[0-9]{1,7}$/D',(string)$s[$key.$device]))){throw new \UnexpectedValueException('Z-index must be a bounded whole number.');}$a[$target.$suffix]=in_array($key,['_z_index','z_index'],true)?(string)(int)$s[$key.$device]:$this->slider($s[$key.$device]);$used[$key.$device]=true;if($suffix){$a[$target.'_last_edited']='on|desktop';} }
            }
            if (isset($s['align'.$device])) { $a[($type==='button'?'button_alignment':($type==='heading'?'title_text_align':($type==='text-editor'?'text_orientation':'align'))).$suffix]=$s['align'.$device];$used['align'.$device]=true; }
            foreach (['background_color'=>'background_color','_background_color'=>'background_color','button_background_hover_color'=>'button_bg_color__hover','button_text_color'=>'button_text_color','title_color'=>in_array($type,['image-box','icon-box'],true)?'header_text_color':'title_text_color','description_color'=>'body_text_color','text_color'=>'text_text_color','primary_color'=>'icon_color','color'=>'icon_color','bar_color'=>'bar_bg_color','bar_bg_color'=>'background_color'] as $key=>$target) {
                if (isset($s[$key.$device]) && $s[$key.$device]!=='') { $a[($type==='button'&&$target==='background_color'?'button_bg_color':$target).$suffix]=$s[$key.$device];$used[$key.$device]=true; }
            }
            foreach (['border_radius','image_border_radius'] as $key) {
                if (isset($s[$key.$device])) { $parts=explode('|',$this->dimensions($s[$key.$device]));if($type==='button'){if(count(array_unique($parts))!==1){$this->diagnostics->add($path,'Unequal button corners require a Divi-specific surface mapping.',true);}$a['button_border_radius'.$suffix]=$parts[0];}else{$a['border_radii'.$suffix]='off|'.implode('|',$parts);}$used[$key.$device]=true; }
            }
            if (isset($s['border_width'.$device])) {
                $widths=explode('|',$this->dimensions($s['border_width'.$device]));
                if($type==='button'){if(count(array_unique($widths))!==1){$this->diagnostics->add($path,'Unequal button border widths require a Divi-specific surface mapping.',true);}$a['button_border_width'.$suffix]=$widths[0];}
                else{foreach (['top','right','bottom','left'] as $i=>$side) { $a['border_width_'.$side.$suffix]=$widths[$i]; }}
                $used['border_width'.$device]=true;
            }
            foreach (['border_border'=>'border_style_all','border_color'=>'border_color_all'] as $key=>$target) { if (isset($s[$key.$device])) { if($type==='button'&&$key==='border_border'&&!in_array($s[$key.$device],['','none','solid'],true)){$this->diagnostics->add($path,'Button border style needs a native Divi equivalent.',true);}$a[($type==='button'&&$key==='border_color'?'button_border_color':$target).$suffix]=$s[$key.$device];$used[$key.$device]=true; } }
        }
        foreach (['_background_background','background_background'] as $key) {
            if (isset($s[$key])) { if (!in_array($s[$key],['','classic'],true)) { $this->diagnostics->add($path,'Gradient/video backgrounds require a Divi-specific mapping.',true); } $used[$key]=true; }
        }
        foreach (['background_image','_background_image'] as $key) { if (!empty($s[$key]['url'])) { $a['background_image']=$s[$key]['url'];$used[$key]=true; } }
        foreach (['hide_mobile'=>'on|off|off','hide_tablet'=>'off|on|off','hide_desktop'=>'off|off|on'] as $key=>$flags) {
            if (!empty($s[$key])) { $current=explode('|',$a['disabled_on']??'off|off|off');foreach(explode('|',$flags) as $i=>$flag){if($flag==='on'){$current[$i]='on';}}$a['disabled_on']=implode('|',$current);$used[$key]=true; }
        }
        foreach (['_box_shadow_box_shadow_type','box_shadow_box_shadow_type','image_box_shadow_box_shadow_type'] as $key) { if(isset($s[$key])){$used[$key]=true;} }
        foreach (['_box_shadow_box_shadow','box_shadow_box_shadow','image_box_shadow_box_shadow'] as $key) {
            if (isset($s[$key]) && is_array($s[$key])) {
                $a['box_shadow_style']='preset1';
                foreach(['horizontal','vertical','blur','spread'] as $property){$a['box_shadow_'.$property]=$this->slider($s[$key][$property]??0);}
                $a['box_shadow_color']=$s[$key]['color']??'';$a['box_shadow_position']=($s[$key]['position']??'')==='inset'?'inset':'outset';$used[$key]=true;
            }
        }
        $groups = in_array($type,['heading','text-editor','button'],true)?['typography_'=>($type==='heading'?'title_':($type==='button'?'button_':'text_'))]:[];
        if(in_array($type,['image-box','icon-box'],true)){$groups=['title_typography_'=>'header_','description_typography_'=>'body_'];}
        if($type==='testimonial'){$groups=['content_typography_'=>'body_','name_typography_'=>'author_','job_typography_'=>'position_'];}
        if($type==='progress'){$groups=['title_typography_'=>'title_','percent_typography_'=>'percent_'];}
        foreach($groups as $prefix=>$target){$this->font($s,$prefix,$target,$a,$used,$path);}
        if($type==='button'){$a['custom_button']='on';}
        return $a;
    }
    private function font(array $s,$prefix,$target,array &$a,array &$used,$path) {
        foreach([''=>'','_tablet'=>'_tablet','_mobile'=>'_phone'] as $device=>$suffix){
            $font=[];$present=false;
            foreach(['font_family','font_weight','font_style','text_transform','text_decoration'] as $property){$key=$prefix.$property.$device;if(isset($s[$key])&&$s[$key]!==''){$font[$property]=$s[$key];$used[$key]=true;$present=true;}}
            if($present){
                $family=isset($font['font_family'])?\SuiteEdge\Migrator\Style\FontFamily::normalize($font['font_family']):'';
                $weight=\SuiteEdge\Migrator\Style\FontFamily::weight((string)($font['font_weight']??'400'));
                $transform=$font['text_transform']??'none';$style=$font['font_style']??'normal';$decoration=$font['text_decoration']??'none';
                if(!in_array($transform,['none','uppercase'],true)||!in_array($style,['normal','italic'],true)||!in_array($decoration,['none','underline','line-through','underline line-through'],true)){$this->diagnostics->add($path,'Font style/transform/decoration has no native Divi equivalent.',true);}
                $a[$target.'font'.$suffix]=implode('|',[$family,$weight,$style==='italic'?'on':'off',$transform==='uppercase'?'on':'off',strpos($decoration,'underline')!==false?'on':'off',strpos($decoration,'line-through')!==false?'on':'off']);
            }
            foreach(['font_size','line_height','letter_spacing'] as $property){$key=$prefix.$property.$device;if(isset($s[$key])){$a[$target.($target==='button_'&&$property==='font_size'?'text_size':$property).$suffix]=$this->slider($s[$key],$property==='line_height'?'em':'px');$used[$key]=true;}}
        }
        if(isset($s[$prefix.'typography'])){$used[$prefix.'typography']=true;}
    }
}
