<?php
/** Disposable WordPress only: typed lookup, source fixture, Apply, render and rollback. */
if (!defined('ABSPATH') || !getenv('SUITEEDGE_QA_EVIDENCE')) { exit(1); }
wp_set_current_user(get_user_by('login','qa_admin')->ID);set_current_screen('suiteedge-migrator-mapping');
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$label);}$checks++;};
$dir=getenv('SUITEEDGE_QA_EVIDENCE');$mode='success';$calls=[];
$media=json_decode(file_get_contents($dir.'/pools-media-1543.json'),true);
$http=function($pre,$args,$url)use(&$mode,&$calls,$media){
    if(strpos($url,'https://bestpoolsllc.com/')!==0){return $pre;}
    $calls[]=$url;$code=200;$body=$media;
    if(strpos($url,'/pages/')!==false){$body=['id'=>1543,'type'=>'page','status'=>'publish','link'=>'https://bestpoolsllc.com/a-page/'];}
    if(strpos($url,'/posts/')!==false){$body=['id'=>1543,'type'=>'post','status'=>'publish','link'=>'https://bestpoolsllc.com/a-post/'];}
    if($mode==='error'){return new WP_Error('http_request_failed','controlled request failure');}
    if($mode==='blocked'){$code=403;}
    if($mode==='plain'&&strpos($url,'/wp-json/')!==false){$code=404;}
    if($mode==='wrong_id'){$body['id']=1544;}
    if($mode==='wrong_type'){$body['type']='page';}
    if($mode==='private'){$body['status']='private';}
    if($mode==='trash'){$body['status']='trash';}
    if($mode==='foreign'){$body['link']='https://example.com/file/';}
    if($mode==='missing_link'){unset($body['link']);}
    if($mode==='invalid'){$body='invalid JSON';}
    return ['headers'=>[],'body'=>is_array($body)?wp_json_encode($body):$body,'response'=>['code'=>$code],'cookies'=>[]];
};add_filter('pre_http_request',$http,10,3);
$token=function($type='attachment'){return '@ET-DC@'.base64_encode(wp_json_encode(['dynamic'=>true,'content'=>'post_link_url_'.$type,'settings'=>['post_id'=>'1543']])).'@';};
$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$serializer=new SuiteEdge\Migrator\Elementor\Classic\Serializer();$validator=new SuiteEdge\Migrator\Elementor\Validator();
$options=['source_origin'=>'https://bestpoolsllc.com','resolve_source_links'=>true];
$source='[et_pb_button button_text="Download Comparison Chart" button_url="'.$token().'" url_new_window="on" /]';
$flat=function($nodes)use(&$flat){$all=[];foreach($nodes as$n){$all[]=$n;$all=array_merge($all,$flat($n['elements']));}return $all;};
try{
    $resolver=new SuiteEdge\Migrator\Dynamic\PageLinkResolver();
    foreach(['page'=>'a-page/','post'=>'a-post/','attachment'=>'pool-comparative_digital_opt/']as$type=>$path){$check($resolver->resolve('1543',$options['source_origin'],[],$type)==='https://bestpoolsllc.com/'.$path,'correct route and permalink: '.$type);}
    $check(count($calls)===3&&count($resolver->diagnostics())===3,'same numeric ID retains separate type diagnostics');
    $resolver->resolve('1543',$options['source_origin'],[],'attachment');$check(count($calls)===3,'attachment lookup cached');
    $check($resolver->resolve('1543','',['1543'=>'/uploads/chart.pdf'],'attachment')==='/uploads/chart.pdf','explicit attachment URL bypasses REST');
    foreach([[], 'javascript:alert(1)','//foreign.example/path','https://user:pass@example.com/x',"/\\foreign.example/x"]as$bad){$check((new SuiteEdge\Migrator\Dynamic\PageLinkResolver())->resolve('1543','',['1543'=>$bad],'attachment')===null,'invalid explicit mapping rejected');}
    $mode='plain';$calls=[];$check((new SuiteEdge\Migrator\Dynamic\PageLinkResolver())->resolve('1543',$options['source_origin'],[],'attachment')===$media['link']&&count($calls)===2,'attachment plain REST route fallback');
    foreach(['blocked'=>'source_http_403','error'=>'source_request_failed','wrong_id'=>'invalid_source_response','wrong_type'=>'invalid_source_response','private'=>'invalid_source_response','trash'=>'invalid_source_response','foreign'=>'source_link_origin_mismatch','missing_link'=>'invalid_source_response','invalid'=>'invalid_source_response']as$mode=>$code){
        $calls=[];$r=new SuiteEdge\Migrator\Dynamic\PageLinkResolver();$check($r->resolve('1543',$options['source_origin'],[],'attachment')===null&&$r->diagnostics()[0]['code']===$code,'actionable lookup failure: '.$mode);
        $r->resolve('1543',$options['source_origin'],[],'attachment');$check(count($calls)===1,'failure cached without losing cause: '.$mode);
        $data=$serializer->serialize($builder->build($source,$options));$check($data[0]['widgetType']==='shortcode'&&strpos($builder->warnings[0],'source attachment 1543')!==false,'failed attachment retains protected source: '.$mode);
    }
    $mode='success';$calls=[];$data=$serializer->serialize($builder->build($source,$options));
    $check($data[0]['settings']['link']['url']===$media['link']&&$data[0]['settings']['link']['url']!==$media['source_url'],'Divi attachment permalink preserved rather than substituted PDF file');
    $check($data[0]['settings']['link']['is_external']==='on'&&!$builder->warnings&&$validator->validate($data,true),'button target and native schema accepted');
    $warnings=[];$info=[];(new SuiteEdge\Migrator\Assets\AssetProcessor())->process($data,0,$options,$warnings,[],$info);$check(!$warnings&&$data[0]['settings']['link']['url']===$media['link'],'Apply preparation does not guess an attachment page on destination');
    $library=Elementor\Plugin::$instance->templates_manager->get_source('local');$template=$library->save_item(['title'=>'QA Attachment permalink','type'=>'page','content'=>$data,'page_settings'=>[]]);if(is_wp_error($template)){throw new RuntimeException($template->get_error_message());}$inserted=$library->get_data(['template_id'=>$template]);$copy=$inserted['content'];$check($copy[0]['id']!==$data[0]['id']&&$validator->validate($copy,true),'native template insertion regenerates valid element IDs');$warnings=[];(new SuiteEdge\Migrator\Assets\AssetProcessor())->process($copy,0,$options,$warnings,[],$info);$check(!$warnings&&$copy[0]['settings']['link']['url']===$media['link'],'attachment provenance survives native template import');
    $testjob=new SuiteEdge\Migrator\Jobs\Job();$testjob->items=[['name'=>'typed-link','information'=>$builder->information]];$d=(new SuiteEdge\Migrator\Reports\WarningReport())->diagnostics($testjob);$check(in_array('source_attachment_dependency',array_column($d['entries'],'code'),true),'source dependency is explicit actionable information');
    $builder->build($source,['source_origin'=>$options['source_origin']]);$check(strpos($builder->warnings[0],'resolution is disabled')!==false,'attachment lookup respects opt-out');
    $local=wp_insert_post(['post_type'=>'attachment','post_status'=>'inherit','post_title'=>'QA attachment link','guid'=>home_url('/wp-content/uploads/qa-chart.pdf')]);
    $check((new SuiteEdge\Migrator\Dynamic\PageLinkResolver())->resolve((string)$local,home_url(),[],'attachment')===get_permalink($local),'same-site attachment only resolved by correct post type');
    $check((new SuiteEdge\Migrator\Dynamic\PageLinkResolver())->resolve((string)$local,home_url(),[],'page')===null,'attachment ID never reused as a local page');
    $repo=new SuiteEdge\Migrator\Jobs\JobRepository();$runner=new SuiteEdge\Migrator\Jobs\BatchRunner($repo);$backup=new SuiteEdge\Migrator\Import\BackupEngine();$sources=new SuiteEdge\Migrator\Import\SourceRepository();
    $raw=file_get_contents('C:/Users/ay/Downloads/Fiberglass Pools.json');$original_report=json_decode(file_get_contents('C:/Users/ay/Downloads/migration-report-0d983d88-d3ee-4f22-b100-4da8eedc6ad7.json'),true);
    $check(hash('sha256',$raw)===$original_report['pages'][0]['source_hash'],'exact reported source fixture verified');
    $source_id=$sources->save($raw,'Fiberglass Pools.json');$results=[];
    foreach(['direct','saved']as$flow){
        $target=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA Pools '.$flow,'post_content'=>'Original protected '.$flow]);$before=$backup->fingerprint($target);
        $job=new SuiteEdge\Migrator\Jobs\Job();$job->settings=array_merge($original_report['conversion_settings'],['output_engine'=>'classic','dry_run'=>$flow==='saved']);$job->items=[['source_id'=>$source_id,'layout_index'=>0,'name'=>'Fiberglass Pools.json','target_page_id'=>$target,'status'=>'pending']];$job->summary['total']=1;$repo->save($job);
        $calls=[];$runner->process_batch($job->id);$converted=$repo->get($job->id);
        $check($converted->status==='completed'&&!$converted->items[0]['warnings'],'real '.$flow.' conversion completes without actionable warnings');
        $check(count($calls)===1&&$converted->items[0]['link_resolution'][0]['source_type']==='attachment','real conversion performs one typed media lookup');
        if($flow==='saved'){$check($before===$backup->fingerprint($target),'analysis leaves target untouched');$hash=$converted->items[0]['output_hash'];(new SuiteEdge\Migrator\Jobs\SavedOutputApplier())->queue([$job->id=>[0]],true);$calls=[];$runner->process_batch($job->id);$converted=$repo->get($job->id);$check(!$calls&&$converted->items[0]['output_hash']===$hash,'saved Apply reuses output without another lookup/conversion');}
        $check($converted->items[0]['status']==='converted','real '.$flow.' Apply succeeds');$nodes=$flat($converted->items[0]['elementor_data']);$images=array_values(array_filter($nodes,function($n){return ($n['widgetType']??'')==='image';}));$buttons=array_values(array_filter($nodes,function($n){return ($n['widgetType']??'')==='button';}));
        $check(count($images)===32&&count($buttons)===1,'all 32 image modules and button preserved');
        foreach($images as$image){$check($image['settings']['width']['size']===100&&strpos($image['settings']['image']['url'],home_url())===0,'pool image full width and local embedded asset');}
        $check($buttons[0]['settings']['link']['url']===$media['link'],'correct download survives '.$flow.' Apply');
        $padding=$buttons[0]['settings']['text_padding'];$check([$padding['top'],$padding['right'],$padding['bottom'],$padding['left']]===['5','30','5','30'],'actual comparison button padding preserved on '.$flow.' Apply');
        $html=Elementor\Plugin::$instance->frontend->get_builder_content_for_display($target);$check(strpos($html,'Download Comparison Chart')!==false&&strpos($html,$media['link'])!==false&&strpos($html,'@ET-DC@')===false,'frontend contains working native button and no raw binding');
        $report=(new SuiteEdge\Migrator\Reports\ReportBuilder())->build($converted);$check($report['diagnostics']['counts']['error']===0&&$report['diagnostics']['counts']['warning']===0,'new report has no errors/actionable warnings');
        $results[$flow]=['job_id'=>$job->id,'target'=>$target,'status'=>$converted->status,'diagnostics'=>$report['diagnostics']['counts'],'images'=>count($images),'button_url'=>$buttons[0]['settings']['link']['url']];
        if($flow==='direct'){
            // Keep a separate render copy; rollback acceptance still checks the actual migrated target.
            $preview=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'QA Pools browser']);update_post_meta($preview,'_wp_page_template','elementor_canvas');update_post_meta($preview,'_elementor_edit_mode','builder');Elementor\Plugin::$instance->documents->get($preview)->save(['elements'=>$converted->items[0]['elementor_data'],'settings'=>[]]);(new SuiteEdge\Migrator\Elementor\CssRegenerator())->regenerate($preview);$results['preview_url']=get_permalink($preview);
        }
        $runner->rollback($job->id);$check($backup->fingerprint($target)===$before,'exact rollback restores '.$flow.' target');
    }
    // Actual apply guard remains intact with a controlled failed media API.
    $target=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA blocked attachment','post_content'=>'Keep original']);$before=$backup->fingerprint($target);$s=$sources->save(wp_json_encode(['data'=>$source]),'attachment-blocker.json');$job=new SuiteEdge\Migrator\Jobs\Job();$job->settings=array_merge($options,['output_engine'=>'classic','dry_run'=>false,'assets'=>[]]);$job->items=[['source_id'=>$s,'layout_index'=>0,'name'=>'blocked','target_page_id'=>$target,'status'=>'pending']];$job->summary['total']=1;$repo->save($job);$mode='blocked';$runner->process_batch($job->id);$check($repo->get($job->id)->status==='completed_with_errors'&&$before===$backup->fingerprint($target),'failed lookup blocks writes and preserves target');
    $mode='success';$small=includes_url('images/w-logo-blue-white-bg.png');$width_source='[et_pb_section][et_pb_row width="80%" max_width="600px"][et_pb_column type="4_4"][et_pb_image src="'.$small.'" force_fullwidth="on" force_fullwidth_tablet="off" force_fullwidth_phone="on" force_fullwidth_last_edited="on|desktop" module_id="width-switch" url="/example" /][et_pb_image src="'.$small.'" force_fullwidth="on" width="40%" max_width="180px" module_id="width-cap" /][/et_pb_column][/et_pb_row][/et_pb_section]';
    $width_data=$serializer->serialize($builder->build($width_source));$check($validator->validate($width_data,true),'responsive full-width controls validate native schema');$page=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'QA Full-width browser']);update_post_meta($page,'_wp_page_template','elementor_canvas');update_post_meta($page,'_elementor_edit_mode','builder');Elementor\Plugin::$instance->documents->get($page)->save(['elements'=>$width_data,'settings'=>[]]);(new SuiteEdge\Migrator\Elementor\CssRegenerator())->regenerate($page);$results['width_url']=get_permalink($page);
    $results['assertions']=$checks;$results['wordpress']=get_bloginfo('version');$results['elementor']=ELEMENTOR_VERSION;file_put_contents($dir.'/pools-wordpress-'.(defined('DB_ENGINE')?'sqlite':'mariadb').'.json',wp_json_encode($results,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
}finally{remove_filter('pre_http_request',$http,10);}
echo 'PASS: '.$checks.' attachment/source-page WordPress assertions'.PHP_EOL;
