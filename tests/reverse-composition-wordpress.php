<?php
/** Full private reports plus real Elementor/Formidable and installed Divi when available. */
if(!defined('ABSPATH')||home_url()!=='http://127.0.0.1:8097'||!getenv('SUITEEDGE_QA_EVIDENCE')){exit(1);}
wp_set_current_user(get_user_by('login','qa_admin')->ID);set_current_screen('ace-reverse-composition');
$dir=getenv('SUITEEDGE_QA_EVIDENCE');$checks=0;$check=function($ok,$name)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$name);}$checks++;};$reject=function($fn,$name)use($check){try{$fn();}catch(Throwable $error){$check(true,$name);return;}$check(false,$name);};
$outputs=[];$sources=[];$provider_calls=0;$http_calls=0;$mail_calls=0;
$observe=function($result,$tag)use(&$provider_calls){if($tag==='formidable'&&$result===false){$provider_calls++;}return $result;};add_filter('pre_do_shortcode_tag',$observe,10,2);
$http=function($pre)use(&$http_calls){if($pre===false){$http_calls++;}return new WP_Error('qa_no_network','Analysis must not fetch fonts/providers');};add_filter('pre_http_request',$http);
$mail=function($result)use(&$mail_calls){$mail_calls++;return true;};add_filter('pre_wp_mail',$mail);
$kit=Elementor\Plugin::$instance->kits_manager->get_active_kit();$kit_before=get_post_meta($kit->get_id());$forms_before=(new SuiteEdge\Migrator\Reverse\Providers())->form('[formidable id=1 title=false]');
foreach(['d820'=>['private-reverse-d820-source.json',53],'0d9c'=>['private-reverse-0d9c-source-0.json',130]] as $name=>$fixture){
    $snapshot=json_decode(file_get_contents($dir.'/'.$fixture[0]),true,100,JSON_THROW_ON_ERROR);$sources[$name]=$snapshot;
    $before=hash('sha256',wp_json_encode($snapshot));$out=(new SuiteEdge\Migrator\Reverse\Document())->convert($snapshot);$outputs[$name]=$out;
    $check(!$out['blockers'],$name.' full report has accepted output: '.implode(' | ',$out['blockers']));
    $check($out['source_element_count']===$fixture[1]&&$out['covered_element_count']===$fixture[1],$name.' every node accounted for');
    $check($before===hash('sha256',wp_json_encode($snapshot)),$name.' source immutable');
    $check(!empty($out['composition_manifest']['css_assets'])&&strpos($out['content'],'data-ace-reverse-composition="1"')!==false,$name.' explicit portable composition');
    $check(strpos($out['content'],'data-ace-source-font="Sora"')!==false&&strpos($out['content'],'fonts.googleapis.com/css?family=Sora')!==false,$name.' saved font resources independent of request cache');
    $check(strpos($out['content'],'--e-global-color-primary:#2DCEF9')!==false,$name.' captured kit values retained');
    $check(strpos($out['content'],'data-ace-reverse-hide-title="1"')!==false,$name.' title presentation separate from SEO');
    $check(strpos($out['content'],'[elementor-element')===false,$name.' no runtime cache/provider shortcode left');
    foreach($out['coverage'] as $record){$check(!$record['unmapped_settings'],$name.' active control coverage '.$record['path']);}
    (new SuiteEdge\Migrator\Reverse\Shortcodes())->validate($out);
}
$check($provider_calls===0&&$http_calls===0&&$mail_calls===0,'analysis executes no provider, network fetch or delivery');
$check($kit_before===get_post_meta($kit->get_id()),'source rendering never changes kit metadata');
$check($forms_before===(new SuiteEdge\Migrator\Reverse\Providers())->form('[formidable id=1 title=false]'),'analysis preserves real form, fields and actions');
$c=$outputs['0d9c']['content'];
$check(substr_count($c,'<details')===5,'all five indexed FAQ answers render');
$check(substr_count($c,'class="elementor-icon-list-item"')===10,'all ten icon list items render');
$check(strpos($c,'[formidable id=1 title=false]')!==false,'real form shortcode retained without execution');
$check(strpos($c,'data-widget_type="shortcode.default"')!==false,'provider retains original Shortcode widget identity and layout');
$check(strpos($c,'--width:725px')!==false&&strpos($c,'--width:480px')!==false&&strpos($c,'--width:400px')!==false,'authored pixel columns preserved');
$check(strpos($c,'--e-con-grid-template-columns:')!==false||strpos($c,'grid-template-columns:')!==false,'grid tracks preserved');
$check(strpos($c,'@media(max-width:767px)')!==false&&strpos($c,'@media(max-width:1024px)')!==false,'source responsive thresholds compiled');
$check(substr_count($c,'class="welded-card"')===6&&strpos($c,'31px!important')!==false,'ordered shared HTML card cascade preserved');
$check(strpos($c,'http://app.jobtread.com/sign-in')!==false,'scheme-less source buttons normalized');
$inactive=false;foreach($outputs['0d9c']['coverage'] as $record){if(in_array('inactive source condition',$record['setting_dispositions']??[],true)){$inactive=true;}}
$check($inactive,'inactive width controls are distinguished from compiled controls');
$widget=function($id,$type,$settings,$children=[]){return ['id'=>$id,'elType'=>'widget','widgetType'=>$type,'settings'=>$settings,'elements'=>$children];};
$container=function($id,$settings,$children){return ['id'=>$id,'elType'=>'container','settings'=>$settings,'elements'=>$children];};
$base=['data'=>[$container('root1',['content_width'=>'full','boxed_width'=>['size'=>1100,'unit'=>'px']],[$widget('head1','heading',['title'=>'Probe','header_size'=>'span','typography_typography'=>'custom','typography_font_family'=>'Sora'])])],'kit_settings'=>$sources['0d9c']['kit_settings']];
$auto=$base;$auto['data'][0]['settings']['margin']=['unit'=>'px','top'=>'0','right'=>'auto','bottom'=>'0','left'=>'auto'];$out=(new SuiteEdge\Migrator\Reverse\Document())->convert($auto);$check(!$out['blockers']&&strpos($out['content'],'auto')!==false,'valid auto spacing reaches the source composition when native dimensions cannot represent it');
// A captured local image keeps the attachment renderer's alt/srcset behavior.
$images=get_posts(['post_type'=>'attachment','post_mime_type'=>'image','numberposts'=>1]);$check((bool)$images,'real local media fixture exists');$image=$images[0];
$alt_exists=metadata_exists('post',$image->ID,'_wp_attachment_image_alt');$alt=get_post_meta($image->ID,'_wp_attachment_image_alt',true);$media_source=0;
try{
 update_post_meta($image->ID,'_wp_attachment_image_alt','Verified local image alternative');
 $media_tree=[$container('mediaroot',['content_width'=>'full'],[$widget('mediaimage','image',['image'=>['id'=>$image->ID,'url'=>wp_get_attachment_url($image->ID)],'image_size'=>'full'])])];
 $media_source=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA captured local media']);update_post_meta($media_source,'_elementor_edit_mode','builder');update_post_meta($media_source,'_elementor_data',wp_slash(wp_json_encode($media_tree)));
 $reader=new SuiteEdge\Migrator\Reverse\Source();$captured=$reader->capture($media_source);$stable_source=$captured;$reader->assert_current($captured);$check(($captured['media'][$image->ID]['alt']??'')==='Verified local image alternative','local attachment metadata is captured with the source');
 $out=(new SuiteEdge\Migrator\Reverse\Document())->convert($captured);$check(!$out['blockers']&&strpos($out['content'],'alt="Verified local image alternative"')!==false,'native image adapter retains local alt text');
 $captured['data'][0]['settings']['margin']=['unit'=>'px','top'=>'0','right'=>'auto','bottom'=>'0','left'=>'auto'];$out=(new SuiteEdge\Migrator\Reverse\Document())->convert($captured);$check(!$out['blockers']&&strpos($out['content'],'wp-image-'.$image->ID)!==false&&strpos($out['content'],'alt="Verified local image alternative"')!==false,'retained composition uses the verified local attachment renderer');
 $reader->assert_current($stable_source);$check(true,'source kit capture stays stable after responsive renderer initialization');
 update_post_meta($image->ID,'_wp_attachment_image_alt','Changed local image alternative');$original=$reader->capture($media_source);update_post_meta($image->ID,'_wp_attachment_image_alt','Another changed alternative');$reject(function()use($reader,$original){$reader->assert_current($original);},'actual attachment metadata changes invalidate saved Apply');
}finally{if($alt_exists){update_post_meta($image->ID,'_wp_attachment_image_alt',$alt);}else{delete_post_meta($image->ID,'_wp_attachment_image_alt');}if($media_source){wp_delete_post($media_source,true);}}
foreach(['custom_css'=>'selector{color:red}','_animation'=>'fadeIn','unknown_active_control'=>'yes','__dynamic__'=>['title'=>'binding']] as $key=>$value){$bad=$base;$bad['data'][0]['elements'][0]['settings'][$key]=$value;$out=(new SuiteEdge\Migrator\Reverse\Document())->convert($bad);$check((bool)$out['blockers'],'unsupported active behavior remains blocked '.$key);}
$bad=$base;$bad['data'][0]['elements'][]=$widget('html1','html',['html'=>'<style>.elementor-element-outside{color:red}</style>']);$check((bool)(new SuiteEdge\Migrator\Reverse\Document())->convert($bad)['blockers'],'CSS cannot bind outside captured nodes');
$bad=$base;$bad['data'][0]['elements'][]=$widget('provider','shortcode',['shortcode'=>'[formidable id=999999 title=false]']);$check((bool)(new SuiteEdge\Migrator\Reverse\Document())->convert($bad)['blockers'],'missing real form blocks analysis');
global $shortcode_tags;$old_shortcode=$shortcode_tags['formidable'];remove_shortcode('formidable');$check((bool)(new SuiteEdge\Migrator\Reverse\Document())->convert($sources['0d9c'])['blockers'],'inactive form provider blocks analysis');add_shortcode('formidable',$old_shortcode);
$before_option=get_option('elementor_local_google_fonts','0');update_option('elementor_local_google_fonts','1');$check((bool)(new SuiteEdge\Migrator\Reverse\Document())->convert($base)['blockers'],'local-font mode cannot silently fetch or switch to remote');update_option('elementor_local_google_fonts',$before_option);
$native=defined('ET_BUILDER_VERSION')&&preg_match('/^4\./',ET_BUILDER_VERSION)&&shortcode_exists('et_pb_code');if(!$native){define('ET_BUILDER_VERSION','4.27.4');foreach(['et_pb_section','et_pb_row','et_pb_column','et_pb_code','et_pb_text'] as $tag){add_shortcode($tag,function($attrs,$content){return do_shortcode($content);});}}
$writer=new SuiteEdge\Migrator\Reverse\Writer();$writer->requirements($outputs['0d9c']);$check(true,'actual providers/assets and destination modules validate');
$bad=$outputs['0d9c'];$first=array_key_first($bad['composition_manifest']['css_assets']);$bad['composition_manifest']['css_assets'][$first]=str_repeat('0',64);$reject(function()use($writer,$bad){$writer->requirements($bad);},'changed source asset blocks before writing');
$bad=$outputs['0d9c'];$bad['dependencies'][0]['fingerprint']=str_repeat('0',64);$reject(function()use($writer,$bad){$writer->requirements($bad);},'changed form contract blocks before writing');
$field=FrmField::get_all_for_form(1)[0];$original_name=$field->name;FrmField::update($field->id,['name'=>'Changed QA field']);
try{$reject(function()use($writer,$outputs){$writer->requirements($outputs['0d9c']);},'actual form field edit invalidates saved output');}finally{FrmField::update($field->id,['name'=>$original_name]);}
$env=(new SuiteEdge\Migrator\Reverse\Providers())->environment();update_option('elementor_google_font','0');$reject(function()use($env){(new SuiteEdge\Migrator\Reverse\Providers())->validate([$env]);},'changed font-loading state is stale');update_option('elementor_google_font',$env['google_fonts']);
$uploads=wp_upload_dir(null,false);$font_file=$uploads['basedir'].'/ace-reverse-local-font-qa.woff2';$font_css=$uploads['basedir'].'/ace-reverse-local-font-qa.css';
$check(strpos(wp_normalize_path($font_file),wp_normalize_path(ABSPATH))===0,'font fixture stays inside disposable WordPress');
$font_manifest=json_decode(file_get_contents($dir.'/reverse-qa-fonts/manifest.json'),true);$binary=reset($font_manifest['files']);copy($dir.'/reverse-qa-fonts/'.$binary,$font_file);file_put_contents($font_css,'@font-face{font-family:"QA local font";src:url("ace-reverse-local-font-qa.woff2")}');
try{$provider=new SuiteEdge\Migrator\Reverse\Providers();$dependency=$provider->font($uploads['baseurl'].'/ace-reverse-local-font-qa.css');$provider->validate([$dependency]);$check(count($dependency['files'])===2,'local font stylesheet and binary fingerprinted');file_put_contents($font_file,'Changed QA binary');$reject(function()use($provider,$dependency){$provider->validate([$dependency]);},'actual local font binary change blocks Apply');unlink($font_file);$reject(function()use($provider,$dependency){$provider->validate([$dependency]);},'missing local font binary blocks Apply');}finally{if(is_file($font_file)){unlink($font_file);}if(is_file($font_css)){unlink($font_css);}}
remove_filter('pre_do_shortcode_tag',$observe,10);$form=do_shortcode('[formidable id=1 title=false]');$check(strpos($form,'<form')!==false&&strpos($form,'type="email"')!==false,'retained shortcode renders the actual installed Formidable form');
$check($mail_calls===0,'form rendering sends no email');
$email_fields=array_values(array_filter(FrmField::get_all_for_form(1),function($field){return $field->type==='email';}));$field=$email_fields[0];$errors=[];FrmEntryValidate::validate_field_types($errors,$field,'invalid-email',['id'=>$field->id]);$check((bool)$errors,'actual provider email validator rejects invalid input without submitting');
// Full retained composition goes through the same saved/bulk Apply and rollback lifecycle.
$repo=new SuiteEdge\Migrator\Jobs\JobRepository();$runner=new SuiteEdge\Migrator\Jobs\BatchRunner($repo);$apply=new SuiteEdge\Migrator\Jobs\SavedOutputApplier();$service=new SuiteEdge\Migrator\Reverse\Service();$backup=new SuiteEdge\Migrator\Import\BackupEngine();
$jobs=[];$targets=[];$hashes=[];$source_ids=[];
foreach($sources as $name=>$snapshot){
 $src=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA full composition source '.$name]);$source_ids[]=$src;update_post_meta($src,'_elementor_edit_mode','builder');update_post_meta($src,'_elementor_data',wp_slash(wp_json_encode($snapshot['data'])));update_post_meta($src,'_elementor_page_settings',$snapshot['page_settings']);
 $dst=wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'QA full composition target '.$name,'post_content'=>'Protected original']);$targets[]=$dst;$hashes[$dst]=$backup->fingerprint($dst);
 $source_hash=$backup->fingerprint($src);$job=$service->create($service->prepare($src,(string)$dst));$runner->process_batch($job->id);$job=$repo->get($job->id);
 $check($job->items[0]['status']==='analyzed'&&!$job->items[0]['divi_data']['blockers'],'full composition saved review '.$name.' '.($job->items[0]['error']??implode(' | ',array_slice($job->items[0]['divi_data']['blockers']??[],0,4))));
 $check($backup->fingerprint($src)===$source_hash&&$backup->fingerprint($dst)===$hashes[$dst],'full review changes neither page '.$name);$jobs[$job->id]=[0];
}
$apply->queue($jobs,true);
foreach($jobs as $id=>$indexes){$runner->process_batch($id);$job=$repo->get($id);$dst=$job->items[0]['target_page_id'];$check($job->items[0]['status']==='converted'&&get_post($dst)->post_content===$job->items[0]['divi_data']['content'],'full composition bulk Apply exact output and bound target');$runner->rollback($id);$check($backup->fingerprint($dst)===$hashes[$dst],'full composition rollback exact content and metadata');}
foreach(array_merge($source_ids,$targets) as $id){wp_delete_post($id,true);}
remove_filter('pre_http_request',$http);remove_filter('pre_wp_mail',$mail);
// Save full output privately for browser projection QA, never inside the plugin ZIP.
foreach($outputs as $name=>$out){file_put_contents($dir.'/private-composed-'.$name.'.json',wp_json_encode($out,JSON_PRETTY_PRINT));}
echo 'PASS: '.$checks.' full reverse composition WordPress assertions; '.($native?'installed native Divi '.ET_BUILDER_VERSION:'declared Divi runtime stubs').".\n";
