<?php
namespace SuiteEdge\Migrator\Forms;
class EmailOptinConverter {
    public function convert(array $raw) { throw new \LogicException('Email opt-in credentials and provider actions require manual configuration. Preserve the original shortcode.'); }
}
