<?php
namespace SuiteEdge\Migrator\Forms;
class ElementorFormBuilder {
    public function build(array $raw) { return (new \SuiteEdge\Migrator\Mapping\Pro\ContactFormConverter())->convert($raw); }
}
