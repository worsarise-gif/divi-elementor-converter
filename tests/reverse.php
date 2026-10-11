<?php
require __DIR__.'/bootstrap.php';
use SuiteEdge\Migrator\Reverse\Document;
use SuiteEdge\Migrator\Reverse\Shortcodes;
use SuiteEdge\Migrator\Divi\ShortcodeTokenizer;
function get_post($id){return (int)$id===17?(object)['ID'=>17,'post_type'=>'attachment']:null;}
function wp_attachment_is_image($id){return (int)$id===17;}
function wp_get_nav_menu_object($id){return (int)$id===32?(object)['term_id'=>32]:false;}
function get_term($id,$taxonomy){return (int)$id===7?(object)['term_id'=>7]:false;}
function is_wp_error($value){return false;}
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$label);}$checks++;};
$reject=function($fn,$label)use($check){try{$fn();}catch(Throwable $e){$check(true,$label);return;}$check(false,$label);};
$widget=function($type,$settings=[],$id='w1'){return ['id'=>$id,'elType'=>'widget','widgetType'=>$type,'settings'=>$settings,'elements'=>[]];};
$container=function($id,$nodes,$settings=[]){return ['id'=>$id,'elType'=>'container','settings'=>$settings,'elements'=>$nodes];};
$convert=function($type,$settings=[])use($widget){return (new Document())->convert(['data'=>[$widget($type,$settings)]]);};
$fixtures=[
    'heading'=>[['title'=>'Team "Ace" & friends','header_size'=>'h2'],'et_pb_heading'],
    'text-editor'=>[['editor'=>'<p>Editable <strong>body</strong>.</p>'],'et_pb_text'],
    'button'=>[['text'=>'Contact','link'=>['url'=>'/contact/','is_external'=>'on']],'et_pb_button'],
    'image'=>[['image'=>['url'=>'https://example.test/a.jpg','id'=>17],'caption_source'=>'none','image_size'=>'full'],'et_pb_image'],
    'image-box'=>[['image'=>['url'=>'https://example.test/a.jpg'],'title_text'=>'Blurb','description_text'=>'Body'],'et_pb_blurb'],
    'divider'=>[['style'=>'solid','weight'=>['size'=>2,'unit'=>'px']],'et_pb_divider'],
    'spacer'=>[['space'=>['size'=>24,'unit'=>'px']],'et_pb_divider'],
    'video'=>[['video_type'=>'youtube','youtube_url'=>'https://www.youtube.com/watch?v=123'],'et_pb_video'],
    'google_maps'=>[['address'=>'Test address','zoom'=>['size'=>12]],'et_pb_code'],
    'testimonial'=>[['testimonial_name'=>'Ace','testimonial_content'=>'Excellent.','testimonial_image'=>['url'=>'https://example.test/a.jpg']],'et_pb_testimonial'],
    'counter'=>[['title'=>'Clients','ending_number'=>23,'starting_number'=>0,'suffix'=>'%'],'et_pb_number_counter'],
    'progress'=>[['title'=>'Progress','percent'=>['size'=>0],'bar_color'=>'#007c91','bar_bg_color'=>'#ffffff'],'et_pb_counters'],
    'social-icons'=>[['social_icon_list'=>[['_id'=>'i1','social_icon'=>['value'=>'fab fa-facebook'],'link'=>['url'=>'https://facebook.com/']]]],'et_pb_social_media_follow'],
    'tabs'=>[['tabs'=>[['_id'=>'t1','tab_title'=>'First','tab_content'=>'<p>Answer</p>']]],'et_pb_tabs'],
    'accordion'=>[['tabs'=>[['_id'=>'t1','tab_title'=>'Question','tab_content'=>'Answer']]],'et_pb_accordion'],
    'toggle'=>[['tabs'=>[['_id'=>'t1','tab_title'=>'Question','tab_content'=>'Answer']]],'et_pb_accordion'],
    'html'=>[['html'=>'<aside class="local-note">Local code</aside>'],'et_pb_code'],
    'nav-menu'=>[['menu'=>'32'],'et_pb_menu'],
    'wp-widget-nav_menu'=>[['nav_menu'=>32],'et_pb_menu'],
    'price-table'=>[['heading'=>'Standard','price'=>'99','features_list'=>[['_id'=>'f1','item_text'=>'Included']]],'et_pb_pricing_tables'],
    'call-to-action'=>[['title'=>'Get started','button'=>'Talk','description'=>'Body'],'et_pb_cta'],
    'image-gallery'=>[['wp_gallery'=>[['id'=>17,'url'=>'https://example.test/a.jpg']],'gallery_columns'=>3],'et_pb_gallery'],
    'ace-post-grid'=>[['layout'=>'list','category_ids'=>'7','posts_per_page'=>5],'et_pb_blog'],
];
foreach($fixtures as $type=>$fixture){$out=$convert($type,$fixture[0]);$check(!$out['blockers'],$type.' accepted: '.implode(' ',$out['blockers']));(new Shortcodes())->validate($out);$check(strpos($out['content'],'['.$fixture[1])!==false,$type.' native target');$check((bool)$out['coverage'],$type.' coverage recorded');}
$padding=['unit'=>'px','top'=>'0','right'=>'17','bottom'=>'9','left'=>'23','isLinked'=>false];
$out=$convert('button',['text'=>'Padding','text_padding'=>$padding,'text_padding_tablet'=>array_replace($padding,['unit'=>'%']),'text_padding_mobile'=>array_replace($padding,['top'=>'2']),'_margin'=>array_replace($padding,['left'=>'-12']),'hide_desktop'=>'hidden-desktop','typography_font_family'=>'Montserrat','typography_font_weight'=>'600','typography_font_style'=>'italic','typography_text_decoration'=>'none','typography_font_size'=>['size'=>14,'unit'=>'px']]);
$check(!$out['blockers'],'responsive button styles accepted');$nodes=(new ShortcodeTokenizer())->tokenize($out['content']);$attrs=$nodes[0]['children'][0]['children'][0]['children'][0]['attributes'];
$check($attrs['custom_button_padding']==='0px|17px|9px|23px','all four button sides and zero preserved');$check($attrs['custom_button_padding_tablet']==='0%|17%|9%|23%','tablet units preserved');$check($attrs['custom_button_padding_phone']==='2px|17px|9px|23px','mobile sides preserved');$check(strpos($attrs['custom_margin'],'-12px')!==false,'negative margin preserved');$check($attrs['disabled_on']==='off|off|on','Divi visibility device order');$check($attrs['button_font']==='Montserrat|600|on|off|off|off','font family, weight and flags');$check($attrs['button_text_size']==='14px','button font size native field');
$check(strpos($convert('heading',$fixtures['heading'][0])['content'],'&quot;Ace&quot; &amp; friends')!==false,'quoted title cannot escape its attribute');
$literal=$convert('text-editor',['editor'=>'Literal [et_pb_section] nested text [/et_pb_section]']);$check(!$literal['blockers']&&strpos($literal['content'],'&#91;et_pb_section]')!==false,'literal builder text cannot inject a section');
foreach(['form','posts','loop-grid','woocommerce-product-title','nested-tabs','nested-accordion','third-party-widget'] as $type){$out=$convert($type,['content'=>'Protected']);$check((bool)$out['blockers'],$type.' blocks Apply');$reject(function()use($out){(new Shortcodes())->validate($out);},$type.' saved output cannot bypass blockers');}
foreach([
    ['button',['text'=>'Unsafe','link'=>['url'=>'javascript:alert(1)']]],
    ['button',['text'=>'Styles','custom_css'=>'selector{position:fixed}']],
    ['button',['text'=>'Dynamic','__dynamic__'=>['text'=>'token']]],
    ['html',['html'=>'<style>.elementor-element {color:red}</style>']],
    ['html',['html'=>'[/et_pb_code][et_pb_section]']],
    ['image-gallery',['wp_gallery'=>[['id'=>18]]]],
    ['nav-menu',['menu'=>33]],
    ['counter',['ending_number'=>10,'prefix'=>'$']],
    ['ace-post-grid',['post_offset'=>2]],
    ['button',['text_padding'=>['unit'=>'unsupported','top'=>4]]],
] as $test){try{$out=$convert($test[0],$test[1]);$check((bool)$out['blockers'],'unsafe/unsupported '.$test[0].' protected');}catch(UnexpectedValueException $e){$check(true,'invalid dimension protected');}}
$data=[$container('section',[$container('row',[$container('c1',[$widget('heading',['title'=>'Left'])],['width'=>['unit'=>'%','size'=>50]]),$container('c2',[$widget('button',['text'=>'Right'],'w2')],['width'=>['unit'=>'%','size'=>50]])],['flex_direction'=>'row','flex_gap'=>['unit'=>'%','size'=>5.5]])],['flex_direction'=>'column'])];
$out=(new Document())->convert(['data'=>$data]);$check(!$out['blockers'],'section/row/two-column composition');$check(substr_count($out['content'],'type="1_2"')===2,'native half columns');$check(strpos($out['content'],'gutter_width="3"')!==false,'equivalent Divi gutter');
$bad=$data;$bad[0]['elements'][0]['settings']['display']='grid';$out=(new Document())->convert(['data'=>$bad]);$check((bool)$out['blockers'],'grid settings cannot disappear');
$bad=$data;$bad[0]['elements'][0]['elements'][1]['elements'][0]['id']='w1';$reject(function()use($bad){(new Document())->convert(['data'=>$bad]);},'duplicate IDs rejected');
$bad=$data;$bad[0]['elType']='e-div-block';$reject(function()use($bad){(new Document())->convert(['data'=>$bad]);},'Atomic element rejected');
$kit=['system_colors'=>[['_id'=>'primary','color'=>'#26afd1']],'system_typography'=>[['_id'=>'primary','typography_font_family'=>'Inter','typography_font_weight'=>'700']]];
$out=(new Document())->convert(['data'=>[$widget('heading',['title'=>'Global','__globals__'=>['title_color'=>'globals/colors?id=primary','typography_typography'=>'globals/typography?id=primary']])],'kit_settings'=>$kit]);$check(!$out['blockers'],'global kit styles resolved');$check(strpos($out['content'],'Inter|700|')!==false&&strpos($out['content'],'#26afd1')!==false,'resolved global values reach native fields');
$out=(new Document())->convert(['data'=>[$widget('heading',['title'=>'Global','__globals__'=>['title_color'=>'globals/colors?id=missing']])],'kit_settings'=>$kit]);$check((bool)$out['blockers'],'missing global style blocks');
$reject(function(){(new Shortcodes())->module('et_pb_button',['button_text'=>['array']]);},'non-scalar attributes rejected');
$out=$convert('google_maps',['address'=>'City & streets','height'=>['size'=>400,'unit'=>'px'],'height_tablet'=>['size'=>300,'unit'=>'px'],'height_mobile'=>['size'=>200,'unit'=>'px'],'zoom'=>['size'=>12]]);$check(!$out['blockers']&&strpos($out['content'],'maps.google.com/maps?q=City%20%26%20streets')!==false,'map uses the official embed URL with encoded address');$check(strpos($out['content'],'max-width:1024px')!==false&&strpos($out['content'],'height:200px')!==false,'iframe responsive height survives independently of Divi defaults');
$out=(new Document())->convert(['data'=>[$widget('heading',['title'=>'Title'])],'page_settings'=>['hide_title'=>'yes']]);$check(!$out['blockers']&&strpos($out['content'],'data-ace-reverse-hide-title="1"')!==false,'page title visibility has an explicit destination presentation marker');
$out=$convert('button',['text'=>'Z','_z_index'=>'highest']);$check((bool)$out['blockers'],'invalid z-index is not coerced to zero');
$out=$convert('image-box',['image'=>['url'=>'https://example.test/a.jpg'],'position'=>'right']);$check((bool)$out['blockers'],'right-positioned image is not silently moved above the blurb');
$out=$convert('html',['html'=>'<p>[ET_PB_BUTTON button_text="Unexpected"]</p>']);$check((bool)$out['blockers'],'code cannot introduce nested builder modules');
$bad=$data;$bad[0]['elements'][0]['settings']['flex_gap']=['unit'=>'px','size'=>0,'column'=>'0','row'=>'30'];$out=(new Document())->convert(['data'=>$bad]);$check((bool)$out['blockers'],'separate row gap cannot vanish under a zero column gap');
$bad=$data;$bad[0]['elements'][0]['settings']['flex_gap_tablet']=['unit'=>'px','column'=>'0','row'=>'30'];$out=(new Document())->convert(['data'=>$bad]);$check((bool)$out['blockers'],'responsive row gap is inspected when size is absent');
$bad=$data;$bad[0]['settings']['content_width']='boxed';$out=(new Document())->convert(['data'=>$bad]);$check((bool)$out['blockers'],'boxed inner-wrapper width cannot be silently consumed');
$check(strpos($convert('heading',['title'=>'Full'])['content'],'width="100%" max_width="100%" custom_padding="0px|0px|0px|0px"')!==false,'synthetic rows avoid additional boxed geometry and padding');
$authored=$data;$authored[0]['elements'][0]['settings']['width']=['unit'=>'%','size'=>80];$out=(new Document())->convert(['data'=>$authored]);$check(!$out['blockers']&&strpos($out['content'],'width="80%"')!==false,'authored row width overrides synthetic row defaults');
echo 'PASS: '.$checks." reverse semantic and safety assertions\n";
