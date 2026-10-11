<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
/** Native button glyph/placement plus portable icon color and hover visibility. */
class ButtonIconTranslator {
    public function translate(Node $node,array $a) {
        if ($node->type !== 'button') { return; }
        $use = $a['button_use_icon'] ?? (!empty($a['button_icon']) ? 'on' : 'off');
        if (!in_array($use,['on','off',''],true)) { throw new \UnexpectedValueException('Unsupported button icon enable flag.'); }
        $keys = ['button_use_icon','button_icon','button_icon_color','button_icon_placement','button_on_hover'];
        if ($use !== 'on') {
            if (isset($a['button_use_icon'])) {
                $node->content['selected_icon'] = ['value'=>'','library'=>''];
                // Saved glyph/color/hover values do not enable an explicitly disabled icon.
                foreach (array_keys($a) as $key) { if (preg_match('/^button_(?:icon(?:_color|_placement)?|on_hover)(?:_tablet|_phone|__hover)?$/D',$key)) { $keys[] = $key; } }
                $this->handled($node,$keys);
            }
            return;
        }
        if (empty($a['button_icon'])) { throw new \UnexpectedValueException('Enabled button icon requires an exported glyph; source/global icon defaults need review.'); }
        $node->content['selected_icon'] = (new \SuiteEdge\Migrator\Mapping\IconMapper())->map($a['button_icon']);
        $placement = $a['button_icon_placement'] ?? 'right'; $hover = $a['button_on_hover'] ?? 'on';
        if (!in_array($placement,['left','right'],true) || !in_array($hover,['on','off'],true)) { throw new \UnexpectedValueException('Unsupported button icon placement or hover flag.'); }
        // Physical legacy aliases are declared by Elementor and remain valid in RTL sites.
        $node->content['icon_align'] = $placement;
        $node->content['icon_indent'] = ['unit'=>'em','size'=>0.5];
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile','__hover'=>'hover'] as $suffix=>$device) {
            if (!isset($a['button_icon_color'.$suffix]) || $a['button_icon_color'.$suffix] === '') { continue; }
            $color = $a['button_icon_color'.$suffix];
            $number = '[+\-]?(?:\d+(?:\.\d+)?|\.\d+)%?';
            $channels = $number.'\s*,\s*'.$number.'\s*,\s*'.$number;
            $pattern = '/^(?:#[a-f0-9]{3}|#[a-f0-9]{4}|#[a-f0-9]{6}|#[a-f0-9]{8}|rgb\(\s*'.$channels.'\s*\)|rgba\(\s*'.$channels.'\s*,\s*'.$number.'\s*\)|transparent|currentcolor|inherit)$/iD';
            if (!is_string($color) || !preg_match($pattern,$color)) { throw new \UnexpectedValueException('Unsupported button icon color; original source retained.'); }
            $selector = $device === 'hover' ? '{{WRAPPER}} .elementor-button:hover .elementor-button-icon,{{WRAPPER}} .elementor-button:focus-visible .elementor-button-icon' : '{{WRAPPER}} .elementor-button-icon';
            $this->rule($node,$selector,'color:'.$color.';fill:currentColor;',$device === 'hover' ? 'desktop' : $device);
            $keys[] = 'button_icon_color'.$suffix;
        }
        if ($hover === 'on') {
            $this->rule($node,'{{WRAPPER}} .elementor-button-icon','display:none;');
            $this->rule($node,'{{WRAPPER}} .elementor-button:hover .elementor-button-icon,{{WRAPPER}} .elementor-button:focus-visible .elementor-button-icon','display:inline-flex;');
        }
        $this->handled($node,$keys);
    }
    private function rule(Node $node,$selector,$css,$device = 'desktop') {
        $node->advanced['scoped_css'][] = ['selector'=>$selector,'css'=>$css,'device'=>$device];
    }
    private function handled(Node $node,array $keys) {
        $node->advanced['handled_attributes'] = array_merge($node->advanced['handled_attributes'] ?? [],$keys);
    }
}
