<?php
namespace SuiteEdge\Migrator\Mapping\Core;
use SuiteEdge\Migrator\Model\Node;
/** Divi 4's standalone Heading module, distinct from headings inside Text. */
class HeadingConverter extends \SuiteEdge\Migrator\Mapping\CommonConverter {
    protected function get_target_type() { return 'heading'; }
    protected function map_content(Node $node, array $raw) {
        $a = $raw['attributes']; $level = $a['title_level'] ?? 'h2';
        if (!in_array($level,['h1','h2','h3','h4','h5','h6'],true)) { throw new \UnexpectedValueException('Unsupported heading level.'); }
        if (!empty($raw['children']) || trim($raw['text_content'] ?? '') !== '') { throw new \UnexpectedValueException('Heading contains extra content requiring review.'); }
        \SuiteEdge\Migrator\Style\FontFamily::properties($a['title_font'] ?? '');
        $node->content = ['title'=>$a['title'] ?? '', 'header_size'=>$level, 'link'=>$this->link($a['link_option_url'] ?? '',$a['link_option_url_new_window'] ?? '')];
        $node->advanced['handled_attributes'] = ['title_level','link_option_url_new_window'];
        $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}} .elementor-heading-title','css'=>'margin:0;padding-bottom:10px;','device'=>'desktop'];
    }
}
