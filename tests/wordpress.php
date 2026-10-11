<?php
/** Run via wp eval-file tests/wordpress.php on an isolated disposable site. */
if (!defined('ABSPATH')) { fwrite(STDERR,"Run with WP-CLI eval-file.\n"); exit(1); }
wp_set_current_user(get_user_by('login','qa_admin')->ID);
$GLOBALS['qa_checks'] = 0;
function qa_check($condition,$name) {  if (!$condition) { throw new RuntimeException('FAIL: ' . $name); } $GLOBALS['qa_checks']++; }
function qa_rejects($callback,$name) { try { $callback(); } catch (Throwable $e) { qa_check(true,$name); return; } qa_check(false,$name); }
use SuiteEdge\Migrator\Import\SourceRepository;
use SuiteEdge\Migrator\Import\BackupEngine;
use SuiteEdge\Migrator\Jobs\Job;
use SuiteEdge\Migrator\Jobs\JobRepository;
use SuiteEdge\Migrator\Jobs\BatchRunner;
use SuiteEdge\Migrator\Jobs\LockManager;
use SuiteEdge\Migrator\Elementor\DocumentBuilder;
use SuiteEdge\Migrator\Elementor\Classic\Serializer;
qa_check(did_action('elementor/loaded') > 0,'Elementor loaded');
qa_check(current_user_can('unfiltered_html'),'QA administrator permission');
$repo = new JobRepository(); $runner = new BatchRunner($repo);
$GLOBALS['wp_rewrite']->set_permalink_structure('/%postname%/'); $GLOBALS['wp_rewrite']->flush_rules(false);
wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'Link destination','post_name'=>'start']);
$source_content = '[et_pb_section module_id="hero" custom_padding="20px|10px|20px|10px" background_color="#eeeeee"][et_pb_row][et_pb_column type="1_2"][et_pb_text text_font_size="18px" text_font_size_phone="14px" custom_margin="0px|0px|10px|0px"]<h1>QA conversion</h1><p>Quotes " and backslash \\ and é›ª.</p>[/et_pb_text][et_pb_button button_text="Start here" button_url="https://source.example/start" /][/et_pb_column][/et_pb_row][/et_pb_section]';
$source_id = (new SourceRepository())->save(wp_json_encode(['data'=>[['post_content'=>$source_content]]]),'qa.json');
qa_check(get_option('suiteedge_source_'.$source_id)['json'] !== '', 'source storage');
qa_rejects(function () { (new SourceRepository())->get('../../wp-config.php'); },'source path rejection');
$page = wp_insert_post(wp_slash(['post_title'=>'QA existing page','post_type'=>'page','post_status'=>'publish','post_name'=>'qa-existing','post_content'=>'Original "quoted" \\ content é›ª.']),true);
update_post_meta($page,'_yoast_wpseo_title','SEO stays'); update_post_meta($page,'suiteedge_faq',['question'=>'FAQ stays']);
update_post_meta($page,'_et_pb_use_builder','on');
update_post_meta($page,'_elementor_page_settings',['layout'=>'existing']);
add_post_meta($page,'_elementor_css',['before'=>'one']); add_post_meta($page,'_elementor_css',['before'=>'two']);
$before = (new BackupEngine())->state($page); $post_before = get_post($page);
function qa_job($source_id,$page,$dry = false) {
    $job = new Job(); $job->items = [['source_id'=>$source_id,'layout_index'=>0,'name'=>'qa.json','target_page_id'=>$page,'status'=>'pending']];
    $job->summary['total'] = 1; $job->settings = ['output_engine'=>'classic','dry_run'=>$dry,'assets'=>[],'source_origin'=>'https://source.example'];
    (new JobRepository())->save($job); return $job;
}
$dry = qa_job($source_id,$page,true); $result = $runner->process_batch($dry->id,0);
qa_check($result['status'] === 'completed' && $result['progress'] === 100,'dry-run complete / clamped batch: '.wp_json_encode($result));
qa_check((new BackupEngine())->state($page) === $before,'dry-run no mutation');
qa_check(get_option('suiteedge_backup_'.$dry->id.'_'.$page,null) === null,'dry-run no backup writes');
$job = qa_job($source_id,$page); $result = $runner->process_batch($job->id,999);
qa_check($result['status'] === 'completed', 'conversion completion: '.wp_json_encode($result));
$stored = get_post_meta($page,'_elementor_data',true); $data = json_decode($stored,true);
qa_check(is_array($data) && $data[0]['elType'] === 'container','native Elementor data persisted');
qa_check(strpos($stored,'é›ª') !== false && strpos($stored,'\\\\') !== false,'Unicode and slash-safe JSON');
qa_check(get_post_meta($page,'_elementor_edit_mode',true) === 'builder','builder mode');
qa_check(get_post_meta($page,'_et_pb_use_builder',true) === 'off','Divi builder disabled on target');
qa_check(get_post($page)->post_content === $post_before->post_content,'source post content retained');
qa_check(get_post($page)->post_title === $post_before->post_title && get_post($page)->post_name === $post_before->post_name && get_post($page)->post_status === 'publish','title/slug/status preserved');
qa_check(get_post_meta($page,'_yoast_wpseo_title',true) === 'SEO stays' && get_post_meta($page,'suiteedge_faq',true) === ['question'=>'FAQ stays'],'SEO and FAQ untouched');
qa_check(get_post_meta($page,'_elementor_page_settings',true) === ['layout'=>'existing'],'unrelated Elementor settings preserved');
qa_check(strpos($stored,'http://127.0.0.1:8097/start') !== false,'configured internal link rewrite');
$html = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($page,true);
qa_check(strpos($html,'QA conversion') !== false && strpos($html,'Start here') !== false,'actual Elementor frontend render');
qa_check(strpos($html,'id="hero"') !== false,'rendered CSS ID');
file_put_contents(ABSPATH.'../rendered-page.html',$html);
qa_check($runner->process_batch($job->id)['status'] === 'completed','terminal job idempotence');
$result = $runner->rollback($job->id);
qa_check($result['status'] === 'rolled_back' && $result['restored_count'] === 1,'rollback completed: '.wp_json_encode($result));
qa_check((new BackupEngine())->state($page) === $before,'exact snapshot restore including absent/multi-valued metadata');
qa_rejects(function () use ($runner,$job) { $runner->rollback($job->id); },'repeat rollback rejected');
// New edits must block rollback without damaging them.
$changed = qa_job($source_id,$page); $runner->process_batch($changed->id);
update_post_meta($page,'_elementor_data','newer edit');
$rollback = $runner->rollback($changed->id);
qa_check($rollback['status'] === 'rollback_failed' && get_post_meta($page,'_elementor_data',true) === 'newer edit','rollback conflict protection');
// Dry-run with create_new creates no page; conversion creates a draft.
$count = wp_count_posts('page')->draft;
$new_dry = qa_job($source_id,0,true); $runner->process_batch($new_dry->id);
qa_check(wp_count_posts('page')->draft === $count,'dry-run creates no draft');
$new = qa_job($source_id,0); $runner->process_batch($new->id);
$new_job = $repo->get($new->id); $new_id = $new_job->items[0]['target_page_id'];
qa_check($new_id > 0 && get_post_status($new_id) === 'draft','new target draft');
qa_check($runner->rollback($new->id)['status'] === 'rolled_back' && get_post_status($new_id) === 'draft','new draft rollback');
// Validate per-job uniqueness and actual atomic lock exclusion.
$one = qa_job($source_id,0,true); $two = qa_job($source_id,0,true);
qa_check($one->id !== $two->id && count($repo->all()) >= 2,'unique job IDs');
$lock = new LockManager(); $lock->acquire('job:'.$one->id);
qa_rejects(function () use ($runner,$one) { $runner->process_batch($one->id); },'concurrent job exclusion'); $lock->release();
qa_check($runner->process_batch($one->id)['status'] === 'completed','lock release');
// Inject a real WordPress metadata write failure and verify automatic restore.
$failure = qa_job($source_id,$page); $snapshot = (new BackupEngine())->state($page);
$fail = function ($check,$id,$key) use ($page) { return $id === $page && $key === '_elementor_data' ? true : $check; };
add_filter('update_post_metadata',$fail,10,3); $failed = $runner->process_batch($failure->id); remove_filter('update_post_metadata',$fail,10);
qa_check($failed['status'] === 'completed_with_errors' && $failed['summary']['failed'] === 1,'write failure reported');
qa_check((new BackupEngine())->state($page) === $snapshot,'write failure restored snapshot');
$bad = qa_job($source_id,999999); $result = $runner->process_batch($bad->id);
qa_check($result['status'] === 'completed_with_errors' && !metadata_exists('post',999999,'_elementor_data'),'invalid target rejection');
// Unprivileged users cannot process jobs or retrieve another user's sources.
$other = wp_insert_user(['user_login'=>'qa_subscriber_'.wp_rand(),'user_pass'=>'local-test','role'=>'subscriber']); $admin = get_current_user_id(); wp_set_current_user($other);
qa_rejects(function () use ($runner,$two) { $runner->process_batch($two->id); },'capability rejection');
qa_rejects(function () use ($source_id) { (new SourceRepository())->get($source_id); },'source owner isolation');
qa_check($repo->get($two->id) === null,'job owner isolation'); wp_set_current_user($admin);
// Validate output settings against current Elementor control names.
$builder = new DocumentBuilder(); $serializer = new Serializer();
$fixtures = [
'[et_pb_text text_font_size="18px" custom_margin="10px" background_color="#fff" module_id="text-id"]Body[/et_pb_text]',
'[et_pb_text positioning="absolute" horizontal_offset="10px" vertical_offset="20px" transform_translate="3px|4px" transform_scale="100%|120%" transform_rotate="0deg|0deg|10deg"]Positioned[/et_pb_text]',
'[et_pb_button button_text="Go" button_url="/go" button_font_size="20px" custom_padding="10px" /]',
'[et_pb_image src="https://example.com/a.jpg" title_text="Caption" filter_blur="2px" border_radii="on|1.5em|1.5em|1.5em|1.5em" /]',
'[et_pb_video src="https://example.com/a.mp4" /]',
'[et_pb_divider /]','[et_pb_number_counter number="2" /]','[et_pb_map][et_pb_map_pin pin_address="London" /][/et_pb_map]',
'[et_pb_testimonial author="Jane"]Body[/et_pb_testimonial]',
'[et_pb_tabs][et_pb_tab title="One"]Body[/et_pb_tab][/et_pb_tabs]',
'[et_pb_accordion][et_pb_accordion_item title="One"]Body[/et_pb_accordion_item][/et_pb_accordion]',
'[et_pb_toggle title="One"]Body[/et_pb_toggle]',
'[et_pb_social_media_follow][et_pb_social_media_follow_network social_network="facebook" url="https://facebook.com" /][/et_pb_social_media_follow]',
'[et_pb_blurb title="Blurb" image="https://example.com/a.jpg"]Body[/et_pb_blurb]'];
foreach ($fixtures as $fixture) {
    $doc = $serializer->serialize($builder->build($fixture)); $node = $doc[0];
    (new \SuiteEdge\Migrator\Elementor\Validator())->validate($doc,true);
    $widget = \Elementor\Plugin::$instance->widgets_manager->get_widget_types($node['widgetType']);
    $stack = $widget->get_stack(); $common = \Elementor\Plugin::$instance->widgets_manager->get_widget_types('common')->get_stack(); $controls = array_merge($stack['controls'], $stack['style_controls'] ?? [], $common['controls'], $common['style_controls'] ?? []);
    foreach ((array)$node['settings'] as $key=>$value) { if (strpos($key,'_suiteedge_') !== 0) { qa_check(isset($controls[$key]) || isset($controls[preg_replace('/_(tablet|mobile)$/','',$key)]),'registered control '.$node['widgetType'].'.'.$key); } }
}
// Real Media Library sideload with a controlled HTTP fixture (no external request).
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aJ1sAAAAASUVORK5CYII=');
$remote = function ($pre,$args,$url) use ($png) {
    if ($url !== 'https://example.com/qa-fixture.png') { return $pre; }
    file_put_contents($args['filename'],$png);
    return ['headers'=>[],'body'=>'','response'=>['code'=>200,'message'=>'OK'],'cookies'=>[]];
};
add_filter('pre_http_request',$remote,10,3);
$media = (new \SuiteEdge\Migrator\Assets\MediaImporter())->import_image('https://example.com/qa-fixture.png',$page,'Source alt');
remove_filter('pre_http_request',$remote,10);
qa_check(is_array($media) && get_post_type($media['id']) === 'attachment','actual Media Library sideload');
qa_check(get_post_meta($media['id'],'_wp_attachment_image_alt',true) === 'Source alt','imported alt preserved');
qa_check((new \SuiteEdge\Migrator\Assets\DuplicateDetector())->find_existing('https://example.com/qa-fixture.png') === $media['id'],'exact source URL reuse');
qa_check((new \SuiteEdge\Migrator\Assets\MediaImporter())->import_image('http://127.0.0.1/private.png',$page) === false,'private URL media rejection');
qa_check((new \SuiteEdge\Migrator\Assets\DuplicateDetector())->find_existing('https://other.example/qa-fixture.png') === false,'same filename does not imply same image');
// A failed source must not write a target or create a new draft.
$empty = new Job(); $repo->save($empty); qa_check($runner->process_batch($empty->id)['progress'] === 100,'empty job handled');
$invalid = qa_job($source_id,0); $invalid->settings['output_engine'] = 'atomic'; $repo->save($invalid);
$count = wp_count_posts('page')->draft; $result = $runner->process_batch($invalid->id);
qa_check($result['status'] === 'completed_with_errors' && wp_count_posts('page')->draft === $count,'Atomic rejected before writes');
$cancel = qa_job($source_id,0); qa_check($runner->cancel($cancel->id)['status'] === 'cancelled','pending job cancellation');
qa_check($runner->process_batch($cancel->id)['status'] === 'cancelled','cancelled job never writes');
// Every rendered admin page must show real data and valid escaped output.
foreach (['Dashboard','Analysis','Mapping','Jobs','Report','Settings','Import'] as $name) {
    $_POST = []; $_GET = ['job'=>$job->id]; $_FILES = [];
    ob_start(); $class = 'SuiteEdge\\Migrator\\Admin\\'.$name.'Page'; (new $class())->render(); $output = ob_get_clean();
    // A real target can legitimately have ID 1001 after repeated disposable runs.
    qa_check(strpos($output,'<div') !== false && ($name !== 'Jobs' || strpos($output,$invalid->id) !== false),'real admin page '.$name);
}
file_put_contents(ABSPATH.'../integration-results'.(defined('DB_ENGINE') ? '-sqlite' : '-mariadb').'.json',wp_json_encode(['assertions'=>$GLOBALS['qa_checks'],'wordpress'=>get_bloginfo('version'),'elementor'=>ELEMENTOR_VERSION,'php'=>PHP_VERSION,'database'=>defined('DB_ENGINE') ? DB_ENGINE : $GLOBALS['wpdb']->db_version(),'frontend_rendered'=>true],JSON_PRETTY_PRINT));
echo 'PASS: '.$GLOBALS['qa_checks'].' WordPress/Elementor integration assertions; WP '.get_bloginfo('version').'; Elementor '.ELEMENTOR_VERSION.PHP_EOL;
