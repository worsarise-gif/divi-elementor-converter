<?php
namespace SuiteEdge\Migrator\Elementor;
class IdGenerator {
    public function generate($index,$source) { return substr(hash('sha256','suiteedge:' . (int)$index . ':' . $source),0,7); }
}
