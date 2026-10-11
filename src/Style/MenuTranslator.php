<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class MenuTranslator {
    private function color($value) {
        if (!is_string($value) || !preg_match('/^(?:#[a-f0-9]{3,4}|#[a-f0-9]{6}|#[a-f0-9]{8}|transparent|(?:rgba?|hsla?)\([\d\s.,%+-]+\))$/iD',$value)) { throw new \UnexpectedValueException('Unsupported menu color.'); }
        return $value;
    }
    private function rule(Node $node,$selector,$css,$device='desktop') {
        if (preg_match('/[<>{}@]/',$css)) { throw new \UnexpectedValueException('Unsupported menu CSS or style value.'); }
        $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}} '.$selector,'css'=>$css,'device'=>$device];
    }
    public function translate(Node $node,array $a,array $theme) {
        $node->advanced['handled_attributes'] = [];
        $this->rule($node,'','flex:1 1 auto;min-width:0;width:auto;position:relative;');
        $this->rule($node,'ul.menu','display:flex;flex-wrap:wrap;align-items:center;list-style:none;margin:0;padding:0;gap:0;');
        $this->rule($node,'li','position:relative;list-style:none;margin:0;padding:0;');
        $this->rule($node,'ul.menu>li','padding:0 11px;');
        $this->rule($node,'li>a','display:block;padding:10px 0;text-decoration:none;line-height:1em;font-weight:inherit;');
        $this->rule($node,'.sub-menu','position:absolute;z-index:1000;top:100%;left:0;display:none;width:240px;padding:20px 0;margin:0;border-top:3px solid;list-style:none;box-shadow:0 2px 5px rgba(0,0,0,.1);');
        $this->rule($node,'.sub-menu .sub-menu','left:100%;top:0;');
        $this->rule($node,'.sub-menu','max-width:calc(100vw - 20px);overflow-wrap:break-word;');
        $this->rule($node,'.sub-menu>li','padding:0 20px;line-height:2em;');
        $this->rule($node,'.sub-menu a','padding:6px 20px;line-height:inherit;');
        $this->rule($node,'li:hover>.sub-menu,{{WRAPPER}} li:focus-within>.sub-menu,{{WRAPPER}} li.suiteedge-submenu-open>.sub-menu','display:block;');
        $this->rule($node,'li.suiteedge-menu-dismissed>.sub-menu','display:none;');
        $this->rule($node,'.suiteedge-menu-toggle','display:none;background:transparent;border:0;padding:0;color:inherit;font:inherit;cursor:pointer;');
        $this->rule($node,'.suiteedge-submenu-toggle','background:transparent;border:0;cursor:pointer;color:inherit;font:inherit;padding:4px;');
        $this->rule($node,'li.menu-item-has-children>a','display:inline-block;');
        // Reserve the source arrow's footprint without letting a button wrap separately.
        $this->rule($node,'ul.menu>li.menu-item-has-children,{{WRAPPER}} .sub-menu>li.menu-item-has-children','padding-right:31px;');
        $this->rule($node,'.suiteedge-submenu-toggle','position:absolute;right:7px;top:5px;width:20px;height:20px;line-height:1;font-size:0;display:inline-flex;align-items:center;justify-content:center;');
        $this->rule($node,'.suiteedge-submenu-toggle::before','content:"";width:5px;height:5px;border-right:1.5px solid currentColor;border-bottom:1.5px solid currentColor;transform:rotate(45deg);');
        $this->rule($node,'button:focus-visible,{{WRAPPER}} a:focus-visible','outline:2px solid currentColor;outline-offset:3px;');
        $unit = new \SuiteEdge\Migrator\Normalize\UnitNormalizer();
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile','__hover'=>'desktop'] as $suffix=>$device) {
            $hover = $suffix === '__hover' ? ':hover' : ''; $css = '';
            foreach (['menu_font_size'=>'font-size','menu_letter_spacing'=>'letter-spacing','menu_line_height'=>'line-height'] as $key=>$property) {
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                if (isset($a[$key.$suffix]) && $a[$key.$suffix] !== '') { $v=$unit->slider($a[$key.$suffix],$property==='line-height'?'em':'px'); if ($v['size'] < 0) { throw new \UnexpectedValueException('Invalid menu size.'); } $css.=$property.':'.$v['size'].$v['unit'].';'; $node->advanced['handled_attributes'][]=$key.$suffix; }
            }
            if (!empty($a['menu_font'.$suffix])) {
                $p=explode('|',$a['menu_font'.$suffix]);$family=$p[0];if ($family===''||strpos($family,'--et_global_')===0) {$family=$theme['body_font'];}
                $css.='font-family:'.FontFamily::css($family).';font-style:normal;text-transform:none;text-decoration:none;';
                if (($p[1]??'')==='on') {$p[1]='700';}
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                $css.='font-weight:'.FontFamily::weight($p[1] ?? '').';';
                $decoration=[];
                foreach ([2=>'font-style:italic;',3=>'text-transform:uppercase;',4=>'underline',5=>'line-through'] as $i=>$rule) {if (($p[$i]??'')==='on') {if ($i<4) {$css.=$rule;} else {$decoration[]=$rule;}} elseif (!in_array($p[$i]??'',['','off'],true)) {throw new \UnexpectedValueException('Unsupported menu font flag.');}}
                if ($decoration) {$css.='text-decoration:'.implode(' ',$decoration).';';}
                if (array_filter(array_slice($p,6),function ($flag) {return $flag!=='';})) {throw new \UnexpectedValueException('Unsupported extended menu font flags.');}
                $node->advanced['handled_attributes'][]='menu_font'.$suffix;
            }
            if ($css) {$this->rule($node,'li>a'.$hover,$css,$device);}
            foreach (['menu_text_color'=>['ul.menu>li>a'.$hover,'color'],'active_link_color'=>['ul.menu>li.current-menu-item>a'.$hover,'color'],'dropdown_menu_bg_color'=>['.sub-menu','background-color'],'dropdown_menu_line_color'=>['.sub-menu','border-color'],'dropdown_menu_text_color'=>['.sub-menu a'.$hover,'color'],'dropdown_menu_active_link_color'=>['.sub-menu .current-menu-item>a'.$hover,'color'],'menu_icon_color'=>['.suiteedge-menu-toggle'.$hover,'color']] as $key=>$rule) {
                if (!isset($a[$key.$suffix]) || $a[$key.$suffix]==='') {continue;}
                $color=$this->color($a[$key.$suffix]);
                $this->rule($node,$rule[0],$rule[1].':'.$color.';',$device);$node->advanced['handled_attributes'][]=$key.$suffix;
                if ($key==='dropdown_menu_line_color') { $this->rule($node,'ul.menu','--suiteedge-menu-line:'.$color.';',$device); }
            }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            if (isset($a['text_orientation'.$suffix])) {$align=['left'=>'flex-start','center'=>'center','right'=>'flex-end'][$a['text_orientation'.$suffix]]??null;if (!$align) {throw new \UnexpectedValueException('Unsupported menu alignment.');}$this->rule($node,'ul.menu','justify-content:'.$align.';',$device);$this->rule($node,'.suiteedge-menu-toggle','margin-left:'.($align==='flex-start'?'0':'auto').';',$device);$node->advanced['handled_attributes'][]='text_orientation'.$suffix;}
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            if (isset($a['menu_icon_font_size'.$suffix])) {$v=$unit->slider($a['menu_icon_font_size'.$suffix]);if ($v['size']<0) {throw new \UnexpectedValueException('Invalid menu icon size.');}$this->rule($node,'.suiteedge-menu-toggle','font-size:'.$v['size'].$v['unit'].';line-height:1;',$device);$node->advanced['handled_attributes'][]='menu_icon_font_size'.$suffix;}
            if (!empty($a['custom_css_dropdown_container'.$suffix])) {
                $css=str_replace('||',';',$a['custom_css_dropdown_container'.$suffix]);
                // A desktop !important width must not defeat an explicit Divi device width.
                if ($device!=='desktop') { $css=preg_replace('/(^|;)\s*width\s*:\s*([^;!]+)(?:\s*!important)?(?=;|$)/i','$1width:$2!important',$css); }
                $this->rule($node,'.suiteedge-menu-ready:not(.suiteedge-mobile-mode) .sub-menu,{{WRAPPER}} .suiteedge-menu-ready.suiteedge-mobile-mode>ul.menu',$css,$device);
                $node->advanced['handled_attributes'][]='custom_css_dropdown_container'.$suffix;
            }
            foreach (['mobile_menu_bg_color'=>'background-color','mobile_menu_text_color'=>'color'] as $key=>$property) {if (isset($a[$key.$suffix])&&$a[$key.$suffix]!=='') {$this->rule($node,$key==='mobile_menu_bg_color'?'ul.menu':'.menu a'.$hover,'--suiteedge-mobile-'.($property==='color'?'color':'background').':'.$this->color($a[$key.$suffix]).';',$device);$node->advanced['handled_attributes'][]=$key.$suffix;}}
        }
        $this->rule($node,'.suiteedge-menu-ready>ul.menu','border-color:'.$this->color($a['dropdown_menu_line_color']??$theme['accent_color']).';');
        $node->advanced['scoped_css'][]=['selector'=>'{{WRAPPER}}','css'=>'min-width:max-content;','device'=>'tablet','max_width'=>980];
        $mobile_rules = [
            '.suiteedge-menu-ready>.suiteedge-menu-toggle'=>'display:block;',
            '.suiteedge-menu-ready>ul.menu'=>'display:none;position:absolute;top:100%;left:var(--suiteedge-menu-left,0px);right:0;width:var(--suiteedge-menu-width,100%);z-index:1000;padding:10px;background:var(--suiteedge-mobile-background,#fff);border-top:3px solid var(--suiteedge-menu-line,#2BAFD8);',
            '.suiteedge-menu-ready.suiteedge-menu-open>ul.menu'=>'display:block;',
            '.menu li'=>'padding:0;',
            '.suiteedge-menu-ready .menu li.menu-item-has-children'=>'padding-right:28px;',
            '.menu a'=>'color:var(--suiteedge-mobile-color,inherit);white-space:normal;',
            '.menu .sub-menu'=>'position:static;width:auto;max-width:100%;box-shadow:none;border:0;padding:0 0 0 15px;',
            '.suiteedge-menu-ready .menu li:hover>.sub-menu,{{WRAPPER}} .suiteedge-menu-ready .menu li:focus-within>.sub-menu'=>'display:none;',
            '.suiteedge-menu-ready .menu li.suiteedge-submenu-open>.sub-menu'=>'display:block;',
        ];
        foreach ($mobile_rules as $selector=>$css) {
            $node->advanced['scoped_css'][]=['selector'=>'{{WRAPPER}} '.$selector,'css'=>$css,'device'=>'tablet','max_width'=>980];
        }
    }
}
