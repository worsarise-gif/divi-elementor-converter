<?php
namespace SuiteEdge\Migrator\Reports;
class AssetReport {
    public function build($content) { return ['image_urls'=>(new \SuiteEdge\Migrator\Assets\AssetScanner())->scan($content),'availability'=>'Not fetched during analysis']; }
}
