<?php
namespace SuiteEdge\Migrator\Reverse;

/** Title visibility follows saved content, so existing backup/rollback stays exact. */
class Presentation {
    public function register() { add_filter('body_class',[$this,'body_class']); }
    public function body_class($classes) {
        if(is_singular('page')){$post=get_post();if($post&&get_post_meta($post->ID,'_et_pb_use_builder',true)==='on'&&strpos($post->post_content,'data-ace-reverse-hide-title="1"')!==false){$classes[]='ace-reverse-hide-title';}}
        return $classes;
    }
    public static function marker($selector) {
        // A page title selector may name a tag/class/id, not arbitrary stylesheet code.
        if(!is_string($selector)||strlen($selector)>200||!preg_match('/^[a-zA-Z0-9_.# >:-]+$/D',$selector)){throw new \UnexpectedValueException('Source page title selector needs a presentation mapping.');}
        return '<span hidden data-ace-reverse-hide-title="1"></span><style>body.ace-reverse-hide-title '.$selector.'{display:none!important}</style>';
    }
}
