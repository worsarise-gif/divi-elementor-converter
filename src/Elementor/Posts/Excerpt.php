<?php
namespace SuiteEdge\Migrator\Elementor\Posts;

/** Read saved text without running Elementor, dynamic queries, shortcodes or content filters. */
class Excerpt {
    public function text($post,array $settings) {
        if(($settings['use_excerpt']??'yes')==='yes' && $post->post_excerpt!=='') {return $this->plain($post->post_excerpt);}
        $text='';$json=get_post_meta($post->ID,'_elementor_data',true);
        if(get_post_meta($post->ID,'_elementor_edit_mode',true)==='builder' && is_string($json) && strlen($json)<=2097152) {
            $data=json_decode($json,true,100);$chunks=[];$count=0;
            $walk=function($nodes,$depth=0)use(&$walk,&$chunks,&$count) {
                if(!is_array($nodes)||$depth>50){return;}
                foreach($nodes as $n) {
                    if(++$count>1000||!is_array($n)){return;}$s=$n['settings']??[];
                    $key=['text-editor'=>'editor','heading'=>'title','icon-box'=>'description_text','image-box'=>'description_text'][$n['widgetType']??'']??null;
                    if($key && is_string($s[$key]??null)) {$chunks[]=$this->plain($s[$key]);}
                    $walk($n['elements']??[],$depth+1);
                }
            };$walk($data);$text=implode(' ',array_filter($chunks));
        }
        if($text==='') {$text=$this->plain($post->post_content);}
        $length=max(1,min(2000,(int)($settings['excerpt_length']??270)));
        // PHP builds without mbstring still preserve UTF-8 character boundaries.
        if(function_exists('mb_substr')) {$excerpt=mb_substr($text,0,$length,'UTF-8');$more=mb_strlen($text,'UTF-8')>$length;}
        else {$characters=preg_split('//u',$text,-1,PREG_SPLIT_NO_EMPTY);if($characters===false){return '';}$excerpt=implode('',array_slice($characters,0,$length));$more=count($characters)>$length;}
        return rtrim($excerpt).($more?'…':'');
    }
    private function plain($html) {
        $html=substr((string)$html,0,2097152);
        $html=preg_replace('/\[\/?(?:et_pb|dgbm)_[a-zA-Z0-9_]+\b[^\]]*\]/','',$html);
        $html=preg_replace('~</(?:p|div|h[1-6]|li|section)>|<br\s*/?>~i',' ',$html);
        return trim(preg_replace('/\s+/u',' ',html_entity_decode(wp_strip_all_tags(strip_shortcodes($html)),ENT_QUOTES|ENT_HTML5,'UTF-8'))??'');
    }
}
