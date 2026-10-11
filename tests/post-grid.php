<?php
require __DIR__.'/bootstrap.php';
$checks=0;$check=function($ok,$name)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$name);}$checks++;};
$reject=function($fn,$name)use($check){try{$fn();}catch(UnexpectedValueException $e){$check(true,$name);return;}$check(false,$name);};
$query=new SuiteEdge\Migrator\Elementor\Posts\GridQuery();
$args=$query->args(['posts_per_page'=>3,'post_offset'=>2,'show_pagination'=>'yes'],2);
$check($args['offset']===5&&$args['post_status']==='publish'&&$args['post_type']==='post','offset includes page and queries published destination posts only');
$check($query->pages(10,['posts_per_page'=>3,'post_offset'=>2])===3,'offset removed from total pages');
$check($query->pages(1,['posts_per_page'=>3,'post_offset'=>2])===0,'offset beyond total has no phantom page');
$args=$query->args(['category_slugs'=>'alpha, beta,alpha']);$check($args['tax_query'][0]['terms']===['alpha','beta']&&$args['tax_query'][0]['field']==='slug','categories explicitly use destination slugs');
$args=$query->args(['post_order'=>'popular']);$check($args['orderby']['comment_count']==='DESC'&&isset($args['orderby']['ID']),'popular query has deterministic tie breaker');
$check($query->args(['post_order'=>'random','show_pagination'=>''])['no_found_rows'],'random allowed without pagination');
foreach([0,-1,101,'1.5',[],INF,'12 UNION SELECT']as$count){$reject(function()use($query,$count){$query->args(['posts_per_page'=>$count]);},'invalid count bounded');}
foreach([-1,10001,'1e3',[]]as$offset){$reject(function()use($query,$offset){$query->args(['post_offset'=>$offset]);},'invalid offset bounded');}
foreach([0,10001,[], '2e2']as$page){$reject(function()use($query,$page){$query->args([],$page);},'invalid page bounded');}
foreach(['<script>','x) OR 1=1',[],str_repeat('a',2001)]as$slugs){$reject(function()use($query,$slugs){$query->args(['category_slugs'=>$slugs]);},'category query input rejected');}
$reject(function()use($query){$query->args(['post_order'=>'random']);},'random paginated results protected');
$b=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$build=function($raw)use($b){return(new SuiteEdge\Migrator\Elementor\Classic\Serializer())->serialize($b->build($raw));};
$raw='[dgbm_blog_module show_excerpt="off" show_author="off" show_more="on" equal_height="on" space_between="40px" space_between_tablet="20px" space_between_phone="0px" title_font="Montserrat|600|||||||" title_font_size="22px" title_font_size_phone="18px" title_line_height="1.3em" title_margin="20px|16px|20px|16px|false|true" button_padding="5px|12px|7px|14px|false|false" button_padding_phone="0px|10px|0px|10px|false|false" button_bg_color="#FF9B40" button_fullwidth="on" read_more_text_color__hover="#FFFFFF" border_radii_image="off|24px|24px||" border_width_all_image="7px" border_radii_post_item="on|24px|24px|24px|24px" transform_scale__hover_enabled="off|hover" transform_scale__hover="101%|101%" /]';
$data=$build($raw);$s=$data[0]['settings'];
$check($data[0]['widgetType']==='ace-post-grid','DiviGear grid converts without shortcode fallback');
$check($s['show_excerpt']===''&&$s['show_author']===''&&$s['show_read_more']==='yes','source content switches preserved');
$check($s['columns']==='3'&&$s['columns_tablet']==='2'&&$s['columns_mobile']==='1','omitted grid defaults are explicit');
$check($s['posts_per_page']===12&&$s['show_pagination']==='yes','omitted query defaults are explicit');
$check($s['grid_gap']['size']===40.0&&$s['grid_gap_tablet']['size']===20.0&&$s['grid_gap_mobile']['size']===0.0,'per-device gaps include zero');
$check($s['title_font_family']==='Montserrat'&&$s['title_font_weight']==='600'&&$s['title_line_height']['unit']==='em','native typography retains family weight and units');
$check($s['title_font_size_mobile']['size']===18.0,'mobile typography preserved');
$check($s['image_radius']['bottom']==='0'&&$s['image_border_width']['left']==='7'&&$s['card_radius']['top']==='24','image and card borders are distinct');
$check($s['button_padding']['bottom']==='7'&&$s['button_padding']['left']==='14'&&$s['button_padding_mobile']['top']==='0','button padding includes all source sides/devices');
$check($s['read_more_background']==='#FF9B40'&&$s['read_more_hover_color']==='#FFFFFF','button colors remain native controls');
$check(count($b->warnings)===1&&strpos($b->warnings[0],'remain active')!==false,'runtime dependency disclosed without lost property warnings');
$check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data),'data is structurally valid');
foreach(['layout="masonry"','layout="fullwidth"','post_type="product"','include_categories="32"','related_post="on"','meta_position="top"','posts_type="unknown"','posts_type="random"','columns="6"','space_between="-2px"','title_text_color="red;display:none"','posts_number="9999"']as$attr){$data=$build('[dgbm_blog_module '.$attr.' /]');$check($data[0]['widgetType']==='shortcode'&&strpos($b->warnings[0],'Post grid:')!==false,'unsupported or malformed configuration remains protected: '.$attr);}
$data=$build('[dgbm_blog_module mystery_query="custom" /]');$check((bool)array_filter($b->warnings,function($s){return strpos($s,'mystery_query')!==false;}),'unknown attributes are still reported');
$data=$build('[dgbm_future_module /]');$check($data[0]['widgetType']==='shortcode','other third-party modules are not claimed supported');
$node=new SuiteEdge\Migrator\Model\Node('dgbm_blog_module','ace-post-grid');$node->content=['title_font_family'=>'Montserrat','title_font_family_mobile'=>'Inter'];$check(SuiteEdge\Migrator\Style\FontFamily::in_node($node)===['Montserrat','Inter'],'responsive-only font families receive availability checks');
echo 'PASS: '.$checks.' post-grid conversion and query safety assertions'.PHP_EOL;
