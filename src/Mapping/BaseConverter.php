<?php
namespace SuiteEdge\Migrator\Mapping;
use SuiteEdge\Migrator\Model\Node;
abstract class BaseConverter {
    protected $force_consume_children = false;
    abstract protected function get_target_type();
    abstract protected function map_content(Node $node, array $raw_node);
    public function convert(array $raw_node) {
        $node = new Node($raw_node['type'], $this->get_target_type());
        $this->map_content($node, $raw_node);
        $a = $raw_node['attributes'] ?? [];
        $node->advanced['css_id'] = $a['module_id'] ?? '';
        $node->advanced['css_classes'] = array_values(array_unique(array_merge($node->advanced['css_classes'] ?? [],preg_split('/\s+/', trim($a['module_class'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))));
        foreach (['Spacing', 'Typography', 'Background', 'Border', 'Visibility', 'Sizing', 'Filter', 'Hover', 'Position', 'Transform', 'Sticky', 'ShapeDivider', 'ScopedCss', 'Motion', 'TextDecoration', 'ButtonIcon', 'ListTypography'] as $name) {
            $class = '\\SuiteEdge\\Migrator\\Style\\' . $name . 'Translator';
            (new $class())->translate($node, $a);
        }
        $node->source_trace = ['shortcode' => $raw_node['original_shortcode'] ?? '', 'attributes' => $a];
        $node->status = 'converted_native';
        return $node;
    }
    protected function get_attr(array $raw, $key, $default = '') { return $raw['attributes'][$key] ?? $default; }
    public function consumes_children() { return $this->force_consume_children; }
}
