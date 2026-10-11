<?php

namespace SuiteEdge\Migrator\Model;

class Node {

    /**
     * @var string The normalized generic type (e.g., 'button', 'container')
     */
    public $type;

    /**
     * @var string The original Divi tag (e.g., 'et_pb_button')
     */
    public $source_type;

    /**
     * @var array Content/settings array specific to this module
     */
    public $content = [];

    /**
     * @var StyleBag Holds responsive styles and formatting
     */
    public $styles;

    /**
     * @var array Advanced settings like css_id, classes, custom_css
     */
    public $advanced = [
        'css_id'      => '',
        'css_classes' => [],
        'custom_css'  => []
    ];

    /**
     * @var Node[] Child nodes
     */
    public $children = [];

    /**
     * @var array Tracing information to debug source location
     */
    public $source_trace = [];

    /**
     * @var string Determines what happened to this node (e.g., 'converted_native', 'unsupported')
     */
    public $status = 'pending';

    public function __construct( $source_type, $type = 'unknown' ) {
        $this->source_type = $source_type;
        $this->type = $type;
        $this->styles = new StyleBag();
    }

    public function add_child( Node $node ) {
        $this->children[] = $node;
    }

    public function to_array() {
        $children_array = [];
        foreach ( $this->children as $child ) {
            $children_array[] = $child->to_array();
        }

        return [
            'type'         => $this->type,
            'source_type'  => $this->source_type,
            'content'      => $this->content,
            'styles'       => $this->styles->to_array(),
            'advanced'     => $this->advanced,
            'children'     => $children_array,
            'source_trace' => $this->source_trace,
            'status'       => $this->status
        ];
    }
}
