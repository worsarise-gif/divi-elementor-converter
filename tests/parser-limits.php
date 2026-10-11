<?php
require __DIR__.'/bootstrap.php';
$checks=0;$check=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException('FAIL: '.$label);}$checks++;};
$parser=new SuiteEdge\Migrator\Divi\ShortcodeTokenizer();
$reject=function($raw,$label)use($parser,$check){try{$parser->tokenize($raw);}catch(LengthException $e){$check(true,$label);return;}$check(false,$label);};
foreach(['[et_pb_text]x[/et_pb_text]','[et_pb_code]<b>x</b>[/et_pb_code]','[et_pb_code /]']as$module){
    $check(count($parser->tokenize(str_repeat($module,10000)))===10000,'10000 accepted: '.$module);
    $reject(str_repeat($module,10001),'10001 rejected: '.$module);
}
$reject(str_repeat('[et_pb_text]x[/et_pb_text][et_pb_code]x[/et_pb_code]',5001),'mixed nodes share the limit');
$raw=str_repeat('[et_pb_section]',100).'[et_pb_code]literal[/et_pb_code]'.str_repeat('[/et_pb_section]',100);
$reject($raw,'code at excessive nesting rejected');
$nodes=$parser->tokenize('[et_pb_code /][et_pb_code title="quoted ] bracket"]<pre>[et_pb_text]literal[/et_pb_text]</pre>[/et_pb_code]');
$check(count($nodes)===2&&$nodes[0]['raw_content']===''&&$nodes[1]['raw_content']==='<pre>[et_pb_text]literal[/et_pb_text]</pre>','self closing code cannot swallow following code; literal content intact');
echo 'PASS: '.$checks.' parser-limit assertions on PHP '.PHP_VERSION.PHP_EOL;
