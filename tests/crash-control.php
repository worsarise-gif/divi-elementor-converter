<?php
wp_set_current_user(get_user_by('login','qa_admin')->ID);
$mode = getenv('SUITEEDGE_CRASH_MODE'); $repo = new SuiteEdge\Migrator\Jobs\JobRepository();
if (in_array($mode,['create','create_saved'],true)) {
    $source = (new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['data'=>'[et_pb_text]Crash fixture[/et_pb_text]']),'crash.json');
    $page = wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'Original crash target','post_content'=>'Original crash text']);
    $job = new SuiteEdge\Migrator\Jobs\Job(); $job->settings = ['output_engine'=>'classic','dry_run'=>false,'assets'=>[]];
    $job->items = [['source_id'=>$source,'layout_index'=>0,'name'=>'crash.json','target_page_id'=>$page,'status'=>'pending']]; $job->summary['total'] = 1; $repo->save($job);
    if ($mode === 'create_saved') {
        $job->settings['dry_run'] = true; $repo->save($job);
        (new SuiteEdge\Migrator\Jobs\BatchRunner($repo))->process_batch($job->id);
        (new SuiteEdge\Migrator\Jobs\SavedOutputApplier())->queue([$job->id=>[0]],true);
    }
    echo 'CRASH_RESULT:'.wp_json_encode(['job'=>$job->id,'page'=>$page]).PHP_EOL;
} elseif ($mode === 'lock') {
    $lock = new SuiteEdge\Migrator\Jobs\LockManager(); $lock->acquire('crash-probe'); $lock->release(); echo 'CRASH_RESULT:'.wp_json_encode(['released'=>true]).PHP_EOL;
} elseif ($mode === 'cancel') {
    echo 'CRASH_RESULT:'.wp_json_encode((new SuiteEdge\Migrator\Jobs\BatchRunner($repo))->cancel(getenv('SUITEEDGE_CRASH_JOB'))).PHP_EOL;
} else {
    $job_id = getenv('SUITEEDGE_CRASH_JOB'); $runner = new SuiteEdge\Migrator\Jobs\BatchRunner($repo); $result = $runner->process_batch($job_id);
    if ($result['status'] === 'needs_recovery') { $result = $runner->recover($job_id); }
    else { $result = $runner->rollback($job_id); }
    $job = $repo->get($job_id); $id = $job->items[0]['target_page_id'];
    echo 'CRASH_RESULT:'.wp_json_encode(['status'=>$result['status'],'item'=>$job->items[0]['status'],'content'=>get_post($id)->post_content,'builder_data'=>get_post_meta($id,'_elementor_data',true),'failed'=>$job->summary['failed']]).PHP_EOL;
}
