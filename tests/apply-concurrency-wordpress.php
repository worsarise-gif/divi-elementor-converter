<?php
/** Media callbacks deliberately simulate saves by an editor during Apply. QA only. */
if(!defined('ABSPATH')){exit(1);}wp_set_current_user(get_user_by('login','qa_admin')->ID);set_current_screen('suiteedge-migrator-mapping');
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$label);}$checks++;};
$repo=new SuiteEdge\Migrator\Jobs\JobRepository();$runner=new SuiteEdge\Migrator\Jobs\BatchRunner($repo);
foreach(['direct','saved','new_draft']as$mode){
    $page=$mode==='new_draft'?0:wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA concurrency '.$mode]);
    if($page){update_post_meta($page,'_elementor_data','original editor data');}
    $url='https://example.com/ace-race-'.wp_generate_uuid4().'.png';
    $json=wp_json_encode(['data'=>'[et_pb_image src="'.$url.'" /]','images'=>[$url=>['url'=>$url,'encoded'=>'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jR0kAAAAASUVORK5CYII=']]]);
    $source=(new SuiteEdge\Migrator\Import\SourceRepository())->save($json,'apply-concurrent-editor.json');$job=new SuiteEdge\Migrator\Jobs\Job();
    $job->settings=['output_engine'=>'classic','dry_run'=>$mode==='saved','assets'=>['import_external'],'reviewed_warnings'=>true];
    $job->items=[['source_id'=>$source,'layout_index'=>0,'name'=>$mode,'target_page_id'=>$page,'target_kind'=>'page','status'=>'pending']];$job->summary['total']=1;$repo->save($job);
    if($mode==='saved'){$runner->process_batch($job->id);(new SuiteEdge\Migrator\Jobs\SavedOutputApplier())->queue([$job->id=>[0]],true);}
    $attachments=[];$triggered=false;
    $edit=function($attachment)use(&$page,$repo,$job,&$attachments,&$triggered){$attachments[]=$attachment;$triggered=true;$page=$repo->get($job->id)->items[0]['target_page_id'];update_post_meta($page,'_elementor_data','newer edit during media preparation');};
    add_action('add_attachment',$edit);
    try{
        $r=$runner->process_batch($job->id);$saved=$repo->get($job->id);
        $check($triggered,$mode.' callback executed during real media import');
        $check(get_post_meta($page,'_elementor_data',true)==='newer edit during media preparation',$mode.' newer editor data retained');
        $check($r['status']==='completed_with_errors'&&strpos($saved->items[0]['error'],'Newer edits are protected')!==false,$mode.' stale write rejected with clear reason');
        $check(get_option('suiteedge_backup_'.$job->id.'_'.$page,null)===null,$mode.' no stale backup or recovery restore created');
    }finally{remove_action('add_attachment',$edit);foreach($attachments as$id){wp_delete_attachment($id,true);}if($page){wp_delete_post($page,true);}}
}
echo 'PASS: '.$checks.' Apply concurrent-editor protection assertions'.PHP_EOL;
