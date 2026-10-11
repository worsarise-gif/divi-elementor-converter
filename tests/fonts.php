<?php
require __DIR__.'/bootstrap.php';
use SuiteEdge\Migrator\Style\FontFamily;
use SuiteEdge\Migrator\Elementor\DocumentBuilder;
use SuiteEdge\Migrator\Elementor\Classic\Serializer;
$checks = 0;
$check = function ($ok,$label) use (&$checks) { if (!$ok) { throw new RuntimeException('FAIL: '.$label); } $checks++; };
$reject = function ($call,$label) use ($check) { try { $call(); } catch (Throwable $e) { $check(true,$label); return; } $check(false,$label); };
foreach (['Inter','Kaushan Script','M PLUS 1p','Noto Sans JP','Foundry Display 2.0',"O'Brien Sans",'A&B Display','源ノ角ゴシック','العربية','Foundry, Display'] as $family) {
    $check(FontFamily::normalize($family)===$family,'preserve family '.$family);
    $check(FontFamily::from_css('font-family:'.FontFamily::css($family).';')===[$family],'round-trip CSS family '.$family);
}
$check(FontFamily::from_css('color:red;font-family:"Noto Sans JP", Arial, sans-serif!important;')===['Noto Sans JP','Arial'],'safe CSS stack detection');
$check(FontFamily::from_css('font-family:var(--source-font);')===[],'CSS variable is not a literal font');
foreach (['',[],"Bad\nName",'Bad"Name','Bad;display:none','</style><script>x</script>','--et_global_body_font','name\\escape'] as $bad) { $reject(function () use ($bad) { FontFamily::normalize($bad); },'unsafe/unresolved name rejected'); }
$check(FontFamily::weight('450')==='450' && FontFamily::weight('1000')==='1000','variable weights preserved');
foreach (['0','1001','450.5','heavy'] as $bad) { $reject(function () use ($bad) { FontFamily::weight($bad); },'invalid weight rejected'); }
$theme=new SuiteEdge\Migrator\Style\SourceThemeProfile();
$profile=$theme->validate(['body_font'=>"O'Brien Sans",'heading_font'=>'源ノ角ゴシック']);
$check($profile['body_font']==="O'Brien Sans",'profile permits registered custom names');
$a=$theme->attributes('et_pb_menu',['menu_font'=>'--et_global_body_font||||||||','menu_font_phone'=>'--et_global_header_font|700|||||||'],$profile);
$check(explode('|',$a['menu_font'])[0]==="O'Brien Sans" && explode('|',$a['menu_font_phone'])[0]==='源ノ角ゴシック','global references resolve per device and role');
$check($theme->attributes('et_pb_menu',[],$profile)['menu_font']==="O'Brien Sans",'omitted menu font uses source body default');
$builder=new DocumentBuilder();$serializer=new Serializer();
$content='[et_pb_section][et_pb_heading title="Responsive heading" title_font="Inter|450|||||||" title_font_tablet="Noto Sans JP|500|||||||" title_font_phone="Kaushan Script|400|on||||||" /][et_pb_text header_2_font="Montserrat|700|||||||" ul_font="M PLUS 1p|400|||||||"]<h2>Heading</h2><p>Body</p><ul><li>Item</li></ul>[/et_pb_text][/et_pb_section]';
$document=$builder->build($content);$data=$serializer->serialize($document);
$check(!$builder->warnings,'supported responsive font choices do not fallback: '.implode(' / ',$builder->warnings));
$nodes=[];$walk=function ($items) use (&$walk,&$nodes) { foreach ($items as $item) { $nodes[]=$item;$walk($item['elements']); } };$walk($data);
$carriers=array_values(array_filter($nodes,function ($n) { return strpos($n['settings']['_css_classes'] ?? '', 'suiteedge-font-carrier')!==false; }));
$families=array_column(array_column($carriers,'settings'),'typography_font_family');
foreach (['Inter','Noto Sans JP','Kaushan Script','Montserrat','M PLUS 1p'] as $family) { $check(in_array($family,$families,true),'portable native font control for '.$family); }
$check(count($families)===count(array_unique($families)),'font controls deduplicated');
$check(strpos(json_encode($data),'font-weight:450;')!==false,'nonstandard weight kept in scoped CSS');
$check(end($data[0]['elements'])['widgetType']==='html','style and behavior carrier remains last');
$check($data===$serializer->serialize($document),'repeat serialization retains deterministic IDs and font controls');
$no_fonts=$serializer->serialize($builder->build('[et_pb_image src="https://example.test/a.png" /]'));
$nodes=[];$walk($no_fonts);
$check(!array_filter($nodes,function ($n) { return strpos($n['settings']['_css_classes'] ?? '', 'suiteedge-font-carrier')!==false; }),'serializer clears font widgets between documents');
$check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data),'font carrier document structurally valid');
echo 'PASS: '.$checks.' font migration assertions on PHP '.PHP_VERSION.PHP_EOL;
