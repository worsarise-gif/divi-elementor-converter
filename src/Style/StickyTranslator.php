<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
class StickyTranslator {
    public function translate(Node $node,array $a) {
        if (!empty($a['sticky_position']) && $a['sticky_position'] !== 'none') { throw new \UnexpectedValueException('Divi sticky behavior requires manual migration and responsive testing.'); }
    }
}
