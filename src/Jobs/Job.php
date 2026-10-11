<?php

namespace SuiteEdge\Migrator\Jobs;

class Job {

    public $owner;
    public $archived = false;
    public $created;
    public $id;
    public $uuid;
    public $status = 'pending';
    public $items = []; // Array of page/file mappings
    public $current_index = 0;
    
    public $settings = [
        'output_engine' => 'classic',
        'assets' => [],
        'safety' => []
    ];
    
    public $summary = [
        'total'     => 0,
        'completed' => 0,
        'failed'    => 0,
        'warnings'  => 0
    ];

    public function __construct( $data = [] ) {
        $this->owner = get_current_user_id();
        $this->created = time();
        $this->uuid = wp_generate_uuid4();
        if ( ! empty( $data ) ) {
            $this->hydrate( $data );
        } else {
            $this->uuid = wp_generate_uuid4();
        }
    }

    public function hydrate( array $data ) {
        foreach ( $data as $key => $value ) {
            if ( property_exists( $this, $key ) ) {
                $this->$key = $value;
            }
        }
    }

    public function to_array() {
        return [
            'owner'         => $this->owner,
            'archived'      => $this->archived,
            'created'       => $this->created,
            'id'            => $this->id,
            'uuid'          => $this->uuid,
            'status'        => $this->status,
            'items'         => $this->items,
            'current_index' => $this->current_index,
            'settings'      => $this->settings,
            'summary'       => $this->summary
        ];
    }
}
