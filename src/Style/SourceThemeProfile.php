<?php
namespace SuiteEdge\Migrator\Style;
/** Explicit source theme defaults: layout exports do not include Divi's Theme Customizer. */
class SourceThemeProfile {
    public function recommended($origin = '',$context = 'page') {
        $profile = ['row_max_width'=>1080,'body_font'=>'Open Sans','heading_font'=>'Open Sans','body_size'=>14,'body_weight'=>500,'body_color'=>'#666666','heading_color'=>'#333333','accent_color'=>'#2ea3f2'];
        // Verified against the public reference Home on 2026-10-06. Editable in mapping.
        // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Pure parser fallback before WordPress loads; WordPress runtime uses wp_parse_url.
        $host = strtolower((string)(function_exists('wp_parse_url') ? wp_parse_url($origin,PHP_URL_HOST) : parse_url($origin,PHP_URL_HOST)));
        if (in_array($host,['suiteedge.com','www.suiteedge.com'],true)) {
            // Home declares Montserrat locally. The standalone FAQ heading inherits Inter.
            $profile = ['row_max_width'=>1280,'body_font'=>'Arial','heading_font'=>'Inter','body_size'=>18,'body_weight'=>500,'body_color'=>'#222222','heading_color'=>'#333333','accent_color'=>'#2BAFD8'];
            // Theme Builder navigation declares inherited Inter in the public Header CSS (2026-10-07).
            if ($context === 'theme-builder') { $profile['body_font']='Inter'; }
        }
        return $profile;
    }
    public function validate($profile) {
        if (!is_array($profile) || array_diff(array_keys($profile),array_keys($this->recommended()))) { throw new \InvalidArgumentException('Invalid source theme defaults.'); }
        $profile = array_merge($this->recommended(),$profile);
        foreach (['row_max_width'=>[320,3840],'body_size'=>[8,100],'body_weight'=>[1,1000]] as $key=>$bounds) {
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            if (!is_scalar($profile[$key]) || !is_numeric($profile[$key]) || !is_finite((float)$profile[$key]) || $profile[$key] < $bounds[0] || $profile[$key] > $bounds[1]) { throw new \InvalidArgumentException('Invalid source theme size: '.$key); }
            $profile[$key] = (float)$profile[$key];
        }
        if (floor($profile['body_weight']) !== $profile['body_weight']) { throw new \InvalidArgumentException('Source font weight must be a whole number.'); }
        foreach (['body_font','heading_font'] as $key) {
            try { $profile[$key] = FontFamily::normalize($profile[$key]); }
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Previous exception is validation context, not HTML output; admin/JSON consumers escape diagnostics.
            catch (\UnexpectedValueException $e) { throw new \InvalidArgumentException('Invalid source font family.',0,$e); }
        }
        foreach (['body_color','heading_color','accent_color'] as $key) { if (!is_string($profile[$key]) || !preg_match('/^#[a-f0-9]{6}$/iD',$profile[$key])) { throw new \InvalidArgumentException('Source theme colors must use six-digit hex.'); } }
        return $profile;
    }
    public function attributes($type,array $a,array $profile) {
        if (in_array($type,['et_pb_row','et_pb_row_inner'],true) && empty($a['max_width'])) { $a['max_width'] = $profile['row_max_width'].'px'; }
        if ($type === 'et_pb_icon' && !isset($a['icon_color'])) { $a['icon_color'] = $profile['accent_color']; }
        if ($type === 'et_pb_heading') {
            $parts = explode('|',$a['title_font'] ?? '');
            if ($parts[0] === '' || strpos($parts[0],'--et_global_') === 0) { $parts[0] = $profile['heading_font']; }
            if (empty($parts[1]) || $parts[1] === 'off') { $parts[1] = '500'; }
            $a['title_font'] = implode('|',$parts);
            $a += ['title_text_color'=>$profile['heading_color'],'title_line_height'=>'1em'];
            $sizes = ['h1'=>30,'h2'=>26,'h3'=>22,'h4'=>18,'h5'=>16,'h6'=>14];
            if (isset($sizes[$a['title_level'] ?? 'h2'])) { $a += ['title_font_size'=>$sizes[$a['title_level'] ?? 'h2'].'px']; }
        }
        if (in_array($type,['et_pb_text','et_pb_blurb'],true)) {
            // Divi text modules inherit the body's computed line height. A local
            // font-size change does not recompute that inherited pixel value.
            $a += ['text_font_size'=>$profile['body_size'].'px','text_text_color'=>$profile['body_color']];
            if (!isset($a['text_line_height']) || $a['text_line_height'] === '') { $a['text_line_height'] = ($profile['body_size']*1.7).'px'; }
            if ($type === 'et_pb_blurb' && empty($a['body_font'])) { $a['body_font'] = $a['text_font'] ?? ''; }
            foreach (['text_font'=>'body_font','body_font'=>'body_font','header_font'=>'heading_font','header_2_font'=>'heading_font','header_3_font'=>'heading_font','header_4_font'=>'heading_font','header_5_font'=>'heading_font','header_6_font'=>'heading_font'] as $key=>$family) {
                if ($key === 'body_font' && $type !== 'et_pb_blurb') { continue; }
                if ($family === 'heading_font' && !isset($a[$key])) { continue; }
                $parts = explode('|',$a[$key] ?? '');
                if ($parts[0] === '' || strpos($parts[0],'--et_global_') === 0) { $parts[0] = $profile[$family]; }
                if ($family === 'body_font' && ($parts[1] ?? '') === '') { $parts[1] = (string)(int)$profile['body_weight']; }
                $a[$key] = implode('|',$parts);
            }
            foreach (['text_font','body_font'] as $base) {
                if ($base === 'body_font' && $type !== 'et_pb_blurb') { continue; }
                $desktop = explode('|',$a[$base] ?? '');
                foreach (['_tablet','_phone','__hover'] as $suffix) {
                    if (empty($a[$base.$suffix])) { continue; }
                    $parts = explode('|',$a[$base.$suffix]);
                    if (($parts[1] ?? '') === '') { $parts[1] = $desktop[1] ?? (string)(int)$profile['body_weight']; }
                    $a[$base.$suffix] = implode('|',$parts);
                }
            }
            if ($type === 'et_pb_text') {
                foreach (['ul','ol'] as $tag) { foreach (['','_tablet','_phone','__hover'] as $suffix) {
                    $key = $tag.'_font'.$suffix;
                    if (!isset($a[$key]) || $a[$key] === '') { continue; }
                    $parts = explode('|',$a[$key]);
                    if ($parts[0] === '' || strpos($parts[0],'--et_global_') === 0) { $parts[0] = $profile['body_font']; }
                    $a[$key] = implode('|',$parts);
                } }
            }
        }
        if ($type === 'et_pb_menu' && empty($a['menu_font'])) { $a['menu_font'] = $profile['body_font']; }
        // Global font references are Theme Customizer choices, not downloadable font names.
        // Resolve every device/state before translating, including menu and button controls.
        foreach ($a as $key=>$value) {
            if (!preg_match('/^(?:menu|button|title|text|header(?:_[2-6])?|body|toggle|closed_toggle|ul|ol|meta|read_more|pagination|date|author|category)_font(?:_tablet|_phone|__hover)?$/D',$key) || !is_string($value)) { continue; }
            $parts = explode('|',$value);
            if (strpos($parts[0],'--et_global_') !== 0) { continue; }
            $heading = strpos($parts[0],'header') !== false || strpos($parts[0],'heading') !== false;
            $parts[0] = $profile[$heading ? 'heading_font' : 'body_font'];
            $a[$key] = implode('|',$parts);
        }
        return $a;
    }
}
