<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
/** Saved UL/OL typography must never affect paragraphs or the opposite list type. */
class ListTypographyTranslator {
    public function translate(Node $node,array $a) {
        if ($node->type !== 'text-editor') { return; }
        $unit = new \SuiteEdge\Migrator\Normalize\UnitNormalizer();
        foreach (['ul','ol'] as $tag) {
            $handled = [];
            foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile','__hover'=>'hover'] as $suffix=>$device) {
                $css = '';
                foreach (['font','text_color','font_size','letter_spacing','line_height','text_align'] as $property) {
                    $key = $tag.'_'.$property.$suffix;
                    if (!array_key_exists($key,$a)) { continue; }
                    $handled[] = $key; $value = $a[$key];
                    if ($value === '') { continue; }
                    if ($property === 'font') { $css .= $this->font($value); }
                    elseif ($property === 'text_color') {
                        $number = '[+\-]?(?:\d+(?:\.\d+)?|\.\d+)%?';
                        $channels = $number.'\s*,\s*'.$number.'\s*,\s*'.$number;
                        if (!is_string($value) || !preg_match('/^(?:#[a-f0-9]{3}|#[a-f0-9]{4}|#[a-f0-9]{6}|#[a-f0-9]{8}|transparent|inherit|currentcolor|(?:rgb|hsl)\(\s*'.$channels.'\s*\)|(?:rgba|hsla)\(\s*'.$channels.'\s*,\s*'.$number.'\s*\))$/iD',$value)) { throw new \UnexpectedValueException('Unsupported list color.'); }
                        $css .= 'color:'.$value.';';
                    } elseif ($property === 'text_align') {
                        if (!in_array($value,['left','center','right','justify','start','end'],true)) { throw new \UnexpectedValueException('Unsupported list alignment.'); }
                        $css .= 'text-align:'.$value.';';
                    } else {
                        $v = $unit->slider($value,$property === 'line_height' ? 'em' : 'px');
                        if (!in_array($v['unit'],['px','em','rem','%'],true) || !is_finite($v['size']) || ($property !== 'letter_spacing' && $v['size'] < 0)) { throw new \UnexpectedValueException('Unsupported list typography size.'); }
                        $css .= str_replace('_','-',$property).':'.$v['size'].$v['unit'].';';
                    }
                }
                if ($css !== '') {
                    $selector = '{{WRAPPER}}'.($device === 'hover' ? ':hover' : '').' '.$tag;
                    $node->advanced['scoped_css'][] = ['selector'=>$selector,'css'=>$css,'device'=>$device === 'hover' ? 'desktop' : $device];
                }
            }
            $node->advanced['handled_attributes'] = array_merge($node->advanced['handled_attributes'] ?? [],$handled);
            if ($handled) { $node->advanced['information'][] = strtoupper($tag).' typography preserved with list-only selectors; saved settings do not restyle paragraphs. Review font availability visually.'; }
        }
    }
    private function font($value) {
        $p = explode('|',$value); $css = '';
        if ($p[0] !== '') {
            $css .= 'font-family:'.FontFamily::css($p[0]).';';
        }
        $weight = $p[1] ?? '';
        if ($weight !== '' && $weight !== 'off') {
            if ($weight === 'on') { $weight = '700'; }
            $css .= 'font-weight:'.FontFamily::weight($weight).';';
        } else { $css .= 'font-weight:400;'; }
        $lines = [];
        foreach ([2=>'font-style',3=>'text-transform',4=>'underline',5=>'line-through'] as $i=>$property) {
            $flag = $p[$i] ?? '';
            if (!in_array($flag,['','on','off'],true)) { throw new \UnexpectedValueException('Unsupported list font flag.'); }
            if ($flag !== 'on') {
                if ($i === 2) { $css .= 'font-style:normal;'; }
                if ($i === 3) { $css .= 'text-transform:none;'; }
                continue;
            }
            if ($i === 2) { $css .= 'font-style:italic;'; }
            elseif ($i === 3) { $css .= 'text-transform:uppercase;'; }
            else { $lines[] = $property; }
        }
        foreach (array_slice($p,6) as $flag) { if ($flag !== '') { throw new \UnexpectedValueException('Unsupported extended list font flag.'); } }
        $css .= 'text-decoration:'.($lines ? implode(' ',$lines) : 'none').';';
        return $css;
    }
}
