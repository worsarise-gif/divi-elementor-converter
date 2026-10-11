<?php
require __DIR__ . '/bootstrap.php';
use SuiteEdge\Migrator\Divi\ShortcodeTokenizer;
use SuiteEdge\Migrator\Import\DiviExportReader;
use SuiteEdge\Migrator\Elementor\DocumentBuilder;
use SuiteEdge\Migrator\Elementor\Classic\Serializer;
use SuiteEdge\Migrator\Elementor\Validator;
$checks = 0;
function check($condition,$name) { global $checks; if (!$condition) { throw new RuntimeException('FAIL: ' . $name); } $checks++; }
function rejects($callback,$name) { try { $callback(); } catch (Throwable $e) { check(true,$name); return; } check(false,$name); }
function wp_get_attachment_url($id) { return $id === 17 ? 'https://example.test/photo.jpg' : false; }
$t = new ShortcodeTokenizer();
$input = '[et_pb_section module_id="hero"][et_pb_section][et_pb_text title="a ] b"]<p>Hello \\ world &amp; friends</p>[/et_pb_text][/et_pb_section][/et_pb_section]';
$nodes = $t->tokenize($input);
check(count($nodes) === 1 && count($nodes[0]['children']) === 1,'same-tag nesting');
check($nodes[0]['original_shortcode'] === $input,'byte-exact raw subtree');
check($nodes[0]['children'][0]['children'][0]['attributes']['title'] === 'a ] b','quoted bracket');
check($nodes[0]['children'][0]['children'][0]['text_content'] === '<p>Hello \\ world &amp; friends</p>','HTML and backslashes');
rejects(function () use ($t) { $t->tokenize('[et_pb_section][/et_pb_row]'); },'mismatched close');
rejects(function () use ($t) { $t->tokenize('[et_pb_section]'); },'unclosed tag');
rejects(function () use ($t) { $t->tokenize('[et_pb_text title="abc]'); },'unclosed attribute');
$code = '[et_pb_code]<script>const x="[et_pb_text]";</script>[/et_pb_code]';
check($t->tokenize($code)[0]['original_shortcode'] === $code,'literal code shortcodes');
check(count($t->tokenize('<p>Before</p>[et_pb_text]Middle[/et_pb_text]<p>After</p>')) === 3,'outside HTML retained');
$reader = new DiviExportReader();
foreach ([['data'=>['5'=>['post_content'=>$input],'6'=>['post_content'=>$input]]],['data'=>['post_content'=>$input]],['data'=>['5'=>$input]],['data'=>$input],['post_content'=>$input]] as $fixture) {
    $result = $reader->read_json(json_encode($fixture)); check($result['post_content'] === $input,'export schema');
}
check(count($reader->read_json(json_encode(['data'=>[['post_content'=>$input],['post_content'=>$input]]]))['layouts']) === 2,'multiple layouts');
foreach (['null','7','[]','{"data":false}','{"data":{"post_content":7}}','{'] as $bad) { rejects(function () use ($reader,$bad) { $reader->read_json($bad); },'invalid export'); }
check(count($reader->read_json(json_encode(['data'=>['wrapper'=>['nested'=>['post_content'=>$input]]]]))['layouts']) === 1,'nested export envelope');
$builder = new DocumentBuilder(); $serializer = new Serializer();
$data = $serializer->serialize($builder->build('[et_pb_text text_font_size="18px" text_font_size_phone="14px" custom_padding="10px|20px|10px|20px" module_id="hello" module_class="a b"]Body[/et_pb_text]'));
$s = $data[0]['settings'];
check($s['typography_font_size'] === ['unit'=>'px','size'=>18.0],'font slider');
check($s['typography_font_size_mobile']['size'] === 14.0,'responsive font');
check($s['_padding']['top'] === '10' && $s['_padding']['unit'] === 'px','dimension units');
check(!isset($s['desktop']) && $s['_element_id'] === 'hello' && strpos($s['_css_classes'],'a b suiteedge-') === 0,'flat settings, IDs and preserved source classes');
check($data === $serializer->serialize($builder->build('[et_pb_text text_font_size="18px" text_font_size_phone="14px" custom_padding="10px|20px|10px|20px" module_id="hello" module_class="a b"]Body[/et_pb_text]')),'determinism');
$unknown = '[et_pb_custom title="hello"][et_pb_text]Nested[/et_pb_text][/et_pb_custom]';
$fallback = $serializer->serialize($builder->build($unknown));
check($fallback[0]['settings']['shortcode'] === $unknown && count($builder->warnings) === 1,'complete unknown fallback');
$fallback = $serializer->serialize($builder->build('[et_pb_text global_module="12"]Global[/et_pb_text]'));
check($fallback[0]['widgetType'] === 'shortcode','unresolved global is preserved');
$fallback = $serializer->serialize($builder->build('[et_pb_button button_text="@ET-DC@abc@" /]'));
check($fallback[0]['widgetType'] === 'shortcode','dynamic fallback');
$fallback = $serializer->serialize($builder->build('[et_pb_contact_form][et_pb_contact_field field_id="Name" /][/et_pb_contact_form]'));
check($fallback[0]['widgetType'] === 'shortcode','Pro guard');
$fallback = $serializer->serialize($builder->build('[et_pb_gallery gallery_ids="999" /]'));
check($fallback[0]['widgetType'] === 'shortcode','foreign gallery IDs retained');
$registry = new \SuiteEdge\Migrator\Mapping\ModuleRegistry();
foreach ($registry->all() as $tag=>$mapping) { check($registry->get_converter($tag) instanceof \SuiteEdge\Migrator\Mapping\BaseConverter,'registered converter ' . $tag); }
$fixtures = [
'et_pb_section'=>'[et_pb_section /]', 'et_pb_row'=>'[et_pb_row /]', 'et_pb_column'=>'[et_pb_column type="1_2" /]',
'et_pb_text'=>'[et_pb_text]Text[/et_pb_text]', 'et_pb_button'=>'[et_pb_button button_text="Go" button_url="/go" /]',
'et_pb_image'=>'[et_pb_image src="https://example.test/photo.jpg" /]', 'et_pb_blurb'=>'[et_pb_blurb title="Blurb" image="https://example.test/photo.jpg"]Body[/et_pb_blurb]',
'et_pb_gallery'=>'[et_pb_gallery gallery_ids="17" /]', 'et_pb_divider'=>'[et_pb_divider /]', 'et_pb_video'=>'[et_pb_video src="https://www.youtube.com/watch?v=abc" /]',
'et_pb_testimonial'=>'[et_pb_testimonial author="Jane"]Review[/et_pb_testimonial]', 'et_pb_tabs'=>'[et_pb_tabs][et_pb_tab title="One"]Body[/et_pb_tab][/et_pb_tabs]',
'et_pb_accordion'=>'[et_pb_accordion][et_pb_accordion_item title="One"]Body[/et_pb_accordion_item][/et_pb_accordion]',
'et_pb_toggle'=>'[et_pb_toggle title="One"]Body[/et_pb_toggle]', 'et_pb_number_counter'=>'[et_pb_number_counter number="42" /]',
'et_pb_map'=>'[et_pb_map][et_pb_map_pin pin_address="London" /][/et_pb_map]',
'et_pb_social_media_follow'=>'[et_pb_social_media_follow][et_pb_social_media_follow_network social_network="facebook" url="https://facebook.com/test" /][/et_pb_social_media_follow]',
'et_pb_code'=>'[et_pb_code]<div>Code</div>[/et_pb_code]', 'et_pb_pricing_tables'=>'[et_pb_pricing_tables /]'];
foreach ($fixtures as $tag=>$fixture) {
    $data = $serializer->serialize($builder->build($fixture,['same_site_assets'=>true])); (new Validator())->validate($data);
    check(($data[0]['widgetType'] ?? '') !== 'shortcode' && !array_filter($builder->warnings,function($warning){return strpos($warning,'supplemental scoped CSS') === false;}),'native fixture ' . $tag);
}
$invalid = $data; $invalid[] = $data[0]; rejects(function () use ($invalid) { (new Validator())->validate($invalid); },'duplicate ID');
$fields = (new \SuiteEdge\Migrator\Forms\DiviContactFormParser())->parse_fields($t->tokenize('[et_pb_contact_field field_id="choice" field_type="select" select_options="Red|Blue" /]'));
check($fields[0]['field_options'] === "Red\nBlue",'real form options');
$rewrite = new \SuiteEdge\Migrator\Assets\UrlRewriter();
check($rewrite->rewrite('https://old.test/a?b=1#c','https://old.test','https://new.test') === 'https://new.test/a?b=1#c','URL rewrite');
check($rewrite->rewrite('https://old.test.evil/a','https://old.test','https://new.test') === 'https://old.test.evil/a','URL boundary');
$unknown = $serializer->serialize($builder->build('[et_pb_text unusual_design_setting="on"]Body[/et_pb_text]'));
check(count($builder->warnings) === 1,'unmapped setting warning');
$css = $serializer->serialize($builder->build('[et_pb_text custom_css_main="color:red;"]Body[/et_pb_text]'));
check($css[0]['widgetType'] === 'text-editor' && $css[1]['elType'] === 'container' && $css[1]['elements'][0]['widgetType'] === 'html' && strpos($css[1]['elements'][0]['settings']['html'],'color:red;') !== false,'self-contained CSS in a valid container without Pro');
check(strpos($css[1]['elements'][0]['settings']['html'],'.elementor-element.'.$css[0]['settings']['_css_classes']) !== false && strpos($css[1]['elements'][0]['settings']['html'],'.elementor-element-'.$css[0]['id']) === false,'CSS scoped to import-stable custom class');
$unsafe_css = $serializer->serialize($builder->build('[et_pb_text custom_css_main="</style><script>alert(1)</script>"]Body[/et_pb_text]'));
check($unsafe_css[0]['widgetType'] === 'shortcode','markup cannot escape generated style');
$weighted = $serializer->serialize($builder->build('[et_pb_text text_font="Montserrat|600||on|||||"]Body[/et_pb_text]'));
check($weighted[0]['settings']['typography_font_weight'] === '600' && $weighted[0]['settings']['typography_text_transform'] === 'uppercase','numeric weights and uppercase retained');
$nested_dynamic = $serializer->serialize($builder->build('[et_pb_section][et_pb_button button_url="@ET-DC@bad@" /][/et_pb_section]'));
check($nested_dynamic[0]['elType'] === 'container' && $nested_dynamic[0]['elements'][0]['widgetType'] === 'shortcode','unresolved child does not replace parent section');
$token = '@ET-DC@'.base64_encode(json_encode(['content'=>'post_link_url_page','settings'=>['post_id'=>'123']])).'@';
$mapped = $serializer->serialize($builder->build('[et_pb_button button_url="'.$token.'" /]',['link_map'=>['123'=>'/contact/']]));
check($mapped[0]['settings']['link']['url'] === '/contact/','explicit dynamic URL mapping');
$inactive = (new \SuiteEdge\Migrator\Normalize\AttributeNormalizer())->normalize(['text_font_size_phone'=>'14px','text_font_size_last_edited'=>'off|desktop','filter_blur__hover'=>'2px','filter_blur__hover_enabled'=>'off|desktop']);
check(!isset($inactive['text_font_size_phone'],$inactive['filter_blur__hover']),'inactive device and hover values removed');
$preset = (new \SuiteEdge\Migrator\Divi\PresetResolver())->resolve_export('et_pb_row',['width'=>'90%'],['et_pb_row'=>['default'=>'_initial','presets'=>['_initial'=>['version'=>'4.7.1','settings'=>['width'=>'80%','width_phone'=>'90%']]]]]);
check($preset['width'] === '90%' && $preset['width_phone'] === '90%','real Divi4 default preset explicit override');
$responsive = $serializer->serialize($builder->build('[et_pb_text content_tablet="Tablet" content_phone="Mobile"]Desktop[/et_pb_text]'));
check(strpos($responsive[0]['settings']['editor'],'Desktop') !== false && strpos($responsive[0]['settings']['editor'],'Tablet') !== false && strpos($responsive[0]['settings']['editor'],'Mobile') !== false,'all device-specific content retained');
check(strpos($responsive[1]['elements'][0]['settings']['html'],'max-width:767px') !== false && strpos($responsive[1]['elements'][0]['settings']['html'],'display:none') !== false,'responsive content uses scoped visibility CSS');
$different = $serializer->serialize($builder->build('[et_pb_text]Different document[/et_pb_text]'));
check($different[0]['id'] !== $weighted[0]['id'],'different documents do not share scoped CSS IDs');
$tooltip = $serializer->serialize($builder->build('[et_pb_image src="https://example.test/image.png" title_text="Tooltip only" /]'));
check($tooltip[0]['settings']['caption_source'] === 'none' && $tooltip[0]['settings']['_suiteedge_image_title'] === 'Tooltip only','image title cannot invent a visible caption');
$sticky = $serializer->serialize($builder->build('[et_pb_text sticky_position="top"]Body[/et_pb_text]'));
check($sticky[0]['widgetType'] === 'shortcode','sticky preservation');
check((new \SuiteEdge\Migrator\Import\DiviExportDetector())->detect('null')['valid'] === false,'export detector rejects');
check((new \SuiteEdge\Migrator\Import\DiviExportDetector())->detect(json_encode(['data'=>$input]))['valid'] === true,'export detector detects');
check((new \SuiteEdge\Migrator\Divi\ShortcodeParser())->parse($input)[0]['type'] === 'et_pb_section','parser facade');
check(count((new \SuiteEdge\Migrator\Reports\ModuleCoverageReport())->build($input)['modules']) === 2,'coverage counts');
check((new \SuiteEdge\Migrator\Model\ResponsiveValue(['desktop'=>10,'mobile'=>5]))->get('tablet') === 10,'responsive inheritance');
rejects(function () { (new \SuiteEdge\Migrator\Elementor\Atomic\Serializer())->serialize([]); },'Atomic adapter explicit rejection');
$template = json_decode((new \SuiteEdge\Migrator\Reports\ExportReport())->template($unknown),true);
check($template['version'] === '0.4' && isset($template['content'][0]),'importable template envelope');
$raw = '[et_pb_tabs][et_pb_tab title="One"][et_pb_text]Nested[/et_pb_text][/et_pb_tab][/et_pb_tabs]';
check($serializer->serialize($builder->build($raw))[0]['settings']['shortcode'] === $raw,'nested tabs preserved');
$global = $serializer->serialize($builder->build('[et_pb_text global_module="12" /]', ['layouts'=>[['source_id'=>'12','post_content'=>'[et_pb_text]Resolved global[/et_pb_text]']]]));
check($global[0]['widgetType'] === 'text-editor' && $global[0]['settings']['editor'] === 'Resolved global','included global layout expansion');
$cycle = $serializer->serialize($builder->build('[et_pb_text global_module="12" /]', ['layouts'=>[['source_id'=>'12','post_content'=>'[et_pb_text global_module="12" /]']]]));
check($cycle[0]['widgetType'] === 'shortcode','cyclic global preserved');

