<?php

namespace SuiteEdge\Migrator\Mapping;

class ModuleRegistry {

    private $registry = [];

    public function __construct() {
        $this->register_defaults();
    }

    private function register_defaults() {
        // Core Modules Map
        $this->registry = [
            'et_pb_counters' => ['converter'=>'\\SuiteEdge\\Migrator\\Mapping\\Core\\BarCountersConverter','fidelity'=>'B'],
            'et_pb_counter' => ['converter'=>'\\SuiteEdge\\Migrator\\Mapping\\Core\\BarCounterConverter','fidelity'=>'B'],
            'et_pb_icon' => ['converter'=>'\\SuiteEdge\\Migrator\\Mapping\\Core\\IconConverter','fidelity'=>'B'],
            'et_pb_section' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\SectionConverter',
                'fidelity' => 'A'
            ],
            'et_pb_row' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\RowConverter',
                'fidelity' => 'A'
            ],
            'et_pb_column' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\ColumnConverter',
                'fidelity' => 'A'
            ],
            'et_pb_blurb' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\BlurbConverter',
                'fidelity' => 'A'
            ],
            'et_pb_gallery' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\GalleryConverter',
                'fidelity' => 'A'
            ],
            'et_pb_divider' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\DividerConverter',
                'fidelity' => 'A'
            ],
            'et_pb_video' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\VideoConverter',
                'fidelity' => 'A'
            ],
            'et_pb_image' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\ImageConverter',
                'fidelity' => 'A'
            ],
            'et_pb_text' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\TextConverter',
                'fidelity' => 'A'
            ],
            'et_pb_button' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\ButtonConverter',
                'fidelity' => 'A'
            ],
            'et_pb_contact_form' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Pro\\ContactFormConverter',
                'fidelity' => 'E'
            ],
            'et_pb_wc_title' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Woo\\ProductTitleConverter',
                'fidelity' => 'A'
            ],
            'et_pb_testimonial' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\TestimonialConverter',
                'fidelity' => 'A'
            ],
            'et_pb_code' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\CodeConverter',
                'fidelity' => 'A'
            ],
            'et_pb_map' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\MapConverter',
                'fidelity' => 'A'
            ],
            'et_pb_map_pin' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\MapConverter', // Eaten by parent
                'fidelity' => 'C'
            ],
            'et_pb_number_counter' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\NumberCounterConverter',
                'fidelity' => 'A'
            ],
            'et_pb_circle_counter' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\NumberCounterConverter',
                'fidelity' => 'B'
            ],
            'et_pb_social_media_follow' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\SocialMediaFollowConverter',
                'fidelity' => 'A'
            ],
            'et_pb_social_media_follow_network' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\SocialMediaFollowConverter',
                'fidelity' => 'C'
            ],
            'et_pb_tabs' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\TabsConverter',
                'fidelity' => 'A'
            ],
            'et_pb_toggle' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\ToggleConverter',
                'fidelity' => 'A'
            ],
            'et_pb_pricing_tables' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Pro\\PricingTablesConverter',
                'fidelity' => 'B'
            ],
            'et_pb_pricing_table' => [
                'converter' => '\\SuiteEdge\\Migrator\\Mapping\\Pro\\PricingTableConverter',
                'fidelity' => 'B'
            ],
        ];
        $this->registry['et_pb_row_inner'] = $this->registry['et_pb_row'];
        $this->registry['et_pb_column_inner'] = $this->registry['et_pb_column'];
        $this->registry['et_pb_accordion'] = ['converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\AccordionConverter', 'fidelity' => 'B'];
        $this->registry['et_pb_heading'] = ['converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\HeadingConverter', 'fidelity' => 'A'];
        $this->registry['et_pb_menu'] = ['converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\MenuConverter', 'fidelity' => 'B'];
        $this->registry['dgbm_blog_module'] = ['converter' => '\\SuiteEdge\\Migrator\\Mapping\\ThirdParty\\BlogGridConverter', 'fidelity' => 'B'];
        $this->registry['et_pb_blog'] = ['converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\BlogConverter', 'fidelity' => 'B'];
        $this->registry['et_pb_cta'] = ['converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\CallToActionConverter', 'fidelity' => 'B'];
        $this->registry['et_pb_team_member'] = ['converter' => '\\SuiteEdge\\Migrator\\Mapping\\Core\\PersonConverter', 'fidelity' => 'B'];
    }

    /**
     * Get target Elementor widget/container type for a Divi shortcode tag.
     * 
     * @param string $divi_tag e.g., 'et_pb_text'
     * @return array|null Returns mapping data or null if not found.
     */
    public function all() { return $this->registry; }

    public function get_mapping( $divi_tag ) {
        return isset( $this->registry[ $divi_tag ] ) ? $this->registry[ $divi_tag ] : null;
    }

    /**
     * Gets an instance of the converter for a specific tag.
     * 
     * @param string $divi_tag
     * @return \SuiteEdge\Migrator\Mapping\BaseConverter|null
     */
    public function get_converter( $divi_tag ) {
        $mapping = $this->get_mapping( $divi_tag );
        if ( $mapping && isset( $mapping['converter'] ) && class_exists( $mapping['converter'] ) ) {
            return new $mapping['converter']();
        }
        return null;
    }

    /**
     * Allows extending or overriding the default registry (e.g., via hooks).
     */
    public function register_mapping( $divi_tag, $target, $fidelity ) {
        $this->registry[ $divi_tag ] = [
            'converter' => $target,
            'fidelity' => $fidelity
        ];
    }
}
