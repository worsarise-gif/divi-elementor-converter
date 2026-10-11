<?php

namespace SuiteEdge\Migrator\Divi;

class DynamicContentParser {

    /**
     * Checks if a string contains Divi dynamic content tokens.
     * Divi tokens usually look like: @ET-DC@eyJkeW5hbWljIjp0cnVlfQ==@
     * 
     * @param string $value The raw string to check.
     * @return bool
     */
    public function is_dynamic( $value ) {
        if ( ! is_string( $value ) ) {
            return false;
        }
        return strpos( $value, '@ET-DC@' ) !== false;
    }

    /**
     * Decodes a Divi dynamic content token.
     * Divi stores dynamic settings in a base64 encoded JSON string between the @ET-DC@ tags.
     * 
     * @param string $value The raw string containing the token.
     * @return array|null Returns the decoded dynamic settings array, or null if invalid.
     */
    public function parse( $value ) {
        if ( ! $this->is_dynamic( $value ) ) {
            return null;
        }

        // Extract the base64 payload
        preg_match( '/@ET-DC@([^@]+)@/', $value, $matches );
        if ( empty( $matches[1] ) ) {
            return null;
        }

        $base64_payload = $matches[1];
        $json_payload = base64_decode( $base64_payload, true );
        
        if ( ! $json_payload ) {
            return null;
        }

        $data = json_decode( $json_payload, true );
        
        return ( json_last_error() === JSON_ERROR_NONE && is_array($data) ) ? $data : null;
    }
}
