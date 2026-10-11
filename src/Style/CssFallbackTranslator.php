<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class CssFallbackTranslator {
    public function translate(Node $node,array $a) {
        if (!defined('ELEMENTOR_PRO_VERSION') && (isset($a['custom_css_main']) || isset($a['custom_css_before']) || isset($a['custom_css_after']))) { throw new \UnexpectedValueException('Custom CSS requires Elementor Pro; original module preserved.'); }
    }
}
