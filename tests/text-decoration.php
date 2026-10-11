<?php
require __DIR__.'/bootstrap.php';
define('ELEMENTOR_PATH',getenv('SUITEEDGE_ELEMENTOR_PATH') ?: dirname(__DIR__,2).'/.qa-runtime/wordpress/wp-content/plugins/elementor/');
if (!is_file(ELEMENTOR_PATH.'assets/lib/font-awesome/json/solid.json')) { throw new RuntimeException('Set SUITEEDGE_ELEMENTOR_PATH to the installed Elementor fixture.'); }
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok)throw new RuntimeException('FAIL: '.$label);$checks++;};
$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$serializer=new SuiteEdge\Migrator\Elementor\Classic\Serializer();
$build=function($raw)use($builder,$serializer){return $serializer->serialize($builder->build($raw));};
$css=function($data){$rules=[];$walk=function($nodes)use(&$walk,&$rules){foreach($nodes as $n){if(($n['widgetType']??'')==='html')$rules[]=$n['settings']['html'];$walk($n['elements']);}};$walk($data);return implode(' ',$rules);};
$button=$build('[et_pb_button button_text="Go" button_url="/go" /]');
$check($button[0]['settings']['typography_text_decoration']==='none','source button default is explicit');
$check(strpos($css($button),' a.elementor-button{text-decoration:none;}')!==false,'button anchor gets portable theme protection');
foreach(['on'=>'icon-box','off'=>'image-box']as$flag=>$type){
 $box=$build('[et_pb_blurb use_icon="'.$flag.'" font_icon="&#xf0c0;||fa||900" title="Card" url="/card" image="https://example.test/photo.jpg"]<a href="/body">Ordinary description link</a>[/et_pb_blurb]');
 $check($box[0]['settings']['title_typography_text_decoration']==='none','default '.$type.' title is explicit');
 $check(strpos($css($box),' .elementor-'.$type.'-title a{text-decoration:inherit;}')!==false,'linked '.$type.' title follows its native title formatting');
 $check(strpos($css($box),'description a')===false&&strpos($css($box),'-image-box-img a')===false,'ordinary description and image links are excluded');
}
foreach(['||||on'=>'underline','|||||on'=>'line-through','||||on|on'=>'underline line-through','Default|700||on|||||'=>'none']as$font=>$decoration){
 $button=$build('[et_pb_button button_text="Go" button_font="'.$font.'" /]');
 $check(strpos($css($button),' a.elementor-button{text-decoration:'.$decoration.';}')!==false,'button source decoration '.$decoration.' retained');
 $box=$build('[et_pb_blurb use_icon="on" font_icon="&#xf0c0;||fa||900" title="Card" header_font="'.$font.'" /]');
 $check(strpos($css($box),' .elementor-icon-box-title{text-decoration:'.$decoration.';}')!==false,'title source decoration '.$decoration.' retained');
 if(strpos($decoration,' ')!==false)$check(!isset($button[0]['settings']['typography_text_decoration'],$box[0]['settings']['title_typography_text_decoration']),'combined decoration does not invent unsupported native enum');
}
$responsive=$build('[et_pb_button button_text="Go" button_font="||||on" button_font_tablet="||||" button_font_phone="|||||on" /]');
$check(strpos($css($responsive),'@media(max-width:980px)')!==false&&strpos($css($responsive),'.elementor-widget a.elementor-button{text-decoration:none;}')!==false,'source tablet breakpoint explicitly clears desktop underline');
$check(strpos($css($responsive),'.elementor-widget a.elementor-button{text-decoration:line-through;}')!==false,'mobile source strikethrough retained');
$normal=$build('[et_pb_text]<p><a href="/text"><u>Ordinary linked text</u></a></p>[/et_pb_text]');
$check(strpos($css($normal),'text-decoration:')===false&&strpos($normal[0]['settings']['editor'],'<u>')!==false,'normal text links and explicit HTML underline untouched');
$unknown=$build('[et_pb_button button_text="Go" button_font="||||broken" /]');
$check($unknown[0]['widgetType']==='shortcode'&&count($builder->warnings)===1,'unrecognized explicit decoration fails closed');
$inactive=$build('[et_pb_blurb use_icon="on" font_icon="&#xf0c0;||fa||900" title="Card" header_font="||||" header_font_phone="||||on" header_font_last_edited="off|desktop" /]');
$check(strpos($css($inactive),'text-decoration:underline;')===false,'inactive responsive font values stay inactive');
$default=$build('[et_pb_button button_text="Go" /]');
$check(empty($builder->warnings)&&strpos(implode(' ',$builder->information),'text decoration preserved')!==false,'supported link styling recorded as information');
echo 'PASS: '.$checks.' text-decoration assertions on PHP '.PHP_VERSION.PHP_EOL;
