<?php
namespace SuiteEdge\Migrator\Mapping\Core;
use SuiteEdge\Migrator\Model\Node;

/** Dedicated Person content never becomes a testimonial or a flattened HTML card. */
class PersonConverter extends BusinessComposition {
    protected function map_content(Node $node, array $raw) {
        $this->start($node,$raw,'0'); $a=$raw['attributes'];
        $context=$raw['_column_context'] ?? [];
        $column=$context['column_type'] ?? '4_4';
        $narrow_portrait=in_array($column,['1_2','1_3','1_4','1_5','1_6','2_5','3_5','3_8'],true);
        $stacked=$narrow_portrait||$column==='3_4';
        $four_column_quarter=$column==='1_4'&&($context['row_columns'] ?? 0)===4;
        $node->content['flex_direction']=$stacked?'column':'row';
        $node->content['flex_direction_tablet']=$four_column_quarter?'column':'row';
        $node->content['flex_direction_mobile']='column';
        $node->content['flex_align_items']='flex-start';
        $node->advanced['layout_css'][]=['selector'=>'{{WRAPPER}}','css'=>'flex-wrap:nowrap;','device'=>'desktop'];
        $node->advanced['handled_attributes']=array_merge($node->advanced['handled_attributes'],['name','position','image_url']);
        if (!empty($a['image_url'])) {
            $image=['src'=>$a['image_url'],'alt'=>$a['name'] ?? '','align'=>$narrow_portrait?'center':'left','align_tablet'=>$four_column_quarter?'center':'left','align_phone'=>'center'];
            foreach ($a as $key=>$value) {
                if (preg_match('/^(border_radii)_image(|_tablet|_phone)$/D',$key,$m) || preg_match('/^((?:border_(?:width_all|color_all|style_all)|box_shadow_(?:style|horizontal|vertical|blur|spread|color|position)))_image()$/D',$key,$m)) { $image[$m[1].$m[2]]=$value; $node->advanced['handled_attributes'][]=$key; }
            }
            $portrait=(new ImageConverter())->convert($this->raw('et_pb_image',$image));
            $portrait->advanced['layout_css'][]=['selector'=>'{{WRAPPER}}.elementor-element.elementor-widget-image','css'=>'max-width:320px;','device'=>'desktop'];
            $portrait->advanced['layout_css'][]=['selector'=>'{{WRAPPER}}.elementor-element.elementor-widget-image','css'=>$narrow_portrait?'width:100%;margin:0 0 12px;':'width:auto;flex-shrink:0;margin:0 30px 0 0;','device'=>'desktop'];
            $portrait->advanced['layout_css'][]=['selector'=>'{{WRAPPER}}.elementor-element.elementor-widget-image','css'=>'width:auto;margin:0 30px 12px 0;','device'=>'tablet'];
            if ($four_column_quarter) { $portrait->advanced['layout_css'][]=['selector'=>'{{WRAPPER}}.elementor-element.elementor-widget-image','css'=>'width:100%;max-width:none;margin:0 0 12px;','device'=>'tablet']; }
            $portrait->advanced['layout_css'][]=['selector'=>'{{WRAPPER}}.elementor-element.elementor-widget-image','css'=>'width:100%;max-width:320px;margin:0 0 12px;','device'=>'mobile'];
            $this->surface_css($node,$portrait,$a,'custom_css_member_image','{{WRAPPER}}.elementor-widget-image');
            $this->child($node,$portrait,'ace-person-portrait');
            if (!$context) { $node->advanced['warnings'][]='Person export has no source column context. A wide-column portrait layout was used; check placement if inserting this standalone module into a narrow column.'; }
        }
        $description=new Node('et_pb_team_member_description','container');$description->content=$this->container();$description->content['width']=['unit'=>'%','size'=>100];
        $description->advanced['layout_css'][]=['selector'=>'{{WRAPPER}}.elementor-element','css'=>'flex:1;min-width:0;','device'=>'desktop'];
        if (($a['name'] ?? '') !== '') { $this->child($description,$this->heading($node,$raw,$a['name'],'header','h4'),'ace-person-name'); }
        if (($a['position'] ?? '') !== '') {
            $role=$this->heading($node,$raw,$a['position'],'position','h4');
            $role->content['header_size']='div';
            // Divi's role is body-sized text, independent of the name heading.
            if (!isset($a['position_font_size'])) { $role->styles->set('typography_font_size',['unit'=>'px','size'=>$this->theme($raw)['body_size']]); }
            if (!isset($a['position_text_color'])) { $role->styles->set('title_color',($a['background_layout'] ?? 'light')==='dark' ? '#ffffff' : '#aaaaaa'); }
            $role->advanced['scoped_css'][0]['css']='margin:0;padding-bottom:7px;';
            $this->child($description,$role,'ace-person-position');
        }
        if (trim($raw['text_content'] ?? '') !== '' || isset($a['content_tablet']) || isset($a['content_phone'])) { $this->child($description,$this->body($node,$raw),'ace-person-biography'); }
        $networks=['facebook_url'=>'facebook','twitter_url'=>'twitter','google_url'=>'google-plus-g','linkedin_url'=>'linkedin'];
        $icons=[];
        foreach ($networks as $key=>$network) {
            if (!isset($a[$key])) { continue; } $node->advanced['handled_attributes'][]=$key;
            if ($a[$key]==='') { continue; }
            $icons[]=['_id'=>substr(hash('sha256',$key),0,7),'social_icon'=>['value'=>'fab fa-'.$network,'library'=>'fa-brands'],'link'=>['url'=>$a[$key],'is_external'=>'on','nofollow'=>''],'item_icon_color'=>'custom','item_icon_primary_color'=>'rgba(0,0,0,0)','item_icon_secondary_color'=>'#b2b2b2'];
            if ($key==='google_url') { $node->advanced['warnings'][]='Legacy Google+ social link preserved; this service is discontinued. Review its destination before publishing.'; }
        }
        if ($icons) {
            $social=new Node('et_pb_team_member_social','social-icons');
            $social->content=['social_icon_list'=>$icons,'shape'=>'square','icon_size'=>['unit'=>'px','size'=>16],'icon_padding'=>['unit'=>'em','size'=>0],'icon_spacing'=>['unit'=>'px','size'=>15],'hover_secondary_color'=>$this->theme($raw)['accent_color'],'align'=>$a['text_orientation'] ?? 'left'];
            foreach (['_tablet'=>'_tablet','_phone'=>'_mobile'] as $source=>$target) { if (isset($a['text_orientation'.$source])) { $social->content['align'.$target]=$a['text_orientation'.$source]; } }
            $this->surface_css($node,$social,$a,'custom_css_member_social_links','{{WRAPPER}} .elementor-social-icons-wrapper');
            $social->advanced['layout_css'][]=['selector'=>'{{WRAPPER}}','css'=>'margin-top:20px;','device'=>'desktop'];
            $this->child($description,$social,'ace-person-social');
        }
        if($description->children){$this->surface_css($node,$description,$a,'custom_css_member_description','{{WRAPPER}}');$this->child($node,$description,'ace-person-description');}
        else{$node->content['flex_direction']='column';}
        $node->advanced['information'][]='Person converted to editable portrait, name, role, biography and social icons; portrait styling is isolated from the card container.';
    }
}
