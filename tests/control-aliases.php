<?php
/** Run in separate wp-admin and frontend WP-CLI processes on a disposable QA site. */
if (!defined('ABSPATH')) { exit(1); }
wp_set_current_user(get_user_by('login','qa_admin')->ID);
$checks = 0;
$check = function ($ok,$name) use (&$checks) { if (!$ok) { throw new RuntimeException('FAIL: '.$name); } $checks++; };
$rejects = function ($callback,$name) use ($check) { try { $callback(); } catch (UnexpectedValueException $e) { $check(true,$name); return; } $check(false,$name); };
$mode = getenv('SUITEEDGE_QA_CONTROL_MODE');
$stack = Elementor\Plugin::$instance->widgets_manager->get_widget_types('image')->get_stack();
$controls = array_merge($stack['controls'],$stack['style_controls'] ?? []);
$check(in_array($mode,['admin','frontend'],true) && isset($controls['align']['options']) === ($mode === 'admin'),'expected full/optimized metadata');
$builder = new SuiteEdge\Migrator\Elementor\DocumentBuilder();
$serializer = new SuiteEdge\Migrator\Elementor\Classic\Serializer();
$validator = new SuiteEdge\Migrator\Elementor\Validator();
foreach (['left','center','right','start','end'] as $alignment) {
    foreach ([''=>'align','_tablet'=>'align_tablet','_phone'=>'align_mobile'] as $source=>$target) {
        $data = $serializer->serialize($builder->build('[et_pb_image src="https://example.com/image.png" align'.$source.'="'.$alignment.'" /]'));
        $original = $data;
        $check($validator->validate($data,true) && $data === $original && $data[0]['settings'][$target] === $alignment,'physical/native alignment preserved '.$target.' '.$alignment);
    }
}
foreach (['align','align_tablet','align_mobile'] as $key) {
    $invalid = $data; $invalid[0]['settings'][$key] = 'invented-alignment';
    $rejects(function () use ($validator,$invalid) { $validator->validate($invalid,true); },'unknown enum rejected '.$key);
}
$invalid = $data; $invalid[0]['settings']['align'] = ['left'];
$rejects(function () use ($validator,$invalid) { $validator->validate($invalid,true); },'array alignment rejected');
// Canonical flex options have selector dictionaries containing CSS expressions, not option aliases.
foreach (['column','row'] as $direction) {
    $container = [['id'=>'abcd123','elType'=>'container','settings'=>['flex_direction'=>$direction],'elements'=>[]]];
    $check($validator->validate($container,true),'canonical flex direction '.$direction);
}
if ($mode === 'admin') {
    $container[0]['settings']['flex_direction'] = 'invented-direction';
    $rejects(function () use ($validator,$container) { $validator->validate($container,true); },'unknown flex option rejected with full metadata');
}
$container[0]['settings'] = ['invented_control'=>'column'];
$rejects(function () use ($validator,$container) { $validator->validate($container,true); },'unknown control rejected');
$page = wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'Control alias regression']);
$backup = new SuiteEdge\Migrator\Import\BackupEngine(); $before = $backup->state($page);
$source = (new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['data'=>'[et_pb_image src="https://example.com/image.png" align="right" align_tablet="center" align_phone="left" /]']),'align.json');
$job = new SuiteEdge\Migrator\Jobs\Job();
$job->settings = ['output_engine'=>'classic','dry_run'=>true,'assets'=>[]];
$job->items = [['source_id'=>$source,'layout_index'=>0,'name'=>'align.json','target_page_id'=>$page,'status'=>'pending']];
$job->summary['total'] = 1; $repo = new SuiteEdge\Migrator\Jobs\JobRepository(); $repo->save($job);
$result = (new SuiteEdge\Migrator\Jobs\BatchRunner($repo))->process_batch($job->id);
$check($result['status'] === 'completed','reported dry-run failure resolved');
$check($backup->state($page) === $before,'dry-run leaves target unchanged');
$record = $repo->get($job->id);
$check($record->items[0]['elementor_data'][0]['settings']['align'] === 'right','job retains physical right alignment');
file_put_contents(ABSPATH.'../control-alias-results-'.$mode.(defined('DB_ENGINE') ? '-sqlite' : '-mariadb').'.json',wp_json_encode(['assertions'=>$checks,'mode'=>$mode,'database'=>defined('DB_ENGINE') ? DB_ENGINE : $GLOBALS['wpdb']->db_version(),'wordpress'=>get_bloginfo('version'),'elementor'=>ELEMENTOR_VERSION,'plugin'=>SUITEEDGE_MIGRATOR_VERSION,'job'=>$job->id],JSON_PRETTY_PRINT));
echo 'PASS: '.$checks.' control alias assertions ('.$mode.')'.PHP_EOL;
