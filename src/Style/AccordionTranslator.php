<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
/** Native shared controls, scoped per-item overrides and inherited responsive sides. */
class AccordionTranslator {
    public function supports($key) {
        return (bool)preg_match('/^(?:(?:toggle|closed_toggle|header|text|body)_(?:font|font_size|text_color|text_align|line_height|letter_spacing)|(?:open|closed)_toggle_(?:background_color|text_color)|(?:icon|open_toggle_icon)_(?:color|font_size)|(?:border_(?:width|color|style)_(?:all|top|right|bottom|left))|border_radii|custom_padding|custom_margin|background_color)(?:_tablet|_phone|__hover)?$/D',$key);
    }
    public function translate(Node $node,array $a,array $children) {
        // Divi toggles are individual boxes, not Elementor's joined borders.
        $this->rule($node,'.elementor-accordion-item','margin-bottom:5.5%;border-style:solid;');
        $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}} .elementor-accordion-item','css'=>'margin-bottom:10px;','device'=>'tablet','max_width'=>980];
        $this->rule($node,'.elementor-accordion-item:last-child','margin-bottom:0;');
        $this->rule($node,'.elementor-accordion-item+.elementor-accordion-item','border-block-start-style:solid;border-block-start-width:1px;');
        $this->rule($node,'.elementor-tab-content','border-block-start:0;');
        $this->rule($node,'.elementor-tab-title','position:relative;');
        $this->rule($node,'.elementor-tab-title .elementor-accordion-icon','position:absolute;right:20px;top:50%;transform:translateY(-50%);width:auto;');
        $this->apply($node,$a,'',true);
        foreach ($children as $i=>$child) { $this->apply($node,$child['attributes'],'.elementor-accordion-item:nth-child('.($i+1).')',false); }
    }
    private function rule(Node $node,$selector,$css,$device = 'desktop') {
        $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}} '.$selector,'css'=>$css,'device'=>$device];
    }
    private function color($value) {
        $number = '[+\-]?(?:\d+(?:\.\d+)?|\.\d+)%?'; $channels = $number.'\s*,\s*'.$number.'\s*,\s*'.$number;
        if (!is_string($value) || !preg_match('/^(?:#[a-f0-9]{3}|#[a-f0-9]{4}|#[a-f0-9]{6}|#[a-f0-9]{8}|transparent|inherit|currentcolor|(?:rgb|hsl)\(\s*'.$channels.'\s*\)|(?:rgba|hsla)\(\s*'.$channels.'\s*,\s*'.$number.'\s*\))$/iD',$value)) { throw new \UnexpectedValueException('Unsupported accordion color.'); }
        return $value;
    }
    private function font($value,$fallback) {
        $p = explode('|',$value); $result = [];
        if ($p[0] === '' || strpos($p[0],'--et_global_') === 0) { $p[0] = $fallback; }
        $p[0] = FontFamily::normalize($p[0]);
        $weight = $p[1] ?? ''; if ($weight === 'on') { $weight = '700'; } if ($weight === '' || $weight === 'off') { $weight = '400'; }
        $weight = FontFamily::weight($weight);
        $result = ['font_family'=>$p[0],'font_weight'=>$weight,'font_style'=>'normal','text_transform'=>'none','text_decoration'=>'none']; $lines = [];
        foreach ([2=>'font_style',3=>'text_transform',4=>'underline',5=>'line-through'] as $i=>$key) {
            $flag = $p[$i] ?? ''; if (!in_array($flag,['','on','off'],true)) { throw new \UnexpectedValueException('Unsupported accordion font flag.'); }
            if ($flag === 'on') { if ($i === 2) { $result[$key] = 'italic'; } elseif ($i === 3) { $result[$key] = 'uppercase'; } else { $lines[] = $key; } }
        }
        foreach (array_slice($p,6) as $flag) { if ($flag !== '') { throw new \UnexpectedValueException('Unsupported extended accordion font flag.'); } }
        if ($lines) { $result['text_decoration'] = implode(' ',$lines); }
        return $result;
    }
    private function apply(Node $node,array $a,$item,$shared) {
        // State-specific values win regardless of JSON attribute order.
        uksort($a,function ($x,$y) { $rank = function ($k) { return strpos($k,'closed_toggle_') === 0 || strpos($k,'open_toggle_') === 0 ? 2 : (strpos($k,'body_') === 0 || strpos($k,'toggle_') === 0 ? 1 : 0); }; return $rank($x) <=> $rank($y); });
        $unit = new \SuiteEdge\Migrator\Normalize\UnitNormalizer(); $box = $item ?: '.elementor-accordion-item';
        foreach ($a as $key=>$value) {
            if (!$this->supports($key)) { continue; }
            $node->advanced['handled_attributes'][] = $key;
            if ($value === '') { continue; }
            preg_match('/^(.*?)(_tablet|_phone|__hover)?$/D',$key,$m); $base = $m[1]; $suffix = $m[2] ?? '';
            $device = $suffix === '_phone' ? 'mobile' : ($suffix === '_tablet' ? 'tablet' : 'desktop');
            $hover = $suffix === '__hover' ? ':hover' : '';
            if (preg_match('/^(toggle|closed_toggle|header|text|body)_(font|font_size|text_color|text_align|line_height|letter_spacing)$/D',$base,$p)) {
                $body = in_array($p[1],['text','body'],true); $closed = $p[1] === 'closed_toggle';
                $selector = ($item ? $item.' ' : '').($body ? '.elementor-tab-content' : '.elementor-tab-title'.($closed ? ':not(.elementor-active)' : '').' .elementor-accordion-title');
                $target = $body ? 'content' : 'title'; $property = $p[2]; $css = '';
                if ($property === 'font') {
                    $values = $this->font($value,$node->content[$target.'_typography_font_family']);
                    $css = FontFamily::declarations($values);
                    if ($shared && !$closed && $suffix === '') {
                        foreach ($values as $prop=>$v) { if ($prop === 'font_weight' && !preg_match('/^[1-9]00$/D',$v)) { continue; } $node->content[$target.'_typography_'.$prop] = $prop === 'text_decoration' && strpos($v,' ') !== false ? 'underline' : $v; }
                        if (preg_match('/^[1-9]00$/D',$values['font_weight'])) { $css = strpos($values['text_decoration'],' ') !== false ? 'text-decoration:'.$values['text_decoration'].';' : ''; }
                    }
                } elseif ($property === 'text_color') {
                    $value = $this->color($value); $css = 'color:'.$value.';';
                    if ($shared && $suffix === '') { if ($body) { $node->content['content_color'] = $value; } elseif ($closed) { $node->content['title_color'] = $value; } else { $node->content['title_color'] = $value; $node->content['tab_active_color'] = $value; } $css = ''; }
                } elseif ($property === 'text_align') {
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                    if (!in_array($value,['left','center','right','justify','start','end'],true)) { throw new \UnexpectedValueException('Unsupported accordion alignment.'); } $css = 'text-align:'.$value.';display:block;';
                } else {
                    $v = $unit->slider($value,$property === 'line_height' ? 'em' : 'px');
                    if (!in_array($v['unit'],['px','em','rem','%'],true) || !is_finite($v['size']) || ($property !== 'letter_spacing' && $v['size'] < 0)) { throw new \UnexpectedValueException('Unsupported accordion typography size.'); }
                    $css = str_replace('_','-',$property).':'.$v['size'].$v['unit'].';';
                    if ($shared && !$closed && $suffix !== '__hover' && in_array($v['unit'],['px','em','%'],true)) { $node->content[$target.'_typography_'.$property.($device === 'desktop' ? '' : '_'.$device)] = $v; $css = ''; }
                }
                if ($css !== '') { $this->rule($node,$selector.$hover,$css,$device); }
            } elseif (preg_match('/^(open|closed)_toggle_(background_color|text_color)$/D',$base,$p)) {
                $active = $p[1] === 'open' ? '.elementor-active' : ':not(.elementor-active)'; $value = $this->color($value);
                if ($p[2] === 'text_color') { $this->rule($node,($item ? $item.' ' : '').'.elementor-tab-title'.$active.$hover.' .elementor-accordion-title','color:'.$value.';',$device); }
                else {
                    $this->rule($node,($item ? $item.' ' : '').'.elementor-tab-title'.$active.$hover,'background-color:'.$value.';',$device);
                    if ($p[1] === 'open') { $this->rule($node,($item ? $item.' ' : '').'.elementor-tab-content'.$hover,'background-color:'.$value.';',$device); }
                }
            } elseif (preg_match('/^(icon|open_toggle_icon)_(color|font_size)$/D',$base,$p)) {
                $selector = ($item ? $item.' ' : '').'.elementor-tab-title'.($p[1] === 'open_toggle_icon' ? '.elementor-active' : '').' .elementor-accordion-icon';
                if ($p[2] === 'color') { $this->rule($node,$selector.$hover,'color:'.$this->color($value).';',$device); $this->rule($node,$selector.$hover.' svg','fill:'.$this->color($value).';',$device); }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                else { $v = $unit->slider($value); if ($v['size'] < 0 || !in_array($v['unit'],['px','em','rem'],true)) { throw new \UnexpectedValueException('Unsupported accordion icon size.'); } $this->rule($node,$selector.$hover,'font-size:'.$v['size'].$v['unit'].';',$device); $this->rule($node,$selector.$hover.' svg','width:1em;height:1em;',$device); }
            } elseif (preg_match('/^border_(width|color|style)_(all|top|right|bottom|left)$/D',$base,$p)) {
                $property = 'border-'.($p[2] === 'all' ? '' : $p[2].'-').$p[1];
                if ($p[1] === 'color') { $value = $this->color($value); }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                elseif ($p[1] === 'width') { $v = $unit->slider($value); if ($v['size'] < 0 || !in_array($v['unit'],['px','em','rem'],true)) { throw new \UnexpectedValueException('Unsupported accordion border width.'); } $value = $v['size'].$v['unit']; }
                elseif (!in_array($value,['none','solid','dashed','dotted','double','groove','ridge','inset','outset'],true)) { throw new \UnexpectedValueException('Unsupported accordion border style.'); }
                if ($shared && $suffix === '' && $p[2] === 'all' && $p[1] !== 'style') { $node->content['border_'.$p[1]] = $p[1] === 'width' ? $v : $value; }
                $this->rule($node,$box.$hover,$property.':'.$value.';',$device);
                // Restore full top borders when Elementor's adjoining-item reset is active.
                if ($p[2] === 'all' || $p[2] === 'top') { $this->rule($node,$box.'+'.$box.$hover,'border-block-start-'.$p[1].':'.$value.';',$device); }
            } elseif ($base === 'custom_margin' || $base === 'custom_padding') {
                if ($shared) { continue; } // Parent spacing belongs to the outer widget.
                $parts = explode('|',$value); if (count($parts) === 1) { $parts = array_fill(0,4,$value); } if (count($parts) < 4 || count($parts) > 6) { throw new \UnexpectedValueException('Invalid accordion item spacing.'); }
                foreach (['top','right','bottom','left'] as $i=>$side) {
                    if ($parts[$i] === '') { continue; } $v = $unit->slider($parts[$i]);
                    if (!in_array($v['unit'],['px','em','rem','%','vh','vw'],true) || ($base === 'custom_padding' && $v['size'] < 0)) { throw new \UnexpectedValueException('Unsupported accordion item spacing.'); }
                    $value = $v['size'].$v['unit'];
                    if ($base === 'custom_margin') { $this->rule($node,$box.$hover,'margin-'.$side.':'.$value.';',$device); }
                    else {
                        $title = $box.' .elementor-tab-title';
                        $this->rule($node,$title.$hover,'padding-'.$side.':'.($side === 'right' ? 'calc('.$value.' + 50px)' : $value).';',$device);
                        if ($side === 'bottom') { $this->rule($node,$title.'.elementor-active'.$hover,'padding-bottom:0;',$device); }
                        if ($side !== 'top') { $this->rule($node,$box.' .elementor-tab-content'.$hover,'padding-'.$side.':'.$value.';',$device); }
                        if ($side === 'right') { $this->rule($node,$title.' .elementor-accordion-icon','right:'.$value.';',$device); }
                    }
                }
            } elseif ($base === 'border_radii') {
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                $parts = explode('|',$value); if (count($parts) !== 5) { throw new \UnexpectedValueException('Invalid accordion radii.'); } $values = [];
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                foreach (array_slice($parts,1) as $part) { $v = $unit->slider($part === '' ? '0' : $part); if ($v['size'] < 0 || !in_array($v['unit'],['px','em','rem','%'],true)) { throw new \UnexpectedValueException('Unsupported accordion radius.'); } $values[] = $v['size'].$v['unit']; }
                $this->rule($node,$box.$hover,'border-radius:'.implode(' ',$values).';overflow:hidden;',$device);
            } elseif ($base === 'background_color' && !$shared) {
                $value = $this->color($value); $this->rule($node,$box.' .elementor-tab-title'.$hover,'background-color:'.$value.';',$device); $this->rule($node,$box.' .elementor-tab-content'.$hover,'background-color:'.$value.';',$device);
            }
        }
    }
}
