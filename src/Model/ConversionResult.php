<?php
namespace SuiteEdge\Migrator\Model;
class ConversionResult {
    public $data; public $warnings;
    public function __construct(array $data,array $warnings = []) { $this->data = $data; $this->warnings = $warnings; }
    public function to_array() { return ['data'=>$this->data,'warnings'=>$this->warnings]; }
}
