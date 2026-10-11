<?php

namespace SuiteEdge\Migrator\Style;

use SuiteEdge\Migrator\Model\Node;
use SuiteEdge\Migrator\Assets\AssetResolver;

class BackgroundTranslator {

    /**
     * Translates Divi Background settings into Elementor format.
     * 
     * @param Node $node The target node to apply styles to.
     * @param array $attributes The raw Divi shortcode attributes.
     */
    public function translate( Node $node, array $attributes ) {
        
        $has_background = false;

        // 1. Solid Color
        $bg_color = ($attributes['background_enable_color'] ?? '') === 'off' ? null : $this->get_attr( $attributes, 'background_color' );
        if ( $bg_color ) {
            $has_background = true;
            $node->styles->set( 'background_background', 'classic' );
            $node->styles->set( 'background_color', $bg_color );
        }

        // 2. Background Image
        $bg_image = ($attributes['background_enable_image'] ?? '') === 'off' ? null : $this->get_attr( $attributes, 'background_image' );
        if ( $bg_image ) {
            $has_background = true;
            $node->styles->set( 'background_background', 'classic' );
            
            $resolved_image = [ 'url' => $bg_image, 'id' => '' ];

            $node->styles->set( 'background_image', $resolved_image );

            // Image properties
            $bg_size = $this->get_attr( $attributes, 'background_size', 'cover' );
            $node->styles->set( 'background_size', $bg_size );

            $bg_position = str_replace('_',' ',$this->get_attr( $attributes, 'background_position', 'center center' ));
            $node->styles->set( 'background_position', $bg_position );
            
            $bg_repeat = $this->get_attr( $attributes, 'background_repeat', 'no-repeat' );
            $node->styles->set( 'background_repeat', $bg_repeat );
            if ($bg_size === 'custom' && !empty($attributes['background_image_width'])) { $node->styles->set('background_bg_width',(new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->slider($attributes['background_image_width'])); }
        }
        foreach (['_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) {
            $key = 'background_enable_image'.$suffix;
            if (isset($attributes[$key]) && $attributes[$key] === 'off') {
                if ($bg_image && (($attributes['use_background_color_gradient'] ?? 'off') !== 'on' || ($attributes['background_color_gradient_overlays_image'] ?? '') === 'on')) {
                    $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}}.elementor-element','css'=>'background-image:none;','device'=>$device];
                }
                $node->advanced['handled_attributes'][] = $key;
            }
        }

        // 3. Background Gradient
        $use_gradient = $this->get_attr( $attributes, 'use_background_color_gradient', 'off' );
        if ( $use_gradient === 'on' ) {
            $has_background = true;
            $base_color = $node->styles->get('background_color');
            $overlay = $bg_image && ($attributes['background_color_gradient_overlays_image'] ?? '') === 'on';
            $prefix = $overlay ? 'background_overlay_' : 'background_';
            $node->styles->set( $prefix.'background', 'gradient' );
            if ($overlay) { $node->styles->set('background_overlay_opacity',['unit'=>'px','size'=>1]); }
            
            $color_1 = $this->get_attr( $attributes, 'background_color_gradient_start', '#2b87da' );
            $color_2 = $this->get_attr( $attributes, 'background_color_gradient_end', '#29c4a9' );
            $type = $this->get_attr( $attributes, 'background_color_gradient_type', 'linear' );
            if (!empty($attributes['background_color_gradient_stops'])) {
                $stops = explode('|',$attributes['background_color_gradient_stops']);
                if (count($stops) !== 2 || !preg_match('/^(.+)\s+(-?\d+(?:\.\d+)?)%$/',$stops[0],$first) || !preg_match('/^(.+)\s+(-?\d+(?:\.\d+)?)%$/',$stops[1],$second)) { throw new \UnexpectedValueException('Gradient requires a multi-stop adapter.'); }
                $color_1 = $first[1]; $color_2 = $second[1];
                $node->styles->set($prefix.'color_stop',['unit'=>'%','size'=>(float)$first[2]]);
                $node->styles->set($prefix.'color_b_stop',['unit'=>'%','size'=>(float)$second[2]]);
            }
            
            $node->styles->set( $prefix.'color', $color_1 );
            $node->styles->set( $prefix.'color_b', $color_2 );
            $node->styles->set( $prefix.'gradient_type', $type );

            if ( $type === 'linear' ) {
                $direction = $this->get_attr( $attributes, 'background_color_gradient_direction', '180deg' );
                $direction = (new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->slider($direction,'deg');
                $node->styles->set( $prefix.'gradient_angle', $direction );
            }
            if (!$overlay && $base_color !== null) {
                // Elementor uses background_color for both the first gradient stop and
                // the solid surface. Divi keeps those independent, including alpha stops.
                // Retain the source solid surface underneath the native gradient image.
                $this->gradient_surface($node,$base_color);
                if (isset($attributes['background_color__hover'])) { $this->gradient_surface($node,$attributes['background_color__hover'],':hover'); }
            }
        }

        // 4. Background Video
        $bg_video = ($attributes['background_enable_video_mp4'] ?? '') === 'off' ? null : $this->get_attr( $attributes, 'background_video_mp4' );
        if ( $bg_video ) {
            $has_background = true;
            $node->styles->set( 'background_background', 'video' );
            $node->styles->set( 'background_video_link', $bg_video );
        }
    }

    private function gradient_surface(Node $node,$color,$state = '') {
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain validation message is escaped by report/admin consumers.
        if (!is_string($color) || !preg_match('/^(?:#(?:[a-f0-9]{3}|[a-f0-9]{4}|[a-f0-9]{6}|[a-f0-9]{8})|[a-z]+|(?:rgb|rgba|hsl|hsla)\\([0-9.,% +\\/\-]+\\)|var\\(--[a-z0-9_-]+\\))$/iD',$color)) { throw new \UnexpectedValueException('Unsupported gradient base color; review its source value.'); }
        $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}}.elementor-element.elementor-element'.$state,'css'=>'background-color:'.$color.';','device'=>'desktop'];
    }
    private function get_attr( $attributes, $key, $default = null ) {
        return $attributes[ $key ] ?? $default;
    }
}
