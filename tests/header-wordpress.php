<?php
/** Disposable WordPress + real Header export; never run on production. */
if (!defined('ABSPATH') || !get_user_by('login','qa_admin')) { throw new RuntimeException('Disposable QA site required.'); }
wp_set_current_user(get_user_by('login','qa_admin')->ID);set_current_screen('suiteedge-migrator-mapping');
$checks=0;$check=function ($ok,$message) use (&$checks) {if (!$ok) {throw new RuntimeException('FAIL: '.$message);}$checks++;};
$reject=function ($fn,$message) use ($check) {try {$fn();}catch (Throwable $e) {$check(true,$message);return;}$check(false,$message);};
$json=file_get_contents(getenv('SUITEEDGE_HEADER_FIXTURE'));$export=(new SuiteEdge\Migrator\Import\DiviExportReader())->read_json($json);$content=$export['layouts'][0]['post_content'];
$mapping=new SuiteEdge\Migrator\Import\MenuMapping();$repo=new SuiteEdge\Migrator\Jobs\JobRepository();$runner=new SuiteEdge\Migrator\Jobs\BatchRunner($repo);$backup=new SuiteEdge\Migrator\Import\BackupEngine();
$existing=wp_get_nav_menu_object('Main Menu');$menu=$existing ? $existing->term_id : wp_create_nav_menu('Main Menu');if (is_wp_error($menu)) {throw new RuntimeException($menu->get_error_message());}
if (!$existing) {
    $root=wp_update_nav_menu_item($menu,0,['menu-item-title'=>'How We Help','menu-item-url'=>home_url('/#how-we-help'),'menu-item-type'=>'custom','menu-item-status'=>'publish']);
    $child=wp_update_nav_menu_item($menu,0,['menu-item-title'=>'Get More Customers','menu-item-url'=>home_url('/#customers'),'menu-item-parent-id'=>$root,'menu-item-type'=>'custom','menu-item-status'=>'publish']);
    wp_update_nav_menu_item($menu,0,['menu-item-title'=>'Customer FAQ','menu-item-url'=>home_url('/#faq'),'menu-item-parent-id'=>$child,'menu-item-type'=>'custom','menu-item-status'=>'publish']);
    wp_update_nav_menu_item($menu,0,['menu-item-title'=>'Careers','menu-item-url'=>home_url('/#careers'),'menu-item-type'=>'custom','menu-item-status'=>'publish']);
}
$check($mapping->references($content)===[32],'both header variants reference the same source menu');
$check($mapping->target($menu)===$menu,'explicit Main Menu validates');
foreach ([0,true,[],99999999] as $bad) {$reject(function () use ($mapping,$bad) {$mapping->target($bad);},'invalid/missing menu rejected');}
$empty=wp_create_nav_menu('QA Empty '.wp_generate_uuid4());$reject(function () use ($mapping,$empty) {$mapping->target($empty);},'empty menu rejected');wp_delete_nav_menu($empty);
$source=(new SuiteEdge\Migrator\Import\SourceRepository())->save($json,'Header.json');$key='suiteedge_migrator_pending_files_'.get_current_user_id();$pending=[['source_id'=>$source,'layout_index'=>0,'name'=>'Header.json / Divi layout']];set_transient($key,$pending,HOUR_IN_SECONDS);
$check($mapping->pending($pending)[$source]['ids']===[32],'source-specific menu discovery');
ob_start();(new SuiteEdge\Migrator\Admin\MappingPage())->render();$form=ob_get_clean();
$check(strpos($form,'menu_map['.$source.'][32]')!==false && strpos($form,'Navigation menu mappings')!==false,'menu mapping selector rendered');
$check(strpos($form,'value="'.$menu.'" selected=')!==false,'unique Main Menu suggested for Header export');
$check($mapping->validate([$source=>[32=>(string)$menu]])[$source][32]===$menu,'menu mapping validated per source file');
$reject(function () use ($mapping,$source) {$mapping->validate([$source=>[32=>true]]);},'boolean mapping rejected');
$reject(function () use ($mapping,$source,$menu) {$mapping->validate([$source=>[32=>$menu,'032'=>$menu]]);},'normalized duplicate mapping rejected');
$reject(function () use ($mapping,$menu) {$mapping->validate(['untrusted-source'=>[32=>$menu]]);},'malformed source scope rejected');
$settings=['output_engine'=>'classic','dry_run'=>true,'assets'=>['import_external','reuse_library'],'source_origin'=>'https://suiteedge.com','source_theme'=>(new SuiteEdge\Migrator\Style\SourceThemeProfile())->recommended('https://suiteedge.com'),'menu_map'=>[$source=>[32=>$menu]],'reviewed_warnings'=>true];
$settings['link_map'] = ['52164'=>home_url('/get-started/')];
$page=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA header target','post_content'=>'Original header target']);$before=$backup->fingerprint($page);
$job=(new SuiteEdge\Migrator\Import\BatchImport())->create([$page],$settings);$runner->process_batch($job->id);$job=$repo->get($job->id);
$check($job->status==='completed'&&$job->items[0]['status']==='analyzed','actual Header analysis completes');
$check($before===$backup->fingerprint($page),'analysis preserves assigned page');
$check(!$job->items[0]['warnings'],'real Header conversion has no unsupported warnings');
$data=$job->items[0]['elementor_data'];$nodes=[];$walk=function ($items) use (&$walk,&$nodes) {foreach ($items as $item) {$nodes[]=$item;$walk($item['elements']);}};$walk($data);
$menus=array_values(array_filter($nodes,function ($n) {return ($n['widgetType']??'')==='wp-widget-nav_menu';}));
$check(count($menus)===2&&$menus[0]['settings']['wp']['nav_menu']===$menu&&$menus[1]['settings']['wp']['nav_menu']===$menu,'both native widgets use mapped menu rather than source ID');
$check(!array_filter($nodes,function ($n) {return ($n['widgetType']??'')==='shortcode';}),'no Divi menu shortcodes remain');
$logos=array_values(array_filter($nodes,function ($n) {return ($n['widgetType']??'')==='image';}));$check(count($logos)===2,'desktop and mobile logos remain native images');
$check($logos[1]['settings']['link']['url']===home_url('/'),'mobile logo resolves destination homepage');
$check(strpos(wp_json_encode($data),'SuiteEdgePortableNavigation')!==false,'portable menu behavior embedded');
$check(strpos(wp_json_encode($data),'@ET-DC@')===false,'no unresolved dynamic binding');
$check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data,true),'all real native controls validate');
$buttons=array_values(array_filter($nodes,function ($n) {return ($n['widgetType']??'')==='button';}));$check(!isset($buttons[0]['settings']['_z_index'],$buttons[1]['settings']['_z_index']),'source button stacking applies to anchors instead of blocking menu with wrappers');
$check(strpos(wp_json_encode($data),'.elementor-button{width:150px!important;')!==false,'source login width targets actual button link');
$items_before=wp_json_encode(wp_get_nav_menu_items($menu));(new SuiteEdge\Migrator\Jobs\SavedOutputApplier())->queue([$job->id=>[0]],true);$runner->process_batch($job->id);$job=$repo->get($job->id);
$check($job->items[0]['status']==='converted','saved Header applies to assigned page');
$check(!$job->items[0]['warnings'],'Header saved Apply preserves source alt without an unresolved warning');
$check(wp_json_encode(wp_get_nav_menu_items($menu))===$items_before,'conversion does not modify destination menu links');
$check(strpos(Elementor\Plugin::$instance->frontend->get_builder_content($page),'How We Help')!==false,'native WordPress menu renders real links');
$portable_data=$job->items[0]['elementor_data'];
$runner->rollback($job->id);$check($before===$backup->fingerprint($page),'header rollback preserves original page');
$blocked=new SuiteEdge\Migrator\Jobs\Job();$blocked->settings=$settings;unset($blocked->settings['menu_map']);$blocked->settings['dry_run']=false;$blocked->items=[['source_id'=>$source,'layout_index'=>0,'name'=>'Header without mapping','target_page_id'=>$page,'status'=>'pending']];$blocked->summary['total']=1;$repo->save($blocked);$runner->process_batch($blocked->id);$blocked=$repo->get($blocked->id);
$check($blocked->status==='completed_with_errors'&&$before===$backup->fingerprint($page),'missing menu mapping blocks writes');
$check(strpos(implode(' ',$blocked->items[0]['warnings']),'Choose a destination WordPress menu')!==false,'mapping failure explains real cause');
$diagnostics=(new SuiteEdge\Migrator\Reports\WarningReport())->diagnostics($blocked);$check(in_array('menu_mapping_required',array_column($diagnostics['entries'],'code'),true),'actionable menu diagnostic');
// A saved widget cannot apply a deleted/emptied menu or silently render another menu.
$invalidMenu=[['id'=>'abcdef0','elType'=>'widget','widgetType'=>'wp-widget-nav_menu','settings'=>['wp'=>['title'=>'','nav_menu'=>99999999]],'elements'=>[]]];
$reject(function () use ($invalidMenu) {(new SuiteEdge\Migrator\Jobs\SavedOutputApplier())->validate_output($invalidMenu);},'saved menu revalidated before application');
$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$test=$builder->build('[et_pb_menu menu_id="32" show_search_icon="on" /]',['menu_map'=>[32=>$menu]]);$check($test->nodes[0]->type==='shortcode','unsupported active search is preserved');
$test=$builder->build('[et_pb_menu menu_id="32abc" /]',['menu_map'=>[32=>$menu]]);$check($test->nodes[0]->type==='shortcode','malformed source ID cannot bind to a valid menu');
$test=$builder->build('[et_pb_menu menu_id="32" show_cart_icon="on" /]',['menu_map'=>[32=>$menu]]);$check($test->nodes[0]->type==='shortcode','unsupported active cart is preserved');
$temporary=wp_create_nav_menu('QA temporary '.wp_generate_uuid4());$temporaryItem=wp_update_nav_menu_item($temporary,0,['menu-item-title'=>'Temporary','menu-item-url'=>home_url('/#temporary'),'menu-item-type'=>'custom','menu-item-status'=>'publish']);
$invalidMenu[0]['settings']['wp']['nav_menu']=$temporary;
(new SuiteEdge\Migrator\Jobs\SavedOutputApplier())->validate_output($invalidMenu);$check(true,'valid live menu accepted before deletion');
wp_delete_post($temporaryItem,true);
$reject(function () use ($invalidMenu) {(new SuiteEdge\Migrator\Jobs\SavedOutputApplier())->validate_output($invalidMenu);},'menu emptied after analysis rejected');wp_delete_nav_menu($temporary);
$cycle=wp_create_nav_menu('QA cycle '.wp_generate_uuid4());$cycleA=wp_update_nav_menu_item($cycle,0,['menu-item-title'=>'A','menu-item-url'=>home_url('/#a'),'menu-item-type'=>'custom','menu-item-status'=>'publish']);$cycleB=wp_update_nav_menu_item($cycle,0,['menu-item-title'=>'B','menu-item-url'=>home_url('/#b'),'menu-item-type'=>'custom','menu-item-status'=>'publish','menu-item-parent-id'=>$cycleA]);update_post_meta($cycleA,'_menu_item_menu_item_parent',$cycleB);
$reject(function () use ($mapping,$cycle) {$mapping->target($cycle);},'cyclic menu relationships rejected');wp_delete_nav_menu($cycle);
$builder->build('[et_pb_menu menu_id="32" mobile_menu_text_color="red;display:none" /]',['menu_map'=>[32=>$menu]]);$check((bool)$builder->warnings,'malformed mobile styling guarded');
$otherSource=(new SuiteEdge\Migrator\Import\SourceRepository())->save($json,'Other Header.json');$wrong=new SuiteEdge\Migrator\Jobs\Job();$wrong->settings=$settings;$wrong->items=[['source_id'=>$otherSource,'layout_index'=>0,'name'=>'Other scoped source','target_page_id'=>$page,'status'=>'pending']];$wrong->summary['total']=1;$repo->save($wrong);$runner->process_batch($wrong->id);$wrong=$repo->get($wrong->id);$check(strpos(implode(' ',$wrong->items[0]['warnings']),'Choose a destination')!==false,'menu choice cannot leak into another uploaded source');
$library=Elementor\Plugin::$instance->templates_manager->get_source('local');$template=$library->save_item(['title'=>'QA header template','type'=>'page','content'=>$portable_data,'page_settings'=>[]]);if (is_wp_error($template)) {throw new RuntimeException($template->get_error_message());}$inserted=$library->get_data(['template_id'=>$template]);
$preview=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'QA header preview']);update_post_meta($preview,'_wp_page_template','elementor_canvas');update_post_meta($preview,'_elementor_edit_mode','builder');Elementor\Plugin::$instance->documents->get($preview)->save(['elements'=>$inserted['content'],'settings'=>[]]);(new SuiteEdge\Migrator\Elementor\CssRegenerator())->regenerate($preview);
$check(strpos(Elementor\Plugin::$instance->frontend->get_builder_content($preview),'Get More Customers')!==false,'real library insertion preserves live navigation');
file_put_contents(ABSPATH.'../header-results'.(defined('DB_ENGINE')?'-sqlite':'-mariadb').'.json',wp_json_encode(['assertions'=>$checks,'plugin'=>SUITEEDGE_MIGRATOR_VERSION,'database'=>defined('DB_ENGINE')?'sqlite':$GLOBALS['wpdb']->db_version(),'menu_id'=>$menu,'source_id'=>$source,'source_sha256'=>hash('sha256',$json),'url'=>get_permalink($preview),'warnings'=>$job->items[0]['warnings'],'native_menus'=>count($menus)],JSON_PRETTY_PRINT));
echo 'PASS: '.$checks.' real Header/WordPress menu assertions'.PHP_EOL;
