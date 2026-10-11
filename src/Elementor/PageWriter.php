<?php
namespace SuiteEdge\Migrator\Elementor;
class PageWriter {
    public function write($id, array $data, $job, $guard = null,$kind = 'page',$expected_hash = null) {
        if ($guard) { $guard(); }
        (new TargetResolver())->validate($id,$kind); (new Validator())->validate($data,true);
        $backup = new \SuiteEdge\Migrator\Import\BackupEngine();
        // Media preparation and validation can run while an editor saves the target.
        // Check outside recovery: a rejected stale write must never restore over that edit.
        if ($expected_hash !== null && !hash_equals($expected_hash,$backup->fingerprint($id))) { throw new \RuntimeException('Target changed while media was being prepared. Newer edits are protected. Reconvert the current target.'); }
        $backup->create_backup($id,$job,$kind,$expected_hash);
        try {
            $values = ['_elementor_data'=>wp_json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '_elementor_edit_mode'=>'builder','_elementor_template_type'=>'wp-page','_elementor_version'=>defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '', '_et_pb_use_builder'=>'off'];
            if ($kind !== 'page') { $values['_elementor_template_type']='wp-post'; }
            if (strpos($kind,'uae-') === 0) { unset($values['_et_pb_use_builder']); }
            if (!is_string($values['_elementor_data'])) { throw new \RuntimeException('Could not encode Elementor data.'); }
            $backup->plan_write($id,$job,$values);
            foreach ($values as $key=>$value) {
                if ($guard) { $guard(); }
                update_post_meta($id,$key,wp_slash($value));
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                if (get_post_meta($id,$key,true) !== $value) { throw new \RuntimeException('Page write failed: ' . $key); }
            }
            (new CssRegenerator())->regenerate($id);
            $backup->mark_written($id,$job);
        } catch (\Throwable $e) {
            // Never restore after losing the lock: a different request may now own this page.
            if ($guard) { $guard(); }
            try { $backup->restore_backup($id,$job,true); }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            catch (\Throwable $restore) { throw new \RuntimeException($e->getMessage() . ' Recovery also failed: ' . $restore->getMessage()); }
            throw $e;
        }
        return true;
    }
}
