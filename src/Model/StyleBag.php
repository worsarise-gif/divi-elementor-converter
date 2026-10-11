<?php

namespace SuiteEdge\Migrator\Model;

class StyleBag {

    private $styles = [
        'desktop' => [
            'default' => [],
            'hover'   => []
        ],
        'tablet'  => [
            'default' => [],
            'hover'   => []
        ],
        'mobile'  => [
            'default' => [],
            'hover'   => []
        ]
    ];

    public function __construct() {
        // Initialization
    }

    /**
     * Sets a style property for a given breakpoint and state.
     */
    public function set( $property, $value, $breakpoint = 'desktop', $state = 'default' ) {
        if ( ! isset( $this->styles[ $breakpoint ][ $state ] ) ) {
            return;
        }

        $this->styles[ $breakpoint ][ $state ][ $property ] = $value;
    }

    /**
     * Gets a style property.
     */
    public function get( $property, $breakpoint = 'desktop', $state = 'default' ) {
        return $this->styles[ $breakpoint ][ $state ][ $property ] ?? null;
    }

    /**
     * Returns the full array representation of the styles.
     */
    public function to_elementor() {
        $settings = [];
        foreach ($this->styles as $breakpoint => $states) {
            foreach ($states as $state => $values) {
                foreach ($values as $key => $value) {
                    $suffix = $breakpoint === 'desktop' ? '' : '_' . $breakpoint;
                    $settings[$key . ($state === 'hover' ? '_hover' : '') . $suffix] = $value;
                }
            }
        }
        return $settings;
    }
    public function to_array() {
        return $this->styles;
    }
}
