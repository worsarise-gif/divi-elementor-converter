<?php
require __DIR__.'/bootstrap.php';
$checks=0;$check=function ($ok,$label) use (&$checks) {if (!$ok) {throw new RuntimeException('FAIL: '.$label);}$checks++;};
$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$serializer=new SuiteEdge\Migrator\Elementor\Classic\Serializer();
$build=function ($attributes) use ($builder,$serializer) {return $serializer->serialize($builder->build('[et_pb_text '.$attributes.']<p><a href="/shop">Shop</a></p>[/et_pb_text]'));};
$css=function ($data) {return report_warnings_css($data);};
function report_warnings_css($nodes) {$result='';foreach ($nodes as $node) {if (($node['widgetType']??'')==='html') {$result.=$node['settings']['html'];}$result.=report_warnings_css($node['elements']);}return $result;}
$data=$build('link_text_color="#2BAFD8" link_text_color__hover_enabled="on|hover" link_text_color__hover="#FF9B40"');$style=$css($data);
$check(!$builder->warnings,'reported hover setting fully handled');
$check(strpos($style,'a{color:#2BAFD8;}')!==false,'normal link color retained');
$check(strpos($style,'a:hover')!==false&&strpos($style,'color:#FF9B40;')!==false,'hover color retained');
$check(strpos($style,'a:focus-visible')!==false,'keyboard focus gets the interactive color');
$check(strpos($style,'text-decoration:')===false,'ordinary link decorations untouched');
$check(strpos($style,'elementor-widget-text-editor')!==false,'link color scoped to its text widget');
$inactive=$css($build('link_text_color="#2BAFD8" link_text_color__hover_enabled="off|hover" link_text_color__hover="#FF9B40"'));
$check(strpos($inactive,'#FF9B40')===false,'disabled saved hover color remains inactive');
$responsive=$css($build('link_text_color="#111111" link_text_color_tablet="#222222" link_text_color_phone="#333333" link_text_color_tablet__hover="#444444"'));
$check(!$builder->warnings,'responsive link settings handled');
$check(strpos($responsive,'@media(max-width:980px)')!==false&&strpos($responsive,'#222222')!==false,'source tablet normal color mapped');
$check(strpos($responsive,'@media(max-width:767px)')!==false&&strpos($responsive,'#333333')!==false,'mobile normal color mapped');
$check(strpos($responsive,'#444444')!==false,'responsive hover color mapped');
$inactive=$css($build('link_text_color="#111111" link_text_color_tablet="#222222" link_text_color_phone="#333333" link_text_color_last_edited="off|desktop"'));
$check(strpos($inactive,'#222222')===false&&strpos($inactive,'#333333')===false,'disabled responsive colors stay inactive');
foreach (['#abcd','rgba(43,175,216,0.5)','hsl(192,70%,50%)','transparent','inherit'] as $color) {$build('link_text_color__hover="'.$color.'"');$check(!$builder->warnings,'valid hover color accepted '.$color);}
foreach (['red;display:none','var(--unknown)','</style><script>'] as $color) {$data=$build('link_text_color__hover="'.$color.'"');$check($data[0]['widgetType']==='shortcode'&&(bool)$builder->warnings,'unsafe/unresolved color fails visibly');}
$data=$serializer->serialize($builder->build('[et_pb_image src="https://example.test/logo.png" /]'));
$check(!array_key_exists('_suiteedge_alt',$data[0]['settings']),'missing source alt leaves attachment metadata available');
$data=$serializer->serialize($builder->build('[et_pb_image src="https://example.test/logo.png" alt="" /]'));
$check(array_key_exists('_suiteedge_alt',$data[0]['settings'])&&$data[0]['settings']['_suiteedge_alt']==='','explicit decorative alt retained');
echo 'PASS: '.$checks.' reported hover/alt assertions on PHP '.PHP_VERSION.PHP_EOL;
