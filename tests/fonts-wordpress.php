<?php
/** Only run on the disposable local QA sites. */
if (!defined('ABSPATH') || !get_user_by('login','qa_admin') || strpos(ABSPATH,'.qa-runtime') === false) { throw new RuntimeException('Disposable QA site required.'); }
wp_set_current_user(get_user_by('login','qa_admin')->ID);
use SuiteEdge\Migrator\Style\FontFamily;
use SuiteEdge\Migrator\Elementor\DocumentBuilder;
use SuiteEdge\Migrator\Elementor\Classic\Serializer;
$checks=0;$check=function ($ok,$label) use (&$checks) { if (!$ok) { throw new RuntimeException('FAIL: '.$label); } $checks++; };
$fonts=Elementor\Fonts::get_fonts();
foreach ($fonts as $family=>$type) {
    $check(FontFamily::normalize($family)===$family,'registered family preserved: '.$family);
    $node=new SuiteEdge\Migrator\Model\Node('et_pb_menu','wp-widget-nav_menu');
    (new SuiteEdge\Migrator\Style\MenuTranslator())->translate($node,['menu_font'=>$family.'|400|||||||'],(new SuiteEdge\Migrator\Style\SourceThemeProfile())->recommended());
    $check(in_array($family,FontFamily::in_node($node,true),true),'registered family supported in menu: '.$family);
}
$menu=wp_get_nav_menu_object('Main Menu')->term_id;
$builder=new DocumentBuilder();$serializer=new Serializer();
$content=(new SuiteEdge\Migrator\Import\DiviExportReader())->read_json(file_get_contents(getenv('SUITEEDGE_HEADER_FIXTURE')))['layouts'][0]['post_content'];
$options=['menu_map'=>[32=>$menu],'link_map'=>['52164'=>home_url('/get-started/')],'source_origin'=>'https://suiteedge.com','source_theme'=>(new SuiteEdge\Migrator\Style\SourceThemeProfile())->recommended('https://suiteedge.com','theme-builder')];
$document=$builder->build($content,$options);$data=$serializer->serialize($document);
$check(!$builder->warnings,'real Header fonts available without warnings');
$check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data,true),'real Header native/font controls accepted');
$library=Elementor\Plugin::$instance->templates_manager->get_source('local');
$template=$library->save_item(['title'=>'QA Ace Header Font Template','type'=>'page','content'=>$data,'page_settings'=>[]]);
if (is_wp_error($template)) { throw new RuntimeException($template->get_error_message()); }
$inserted=$library->get_data(['template_id'=>$template]);
$page=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'QA Ace header font preview']);
update_post_meta($page,'_wp_page_template','elementor_canvas');update_post_meta($page,'_elementor_edit_mode','builder');
Elementor\Plugin::$instance->documents->get($page)->save(['elements'=>$inserted['content'],'settings'=>[]]);
$css=new Elementor\Core\Files\CSS\Post($page);$css->update();$loaded=$css->get_fonts();
foreach (['Inter','Kaushan Script','Montserrat'] as $family) { $check(in_array($family,$loaded,true),'Elementor CSS loads real Header family after library insertion: '.$family); }
$html=Elementor\Plugin::$instance->frontend->get_builder_content($page,true);
$check(strpos($html,'font-family:"Inter"')!==false,'visible menu uses resolved source font');
$check(strpos($html,'suiteedge-font-carrier')!==false,'font carrier retained by actual frontend rendering');
$unknown=$builder->build('[et_pb_text text_font="QA Uninstalled Custom 2.0|400|||||||"]Test[/et_pb_text]');
$check(count($builder->warnings)===1 && strpos($builder->warnings[0],'not registered in Elementor')!==false,'missing custom font reported once');
$job=new SuiteEdge\Migrator\Jobs\Job();$job->items=[['name'=>'Custom font','warnings'=>$builder->warnings]];
$check((new SuiteEdge\Migrator\Reports\WarningReport())->diagnostics($job)['entries'][0]['code']==='font_registration_required','missing font actionable report');
$custom='QA Foundry 2.0';$property=new ReflectionProperty(Elementor\Fonts::class,'fonts');$property->setAccessible(true);$original=$property->getValue();
$property->setValue(null,array_merge($original,[$custom=>'qa-local-font']));
try {
    $document=$builder->build('[et_pb_text header_2_font="'.$custom.'|400|||||||"]<h2>Custom provider</h2><p>Body</p>[/et_pb_text]');
    $check(!$builder->warnings,'registered custom font provider recognized');
    $custom_data=$serializer->serialize($document);
    $custom_page=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA custom-font provider']);update_post_meta($custom_page,'_elementor_edit_mode','builder');
    Elementor\Plugin::$instance->documents->get($custom_page)->save(['elements'=>$custom_data,'settings'=>[]]);
    $custom_css=new Elementor\Core\Files\CSS\Post($custom_page);$custom_css->update();
    $check(in_array($custom,$custom_css->get_fonts(),true),'native CSS routes scoped custom font to provider');
    $seen=[];$capture=function ($family) use (&$seen) { $seen[]=$family; };add_action('elementor/fonts/print_font_links/qa-local-font',$capture);
    $custom_css->enqueue();ob_start();Elementor\Plugin::$instance->frontend->print_fonts_links();ob_end_clean();remove_action('elementor/fonts/print_font_links/qa-local-font',$capture);
    $check(in_array($custom,$seen,true),'registered provider font-loading hook invoked');
} finally { $property->setValue(null,$original); }
$original_setting=get_option('elementor_google_font','1');
try {
    update_option('elementor_google_font','0');$builder->build('[et_pb_text text_font="Inter|400|||||||"]Test[/et_pb_text]');
    $check(count($builder->warnings)===1 && strpos($builder->warnings[0],'Google font loading is disabled')===0,'disabled font loading diagnosed');
    $check(get_option('elementor_google_font')==='0','converter respects disabled Google font setting');
} finally { update_option('elementor_google_font',$original_setting); }
$evidence=['assertions'=>$checks,'catalogue_families'=>count($fonts),'header_loaded_fonts'=>$loaded,'header_page'=>$page,'header_url'=>get_permalink($page),'version'=>SUITEEDGE_MIGRATOR_VERSION,'warnings'=>[],'custom_provider_verified'=>true,'google_loading_setting_preserved'=>true];
file_put_contents(getenv('SUITEEDGE_QA_EVIDENCE').'/ace-fonts-wordpress'.(defined('DB_ENGINE')?'-sqlite':'-mariadb').'.json',wp_json_encode($evidence,JSON_PRETTY_PRINT));
echo 'PASS: '.$checks.' WordPress font assertions ('.count($fonts).' registered families)'.PHP_EOL;
