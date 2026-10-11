<?php
/** Explicit native replacements and the user's five-page batch, on disposable QA only. */
if(!defined('ABSPATH')){exit(1);}wp_set_current_user(get_user_by('login','qa_admin')->ID);set_current_screen('suiteedge-migrator-mapping');
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$label);}$checks++;};
$reject=function($fn,$label)use($check){try{$fn();}catch(Throwable $e){$check(true,$label);return;}$check(false,$label);};
$replacement=new SuiteEdge\Migrator\Import\TemplateReplacement();$library=Elementor\Plugin::$instance->templates_manager->get_source('local');
$settings=['posts_per_page'=>3,'orderby'=>'date','order'=>'desc','columns'=>'3','columns_tablet'=>'2','columns_mobile'=>'1','show_title'=>'yes','show_image'=>'yes','show_excerpt'=>'','show_meta'=>'yes','show_date'=>'yes','show_author'=>'','show_more'=>'yes'];
// Settings below are real UAE Basic Posts controls, not a made-up static grid.
$settings['show_read_more']='yes';unset($settings['show_more']);
$settings['__globals__']=['read_more_color'=>'globals/colors?id=accent'];
$grid=[['id'=>'aaaaaaa','elType'=>'container','settings'=>['content_width'=>'full','flex_direction'=>'column'],'elements'=>[['id'=>'bbbbbbb','elType'=>'widget','widgetType'=>'hfe-basic-posts','settings'=>$settings,'elements'=>[]]]]];
// Assert availability/control compatibility before saving any template.
(new SuiteEdge\Migrator\Elementor\Validator())->validate($grid,true);
$template=$library->save_item(['title'=>'QA Learning Center native blog replacement','type'=>'container','content'=>$grid,'page_settings'=>[]]);if(is_wp_error($template)){throw new RuntimeException($template->get_error_message());}
$stored=json_decode(get_post_meta($template,'_elementor_data',true),true);$stored_id=$stored[0]['elements'][0]['id'];
$raw='[et_pb_section][et_pb_row][et_pb_column type="4_4"][dgbm_blog_module show_excerpt="off" /][/et_pb_column][/et_pb_row][/et_pb_section]';
$source=(new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['data'=>$raw]),'unsupported-blog.json');
$check($replacement->validate([$source=>['dgbm_blog_module'=>$template]])[$source]['dgbm_blog_module']===$template,'replacement validated and source scoped');
$check($replacement->validate([$source=>['dgbm_blog_module'=>0]],[$source=>['dgbm_blog_module'=>' '.$template.' ']])[$source]['dgbm_blog_module']===$template,'manual ID takes priority and selects any editable template');
$check($replacement->validate([$source=>['dgbm_blog_module'=>$template]],[$source=>['dgbm_blog_module'=>'']])[$source]['dgbm_blog_module']===$template,'blank override preserves dropdown selection');
foreach(['0','bad',[]]as$bad_id){$reject(function()use($replacement,$source,$bad_id){$replacement->validate([],[$source=>['dgbm_blog_module'=>$bad_id]]);},'invalid manual template ID rejected');}
$pending_notices=[];set_error_handler(function($severity,$message)use(&$pending_notices){$pending_notices[]=$message;return true;});
try{$pending=$replacement->pending([['source_id'=>$source,'layout_index'=>0]]);}finally{restore_error_handler();}
$check(!$pending_notices,'module chooser scan emits no PHP notices or warnings');
$check($pending[$source]['modules']['dgbm_blog_module']===1,'optional replacement instance detected');
$reject(function()use($replacement,$source,$template){$replacement->validate([$source=>['et_pb_text'=>$template]]);},'supported source mappings cannot be overwritten');
$reject(function()use($replacement,$template){$replacement->validate([wp_generate_uuid4()=>['dgbm_blog_module'=>$template]]);},'missing or foreign source rejected');
$page=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA not a replacement template']);
$reject(function()use($replacement,$source,$page){$replacement->validate([$source=>['dgbm_blog_module'=>$page]]);},'ordinary page cannot masquerade as saved template');
$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$serializer=new SuiteEdge\Migrator\Elementor\Classic\Serializer();
$data=$serializer->serialize($builder->build($raw,['module_replacements'=>['dgbm_blog_module'=>$template]]));
$check(count($builder->warnings)===1&&strpos($builder->warnings[0],'Explicit Elementor template replacement applied')!==false,'design replacement review is explicit');
$walk=function($nodes)use(&$walk){$all=[];foreach($nodes as$n){$all[]=$n;$all=array_merge($all,$walk($n['elements']));}return $all;};$nodes=$walk($data);
$check(!array_filter($nodes,function($n){return ($n['widgetType']??'')==='shortcode';}),'replacement leaves no unresolved Divi fallback');
$posts=array_values(array_filter($nodes,function($n){return ($n['widgetType']??'')==='hfe-basic-posts';}));
$check(count($posts)===1&&$posts[0]['settings']['posts_per_page']===3&&$posts[0]['settings']['columns_mobile']==='1','query and device settings copied intact');
$check($posts[0]['settings']['__globals__']['read_more_color']==='globals/colors?id=accent','native destination global style reference preserved');
$check($posts[0]['id']!==$stored_id&&strpos($posts[0]['settings']['_css_classes'],'elementor-element-'.$stored_id)!==false,'native IDs regenerated with stylesheet selector compatibility');
$check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data,true),'copied replacement validates actual Elementor controls');
$second=$serializer->serialize($builder->build($raw));$check((bool)array_filter($walk($second),function($n){return ($n['widgetType']??'')==='ace-post-grid';}),'without optional template mapping the standard grid converts automatically');
$builder->build('[dgbm_blog_module _module_preset="missing" test="@ET-DC@unknown@" /]',['module_replacements'=>['dgbm_blog_module'=>$template]]);$check(strpos($builder->warnings[0],'Explicit Elementor template replacement')!==false,'explicit whole-module replacement does not parse abandoned source bindings');
set_transient('suiteedge_migrator_pending_files_'.get_current_user_id(),[['source_id'=>$source,'layout_index'=>0,'name'=>'unsupported-blog.json']],HOUR_IN_SECONDS);
ob_start();(new SuiteEdge\Migrator\Admin\MappingPage())->render();$html=ob_get_clean();
$check(strpos($html,'name="module_replacements['.$source.'][dgbm_blog_module]"')!==false&&strpos($html,'QA Learning Center native blog replacement')!==false,'mapping UI lists source module and saved template');
// Real five exports, exact seven source-page URLs; no network needed for deterministic QA.
$repo=new SuiteEdge\Migrator\Jobs\JobRepository();$runner=new SuiteEdge\Migrator\Jobs\BatchRunner($repo);$backup=new SuiteEdge\Migrator\Import\BackupEngine();
$job=new SuiteEdge\Migrator\Jobs\Job();$links=json_decode(file_get_contents(getenv('SUITEEDGE_QA_EVIDENCE').'/report-384c-link-map.json'),true);
$job->settings=['output_engine'=>'classic','dry_run'=>false,'assets'=>['reuse_library','import_external'],'source_origin'=>'https://suiteedge.com','resolve_source_links'=>true,'link_map'=>$links,'reviewed_warnings'=>true];$before=[];
foreach(['Get Started','Careers','Why Suite Edge','Learning Center','Reviews']as$name){
    $json=file_get_contents('C:/Users/ay/Downloads/'.$name.'.json');$id=(new SuiteEdge\Migrator\Import\SourceRepository())->save($json,$name.'.json');
    $target=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA fixed batch '.$name,'post_content'=>'Protected '.$name]);$before[$target]=$backup->fingerprint($target);
    $job->items[]=['source_id'=>$id,'layout_index'=>0,'name'=>$name.'.json','target_page_id'=>$target,'status'=>'pending'];
    if($name==='Learning Center'){$job->settings['module_replacements'][$id]['dgbm_blog_module']=$template;}
}
$job->summary['total']=5;$repo->save($job);do{$r=$runner->process_batch($job->id,1);}while(in_array($r['status'],['pending','running'],true));$job=$repo->get($job->id);
$check($job->status==='completed'&&$job->summary['completed']===5&&$job->summary['failed']===0,'all five assigned pages apply with explicit grid replacement');
foreach($job->items as$item){$check($item['status']==='converted'&&get_post_meta($item['target_page_id'],'_elementor_edit_mode',true)==='builder','correct batch target written: '.$item['name']);}
$learning=$job->items[3];$learning_page=$learning['target_page_id'];
$created_posts=[];for($i=1;$i<=4;$i++){$created_posts[]=wp_insert_post(['post_type'=>'post','post_status'=>'publish','post_title'=>'QA native grid article '.$i,'post_date'=>gmdate('Y-m-d H:i:s',time()+$i),'post_content'=>'QA native post content']);}
$render=Elementor\Plugin::$instance->frontend->get_builder_content_for_display($learning_page,true);
$check(strpos($render,'hfe-posts-grid')!==false&&strpos($render,'QA native grid article 4')!==false,'copied UAE grid performs a real destination post query and renders');
$check(strpos($render,'QA native grid article 1')===false,'native posts-per-page limit honored');
$preview=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'QA replacement browser']);update_post_meta($preview,'_wp_page_template','elementor_canvas');update_post_meta($preview,'_elementor_edit_mode','builder');Elementor\Plugin::$instance->documents->get($preview)->save(['elements'=>$learning['elementor_data'],'settings'=>[]]);(new SuiteEdge\Migrator\Elementor\CssRegenerator())->regenerate($preview);
$report=(new SuiteEdge\Migrator\Reports\ReportBuilder())->build($job);$check($report['conversion_settings']['module_replacements']===$job->settings['module_replacements'],'report records precise source-scoped template selection');
// Saved Apply uses the copied tree even after a replacement template changes.
$analysis=new SuiteEdge\Migrator\Jobs\Job();$analysis->settings=$job->settings;$analysis->settings['dry_run']=true;$analysis->items=[$job->items[3]];$analysis->items[0]['target_page_id']=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA saved replacement']);$analysis->items[0]['status']='pending';unset($analysis->items[0]['error']);$analysis->summary['total']=1;$repo->save($analysis);$runner->process_batch($analysis->id);$analysis=$repo->get($analysis->id);
$grid[0]['elements'][0]['settings']['posts_per_page']=1;update_post_meta($template,'_elementor_data',wp_slash(wp_json_encode($grid)));(new SuiteEdge\Migrator\Jobs\SavedOutputApplier())->queue([$analysis->id=>[0]],true);$runner->process_batch($analysis->id);$applied=$repo->get($analysis->id);
$copied=array_values(array_filter($walk($applied->items[0]['elementor_data']),function($n){return ($n['widgetType']??'')==='hfe-basic-posts';}));$check($copied[0]['settings']['posts_per_page']===3,'saved Apply never reads changed replacement template');
$bad=$library->save_item(['title'=>'QA invalid replacement','type'=>'container','content'=>[['id'=>'ccccccc','elType'=>'widget','widgetType'=>'shortcode','settings'=>['shortcode'=>'[dgbm_blog_module]'],'elements'=>[]]],'page_settings'=>[]]);
$reject(function()use($replacement,$source,$bad){$replacement->validate([$source=>['dgbm_blog_module'=>$bad]]);},'shortcode fallback cannot bypass apply safety via replacement');
wp_update_post(['ID'=>$template,'post_status'=>'trash']);$builder->build($raw,['module_replacements'=>['dgbm_blog_module'=>$template]]);$check(strpos($builder->warnings[0],'Module replacement:')!==false,'deleted replacement yields actionable fallback');
$check($runner->rollback($job->id)['status']==='rolled_back','whole batch rollback succeeds');foreach($before as$id=>$hash){$check($backup->fingerprint($id)===$hash,'rollback restores target '.$id);}
file_put_contents(getenv('SUITEEDGE_QA_EVIDENCE').'/replacements-wordpress-'.(defined('DB_ENGINE')?'sqlite':'mariadb').'.json',wp_json_encode(['assertions'=>$checks,'plugin'=>SUITEEDGE_MIGRATOR_VERSION,'job_id'=>$job->id,'pages'=>array_map(function($i){return array_intersect_key($i,array_flip(['name','status','warnings','target_page_id']));},$job->items),'url'=>get_permalink($preview),'template_id'=>$template,'browser_post_ids'=>$created_posts],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo 'PASS: '.$checks.' native replacements and real five-page batch assertions'.PHP_EOL;
