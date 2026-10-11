<?php

namespace SuiteEdge\Migrator\Model;

class Document {

    /**
     * @var string Title of the document
     */
    public $title = '';

    /**
     * @var string WordPress post type (usually 'page' or 'post')
     */
    public $post_type = 'page';

    /**
     * @var array Global page settings (e.g. page template, global colors)
     */
    public $page_settings = [];

    /**
     * @var Node[] Top-level nodes (usually sections/containers)
     */
    public $nodes = [];

    public function __construct() {
        // Initialization
    }

    public function add_node( Node $node ) {
        $this->nodes[] = $node;
    }

    public function to_array() {
        $nodes_array = [];
        foreach ( $this->nodes as $node ) {
            $nodes_array[] = $node->to_array();
        }

        return [
            'title'         => $this->title,
            'post_type'     => $this->post_type,
            'page_settings' => $this->page_settings,
            'nodes'         => $nodes_array
        ];
    }
}
