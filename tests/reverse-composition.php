<?php
require __DIR__.'/bootstrap.php';
use SuiteEdge\Migrator\Reverse\CssScope;
use SuiteEdge\Migrator\Reverse\Document;
use SuiteEdge\Migrator\Reverse\Values;
$checks=0;$check=function($ok,$name)use(&$checks){if(!$ok){throw new RuntimeException($name);}$checks++;};
$reject=function($fn,$name)use($check){try{$fn();}catch(Throwable $error){$check(true,$name);return;}$check(false,$name);};
$values=new Values();
foreach(['app.jobtread.com/sign-in'=>'http://app.jobtread.com/sign-in','https://example.test/a?q=1'=>'https://example.test/a?q=1','/contact/'=>'/contact/','#faq'=>'#faq','?page=2'=>'?page=2','tel:+123'=>'tel:+123','mailto:test@example.test'=>'mailto:test@example.test',''=>''] as $source=>$expected){$check($values->url($source)===$expected,'URL '.$source);}
foreach(['javascript:alert(1)','data:text/html,test','//example.test','bad relative path','https://example.test/"x',"https://example.test/\n",[],false] as $bad){$reject(function()use($values,$bad){$values->url($bad);},'unsafe URL');}
$css=new CssScope();
$check($css->compile(':root{--x:1}.a,.b{color:red}', '.scope')==='.scope{--x:1}.scope .a,.scope .b{color:red}','root variables and selector isolation');
$check($css->compile('[data-v="a,b"],:is(.a,.b){content:"{;}"}', '.scope')==='.scope [data-v="a,b"],.scope :is(.a,.b){content:"{;}"}','quoted/function commas are retained');
$check($css->compile('@media(max-width:767px){.a{padding:0}}', '.scope')==='@media(max-width:767px){.scope .a{padding:0}}','nested responsive scope');
$check(strpos($css->compile('@keyframes spin{to{transform:rotate(360deg)}}', '.scope'),'.scope')===false,'keyframe frames are not selectors');
$check($css->compile('/* source */{} .a{color:red}', '.scope')==='.scope .a{color:red}','official compiler empty rule is harmless');
$check($css->compile(".parent\n.child{color:red}",'.scope')==='.scope .parent .child{color:red}','multiline descendant selector survives Divi newline removal');
$check($css->compile('[data-note="/*keep*/"]{content:"a  b";}', '.scope')==='.scope [data-note="/*keep*/"]{content:"a  b";}','quoted comments and spaces remain literal');
$check($css->compile(".a{--spacing:1px\n2px}",'.scope')==='.scope .a{--spacing:1px 2px}','multiline value tokens stay separate');
$check($css->compile(".a{content:\"a\\\nb\"}",'.scope')==='.scope .a{content:"ab"}','CSS string continuation follows its original meaning');
$reject(function()use($css){$css->compile(".a{content:\"a\nb\"}",'.scope');},'unescaped string newline is invalid CSS');
foreach(['.a{color:red','@import "remote.css";','.a{content:"bad}','.a{}</style><script>bad</script>','.a,,.b{color:red}','/* incomplete','@unknown{.x{color:red}}'] as $bad){$reject(function()use($css,$bad){$css->compile($bad,'.scope');},'invalid CSS');}
$widget=function($id,$type,$settings,$children=[]){return ['id'=>$id,'elType'=>'widget','widgetType'=>$type,'settings'=>$settings,'elements'=>$children];};
$container=function($id,$settings,$children=[]){return ['id'=>$id,'elType'=>'container','settings'=>$settings,'elements'=>$children];};
foreach(['p','span','div'] as $tag){$out=(new Document())->convert(['data'=>[$widget('title','heading',['title'=>'A label','header_size'=>$tag])]]);$check(!$out['blockers']&&strpos($out['content'],'<'.$tag.'>A label</'.$tag.'>')!==false,'valid text tag '.$tag);}
$out=(new Document())->convert(['data'=>[$widget('title','heading',['title'=>'A','header_size'=>'span','link'=>['url'=>'/','nofollow'=>'on']])]]);$check((bool)$out['blockers'],'unmapped link attributes still block');
$out=(new Document())->convert(['data'=>[$widget('box','icon-box',['selected_icon'=>['value'=>'','library'=>''],'title_text'=>'A','title_color'=>'#123456','description_color'=>'#abcdef'])]]);$check(!$out['blockers']&&strpos($out['content'],'use_icon="off"')!==false&&strpos($out['content'],'header_text_color="#123456"')!==false&&strpos($out['content'],'body_text_color="#abcdef"')!==false,'empty icon and actual blurb color properties');
$out=(new Document())->convert(['data'=>[$widget('title','heading',['title'=>'A','title_color'=>'#123456','__globals__'=>['title_color'=>'']])]]);$check(!$out['blockers']&&strpos($out['content'],'#123456')!==false,'empty global never erases local color');
$out=(new Document())->convert(['data'=>[$container('row',['flex_direction'=>'row'],[$container('col',['width'=>['unit'=>'px','size'=>400]],[$widget('body','text-editor',['editor'=>'Body'])])])]]);$check((bool)$out['blockers']&&$out['covered_element_count']===3,'pixel column cannot silently become a fraction');
$out=(new Document())->convert(['data'=>[$widget('faq','nested-accordion',['items'=>[['_id'=>'i','item_title'=>'Q']]],[$container('answer',[],[$widget('text','text-editor',['editor'=>'A'])])])]]);$check($out['source_element_count']===3&&$out['covered_element_count']===3&&$out['blockers'],'unsupported parent still audits every child');
echo 'PASS: '.$checks." reverse composition safety assertions.\n";
