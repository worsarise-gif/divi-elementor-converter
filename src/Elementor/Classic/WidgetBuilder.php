<?php
namespace SuiteEdge\Migrator\Elementor\Classic;
class WidgetBuilder {
    public function build(\SuiteEdge\Migrator\Model\Node $node) {
        if (($node->type === 'container') === true) { throw new \InvalidArgumentException('Invalid node type.'); }
        $document = new \SuiteEdge\Migrator\Model\Document(); $document->add_node($node);
        return (new Serializer())->serialize($document)[0];
    }
}
