<?php
namespace SuiteEdge\Migrator\Import;
/** Job-scoped snapshots only contain fields this plugin writes. */
class BackupEngine {
    const KEYS = ['_elementor_data','_elementor_edit_mode','_elementor_template_type','_elementor_version','_elementor_css','_elementor_page_assets','_et_pb_use_builder'];
    private function key($id, $job) { return 'suiteedge_backup_' . $job . '_' . (int)$id; }
    public function state($id) {
        $post = get_post($id); if (!$post) { throw new \RuntimeException('Page missing.'); }
        $state = ['post_content'=>$post->post_content,'meta'=>[]];
        foreach (self::KEYS as $key) { $state['meta'][$key] = get_post_meta($id,$key,false); }
        if ($post->post_type === 'elementor-hf') {
            $state['target_context']=['post_type'=>$post->post_type,'post_status'=>$post->post_status,'rules'=>[]];
            foreach (['ehf_template_type','ehf_target_include_locations','ehf_target_exclude_locations','ehf_target_user_roles','display-on-canvas-template'] as $key) { $state['target_context']['rules'][$key]=get_post_meta($id,$key,false); }
        }
        if ($post->post_type === 'post') { $state['target_context']=['post_type'=>'post','post_status'=>$post->post_status]; }
        return $state;
    }
    public function fingerprint($id) { $state = $this->state($id); unset($state['meta']['_elementor_css'],$state['meta']['_elementor_page_assets']); return hash('sha256',serialize($state)); }
    private function state_hash(array $state) { unset($state['meta']['_elementor_css'],$state['meta']['_elementor_page_assets']); return hash('sha256',serialize($state)); }
    public function plan_write($id,$job,array $values,$content = null) {
        $key = $this->key($id,$job); $snapshot = get_option($key);
        if (!is_array($snapshot) || ($snapshot['owner'] ?? 0) !== get_current_user_id()) { throw new \RuntimeException('Backup unavailable.'); }
        $planned = $snapshot['state'];
        if ($content !== null) { if (!is_string($content)) { throw new \InvalidArgumentException('Planned page content must be text.'); } $planned['post_content']=$content; }
        foreach ($values as $meta_key=>$value) { $planned['meta'][$meta_key] = array_fill(0,max(1,count($planned['meta'][$meta_key])),$value); }
        $snapshot['planned_hash'] = $this->state_hash($planned);
        update_option($key,$snapshot,false);
        if (get_option($key) !== $snapshot) { throw new \RuntimeException('Write plan persistence verification failed.'); }
    }
    public function interrupted_state($id,$job) {
        $snapshot = get_option($this->key($id,$job),null);
        if ($snapshot === null) { return 'unchanged'; }
        if (!is_array($snapshot) || ($snapshot['owner'] ?? 0) !== get_current_user_id() || !isset($snapshot['state'])) { throw new \RuntimeException('Backup unavailable.'); }
        $hash = $this->fingerprint($id);
        foreach (['written_hash','planned_hash'] as $field) { if (!empty($snapshot[$field]) && hash_equals($snapshot[$field],$hash)) { $this->mark_written($id,$job); return 'written'; } }
        return hash_equals($this->state_hash($snapshot['state']),$hash) ? 'unchanged' : 'review';
    }
    public function create_backup($id, $job,$kind = 'page',$expected_hash = null) {
        $key = $this->key($id,$job);
        $snapshot = ['state'=>$this->state($id),'created'=>time(),'owner'=>get_current_user_id(),'written_hash'=>null];
        if ($expected_hash !== null && !hash_equals($expected_hash,$this->state_hash($snapshot['state']))) { throw new \RuntimeException('Target changed before backup. Newer edits are protected.'); }
        if ($kind !== 'page') { $snapshot['target_kind']=$kind; }
        if (!add_option($key,$snapshot,'',false)) { throw new \RuntimeException('Backup already exists or could not be saved; retry requires review.'); }
        if (get_option($key) !== $snapshot) { throw new \RuntimeException('Backup persistence verification failed.'); }
        return true;
    }
    public function mark_written($id,$job) {
        $key = $this->key($id,$job); $snapshot = get_option($key);
        if (!is_array($snapshot)) { throw new \RuntimeException('Backup missing.'); }
        $snapshot['written_hash'] = $this->fingerprint($id); update_option($key,$snapshot,false);
        if (get_option($key) !== $snapshot) { throw new \RuntimeException('Backup verification failed.'); }
    }
    public function restore_backup($id, $job, $recover = false) {
        $key = $this->key($id,$job); $snapshot = get_option($key,null);
        if (!is_array($snapshot) || ($snapshot['owner'] ?? 0) !== get_current_user_id()) { throw new \RuntimeException('Backup unavailable.'); }
        (new \SuiteEdge\Migrator\Elementor\TargetResolver())->validate($id,$snapshot['target_kind'] ?? 'page');
        if (isset($snapshot['state']['target_context']) && ($this->state($id)['target_context'] ?? null) !== $snapshot['state']['target_context']) { throw new \RuntimeException('Target identity, publication state or UAE display rules changed; restoration stopped to protect the new assignment.'); }
        if (!$recover && (!$snapshot['written_hash'] || !hash_equals($snapshot['written_hash'],$this->fingerprint($id)))) { throw new \RuntimeException('Page changed after migration; rollback stopped to preserve newer edits.'); }
        // Current migrations write metadata only. An unnecessary post save would reset
        // draft dates and trigger unrelated taxonomy/SEO hooks during rollback.
        $result=true;
        if (get_post($id)->post_content !== $snapshot['state']['post_content']) {
            $result = wp_update_post(wp_slash(['ID'=>(int)$id, 'post_content'=>$snapshot['state']['post_content'],'edit_date'=>true]),true);
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
        if (is_wp_error($result)) { throw new \RuntimeException($result->get_error_message()); }
        foreach ($snapshot['state']['meta'] as $meta_key=>$values) {
            delete_post_meta($id,$meta_key);
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            foreach ($values as $value) { if (!add_post_meta($id,$meta_key,wp_slash($value))) { throw new \RuntimeException('Metadata restore failed: ' . $meta_key); } }
        }
        if ($this->state($id) !== $snapshot['state']) { throw new \RuntimeException('Restored page does not match backup.'); }
        return true;
    }
}
