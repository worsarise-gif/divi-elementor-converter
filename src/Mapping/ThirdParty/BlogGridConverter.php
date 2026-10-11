<?php
namespace SuiteEdge\Migrator\Mapping\ThirdParty;
use SuiteEdge\Migrator\Model\Node;
use SuiteEdge\Migrator\Normalize\UnitNormalizer;
use SuiteEdge\Migrator\Style\FontFamily;

/** Explicit adapter for DiviGear's conventional post grid. Unsupported modes stay protected. */
class BlogGridConverter extends \SuiteEdge\Migrator\Mapping\BaseConverter {
    protected $force_consume_children = true;
    protected function get_target_type() { return 'ace-post-grid'; }
    public function convert(array $raw) {
        $node=new Node($raw['type'],$this->get_target_type());$this->map_content($node,$raw);
        $a=$raw['attributes'];$node->advanced['css_id']=$a['module_id']??'';
        $node->advanced['css_classes']=preg_split('/\s+/',trim($a['module_class']??''),-1,PREG_SPLIT_NO_EMPTY);
        // DiviGear background/border/typography belong to individual cards. Applying
        // the core translators to the widget wrapper changes the module's geometry.
        foreach(['Spacing','Visibility','Sizing','Position','ScopedCss'] as $name){
            $class='\\SuiteEdge\\Migrator\\Style\\'.$name.'Translator';(new $class())->translate($node,$a);
        }
        $node->source_trace=['shortcode'=>$raw['original_shortcode']??'','attributes'=>$a];$node->status='converted_native';return $node;
    }
    protected function map_content(Node $node,array $raw) {
        $a=$raw['attributes'];
        $node->advanced['source_body_weight']=(string)(int)($raw['_source_theme']['body_weight']??400);
        if ($raw['children'] || trim($raw['raw_content'])!=='') { throw new \UnexpectedValueException('Post grid: nested or mixed content requires a native template replacement.'); }
        foreach(['layout','blog_layout'] as $key){if(isset($a[$key]) && $a[$key]!=='' && $a[$key]!=='grid'){throw new \UnexpectedValueException('Post grid: only the standard grid layout is supported automatically; use a native template replacement for masonry or full-width layouts.');}$this->handled($node,$key);}
        if(($a['post_type']??'post')!=='post' || ($a['related_post']??'off')==='on' || ($a['featured_image_background']??'off')==='on') { throw new \UnexpectedValueException('Post grid: custom post types, related queries and image-background layouts require a native template replacement.'); }
        $this->handled($node,'post_type','related_post','featured_image_background');
        $categories=$a['include_categories']??'';
        if($categories!=='' && $categories!=='all'){throw new \UnexpectedValueException('Post grid: source category IDs require a configured destination category query. Choose a native template replacement with destination category slugs; numeric source IDs are never copied.');}
        $this->handled($node,'include_categories');
        $node->content=['posts_per_page'=>12,'post_offset'=>0,'post_order'=>'recent','category_slugs'=>'','columns'=>'3','columns_tablet'=>'2','columns_mobile'=>'1'];
        foreach(['posts_number'=>'posts_per_page','posts_count'=>'posts_per_page','offset_number'=>'post_offset'] as $source=>$target){if(isset($a[$source]) && $a[$source]!==''){$node->content[$target]=$a[$source];}$this->handled($node,$source);}
        if(isset($a['posts_type'])) {
            $sort=['recent'=>'recent','popular'=>'popular','random'=>'random'][$a['posts_type']]??null;
            if($sort===null){throw new \UnexpectedValueException('Post grid: unsupported posts-by-type query. Configure a native template replacement.');}
            $node->content['post_order']=$sort;$this->handled($node,'posts_type');
        }
        foreach(['columns'=>'columns','columns_tablet'=>'columns_tablet','columns_phone'=>'columns_mobile'] as $source=>$target){
            if(isset($a[$source])){if(!preg_match('/^[1-5]$/D',$a[$source])){throw new \UnexpectedValueException('Post grid: columns must be between 1 and 5.');}$node->content[$target]=$a[$source];}$this->handled($node,$source);
        }
        $switches=['show_thumbnail'=>['show_image','on'],'show_excerpt'=>['show_excerpt','on'],'use_excerpt'=>['use_excerpt','on'],'show_author'=>['show_author','on'],'show_date'=>['show_date','on'],'show_categories'=>['show_categories','on'],'show_comments'=>['show_comments','off'],'show_more'=>['show_read_more','off'],'show_pagination'=>['show_pagination','on'],'use_meta_icon'=>['meta_icons','off'],'equal_height'=>['equal_height','off'],'button_fullwidth'=>['button_fullwidth','off'],'button_at_bottom'=>['button_at_bottom','off'],'image_overlay'=>['image_overlay','off']];
        foreach($switches as $source=>$config){$v=$a[$source]??$config[1];if(!in_array($v,['on','off'],true)){throw new \UnexpectedValueException('Post grid: invalid visibility or layout switch.');}$node->content[$config[0]]=$v==='on'?'yes':'';$this->handled($node,$source);}
        $node->content['read_more_text']=$a['more_text']??'Read More';$node->content['date_format']=$a['meta_date']??'M j, Y';$node->content['excerpt_length']=$a['excerpt_length']??270;
        $this->handled($node,'more_text','meta_date','excerpt_length');
        (new \SuiteEdge\Migrator\Elementor\Posts\GridQuery())->args($node->content);
        if(!is_numeric($node->content['excerpt_length']) || $node->content['excerpt_length']<1 || $node->content['excerpt_length']>2000){throw new \UnexpectedValueException('Post grid: invalid excerpt length.');}
        $u=new UnitNormalizer();
        foreach([''=>'','_tablet'=>'_tablet','_phone'=>'_mobile'] as $source_suffix=>$target_suffix){
            if(isset($a['space_between'.$source_suffix])){$v=$u->slider($a['space_between'.$source_suffix]);if($v['size']<0 || !in_array($v['unit'],['px','em','rem','%'],true)){throw new \UnexpectedValueException('Post grid: unsupported gap.');}$node->content['grid_gap'.$target_suffix]=$v;$this->handled($node,'space_between'.$source_suffix);}
            foreach(['container','article','image','content','title','meta_default','date','author','category','text','button','pagination'] as $prefix){
                foreach(['padding','margin'] as $property){
                    $key=$prefix.'_'.$property.$source_suffix;if(!isset($a[$key])){continue;}
                    $dims=$u->dimensions($a[$key]);
                    // Empty Divi sides inherit the element's default, they are not
                    // CSS tokens. Fill each side so Elementor emits valid dimensions.
                    $defaults=$prefix==='date' && $property==='margin'?['top'=>'0','right'=>'12','bottom'=>'0','left'=>'0']:array_fill_keys(['top','right','bottom','left'],'0');
                    foreach($defaults as $side=>$default){if($dims[$side]===''){$dims[$side]=$default;}}
                    $node->content[$prefix.'_'.$property.$target_suffix]=$dims;$this->handled($node,$key);
                }
            }
            foreach(['post_item'=>'card','image'=>'image'] as $source=>$target){
                $radius='border_radii_'.$source.$source_suffix;
                if(isset($a[$radius])){
                    $parts=explode('|',$a[$radius]);if(count($parts)!==5 || !in_array($parts[0],['on','off'],true)){throw new \UnexpectedValueException('Post grid: invalid corner radii.');}
                    $v=$u->dimensions(implode('|',array_map(function($value){return $value===''?'0px':$value;},array_slice($parts,1))));
                    if(!in_array($v['unit'],['px','%'],true)){throw new \UnexpectedValueException('Post grid: unsupported radius unit.');}
                    $node->content[$target.'_radius'.$target_suffix]=$v;$this->handled($node,$radius);
                }
                foreach(['width'=>'width','color'=>'color','style'=>'border'] as $source_property=>$target_property){
                    $key='border_'.$source_property.'_all_'.$source.$source_suffix;if(!isset($a[$key])){continue;}
                    $value=$a[$key];if($source_property==='width'){$value=$u->dimensions($value);$node->content[$target.'_border_border']='solid';}
                    elseif($source_property==='color'){$value=$this->color($value);}
                    elseif(!in_array($value,['solid','double','dotted','dashed','groove','none'],true)){throw new \UnexpectedValueException('Post grid: unsupported border style.');}
                    $node->content[$target.'_border_'.$target_property.$target_suffix]=$value;$this->handled($node,$key);
                }
            }
            foreach(['title','meta','read_more','pagination','date','author','category','text'] as $prefix){$this->typography($node,$a,$prefix,$source_suffix,$target_suffix);}
        }
        foreach(['background_color'=>'card_color','button_bg_color'=>'read_more_background','pagination_background'=>'pagination_background','overlay_color'=>'overlay_color','date_background_color'=>'date_background','author_background_color'=>'author_background','category_background_color'=>'category_background','read_more_text_color__hover'=>'read_more_hover_color'] as $source=>$target){if(isset($a[$source]) && $a[$source]!==''){$node->content[$target]=$this->color($a[$source]);}$this->handled($node,$source);}
        if(isset($a['button_alignment'])){if(!in_array($a['button_alignment'],['left','center','right'],true)){throw new \UnexpectedValueException('Post grid: invalid button alignment.');}$node->content['button_alignment']=$a['button_alignment'];$this->handled($node,'button_alignment');}
        foreach(['meta_top','meta_bottom'] as $prefix){foreach(['margin','padding'] as $property){$key=$prefix.'_'.$property;if(isset($a[$key])){if(preg_match('/[0-9]/',$a[$key])){$node->advanced['information'][]='Saved '.$key.' is inactive with the default metadata position.';}$this->handled($node,$key);}}}
        if(isset($a['meta_position']) && $a['meta_position']!=='default'){throw new \UnexpectedValueException('Post grid: non-default metadata placement requires a native template replacement.');}$this->handled($node,'meta_position');
        // A hover shadow has no effect without a selected shadow preset. Do not
        // activate residual spread/blur controls left over in Divi exports.
        foreach(['box_shadow_spread_container__hover','box_shadow_blur_container__hover'] as $key){if(isset($a[$key]) && empty($a['box_shadow_style_container'])){$this->handled($node,$key);$node->advanced['information'][]='Saved '.$key.' is inactive because no card shadow preset is selected.';}}
        $node->advanced['information'][]='Post grid uses destination published posts. Omitted source query/layout values use standard grid defaults: 12 newest posts, all categories, columns 3/2/1 and older/newer pagination; review them in Elementor.';
        $node->advanced['warnings'][]='Ace Post Grid requires Ace Migrator to remain active. It replaces DiviGear rendering and does not import blog posts.';
    }
    private function typography(Node $node,array $a,$prefix,$source_suffix,$target_suffix) {
        $key=$prefix.'_font'.$source_suffix;
        if(isset($a[$key]) && $a[$key]!==''){
            $parts=explode('|',$a[$key]);
            if(($parts[1]??'')===''){$parts[1]=$node->advanced['source_body_weight'];}
            $properties=FontFamily::properties(implode('|',$parts));$node->content[$prefix.'_typography']='custom';
            foreach($properties as $prop=>$v){$node->content[$prefix.'_'.$prop.$target_suffix]=$v;}
            $this->handled($node,$key);
        }
        foreach(['font_size'=>'font_size','line_height'=>'line_height','letter_spacing'=>'letter_spacing'] as $source=>$target){
            $key=$prefix.'_'.$source.$source_suffix;if(!isset($a[$key]) || $a[$key]===''){continue;}
            $node->content[$prefix.'_typography']='custom';$node->content[$prefix.'_'.$target.$target_suffix]=(new UnitNormalizer())->slider($a[$key],$source==='line_height'?'em':'px');$this->handled($node,$key);
        }
        $key=$prefix.'_text_color'.$source_suffix;if(isset($a[$key]) && $a[$key]!==''){$node->content[$prefix.'_color'.$target_suffix]=$this->color($a[$key]);$this->handled($node,$key);}
    }
    private function color($value) {
        if(!is_string($value) || !preg_match('/^(?:#[a-f0-9]{3}|#[a-f0-9]{4}|#[a-f0-9]{6}|#[a-f0-9]{8}|transparent|inherit|currentcolor|rgba?\(\s*[0-9.%,\s]+\))$/iD',$value)){throw new \UnexpectedValueException('Post grid: unsupported color.');}
        return $value;
    }
    private function handled(Node $node,...$keys) { $node->advanced['handled_attributes']=array_merge($node->advanced['handled_attributes']??[],$keys); }
}
