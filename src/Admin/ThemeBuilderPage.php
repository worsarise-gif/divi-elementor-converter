<?php
namespace SuiteEdge\Migrator\Admin;
class ThemeBuilderPage {
    public function render() { (new ImportPage())->render(true); }
    public function mapping() { (new MappingPage())->render(true); }
}
