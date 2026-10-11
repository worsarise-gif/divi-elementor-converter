<?php
namespace SuiteEdge\Migrator\Reverse;

/** Read only current, editable Elementor page data. No frontend rendering is executed. */
class Source {
    public function available($post) {
        return $post && $post->post_type === 'page' && !in_array($post->post_status, ['trash','auto-draft','inherit'], true) && current_user_can('edit_post', $post->ID) && get_post_meta($post->ID, '_elementor_edit_mode', true) === 'builder';
    }
    public function capture($id) {
        if (!current_user_can('manage_options') || !current_user_can('unfiltered_html')) { throw new \RuntimeException('Elementor conversion requires administrator and unfiltered_html permissions.'); }
        $post = (new \SuiteEdge\Migrator\Import\SitePageSource())->page($id);
        if (!$this->available($post)) { throw new \UnexpectedValueException('Choose a page currently edited with Elementor. Retained Elementor metadata on a Divi page is not its current design.'); }
        $json = get_post_meta($post->ID, '_elementor_data', true);
        if (!is_string($json) || strlen($json) > 20971520) { throw new \LengthException('Elementor source data is missing or exceeds 20 MB.'); }
        $data = json_decode($json, true, 100, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !$data || array_keys($data) !== range(0, count($data)-1)) { throw new \UnexpectedValueException('Elementor source must contain a non-empty element list.'); }
        $page_settings = get_post_meta($post->ID, '_elementor_page_settings', true);
        $kit = get_option('elementor_active_kit', 0);
        $kit_settings = $kit ? get_post_meta($kit, '_elementor_page_settings', true) : [];
        // Elementor supplies default globals even when a new kit has never
        // persisted its system-color/type records. Capture that real source state.
        if($kit&&class_exists('Elementor\Core\Kits\Documents\Kit')){
            // The renderer lazily registers responsive defaults. Always capture
            // the same complete schema so rendering cannot masquerade as an edit.
            $breakpoints=\Elementor\Plugin::$instance->breakpoints;$previous=$breakpoints->get_responsive_control_duplication_mode();$breakpoints->set_responsive_control_duplication_mode('on');
            try{$model=new \Elementor\Core\Kits\Documents\Kit(['post_id'=>$kit,'settings'=>is_array($kit_settings)?$kit_settings:[]]);$kit_settings=$model->get_settings();}
            finally{$breakpoints->set_responsive_control_duplication_mode($previous);}
        }
        $snapshot = ['id' => $post->ID, 'title' => $post->post_title, 'status' => $post->post_status, 'site' => home_url('/'), 'data' => $data, 'page_settings' => is_array($page_settings) ? $page_settings : [], 'kit' => $kit, 'kit_settings' => is_array($kit_settings) ? $kit_settings : []];
        $snapshot['document_version']=get_post_meta($post->ID,'_elementor_version',true);
        $snapshot['media']=$this->media($data);
        $origin=(new Origin())->find($post->ID,$data);if($origin){$snapshot['origin']=$origin;}
        $snapshot['environment']=(new Providers())->environment();
        if (strlen(wp_json_encode($snapshot)) > 20971520) { throw new \LengthException('Source and global styles exceed 20 MB.'); }
        $snapshot['fingerprint'] = hash('sha256', wp_json_encode($snapshot));
        return $snapshot;
    }
    private function media(array $data) {
        $records=[];$walk=function($values)use(&$walk,&$records){foreach($values as $value){if(!is_array($value)){continue;}$id=$value['id']??null;
            if((is_int($id)||is_string($id)&&ctype_digit($id))&&$id>0&&isset($value['url'])&&wp_attachment_is_image((int)$id)){
                $post=get_post((int)$id);$records[(int)$id]=['url'=>wp_get_attachment_url($id),'alt'=>get_post_meta($id,'_wp_attachment_image_alt',true),'title'=>$post->post_title,'caption'=>$post->post_excerpt,'description'=>$post->post_content,'file'=>get_post_meta($id,'_wp_attached_file',true),'metadata'=>get_post_meta($id,'_wp_attachment_metadata',true)];
            }$walk($value);
        }};$walk($data);ksort($records);return $records;
    }
    public function assert_current(array $snapshot) {
        if (($snapshot['site'] ?? '') !== home_url('/') || !is_string($snapshot['fingerprint'] ?? null)) { throw new \RuntimeException('The Elementor source snapshot belongs to another site or is invalid.'); }
        $stored=$snapshot;unset($stored['fingerprint']);
        if(!hash_equals($snapshot['fingerprint'],hash('sha256',wp_json_encode($stored)))){throw new \RuntimeException('The private Elementor snapshot changed. Select the source again.');}
        $current = $this->capture($snapshot['id'] ?? 0);
        if (!hash_equals($snapshot['fingerprint'], $current['fingerprint'])) { throw new \RuntimeException('The Elementor source, media, providers or global kit changed. Select the page again and reconvert before applying.'); }
    }
}
