<?php
require __DIR__.'/bootstrap.php';
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$label);}$checks++;};
$token=function($type,$id=1543,$extra=[]) {return '@ET-DC@'.base64_encode(json_encode(array_merge(['dynamic'=>true,'content'=>'post_link_url_'.$type,'settings'=>['post_id'=>$id]],$extra))).'@';};
$parser=new SuiteEdge\Migrator\Dynamic\PostLinkBinding();
foreach(['page','post','attachment']as$type){$check($parser->parse('et_pb_button','button_url',$token($type))===['type'=>$type,'id'=>'1543'],'typed binding '.$type);}
foreach(['et_pb_cta'=>'button_url','et_pb_image'=>'url','et_pb_blurb'=>'link_option_url','et_pb_icon'=>'url','et_pb_menu'=>'logo_url','et_pb_pricing_table'=>'button_url','et_pb_social_media_follow_network'=>'url']as$module=>$field){$check($parser->parse($module,$field,$token('attachment'))!==null,'supported URL field '.$module);}
foreach([$token('product'),'prefix'.$token('attachment'),$token('attachment').'suffix',$token('attachment').$token('page'),'@ET-DC@invalid@',$token('attachment',[]),$token('attachment',0),$token('attachment',-1),$token('attachment',true),$token('attachment','1e3'),$token('attachment',1543,['dynamic'=>false]),$token('attachment',1543,['settings'=>['post_id'=>'1543','before'=>'prefix']])]as$bad){$check($parser->parse('et_pb_button','button_url',$bad)===null,'malformed/unsupported binding rejected');}
$check($parser->parse('et_pb_button','button_text',$token('page'))===null,'URL binding cannot overwrite text');
$check($parser->parse('et_pb_image','src',$token('attachment'))===null,'attachment permalink cannot become an image URL');
foreach([$token('page','99999999999999999999999'),$token('page',1543,['dynamic'=>null]),$token('page',1543,['before'=>'prefix'])]as$bad){$check($parser->parse('et_pb_button','button_url',$bad)===null,'overflow/extra binding semantics protected');}
$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$serializer=new SuiteEdge\Migrator\Elementor\Classic\Serializer();
$build=function($attrs)use($builder,$serializer){return $serializer->serialize($builder->build('[et_pb_image src="https://example.test/photo.png" '.$attrs.' /]'));};
$data=$build('force_fullwidth="on" width="50%" max_width="300px" height="90px"');
$check($data[0]['settings']['width']===['unit'=>'%','size'=>100],'native image width is full');
$check($data[0]['settings']['_element_custom_width']['size']==50,'explicit module width kept separately');
$all=json_encode($data);$check(strpos($all,'max-width:300px;')!==false&&strpos($all,'width:100%;')!==false,'outer cap and image surface retained');
$check(!$builder->warnings,'handled full-width does not report false unmapped warnings');
$check(strpos($all,'.elementor-element.elementor-element{max-width:300px;}')!==false,'explicit widget cap outranks Elementor flex defaults');
$data=$build('force_fullwidth="on" force_fullwidth_tablet="off" force_fullwidth_phone="on" force_fullwidth_last_edited="on|desktop"');$all=json_encode($data);
$check($data[0]['settings']['width_mobile']['size']===100&&strpos($all,'width:auto;')!==false,'responsive off reset and on native mobile width');
$data=$build('force_fullwidth="on" force_fullwidth_tablet="off" force_fullwidth_phone="off" force_fullwidth_last_edited="off|desktop"');
$check(!isset($data[0]['settings']['width_mobile'])&&strpos(json_encode($data),'width:auto;')===false,'inactive device full-width values ignored');
foreach(['invalid','yes']as$value){$data=$build('force_fullwidth="'.$value.'"');$check($data[0]['widgetType']==='shortcode','invalid full-width setting retains protected fallback');}
$data=$build('force_fullwidth="off"');$check(!isset($data[0]['settings']['width'])&&!$builder->warnings,'explicit off does not impose native percentage width');
$data=$build('force_fullwidth=""');$check(!isset($data[0]['settings']['width'])&&!$builder->warnings,'empty default full-width value does not report false mapping failure');
$data=$build('force_fullwidth="on" force_fullwidth__hover="off" force_fullwidth__hover_enabled="on|hover"');$check((bool)$builder->warnings,'unsupported hover remains reported rather than silently dropped');
echo 'PASS: '.$checks.' attachment link and full-width image assertions'.PHP_EOL;
