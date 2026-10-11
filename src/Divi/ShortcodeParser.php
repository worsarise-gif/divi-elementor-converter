<?php
namespace SuiteEdge\Migrator\Divi;
class ShortcodeParser extends ShortcodeTokenizer {
    public function parse($content) { return $this->tokenize($content); }
}
