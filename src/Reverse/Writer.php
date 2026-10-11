<?php
namespace SuiteEdge\Migrator\Reverse;

/** Enable Divi only after valid output, a fresh destination and a durable backup. */
class Writer {
    public function requirements(array $output = []) {
        if(!defined('ET_BUILDER_VERSION')||!preg_match('/^4\./',ET_BUILDER_VERSION)||!shortcode_exists('et_pb_section')){throw new \RuntimeException('Apply requires an active Divi 4 theme or Divi 4 Builder plugin. Divi 5 block output is not supported. Analysis and reports remain available.');}
        (new Providers())->validate($output['dependencies']??[]);
        foreach($output['composition_manifest']['css_assets']??[] as $relative=>$hash){if(!defined('ELEMENTOR_PATH')||!is_string($relative)||!is_string($hash)||!preg_match('~^assets/[a-zA-Z0-9_./-]+\.(?:css|woff2?|ttf|eot|svg)$~D',$relative)||strpos($relative,'..')!==false||!is_readable(ELEMENTOR_PATH.$relative)||!hash_equals($hash,hash_file('sha256',ELEMENTOR_PATH.$relative))){throw new \RuntimeException('A source CSS/font provider asset changed or is missing. Reconvert before applying.');}}
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Module names are tokenizer-validated; callers escape domain diagnostics in HTML/JSON.
        if($output){$walk=function($nodes)use(&$walk){foreach($nodes as $node){if(!shortcode_exists($node['type'])){throw new \RuntimeException('The destination is missing the Divi module/provider "'.$node['type'].'". Activate a compatible provider before applying.');}$walk($node['children']);}};$walk((new \SuiteEdge\Migrator\Divi\ShortcodeTokenizer())->tokenize($output['content']));}
    }
    public function write($id,array $output,$job,$guard,$expected) {
        if(!current_user_can('manage_options')||!current_user_can('unfiltered_html')){throw new \RuntimeException('Divi application requires administrator and unfiltered_html permissions.');}
        $this->requirements($output);(new Shortcodes())->validate($output);$guard();
        (new \SuiteEdge\Migrator\Elementor\TargetResolver())->validate($id,'page');$backup=new \SuiteEdge\Migrator\Import\BackupEngine();
        if(!hash_equals($expected,$backup->fingerprint($id))){throw new \RuntimeException('Destination changed before the Divi write. Newer edits are protected.');}
        $backup->create_backup($id,$job,'page',$expected);
        $values=['_elementor_edit_mode'=>'','_et_pb_use_builder'=>'on'];$backup->plan_write($id,$job,$values,$output['content']);
        foreach($values as $key=>$value){$guard();update_post_meta($id,$key,wp_slash($value));if(get_post_meta($id,$key,true)!==$value){throw new \RuntimeException('Divi builder metadata could not be saved. Review the interrupted-write recovery report.');}}
        $guard();$result=wp_update_post(wp_slash(['ID'=>(int)$id,'post_content'=>$output['content'],'edit_date'=>true]),true);
        if(is_wp_error($result)||!$result||get_post($id)->post_content!==$output['content']){throw new \RuntimeException('Divi content could not be saved. Review the interrupted-write recovery report.');}
        $guard();$backup->mark_written($id,$job);return true;
    }
}
