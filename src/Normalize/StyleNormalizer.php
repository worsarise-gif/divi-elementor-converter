<?php
namespace SuiteEdge\Migrator\Normalize;
class StyleNormalizer {
    public function normalize(\SuiteEdge\Migrator\Model\StyleBag $bag) { return $bag->to_elementor(); }
}
