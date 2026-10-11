<?php
/** Disposable WordPress integration only: safe public REST responses are controlled. */
if (!defined('ABSPATH')) { exit(1); }
wp_set_current_user(get_user_by('login','qa_admin')->ID);set_current_screen('suiteedge-migrator-mapping');
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$label);}$checks++;};
$reject=function($fn,$label)use($check){try{$fn();}catch(Throwable $e){$check(true,$label);return;}$check(false,$label);};
$settings=new SuiteEdge\Migrator\Dynamic\SourceLinkSettings();
$check($settings->origin(' HTTPS://SuiteEdge.com:443/learning-center/?x=1#test ')==='https://suiteedge.com','source page URL normalized to origin');
$check($settings->origin('')==='','empty source remains empty');
foreach([[], 'javascript:alert(1)','https://user:pass@suiteedge.com/','https://suiteedge.com/<bad>']as$value){$reject(function()use($settings,$value){$settings->origin($value);},'invalid origin rejected');}
$check($settings->enabled([],'https://suiteedge.com'),'URL enables older form default');
$check(!$settings->enabled(['resolve_source_links_present'=>'1'],'https://suiteedge.com'),'explicit unchecked option respected');
$check($settings->enabled(['resolve_source_links_present'=>'1','resolve_source_links'=>'1'],'https://suiteedge.com'),'explicit enabled option respected');
$rewriter=new SuiteEdge\Migrator\Assets\UrlRewriter();
$check($rewriter->rewrite('https://suiteedge.com/get-started/?x=1#form','https://www.suiteedge.com',home_url())===rtrim(home_url(),'/').'/get-started/?x=1#form','canonical alias rewrites path/query/fragment');
$check($rewriter->rewrite('https://suiteedge.com.evil.example/page','https://suiteedge.com',home_url())==='https://suiteedge.com.evil.example/page','foreign prefix host not rewritten');
$check($rewriter->rewrite('http://suiteedge.com/page','https://suiteedge.com',home_url())==='http://suiteedge.com/page','HTTPS downgrade not rewritten');
$check($rewriter->rewrite('https://suiteedge.com:8443/page','https://suiteedge.com',home_url())==='https://suiteedge.com:8443/page','foreign port not rewritten');
$mode='success';$calls=[];
$http=function($pre,$args,$url)use(&$mode,&$calls){
    if(strpos($url,'https://suiteedge.com/')!==0&&strpos($url,'https://www.suiteedge.com/')!==0){return $pre;}
    $calls[]=$url;$code=200;$body=['id'=>52164,'type'=>'page','status'=>'publish','link'=>'https://suiteedge.com/get-started/'];
    if($mode==='error'){return new WP_Error('http_request_failed','controlled network failure');}
    if($mode==='blocked'){$code=403;}
    if($mode==='plain'&&strpos($url,'/wp-json/')!==false){$code=404;}
    if($mode==='foreign'){$body['link']='https://example.com/foreign/';}
    if($mode==='wrong'){$body['id']=5;}
    if($mode==='draft'){$body['status']='draft';}
    if($mode==='invalid'){$body='not json';}
    return ['headers'=>[],'body'=>is_array($body)?wp_json_encode($body):$body,'response'=>['code'=>$code],'cookies'=>[]];
};add_filter('pre_http_request',$http,10,3);
try{
    $resolver=new SuiteEdge\Migrator\Dynamic\PageLinkResolver();
    $check($resolver->resolve('52164','https://suiteedge.com')==='https://suiteedge.com/get-started/','published remote page resolved');
    $resolver->resolve('52164','https://suiteedge.com');$check(count($calls)===1,'same page cached within conversion');
    $check($resolver->diagnostics()[0]['code']==='resolved_public_page','success diagnostics recorded');
    $check((new SuiteEdge\Migrator\Dynamic\PageLinkResolver())->resolve('52164','https://www.suiteedge.com')==='https://suiteedge.com/get-started/','www canonical alias accepted');
    $mode='plain';$calls=[];$resolver=new SuiteEdge\Migrator\Dynamic\PageLinkResolver();$check($resolver->resolve('52164','https://suiteedge.com')!==null&&count($calls)===2&&strpos($calls[1],'rest_route=')!==false,'plain REST fallback after pretty route 404');
    foreach(['error'=>'source_request_failed','blocked'=>'source_http_403','foreign'=>'source_link_origin_mismatch','wrong'=>'invalid_source_response','draft'=>'invalid_source_response','invalid'=>'invalid_source_response']as$mode=>$expected){
        $calls=[];$resolver=new SuiteEdge\Migrator\Dynamic\PageLinkResolver();
        $check($resolver->resolve('52164','https://suiteedge.com')===null&&$resolver->diagnostics()[0]['code']===$expected,'safe rejection and actionable diagnostics: '.$mode);
        $check(count($calls)===1,'no duplicate requests for failure '.$mode);
    }
    $resolver=new SuiteEdge\Migrator\Dynamic\PageLinkResolver();$check($resolver->resolve('52164','',['52164'=>'/contact/'])==='/contact/','explicit mapping works without API/source');
    $resolver=new SuiteEdge\Migrator\Dynamic\PageLinkResolver();$check($resolver->resolve('52164','')===null&&$resolver->diagnostics()[0]['code']==='missing_source_origin','missing source distinct cause');
    $token='@ET-DC@'.base64_encode(wp_json_encode(['dynamic'=>true,'content'=>'post_link_url_page','settings'=>['post_id'=>'52164']])).'@';
    $raw='[et_pb_button button_text="Contact" button_url="'.$token.'" /]';
    $builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$builder->build($raw,['source_origin'=>'https://suiteedge.com']);
    $check(strpos($builder->warnings[0],'resolution is disabled')!==false,'disabled resolver has a precise repair message');
    $builder->build($raw);$check(strpos($builder->warnings[0],'Source site URL is missing')!==false,'missing URL has precise repair message');
    $mode='blocked';$builder->build($raw,['source_origin'=>'https://suiteedge.com','resolve_source_links'=>true]);
    $check(strpos($builder->warnings[0],'HTTP 403')!==false&&$builder->link_diagnostics[0]['code']==='source_http_403','HTTP cause survives conversion fallback');
    $mode='success';$source=(new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['data'=>$raw]),'source-link-form.json');
    set_transient('suiteedge_migrator_pending_files_'.get_current_user_id(),[['source_id'=>$source,'layout_index'=>0,'name'=>'Source links']],HOUR_IN_SECONDS);
    $_GET=[];$_POST=['submit_conversion'=>'1','_wpnonce'=>wp_create_nonce('suiteedge_create_job'),'target_page'=>['create_new'],'source_origin'=>'https://suiteedge.com/get-started/','execution_mode'=>'analyze'];$_REQUEST=$_POST;
    ob_start();(new SuiteEdge\Migrator\Admin\JobsPage())->render();ob_end_clean();
    $repo=new SuiteEdge\Migrator\Jobs\JobRepository();$job=$repo->get_latest_active();
    $check($job->settings['source_origin']==='https://suiteedge.com'&&$job->settings['resolve_source_links'],'actual form persists normalized URL and default resolver');
    (new SuiteEdge\Migrator\Jobs\BatchRunner($repo))->process_batch($job->id);$job=$repo->get($job->id);
    $check($job->status==='completed'&&$job->items[0]['link_resolution'][0]['code']==='resolved_public_page','actual job resolves and records link');
    $report=(new SuiteEdge\Migrator\Reports\ReportBuilder())->build($job);
    $check($report['conversion_settings']['resolve_source_links']&&strlen($report['pages'][0]['source_hash'])===64,'report includes resolver flag and source hash');
}finally{remove_filter('pre_http_request',$http,10);$_GET=[];$_POST=[];$_REQUEST=[];}
echo 'PASS: '.$checks.' source-link workflow assertions'.PHP_EOL;
