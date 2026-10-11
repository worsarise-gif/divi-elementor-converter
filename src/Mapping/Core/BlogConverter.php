<?php
namespace SuiteEdge\Migrator\Mapping\Core;
use SuiteEdge\Migrator\Model\Node;
use SuiteEdge\Migrator\Normalize\UnitNormalizer;
use SuiteEdge\Migrator\Style\FontFamily;

/** Core Divi Blog has its own schema/defaults; it is not a DiviGear alias. */
class BlogConverter extends \SuiteEdge\Migrator\Mapping\BaseConverter {
    protected $force_consume_children=true;
    protected function get_target_type() {return 'ace-post-grid';}
    public function convert(array $raw) {
        $n=new Node($raw['type'],$this->get_target_type());$this->map_content($n,$raw);$a=$raw['attributes'];
        $n->advanced['css_id']=$a['module_id']??'';$n->advanced['css_classes']=preg_split('/\s+/',trim($a['module_class']??''),-1,PREG_SPLIT_NO_EMPTY);
        foreach(['Spacing','Background','Visibility','Sizing','Filter','Hover','Position','Transform','Sticky','ScopedCss','Motion'] as $name) {
            $class='\\SuiteEdge\\Migrator\\Style\\'.$name.'Translator';(new $class())->translate($n,$a);
        }
        $n->source_trace=['shortcode'=>$raw['original_shortcode']??'','attributes'=>$a];$n->status='converted_native';return $n;
    }
    protected function map_content(Node $n,array $raw) {
        $a=$raw['attributes'];$theme=$raw['_source_theme']??(new \SuiteEdge\Migrator\Style\SourceThemeProfile())->recommended();
        if(!empty($raw['children'])||trim($raw['raw_content']??'')!=='') {throw new \UnexpectedValueException('Core Blog: nested module content needs review; the complete original module is protected.');}
        if(!in_array($a['box_shadow_style']??'none',['','none','off'],true)) {throw new \UnexpectedValueException('Core Blog: card shadows require a configured Elementor template replacement until their source variants are accepted.');}
        if(($a['post_type']??'post')!=='post'||($a['use_current_loop']??'off')!=='off'||!in_array($a['show_content']??'off',['off'],true)) {
            throw new \UnexpectedValueException('Core Blog: full-content, current-archive and non-post queries need a configured Elementor template replacement. The original module is protected.');
        }
        foreach(['link_option_url','link_option_url_new_window','hover_icon','hover_icon_color','custom_css_post','custom_css_title','custom_css_post_meta','custom_css_pagination','custom_css_read_more','custom_css_featured_image'] as $key) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Diagnostic key comes from the fixed allowlist; admin/report consumers escape the raw domain exception.
            if(!empty($a[$key])) {throw new \UnexpectedValueException('Core Blog: '.$key.' needs a configured Elementor template replacement; nested links or unsupported selectors must not be discarded.');}
        }
        if(($a['use_overlay']??'off')!=='off') {throw new \UnexpectedValueException('Core Blog: custom image overlay requires a configured Elementor template replacement.');}
        $full=$a['fullwidth']??'on';if(!in_array($full,['on','off'],true)) {throw new \UnexpectedValueException('Core Blog: invalid layout switch.');}
        $grid=$full==='off';$map=new \SuiteEdge\Migrator\Import\BlogCategoryMapping();$ids=$map->ids($a['include_categories']??'');$dest=[];
        foreach($ids as $id) {$term=$map->target($raw['_blog_category_map'][$id]??0);$dest[]=(string)$term->term_id;}
        $level=strtolower($a['header_level']??'h2');if(!in_array($level,['h1','h2','h3','h4','h5','h6'],true)){throw new \UnexpectedValueException('Core Blog: invalid title HTML level.');}
        $n->content=['posts_per_page'=>$a['posts_number']??10,'post_offset'=>$a['offset_number']??0,'post_order'=>'recent','category_slugs'=>'','category_ids'=>implode(',',array_unique($dest)),'layout'=>$grid?'masonry':'list','title_tag'=>$level,'columns'=>$grid?'3':'1','columns_tablet'=>$grid?'2':'1','columns_mobile'=>'1','grid_gap'=>['size'=>$grid?30:60,'unit'=>'px'],'read_more_text'=>'read more','date_format'=>$a['meta_date']??'M j, Y','excerpt_length'=>$a['excerpt_length']??270,'meta_separator'=>'yes'];
        // Divi's narrow columns select fewer masonry columns. Keep these controls editable.
        if($grid) {
            $type=$raw['_column_context']['column_type']??'4_4';
            if(in_array($type,['3_4','2_3','3_5'],true)) {$n->content['columns']='2';}
            elseif(!in_array($type,['4_4','1_1'],true)) {$n->content['columns']='1';$n->content['columns_tablet']='1';}
        }
        foreach(['show_thumbnail'=>['show_image','on'],'show_excerpt'=>['show_excerpt','on'],'use_excerpt'=>['use_excerpt','on'],'show_author'=>['show_author','on'],'show_date'=>['show_date','on'],'show_categories'=>['show_categories','on'],'show_comments'=>['show_comments','off'],'show_more'=>['show_read_more','off'],'show_pagination'=>['show_pagination','on']] as $key=>$config) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed source-control key in a domain diagnostic, escaped by admin/report consumers.
            $v=$a[$key]??$config[1];if(!in_array($v,['on','off'],true)){throw new \UnexpectedValueException('Core Blog: invalid content switch '.$key.'.');}$n->content[$config[0]]=$v==='on'?'yes':'';
        }
        (new \SuiteEdge\Migrator\Elementor\Posts\GridQuery())->args($n->content);
        if(!preg_match('/^[0-9]{1,4}$/D',(string)$n->content['excerpt_length'])||$n->content['excerpt_length']<1||$n->content['excerpt_length']>2000){throw new \UnexpectedValueException('Core Blog: excerpt length must be 1 to 2000 characters.');}
        if(!is_string($n->content['date_format'])||strlen($n->content['date_format'])>100||preg_match('/[<>\x00-\x1f]/',$n->content['date_format'])){throw new \UnexpectedValueException('Core Blog: invalid date format.');}
        $n->content['card_color']=$grid?($a['masonry_tile_background_color']??'#ffffff'):'transparent';
        $u=new UnitNormalizer();$n->content['card_border_border']=$grid?'solid':'none';$n->content['card_border_width']=$u->dimensions($grid?'1px':'0px');$n->content['card_border_color']='#d8d8d8';
        $n->content['content_padding']=$u->dimensions($grid?'19px':'0px');$n->content['image_margin']=$u->dimensions('0px|0px|20px|0px');
        $n->content['title_margin']=$u->dimensions('0px|0px|10px|0px');$n->content['meta_default_margin']=$u->dimensions('0px|0px|6px|0px');
        foreach(['header'=>'title','body'=>'text','meta'=>'meta','read_more'=>'read_more','pagination'=>'pagination'] as $source=>$target) {
            $heading=$source==='header';$n->content[$target.'_typography']='custom';$n->content[$target.'_font_family']=$theme[$heading?'heading_font':'body_font'];$n->content[$target.'_font_weight']=$heading?'500':(string)(int)$theme['body_weight'];$n->content[$target.'_color']=$theme[$heading?'heading_color':'body_color'];$n->content[$target.'_font_size']=$u->slider($heading?($grid?'18px':'26px'):$theme['body_size'].'px');$n->content[$target.'_line_height']=$u->slider($heading?'1em':'1.7em');
            foreach([''=>'','_tablet'=>'_tablet','_phone'=>'_mobile'] as $suffix=>$device) {
                $key=$source.'_font'.$suffix;
                if(isset($a[$key])&&$a[$key]!=='') {
                    $parts=explode('|',$a[$key]);if($parts[0]===''){$parts[0]=$n->content[$target.'_font_family'];}if(($parts[1]??'')===''){$parts[1]=$n->content[$target.'_font_weight'];}
                    foreach(FontFamily::properties(implode('|',$parts)) as $property=>$v){$n->content[$target.'_'.$property.$device]=$v;}
                }
                foreach(['font_size','letter_spacing','line_height'] as $property) {if(isset($a[$source.'_'.$property.$suffix])&&$a[$source.'_'.$property.$suffix]!==''){$n->content[$target.'_'.$property.$device]=$u->slider($a[$source.'_'.$property.$suffix],$property==='line_height'?'em':'px');}}
                if(isset($a[$source.'_text_color'.$suffix])&&$a[$source.'_text_color'.$suffix]!==''){$n->content[$target.'_color'.$device]=$a[$source.'_text_color'.$suffix];}
                if(isset($a[$source.'_text_align'.$suffix])&&$a[$source.'_text_align'.$suffix]!=='') {
                    $align=$a[$source.'_text_align'.$suffix];if(!in_array($align,['left','center','right','justify'],true)){throw new \UnexpectedValueException('Core Blog: invalid text alignment.');}$n->content[$target.'_alignment'.$device]=$align;
                }
            }
        }
        foreach(['read_more','pagination'] as $part){if(!isset($a[$part.'_text_color'])){$n->content[$part.'_color']=$theme['accent_color'];}}
        // Core Blog borders surround grid articles, not the entire widget.
        foreach([''=>'','_tablet'=>'_tablet','_phone'=>'_mobile'] as $suffix=>$device) {
            foreach(['width'=>'width','style'=>'border','color'=>'color'] as $property=>$target) {
                $key='border_'.$property.'_all'.$suffix;if(isset($a[$key])&&$a[$key]!==''){$n->content['card_border_'.$target.$device]=$property==='width'?$u->dimensions($a[$key]):$a[$key];}
            }
            if(!empty($a['border_radii'.$suffix])) {$parts=explode('|',$a['border_radii'.$suffix]);if(count($parts)!==5||!in_array($parts[0],['on','off'],true)){throw new \UnexpectedValueException('Core Blog: invalid card corners.');}$n->content['card_radius'.$device]=$u->dimensions(implode('|',array_map(function($v){return$v===''?'0px':$v;},array_slice($parts,1))));}
        }
        $handled=['post_type','use_current_loop','show_content','fullwidth','posts_number','offset_number','include_categories','header_level','masonry_tile_background_color','meta_date','excerpt_length','show_thumbnail','show_excerpt','use_excerpt','show_author','show_date','show_categories','show_comments','show_more','show_pagination','use_overlay','hover_enabled','sticky_enabled'];
        foreach(array_keys($a) as $key) {
            if(preg_match('/^(?:header|body|meta|read_more|pagination)_(?:font|font_size|letter_spacing|line_height|text_color|text_align)(?:_tablet|_phone)?$/D',$key)||preg_match('/^border_(?:width_all|color_all|style_all|radii)(?:_tablet|_phone)?$/D',$key)){$handled[]=$key;}
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Source setting name is raw diagnostic data, escaped at admin/report HTML consumers.
            if(preg_match('/^(?:header|body|meta|read_more|pagination)_.*__hover$/D',$key)&&$a[$key]!==''){throw new \UnexpectedValueException('Core Blog: hover typography '.$key.' needs a template replacement.');}
        }
        $n->advanced['handled_attributes']=$handled;
        foreach($n->content as $key=>$value) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Native control key is a raw domain diagnostic; HTML consumers escape it.
            if(preg_match('/_color(?:_tablet|_mobile)?$/D',$key) && (!is_string($value)||!preg_match('/^(?:#[a-f0-9]{3}|#[a-f0-9]{4}|#[a-f0-9]{6}|#[a-f0-9]{8}|transparent|inherit|currentcolor|rgba?\(\s*[0-9.%,\s]+\))$/iD',$value))) {throw new \UnexpectedValueException('Core Blog: unsupported color '.$key.'.');}
        }
        $n->advanced['information'][]='Core Blog uses destination published posts and source character-based excerpts. Omitted post count uses 10; masonry columns, gap and theme defaults remain editable for source comparison.';
        $n->advanced['warnings'][]='Ace Post Grid requires Ace Migrator to remain active. It replaces Divi Blog rendering and does not import the post database.';
    }
}
