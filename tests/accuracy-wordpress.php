<?php
/** Run only on a disposable site with the explicitly provided private source exports. */
if (!defined('ABSPATH') || !getenv('SUITEEDGE_ACCURACY_DIR') || !getenv('SUITEEDGE_QA_EVIDENCE')) { throw new RuntimeException('Explicit private accuracy fixture directory required.'); }
wp_set_current_user(get_user_by('login','qa_admin')->ID);set_current_screen('suiteedge-migrator-mapping');
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$label);}$checks++;};
$directory=getenv('SUITEEDGE_ACCURACY_DIR');$evidence=getenv('SUITEEDGE_QA_EVIDENCE');$links=[];
$kit=Elementor\Plugin::$instance->kits_manager->get_active_kit();$kit_before=serialize($kit->get_settings());
$probe=(new SuiteEdge\Migrator\Elementor\Classic\Serializer())->serialize((new SuiteEdge\Migrator\Elementor\DocumentBuilder())->build('[et_pb_text text_font_size="42px" text_font_size_tablet="24px" text_font_size_phone="16px" text_font_size_last_edited="on|phone" custom_margin="0px||0px||false|false" custom_margin_tablet="50px||0px||false|false" custom_margin_last_edited="on|tablet"]Responsive probe[/et_pb_text]'));
$probe_css='';$collect=function($nodes)use(&$collect,&$probe_css){foreach($nodes as$n){if(($n['widgetType']??'')==='html'){$probe_css.=$n['settings']['html'];}$collect($n['elements']);}};$collect($probe);
$check(strpos($probe_css,'@media(min-width:981px)')!==false&&strpos($probe_css,'font-size:42px;')!==false,'native desktop controls compiled at the source breakpoint');
$check(strpos($probe_css,'@media(min-width:768px) and (max-width:980px)')!==false&&strpos($probe_css,'font-size:24px;')!==false&&strpos($probe_css,'font-size:16px;')!==false,'native tablet/mobile values retain the source breakpoints');
$check(serialize($kit->get_settings())===$kit_before&&!get_post_meta(0,'_elementor_css',true),'responsive compilation does not modify destination kit or post CSS metadata');
foreach(['accuracy','home','footer','header']as$scope){$file=$evidence.'/'.$scope.'-source-links.json';if(is_file($file)){$links+=json_decode(file_get_contents($file),true)['links'];}}
$repo=new SuiteEdge\Migrator\Jobs\JobRepository();$runner=new SuiteEdge\Migrator\Jobs\BatchRunner($repo);$backup=new SuiteEdge\Migrator\Import\BackupEngine();
$job=new SuiteEdge\Migrator\Jobs\Job();$job->settings=['output_engine'=>'classic','dry_run'=>false,'assets'=>['reuse_library','import_external'],'link_map'=>$links,'source_origin'=>'https://suiteedge.com'];
$before=[];$raw=[];$native=[];$sources=[];$results=[];
foreach(['Get Started','Careers','Why Suite Edge','Learning Center','Reviews']as$name){
    $json=file_get_contents($directory.'/'.$name.'.json');$export=(new SuiteEdge\Migrator\Import\DiviExportReader())->read_json($json);$raw[$name]=$export;
    $builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$data=(new SuiteEdge\Migrator\Elementor\Classic\Serializer())->serialize($builder->build($export['post_content'],['presets'=>$export['presets'],'source_origin'=>'https://suiteedge.com','link_map'=>$links]));
    if ($name === 'Learning Center') { $check(count($builder->warnings)===1 && strpos($builder->warnings[0],'Ace Post Grid requires Ace Migrator')!==false,'standard blog converts with disclosed runtime dependency'); }
    else { $check(!$builder->warnings,$name.' converts without lost-style mappings: '.implode(' / ',$builder->warnings)); }
    $check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data,true),$name.' actual Elementor controls validate');
    $native[$name]=$data;
    $source=(new SuiteEdge\Migrator\Import\SourceRepository())->save($json,$name.'.json');$sources[]=['source_id'=>$source];
    $page=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA accuracy '.$name,'post_content'=>'Protected original '.$name]);$before[$page]=$backup->fingerprint($page);
    $job->items[]=['source_id'=>$source,'layout_index'=>0,'name'=>$name.'.json','target_page_id'=>$page,'status'=>'pending'];
}
$defaults=(new SuiteEdge\Migrator\Import\RecommendedSettings())->for_pending($sources);$check($defaults['source_theme']['row_max_width']===1280,'mixed source batch retains measured theme defaults even for the image-free layout');
$check($defaults['source_origin']==='','unproven link provenance still requires explicit source origin');
$job->summary['total']=5;$repo->save($job);
do{$status=$runner->process_batch($job->id);}while(!in_array($status['status'],['completed','completed_with_errors'],true));
$saved=$repo->get($job->id);$check($saved->status==='completed' && $saved->summary['completed']===5 && $saved->summary['failed']===0,'all five original exports apply with automatic standard grid conversion');
foreach($saved->items as$item){$id=$item['target_page_id'];$check($item['status']==='converted'&&get_post_meta($id,'_elementor_edit_mode',true)==='builder',$item['name'].' exact assigned target written');$check(get_post_status($id)==='draft'&&strpos(get_post($id)->post_content,'Protected original')===0,'publication and original content retained');$results[]=['name'=>$item['name'],'status'=>$item['status'],'warnings'=>$item['warnings'],'page_id'=>$id];}
$diagnostics=(new SuiteEdge\Migrator\Reports\WarningReport())->diagnostics($saved);
foreach($diagnostics['entries']as$d){if(strpos($d['cause'],'Internal link kept')===0){$check($d['code']==='internal_link_unresolved','source link with media in slug is correctly classified');}}
$report=(new SuiteEdge\Migrator\Reports\ReportBuilder())->build($saved);$check($report['conversion_settings']['source_origin']==='https://suiteedge.com','reports record effective source provenance');
$previewdata=$saved->items[2]['elementor_data'];
$library=Elementor\Plugin::$instance->templates_manager->get_source('local');$template=$library->save_item(['title'=>'QA accurate team portable','type'=>'page','content'=>$previewdata,'page_settings'=>[]]);if(is_wp_error($template)){throw new RuntimeException($template->get_error_message());}
$inserted=$library->get_data(['template_id'=>$template]);$check($inserted['content'][0]['id']!==$previewdata[0]['id'],'portable template insertion regenerates IDs');
$preview=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'QA accuracy team browser']);update_post_meta($preview,'_wp_page_template','elementor_canvas');update_post_meta($preview,'_elementor_edit_mode','builder');Elementor\Plugin::$instance->documents->get($preview)->save(['elements'=>$inserted['content'],'settings'=>[]]);(new SuiteEdge\Migrator\Elementor\CssRegenerator())->regenerate($preview);
$html=Elementor\Plugin::$instance->frontend->get_builder_content_for_display($preview,true);$dom=new DOMDocument();libxml_use_internal_errors(true);$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);libxml_clear_errors();$xpath=new DOMXPath($dom);
$check($xpath->query('//*[contains(concat(" ", normalize-space(@class), " ")," elementor-image-box-description ")]')->length===15,'all fifteen member roles remain inside native description elements');
$check(strpos($html,'Stephen O.')!==false&&strpos($html,'Web Developer')!==false,'Stephen name and role retained');
$check(strpos($html,'[et_pb_')===false,'no unresolved source shortcodes in team output');
$rolled=$runner->rollback($job->id);$check($rolled['status']==='rolled_back','five-page rollback succeeds');foreach($before as$id=>$hash){$check($backup->fingerprint($id)===$hash,'rollback restores assigned target '.$id);}
$output=['assertions'=>$checks,'plugin'=>SUITEEDGE_MIGRATOR_VERSION,'backend'=>defined('DB_ENGINE')?'sqlite':'mariadb','url'=>get_permalink($preview),'job_id'=>$job->id,'pages'=>$results,'template_id'=>$template,'conversion_settings'=>$report['conversion_settings']];
file_put_contents($evidence.'/accuracy-wordpress-'.(defined('DB_ENGINE')?'sqlite':'mariadb').'.json',wp_json_encode($output,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));echo 'PASS: '.$checks.' real-export accuracy and batch/rollback assertions'.PHP_EOL;
