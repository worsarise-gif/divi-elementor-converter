<?php
require __DIR__.'/bootstrap.php';
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$label);}$checks++;};
$b=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$s=new SuiteEdge\Migrator\Elementor\Classic\Serializer();
$build=function($content,$options=[])use($b,$s){return$s->serialize($b->build($content,$options));};
$data=$build('[et_pb_blog /]');$v=$data[0]['settings'];
$check($data[0]['widgetType']==='ace-post-grid'&&$v['layout']==='list','core Blog has separate native list adapter');
$check($v['columns']==='1'&&$v['columns_tablet']==='1'&&$v['columns_mobile']==='1','list always single column');
$check($v['posts_per_page']===10&&$v['excerpt_length']===270&&$v['use_excerpt']==='yes','source query and character excerpt defaults explicit');
$check($v['show_image']==='yes'&&$v['show_comments']===''&&$v['show_pagination']==='yes','source visibility defaults');
$check($v['card_color']==='transparent','list does not paint a grid tile background');
$data=$build('[et_pb_blog fullwidth="off" posts_number="4" offset_number="0" header_level="h3" header_font="PT Sans|700" header_font_size="18px" header_font_size_tablet="16px" header_font_size_phone="14px" body_font="Arial|400" body_text_color="#123456" meta_font="Inter|550||on" show_author="off" show_comments="on" masonry_tile_background_color="rgba(0,0,0,0)" background_color="#abcdef" border_width_all="0px" /]');$v=$data[0]['settings'];
$check($v['layout']==='masonry'&&$v['posts_per_page']==='4'&&$v['post_offset']==='0','grid query preserves explicit count and zero');
$check($v['title_tag']==='h3'&&$v['title_font_family']==='PT Sans'&&$v['title_font_weight']==='700','title level and font native');
$check($v['title_font_size_tablet']['size']===16.0&&$v['title_font_size_mobile']['size']===14.0,'active responsive source typography');
$check($v['card_color']==='rgba(0,0,0,0)'&&$v['_background_color']==='#abcdef','module and tile backgrounds separate');
$check($v['card_border_width']['left']==='0'&&$v['text_color']==='#123456','card border and body color native');
$check($v['show_author']===''&&$v['show_comments']==='yes'&&$v['meta_font_weight']==='550','metadata values preserved');
$check(count($b->warnings)===1&&strpos($b->warnings[0],'requires Ace Migrator')!==false,'runtime dependency only warning for mapped fields');
$check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data),'standalone serialization validates');
$data=$build('[et_pb_blog header_font_size_tablet="90px" header_font_size_phone="80px" header_font_size_last_edited="off|phone" /]');$check(!isset($data[0]['settings']['title_font_size_tablet'],$data[0]['settings']['title_font_size_mobile']),'inactive device values inherit');
foreach(['post_type="product"','use_current_loop="on"','show_content="on"','include_categories="32"','include_categories="-1"','include_categories="0"','include_categories="1 OR 1"','fullwidth="unknown"','header_level="script"','show_more="bad"','posts_number="101"','offset_number="-1"','excerpt_length="0"','use_overlay="on"','link_option_url="https://example.test"','body_text_color="red;display:none"'] as $attrs){$raw='[et_pb_blog '.$attrs.' /]';$data=$build($raw);$check($data[0]['widgetType']==='shortcode'&&$data[0]['settings']['shortcode']===$raw,'unsupported source configuration protected: '.$attrs);}
$data=$build('[et_pb_blog mystery_property="value" /]');$check((bool)array_filter($b->warnings,function($v){return strpos($v,'mystery_property')!==false;}),'unknown attributes remain reported');
$raw='[et_pb_blog][et_pb_text]Must remain present[/et_pb_text][/et_pb_blog]';$data=$build($raw);$check($data[0]['widgetType']==='shortcode'&&$data[0]['settings']['shortcode']===$raw,'unexpected nested content never disappears');
$data=$build('[et_pb_blog body_text_color_phone="red;display:none" /]');$check($data[0]['widgetType']==='shortcode','invalid responsive colors protected');
$data=$build('[et_pb_blog box_shadow_style="preset1" /]');$check($data[0]['widgetType']==='shortcode','unaccepted card shadow never silently disappears');
$data=$build('[et_pb_text theme_builder_area="post_content"]<p>Post body</p>[/et_pb_text]');$check(!$b->warnings,'post-content export marker is metadata');
$data=$build('[et_pb_column custom_padding__hover="|||"][et_pb_text]Body[/et_pb_text][/et_pb_column]');$check(!$b->warnings,'empty hover padding emits no false loss warning');
$dir=getenv('ACE_BLOG_FIXTURE_DIR');
if($dir){
    $candidate=json_decode(file_get_contents($dir.'/next-candidate-et_pb_blog-nodes.json'),true,128,JSON_THROW_ON_ERROR)[0];
    $raw=$candidate['node']['original_shortcode'];$data=$build($raw,['presets'=>$candidate['presets']]);$v=$data[0]['settings'];
    $check($v['layout']==='masonry'&&$v['title_tag']==='h3'&&$v['title_font_family']==='PT Sans','official Divi 4.8.2 preset resolved');
    $check($v['posts_per_page']==='1'&&$v['show_author']===''&&$v['show_date']==='','local source attributes override preset correctly');
    $e=(new SuiteEdge\Migrator\Import\DiviExportReader())->read_json(file_get_contents($dir.'/private-post-fixture.json'));$data=$build($e['post_content'],['presets'=>$e['presets'],'layouts'=>$e['layouts']]);
    $check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data)&&!$b->warnings,'real individual Divi 4.16 post converts without warnings');
}
echo 'PASS: '.$checks.' core Blog and individual post conversion assertions'.PHP_EOL;
