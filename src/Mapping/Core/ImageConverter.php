<?php
namespace SuiteEdge\Migrator\Mapping\Core;
class ImageConverter extends \SuiteEdge\Migrator\Mapping\CommonConverter {
    protected function get_target_type() { return 'image'; }
    public function convert(array $raw_node) {
        $node = parent::convert($raw_node);
        $a = $raw_node['attributes'] ?? [];
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) {
            $key = 'force_fullwidth'.$suffix;
            if (!isset($a[$key])) { continue; }
            if ($a[$key] === '') { $node->advanced['handled_attributes'][] = $key; continue; }
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped by the report/admin consumers.
            if (!in_array($a[$key],['on','off'],true)) { throw new \UnexpectedValueException('Image full-width setting is invalid; review the source value.'); }
            $node->advanced['handled_attributes'][] = $key;
            // Divi stretches both the image wrap and image, without changing an explicit
            // module width/max-width. Native width stays editable; scoped CSS also handles
            // responsive off states, links and supplied image heights consistently.
            $target = $suffix === '_phone' ? '_mobile' : $suffix;
            if ($a[$key] === 'on') { $node->content['width'.$target] = ['unit'=>'%','size'=>100]; }
            $width = $a[$key] === 'on' ? '100%' : 'auto';
            $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}}.elementor-widget-image.elementor-widget-image img,{{WRAPPER}}.elementor-widget-image.elementor-widget-image a','css'=>'width:'.$width.';','device'=>$device];
        }
        return $node;
    }
}
