<?php
require __DIR__.'/bootstrap.php';
$checks=0;
$check=function($ok,$label)use(&$checks){if(!$ok)throw new RuntimeException('FAIL: '.$label);$checks++;};
$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();
$serializer=new SuiteEdge\Migrator\Elementor\Classic\Serializer();
$build=function($shortcode)use($builder,$serializer){return $serializer->serialize($builder->build($shortcode));};
$inactive=$build('[et_pb_column scroll_horizontal_motion="not even a valid curve" motion_trigger_start="top" /]');
$check(empty($builder->warnings),'disabled stored motion is not a conversion failure');
$check(!isset($inactive[0]['settings']['html'])&&!strpos(json_encode($inactive),'"horizontal"'),'disabled curves do not activate motion');
$check(strpos(implode(' ',$builder->information),'disabled')!==false,'disabled curves remain in informational diagnostics');
$active=$build('[et_pb_text scroll_horizontal_motion_enable="on" scroll_horizontal_motion="0|50|50|100|-5|0|0"]Moving[/et_pb_text]');
$runtime=end($active[1]['elements'])['settings']['html'];
$check(!$builder->warnings&&strpos($runtime,'"horizontal"')!==false,'enabled motion maps without loss warnings');
$check(strpos($runtime,'[-5')===false&&strpos($runtime,'[0,50,50,100,-5,0,0]')!==false,'curve retains Divi viewport keypoints and signed offsets');
$check(strpos($runtime,'prefers-reduced-motion')!==false&&strpos($runtime,'Math.round(100 * interpolate')!==false,'runtime includes reduced motion and Divi offset scale');
$check(strpos($runtime,'style.translate')!==false&&strpos($runtime,'window.SuiteEdgePortableEffects')!==false,'base transforms preserved and duplicate imports share one runtime');
$responsive=$build('[et_pb_text scroll_horizontal_motion_enable="on" scroll_horizontal_motion_enable_phone="off" scroll_horizontal_motion="0|50|50|100|3|0|0"]Move[/et_pb_text]');
$check(strpos(end($responsive[1]['elements'])['settings']['html'],'"mobile":false')!==false,'mobile can disable an enabled desktop effect');
$parallax=$build('[et_pb_section parallax="on" background_image="https://example.test/photo.jpg" /]');
$check(strpos(end($parallax[0]['elements'])['settings']['html'],'"desktop":"true"')!==false,'missing parallax method retains Divi true-parallax default');
$fixed=$build('[et_pb_section parallax="on" parallax_method="off" background_image="https://example.test/photo.jpg" /]');
$check(strpos(end($fixed[0]['elements'])['settings']['html'],'"desktop":"fixed"')!==false,'Divi off method means CSS fixed background, not disabled parallax');
$check($fixed[0]['settings']['background_image']['url']==='https://example.test/photo.jpg','native background remains eligible for media import');
$bad=['0|50|100|0|1|0|0','0|50|50|100|1|0','0|50|50|100|NaN|0|0','0|50|50|100|101|0|0','-1|50|50|100|1|0|0'];
foreach($bad as $curve){$data=$build('[et_pb_text scroll_horizontal_motion_enable="on" scroll_horizontal_motion="'.$curve.'"]Keep[/et_pb_text]');$check($data[0]['widgetType']==='shortcode'&&count($builder->warnings)===1,'malformed active curve preserved and blocked: '.$curve);}
foreach(['[et_pb_section parallax="on" background_image="https://example.test/a.jpg" parallax_method="bad" /]','[et_pb_text scroll_horizontal_motion_enable="bad"]Keep[/et_pb_text]']as$raw){$data=$build($raw);$check($data[0]['widgetType']==='shortcode','invalid active motion cannot silently disappear');}
foreach (['parallax="on"','parallax="on" background_enable_image="off" background_image="https://example.test/a.jpg"'] as $attrs) { $data=$build('[et_pb_section '.$attrs.' /]'); $check($data[0]['elType']==='container' && !$builder->warnings && strpos(implode(' ',$builder->information),'disabled')!==false,'inactive parallax is informational and does not block a native section'); }
$other=$build('[et_pb_text scroll_vertical_motion_enable="on" scroll_vertical_motion="0|50|50|100|1|0|0" motion_trigger_start="bottom"]Keep[/et_pb_text]');
$check(count($builder->warnings)===3,'unsupported active motion is still reported');
$title=$build('[et_pb_image src="https://example.test/a.jpg" title_text="&lt;/script&gt; quoted tooltip" /]');
$html=end($title[1]['elements'])['settings']['html'];
$check($title[0]['settings']['caption_source']==='none'&&strpos($html,'"image_title"')!==false,'title restored through portable runtime without caption');
$check(substr_count($html,'</script>')===1&&strpos($html,'\\u003C')!==false,'tooltip text cannot escape inline script');
$check(!$builder->warnings&&count($builder->information)>0,'supported styles and tooltip are information');
echo 'PASS: '.$checks.' motion assertions on PHP '.PHP_VERSION.PHP_EOL;
