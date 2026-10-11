<?php
/** Execute with wp eval-file on a disposable QA site only. */
if (!defined('ABSPATH') || !get_user_by('login','qa_admin')) { throw new RuntimeException('Disposable QA site required.'); }
wp_set_current_user(get_user_by('login','qa_admin')->ID); set_current_screen('suiteedge-migrator-jobs');
$checks = 0; $check = function ($ok,$label) use (&$checks) { if (!$ok) { throw new RuntimeException('FAIL: '.$label); } $checks++; };
$reject = function ($fn,$label) use ($check) { try { $fn(); } catch (Throwable $e) { $check(true,$label); return; } $check(false,$label); };
$repo = new SuiteEdge\Migrator\Jobs\JobRepository(); $runner = new SuiteEdge\Migrator\Jobs\BatchRunner($repo); $apply = new SuiteEdge\Migrator\Jobs\SavedOutputApplier(); $backup = new SuiteEdge\Migrator\Import\BackupEngine();
$make_page = function ($title) { return wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>$title,'post_content'=>'Original '.$title]); };
$p1 = $make_page('QA saved one'); $p2 = $make_page('QA saved two'); $p3 = $make_page('QA ignored three');
$source = (new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['context'=>'et_builder','data'=>['1'=>'[et_pb_text]<p>First result</p>[/et_pb_text]','2'=>'[et_pb_text]<p>Second result</p>[/et_pb_text]','3'=>'[et_pb_unknown /]']]),'Bulk.json');
$pending = []; foreach (['One','Two','Unsupported'] as $i=>$name) { $pending[] = ['source_id'=>$source,'layout_index'=>$i,'name'=>$name]; }
$key = 'suiteedge_migrator_pending_files_'.get_current_user_id();
set_transient($key,$pending,HOUR_IN_SECONDS);
$reject(function () use ($p1) { (new SuiteEdge\Migrator\Import\BatchImport())->create([$p1],['output_engine'=>'classic','dry_run'=>true],[]); },'empty selection rejected');
$reject(function () use ($p1) { (new SuiteEdge\Migrator\Import\BatchImport())->create([$p1,$p1],['output_engine'=>'classic','dry_run'=>true],[0,1]); },'duplicate target rejected');
$reject(function () use ($p1) { (new SuiteEdge\Migrator\Import\BatchImport())->create([$p1],['output_engine'=>'classic','dry_run'=>true],[0,0]); },'duplicate source selection rejected');
$job = (new SuiteEdge\Migrator\Import\BatchImport())->create([$p1,$p2,$p3],['output_engine'=>'classic','dry_run'=>true,'assets'=>[]],[0,1]);
$check(count($job->items) === 2 && $job->items[1]['target_page_id'] === $p2,'bulk selects only two original mappings');
$check(array_keys(get_transient($key)) === [2] && get_transient($key)[2]['layout_index'] === 2,'unchecked source remains available with its original index');
$reject(function () use ($p1) { (new SuiteEdge\Migrator\Import\BatchImport())->create([$p1],['output_engine'=>'classic','dry_run'=>true]); },'consumed session cannot create duplicate job');
$before1 = $backup->fingerprint($p1); $before2 = $backup->fingerprint($p2); $count = $repo->count();
$runner->process_batch($job->id,5); $job = $repo->get($job->id);
$check($job->status === 'completed' && $job->items[0]['status'] === 'analyzed' && $job->items[1]['status'] === 'analyzed','per-page bulk analysis completes');
$check($backup->fingerprint($p1) === $before1 && $backup->fingerprint($p2) === $before2,'analysis preserves both pages');
$saved = $job->items[0]['elementor_data'];
// Changing the source proves saved Apply never rebuilds it.
$source_record = get_option('suiteedge_source_'.$source); $source_record['json'] = wp_json_encode(['context'=>'et_builder','data'=>['1'=>'[et_pb_text]Unexpected replacement[/et_pb_text]','2'=>'[et_pb_text]Replacement two[/et_pb_text]']]); update_option('suiteedge_source_'.$source,$source_record,false);
$queued = $apply->queue([$job->id=>[0,1]],true); $check($queued['queued'] === 2 && $repo->count() === $count,'bulk apply reuses same job');
$reject(function () use ($apply,$job) { $apply->queue([$job->id=>[0]],true); },'duplicate in-flight Apply blocked');
$runner->process_batch($job->id,5); $job = $repo->get($job->id);
$check($job->items[0]['status'] === 'converted' && $job->items[1]['status'] === 'converted','both saved results applied');
$check(json_decode(get_post_meta($p1,'_elementor_data',true),true) === json_decode(wp_json_encode($saved),true),'exact saved output applied despite changed source');
$check(strpos(get_post_meta($p2,'_elementor_data',true),'Second result') !== false && strpos(get_post_meta($p1,'_elementor_data',true),'Second result') === false,'bulk output cannot swap targets');
$after1 = $backup->fingerprint($p1); $after2 = $backup->fingerprint($p2); $check($apply->queue([$job->id=>[0,1]],true)['queued'] === 0,'completed Apply idempotent');
$check($backup->fingerprint($p1) === $after1 && $repo->count() === $count,'repeat does not write or create jobs');
$check($apply->clear([$job->id])['results'][0]['success'],'completed job cleared');
$check($repo->get($job->id)->archived && !in_array($job->id,array_map(function ($j) { return $j->id; },$repo->all(1,100)),true),'cleared job hidden from normal list');
$check(in_array($job->id,array_map(function ($j) { return $j->id; },$repo->all(1,100,true)),true),'cleared report remains accessible');
$check($backup->fingerprint($p1) === $after1 && $backup->fingerprint($p2) === $after2,'clearing does not modify pages');
$apply->clear([$job->id],true); $runner->rollback($job->id);
$check($backup->fingerprint($p1) === $before1 && $backup->fingerprint($p2) === $before2,'restored job retains both rollback backups');
$make = function ($id,$raw = '[et_pb_text]Saved output[/et_pb_text]') use ($repo, $runner) {
    $s = (new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['context'=>'et_builder','data'=>['1'=>$raw]]),'One.json');
    $j = new SuiteEdge\Migrator\Jobs\Job(); $j->settings = ['output_engine'=>'classic','dry_run'=>true,'assets'=>[]]; $j->items = [['source_id'=>$s,'layout_index'=>0,'name'=>'One','target_page_id'=>$id,'status'=>'pending']]; $j->summary['total'] = 1; $repo->save($j); $runner->process_batch($j->id); return $repo->get($j->id);
};
$stale = $make($p1); wp_update_post(['ID'=>$p1,'post_content'=>'New edit']);
$reject(function () use ($apply,$stale) { $apply->queue([$stale->id=>[0]],true); },'newer page edits block queue');
$fresh = $make($p2); $apply->queue([$fresh->id=>[0]],true); wp_update_post(['ID'=>$p2,'post_content'=>'New edit after queue']); $runner->process_batch($fresh->id);
$check($repo->get($fresh->id)->items[0]['status'] === 'analyzed' && !empty($repo->get($fresh->id)->items[0]['error']) && get_post($p2)->post_content === 'New edit after queue','edits after queue block write and retain completed output');
$check($apply->clear([$fresh->id])['results'][0]['success'],'failed job can be cleared');
$bad = $make($p3,'[et_pb_unknown /]'); $reject(function () use ($apply,$bad) { $apply->queue([$bad->id=>[0]],true); },'unsupported saved output blocked');
$tamper = $make($p3); $original = $tamper->items[0]['target_page_id']; $tamper->items[0]['target_page_id'] = $p1; $repo->save($tamper);
$reject(function () use ($apply,$tamper) { $apply->queue([$tamper->id=>[0]],true); },'changed target mapping blocked');
$tamper->items[0]['target_page_id'] = $original; $tamper->items[0]['elementor_data'][0]['settings']['editor'] = 'Tampered'; $repo->save($tamper);
$reject(function () use ($apply,$tamper) { $apply->queue([$tamper->id=>[0]],true); },'changed saved tree blocked');
$new = $make(0); $pages = wp_count_posts('page')->draft; $apply->queue([$new->id=>[0]],true); $runner->process_batch($new->id); $new = $repo->get($new->id);
$check($new->items[0]['status'] === 'converted' && get_post_status($new->items[0]['target_page_id']) === 'draft','new mapped draft created from saved output');
$check((int)wp_count_posts('page')->draft === (int)$pages+1 && $apply->queue([$new->id=>[0]],true)['queued'] === 0,'repeated Apply cannot duplicate draft');
$partial = $make($p3); $partial->status = 'completed_with_errors'; $partial->items[] = ['name'=>'Failed page','target_page_id'=>$p1,'status'=>'failed']; $partial->current_index = 2; $partial->summary['total'] = 2; $partial->summary['failed'] = 1; $repo->save($partial);
$check($apply->eligible($partial) === [0],'partial job exposes only completed output'); $apply->queue([$partial->id=>[0]],true); $runner->process_batch($partial->id,5); $partial = $repo->get($partial->id);
$check($partial->items[0]['status'] === 'converted' && $partial->items[1]['status'] === 'failed','partial job applies valid result without retrying failed one');
$pending_job = new SuiteEdge\Migrator\Jobs\Job(); $pending_job->items = [['status'=>'pending']]; $repo->save($pending_job); $check($apply->clear([$pending_job->id])['results'][0]['success'] && $repo->get($pending_job->id)->status === 'cancelled','unused pending job cancelled before clearing');
$active = new SuiteEdge\Migrator\Jobs\Job(); $active->status = 'running'; $active->items = [['status'=>'processing']]; $repo->save($active); $check(!$apply->clear([$active->id])['results'][0]['success'] && !$repo->get($active->id)->archived,'running item protected from clearing');
$history = [];
for ($i = 0; $i < 105; $i++) { $old = new SuiteEdge\Migrator\Jobs\Job(); $old->status = 'cancelled'; $old->archived = $i % 2 === 0; $old->items = [['status'=>'pending']]; $repo->save($old); $history[] = $old->id; }
$check(($repo->get_latest_active()->id ?? null) === $active->id,'large cleared/cancelled history cannot hide active bulk queue');
foreach ($history as $id) { delete_option('suiteedge_job_'.$id); }
$active->status = 'needs_recovery'; $active->items[0]['status'] = 'needs_recovery'; $repo->save($active); $check(!$apply->clear([$active->id])['results'][0]['success'],'recovery jobs protected');
$active->status = 'cancelled'; $active->items[0]['status'] = 'failed'; $repo->save($active); $apply->clear([$active->id]);
$conflict1 = $make($p1); $conflict2 = $make($p1); $reject(function () use ($apply,$conflict1,$conflict2) { $apply->queue([$conflict1->id=>[0],$conflict2->id=>[0]],true); },'cross-job duplicate targets rejected before any queue');
$check($repo->get($conflict1->id)->status === 'completed' && $repo->get($conflict2->id)->status === 'completed','bulk preflight failure leaves all jobs unchanged');
$public = $make_page('QA published'); wp_update_post(['ID'=>$public,'post_status'=>'publish']); $warning = $make($public); $warning->items[0]['warnings'] = ['Review difference']; $repo->save($warning);
$reject(function () use ($apply,$warning) { $apply->queue([$warning->id=>[0]],false); },'published warnings require acknowledgement'); $apply->queue([$warning->id=>[0]],true); $runner->process_batch($warning->id); $check($repo->get($warning->id)->items[0]['status'] === 'converted','reviewed published output applies');
$media_review = $make($public,'[et_pb_text]<a href="https://suiteedge.com/absent-qa-page/">Missing target link</a>[/et_pb_text]'); $media_review->settings['source_origin'] = 'https://suiteedge.com'; $repo->save($media_review); $protected = $backup->fingerprint($public);
$apply->queue([$media_review->id=>[0]],false); $runner->process_batch($media_review->id); $media_review = $repo->get($media_review->id);
$check($media_review->items[0]['status'] === 'analyzed' && !empty($media_review->items[0]['error']) && $backup->fingerprint($public) === $protected,'new asset/link warnings protect publication and retain saved result');
$apply->queue([$media_review->id=>[0]],true); $runner->process_batch($media_review->id); $media_review = $repo->get($media_review->id);
$check($media_review->items[0]['status'] === 'converted' && $media_review->summary['failed'] === 0,'reviewed preparation failure retries saved output without reconversion');
$owner = get_current_user_id(); $other = wp_create_user('qa_bulk_'.wp_generate_password(8,false),'local-qa-only'); wp_set_current_user($other);
$reject(function () use ($apply,$warning) { $apply->queue([$warning->id=>[0]],true); },'non-admin application blocked'); $check(!$repo->get($warning->id),'foreign job inaccessible'); wp_set_current_user($owner);
$cross1 = $make($p1); $cross2 = $make($p2); $check($apply->queue([$cross1->id=>[0],$cross2->id=>[0]],true)['queued'] === 2,'distinct completed jobs queue in one bulk action');
$runner->process_batch($cross1->id); $runner->process_batch($cross2->id); $check($repo->get($cross1->id)->items[0]['status'] === 'converted' && $repo->get($cross2->id)->items[0]['status'] === 'converted','cross-job bulk applies each mapped page');
$legacy = $make($make_page('QA older analysis')); unset($legacy->items[0]['output_hash'],$legacy->items[0]['conversion_target'],$legacy->items[0]['target_hash']); $repo->save($legacy); $apply->queue([$legacy->id=>[0]],true); $runner->process_batch($legacy->id); $check($repo->get($legacy->id)->items[0]['status'] === 'converted','older completed output can apply without reconversion');
$inline = [['settings'=>['tabs'=>[['_id'=>'aaaaaaa','tab_content'=>'<a href="https://suiteedge.com/">Home</a>']]],'elements'=>[]]]; $inline_warnings = []; (new SuiteEdge\Migrator\Assets\AssetProcessor())->process($inline,0,['source_origin'=>'https://suiteedge.com'],$inline_warnings); $check(strpos($inline[0]['settings']['tabs'][0]['tab_content'],home_url()) !== false,'accordion answer links processed');
$style = '[et_pb_accordion module_id="qa-accordion-second" toggle_font="Arial|700|||||||" toggle_font_size="24px" toggle_font_size_phone="18px" body_font_size="16px" body_line_height="1.8em" border_width_all="2px" border_color_all="#123456"][et_pb_accordion_item title="First" open="off"]<p>First answer</p>[/et_pb_accordion_item][et_pb_accordion_item title="Second" open="on" custom_padding="10px|1em|12px|2em"]<p>Second answer</p>[/et_pb_accordion_item][/et_pb_accordion]';
$style .= '[et_pb_accordion module_id="qa-accordion-closed"][et_pb_accordion_item title="Closed" open="off"]<p>Answer</p>[/et_pb_accordion_item][/et_pb_accordion]';
$style .= '[et_pb_button module_id="qa-padding" button_text="Padding" button_use_icon="off" button_text_size="20px" custom_padding=".5em|20px||2rem" custom_padding_tablet="8px|16px|12px|24px" custom_padding_phone="0px||4px|" /]';
$data = (new SuiteEdge\Migrator\Elementor\Classic\Serializer())->serialize((new SuiteEdge\Migrator\Elementor\DocumentBuilder())->build($style)); $check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data,true),'new native accordion and button controls validate');
$library = Elementor\Plugin::$instance->templates_manager->get_source('local'); $template = $library->save_item(['title'=>'QA portable improvements','type'=>'page','content'=>$data,'page_settings'=>[]]); if (is_wp_error($template)) { throw new RuntimeException($template->get_error_message()); }
$inserted = $library->get_data(['template_id'=>$template]); $check($inserted['content'][0]['id'] !== $data[0]['id'],'actual template insertion regenerates element IDs');
$check(count($inserted['content']) === count($data) && strpos(wp_json_encode($inserted['content']),'suiteedge-') !== false,'native insertion preserves every root and portable scope');
$preview = $make_page('QA improvements preview'); wp_update_post(['ID'=>$preview,'post_status'=>'publish']); update_post_meta($preview,'_wp_page_template','elementor_canvas'); update_post_meta($preview,'_elementor_edit_mode','builder'); Elementor\Plugin::$instance->documents->get($preview)->save(['elements'=>$inserted['content'],'settings'=>[]]); (new SuiteEdge\Migrator\Elementor\CssRegenerator())->regenerate($preview);
$ui1 = $make($make_page('QA UI individual')); $ui2 = $make($make_page('QA UI bulk two')); $ui3 = $make($make_page('QA UI bulk three'));
$record = ['assertions'=>$checks,'url'=>get_permalink($preview),'individual_job'=>$ui1->id,'bulk_jobs'=>[$ui2->id,$ui3->id],'archive_job'=>$ui1->id,'plugin'=>SUITEEDGE_MIGRATOR_VERSION];
file_put_contents(dirname(__DIR__,2).'/.qa-runtime/improvements-results-'.((defined('DB_ENGINE') && DB_ENGINE === 'sqlite') ? 'sqlite' : 'mariadb').'.json',wp_json_encode($record,JSON_PRETTY_PRINT));
echo 'PASS: '.$checks.' improvements WordPress assertions'.PHP_EOL;
