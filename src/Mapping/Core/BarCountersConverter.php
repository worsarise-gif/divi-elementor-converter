<?php
namespace SuiteEdge\Migrator\Mapping\Core;
use SuiteEdge\Migrator\Model\Node;

/** Preserve the collection wrapper without painting its track palette on the title area. */
class BarCountersConverter extends \SuiteEdge\Migrator\Mapping\BaseConverter {
    protected function get_target_type() { return 'container'; }
    public function convert(array $raw) {
        $original=$raw['attributes'];$raw['_bars_attributes']=$original;
        foreach(array_keys($raw['attributes']) as $key){if(preg_match('/^(?:background_|use_background_|bar_|title_|percent_|use_percentages|border_radii)/',$key)){unset($raw['attributes'][$key]);}}
        $node=parent::convert($raw);$node->source_trace['attributes']=$original;return $node;
    }
    protected function map_content(Node $node,array $raw) {
        $this->force_consume_children=true;
        if(!$raw['children']){throw new \UnexpectedValueException('Empty bar collection requires review.');}
        $a=$raw['_bars_attributes'];
        foreach($a as $key=>$v){if((strpos($key,'custom_css_')===0 && !in_array($key,['custom_css_main','custom_css_main_element','custom_css_before','custom_css_after'],true))||($key==='link_option_url'&&$v!=='')){throw new \UnexpectedValueException('Bar collection links or surface CSS require manual migration.');}}
        $node->content=['content_width'=>'full','flex_direction'=>'column','padding'=>['unit'=>'px','top'=>'0','right'=>'0','bottom'=>'0','left'=>'0','isLinked'=>true],'flex_gap'=>['unit'=>'px','size'=>10,'column'=>'0','row'=>'10','isLinked'=>false]];
        $node->advanced['css_classes'][]='ace-bar-counters';
        $node->advanced['handled_attributes']=[];
        foreach($a as $key=>$value){
            if(preg_match('/^overflow-([xy])$/D',$key,$m)){
                if(!in_array($value,['visible','hidden','scroll','auto'],true)){throw new \UnexpectedValueException('Invalid bar collection overflow.');}
                $node->advanced['scoped_css'][]=['selector'=>'{{WRAPPER}}.elementor-element','css'=>'overflow-'.$m[1].':'.$value.';','device'=>'desktop'];$node->advanced['handled_attributes'][]=$key;
            }
            if($value===''&&preg_match('/^(?:title|percent)_(?:font|font_size|letter_spacing|line_height|text_color|text_align)(?:_tablet|_phone|__hover)?$/D',$key)){$node->advanced['handled_attributes'][]=$key;}
        }
        $inherited=[];foreach($a as $key=>$value){if(preg_match('/^(?:background_|use_background_|bar_|title_|percent_|use_percentages|border_radii|text_orientation|background_layout)/',$key)){$inherited[$key]=$value;}}
        foreach($raw['children'] as $child){
            if($child['type']!=='et_pb_counter'){throw new \UnexpectedValueException('Only bar items are accepted inside Bar Counters.');}
            $child['_bar_parent']=$inherited;$child['_source_theme']=$raw['_source_theme'] ?? [];
            if(!$child['_source_theme']){unset($child['_source_theme']);}
            $converted=(new BarCounterConverter())->convert($child);$node->add_child($converted);
            foreach(['handled_attributes','warnings','information'] as $key){$node->advanced[$key]=array_merge($node->advanced[$key] ?? [],$converted->advanced[$key] ?? []);}
        }
        foreach(array_diff_key($a,array_flip($node->advanced['handled_attributes'])) as $key=>$value){
            if($value!==''&&!preg_match('/(?:_last_edited|__hover_enabled)$/D',$key)&&preg_match('/^(?:bar_|title_|percent_|background_|use_background_|use_percentages|border_radii)/',$key)){
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Diagnostic is escaped by report consumers.
                throw new \UnexpectedValueException('Unaccepted bar collection surface setting: '.$key.'.');
            }
        }
        $node->advanced['information']=array_values(array_unique($node->advanced['information'] ?? []));
    }
}
