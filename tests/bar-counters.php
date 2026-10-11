<?php
require __DIR__.'/bootstrap.php';
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$label);}$checks++;};
$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();$serializer=new SuiteEdge\Migrator\Elementor\Classic\Serializer();
$source='[et_pb_counters module_id="skills" module_class="source-skills" background_color="#eeeeee" bar_bg_color="#123456" title_font="Arial|700" title_font_size="18px" title_font_size_tablet="16px" percent_font="Arial|400" percent_text_color="#ffffff" custom_padding="1px|2px|3px|4px"][et_pb_counter percent="0" module_id="zero" height="24px"]Zero[/et_pb_counter][et_pb_counter percent="37.5" height="30px" height_tablet="4vw" height_phone="12px" custom_margin="0px|0px|7px|0px"]Fraction[/et_pb_counter][et_pb_counter percent="100"]Full[/et_pb_counter][/et_pb_counters]';
$doc=$builder->build($source);$outer=$doc->nodes[0];$data=$serializer->serialize($doc);
$check($outer->type==='container'&&count($outer->children)===3,'ordered editable collection');
$check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data),'standalone schema');
$check($outer->advanced['css_id']==='skills'&&in_array('source-skills',$outer->advanced['css_classes'],true),'outer identity');
$check($outer->styles->get('background_color')===null,'track palette never paints title/collection background');
$check($data[0]['settings']['padding']['left']==='4','outer padding retained');
foreach($outer->children as $i=>$bar){
    $check($bar->type==='progress','native Free progress widget');
    $check($bar->content['percent']['size']===[0.0,37.5,100.0][$i],'precise percentage retained');
    $check($bar->content['bar_color']==='#123456'&&$bar->content['bar_bg_color']==='#eeeeee','parent fill and track on correct surfaces');
    $check($bar->content['inner_text']==='','no Elementor placeholder');
    $check($bar->styles->get('typography_font_size','mobile')['size']===16.0,'phone inherits active tablet typography');
    $check(strpos(implode('',array_column($bar->advanced['layout_css'],'css')),'transition:none;')!==false,'native fill transitions disabled for motion-safe adaptation');
}
$check(!$builder->warnings,'accepted declared fields do not warn');
$check((bool)$builder->information,'native defaults/animation adaptation is disclosed');
$fraction=$outer->children[1];$css=json_encode($fraction->advanced['scoped_css']);
$check(strpos($css,'height:4vw;')!==false&&strpos($css,'height:12px;')!==false,'independent responsive track height');
$check($fraction->styles->get('margin')['bottom']==='7','item spacing retained');
$empty=$builder->build('[et_pb_counter percent="0" /]')->nodes[0];
$check($empty->content['title']==='Progress'&&$empty->content['title_display']==='','unlabeled zero bar has hidden accessible title so native render keeps track');
$responsive=$builder->build('[et_pb_counters use_percentages="off" use_percentages_tablet="on" use_percentages_phone="off"][et_pb_counter percent="60"]Visible state[/et_pb_counter][/et_pb_counters]')->nodes[0]->children[0];
$check($responsive->content['display_percentage']==='show','native percentage markup available for active responsive state');
$rules=$responsive->advanced['scoped_css'];foreach(['desktop'=>'none','tablet'=>'block','mobile'=>'none'] as $device=>$display){$matching=array_filter($rules,function($r)use($device,$display){return$r['device']===$device&&strpos($r['selector'],'progress-percentage')!==false&&strpos($r['css'],'display:'.$display)!==false;});$check((bool)$matching,'percentage state '.$device);}
$inactive=$builder->build('[et_pb_counters title_font_size="15px" title_font_size_phone="9px" title_font_size_last_edited="off|desktop"][et_pb_counter percent="50" height="20px" height_phone="40px" height_last_edited="off|desktop"]Inactive[/et_pb_counter][/et_pb_counters]')->nodes[0]->children[0];
$check($inactive->styles->get('typography_font_size','mobile')['size']===15.0,'inactive parent device values ignored');
$check(strpos(json_encode($inactive->advanced['scoped_css']),'height:40px')===false,'inactive child device values ignored');
$child_override=$builder->build('[et_pb_counters background_color="#123456" bar_bg_color="#654321"][et_pb_counter percent="10" background_color="#abcdef" background_color_default="#fedcba" bar_background_color_default="#000000"]Override[/et_pb_counter][/et_pb_counters]')->nodes[0]->children[0];
$check($child_override->content['bar_bg_color']==='#abcdef'&&$child_override->content['bar_color']==='#654321','explicit parent/child colors beat inherited default metadata');
$hover=$builder->build('[et_pb_counters title_font_size_tablet="15px" title_font_size_phone="13px" bar_bg_color__hover="#abcdef"][et_pb_counter percent="10"]Hover[/et_pb_counter][/et_pb_counters]')->nodes[0]->children[0];
$hover_rules=array_filter($hover->advanced['scoped_css'],function($r){return strpos($r['selector'],':hover')!==false;});
$check(count($hover_rules)===1&&strpos(reset($hover_rules)['selector'],'progress-bar')!==false,'fill-only hover never resets unrelated responsive typography');
foreach(['-1','101','NaN','INF','1e2','10%',''] as $bad){$n=$builder->build('[et_pb_counters][et_pb_counter percent="'.$bad.'"]Invalid[/et_pb_counter][/et_pb_counters]')->nodes[0];$check($n->status==='fallback','invalid percentage '.$bad.' preserves complete parent');}
foreach(['[et_pb_text]Nested[/et_pb_text]','[et_pb_counter percent="50" background_image="https://example.test/image.jpg"]Image[/et_pb_counter]','[et_pb_counter percent="50" custom_css_title="display:none;"]CSS[/et_pb_counter]','[et_pb_counter percent="50" unsupported_surface="value"]Unknown[/et_pb_counter]','[et_pb_counter percent="50" _module_preset="missing"]Preset[/et_pb_counter]','[et_pb_counter percent="50" link_option_url="/go"]Link[/et_pb_counter]','[et_pb_counter percent="50"][et_pb_text]Nested item[/et_pb_text][/et_pb_counter]'] as $bad){$n=$builder->build('[et_pb_counters]'.$bad.'[/et_pb_counters]')->nodes[0];$check($n->status==='fallback'&&strpos($n->content['shortcode'],$bad)!==false,'unsupported compound retains source subtree');}
$n=$builder->build('[et_pb_counters use_background_color_gradient="on"][et_pb_counter percent="50"]Gradient[/et_pb_counter][/et_pb_counters]')->nodes[0];$check($n->status==='fallback','unaccepted parent track mode cannot silently become wrapper background');
$n=$builder->build('[et_pb_counters title_text_shadow_style="preset1"][et_pb_counter percent="50"]Shadow[/et_pb_counter][/et_pb_counters]')->nodes[0];$check($n->status==='fallback','unaccepted parent title surface cannot silently lose style');
if(getenv('ACE_MODULE_FIXTURE_DIR')){
    foreach(['legacy','divi4'] as $key){$fixtures=json_decode(file_get_contents(getenv('ACE_MODULE_FIXTURE_DIR').'/bars-'.$key.'-nodes.json'),true);$presets=$key==='divi4'?json_decode(file_get_contents(getenv('ACE_MODULE_FIXTURE_DIR').'/bars-divi4-presets.json'),true):[];
        foreach($fixtures as $raw){$d=$builder->build($raw['original_shortcode'],['presets'=>$presets]);$check(count($d->nodes[0]->children)===count($raw['children']),'actual exported children retained '.$key);$check(!$builder->warnings,'actual exported fields accepted '.$key);foreach($raw['children'] as $i=>$child){$check($d->nodes[0]->children[$i]->content['title']===$child['text_content'],'actual source label retained');$check($d->nodes[0]->children[$i]->content['percent']['size']===(float)$child['attributes']['percent'],'actual source value retained');}}
    }
}
echo 'PASS: '.$checks.' bar-counter assertions'.PHP_EOL;
