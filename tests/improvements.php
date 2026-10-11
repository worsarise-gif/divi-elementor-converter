<?php
require __DIR__.'/bootstrap.php';
$checks = 0; $check = function ($ok,$label) use (&$checks) { if (!$ok) { throw new RuntimeException('FAIL: '.$label); } $checks++; };
$builder = new SuiteEdge\Migrator\Elementor\DocumentBuilder();
$build = function ($raw) use ($builder) { return (new SuiteEdge\Migrator\Elementor\Classic\Serializer())->serialize($builder->build($raw)); };
$css = function ($data) { $s = ''; $walk = function ($nodes) use (&$walk,&$s) { foreach ($nodes as $n) { if (($n['widgetType'] ?? '') === 'html') { $s .= $n['settings']['html']; } $walk($n['elements']); } }; $walk($data); return $s; };
$button = $build('[et_pb_button button_text="Test" custom_padding="11px|22px|33px|44px|false|false" custom_padding_tablet="4em|3em|2em|1em" custom_padding_phone="0px|0px|0px|0px" /]')[0]['settings'];
$check($button['text_padding']['top'] === '11' && $button['text_padding']['right'] === '22' && $button['text_padding']['bottom'] === '33' && $button['text_padding']['left'] === '44','all four native button sides');
$check($button['text_padding_tablet']['unit'] === 'em' && $button['text_padding_mobile']['left'] === '0','tablet units and explicit mobile zero');
$check(!isset($button['_padding']),'surface padding rather than wrapper padding');
$mixed = $build('[et_pb_button custom_padding=".5em|20px||2rem" custom_padding_tablet="8px|16px|12px|24px" custom_padding_phone="0px||4px|" /]'); $s = $css($mixed);
$check(!$builder->warnings && $mixed[0]['widgetType'] === 'button','mixed units remain native button');
foreach (['padding-top:0.5em;','padding-right:20px;','padding-left:2rem;','padding-bottom:12px;','padding-top:0px;','padding-bottom:4px;','@media(max-width:980px)','@media(max-width:767px)'] as $rule) { $check(strpos($s,$rule) !== false,'portable mixed/inherited rule '.$rule); }
$inactive = $build('[et_pb_button custom_padding="1px|2px|3px|4px" custom_padding_phone="bad" custom_padding_last_edited="off|desktop" /]');
$check(!$builder->warnings && !isset($inactive[0]['settings']['text_padding_mobile']),'inactive mobile padding ignored');
$default = $build('[et_pb_button /]')[0]['settings']; $check($default['text_padding']['unit'] === 'em' && $default['text_padding']['top'] === '0.3' && $default['text_padding']['right'] === '1','Divi surface defaults preserved');
foreach (['-1px|0px|0px|0px','1deg|2deg|3deg|4deg','bad','1px|2px|3px','1px|2px|3px|4px|malformed'] as $value) { $d = $build('[et_pb_button custom_padding="'.$value.'" /]'); $check($d[0]['widgetType'] === 'shortcode' && $builder->warnings,'invalid padding guarded'); }
$item = function ($attrs = '') { return '[et_pb_accordion_item title="Question" '.$attrs.']<p>Answer</p>[/et_pb_accordion_item]'; };
$faq = $build('[et_pb_accordion toggle_font="Arial|700|on|on|||" toggle_font_size="24px" toggle_font_size_phone="18px" body_font="Arial|400|||||||" body_font_size="16px" body_line_height="1.8em" closed_toggle_text_color="#112233" open_toggle_background_color="#abcdef" border_width_all="2px" border_color_all="#123456"]'.$item().$item('open="off" custom_padding="10px|1em|12px|2em" body_text_color="#ff0000"').'[/et_pb_accordion]'); $f = $faq[0]['settings']; $s = $css($faq);
$check(!$builder->warnings && $faq[0]['widgetType'] === 'accordion','styled accordion remains native');
$check($f['title_typography_font_size']['size'] === 24.0 && $f['title_typography_font_size_mobile']['size'] === 18.0 && $f['title_typography_font_style'] === 'italic' && $f['title_typography_text_transform'] === 'uppercase','native responsive title typography');
$check($f['content_typography_font_size']['size'] === 16.0 && $f['content_typography_line_height']['size'] === 1.8,'native body typography');
$check($f['border_width']['size'] === 2.0 && $f['border_color'] === '#123456','native accordion border slider');
foreach (['margin-bottom:5.5%;','margin-bottom:10px;','border-block-start:0;','padding-right:calc(1em + 50px);','padding-left:2em;','color:#ff0000;','background-color:#abcdef;'] as $rule) { $check(strpos($s,$rule) !== false,'accordion rule '.$rule); }
$closed = $build('[et_pb_accordion]'.$item('open="off"').$item('open="off"').'[/et_pb_accordion]'); $check(strpos($css($closed),'"initial":null') !== false,'all-closed state serialized');
$second = $build('[et_pb_accordion]'.$item('open="off"').$item('open="on"').'[/et_pb_accordion]'); $check(strpos($css($second),'"initial":1') !== false,'nonfirst state serialized');
$shadow = $build('[et_pb_accordion box_shadow_style="preset1" box_shadow_horizontal="2px" box_shadow_vertical="3px" box_shadow_blur="4px"]'.$item().'[/et_pb_accordion]'); $check($shadow[0]['settings']['_box_shadow_box_shadow_type'] === 'yes' && $shadow[0]['settings']['_box_shadow_box_shadow']['horizontal'] === '2','existing outer accordion box shadow retained');
foreach (['body_font_size="bad"','closed_toggle_text_color="red;display:none"','toggle_font="Arial|bad"','border_width_all="-1px"','toggle_unknown="on"'] as $a) { $d = $build('[et_pb_accordion '.$a.']'.$item().'[/et_pb_accordion]'); $check($d[0]['widgetType'] === 'shortcode' && $builder->warnings,'invalid accordion styling guarded'); }
echo 'PASS: '.$checks.' improvement standalone assertions on PHP '.PHP_VERSION.PHP_EOL;
