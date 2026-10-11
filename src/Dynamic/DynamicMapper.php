<?php

namespace SuiteEdge\Migrator\Dynamic;

class DynamicMapper {

    /**
     * Maps a decoded Divi dynamic data payload to an Elementor dynamic tag string.
     * 
     * @param array $divi_dynamic_data The decoded data from the Divi token.
     * @return array|null Returns an array with the tag string and settings, or null if unmapped.
     */
    public function map( array $divi_dynamic_data ) {
        if ( !defined('ELEMENTOR_PRO_VERSION') || empty( $divi_dynamic_data['dynamic_content'] ) ) {
            return null;
        }

        $divi_type = $divi_dynamic_data['dynamic_content'];
        $settings = $divi_dynamic_data['settings'] ?? [];

        // Example Divi types: 'post_title', 'post_excerpt', 'post_date', 'custom_meta'
        switch ( $divi_type ) {
            case 'post_title':
                return $this->build_elementor_tag( 'post-title', [] );
            
            case 'post_excerpt':
                return $this->build_elementor_tag( 'post-excerpt', [] );
            
            case 'post_date':
                return $this->build_elementor_tag( 'post-date', [ 'format' => 'default' ] );
            
            case 'post_featured_image':
                return $this->build_elementor_tag( 'post-image', [] );
            
            case 'custom_meta':
                $meta_key = $settings['custom_meta_key'] ?? '';
                return $this->build_elementor_tag( 'post-custom-field', [ 'key' => $meta_key ] );

            default:
                // Unmapped or unknown dynamic content
                return null;
        }
    }

    /**
     * Builds the string syntax that Elementor expects for dynamic tags.
     * Example: [elementor-tag id="post-title" name="post-title" settings="..."]
     */
    private function build_elementor_tag( $name, $settings = [] ) {
        $tag_string = sprintf( '[elementor-tag id="%s" name="%s"', substr(hash('sha256',$name . json_encode($settings)),0,7), $name );
        
        if ( ! empty( $settings ) ) {
            $settings_json = wp_json_encode( $settings );
            // Elementor expects settings to be urlencoded in the string
            $tag_string .= sprintf( ' settings="%s"', rawurlencode( $settings_json ) );
        }
        
        $tag_string .= ']';

        return [
            'tag' => $tag_string,
            'name' => $name
        ];
    }
}
