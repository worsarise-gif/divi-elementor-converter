<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class VisibilityTranslator {
    public function translate(Node $node, array $a) {
        $disabled = explode('|',$a['disabled_on'] ?? '');
        if (($a['disabled'] ?? '') === 'on') { $disabled = ['on','on','on']; }
        foreach (['hide_mobile','hide_tablet','hide_desktop'] as $i=>$key) { if (($disabled[$i] ?? '') === 'on') { $node->styles->set($key,'hidden-' . substr($key,5)); } }
    }
}