$grid=$serializer->serialize($builder->build('[et_pb_section][et_pb_row gutter_width="4"][et_pb_column type="3_5" /][et_pb_column type="2_5" /][/et_pb_row][/et_pb_section]',['source_origin'=>'https://suiteedge.com']));
$row=$grid[0]['elements'][0];
check(abs($row['elements'][0]['settings']['width']['size']-56.8)<0.001&&abs($row['elements'][1]['settings']['width']['size']-35.2)<0.001,'Divi spanning columns include internal gutters');
check($row['settings']['padding']['right']==='0'&&$row['elements'][1]['settings']['flex_gap']['size']===0,'container defaults do not add Elementor padding or widget gaps');
check($row['elements'][1]['settings']['width_tablet']['size']===100&&$row['settings']['flex_direction_tablet']==='column','tablet columns stack at target breakpoint');
$heads=$serializer->serialize($builder->build('[et_pb_section][et_pb_text header_font="Montserrat|800|||||||" header_text_color="#ffffff" header_font_size="55px"]<h1>Hero</h1>[/et_pb_text][/et_pb_section]'));
$style=end($heads[0]['elements'])['settings']['html'];
check(strpos($style,' h1{')!==false&&strpos($style,'.elementor-widget-container h1')===false,'heading style supports optimized and legacy markup');
check($heads[0]['elements'][0]['settings']['typography_font_family']==='Montserrat','heading font registered through native control');
$profile=new \SuiteEdge\Migrator\Style\SourceThemeProfile();
rejects(function()use($profile){$profile->validate(['body_font'=>'x</style>']);},'theme profile cannot inject markup');
$partial=$serializer->serialize($builder->build('[et_pb_section][et_pb_row custom_margin="75px||0px||false|false" /][/et_pb_section]'));
$partialStyle=end($partial[0]['elements'])['settings']['html'];
check(strpos($partialStyle,'margin-top:75px!important;margin-bottom:0px!important;')!==false&&strpos($partialStyle,'margin-left:auto;margin-right:auto;')!==false,'partial row spacing preserves source priority and inherited centering');
$photo=$serializer->serialize($builder->build('[et_pb_image src="https://example.test/photo.jpg" border_radii="on|15px|15px|15px|15px" /]'));
check($photo[0]['settings']['image_border_radius']['top']==='15'&&!isset($photo[0]['settings']['_border_radius']),'rounded source image uses actual image control');
$button=$serializer->serialize($builder->build('[et_pb_button button_text="Go" custom_padding="8px|20px|8px|20px" button_border_width="2px" /]'));
check($button[0]['settings']['text_padding']['top']==='8'&&!isset($button[0]['settings']['_padding'],$button[0]['settings']['_border_width']),'button spacing and border apply to button surface once');
check($row['settings']['width']['size']===80.0&&$row['settings']['width_mobile']['size']===80.0,'default row width retained on mobile without implicit Elementor inheritance');
echo 'PASS: ' . $checks . ' standalone assertions on PHP ' . PHP_VERSION . PHP_EOL;
