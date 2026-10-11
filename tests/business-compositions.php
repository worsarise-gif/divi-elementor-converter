<?php
require __DIR__.'/bootstrap.php';
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$label);}$checks++;};
$builder=new SuiteEdge\Migrator\Elementor\DocumentBuilder();
$serialize=function($content)use($builder){return(new SuiteEdge\Migrator\Elementor\Classic\Serializer())->serialize($builder->build($content));};
$walk=function($nodes)use(&$walk){$all=[];foreach($nodes as $node){$all[]=$node;$all=array_merge($all,$walk($node['elements']));}return $all;};
$find=function($data,$class)use($walk){foreach($walk($data) as $node){if(in_array($class,explode(' ',$node['settings']['_css_classes'] ?? $node['settings']['css_classes'] ?? ''),true)){return $node;}}return null;};
$source='[et_pb_cta title="Join us" button_text="Learn more" button_url="https://example.com/join" url_new_window="on" custom_button="on" button_use_icon="off" custom_padding="30px|40px|50px|60px" header_font="Inter|700|||||||" header_font_size="32px" header_font_size_phone="20px" body_font="Arial|400|||||||" body_font_size="17px" background_color="#123456" custom_css_promo_title="padding-bottom:25px;" ]<p>Editable body <a href="https://example.com">link</a></p>[/et_pb_cta]';
$data=$serialize($source);$check($data[0]['elType']==='container','CTA is an editable container');
$check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data),'CTA structural validation');
$title=$find($data,'ace-cta-title');$body=$find($data,'ace-cta-body');$button=$find($data,'ace-cta-button');
$check($title['settings']['title']==='Join us'&&$title['settings']['typography_font_family']==='Inter','title content and font remain native');
$check($title['settings']['typography_font_size_mobile']['size']===20.0,'title mobile size retained');
$check($body['settings']['typography_font_family']==='Arial'&&strpos($body['settings']['editor'],'Editable body')!==false,'body remains separately editable');
$check($button['settings']['link']['url']==='https://example.com/join'&&$button['settings']['link']['is_external']==='on','CTA destination and target retained');
$check($data[0]['settings']['padding']['left']==='60'&&$button['settings']['text_padding']['unit']==='em','outer padding is not mistakenly applied to button');
$check($data[0]['settings']['background_color']==='#123456'&&!isset($button['settings']['_background_color']),'background belongs to module surface');
$check(!array_filter($builder->warnings,function($x){return strpos($x,'unmapped')!==false;}),'verified CTA properties have no unexplained mapping warnings');
foreach(['button_url="" button_text="Go"','button_url="#" button_text=""','button_text="Go"'] as $attrs){$empty=$serialize('[et_pb_cta '.$attrs.' /]');$check($find($empty,'ace-cta-button')===null,'Divi empty-button visibility respected');}
$data=$serialize('[et_pb_team_member name="Jane Doe" position="Engineer" image_url="https://example.com/jane.jpg" header_font="Inter|700|||||||" position_font="Arial|400|||||||" position_font_size="16px" body_font="Arial|400|||||||" facebook_url="https://example.com/facebook" linkedin_url="https://example.com/linkedin" border_radii_image="on|50%|50%|50%|50%" background_color="#abcdef" custom_css_member_image="padding-bottom:10px;"]<p>Biography <strong>retained</strong>.</p>[/et_pb_team_member]');
$check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data),'Person structural validation');
$portrait=$find($data,'ace-person-portrait');$role=$find($data,'ace-person-position');$bio=$find($data,'ace-person-biography');$social=$find($data,'ace-person-social');
$check($portrait['settings']['image']['url']==='https://example.com/jane.jpg'&&$portrait['settings']['image']['alt']==='Jane Doe','portrait URL and accessible name retained');
$check($portrait['settings']['image_border_radius']['unit']==='%'&&$portrait['settings']['image_border_radius']['top']==='50','portrait radius is on image');
$check(!isset($data[0]['settings']['border_radius']),'portrait radius does not alter card');
$check($role['settings']['title']==='Engineer'&&$role['settings']['header_size']==='div'&&$role['settings']['typography_font_family']==='Arial','role is editable text independent from name');
$check(strpos($bio['settings']['editor'],'<strong>retained</strong>')!==false,'biography markup retained');
$check(count($social['settings']['social_icon_list'])===2&&$social['settings']['social_icon_list'][1]['link']['url']==='https://example.com/linkedin','all social destinations retained');
$check((bool)array_filter($builder->warnings,function($x){return strpos($x,'no source column context')!==false;}),'standalone portrait placement context is disclosed');
$empty=$serialize('[et_pb_team_member name="No portrait" position="Role" /]');$check($find($empty,'ace-person-portrait')===null&&$find($empty,'ace-person-social')===null,'empty portrait and social do not gain defaults');
foreach(['[et_pb_cta link_option_url="/whole-card" button_url="/button" button_text="Go" /]','[et_pb_team_member link_option_url="/profile" /]','[et_pb_cta]<p>body</p>[et_pb_text]nested[/et_pb_text][/et_pb_cta]','[et_pb_cta custom_css_promo_title="@import url(x);" title="Title" /]','[et_pb_team_member header_level="script" name="bad" /]'] as $source){$blocked=$serialize($source);$check($blocked[0]['widgetType']==='shortcode'&&$blocked[0]['settings']['shortcode']===$source,'unsafe/incomplete composition retains exact original and blocks Apply');}
foreach(['[et_pb_cta custom_button="on" button_text="Go" button_url="#" button_unknown_surface="on" /]','[et_pb_team_member name="Person" image_url="/image.jpg" border_color_all_image_phone="#123456" /]'] as $source){$blocked=$serialize($source);$check($blocked[0]['widgetType']==='shortcode','unaccepted surface cannot silently discard its setting');}
$data=$serialize('[et_pb_cta title="States" header_text_align="right" header_font="Arial|700|||||||" header_font_phone="Inter||||||||" header_text_color_phone="#123456" header_font_size__hover="40px" body_font="Arial|600|||||||" body_font_phone="Inter||||||||" body_text_color_phone="#654321" body_text_align="right"]<p>Responsive content</p>[/et_pb_cta]');
$title=$find($data,'ace-cta-title');$body=$find($data,'ace-cta-body');$check($title['settings']['align']==='right'&&$body['settings']['align']==='right','per-surface alignment takes priority over module default');
$css=implode('',array_map(function($node){return($node['widgetType']??'')==='html'?($node['settings']['html']??''):'';},$walk($data)));
$check(strpos($css,'color:#123456;')!==false&&strpos($css,'color:#654321;')!==false,'responsive title and body colors retained');
$check(preg_match('/\{(?=[^}]*font-family:"Inter";)(?=[^}]*font-weight:700;)/',$css)&&preg_match('/\{(?=[^}]*font-family:"Inter";)(?=[^}]*font-weight:600;)/',$css),'responsive families retain inherited source weights');
$check(strpos($css,':hover .ace-cta-title .elementor-heading-title{font-size:40px;}')!==false,'module hover affects title surface');
$data=$serialize('[et_pb_cta title="Padding" background_color="#abcdef" custom_padding="|30px||" /]');
$check($data[0]['settings']['padding']['top']==='40'&&$data[0]['settings']['padding']['left']==='60','partial desktop padding inherits actual CTA defaults');
$check($data[0]['settings']['padding_mobile']['top']==='40'&&$data[0]['settings']['padding_mobile']['left']==='40'&&$data[0]['settings']['padding_mobile']['right']==='30','mobile default sides change while explicit desktop side inherits');
$data=$serialize('[et_pb_cta title="No background" background_enable_color="off" background_color="#abcdef" /]');$check($data[0]['settings']['padding']['top']==='0','background disabled removes default CTA padding');
$data=$serialize('[et_pb_column type="1_3"][et_pb_cta background_color="#abcdef" /][et_pb_team_member name="Narrow" image_url="/person.jpg" /][/et_pb_column]');$check($data[0]['elements'][0]['settings']['padding']['left']==='40','narrow column CTA padding uses source rule');
$check($data[0]['elements'][1]['settings']['flex_direction']==='column'&&$data[0]['elements'][1]['settings']['flex_direction_tablet']==='row','Person column context controls source tablet transition');
$portrait=$find($data,'ace-person-portrait');$check($portrait['settings']['align']==='center'&&$portrait['settings']['align_tablet']==='left'&&$portrait['settings']['align_mobile']==='center','portrait alignment follows its source surface rather than body text');
$wide=$serialize('[et_pb_column type="3_4"][et_pb_team_member name="Three-quarter" image_url="/person.jpg" /][/et_pb_column]');$check($wide[0]['elements'][0]['settings']['flex_direction']==='column'&&$find($wide,'ace-person-portrait')['settings']['align']==='left','three-quarter Person description stacks without borrowing narrow portrait alignment');
foreach(['[et_pb_cta title="State" background_color_tablet="#123456" /]','[et_pb_cta title="State" background_color__hover="#123456" /]','[et_pb_team_member name="State" background_layout_phone="dark" /]','[et_pb_cta title="State" custom_padding__hover="10px|20px|30px|40px" /]'] as $source){$blocked=$serialize($source);$check($blocked[0]['widgetType']==='shortcode'&&$blocked[0]['settings']['shortcode']===$source,'unaccepted compound background/padding state is protected');}
foreach(['0','a','12ab','abc123','1234567','12345678'] as $id){$check((new SuiteEdge\Migrator\Elementor\Validator())->validate([['id'=>$id,'elType'=>'container','settings'=>[],'elements'=>[]]]),'native short hexadecimal ID accepted');}
foreach(['','abc123456','GHIJKLM',3,[], 'a<script>'] as $id){try{(new SuiteEdge\Migrator\Elementor\Validator())->validate([['id'=>$id,'elType'=>'container','settings'=>[],'elements'=>[]]]);$check(false,'invalid ID rejected');}catch(UnexpectedValueException $e){$check(true,'invalid ID rejected');}}
$short=['id'=>'a','elType'=>'container','settings'=>[],'elements'=>[]];try{(new SuiteEdge\Migrator\Elementor\Validator())->validate([$short,$short]);$check(false,'duplicate short IDs rejected');}catch(UnexpectedValueException $e){$check(true,'duplicate short IDs rejected');}
if(getenv('ACE_P01_FIXTURE_DIR')){
    foreach(['cta','person','summit'] as $name){
        $nodes=json_decode(file_get_contents(getenv('ACE_P01_FIXTURE_DIR').'/p01-'.$name.'-nodes.json'),true);
        foreach($nodes as $node){$data=$serialize($node['original_shortcode']);$check(!isset($data[0]['widgetType'])&&$data[0]['elType']==='container','real exported '.$node['type'].' converts');$check((new SuiteEdge\Migrator\Elementor\Validator())->validate($data),'real exported composition validates');}
    }
}
echo 'PASS: '.$checks.' business composition assertions'.PHP_EOL;
