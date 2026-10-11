<?php
namespace SuiteEdge\Migrator\Elementor\Posts;

/** Editable destination post listing for supported core Divi and DiviGear modules. */
class GridWidget extends \Elementor\Widget_Base {
    public function get_name() { return 'ace-post-grid'; }
    public function get_title() { return esc_html__('Ace Post Grid', 'suiteedge-divi-elementor-migrator'); }
    public function get_icon() { return 'eicon-posts-grid'; }
    public function get_categories() { return ['general']; }
    public function get_style_depends() { return ['ace-migrator-post-grid']; }
    public function get_script_depends() { return ['ace-migrator-post-grid']; }
    // A changing post query / pagination must not be frozen by Elementor element caching.
    protected function is_dynamic_content(): bool { return true; }
    protected function register_controls() {
        $c = '\Elementor\Controls_Manager';
        $this->start_controls_section('query_section', ['label' => esc_html__('Destination posts', 'suiteedge-divi-elementor-migrator')]);
        $this->add_control('query_note', ['type' => $c::RAW_HTML, 'raw' => esc_html__('Displays published posts from this WordPress site. Divi layout JSON contains no blog posts. Keep Ace Migrator active to render this widget.', 'suiteedge-divi-elementor-migrator')]);
        $this->add_control('posts_per_page', ['label' => esc_html__('Posts per page', 'suiteedge-divi-elementor-migrator'), 'type' => $c::NUMBER, 'min' => 1, 'max' => 100, 'default' => 12]);
        $this->add_control('post_offset', ['label' => esc_html__('Skip first posts', 'suiteedge-divi-elementor-migrator'), 'type' => $c::NUMBER, 'min' => 0, 'max' => 10000, 'default' => 0]);
        $this->add_control('post_order', ['label' => esc_html__('Order', 'suiteedge-divi-elementor-migrator'), 'type' => $c::SELECT, 'default' => 'recent', 'options' => ['recent' => esc_html__('Newest first', 'suiteedge-divi-elementor-migrator'), 'popular' => esc_html__('Most comments first', 'suiteedge-divi-elementor-migrator'), 'random' => esc_html__('Random (pagination must be off)', 'suiteedge-divi-elementor-migrator')]]);
        $this->add_control('category_slugs', ['label' => esc_html__('Destination category slugs', 'suiteedge-divi-elementor-migrator'), 'type' => $c::TEXT, 'description' => esc_html__('Comma-separated slugs. Blank displays all categories. Source category IDs are never reused.', 'suiteedge-divi-elementor-migrator'), 'default' => '']);
        $this->add_control('category_ids', ['label'=>esc_html__('Mapped destination category IDs','suiteedge-divi-elementor-migrator'),'type'=>$c::TEXT,'default'=>'','description'=>esc_html__('Filled by the explicit Blog category mapping. Clear this before using category slugs instead. Missing categories stop the query.','suiteedge-divi-elementor-migrator')]);
        $this->add_control('title_tag',['label'=>esc_html__('Title HTML tag','suiteedge-divi-elementor-migrator'),'type'=>$c::SELECT,'default'=>'h2','options'=>array_combine(['h1','h2','h3','h4','h5','h6'],['H1','H2','H3','H4','H5','H6'])]);
        $this->add_control('meta_separator',['label'=>esc_html__('Separate metadata with bars','suiteedge-divi-elementor-migrator'),'type'=>$c::SWITCHER,'default'=>'']);
        foreach (['show_image' => __('Featured image', 'suiteedge-divi-elementor-migrator'), 'show_excerpt' => __('Excerpt', 'suiteedge-divi-elementor-migrator'), 'use_excerpt' => __('Use manual excerpt', 'suiteedge-divi-elementor-migrator'), 'show_author' => __('Author', 'suiteedge-divi-elementor-migrator'), 'show_date' => __('Date', 'suiteedge-divi-elementor-migrator'), 'show_categories' => __('Categories', 'suiteedge-divi-elementor-migrator'), 'show_comments' => __('Comment count', 'suiteedge-divi-elementor-migrator'), 'show_read_more' => __('Read more button', 'suiteedge-divi-elementor-migrator'), 'show_pagination' => __('Older / newer pagination', 'suiteedge-divi-elementor-migrator'), 'meta_icons' => __('Metadata icons', 'suiteedge-divi-elementor-migrator'), 'equal_height' => __('Equal card heights', 'suiteedge-divi-elementor-migrator'), 'button_fullwidth' => __('Full-width button', 'suiteedge-divi-elementor-migrator'), 'button_at_bottom' => __('Button at bottom', 'suiteedge-divi-elementor-migrator'), 'image_overlay' => __('Image overlay on hover', 'suiteedge-divi-elementor-migrator')] as $name => $label) {
            $this->add_control($name, ['label' => $label, 'type' => $c::SWITCHER, 'default' => in_array($name, ['show_comments', 'show_read_more', 'meta_icons', 'equal_height', 'button_fullwidth', 'button_at_bottom', 'image_overlay'], true) ? '' : 'yes']);
        }
        $this->add_control('excerpt_length', ['label' => esc_html__('Automatic excerpt characters', 'suiteedge-divi-elementor-migrator'), 'type' => $c::NUMBER, 'min' => 1, 'max' => 2000, 'default' => 270]);
        $this->add_control('date_format', ['label' => esc_html__('Date format', 'suiteedge-divi-elementor-migrator'), 'type' => $c::TEXT, 'default' => 'M j, Y']);
        $this->add_control('read_more_text', ['label' => esc_html__('Read more text', 'suiteedge-divi-elementor-migrator'), 'type' => $c::TEXT, 'default' => __('Read More', 'suiteedge-divi-elementor-migrator')]);
        $this->end_controls_section();
        $this->start_controls_section('layout_section', ['label' => esc_html__('Grid and card', 'suiteedge-divi-elementor-migrator'), 'tab' => $c::TAB_STYLE]);
        $this->add_control('layout',['label'=>esc_html__('Layout','suiteedge-divi-elementor-migrator'),'type'=>$c::SELECT,'default'=>'grid','options'=>['grid'=>esc_html__('Grid','suiteedge-divi-elementor-migrator'),'masonry'=>esc_html__('Masonry','suiteedge-divi-elementor-migrator'),'list'=>esc_html__('Fullwidth list','suiteedge-divi-elementor-migrator')]]);
        $this->add_responsive_control('columns', ['label' => esc_html__('Columns', 'suiteedge-divi-elementor-migrator'), 'type' => $c::SELECT, 'default' => '3', 'tablet_default' => '2', 'mobile_default' => '1', 'options' => ['1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5'], 'selectors' => ['{{WRAPPER}} .ace-post-grid' => 'grid-template-columns:repeat({{VALUE}},minmax(0,1fr));']]);
        $this->add_responsive_control('grid_gap', ['label' => esc_html__('Space between cards', 'suiteedge-divi-elementor-migrator'), 'type' => $c::SLIDER, 'size_units' => ['px','em','rem','%'], 'default' => ['unit'=>'px','size'=>20], 'selectors' => ['{{WRAPPER}}' => '--ace-post-gap:{{SIZE}}{{UNIT}};']]);
        $this->add_control('card_color', ['label' => esc_html__('Card background', 'suiteedge-divi-elementor-migrator'), 'type' => $c::COLOR, 'default' => '#ffffff', 'selectors' => ['{{WRAPPER}} .ace-post-card,{{WRAPPER}} .ace-post-content' => 'background-color:{{VALUE}};']]);
        $this->add_control('image_ratio',['label'=>esc_html__('Featured image ratio','suiteedge-divi-elementor-migrator'),'type'=>$c::SELECT,'default'=>'8 / 5','options'=>['8 / 5'=>esc_html__('Divi standard crop (8:5)','suiteedge-divi-elementor-migrator'),'auto'=>esc_html__('Original image ratio','suiteedge-divi-elementor-migrator'),'1'=>esc_html__('Square','suiteedge-divi-elementor-migrator'),'16 / 9'=>esc_html__('Widescreen','suiteedge-divi-elementor-migrator'),'4 / 3'=>'4:3'],'selectors'=>['{{WRAPPER}} .ace-post-image img'=>'aspect-ratio:{{VALUE}};']]);
        $this->add_control('image_fit',['label'=>esc_html__('Featured image fit','suiteedge-divi-elementor-migrator'),'type'=>$c::SELECT,'default'=>'cover','options'=>['cover'=>esc_html__('Cover','suiteedge-divi-elementor-migrator'),'contain'=>esc_html__('Contain','suiteedge-divi-elementor-migrator')],'selectors'=>['{{WRAPPER}} .ace-post-image img'=>'object-fit:{{VALUE}};']]);
        foreach (['card'=>' .ace-post-card','image'=>' .ace-post-image'] as $prefix=>$selector) {
            $this->add_group_control(\Elementor\Group_Control_Border::get_type(), ['name'=>$prefix.'_border','selector'=>'{{WRAPPER}}'.$selector,'fields_options'=>['border'=>['responsive'=>true],'color'=>['responsive'=>true]]]);
            $this->add_responsive_control($prefix.'_radius', ['label'=>$prefix==='card'?esc_html__('Card corners','suiteedge-divi-elementor-migrator'):esc_html__('Image corners','suiteedge-divi-elementor-migrator'),'type'=>$c::DIMENSIONS,'size_units'=>['px','%'],'selectors'=>['{{WRAPPER}}'.$selector=>'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        }
        $this->add_control('overlay_color', ['label'=>esc_html__('Image overlay color','suiteedge-divi-elementor-migrator'),'type'=>$c::COLOR,'default'=>'rgba(51,51,51,0.59)','selectors'=>['{{WRAPPER}} .ace-post-image:after'=>'background-color:{{VALUE}};']]);
        $this->add_responsive_control('button_alignment', ['label'=>esc_html__('Button alignment','suiteedge-divi-elementor-migrator'),'type'=>$c::SELECT,'default'=>'left','options'=>['left'=>__('Left','suiteedge-divi-elementor-migrator'),'center'=>__('Center','suiteedge-divi-elementor-migrator'),'right'=>__('Right','suiteedge-divi-elementor-migrator')],'selectors'=>['{{WRAPPER}} .ace-post-button-wrap'=>'text-align:{{VALUE}};']]);
        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), ['name'=>'card_shadow','selector'=>'{{WRAPPER}} .ace-post-card']);
        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), ['name'=>'card_hover_shadow','selector'=>'{{WRAPPER}} .ace-post-card:hover,{{WRAPPER}} .ace-post-card:focus-within']);
        $this->end_controls_section();
        $parts=['title'=>[' .ace-post-title, .ace-post-title a',__('Title','suiteedge-divi-elementor-migrator')], 'meta'=>[' .ace-post-meta, .ace-post-meta a',__('Metadata','suiteedge-divi-elementor-migrator')], 'date'=>[' .ace-post-date',__('Date','suiteedge-divi-elementor-migrator')], 'author'=>[' .ace-post-author, .ace-post-author a',__('Author','suiteedge-divi-elementor-migrator')], 'category'=>[' .ace-post-categories, .ace-post-categories a',__('Categories','suiteedge-divi-elementor-migrator')], 'text'=>[' .ace-post-excerpt',__('Excerpt','suiteedge-divi-elementor-migrator')], 'read_more'=>[' .ace-post-button',__('Read more','suiteedge-divi-elementor-migrator')], 'pagination'=>[' .ace-post-pagination a',__('Pagination','suiteedge-divi-elementor-migrator')]];
        foreach($parts as $prefix=>$part) {
            $selector=implode(',',array_map(function($s){return '{{WRAPPER}}'.trim($s,' ').' ';},explode(',',$part[0])));
            // Use a space between wrapper and child (the controls remain native/editable).
            $selector=str_replace('{{WRAPPER}}.','{{WRAPPER}} .',$selector);
            $this->start_controls_section($prefix.'_section',['label'=>$part[1],'tab'=>$c::TAB_STYLE]);
            $this->add_group_control(\Elementor\Group_Control_Typography::get_type(),['name'=>$prefix,'selector'=>$selector,'fields_options'=>[
                'font_family'=>['responsive'=>true],
                'font_weight'=>['responsive'=>true,'type'=>$c::NUMBER,'min'=>1,'max'=>1000,'step'=>1],
                'font_style'=>['responsive'=>true],'text_transform'=>['responsive'=>true],
                'text_decoration'=>['responsive'=>true,'options'=>[''=>esc_html__('Default','suiteedge-divi-elementor-migrator'),'none'=>esc_html__('None','suiteedge-divi-elementor-migrator'),'underline'=>esc_html__('Underline','suiteedge-divi-elementor-migrator'),'line-through'=>esc_html__('Strike through','suiteedge-divi-elementor-migrator'),'underline line-through'=>esc_html__('Underline and strike through','suiteedge-divi-elementor-migrator')]],
            ]]);
            $this->add_responsive_control($prefix.'_color',['label'=>esc_html__('Text color','suiteedge-divi-elementor-migrator'),'type'=>$c::COLOR,'selectors'=>[$selector=>'color:{{VALUE}};']]);
            $this->add_responsive_control($prefix.'_alignment',['label'=>esc_html__('Alignment','suiteedge-divi-elementor-migrator'),'type'=>$c::SELECT,'options'=>['left'=>__('Left','suiteedge-divi-elementor-migrator'),'center'=>__('Center','suiteedge-divi-elementor-migrator'),'right'=>__('Right','suiteedge-divi-elementor-migrator'),'justify'=>__('Justify','suiteedge-divi-elementor-migrator')],'selectors'=>[$selector=>'text-align:{{VALUE}};'.($prefix==='meta'?'justify-content:{{VALUE}};':'')]]);
            $this->add_control($prefix.'_background',['label'=>esc_html__('Background','suiteedge-divi-elementor-migrator'),'type'=>$c::COLOR,'selectors'=>[$selector=>'background-color:{{VALUE}};']]);
            $this->end_controls_section();
        }
        $this->start_controls_section('spacing_section',['label'=>esc_html__('Spacing and hover','suiteedge-divi-elementor-migrator'),'tab'=>$c::TAB_STYLE]);
        foreach(['container'=>'.ace-post-grid','article'=>'.ace-post-card','image'=>'.ace-post-image','content'=>'.ace-post-content','title'=>'.ace-post-title','meta_default'=>'.ace-post-meta','date'=>'.ace-post-date','author'=>'.ace-post-author','category'=>'.ace-post-categories','text'=>'.ace-post-excerpt','button'=>'.ace-post-button','pagination'=>'.ace-post-pagination a'] as $prefix=>$selector) {
            foreach(['margin','padding'] as $property) {
                /* translators: 1: Grid element name 2: Spacing property */
                $this->add_responsive_control($prefix.'_'.$property,['label'=>sprintf(esc_html__('%1$s %2$s','suiteedge-divi-elementor-migrator'),ucfirst(str_replace('_',' ',$prefix)),$property),'type'=>$c::DIMENSIONS,'size_units'=>['px','em','rem','%'],'selectors'=>['{{WRAPPER}} '.$selector=>$property.':{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
            }
        }
        $this->add_control('read_more_hover_color',['label'=>esc_html__('Read more hover color','suiteedge-divi-elementor-migrator'),'type'=>$c::COLOR,'selectors'=>['{{WRAPPER}} .ace-post-card:hover .ace-post-button,{{WRAPPER}} .ace-post-card:focus-within .ace-post-button'=>'color:{{VALUE}};']]);
        $this->end_controls_section();
    }
    protected function render() {
        $s=$this->get_settings_for_display();$key='ace_grid_'.$this->get_id();$page=1;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Read-only pagination: raw input must match at most five digits before integer casting/bounding below; text sanitization could turn malformed markup into a valid page.
        $requested=isset($_GET[$key])?wp_unslash($_GET[$key]):'1';
        if (is_string($requested) && preg_match('/^[0-9]{1,5}$/D',$requested)) { $page=max(1,min(10000,(int)$requested)); }
        if (($s['show_pagination']??'')!=='yes' || \Elementor\Plugin::$instance->editor->is_edit_mode()) { $page=1; }
        $query=new GridQuery();
        try { $args=$query->args($s,$page); }
        catch(\UnexpectedValueException $e) { echo '<p class="ace-post-empty">'.esc_html($e->getMessage()).'</p>';return; }
        $posts=new \WP_Query($args);$pages=$query->pages($posts->found_posts,$s);
        $classes='ace-post-grid';foreach(['equal_height','button_fullwidth','button_at_bottom','image_overlay'] as $flag){if(($s[$flag]??'')==='yes'){$classes.=' ace-'.$flag;}}
        $layout=$s['layout']??'grid';$layout=in_array($layout,['grid','masonry','list'],true)?$layout:'grid';$classes.=' ace-post-layout-'.$layout;
        if(($s['meta_separator']??'')==='yes'){$classes.=' ace-post-meta-separated';}
        $tag=$s['title_tag']??'h2';$tag=in_array($tag,['h1','h2','h3','h4','h5','h6'],true)?$tag:'h2';
        echo '<div class="'.esc_attr($classes).'">';
        if (!$posts->posts) { echo '<p class="ace-post-empty">'.esc_html__('No published posts match this grid. Check destination posts, categories and offset.','suiteedge-divi-elementor-migrator').'</p>'; }
        foreach($posts->posts as $post) {
            $id=$post->ID;$url=get_permalink($id);$title=get_the_title($id);
            echo '<article class="ace-post-card" data-post-id="'.esc_attr($id).'">';
            if(($s['show_image']??'')==='yes' && has_post_thumbnail($id)) {
                echo '<a class="ace-post-image" href="'.esc_url($url).'" aria-label="'.esc_attr($title).'">';
                echo wp_kses_post(get_the_post_thumbnail($id,'medium_large',['loading'=>'lazy']));echo '</a>';
            }
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $tag is selected exclusively from the fixed h1-h6 allowlist above.
            echo '<div class="ace-post-content"><'.$tag.' class="ace-post-title"><a href="'.esc_url($url).'">'.esc_html($title).'</a></'.$tag.'>';
            $this->meta($post,$s);
            if(($s['show_excerpt']??'')==='yes') {
                $excerpt=(new Excerpt())->text($post,$s);
                echo '<p class="ace-post-excerpt">'.esc_html($excerpt).'</p>';
            }
            if(($s['show_read_more']??'')==='yes') {
                /* translators: 1: Read more button text 2: Post title */
                $label=sprintf(__('%1$s: %2$s','suiteedge-divi-elementor-migrator'),$s['read_more_text'],$title);
                echo '<div class="ace-post-button-wrap"><a class="ace-post-button" href="'.esc_url($url).'" aria-label="'.esc_attr($label).'">'.esc_html($s['read_more_text']).'</a></div>';
            }
            echo '</div></article>';
        }
        echo '</div>';
        if(($s['show_pagination']??'')==='yes' && $pages>1) {
            echo '<nav class="ace-post-pagination" aria-label="'.esc_attr__('Blog pagination','suiteedge-divi-elementor-migrator').'">';
            // Elementor may render a template on a different host document. Pagination uses
            // the displayed page permalink and a widget-specific argument, never /page/N.
            $base=get_permalink(get_queried_object_id());if(!$base){$base=home_url('/');}
            // Preserve independent grids without reflecting arbitrary public parameters.
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Read-only pagination keys and values are strictly allowlisted and bounded below.
            foreach($_GET as $other=>$value){if($other!==$key && is_string($other) && preg_match('/^ace_grid_[a-f0-9]{1,8}$/D',$other) && is_string($value) && preg_match('/^[0-9]{1,5}$/D',$value) && (int)$value>=1 && (int)$value<=10000){$base=add_query_arg($other,(int)$value,$base);}}
            if($page<$pages){echo '<a class="ace-post-older" href="'.esc_url(add_query_arg($key,$page+1,$base)).'">'.esc_html__('« Older Entries','suiteedge-divi-elementor-migrator').'</a>';}
            if($page>1){$url=$page===2?$base:add_query_arg($key,$page-1,$base);echo '<a class="ace-post-newer" href="'.esc_url($url).'">'.esc_html__('Newer Entries »','suiteedge-divi-elementor-migrator').'</a>';}
            echo '</nav>';
        }
    }
    private function meta($post,array $s) {
        $entries=[];$icons=($s['meta_icons']??'')==='yes';
        if(($s['show_author']??'')==='yes'){$entries['author']='<a href="'.esc_url(get_author_posts_url($post->post_author)).'">'.esc_html(get_the_author_meta('display_name',$post->post_author)).'</a>';}
        if(($s['show_date']??'')==='yes'){$entries['date']=esc_html(get_the_date(substr((string)($s['date_format']??'M j, Y'),0,100),$post));}
        if(($s['show_categories']??'')==='yes'){
            $cats=get_the_category($post->ID);$links=[];foreach($cats as $cat){$url=get_category_link($cat);if(!is_wp_error($url)){$links[]='<a href="'.esc_url($url).'">'.esc_html($cat->name).'</a>';}}
            if($links){$entries['categories']=implode(', ',$links);}
        }
        if(($s['show_comments']??'')==='yes'){
            $count=(int)$post->comment_count;
            /* translators: %s: Number of comments */
            $entries['comments']=sprintf(_n('%s comment','%s comments',$count,'suiteedge-divi-elementor-migrator'),number_format_i18n($count));
        }
        if(!$entries){return;}
        echo '<p class="ace-post-meta">';
        foreach($entries as $type=>$html){echo '<span class="ace-post-'.esc_attr($type).'">';if($icons){
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon() returns only fixed original SVG primitives, never user markup.
            echo $this->icon($type);
        }echo wp_kses_post($html).'</span>';}
        echo '</p>';
    }
    private function icon($type) {
        // Original, font-independent SVG primitives; decorative metadata only.
        $paths=['author'=>'<circle cx="12" cy="7" r="4"/><path d="M4 22v-3a8 8 0 0 1 16 0v3"/>','date'=>'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M7 2v6M17 2v6"/>','categories'=>'<path d="M2 6h8l2 3h10v12H2z"/>','comments'=>'<path d="M3 3h18v14H9l-6 4z"/>'];
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG primitives selected from a fixed dictionary, never user markup.
        return '<svg class="ace-meta-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">'.$paths[$type].'</svg>';
    }
}
