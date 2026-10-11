<?php
require __DIR__.'/bootstrap.php';
define('ELEMENTOR_PATH',getenv('SUITEEDGE_ELEMENTOR_PATH') ?: dirname(__DIR__,2).'/.qa-runtime/wordpress/wp-content/plugins/elementor/');
$GLOBALS['qa_footer_home']='https://destination.example/site/';
function home_url($path='') { return rtrim($GLOBALS['qa_footer_home'],'/').$path; }
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok)throw new RuntimeException('FAIL: '.$label);$checks++;};
$token=function($content='home_url',$settings=[],$dynamic=true){return '@ET-DC@'.base64_encode(json_encode(['dynamic'=>$dynamic,'content'=>$content,'settings'=>$settings])).'@';};
$resolver=new SuiteEdge\Migrator\Dynamic\SiteLinkResolver();$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$serializer=new SuiteEdge\Migrator\Elementor\Classic\Serializer();
$check($resolver->resolve('et_pb_image','url',$token())==='https://destination.example/site/','frontend subdirectory home retained');
foreach([['et_pb_button','button_url'],['et_pb_blurb','url'],['et_pb_icon','link_option_url']]as$pair)$check($resolver->resolve($pair[0],$pair[1],$token())===home_url('/'),'supported native link '.$pair[0]);
foreach([['et_pb_image','src',$token()],['et_pb_text','url',$token()],['et_pb_image','url','prefix'.$token()],['et_pb_image','url',$token().'suffix'],['et_pb_image','url',$token('site_url')],['et_pb_image','url',$token('home_url',['before'=>'bad'])],['et_pb_image','url',$token('home_url',[],false)],['et_pb_image','url','@ET-DC@broken@']]as$case)$check($resolver->resolve(...$case)===null,'unknown/malformed binding stays unresolved');
$GLOBALS['qa_footer_home']='javascript:alert(1)';$check($resolver->resolve('et_pb_image','url',$token())===null,'unsafe destination rejected');$GLOBALS['qa_footer_home']='https://user:password@example.test';$check($resolver->resolve('et_pb_image','url',$token())===null,'credentials rejected');$GLOBALS['qa_footer_home']='https://destination.example/site/';
$build=function($raw)use($builder,$serializer){return $serializer->serialize($builder->build($raw,['source_origin'=>'https://origin.example']));};
$image=$build('[et_pb_image src="https://origin.example/logo.png" url="'.$token().'" /]');$check($image[0]['widgetType']==='image'&&$image[0]['settings']['link']['url']===home_url('/')&&!$builder->warnings,'logo home link native and warning-free');
$glyph='&#xf879;||fa||900';$icon=$build('[et_pb_button button_text="Call" button_url="tel:123" button_icon="'.$glyph.'" button_icon_color="#FFFFFF" button_icon_placement="left" button_on_hover="off" /]');
$check($icon[0]['settings']['selected_icon']['value']==='fas fa-phone-alt','actual exported phone glyph');$check($icon[0]['settings']['icon_align']==='left','physical icon placement');$check(!$builder->warnings,'handled icon settings produce no mapping warnings');
$css=function($data){$s='';$walk=function($nodes)use(&$walk,&$s){foreach($nodes as$n){if(($n['widgetType']??'')==='html')$s.=$n['settings']['html'];$walk($n['elements']);}};$walk($data);return$s;};
$check(strpos($css($icon),'color:#FFFFFF;fill:currentColor;')!==false,'icon color portable for SVG/font');
$disabled=$build('[et_pb_button button_use_icon="off" button_icon="unsupported" button_icon_color="unsafe;color:red" button_on_hover="invalid" /]');$check($disabled[0]['settings']['selected_icon']['value']===''&&!$builder->warnings,'disabled saved icon never re-enabled');
$hover=$build('[et_pb_button button_icon="'.$glyph.'" button_on_hover="on" button_icon_color__hover="#ff9900" button_icon_color__hover_enabled="on|hover" /]');
$check(strpos($css($hover),'display:none;')!==false&&strpos($css($hover),':focus-visible')!==false,'hover-only icon available to keyboard');$check(strpos($css($hover),'color:#ff9900')!==false,'active icon hover color');
$inactive=$build('[et_pb_button button_icon="'.$glyph.'" button_icon_color_tablet="#00ff00" button_icon_color_last_edited="off|desktop" button_icon_color__hover="unsafe;" button_icon_color__hover_enabled="off|desktop" /]');$check(strpos($css($inactive),'#00ff00')===false&&strpos($css($inactive),'unsafe;')===false,'inactive device/hover settings ignored');
$responsive=$build('[et_pb_button button_icon="'.$glyph.'" button_icon_color="#ff0000" button_icon_color_phone="#00ff00" button_icon_color_last_edited="on|phone" /]');$check(strpos($css($responsive),'@media(max-width:767px)')!==false&&strpos($css($responsive),'color:#00ff00')!==false,'responsive color portable');
foreach(['button_icon="unsupported"','button_use_icon="on"','button_icon="'.$glyph.'" button_icon_color="red;display:none"','button_icon="'.$glyph.'" button_icon_color="rgba(1,2)"','button_icon="'.$glyph.'" button_icon_placement="middle"','button_icon="'.$glyph.'" button_on_hover="bad"']as$attrs){$data=$build('[et_pb_button '.$attrs.' /]');$check($data[0]['widgetType']==='shortcode'&&$builder->warnings,'unrepresentable active icon falls back');}
$unknown=$build('[et_pb_image src="https://origin.example/logo.png" url="'.$token('unsupported_binding').'" /]');$check($unknown[0]['widgetType']==='shortcode','unknown dynamic bindings still guarded');
echo 'PASS: '.$checks.' footer standalone assertions on PHP '.PHP_VERSION.PHP_EOL;
