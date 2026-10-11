<?php
namespace SuiteEdge\Migrator\Reverse;

/** Lossless round trip only for an unchanged, proven local Ace forward migration. */
class Origin {
    private function settings_hash($id) {
        $kit=get_option('elementor_active_kit',0);
        return hash('sha256',wp_json_encode([get_post_meta($id,'_elementor_page_settings',true),$kit,$kit?get_post_meta($kit,'_elementor_page_settings',true):[]]));
    }
    public function record($id,array $data,array $source,$source_id,$layout_index) {
        if(!isset($source['site_page'])){return null;}
        return ['source_id'=>$source_id,'layout_index'=>$layout_index,'data_hash'=>hash('sha256',wp_json_encode($data)),'settings_hash'=>$this->settings_hash($id),'source_hash'=>hash('sha256',$source['json'])];
    }
    private function source(array $proof) {
        $source=(new \SuiteEdge\Migrator\Import\SourceRepository())->get($proof['source_id']);
        if(!isset($source['site_page'])||($source['site_page']['site_url']??'')!==home_url('/')||!hash_equals($proof['source_hash'],hash('sha256',$source['json']))){throw new \RuntimeException('Original local Divi source is no longer verifiable.');}
        $export=json_decode($source['json'],true,128,JSON_THROW_ON_ERROR);
        foreach($export['data'] as $id=>$captured){
            $post=get_post((int)$id);$root=(int)$id===(int)$source['site_page']['source_page_id'];
            if(!$post||$post->post_type!==($root?'page':'et_pb_layout')||!current_user_can('edit_post',$post->ID)||in_array($post->post_status,['trash','auto-draft','inherit'],true)||($root&&$post->post_status!==$source['site_page']['source_page_status'])||$post->post_title!==$captured['post_title']||$post->post_content!==$captured['post_content']){throw new \RuntimeException('Original Divi page or global layout changed. Use the semantic reverse mapper.');}
        }
        $presets=get_option('et_divi_builder_global_presets_ng',[]);if(is_string($presets)){$presets=json_decode($presets,true,128,JSON_THROW_ON_ERROR);}
        if($presets!==($export['presets']??[])){throw new \RuntimeException('Original Divi presets changed. Use the semantic reverse mapper.');}
        $layout=(new \SuiteEdge\Migrator\Import\DiviExportReader())->read_json($source['json'])['layouts'][$proof['layout_index']]??null;
        if(!$layout){throw new \RuntimeException('Original Divi layout is unavailable.');}return $layout['post_content'];
    }
    public function find($id,array $data) {
        $hash=hash('sha256',wp_json_encode($data));$settings=$this->settings_hash($id);
        $keys=get_option('ace_reverse_origins_'.(int)$id,[]);
        if(!is_array($keys)||count($keys)>10){return null;}
        $repo=new \SuiteEdge\Migrator\Jobs\JobRepository();
        foreach($keys as $key){if(!is_array($key)||($key['owner']??0)!==get_current_user_id()){continue;}$job=$repo->get($key['job']??'');if(!$job||($job->settings['direction']??'divi_to_elementor')!=='divi_to_elementor'){continue;}
            foreach($job->items as $index=>$item){$proof=$item['reverse_origin']??null;
                if(($item['status']??'')!=='converted'||(int)($item['target_page_id']??0)!==(int)$id||!is_array($proof)||!is_string($proof['data_hash']??null)||!hash_equals($proof['data_hash'],$hash)||!is_string($proof['settings_hash']??null)||!hash_equals($proof['settings_hash'],$settings)){continue;}
                try{return ['job'=>$job->id,'index'=>$index,'proof'=>$proof,'content'=>$this->source($proof)];}catch(\Throwable $e){continue;}
            }
        }return null;
    }
    public function register($id,$job) {
        $key='ace_reverse_origins_'.(int)$id;$index=get_option($key,[]);
        if(!is_array($index)){$index=[];}
        $index=array_values(array_filter($index,function($entry)use($job){return is_array($entry)&&($entry['job']??'')!==$job;}));
        array_unshift($index,['owner'=>get_current_user_id(),'job'=>$job]);$index=array_slice($index,0,10);
        update_option($key,$index,false);if(get_option($key)!==$index){throw new \RuntimeException('Could not persist local round-trip index.');}
    }
    public function current(array $origin,$id,array $data) {
        $found=$this->find($id,$data);
        if(!$found||$found!==$origin){throw new \RuntimeException('Verified Divi round-trip dependencies changed. Reconvert the current Elementor page.');}
    }
}
