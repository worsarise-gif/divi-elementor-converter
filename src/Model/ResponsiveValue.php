<?php
namespace SuiteEdge\Migrator\Model;
class ResponsiveValue {
    private $values;
    public function __construct(array $values = []) { $this->values = array_intersect_key($values,array_flip(['desktop','tablet','mobile'])); }
    public function get($device) { return $this->values[$device] ?? $this->values['desktop'] ?? null; }
    public function to_array() { return $this->values; }
}
