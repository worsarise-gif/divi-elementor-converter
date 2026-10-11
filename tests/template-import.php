<?php
/** Exercise Elementor's actual template save/insertion path, including both ID rewrites. */
if (!defined('ABSPATH')) { exit(1); }
wp_set_current_user(get_user_by('login','qa_admin')->ID);
$checks=0;$check=function($ok,$name)use(&$checks){if(!$ok)throw new RuntimeException('FAIL: '.$name);$checks++;};
$export=(new SuiteEdge\Migrator\Import\DiviExportReader())->read_file(getenv('SUITEEDGE_HOME_FIXTURE'));
$links=json_decode(file_get_contents(getenv('SUITEEDGE_HOME_LINKS')),true)['links'];
$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();
$data=(new SuiteEdge\Migrator\Elementor\Classic\Serializer())->serialize($builder->build($export['post_content'],['presets'=>$export['presets'],'link_map'=>$links,'source_origin'=>'https://suiteedge.com']));
(new SuiteEdge\Migrator\Elementor\Validator())->validate($data,true);
$check(count($data)===12&&!array_filter($data,function($e){return $e['elType']!=='container';}),'Home exports only valid root containers');
$css=end($data[0]['elements']);$check(($css['widgetType']??'')==='html'&&strpos($css['settings']['html'],'suiteedge-style-carrier')!==false,'stylesheet lives inside first section');
$check(strpos($css['settings']['html'],'.elementor-widget-container h')===false,'heading selectors support optimized DOM');
$check(strpos($css['settings']['html'],'.elementor-element-')===false,'supplemental CSS does not depend on regenerated IDs');
$warn=[];(new SuiteEdge\Migrator\Assets\AssetProcessor())->process($data,0,['assets'=>['reuse_library','import_external']],$warn,$export['images']);
$local=Elementor\Plugin::$instance->templates_manager->get_source('local');
$template=$local->save_item(['type'=>'page','title'=>'QA portable Home','content'=>$data,'page_settings'=>[]]);
$check(!is_wp_error($template),'Elementor template saved: '.(is_wp_error($template)?$template->get_error_message():''));
$inserted=$local->get_data(['template_id'=>$template]);
$check($data[0]['id']!==$inserted['content'][0]['id'],'Elementor regenerated IDs on insertion');
$insertCss=end($inserted['content'][0]['elements']);
$check($insertCss['settings']['html']===$css['settings']['html'],'nested supplemental styles survive library save and insertion');
$page=wp_insert_post(['post_type'=>'page','post_title'=>'QA portable Home browser preview','post_status'=>'publish']);
update_post_meta($page,'_wp_page_template','elementor_canvas');
update_post_meta($page,'_elementor_edit_mode','builder');
Elementor\Plugin::$instance->documents->get($page)->save(['elements'=>$inserted['content'],'settings'=>[]]);
(new SuiteEdge\Migrator\Elementor\CssRegenerator())->regenerate($page);
$html=Elementor\Plugin::$instance->frontend->get_builder_content_for_display($page,true);
$check(strpos($html,'suiteedge-content-desktop')!==false&&strpos($html,'<style>')!==false,'inserted page renders responsive content and stylesheet');
$profile=new SuiteEdge\Migrator\Style\SourceThemeProfile();
$check($profile->recommended('https://suiteedge.com')['row_max_width']===1280&&$profile->recommended('https://unrelated.example')['row_max_width']===1080,'source-specific recommendation does not affect other sites');
foreach([['row_max_width'=>99999],['body_font'=>'</style>'],['accent_color'=>'red;display:none'],['body_size'=>[]],['unknown'=>'value']]as$bad){try{$profile->validate($bad);$check(false,'malformed theme profile');}catch(InvalidArgumentException $e){$check(true,'malformed theme profile rejected');}}
file_put_contents(ABSPATH.'../template-import-results'.(defined('DB_ENGINE')?'-sqlite':'-mariadb').'.json',wp_json_encode(['assertions'=>$checks,'page_id'=>$page,'template_id'=>$template,'url'=>get_permalink($page),'wordpress'=>get_bloginfo('version'),'elementor'=>ELEMENTOR_VERSION,'plugin'=>SUITEEDGE_MIGRATOR_VERSION,'database'=>defined('DB_ENGINE')?DB_ENGINE:$GLOBALS['wpdb']->db_version()],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo 'PASS: '.$checks.' actual Elementor template import assertions; preview '.get_permalink($page).PHP_EOL;
