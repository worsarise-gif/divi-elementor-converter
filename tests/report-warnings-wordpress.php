<?php
/** Disposable QA only: real attachment rendering and the supplied E-Commerce export. */
if (!defined('ABSPATH') || !get_user_by('login','qa_admin')) {throw new RuntimeException('Disposable QA site required.');}
wp_set_current_user(get_user_by('login','qa_admin')->ID);set_current_screen('suiteedge-migrator-mapping');
$checks=0;$check=function ($ok,$label) use (&$checks) {if (!$ok) {throw new RuntimeException('FAIL: '.$label);}$checks++;};
$upload=wp_upload_bits('qa-alt-'.wp_generate_uuid4().'.png',null,base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6pXkAAAAASUVORK5CYII='));
if ($upload['error']) {throw new RuntimeException($upload['error']);}
$attachment=wp_insert_attachment(['post_mime_type'=>'image/png','post_title'=>'Shared QA logo','post_status'=>'inherit'],$upload['file']);
update_post_meta($attachment,'_wp_attachment_image_alt','Shared media description');
$url=wp_get_attachment_url($attachment);$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$serializer=new SuiteEdge\Migrator\Elementor\Classic\Serializer();
$data=[];foreach (['Source desktop logo','Source mobile logo & café','',null,'Shared media description'] as $alt) {
    $node=['id'=>substr(md5(count($data).'alt'),0,7),'elType'=>'widget','widgetType'=>'image','settings'=>['image'=>['id'=>$attachment,'url'=>$url],'image_size'=>'full','caption_source'=>'none'],'elements'=>[]];
    if ($alt!==null) {$node['settings']['_suiteedge_alt']=$alt;}$data[]=$node;
}
$warnings=[];$information=[];$processor=new SuiteEdge\Migrator\Assets\AssetProcessor();$processor->process($data,0,['assets'=>['reuse_library']],$warnings,[],$information);
$check(!$warnings,'differing alt preserved without unresolved asset warnings');
$check((bool)$information,'native URL rendering tradeoff reported as information');
$check(get_post_meta($attachment,'_wp_attachment_image_alt',true)==='Shared media description','shared Media Library alt unchanged');
foreach (['Source desktop logo','Source mobile logo & café',''] as $i=>$alt) {
    $s=$data[$i]['settings'];$check(empty($s['image']['id'])&&$s['image']['url']===$url&&$s['_suiteedge_attachment_id']===$attachment,'conflicting alt uses existing local URL '.$i);
    $html=Elementor\Group_Control_Image_Size::get_attachment_image_html($s);$tags=new WP_HTML_Tag_Processor($html);$tags->next_tag('IMG');
    $check($tags->get_attribute('alt')===$alt,'actual server-rendered per-image alt '.$i);
}
$check($data[3]['settings']['image']['id']===$attachment,'missing source alt preserves attachment rendering');
$check($data[4]['settings']['image']['id']===$attachment,'matching alt preserves attachment rendering/srcset');
$processor->process($data,0,['assets'=>['reuse_library']],$warnings,[],$information);
$check(empty($data[0]['settings']['image']['id'])&&get_post_meta($attachment,'_wp_attachment_image_alt',true)==='Shared media description','reprocessing reused URL is idempotent');
$check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data,true),'native image settings validate');
$local=Elementor\Plugin::$instance->templates_manager->get_source('local');
$imageTemplate=$local->save_item(['type'=>'page','title'=>'QA distinct alt template','content'=>[['id'=>'a11cafe','elType'=>'container','settings'=>[],'elements'=>$data]],'page_settings'=>[]]);
if (is_wp_error($imageTemplate)) {throw new RuntimeException($imageTemplate->get_error_message());}
$inserted=$local->get_data(['template_id'=>$imageTemplate]);
$check($inserted['content'][0]['elements'][0]['settings']['image']['alt']==='Source desktop logo','per-instance alt survives Elementor library insertion');
$page=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'QA native image alt portability']);update_post_meta($page,'_wp_page_template','elementor_canvas');update_post_meta($page,'_elementor_edit_mode','builder');
Elementor\Plugin::$instance->documents->get($page)->save(['elements'=>$inserted['content'],'settings'=>[]]);
$html=Elementor\Plugin::$instance->frontend->get_builder_content($page);$tags=new WP_HTML_Tag_Processor($html);$alts=[];while ($tags->next_tag('IMG')) {$alts[]=$tags->get_attribute('alt');}
$check($alts===['Source desktop logo','Source mobile logo & café','','Shared media description','Shared media description'],'all actual native widgets render correct distinct alt');
$interactive=$serializer->serialize($builder->build('[et_pb_text link_text_color="#111111" link_text_color_tablet="#222222" link_text_color_phone="#333333" link_text_color__hover="#444444" link_text_color_tablet__hover="#555555" link_text_color_phone__hover="#666666"]<p><a id="qa-migrated-link" href="/">Converted link</a></p>[/et_pb_text][et_pb_text]<p><a id="qa-ordinary-link" href="/">Ordinary link</a></p>[/et_pb_text]'));
$combined=$inserted['content'];foreach ($interactive as $node) {$combined[0]['elements'][]=$node;}
Elementor\Plugin::$instance->documents->get($page)->save(['elements'=>$combined,'settings'=>[]]);(new SuiteEdge\Migrator\Elementor\CssRegenerator())->regenerate($page);
// Real export reproduces the exact report warning and exercises saved-result Apply.
$json=file_get_contents(getenv('SUITEEDGE_ECOMMERCE_FIXTURE'));$export=(new SuiteEdge\Migrator\Import\DiviExportReader())->read_json($json);$content=$export['layouts'][0]['post_content'];
$links=[];preg_match_all('/@ET-DC@([^@]+)@/',$content,$bindings);foreach ($bindings[0] as $binding) {$value=(new SuiteEdge\Migrator\Divi\DynamicContentParser())->parse($binding);if (($value['content']??'')==='post_link_url_page') {$links[(string)$value['settings']['post_id']]=home_url('/');}}
$options=['presets'=>$export['presets'],'source_origin'=>'https://suiteedge.com','link_map'=>$links];
$ecommerce=$serializer->serialize($builder->build($content,$options));
$check(!$builder->warnings,'real E-Commerce has no conversion warnings: '.implode('; ',$builder->warnings));
$check((new SuiteEdge\Migrator\Elementor\Validator())->validate($ecommerce,true),'real E-Commerce native controls validate');
$styles=wp_json_encode($ecommerce);$check(strpos($styles,'a:hover')!==false&&strpos($styles,'#FF9B40')!==false,'real exported orange hover preserved');
$source=(new SuiteEdge\Migrator\Import\SourceRepository())->save($json,'E-Commerce.json');$pending='suiteedge_migrator_pending_files_'.get_current_user_id();set_transient($pending,[['source_id'=>$source,'layout_index'=>0,'name'=>'E-Commerce.json / Divi layout']],HOUR_IN_SECONDS);
$target=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA E-Commerce applied target','post_content'=>'Original QA commerce']);$backup=new SuiteEdge\Migrator\Import\BackupEngine();$before=$backup->fingerprint($target);
// Do not rewrite unrelated source paths; this regression isolates the two reported warnings.
$settings=['output_engine'=>'classic','dry_run'=>true,'assets'=>['reuse_library','import_external'],'source_theme'=>(new SuiteEdge\Migrator\Style\SourceThemeProfile())->recommended('https://suiteedge.com'),'link_map'=>$links];
$repo=new SuiteEdge\Migrator\Jobs\JobRepository();$runner=new SuiteEdge\Migrator\Jobs\BatchRunner($repo);$job=(new SuiteEdge\Migrator\Import\BatchImport())->create([$target],$settings);$runner->process_batch($job->id);$job=$repo->get($job->id);
$check($job->status==='completed'&&$job->items[0]['status']==='analyzed','real export analysis completes');
$check(!$job->items[0]['warnings'],'real export analysis no hover warning');
$check($before===$backup->fingerprint($target),'analysis leaves target unchanged');
(new SuiteEdge\Migrator\Jobs\SavedOutputApplier())->queue([$job->id=>[0]],true);$runner->process_batch($job->id);$job=$repo->get($job->id);
$check($job->items[0]['status']==='converted','same analyzed job applies to correct E-Commerce target');
$check(!$job->items[0]['warnings'],'real export saved Apply no unresolved warnings: '.implode('; ',$job->items[0]['warnings']));
$check($job->items[0]['target_page_id']===$target,'assigned target preserved');
$applied=Elementor\Plugin::$instance->frontend->get_builder_content($target);$check(strpos($applied,'#FF9B40')!==false&&strpos($applied,'a:hover')!==false,'applied page renders source hover CSS');
$runner->rollback($job->id);$check($before===$backup->fingerprint($target),'rollback restores original E-Commerce target');
file_put_contents(ABSPATH.'../report-warnings-results'.(defined('DB_ENGINE')?'-sqlite':'-mariadb').'.json',wp_json_encode(['assertions'=>$checks,'plugin'=>SUITEEDGE_MIGRATOR_VERSION,'database'=>defined('DB_ENGINE')?'sqlite':$GLOBALS['wpdb']->db_version(),'image_url'=>get_permalink($page),'source_sha256'=>hash('sha256',$json),'warnings'=>$job->items[0]['warnings']],JSON_PRETTY_PRINT));
echo 'PASS: '.$checks.' native alt/real E-Commerce assertions'.PHP_EOL;
