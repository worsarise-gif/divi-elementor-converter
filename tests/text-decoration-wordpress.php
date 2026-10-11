<?php
if (!defined('ABSPATH')) { exit(1); }
wp_set_current_user(get_user_by('login','qa_admin')->ID);
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok)throw new RuntimeException('FAIL: '.$label);$checks++;};
$hooks=[];
foreach(['wp_enqueue_scripts','elementor/preview/enqueue_styles']as$hook){foreach($GLOBALS['wp_filter'][$hook]->callbacks??[]as$callbacks){foreach($callbacks as$entry){$callback=$entry['function'];if(is_array($callback)&&$callback[0]instanceof SuiteEdge\Migrator\Elementor\ConvertedLinkStyles)$hooks[$hook]=true;}}}
$check(count($hooks)===2,'repair registered for frontend and Elementor preview');
do_action('elementor/preview/enqueue_styles');
$check(wp_style_is('suiteedge-converted-links','enqueued'),'preview enqueues the compatibility stylesheet');
$style=wp_styles()->registered['suiteedge-converted-links'];
$check($style->ver===SUITEEDGE_MIGRATOR_VERSION&&strpos($style->src,'assets/css/converted-links.css')!==false,'cache version and stylesheet source correct');
$native_button=Elementor\Plugin::$instance->widgets_manager->get_widget_types('button')->get_controls();
$native_box=Elementor\Plugin::$instance->widgets_manager->get_widget_types('icon-box')->get_controls();
$check(isset($native_button['typography_text_decoration']['options']['none'],$native_box['title_typography_text_decoration']['options']['none']),'native decoration choices are real registered controls');
$url=home_url('/start/');
$raw='[et_pb_section][et_pb_row][et_pb_column type="4_4"]'
 .'[et_pb_button module_id="default-button" button_text="Default button" button_url="'.$url.'" /]'
 .'[et_pb_blurb module_id="default-card" use_icon="on" font_icon="&#xf0c0;||fa||900" title="Default card" url="'.$url.'"]<a href="'.$url.'" class="description-link">Description link</a>[/et_pb_blurb]'
 .'[et_pb_blurb module_id="default-image-card" title="Default image card" url="'.$url.'" /]'
 .'[et_pb_button module_id="underlined-button" button_text="Source underline" button_url="'.$url.'" button_font="||||on" /]'
 .'[et_pb_blurb module_id="underlined-card" use_icon="on" font_icon="&#xf0c0;||fa||900" title="Source underline card" url="'.$url.'" header_font="||||on" /]'
 .'[et_pb_button module_id="combined-button" button_text="Source combined" button_url="'.$url.'" button_font="||||on|on" /]'
 .'[et_pb_blurb module_id="combined-card" use_icon="on" font_icon="&#xf0c0;||fa||900" title="Source combined card" url="'.$url.'" header_font="||||on|on" /]'
 .'[et_pb_blurb module_id="responsive-card" use_icon="on" font_icon="&#xf0c0;||fa||900" title="Responsive card" url="'.$url.'" header_font="||||on" header_font_tablet="||||" header_font_phone="|||||on" /]'
 .'[et_pb_button module_id="responsive-button" button_text="Responsive button" button_url="'.$url.'" button_font="||||on" button_font_tablet="||||" button_font_phone="|||||on" /]'
 .'[et_pb_text]<p><a href="'.$url.'" id="ordinary-link">Ordinary text link</a> <u>Explicit HTML underline</u></p>[/et_pb_text]'
 .'[/et_pb_column][/et_pb_row][/et_pb_section]';
$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();
$data=(new SuiteEdge\Migrator\Elementor\Classic\Serializer())->serialize($builder->build($raw));
(new SuiteEdge\Migrator\Elementor\Validator())->validate($data,true);
$check(!$builder->warnings,'all decoration fixtures validate without warning or fallback');
$local=Elementor\Plugin::$instance->templates_manager->get_source('local');$template=$local->save_item(['type'=>'page','title'=>'QA link decoration','content'=>$data,'page_settings'=>[]]);
$check(!is_wp_error($template),'actual template library save');
$inserted=$local->get_data(['template_id'=>$template]);
$check($inserted['content'][0]['id']!==$data[0]['id'],'official template insertion regenerates IDs');
$check(end($inserted['content'][0]['elements'])['settings']['html']===end($data[0]['elements'])['settings']['html'],'decoration stylesheet remains portable after insertion');
$page=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'QA link decoration browser']);update_post_meta($page,'_wp_page_template','elementor_canvas');update_post_meta($page,'_elementor_edit_mode','builder');
Elementor\Plugin::$instance->documents->get($page)->save(['elements'=>$inserted['content'],'settings'=>[]]);(new SuiteEdge\Migrator\Elementor\CssRegenerator())->regenerate($page);
$html=Elementor\Plugin::$instance->frontend->get_builder_content_for_display($page,true);
$check(strpos($html,'elementor-icon-box-title')!==false&&strpos($html,'elementor-image-box-title')!==false,'actual icon/image-box title anchors rendered');
$check(strpos($html,'text-decoration:inherit;')!==false&&strpos($html,'text-decoration:underline line-through;')!==false,'native/portable decoration and combined source style rendered');
$check(strpos($html,'description-link')!==false&&strpos($html,'id="ordinary-link"')!==false,'ordinary text anchors preserved');
$before=(new SuiteEdge\Migrator\Import\BackupEngine())->state($page);(new SuiteEdge\Migrator\Elementor\ConvertedLinkStyles())->enqueue();
$check((new SuiteEdge\Migrator\Import\BackupEngine())->state($page)===$before,'existing-page compatibility repair writes no page metadata');
$export=(new SuiteEdge\Migrator\Import\DiviExportReader())->read_file(getenv('SUITEEDGE_HOME_FIXTURE'));
$links=json_decode(file_get_contents(getenv('SUITEEDGE_HOME_LINKS')),true)['links'];
$home=(new SuiteEdge\Migrator\Elementor\Classic\Serializer())->serialize($builder->build($export['post_content'],['presets'=>$export['presets'],'link_map'=>$links,'source_origin'=>'https://suiteedge.com']));
$count=['buttons'=>0,'cards'=>0];$walk=function($nodes)use(&$walk,&$count,$check){foreach($nodes as$n){if(($n['widgetType']??'')==='button'){$count['buttons']++;$check($n['settings']['typography_text_decoration']==='none','real Home button default protected');}if(($n['widgetType']??'')==='icon-box'){$count['cards']++;$check($n['settings']['title_typography_text_decoration']==='none','real Home linked card default protected');}$walk($n['elements']);}};$walk($home);
$check($count===['buttons'=>4,'cards'=>12]&&!$builder->warnings,'all Home buttons/cards protected without additional warnings');
file_put_contents(ABSPATH.'../text-decoration-results'.(defined('DB_ENGINE')?'-sqlite':'-mariadb').'.json',wp_json_encode(['assertions'=>$checks,'plugin'=>SUITEEDGE_MIGRATOR_VERSION,'url'=>get_permalink($page),'page_id'=>$page,'template_id'=>$template,'home_control_counts'=>$count,'database'=>defined('DB_ENGINE')?DB_ENGINE:$GLOBALS['wpdb']->db_version()],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo 'PASS: '.$checks.' WordPress text-decoration assertions; preview '.get_permalink($page).PHP_EOL;
