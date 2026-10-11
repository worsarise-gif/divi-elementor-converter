<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class CustomCssTranslator {
    public function translate(Node $node, array $a) {
        $css = '';
        foreach (['custom_css_main'=>'selector','custom_css_before'=>'selector::before','custom_css_after'=>'selector::after'] as $key=>$selector) {
            if (!empty($a[$key])) { $css .= $selector . '{' . $a[$key] . "}\n"; }
        }
        if ($css !== '') { $node->styles->set('custom_css',$css); }
    }
}
