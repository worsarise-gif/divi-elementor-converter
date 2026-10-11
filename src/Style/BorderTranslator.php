<?php

namespace SuiteEdge\Migrator\Style;

use SuiteEdge\Migrator\Model\Node;

class BorderTranslator {

    /**
     * Translates Divi Border and Box Shadow settings into Elementor format.
     * 
     * @param Node $node The target node to apply styles to.
     * @param array $attributes The raw Divi shortcode attributes.
     */
    public function translate( Node $node, array $attributes ) {
        // Accordion item borders use a slider; retain generic outer box shadows.
        if ($node->type === 'accordion') { unset($attributes['border_radii'],$attributes['border_width_all'],$attributes['border_color_all'],$attributes['border_style_all']); }
        if ($node->type === 'button') {
            foreach (['button_border_width'=>'border_width_all','button_border_color'=>'border_color_all'] as $source=>$target) { if (isset($attributes[$source])) { $attributes[$target] = $attributes[$source]; } }
            if (isset($attributes['button_border_radius'])) { $node->content['border_radius'] = (new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->dimensions($attributes['button_border_radius']); }
            if (isset($attributes['button_border_width'])) { $node->content['border_border'] = 'solid'; $node->content['border_width'] = (new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->dimensions($attributes['button_border_width']); }
            if (isset($attributes['button_border_color'])) { $node->content['border_color'] = $attributes['button_border_color']; }
            if (isset($attributes['button_bg_color'])) { $node->content['background_color'] = $attributes['button_bg_color']; }
            if (isset($attributes['button_bg_color__hover'])) { $node->content['button_background_hover_color'] = $attributes['button_bg_color__hover']; }
        }
        
        // 1. Borders
        // Divi often stores unified borders or directional borders
        $border_radii = $this->get_attr( $attributes, 'border_radii' ); // e.g. "on|10px|10px|10px|10px"
        
        if ( $border_radii && $border_radii !== 'on||||' ) {
            $parts = explode( '|', $border_radii );
            if ( count( $parts ) >= 5 ) {
                $node->styles->set($node->type === 'image' ? 'image_border_radius' : 'border_radius',(new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->dimensions(implode('|',array_slice($parts,1,4))));
            }
        }
        foreach (['_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) {
            $value = $attributes['border_radii'.$suffix] ?? '';
            $parts = explode('|',$value);
            if (count($parts) >= 5 && implode('',array_slice($parts,1,4)) !== '') {
                $node->styles->set($node->type === 'image' ? 'image_border_radius' : 'border_radius',(new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->dimensions(implode('|',array_slice($parts,1,4))),$device);
            }
        }

        // Border Width and Color
        $border_width = $this->get_attr( $attributes, 'border_width_all' );
        if ( $border_width ) {
            $prefix = $node->type === 'image' ? 'image_border_' : 'border_';
            $node->styles->set( $prefix.'border', 'solid' );
            $node->styles->set($prefix.'width',(new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->dimensions($border_width));

            $border_color = $this->get_attr( $attributes, 'border_color_all' );
            if ( $border_color ) {
                $node->styles->set( $prefix.'color', $border_color );
            }
            
            $border_style = $this->get_attr( $attributes, 'border_style_all' );
            if ( $border_style ) {
                $node->styles->set( $prefix.'border', $border_style );
            }
        }

        // 2. Box Shadow
        $box_shadow = $this->get_attr( $attributes, 'box_shadow_style' ); // e.g. "preset1"
        if ( $box_shadow && $box_shadow !== 'none' ) {
            
            $shadow_prefix = $node->type === 'image' ? 'image_box_shadow_' : 'box_shadow_';
            $node->styles->set( $shadow_prefix.'box_shadow_type', 'yes' );
            
            $horizontal = $this->get_attr( $attributes, 'box_shadow_horizontal', '0px' );
            $vertical = $this->get_attr( $attributes, 'box_shadow_vertical', '2px' );
            $blur = $this->get_attr( $attributes, 'box_shadow_blur', '18px' );
            $spread = $this->get_attr( $attributes, 'box_shadow_spread', '0px' );
            $color = $this->get_attr( $attributes, 'box_shadow_color', 'rgba(0,0,0,0.3)' );
            $position = $this->get_attr( $attributes, 'box_shadow_position', 'outset' ); // Elementor uses ' ' or 'inset'

            $node->styles->set( $shadow_prefix.'box_shadow', [
                'horizontal' => preg_replace( '/[^0-9.-]/', '', $horizontal ),
                'vertical'   => preg_replace( '/[^0-9.-]/', '', $vertical ),
                'blur'       => preg_replace( '/[^0-9.-]/', '', $blur ),
                'spread'     => preg_replace( '/[^0-9.-]/', '', $spread ),
                'color'      => $color,
                'position'   => $position === 'inset' ? 'inset' : ' ',
            ] );
        }
        // Divi directional borders override the unified border on that side only.
        $unit = new \SuiteEdge\Migrator\Normalize\UnitNormalizer();
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile','__hover'=>'desktop'] as $suffix=>$device) {
            $css = '';
            foreach (['top','right','bottom','left'] as $side) {
                $width = 'border_width_'.$side.$suffix;
                $color = 'border_color_'.$side.$suffix;
                $style = 'border_style_'.$side.$suffix;
                if (isset($attributes[$width]) && $attributes[$width] !== '') {
                    $v = $unit->slider($attributes[$width]);
                    if ($v['size'] < 0 || !in_array($v['unit'],['px','em','rem'],true)) { throw new \UnexpectedValueException('Unsupported directional border width.'); }
                    $css .= 'border-'.$side.'-width:'.$v['size'].$v['unit'].';border-'.$side.'-style:solid;';
                    $node->advanced['handled_attributes'][] = $width;
                }
                if (isset($attributes[$style]) && $attributes[$style] !== '') {
                    if (!in_array($attributes[$style],['none','solid','dotted','dashed','double','groove','ridge','inset','outset'],true)) { throw new \UnexpectedValueException('Unsupported directional border style.'); }
                    $css .= 'border-'.$side.'-style:'.$attributes[$style].';'; $node->advanced['handled_attributes'][] = $style;
                }
                if (isset($attributes[$color]) && $attributes[$color] !== '') {
                    if (!preg_match('/^(?:#[a-f0-9]{3}|#[a-f0-9]{4}|#[a-f0-9]{6}|#[a-f0-9]{8}|(?:rgb|rgba|hsl|hsla)\([0-9.,%\s+\-]+\)|transparent|currentColor|inherit)$/iD',$attributes[$color])) { throw new \UnexpectedValueException('Unsupported directional border color.'); }
                    $css .= 'border-'.$side.'-color:'.$attributes[$color].';'; $node->advanced['handled_attributes'][] = $color;
                }
            }
            if ($css !== '') {
                $selector = '{{WRAPPER}}.elementor-element'.($suffix === '__hover' ? ':hover' : '').($node->type === 'image' ? ' img' : ($node->type === 'button' ? ' .elementor-button' : ''));
                $node->advanced['scoped_css'][] = ['selector'=>$selector,'css'=>$css,'device'=>$device];
            }
        }
    }

    private function get_attr( $attributes, $key, $default = null ) {
        return $attributes[ $key ] ?? $default;
    }
}
