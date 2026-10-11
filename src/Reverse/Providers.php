<?php
namespace SuiteEdge\Migrator\Reverse;

/** Explicit dependencies, checked without executing third-party shortcodes. */
class Providers {
    public function form($shortcode) {
        if (!is_string($shortcode) || !preg_match('/^\[formidable\s+id=(?:"([1-9][0-9]*)"|\x27([1-9][0-9]*)\x27|([1-9][0-9]*))(?:\s+(?:title|description)=(?:"(?:0|1|true|false)"|\x27(?:0|1|true|false)\x27|(?:0|1|true|false)))*\s*\]$/D',$shortcode,$m)) { throw new \UnexpectedValueException('Provider shortcode has no accepted preservation contract.'); }
        $id=(int)($m[1]?:($m[2]?:$m[3]));
        if(!function_exists('shortcode_exists')||!shortcode_exists('formidable')||!class_exists('FrmForm')||!method_exists('FrmForm','getOne')){throw new \UnexpectedValueException('Formidable provider is unavailable. Activate Formidable Forms before reconverting.');}
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Validated numeric ID in a domain diagnostic; consumers escape report output.
        $form=\FrmForm::getOne($id);if(!$form){throw new \UnexpectedValueException('Formidable provider form '.$id.' is unavailable. Restore or map the local form before reconverting.');}
        $fields=[];if(class_exists('FrmField')){if(method_exists('FrmField','get_all_for_form')){$fields=\FrmField::get_all_for_form($id);}elseif(method_exists('FrmField','getAllForForm')){$fields=\FrmField::getAllForForm($id);}}
        $actions=[];
        if(class_exists('FrmFormAction')&&method_exists('FrmFormAction','get_action_for_form')){$actions=\FrmFormAction::get_action_for_form($id);}
        return ['kind'=>'formidable','id'=>$id,'shortcode'=>$shortcode,'version'=>class_exists('FrmAppHelper')&&method_exists('FrmAppHelper','plugin_version')?\FrmAppHelper::plugin_version():null,'fingerprint'=>hash('sha256',serialize([$form,$fields,$actions]))];
    }
    public function validate(array $dependencies) {
        foreach($dependencies as $dependency){
            if(($dependency['kind']??'')==='formidable'){$current=$this->form($dependency['shortcode']);}
            elseif(($dependency['kind']??'')==='elementor_environment'){$current=$this->environment();}
            elseif(($dependency['kind']??'')==='local_font'){$current=$this->font($dependency['url']);}
            else{throw new \RuntimeException('Unknown reverse provider dependency. Reconvert.');}
            if($current!==$dependency){throw new \RuntimeException('A reverse provider, form, font or source renderer changed after analysis. Reconvert before applying.');}
        }
    }
    public function font($url) {
        $uploads=wp_upload_dir(null,false);$base=trailingslashit($uploads['baseurl']);$root=realpath($uploads['basedir']);
        $resolve=function($resource,$directory=null)use($base,$root){
            if(!$root||!is_string($resource)){throw new \UnexpectedValueException('Local font provider directory is unavailable.');}
            if(preg_match('~^https?://~i',$resource)){if(strpos($resource,$base)!==0){throw new \UnexpectedValueException('Local font provider references an unverified remote stylesheet/file.');}$file=$root.'/'.rawurldecode(substr(strtok($resource,'?#'),strlen($base)));}
            elseif($directory&&!preg_match('~^(?:/|[a-z][a-z0-9+.-]*:)~i',$resource)){$file=$directory.'/'.rawurldecode(strtok($resource,'?#'));}
            else{throw new \UnexpectedValueException('Local font provider URL needs an explicit mapping.');}
            $file=realpath($file);if(!$file||strpos(wp_normalize_path($file),trailingslashit(wp_normalize_path($root)))!==0||!is_readable($file)){throw new \UnexpectedValueException('Local font provider stylesheet or binary is missing.');}return $file;
        };
        $file=$resolve($url);if(filesize($file)>2097152){throw new \LengthException('Local font stylesheet exceeds 2 MB.');}$css=file_get_contents($file);$files=[$file];
        if(stripos($css,'@import')!==false){throw new \UnexpectedValueException('Local font provider stylesheet imports need a verified dependency contract.');}
        preg_match_all('/url\(\s*(["\x27]?)([^)"\x27]+)\1\s*\)/',$css,$urls,PREG_SET_ORDER);
        foreach($urls as $match){if(strpos($match[2],'data:')===0){continue;}$files[]=$resolve($match[2],dirname($file));}
        $hashes=[];foreach(array_unique($files) as $asset){$hashes[substr(wp_normalize_path($asset),strlen(trailingslashit(wp_normalize_path($root))))]=hash_file('sha256',$asset);}ksort($hashes);
        return ['kind'=>'local_font','url'=>$url,'files'=>$hashes];
    }
    public function environment() {
        $fonts=[];
        if(function_exists('get_posts')){foreach(get_posts(['post_type'=>'elementor_font','post_status'=>'any','posts_per_page'=>500,'orderby'=>'ID','order'=>'ASC']) as $post){$fonts[]=[$post->ID,$post->post_title,get_post_meta($post->ID)];}}
        return ['kind'=>'elementor_environment','version'=>defined('ELEMENTOR_VERSION')?ELEMENTOR_VERSION:null,'pro_version'=>defined('ELEMENTOR_PRO_VERSION')?ELEMENTOR_PRO_VERSION:null,'google_fonts'=>function_exists('get_option')?get_option('elementor_google_font','1'):null,'local_google_fonts'=>function_exists('get_option')?get_option('elementor_local_google_fonts','0'):null,'cached_fonts'=>function_exists('get_option')?get_option('_elementor_local_google_fonts',[]):[],'disable_colors'=>function_exists('get_option')?get_option('elementor_disable_color_schemes'):null,'disable_typography'=>function_exists('get_option')?get_option('elementor_disable_typography_schemes'):null,'font_fingerprint'=>hash('sha256',serialize($fonts))];
    }
}
