<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;

/** Font names are preserved independently of the destination's font catalogue. */
class FontFamily {
    public static function normalize($value) {
        if (!is_string($value) || !preg_match('//u',$value)) { throw new \UnexpectedValueException('Invalid font family.'); }
        $value = trim($value);
        // Quoted CSS names may contain punctuation and non-Latin characters, but not markup or declarations.
        if ($value === '' || strlen($value) > 240 || preg_match('/[\x00-\x1f\x7f<>";{}@|\\\\]/u',$value) || strpos($value,'--et_global_') === 0) { throw new \UnexpectedValueException('Invalid or unresolved font family.'); }
        return $value;
    }
    public static function css($value) { return '"'.self::normalize($value).'"'; }
    public static function weight($value) {
        if ($value === 'on') { return '700'; }
        if ($value === '' || $value === 'off') { return '400'; }
        if (!is_string($value) || !ctype_digit($value) || (int)$value < 1 || (int)$value > 1000) { throw new \UnexpectedValueException('Unsupported font weight.'); }
        return (string)(int)$value;
    }
    public static function properties($value) {
        if (!is_string($value)) { throw new \UnexpectedValueException('Invalid font settings.'); }
        $p = explode('|',$value);
        $result = ['font_weight'=>self::weight($p[1] ?? ''),'font_style'=>'normal','text_transform'=>'none','text_decoration'=>'none'];
        if ($p[0] !== '') { $result['font_family'] = self::normalize($p[0]); }
        $lines = [];
        foreach ([2=>'font_style',3=>'text_transform',4=>'underline',5=>'line-through'] as $i=>$property) {
            if (!in_array($p[$i] ?? '',['','on','off'],true)) { throw new \UnexpectedValueException('Unsupported font style flag.'); }
            if (($p[$i] ?? '') !== 'on') { continue; }
            if ($i === 2) { $result[$property] = 'italic'; }
            elseif ($i === 3) { $result[$property] = 'uppercase'; }
            else { $lines[] = $property; }
        }
        foreach (array_slice($p,6) as $flag) { if ($flag !== '') { throw new \UnexpectedValueException('Unsupported extended font flags.'); } }
        if ($lines) { $result['text_decoration'] = implode(' ',$lines); }
        return $result;
    }
    public static function declarations(array $properties) {
        $css = '';
        foreach ($properties as $property=>$value) { $css .= str_replace('_','-',$property).':'.($property === 'font_family' ? self::css($value) : $value).';'; }
        return $css;
    }
    public static function from_css($css) {
        $families = [];
        preg_match_all('/(?:^|;)\s*font-family\s*:\s*([^;]+)(?:;|$)/i',$css,$declarations);
        foreach ($declarations[1] as $value) {
            $value = preg_replace('/\s*!important\s*$/i','',$value);
            if (preg_match('/^(?:var|env)\(/i',$value)) { continue; }
            preg_match_all('/"([^"\\\\]*)"|\x27([^\x27\\\\]*)\x27|([^,]+)/u',$value,$parts,PREG_SET_ORDER);
            foreach ($parts as $part) {
                $family = trim($part[1] !== '' ? $part[1] : (($part[2] ?? '') !== '' ? $part[2] : ($part[3] ?? '')));
                if ($family === '' || self::is_generic($family)) { continue; }
                $families[] = self::normalize($family);
            }
        }
        return array_values(array_unique($families));
    }
    public static function in_node(Node $node,$scoped_only = false) {
        $families = [];
        if (!$scoped_only) {
            foreach (array_merge($node->content,$node->styles->to_elementor()) as $key=>$value) {
                if (preg_match('/_font_family(?:_tablet|_mobile)?$/D',$key) && is_string($value) && $value !== '') { $families[] = self::normalize($value); }
            }
        }
        foreach (array_merge($node->advanced['layout_css'] ?? [],$node->advanced['scoped_css'] ?? []) as $rule) { $families = array_merge($families,self::from_css($rule['css'])); }
        return array_values(array_unique($families));
    }
    public static function is_generic($family) {
        return in_array(strtolower($family),['serif','sans-serif','monospace','cursive','fantasy','system-ui','ui-serif','ui-sans-serif','ui-monospace','ui-rounded','emoji','math','fangsong','inherit','initial','unset','revert','revert-layer'],true);
    }
}
