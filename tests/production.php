<?php
/** Additional release regressions. Run after wordpress.php on a disposable QA site. */
if (!defined('ABSPATH')) { exit(1); }
global $wpdb;
wp_set_current_user(get_user_by('login','qa_admin')->ID);
$GLOBALS['release_checks'] = 0;
function release_check($condition,$name) { if (!$condition) { throw new RuntimeException('FAIL: '.$name); } $GLOBALS['release_checks']++; }
function release_rejects($callback,$name) { try { $callback(); } catch (Throwable $e) { release_check(true,$name); return; } release_check(false,$name); }
$source = (new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['data'=>'[et_pb_text]Release fixture[/et_pb_text]']),'release.json');
$repo = new SuiteEdge\Migrator\Jobs\JobRepository(); $runner = new SuiteEdge\Migrator\Jobs\BatchRunner($repo);
function release_job($source,$page = 0,$dry = false) {
    $job = new SuiteEdge\Migrator\Jobs\Job(); $job->settings = ['output_engine'=>'classic','dry_run'=>$dry,'assets'=>[]];
    $job->items = [['source_id'=>$source,'layout_index'=>0,'name'=>'release.json','target_page_id'=>$page,'status'=>'pending']];
    $job->summary['total'] = 1; (new SuiteEdge\Migrator\Jobs\JobRepository())->save($job); return $job;
}
// Published pages with lossy conversion warnings are protected unless explicitly reviewed.
$warn_source = (new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['data'=>'[et_pb_text unfamiliar_setting="on"]Body[/et_pb_text]']),'warnings.json');
$page = wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'Protected release page']);
$warn_job = release_job($warn_source,$page); $before = (new SuiteEdge\Migrator\Import\BackupEngine())->state($page);
release_check($runner->process_batch($warn_job->id)['status'] === 'completed_with_errors' && (new SuiteEdge\Migrator\Import\BackupEngine())->state($page) === $before,'unreviewed published target unchanged');
$reviewed = release_job($warn_source,$page); $reviewed->settings['reviewed_warnings'] = true; $repo->save($reviewed);
release_check($runner->process_batch($reviewed->id)['status'] === 'completed','reviewed warning applies');
$runner->rollback($reviewed->id);
// Unsupported fallback must never create even a draft target.
$unsupported = (new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['data'=>'[et_pb_email_optin /]']),'unsupported.json');
$job = release_job($unsupported); $drafts = wp_count_posts('page')->draft;
release_check($runner->process_batch($job->id)['status'] === 'completed_with_errors' && wp_count_posts('page')->draft === $drafts,'fallback blocked before draft creation');
// A busy page remains pending and succeeds once its live owner releases it.
$pending = release_job($source,$page); $lock = new SuiteEdge\Migrator\Jobs\LockManager(); $lock->acquire('page:'.$page);
$busy = $runner->process_batch($pending->id); $lock->release();
release_check($busy['progress'] === 0 && $repo->get($pending->id)->items[0]['status'] === 'pending','page contention never fails or advances item');
release_check($runner->process_batch($pending->id)['status'] === 'completed','page contention resume');
$runner->rollback($pending->id);
$legacy_key = 'suiteedge_lock_'.hash('sha256','legacy-probe'); add_option($legacy_key,['token'=>'pre-upgrade','created'=>1],'',false);
release_rejects(function () { (new SuiteEdge\Migrator\Jobs\LockManager())->acquire('legacy-probe'); },'pre-upgrade lock cannot be bypassed or expired');
release_check(get_option($legacy_key,null) !== null,'legacy lock preserved for confirmed-stop review'); delete_option($legacy_key);
// Crash after the write but before job bookkeeping: recognize planned/written state, never replay.
$written = release_job($source,$page); $runner->process_batch($written->id); $record = $repo->get($written->id);
$record->status = 'running'; $record->current_index = 0; $record->items[0]['status'] = 'processing'; $record->summary['completed'] = 0; $repo->save($record);
$snapshot = get_option('suiteedge_backup_'.$written->id.'_'.$page); $snapshot['written_hash'] = null; update_option('suiteedge_backup_'.$written->id.'_'.$page,$snapshot,false);
release_check($runner->process_batch($written->id)['status'] === 'completed' && $repo->get($written->id)->summary['completed'] === 1,'planned write recognized after interruption');
$runner->rollback($written->id);
// Partial interruption requires review and protects changes made after that review snapshot.
$partial = release_job($source,$page); $partial->status = 'running'; $partial->items[0]['status'] = 'processing'; $repo->save($partial);
$backup = new SuiteEdge\Migrator\Import\BackupEngine(); $backup->create_backup($page,$partial->id); $original = $backup->state($page);
update_post_meta($page,'_elementor_data','partial write');
release_check($runner->process_batch($partial->id)['status'] === 'needs_recovery','partial write stops job for review');
update_post_meta($page,'_elementor_data','newer manual edit');
release_rejects(function () use ($runner,$partial) { $runner->recover($partial->id); },'recovery rejects later edit');
release_check(get_post_meta($page,'_elementor_data',true) === 'newer manual edit','newer recovery edit untouched');
release_check($runner->review_recovery($partial->id)['status'] === 'needs_recovery','explicit recovery review refresh');
release_check($runner->recover($partial->id)['status'] === 'completed_with_errors' && $backup->state($page) === $original,'explicit recovery exact original snapshot');
// Installed controls reject malformed value shapes and unsupported enums before any write.
$builder = new SuiteEdge\Migrator\Elementor\DocumentBuilder(); $serializer = new SuiteEdge\Migrator\Elementor\Classic\Serializer();
$data = $serializer->serialize($builder->build('[et_pb_image src="https://example.com/image.png" /]'));
$invalid = $data; $invalid[0]['settings']['image'] = 'not-a-media-object';
release_rejects(function () use ($invalid) { (new SuiteEdge\Migrator\Elementor\Validator())->validate($invalid,true); },'invalid media shape');
$invalid = $data; $invalid[0]['settings']['align'] = 'invented-alignment';
release_rejects(function () use ($invalid) { (new SuiteEdge\Migrator\Elementor\Validator())->validate($invalid,true); },'invalid select option');
$data = $serializer->serialize($builder->build('[et_pb_button button_url="javascript:alert(1)" /]'));
release_rejects(function () use ($data) { (new SuiteEdge\Migrator\Elementor\Validator())->validate($data,true); },'unsafe link scheme');
// All declared Font Awesome styles and glyphs must resolve against installed library data.
foreach (['&#xf002;||fa||900'=>'fas fa-search','&#xf1a0;||fa||400'=>'fab fa-google'] as $source_icon=>$expected) { release_check((new SuiteEdge\Migrator\Mapping\IconMapper())->map($source_icon)['value'] === $expected,'exact icon '.$expected); }
// Remote dynamic link resolution validates identity/type/origin and caches only this request.
$GLOBALS['release_rest_requests'] = 0;
$rest = function ($pre,$args,$url) {
    if (strpos($url,'https://example.com/wp-json/wp/v2/pages/') !== 0) { return $pre; }
    $GLOBALS['release_rest_requests']++;
    return ['headers'=>[],'body'=>wp_json_encode(['id'=>123,'status'=>'publish','type'=>'page','link'=>'https://example.com/contact/']),'response'=>['code'=>200],'cookies'=>[]];
};
add_filter('pre_http_request',$rest,10,3); $resolver = new SuiteEdge\Migrator\Dynamic\PageLinkResolver();
release_check($resolver->resolve('123','https://example.com') === 'https://example.com/contact/','public page identity/origin resolution');
release_check($resolver->resolve('123','https://example.com') === 'https://example.com/contact/' && $GLOBALS['release_rest_requests'] === 1,'request-local link resolver cache');
release_check($resolver->resolve('456','https://example.com') === null,'different REST page ID rejected');
release_check($resolver->resolve('123','http://127.0.0.1') === null,'private source origin rejected');
remove_filter('pre_http_request',$rest,10);
// Retention protects every backup and every referenced source, including interrupted jobs.
$orphan = (new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['data'=>'[et_pb_text]Orphan[/et_pb_text]']),'orphan.json');
$orphan_record = get_option('suiteedge_source_'.$orphan); $orphan_record['created'] = time()-100*DAY_IN_SECONDS; update_option('suiteedge_source_'.$orphan,$orphan_record,false);
$old_dry = release_job($source,0,true); $runner->process_batch($old_dry->id); $old_dry = $repo->get($old_dry->id); $old_dry->created = time()-100*DAY_IN_SECONDS; $repo->save($old_dry);
$protected = $repo->get($reviewed->id); $protected->created = time()-100*DAY_IN_SECONDS; $repo->save($protected);
$cleanup = new SuiteEdge\Migrator\Storage\Retention(); update_option('suiteedge_cleanup_cursor',0,false);
// Repeated release runs accumulate history on this disposable QA site. Traverse
// enough bounded batches for the actual fixture count rather than assuming <3,000.
$retention_records = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",$wpdb->esc_like('suiteedge_job_').'%',$wpdb->esc_like('suiteedge_source_').'%'));
$retention_batches = (int)ceil($retention_records/100)+1;
for ($i=0;$i<$retention_batches;$i++) { $cleanup->run(); if ((int)get_option('suiteedge_cleanup_cursor') === 0) { break; } }
release_check((int)get_option('suiteedge_cleanup_cursor') === 0,'retention completes bounded traversal of actual history');
release_check(get_option('suiteedge_source_'.$orphan,null) === null,'unreferenced old source cleaned');
release_check($repo->get($old_dry->id) === null,'eligible old analysis job cleaned');
release_check($repo->get($reviewed->id) !== null && get_option('suiteedge_backup_'.$reviewed->id.'_'.$page,null) !== null,'backup-linked history retained');
release_check(get_option('suiteedge_source_'.$source,null) !== null,'referenced source retained');
release_check(count($repo->all(1,2)) === 2 && count($repo->all(2,2)) === 2 && $repo->all(1,2)[0]->id !== $repo->all(2,2)[0]->id,'history paginates without duplicates');
release_check($repo->count() > count($repo->all(1,2)),'total count independent of page size');
release_check(wp_next_scheduled(SuiteEdge\Migrator\Storage\Retention::HOOK) !== false,'daily retention scheduled');
file_put_contents(ABSPATH.'../production-results'.(defined('DB_ENGINE') ? '-sqlite' : '-mariadb').'.json',wp_json_encode(['assertions'=>$GLOBALS['release_checks'],'database'=>defined('DB_ENGINE') ? DB_ENGINE : $GLOBALS['wpdb']->db_version(),'wordpress'=>get_bloginfo('version'),'elementor'=>ELEMENTOR_VERSION],JSON_PRETTY_PRINT));
echo 'PASS: '.$GLOBALS['release_checks'].' production regression assertions'.PHP_EOL;
