<?php
/** Disposable-site only: native schema, render, saved Apply, blockers and exact rollback. */
if (!defined('ABSPATH') || !getenv('ACE_P01_FIXTURE_DIR')) { exit(1); }
wp_set_current_user(get_user_by('login','qa_admin')->ID); set_current_screen('suiteedge-migrator-mapping');
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$label);}$checks++;};
$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder(); $serializer=new SuiteEdge\Migrator\Elementor\Classic\Serializer(); $validator=new SuiteEdge\Migrator\Elementor\Validator();
$source='[et_pb_section][et_pb_row][et_pb_column type="4_4"][et_pb_cta title="Join our team" button_text="Apply" button_url="/careers" custom_button="on" button_use_icon="off" header_font="Arial|700|||||||" header_font_size_phone="22px" body_font="Arial|400|||||||" custom_padding="20px|30px|40px|50px"]<p>Editable CTA body</p>[/et_pb_cta][et_pb_team_member name="QA Person" position="Developer" facebook_url="https://example.com/profile" body_font="Arial|400|||||||" header_font="Arial|700|||||||" position_font="Arial|400|||||||"]<p>Biography retained</p>[/et_pb_team_member][/et_pb_column][/et_pb_row][/et_pb_section]';
$source=str_replace('title="Join our team"','title="Join our team" background_color="#123456"',$source);
$portrait=includes_url('images/w-logo-blue-white-bg.png');
$source.='[et_pb_section][et_pb_row][et_pb_column type="4_4"][et_pb_team_member name="Wide Portrait" position="Wide role" image_url="'.$portrait.'"]<p>Wide biography</p>[/et_pb_team_member][/et_pb_column][/et_pb_row][et_pb_row][et_pb_column type="1_4"][et_pb_team_member name="Quarter Portrait" image_url="'.$portrait.'"]<p>Quarter biography</p>[/et_pb_team_member][/et_pb_column][et_pb_column type="1_4"][/et_pb_column][et_pb_column type="1_4"][/et_pb_column][et_pb_column type="1_4"][/et_pb_column][/et_pb_row][/et_pb_section]';
$data=$serializer->serialize($builder->build($source));
$check($validator->validate($data,true),'all composition controls validate installed Elementor schema');
// Deterministic regression for Elementor's variable-length native IDs, including repeaters.
$short=$data;$rewrite=function(&$nodes)use(&$rewrite){static $index=0;foreach($nodes as &$node){$node['id']=dechex(++$index);$rewrite($node['elements']);}unset($node);};$rewrite($short);$check($validator->validate($short,true),'short native element IDs validate deterministically');
foreach (['cta','person','summit'] as $name) {
    $nodes=json_decode(file_get_contents(getenv('ACE_P01_FIXTURE_DIR').'/p01-'.$name.'-nodes.json'),true);
    foreach ($nodes as $node) { $converted=$serializer->serialize($builder->build($node['original_shortcode'])); $check($validator->validate($converted,true),'official exported '.$node['type'].' native controls validate'); }
}
$controls=[];
foreach (['heading','text-editor','image','button','social-icons'] as $name) {
    $widget=Elementor\Plugin::$instance->widgets_manager->get_widget_types($name);$stack=$widget->get_stack();
    $controls[$name]=array_keys(array_merge($stack['controls'],$stack['style_controls'] ?? []));
    $check((bool)$widget,'required Free widget exists: '.$name);
}
$repo=new SuiteEdge\Migrator\Jobs\JobRepository();$runner=new SuiteEdge\Migrator\Jobs\BatchRunner($repo);$backup=new SuiteEdge\Migrator\Import\BackupEngine();
$source_id=(new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['data'=>$source]),'business-compositions.json');
$targets=[];$before=[];
for($i=0;$i<2;$i++){$target=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA Business Composition '.$i,'post_content'=>'Protected '.$i]);$targets[]=$target;$before[$target]=$backup->fingerprint($target);}
$job=new SuiteEdge\Migrator\Jobs\Job();$job->settings=['output_engine'=>'classic','dry_run'=>true,'assets'=>[]];
$job->items=[];foreach($targets as $target){$job->items[]=['source_id'=>$source_id,'layout_index'=>0,'name'=>'business-compositions.json','target_page_id'=>$target,'status'=>'pending'];}$job->summary['total']=2;$repo->save($job);
for($step=0;$step<2;$step++){$runner->process_batch($job->id);}$saved=$repo->get($job->id);$check($saved->status==='completed','multi-page composition analysis completes');
$hashes=array_column($saved->items,'output_hash');foreach($targets as $target){$check($backup->fingerprint($target)===$before[$target],'analysis leaves original target intact');}
(new SuiteEdge\Migrator\Jobs\SavedOutputApplier())->queue([$job->id=>[0,1]],true);for($step=0;$step<2;$step++){$runner->process_batch($job->id);}$applied=$repo->get($job->id);
foreach($targets as $index=>$target){$check($applied->items[$index]['status']==='converted'&&$applied->items[$index]['output_hash']===$hashes[$index],'bulk Apply retains corresponding output without reconverting');$check(get_post_meta($target,'_elementor_edit_mode',true)==='builder','target is editable Elementor');}
$check($runner->rollback($job->id)['status']==='rolled_back','composition bulk rollback completes');
foreach($targets as $target){$check($backup->fingerprint($target)===$before[$target],'rollback exactly restores target');}
$blocked=$serializer->serialize($builder->build('[et_pb_cta title="Protected" link_option_url="/module" button_text="Go" button_url="/button" /]'));
try{(new SuiteEdge\Migrator\Jobs\SavedOutputApplier())->validate_output($blocked);$check(false,'whole-module link blocker');}catch(UnexpectedValueException $e){$check(true,'whole-module link cannot bypass saved Apply');}
$library=Elementor\Plugin::$instance->templates_manager->get_source('local');$template=$library->save_item(['title'=>'QA Business Composition','type'=>'page','content'=>$data,'page_settings'=>[]]);
if(is_wp_error($template)){throw new RuntimeException($template->get_error_message());}
$inserted=$library->get_data(['template_id'=>$template]);$check($inserted['content'][0]['id']!==$data[0]['id'],'template insertion regenerates IDs');$check($validator->validate($inserted['content'],true),'native inserted composition remains valid');
$preview=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'QA P01 composition browser']);update_post_meta($preview,'_wp_page_template','elementor_canvas');update_post_meta($preview,'_elementor_edit_mode','builder');
Elementor\Plugin::$instance->documents->get($preview)->save(['elements'=>$data,'settings'=>[]]);(new SuiteEdge\Migrator\Elementor\CssRegenerator())->regenerate($preview);
$html=Elementor\Plugin::$instance->frontend->get_builder_content_for_display($preview);
foreach(['Join our team','Editable CTA body','QA Person','Developer','Biography retained','https://example.com/profile'] as $text){$check(strpos($html,$text)!==false,'native frontend retains '.$text);}
file_put_contents(getenv('ACE_P01_FIXTURE_DIR').'/p01-wordpress-'.(defined('DB_ENGINE')?'sqlite':'mariadb').'.json',wp_json_encode(['assertions'=>$checks,'wordpress'=>get_bloginfo('version'),'elementor'=>ELEMENTOR_VERSION,'url'=>get_permalink($preview),'page_id'=>$preview,'job_id'=>$job->id,'controls'=>$controls,'warnings'=>$builder->warnings],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo 'PASS: '.$checks.' business composition WordPress assertions'.PHP_EOL;
