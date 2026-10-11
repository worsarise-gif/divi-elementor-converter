<?php
namespace SuiteEdge\Migrator\Admin;
class AjaxHandler {
    public function __construct() {
        add_action('wp_ajax_suiteedge_migrator_apply_saved',[$this,'apply_saved']);
        add_action('wp_ajax_suiteedge_migrator_clear_jobs',[$this,'clear_jobs']);
        add_action('wp_ajax_suiteedge_migrator_process_batch',[$this,'process_batch']);
        add_action('wp_ajax_suiteedge_migrator_cancel',[$this,'cancel_job']);
        add_action('wp_ajax_suiteedge_migrator_rollback',[$this,'rollback_job']);
        add_action('wp_ajax_suiteedge_migrator_recover',[$this,'recover_job']);
        add_action('wp_ajax_suiteedge_migrator_review_recovery',[$this,'review_recovery']);
    }
    private function runner() {
        check_ajax_referer('suiteedge_migrator_nonce','nonce');
        if (!current_user_can('manage_options')) { wp_send_json_error(['message'=>'Unauthorized.'],403); }
        return new \SuiteEdge\Migrator\Jobs\BatchRunner(new \SuiteEdge\Migrator\Jobs\JobRepository());
    }
    public function process_batch() {
        $runner = $this->runner();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public handlers call runner() first; it verifies suiteedge_migrator_nonce and manage_options before reading request values.
        try { wp_send_json_success($runner->process_batch(sanitize_text_field(wp_unslash($_POST['job_id'] ?? '')), (int)sanitize_text_field(wp_unslash($_POST['batch_size'] ?? 1)))); }
        catch (\Throwable $e) { wp_send_json_error(['message'=>$e->getMessage()],400); }
    }
    public function cancel_job() {
        $runner = $this->runner();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public handlers call runner() first; it verifies suiteedge_migrator_nonce and manage_options before reading request values.
        try { wp_send_json_success($runner->cancel(sanitize_text_field(wp_unslash($_POST['job_id'] ?? '')))); }
        catch (\Throwable $e) { wp_send_json_error(['message'=>$e->getMessage()],400); }
    }
    public function rollback_job() {
        $runner = $this->runner();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public handlers call runner() first; it verifies suiteedge_migrator_nonce and manage_options before reading request values.
        try { wp_send_json_success($runner->rollback(sanitize_text_field(wp_unslash($_POST['job_id'] ?? '')))); }
        catch (\Throwable $e) { wp_send_json_error(['message'=>$e->getMessage()],400); }
    }
    private function json_input($key) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Called only after runner() authentication; bounded JSON is decoded and validated by SavedOutputApplier.
        $raw = wp_unslash($_POST[$key] ?? '');
        if (!is_string($raw) || strlen($raw) > 65536) { throw new \InvalidArgumentException('Invalid selection size.'); }
        $value = json_decode($raw,true,16,JSON_THROW_ON_ERROR);
        if (!is_array($value)) { throw new \InvalidArgumentException('Selection must be an array or object.'); }
        return $value;
    }
    public function apply_saved() {
        $this->runner();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public handlers call runner() first; it verifies suiteedge_migrator_nonce and manage_options before reading request values.
        try { wp_send_json_success((new \SuiteEdge\Migrator\Jobs\SavedOutputApplier())->queue($this->json_input('selection'),sanitize_text_field(wp_unslash($_POST['reviewed_warnings'] ?? '')) === '1')); }
        catch (\Throwable $e) { wp_send_json_error(['message'=>$e->getMessage()],400); }
    }
    public function clear_jobs() {
        $this->runner();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- runner() above verifies nonce and manage_options before the POST read.
        try { $ids = $this->json_input('jobs'); foreach ($ids as $id) { if (!is_string($id)) { throw new \InvalidArgumentException('Invalid job ID.'); } } wp_send_json_success((new \SuiteEdge\Migrator\Jobs\SavedOutputApplier())->clear($ids,sanitize_text_field(wp_unslash($_POST['restore'] ?? '')) === '1')); }
        catch (\Throwable $e) { wp_send_json_error(['message'=>$e->getMessage()],400); }
    }
    public function recover_job() {
        $runner = $this->runner();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public handlers call runner() first; it verifies suiteedge_migrator_nonce and manage_options before reading request values.
        try { wp_send_json_success($runner->recover(sanitize_text_field(wp_unslash($_POST['job_id'] ?? '')))); }
        catch (\Throwable $e) { wp_send_json_error(['message'=>$e->getMessage()],400); }
    }
    public function review_recovery() {
        $runner = $this->runner();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public handlers call runner() first; it verifies suiteedge_migrator_nonce and manage_options before reading request values.
        try { wp_send_json_success($runner->review_recovery(sanitize_text_field(wp_unslash($_POST['job_id'] ?? '')))); }
        catch (\Throwable $e) { wp_send_json_error(['message'=>$e->getMessage()],400); }
    }
}
