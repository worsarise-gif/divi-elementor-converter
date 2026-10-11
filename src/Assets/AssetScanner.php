<?php
namespace SuiteEdge\Migrator\Assets;
class AssetScanner {
    public function scan($content) { return (new \SuiteEdge\Migrator\Divi\Scanner())->scan($content)['assets']; }
}
