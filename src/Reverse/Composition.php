<?php
namespace SuiteEdge\Migrator\Reverse;

/** Preserve a validated Classic tree and its source CSS in an independent Divi Code surface. */
class Composition {
    private $d;
    private $nodes=[];
    private $dependencies=[];
    private $kit=[];
    private $styles;
    private $types=[];
    private $scope;
    private $asset_hashes=[];
    private $font_families=[];
    private $animations=[];
    private $media=[];
    private $safe=['heading','text-editor','button','image','image-box','icon-box','icon','icon-list','divider','spacer','testimonial','html','shortcode','google_maps','social-icons','nested-accordion','accordion','toggle','tabs'];
    public static function available() { return class_exists('Elementor\Core\Files\CSS\Post') && isset(\Elementor\Plugin::$instance->elements_manager); }
    public function convert(array $snapshot) {
        $read_only=function(){return new \WP_Error('ace_reverse_read_only','Source composition review does not fetch remote provider assets.');};
        $direct_render=function(){return false;};
        add_filter('pre_http_request',$read_only,-PHP_INT_MAX);
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Source builder's supported direct-render hook; never persist its cache during review.
        add_filter('elementor/element/should_render_shortcode',$direct_render,PHP_INT_MAX);
        try{return $this->build($snapshot);}finally{remove_filter('pre_http_request',$read_only,-PHP_INT_MAX);remove_filter('elementor/element/should_render_shortcode',$direct_render,PHP_INT_MAX);}
    }
    private function build(array $snapshot) {
        $this->nodes=[];$this->dependencies=[];$this->types=[];$this->asset_hashes=[];$this->font_families=[];$this->animations=[];
        $this->d=new Diagnostics();$this->styles=new Styles($this->d);$this->kit=$snapshot['kit_settings']??[];
        $this->media=($snapshot['site']??'')===home_url('/')?($snapshot['media']??[]):[];
        $this->scope='ace-reverse-'.substr(hash('sha256',json_encode($snapshot['data'])),0,16);
        $this->index($snapshot['data']);
        $breakpoints=\Elementor\Plugin::$instance->breakpoints;
        $previous=$breakpoints->get_responsive_control_duplication_mode();$breakpoints->set_responsive_control_duplication_mode('on');
        try{$tree=$this->prepare($snapshot['data'],'root');}finally{$breakpoints->set_responsive_control_duplication_mode($previous);}
        $page=$snapshot['page_settings']??[];$page_used=array_fill_keys(['post_status','post_title','post_excerpt','featured_image','hide_title'],true);
        $this->d->element('page','page settings','Divi page presentation',$page,$page_used);
        if(!empty($this->kit['__dynamic__'])||!empty($this->kit['custom_css'])){$this->d->add('kit','Dynamic/global custom CSS needs an explicit source kit composition contract.',true);}
        $css='';$html='';$fonts='';
        if(!$this->d->blockers){
            $compiler=new CompositionCss($tree);$css=$compiler->get_content();
            $kit_compiler=new CompositionCss([]);$kit_compiler->get_content();$kit_compiler->compile_kit($this->kit);$base_kit=(string)$kit_compiler->get_stylesheet();
            // Keep the actual Shortcode widget's DOM/default width without
            // executing its provider during review. Only validated embeds may pass.
            $retained=[];foreach($this->dependencies as $dependency){if(($dependency['kind']??'')==='formidable'){$retained[$dependency['shortcode']]=true;}}
            $preserve=function($pre,$tag,$attrs,$match)use($retained){if($tag==='formidable'&&isset($retained[$match[0]])){return $match[0];}throw new \UnexpectedValueException('Source renderer requested an unverified shortcode provider.');};
            add_filter('pre_do_shortcode_tag',$preserve,-PHP_INT_MAX,4);
            $buffer=ob_get_level();ob_start();
            try{foreach($tree as $data){$element=\Elementor\Plugin::$instance->elements_manager->create_element_instance($data);if(!$element){throw new \UnexpectedValueException('Source renderer cannot create an accepted element.');}$element->print_element();}$html=ob_get_clean();}
            catch(\Throwable $e){while(ob_get_level()>$buffer){ob_end_clean();}throw $e;}
            finally{remove_filter('pre_do_shortcode_tag',$preserve,-PHP_INT_MAX);}
            if(preg_match('/\[elementor-element\b|data-elementor-open-lightbox="yes"|data-e-action-hash/',$html)){throw new \UnexpectedValueException('Composition still requires an Elementor cache/lightbox runtime.');}
            $html=$this->scope_styles($html);
            $base=$this->asset('assets/css/frontend.min.css');
            foreach(array_keys($this->types) as $type){$file='assets/css/widget-'.$type.'.min.css';if(is_readable(ELEMENTOR_PATH.$file)){$base.=$this->asset($file);}}
            foreach(array_keys($this->animations) as $animation){$base.=$this->asset('assets/lib/animations/styles/e-animation-'.$animation.'.min.css');}
            foreach(['solid','regular','brands'] as $icon_kind){$file='assets/lib/font-awesome/css/'.$icon_kind.'.min.css';if(is_readable(ELEMENTOR_PATH.$file)){$base.=$this->asset($file);}}
            $file='assets/lib/font-awesome/css/fontawesome.min.css';if(is_readable(ELEMENTOR_PATH.$file)){$base.=$this->asset($file);}
            if(class_exists('Elementor\Core\Files\CSS\Global_CSS')){$base.=(new \Elementor\Core\Files\CSS\Global_CSS('ace-reverse-global'))->get_content();}
            // Divi's .entry-content ul line-height would override the source
            // widget's inheritance. Authored source CSS remains later and stronger.
            $isolation='.ace-source-kit .elementor-widget-icon-list .elementor-icon-list-items{line-height:inherit;}';
            $this->capture_kit_fonts();$css=(new CssScope())->compile($base.$isolation.$base_kit.$css,'.'.$this->scope);
            $font_provider=new Fonts();$fonts=$font_provider->links(array_keys($this->font_families),$this->d);$this->dependencies=array_merge($this->dependencies,$font_provider->dependencies);
            $this->dependencies[]=(new Providers())->environment();
        }
        $manifest=['css_assets'=>$this->asset_hashes,'fonts'=>array_keys($this->font_families),'source_version'=>defined('ELEMENTOR_VERSION')?ELEMENTOR_VERSION:null];
        // Divi's FitVids honors this parent opt-out. Source embeds already own
        // their responsive geometry and must not acquire a second video wrapper.
        // Elementor's document metadata enables legacy widget widths even on a
        // newer runtime. Retain that real source wrapper contract separately.
        $document_class=array_key_exists('document_version',$snapshot)&&version_compare((string)$snapshot['document_version'],'2.5.0','<')?' elementor-bc-flex-widget':'';
        $body='<div class="'.$this->scope.' fitvidsignore" data-ace-reverse-composition="1">'.$fonts.'<style>'.$css.'</style><div class="ace-source-kit elementor elementor-'.(int)($snapshot['id']??0).$document_class.'">'.$html.'</div>'.$this->runtime().'</div>';
        $s=new Shortcodes();$code=$s->module('et_pb_code',['module_class'=>'ace-reverse-code','custom_margin'=>'0px|0px|0px|0px','custom_padding'=>'0px|0px|0px|0px'],$body);
        $content=$s->module('et_pb_section',['module_class'=>'ace-reverse-section','custom_padding'=>'0px|0px|0px|0px','background_color'=>'rgba(0,0,0,0)'],$s->module('et_pb_row',['width'=>'100%','max_width'=>'100%','custom_padding'=>'0px|0px|0px|0px','use_custom_gutter'=>'on','gutter_width'=>'1'],$s->module('et_pb_column',['type'=>'4_4'],$code)));
        return ['engine'=>'divi4','content'=>$content,'blockers'=>array_values(array_unique($this->d->blockers)),'warnings'=>array_values(array_unique($this->d->warnings)),'coverage'=>$this->d->coverage,'issues'=>$this->d->issues,'dependencies'=>$this->dependencies,'composition_manifest'=>$manifest,'presentation'=>['hide_title'=>($page['hide_title']??'')==='yes'],'information'=>['Complex source geometry is retained in a self-contained Divi Code composition with compiled source controls, original container surfaces, grid/flex layout and responsive breakpoints. Edit its HTML/CSS in Divi Code; its children are not separate native Divi modules.','The source renderer is used only during conversion. Output includes source CSS and independent accordion/tab behavior; Elementor frontend JavaScript is not required. Retained font files, HTML embeds and third-party shortcode providers must remain available.','Review native Divi Visual Builder and frontend rendering before publication. Existing pages and metadata are not modified by review.']];
    }
    private function index(array $nodes) { foreach($nodes as $node){$this->nodes[$node['id']]=$node;$this->index($node['elements']);} }
    private function prepare(array $nodes,$parent) {
        $out=[];
        foreach($nodes as $node){
            $path=$parent.'/'.$node['id'];$s=$this->styles->globals($node['settings'],$this->kit,$path);$used=[];$ledger=[];$type=$node['widgetType']??$node['elType'];
            try{
                if($node['elType']==='widget'&&!in_array($type,$this->safe,true)){throw new \UnexpectedValueException('Widget "'.$type.'" needs an independent runtime adapter before portable composition.');}
                if(!empty($s['__dynamic__'])){throw new \UnexpectedValueException('Dynamic source bindings require a destination binding contract.');}
                $probe=$node;$probe['settings']=$s;$probe['elements']=[];$element=\Elementor\Plugin::$instance->elements_manager->create_element_instance($probe);
                if(!$element){throw new \UnexpectedValueException('Source element provider is unavailable.');}
                $controls=$element->get_controls();$values=$element->get_settings();
                $content=$this->content_controls($type);
                foreach($s as $key=>$value){
                    if($value===''||$value===null||$value===[]||$value===false){$used[$key]=true;$ledger[$key]='empty';continue;}
                    if(in_array($key,['_title','_element_id','css_classes','_css_classes','__globals__','presetTitle','presetIcon','_element_cache'],true)){$used[$key]=true;$ledger[$key]='metadata or preserved attribute';continue;}
                    $control=$controls[$key]??null;
                    if($control&&!$element->is_control_visible($control,$values,$controls)){$used[$key]=true;$ledger[$key]='inactive source condition';continue;}
                    if(preg_match('/^(?:_animation|animation|motion_fx|sticky|background_video|background_slideshow|custom_css|__dynamic__)/',$key)){continue;}
                    if($key==='_skin'&&!in_array($value,['','default'],true)){continue;}
                    if($control&&(!empty($control['selectors'])||!empty($control['prefix_class'])||in_array($control['type']??'', ['heading'],true))){$this->style_value($key,$value);$used[$key]=true;$ledger[$key]='source CSS compiler';continue;}
                    if(in_array($key,$content,true)||$control&&preg_match('/(?:typography|box_shadow_type|background_background|overlay_background)$/D',$key)){$used[$key]=true;$ledger[$key]='source renderer';continue;}
                }
                $this->validate_content($type,$s,$element,$path,$used);
                foreach($s as $key=>$value){if(preg_match('/font_family(?:_(?:tablet|mobile))?$/D',$key)&&is_string($value)&&$value!==''){$family=\SuiteEdge\Migrator\Style\FontFamily::normalize($value);$this->font_families[$family]=true;}}
                // Use resolved snapshot globals, not the QA/destination kit. Empty references never erase local values.
                unset($s['__globals__']);$s['_element_cache']='yes'; // Elementor labels 'yes' as cache Inactive.
                if($type==='image'&&($s['image_size']??'full')==='full'&&isset($s['image'])&&!isset($this->media[(int)($s['image']['id']??0)])){$s['image']['id']=0;}
                if($type==='shortcode'){$this->dependencies[]=(new Providers())->form($s['shortcode']??'');}
                $this->types[$type]=true;
            }catch(\UnexpectedValueException $e){$this->d->add($path,$e->getMessage(),true);}
            $this->d->element($path,$type,'et_pb_code composition',$node['settings'],$used);
            $this->d->coverage[count($this->d->coverage)-1]['setting_dispositions']=$ledger;
            $node['settings']=$s;$node['elements']=$this->prepare($node['elements'],$path);$out[]=$node;
        }
        return $out;
    }
    private function content_controls($type) {
        $shared=['html_tag','content_width','container_type','_skin'];
        $map=['heading'=>['title','header_size','link','size'],'text-editor'=>['editor','drop_cap','text_columns'],'button'=>['text','link','selected_icon','icon','icon_align','button_type','size','hover_animation'],'image'=>['image','image_size','image_custom_dimension','link_to','link','caption_source','caption','open_lightbox'],'image-box'=>['image','image_size','title_text','description_text','title_size','position','link','hover_animation'],'icon-box'=>['selected_icon','icon','title_text','description_text','title_size','position','link','hover_animation'],'icon'=>['selected_icon','icon','link','view','shape','hover_animation'],'icon-list'=>['icon_list','view','link_click'],'divider'=>['style','text','selected_icon','look'],'spacer'=>[],'testimonial'=>['testimonial_name','testimonial_job','testimonial_content','testimonial_image','image_size'],'html'=>['html'],'shortcode'=>['shortcode'],'google_maps'=>['address'],'social-icons'=>['social_icon_list','shape','columns','align'],'nested-accordion'=>['items','title_tag','default_state','max_items_expended','accordion_item_title_icon','accordion_item_title_icon_active','faq_schema'],'accordion'=>['tabs','selected_icon','selected_active_icon','title_html_tag'],'toggle'=>['tabs','selected_icon','selected_active_icon','title_html_tag'],'tabs'=>['tabs','type']];
        return array_merge($shared,$map[$type]??[]);
    }
    private function validate_content($type,array &$s,$element,$path,array &$used) {
        if(!empty($s['hover_animation'])){$animation=$s['hover_animation'];if(!is_string($animation)||!preg_match('/^[a-z0-9-]+$/D',$animation)||!is_readable(ELEMENTOR_PATH.'assets/lib/animations/styles/e-animation-'.$animation.'.min.css')){throw new \UnexpectedValueException('Hover animation requires an available source CSS adapter.');}$this->animations[$animation]=true;}
        foreach($s as $key=>$value){if(preg_match('/(?:link|image)(?:_(?:tablet|mobile))?$/D',$key)&&is_array($value)&&isset($value['url'])){$s[$key]['url']=(new Values())->url($value['url']);}}
        if(in_array($type,['heading','icon-box','image-box'],true)){ $key=$type==='heading'?'header_size':'title_size';if(isset($s[$key])&&!in_array($s[$key],['h1','h2','h3','h4','h5','h6','p','span','div'],true)){throw new \UnexpectedValueException('Invalid source title tag.');} }
        if($type==='image'&&($s['link_to']??'none')==='file'){throw new \UnexpectedValueException('Image lightbox/file behavior needs an independent runtime adapter.');}
        if($type==='image'&&isset($s['image_size'])&&$s['image_size']!=='full'){$image=$s['image']??[];$id=(int)($image['id']??0);if(!$id||!wp_attachment_is_image($id)||wp_get_attachment_url($id)!==($image['url']??'')){throw new \UnexpectedValueException('Cropped image requires a verified local attachment matching its source URL.');}}
        if($type==='text-editor'&&preg_match('/\[[\/]?[a-z][a-z0-9_-]*(?:\s|\])/i',$s['editor']??'')){throw new \UnexpectedValueException('Text Editor contains a provider shortcode; use a verified Shortcode widget contract.');}
        if(in_array($type,['html','text-editor'],true)&&preg_match('/\[\/?(?:et_pb_|dgbm_)/i',$s[$type==='html'?'html':'editor']??'')){throw new \UnexpectedValueException('Source content contains a builder delimiter.');}
        if($type==='html'){
            $html=$s['html']??'';if(!is_string($html)){throw new \UnexpectedValueException('HTML content must be text.');}
            if(preg_match('/elementorFrontend|elementorProFrontend|SuiteEdgePortable|suiteedge-style-carrier|suiteedge-font-carrier|\[elementor-element\b/',$html)){throw new \UnexpectedValueException('HTML requires a builder JavaScript/provider runtime.');}
            preg_match_all('/elementor-element-([a-zA-Z0-9_-]+)/',$html,$refs);
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Source ID in domain diagnostics; report consumers escape HTML/JSON.
            foreach($refs[1] as $id){if(!isset($this->nodes[$id])){throw new \UnexpectedValueException('CSS references an Elementor element outside the captured composition: '.$id);}}
            if(strpos($html,'elementor-')!==false&&!$refs[1]){throw new \UnexpectedValueException('Unbound Elementor-specific HTML/CSS requires a composition contract.');}
            if(preg_match('/<script\b|<iframe\b/i',$html)){$this->d->add($path,'Independent HTML embed/scripts are retained. Verify their external provider, accessibility and browser behavior.');}
        }
        if($type==='nested-accordion'){
            $items=$s['items']??[];$children=$this->nodes[basename($path)]['elements'];
            if(!is_array($items)||count($items)>200||count($items)!==count($children)){throw new \UnexpectedValueException('Accordion titles and indexed child containers do not match.');}
            foreach($items as $i=>$item){if(!is_array($item)||!is_string($item['item_title']??null)||($children[$i]['elType']??'')!=='container'){throw new \UnexpectedValueException('Invalid accordion item/container binding.');}$this->d->element($path.'/item-'.$i,'accordion title','indexed composition child',$item,array_fill_keys(['_id','item_title','element_css_id'],true));}
            $settings=$element->get_settings();$s['default_state']=$settings['default_state'];$s['max_items_expended']=$settings['max_items_expended'];$used['default_state']=true;$used['max_items_expended']=true;
        }
        if(in_array($type,['icon-list','social-icons'],true)){
            $key=$type==='icon-list'?'icon_list':'social_icon_list';$items=$s[$key]??[];if(!is_array($items)||count($items)>200){throw new \UnexpectedValueException('Invalid or oversized icon collection.');}
            foreach($items as $i=>$item){if(!is_array($item)){throw new \UnexpectedValueException('Invalid icon collection item.');}$item_used=array_fill_keys($type==='icon-list'?['_id','text','selected_icon','icon','link']:['_id','social_icon','link','item_icon_color','item_icon_primary_color','item_icon_secondary_color'],true);$this->d->element($path.'/item-'.$i,$type.' item','composition list item',$item,$item_used);if(isset($item['link']['url'])){$s[$key][$i]['link']['url']=(new Values())->url($item['link']['url']);}}
        }
        $icons=function($values)use(&$icons){foreach($values as $key=>$value){if(!is_array($value)){continue;}if(isset($value['library'])&&!empty($value['value'])){$class=$value['value'];if(!is_string($class)||!preg_match('/^(fas|far|fab) fa-([a-z0-9-]+)$/D',$class,$match)){throw new \UnexpectedValueException('Custom/SVG icon needs a verified portable icon provider.');}$kind=['fas'=>'solid','far'=>'regular','fab'=>'brands'][$match[1]];$file=ELEMENTOR_PATH.'assets/lib/font-awesome/json/'.$kind.'.json';$library=is_readable($file)?json_decode(file_get_contents($file),true):[];if(!isset($library['icons'][$match[2]])){throw new \UnexpectedValueException('Source icon glyph is missing from its installed provider.');}}else{$icons($value);}}};$icons($s);
    }
    private function style_value($key,$value) {
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Control key in domain diagnostics; report consumers escape at their output boundary.
        if(is_array($value)){foreach($value as $part=>$v){if($part==='sizes'||$part==='isLinked'){continue;}if($part==='url'){$this->style_value($key,$v);}elseif(is_string($v)&&preg_match('/[<>;{}\x00-\x1f]/',$v)){throw new \UnexpectedValueException('Invalid source CSS control value: '.$key);}}}
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Control key in domain diagnostics; report consumers escape at their output boundary.
        elseif(!is_scalar($value)||is_string($value)&&preg_match('/[<>;{}\x00-\x1f]/',$value)){throw new \UnexpectedValueException('Invalid source CSS control value: '.$key);}
    }
    private function asset($relative) {
        $path=ELEMENTOR_PATH.$relative;$css=file_get_contents($path);if($css===false){throw new \UnexpectedValueException('Source CSS provider asset is missing.');}
        $this->asset_hashes[$relative]=hash('sha256',$css);
        $base=defined('ELEMENTOR_URL')?ELEMENTOR_URL:plugins_url('/',ELEMENTOR_PATH.'elementor.php');
        $directory=dirname($relative).'/';
        return preg_replace_callback('/url\(\s*(["\x27]?)([^)"\x27]+)\1\s*\)/',function($m)use($base,$directory){if(preg_match('~^(?:https?:|data:|/|\#)~i',$m[2])){return $m[0];}
            $path=realpath(ELEMENTOR_PATH.$directory.strtok($m[2],'?#'));$root=realpath(ELEMENTOR_PATH);
            if(!$path||strpos(wp_normalize_path($path),trailingslashit(wp_normalize_path($root)))!==0||!is_readable($path)){throw new \UnexpectedValueException('Source CSS references a missing font/asset file.');}
            $relative=substr(wp_normalize_path($path),strlen(trailingslashit(wp_normalize_path($root))));$this->asset_hashes[$relative]=hash_file('sha256',$path);
            return 'url("'.$base.$relative.'")';},$css);
    }
    private function capture_kit_fonts() {
        // All kit CSS and variables are compiled by the source control schema.
        // Capture resources without duplicating conditional CSS on another surface.
        $walk=function($settings)use(&$walk){foreach($settings as $key=>$value){
            if(is_array($value)){$walk($value);}
            elseif(preg_match('/font_family(?:_(?:tablet|mobile))?$/D',(string)$key)&&is_string($value)&&$value!==''){$this->font_families[\SuiteEdge\Migrator\Style\FontFamily::normalize($value)]=true;}
        }};$walk($this->kit);
    }
    private function scope_styles($html) {
        return preg_replace_callback('~<style\b[^>]*>(.*?)</style>~is',function($m){if(preg_match('/(?:^|[{}])\s*(?:body|html)(?:[\s.#:\[]|\s*\{)/i',$m[1])){throw new \UnexpectedValueException('HTML CSS depends on document body context outside the retained source composition.');}return '<style>'.(new CssScope())->compile($m[1],'.'.$this->scope).'</style>';},$html);
    }
    private function runtime() {
        if(!array_intersect(array_keys($this->types),['nested-accordion','accordion','toggle','tabs'])){return '';}
        $file=dirname(__DIR__,2).'/assets/js/reverse-composition.js';
        $script=is_readable($file)?file_get_contents($file):false;
        if(!$script){throw new \UnexpectedValueException('Independent composition interaction asset is missing.');}
        return '<script>'.$script.'</script>';
    }
}
