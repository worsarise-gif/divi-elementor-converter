<?php
namespace SuiteEdge\Migrator\Mapping;
class MappingResult {
    public $node; public $warnings;
    public function __construct(\SuiteEdge\Migrator\Model\Node $node,array $warnings = []) { $this->node = $node; $this->warnings = $warnings; }
}
