<?php
namespace SuiteEdge\Migrator\Reports;
class ModuleCoverageReport {
    public function build($content) { return (new \SuiteEdge\Migrator\Divi\Scanner())->scan($content); }
}
