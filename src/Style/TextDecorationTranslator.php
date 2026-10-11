<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;

/** Keep button/title decoration independent of the destination theme's generic links. */
class TextDecorationTranslator {
    public function translate(Node $node, array $a) {
        if ($node->type === 'button') {
            $font = 'button_font'; $group = 'typography_';
            $selector = '{{WRAPPER}} a.elementor-button';
        } elseif ($node->type === 'heading') {
            $font = 'title_font'; $group = 'typography_';
            $selector = '{{WRAPPER}} .elementor-heading-title';
            $this->rule($node,$selector.' a','text-decoration:inherit;');
        } elseif (in_array($node->type,['icon-box','image-box'],true)) {
            $font = 'header_font'; $group = 'title_typography_';
            $selector = '{{WRAPPER}} .elementor-'.$node->type.'-title';
            // Inherit the title's decoration, including later native editor changes.
            // Do not select description links or the image/icon link.
            $this->rule($node,$selector.' a','text-decoration:inherit;');
        } else { return; }
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) {
            if ($suffix !== '' && !isset($a[$font.$suffix])) { continue; }
            $decoration = $this->decoration($a[$font.$suffix] ?? '');
            if ($device === 'desktop') {
                $node->styles->set($group.'typography','custom');
                // Elementor's native decoration field is not responsive and cannot store two lines.
                if (strpos($decoration,' ') === false) { $node->styles->set($group.'text_decoration',$decoration); }
            }
            // Device rules must outrank the desktop native control. Default rules deliberately
            // remain below that control so choosing Underline in Elementor continues to work.
            $target = $device === 'desktop' ? $selector : str_replace('{{WRAPPER}}','{{WRAPPER}}.elementor-widget',$selector);
            // Icon-box's native typography selector also decorates its title anchor directly.
            if ($device !== 'desktop' && $node->type !== 'button') { $target .= ','.$target.' a'; }
            $this->rule($node,$target,'text-decoration:'.$decoration.';',$device);
        }
        $node->advanced['information'][] = 'Source button/title text decoration preserved; destination theme link underlines are isolated to ordinary text links.';
    }
    private function decoration($font) {
        $parts = explode('|',$font); $lines = [];
        foreach ([4=>'underline',5=>'line-through'] as $index=>$line) {
            $flag = $parts[$index] ?? '';
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            if (!in_array($flag,['','on','off'],true)) { throw new \UnexpectedValueException('Unsupported source text decoration flag; review '.$line.'.'); }
            if ($flag === 'on') { $lines[] = $line; }
        }
        return $lines ? implode(' ',$lines) : 'none';
    }
    private function rule(Node $node,$selector,$css,$device = 'desktop') {
        $node->advanced['scoped_css'][] = ['selector'=>$selector,'css'=>$css,'device'=>$device];
    }
}
