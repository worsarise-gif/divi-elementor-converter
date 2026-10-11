<?php
namespace SuiteEdge\Migrator\Model;
class DynamicBinding {
    public $source; public $settings;
    public function __construct($source,array $settings = []) { $this->source = $source; $this->settings = $settings; }
    public function to_array() { return ['dynamic_content'=>$this->source,'settings'=>$this->settings]; }
}
