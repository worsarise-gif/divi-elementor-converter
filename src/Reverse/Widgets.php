<?php
namespace SuiteEdge\Migrator\Reverse;

/** Explicit semantic adapters; no widget is guessed from its rendered HTML. */
class Widgets {
    private $d;
    private $styles;
    private $shortcodes;
    private $media;
    public function __construct(Diagnostics $diagnostics,array $media=[]) { $this->d=$diagnostics;$this->styles=new Styles($diagnostics);$this->shortcodes=new Shortcodes();$this->media=$media; }
    public function catalog() {
        return ['heading'=>'et_pb_heading','text-editor'=>'et_pb_text','button'=>'et_pb_button','image'=>'et_pb_image','image-box'=>'et_pb_blurb','icon-box'=>'et_pb_blurb','icon'=>'et_pb_icon','divider'=>'et_pb_divider','spacer'=>'et_pb_divider','video'=>'et_pb_video','google_maps'=>'et_pb_code (Google iframe)','testimonial'=>'et_pb_testimonial','counter'=>'et_pb_number_counter','progress'=>'et_pb_counters','social-icons'=>'et_pb_social_media_follow','tabs'=>'et_pb_tabs','accordion'=>'et_pb_accordion','toggle'=>'et_pb_accordion','html'=>'et_pb_code','shortcode'=>'et_pb_code (external provider)','nav-menu'=>'et_pb_menu','wp-widget-nav_menu'=>'et_pb_menu','price-table'=>'et_pb_pricing_tables','call-to-action'=>'et_pb_cta','ace-post-grid'=>'et_pb_blog (bounded query)','posts'=>'guarded: Pro query/skin','loop-grid'=>'guarded: query/template','form'=>'guarded: delivery/actions','woocommerce-product-title'=>'guarded: product context','icon-list'=>'et_pb_code (retained list and responsive controls)','nested-accordion'=>'et_pb_code (indexed children and independent interaction)','nested-tabs'=>'guarded: nested content','image-gallery'=>'et_pb_gallery'];
    }
    public function convert(array $node, array $kit, $path) {
        $type=$node['widgetType'];$s=$this->styles->globals($node['settings'],$kit,$path);$used=[];
        $a=$this->styles->translate($s,$type,$used,$path);$body='';$target=$this->catalog()[$type]??'unsupported';
        $take=function($key,$default='')use($s,&$used){$used[$key]=true;return $s[$key]??$default;};
        if (!empty($node['elements'])) { $this->d->add($path,'Nested widget content requires its own Divi composition; it is retained in the source snapshot.',true); }
        try {
            switch ($type) {
                case 'heading':
                    $target='et_pb_heading';$a['title']=$take('title');$level=$take('header_size','h2');
                    if(!in_array($level,['h1','h2','h3','h4','h5','h6','div','span','p'],true)){throw new \UnexpectedValueException('Unsupported heading HTML tag.');}
                    if(in_array($level,['div','span','p'],true)){
                        $target='et_pb_text';$title=$a['title'];unset($a['title']);
                        foreach(array_keys($a) as $key){if(strpos($key,'title_')===0){$a['text_'.substr($key,6)]=$a[$key];unset($a[$key]);}}
                        $link=$take('link',[]);$link_attrs=$this->link($link,'url','new_window');$url=$link_attrs['url'];
                        if($url!==''){$title='<a href="'.htmlspecialchars($url,ENT_QUOTES,'UTF-8').'"'.(!empty($link['is_external'])?' target="_blank" rel="noopener noreferrer"':'').'>'.$title.'</a>';}
                        $body=$this->shortcodes->literal('<'.$level.'>'.$title.'</'.$level.'>');
                    }else{$a['title_level']=$level;$a+=$this->link($take('link',[]),'link_option_url','link_option_url_new_window');}break;
                case 'text-editor':$target='et_pb_text';$body=$this->shortcodes->literal($take('editor'));break;
                case 'button':$target='et_pb_button';$a['button_text']=$take('text');$a+=$this->link($take('link',[]),'button_url','url_new_window');break;
                case 'image':
                    $target='et_pb_image';$image=$take('image',[]);$media=$this->media[(int)($image['id']??0)]??[];$a['src']=$this->image($media?['url'=>$media['url']]:$image);$a['alt']=$take('_suiteedge_alt',$image['alt']??$media['alt']??'');$a['title_text']=$take('_suiteedge_image_title');
                    $link_to=$take('link_to','none');if($link_to==='custom'){$a+=$this->link($take('link',[]),'url','url_new_window');}elseif($link_to!=='none'){throw new \UnexpectedValueException('Media-file links/lightbox need an explicit Divi link mapping.');}
                    if($take('image_size','full')!=='full'){throw new \UnexpectedValueException('Image crop size needs a destination-size mapping.');}
                    if(!in_array($take('caption_source','none'),['','none'],true)){throw new \UnexpectedValueException('Visible image captions require a separate Divi Text module.');}break;
                case 'image-box':case 'icon-box':
                    $target='et_pb_blurb';$a['title']=$take('title_text');$body=$this->shortcodes->literal($take('description_text'));$a['header_level']=$take('title_size','h3');
                    $a+=$this->link($take('link',[]),'url','url_new_window');$a['use_icon']=$type==='icon-box'?'on':'off';
                    if($type==='icon-box'){$icon=$take('selected_icon',[]);if(empty($icon['value'])){$a['use_icon']='off';$a['image']='';}else{$a['font_icon']=$this->icon($icon);}}else{$a['image']=$this->image($take('image',[]));}
                    if(isset($s['position'])){$position=$take('position');if(!in_array($position,['left','top'],true)){throw new \UnexpectedValueException('Blurb image/icon placement needs an explicit Divi equivalent.');}$a['image_icon_placement']=$position;}break;
                case 'icon':$target='et_pb_icon';$a['font_icon']=$this->icon($take('selected_icon',[]));$a+=$this->link($take('link',[]),'url','url_new_window');if(isset($s['size'])){$a['icon_width']=$this->styles->slider($take('size'));}break;
                case 'divider':$target='et_pb_divider';$a['show_divider']='on';$a['divider_style']=$take('style','solid');if(isset($s['weight'])){$a['divider_weight']=$this->styles->slider($take('weight'));}break;
                case 'spacer':$target='et_pb_divider';$a['show_divider']='off';$a['height']=$this->styles->slider($take('space',['size'=>50,'unit'=>'px']));break;
                case 'video':
                    $target='et_pb_video';$video_type=$take('video_type','youtube');$value=$take($video_type.'_url');
                    if($video_type==='hosted'){$a['src']=$this->image($value);}elseif(in_array($video_type,['youtube','vimeo'],true)){$a['src']=$this->url($value);}else{throw new \UnexpectedValueException('Unsupported video provider.');}
                    if($take('show_image_overlay')==='yes'){$a['image_src']=$this->image($take('image_overlay',[]));}break;
                case 'google_maps':
                    $target='et_pb_code';$address=$take('address');$zoom=isset($s['zoom'])?($take('zoom')['size']??10):10;
                    if(!is_string($address)||!is_numeric($zoom)||$zoom<1||$zoom>20){throw new \UnexpectedValueException('Map address or zoom is invalid.');}
                    $class='ace-divi-map-'.$node['id'];$url='https://maps.google.com/maps?q='.rawurlencode($address).'&t=m&z='.(int)$zoom.'&output=embed&iwloc=near';$css='.' . $class . '{width:100%;border:0;height:'.(isset($s['height'])?$this->styles->slider($s['height']):'300px').';}';
                    foreach(['tablet'=>'_tablet','mobile'=>'_phone'] as $device=>$suffix){if(isset($s['height_'.$device])){$limit=(int)($kit['viewport_'.$device]??($device==='tablet'?1024:767));if($limit<240||$limit>10000){throw new \UnexpectedValueException('Map responsive breakpoint is invalid.');}$css.='@media(max-width:'.$limit.'px){.'.$class.'{height:'.$this->styles->slider($s['height_'.$device]).';}}';}unset($a['height'.$suffix]);}unset($a['height']);
                    $body='<style>'.$css.'</style><iframe class="'.$class.'" loading="lazy" title="'.htmlspecialchars('Map: '.$address,ENT_QUOTES,'UTF-8').'" src="'.htmlspecialchars($url,ENT_QUOTES,'UTF-8').'" allowfullscreen></iframe>';
                    $this->d->add($path,'Google Maps is preserved as a scoped iframe in Divi Code. It keeps the original Google embed provider and does not require a new Divi geocoding/API-key setup.');break;
                case 'testimonial':$target='et_pb_testimonial';$a['author']=$take('testimonial_name');$a['job_title']=$take('testimonial_job');$a['portrait_url']=$this->image($take('testimonial_image',[]));$body=$this->shortcodes->literal($take('testimonial_content'));break;
                case 'counter':
                    $target='et_pb_number_counter';$a['title']=$take('title');$a['number']=$take('ending_number',100);$a['percent_sign']=$take('suffix')==='%'?'on':'off';
                    if($take('starting_number',0)!=0||$take('prefix')!==''||!in_array($s['suffix']??'',['','%'],true)){throw new \UnexpectedValueException('Counter start/prefix/suffix has no native Divi equivalent.');}break;
                case 'progress':
                    $target='et_pb_counters';$percent=$take('percent',['size'=>50]);$number=$percent['size']??0;
                    if(!is_numeric($number)||$number<0||$number>100){throw new \UnexpectedValueException('Bar percentage must be between 0 and 100.');}
                    $body=$this->shortcodes->module('et_pb_counter',['title'=>$take('title'),'percent'=>(string)$number]);break;
                case 'tabs':case 'accordion':case 'toggle':
                    $target=$type==='tabs'?'et_pb_tabs':'et_pb_accordion';$items=$take('tabs',[]);if(!is_array($items)||count($items)>200){throw new \UnexpectedValueException('Invalid or oversized tab collection.');}
                    foreach($items as $index=>$item){if(!is_array($item)){throw new \UnexpectedValueException('Invalid tab item.');}$child=$type==='tabs'?'et_pb_tab':'et_pb_accordion_item';$body.=$this->shortcodes->module($child,['title'=>$item['tab_title']??''],$this->shortcodes->literal($item['tab_content']??''));$this->d->element($path.'/item-'.$index,$type.' item',$child,$item,array_fill_keys(['_id','tab_title','tab_content'],true));}
                    $this->d->add($path,'Divi uses its own tab/accordion open-state and interaction defaults; verify these after conversion.');break;
                case 'social-icons':
                    $target='et_pb_social_media_follow';$items=$take('social_icon_list',[]);if(!is_array($items)||count($items)>100){throw new \UnexpectedValueException('Invalid social icon collection.');}
                    foreach($items as $index=>$item){$icon=$item['social_icon']['value']??'';if(!is_string($icon)||!preg_match('/^fab fa-([a-z0-9-]+)$/D',$icon,$m)){throw new \UnexpectedValueException('Social network icon requires an explicit mapping.');}$attr=['social_network'=>$m[1]]+$this->link($item['link']??[],'url','url_new_window');$body.=$this->shortcodes->module('et_pb_social_media_follow_network',$attr);$this->d->element($path.'/item-'.$index,'social icon','et_pb_social_media_follow_network',$item,array_fill_keys(['_id','social_icon','link'],true));}break;
                case 'html':
                    $target='et_pb_code';$body=$take('html');if(!is_string($body)||preg_match('/\[\/?(?:et_pb_|dgbm_)/i',$body)){throw new \UnexpectedValueException('Code content contains a builder delimiter.');}
                    if(preg_match('/elementor-|SuiteEdgePortable|suiteedge-style-carrier|suiteedge-font-carrier/',$body)){throw new \UnexpectedValueException('Elementor-specific compiled HTML/CSS/scripts cannot be reattached to a different Divi DOM. Rebuild the source composition with native Divi modules.');}
                    $this->d->add($path,'HTML is preserved as Divi Code, with its original external providers and scripts. Review code and accessibility.');break;
                case 'shortcode':
                    $target='et_pb_code';$body=$this->shortcodes->literal($take('shortcode'));$this->d->add($path,'Third-party shortcode functionality still depends on its original provider; a native Divi mapping is required.',true);break;
                case 'nav-menu':case 'wp-widget-nav_menu':
                    $target='et_pb_menu';$menu=$take('menu',$take('nav_menu'));if(!is_scalar($menu)||!ctype_digit((string)$menu)||!(int)$menu||!wp_get_nav_menu_object((int)$menu)){throw new \UnexpectedValueException('Choose an existing local WordPress navigation menu.');}$a['menu_id']=(string)$menu;break;
                case 'price-table':
                    $target='et_pb_pricing_tables';$attr=['title'=>$take('heading'),'subtitle'=>$take('sub_heading'),'currency'=>$take('currency_symbol'),'sum'=>$take('price'),'per'=>$take('period'),'button_text'=>$take('button_text')]+$this->link($take('link',[]),'button_url','url_new_window');
                    $features=$take('features_list',[]);$text='';foreach($features as $index=>$feature){$text.='<p>'.htmlspecialchars((string)($feature['item_text']??''),ENT_QUOTES,'UTF-8').'</p>';$this->d->element($path.'/feature-'.$index,'pricing feature','pricing text',$feature,array_fill_keys(['_id','item_text'],true));}$body=$this->shortcodes->module('et_pb_pricing_table',$attr,$text);break;
                case 'call-to-action':
                    $target='et_pb_cta';$a['title']=$take('title');$a['button_text']=$take('button');$a+=$this->link($take('link',[]),'button_url','url_new_window');$body=$this->shortcodes->literal($take('description'));break;
                case 'image-gallery':
                    $target='et_pb_gallery';$images=$take('wp_gallery',[]);if(!is_array($images)||count($images)>500||!$images){throw new \UnexpectedValueException('Choose up to 500 local gallery images.');}$ids=[];
                    foreach($images as $image){$id=$image['id']??0;$post=get_post($id);if(!$post||$post->post_type!=='attachment'||!wp_attachment_is_image($id)){throw new \UnexpectedValueException('Gallery images must be existing local image attachments.');}$ids[]=(int)$id;}$a['gallery_ids']=implode(',',$ids);$a['gallery_columns']=$take('gallery_columns',4);break;
                case 'ace-post-grid':
                    $target='et_pb_blog';$a=$a+$this->blog($s,$take,$path);break;
                default:
                    $this->d->add($path,'Widget "'.$type.'" has no accepted native reverse mapping. Its complete settings are retained in the private source snapshot.',true);$target='unsupported';break;
            }
        } catch (\UnexpectedValueException $e) { $this->d->add($path,$e->getMessage(),true);$target='unsupported'; }
        $this->d->element($path,$type,$target,$s,$used);
        return $target==='unsupported'?'':$this->shortcodes->module($target,$a,$body);
    }
    private function url($value) {
        return (new Values())->url($value);
    }
    private function image($value) { if(!is_array($value)){throw new \UnexpectedValueException('Image must be a media object.');}return $this->url($value['url']??''); }
    private function link($value,$url_key,$window_key) {
        if(!is_array($value)){throw new \UnexpectedValueException('Link must be a URL object.');}
        if(!empty($value['custom_attributes'])||!empty($value['nofollow'])){throw new \UnexpectedValueException('Link rel/custom attributes require a Divi-specific mapping.');}
        return [$url_key=>$this->url($value['url']??''),$window_key=>!empty($value['is_external'])?'on':'off'];
    }
    private function icon($icon) {
        if(!is_array($icon)||!is_string($icon['value']??null)||!preg_match('/^(fas|far|fab) fa-([a-z0-9-]+)$/D',$icon['value'],$m)||!defined('ELEMENTOR_PATH')){throw new \UnexpectedValueException('SVG/custom icons require an explicit Divi icon or image mapping.');}
        $kind=['fas'=>'solid','far'=>'regular','fab'=>'brands'][$m[1]];$file=ELEMENTOR_PATH.'assets/lib/font-awesome/json/'.$kind.'.json';
        $library=is_readable($file)?json_decode(file_get_contents($file),true):[];$glyph=$library['icons'][$m[2]][3]??'';
        if(!preg_match('/^[a-f0-9]{4}$/iD',$glyph)){throw new \UnexpectedValueException('Font Awesome glyph is missing from the installed library.');}
        return html_entity_decode('&#x'.$glyph.';',ENT_QUOTES,'UTF-8').'||fa||'.($kind==='solid'?'900':'400');
    }
    private function blog(array $s,$take,$path) {
        if($take('post_order','recent')!=='recent'||$take('post_offset',0)!=0){throw new \UnexpectedValueException('Post order/offset pagination needs an accepted Divi query mapping.');}
        if($take('category_slugs')!==''){throw new \UnexpectedValueException('Map category slugs to local category IDs first.');}
        $ids=$take('category_ids');if($ids!==''&&!preg_match('/^[1-9][0-9]*(?:,[1-9][0-9]*)*$/D',$ids)){throw new \UnexpectedValueException('Invalid local Blog category IDs.');}
        foreach($ids===''?[]:explode(',',$ids) as $id){$term=get_term((int)$id,'category');if(!$term||is_wp_error($term)){throw new \UnexpectedValueException('Blog destination category no longer exists.');}}
        $layout=$take('layout','grid');if(!in_array($layout,['list','grid','masonry'],true)){throw new \UnexpectedValueException('Unsupported Blog layout.');}
        $a=['post_type'=>'post','fullwidth'=>$layout==='list'?'on':'off','include_categories'=>$ids,'posts_number'=>$take('posts_per_page',12),'header_level'=>$take('title_tag','h2'),'meta_date'=>$take('date_format','M j, Y'),'excerpt_length'=>$take('excerpt_length',270)];
        foreach(['show_image'=>'show_thumbnail','show_excerpt'=>'show_excerpt','use_excerpt'=>'use_excerpt','show_author'=>'show_author','show_date'=>'show_date','show_categories'=>'show_categories','show_comments'=>'show_comments','show_read_more'=>'show_more','show_pagination'=>'show_pagination'] as $key=>$target){$a[$target]=$take($key,in_array($key,['show_comments','show_read_more'],true)?'':'yes')==='yes'?'on':'off';}
        $this->d->add($path,'Blog uses local published posts. Compare Divi query pagination, masonry columns and excerpt rendering.');return $a;
    }
}
