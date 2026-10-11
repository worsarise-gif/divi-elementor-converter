<?php
namespace SuiteEdge\Migrator\Storage;
/** Bounded cleanup: never delete backups, active/recovery jobs or a referenced source. */
class Retention {
    const HOOK = 'suiteedge_migrator_cleanup';
    public function register() {
        add_action(self::HOOK,[$this,'run']);
        if (!wp_next_scheduled(self::HOOK)) { wp_schedule_event(time()+HOUR_IN_SECONDS,'daily',self::HOOK); }
    }
    private function has_backup($job_id) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared private-option or session-lock query; live cross-worker state must not be cached.
        return (bool)$wpdb->get_var($wpdb->prepare("SELECT option_id FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 1",$wpdb->esc_like('suiteedge_backup_'.$job_id.'_').'%'));
    }
    private function referenced($source_id) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared private-option or session-lock query; live cross-worker state must not be cached.
        return (bool)$wpdb->get_var($wpdb->prepare("SELECT option_id FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value LIKE %s LIMIT 1",$wpdb->esc_like('suiteedge_job_').'%','%'.$wpdb->esc_like('s:9:"source_id";s:36:"'.$source_id.'";').'%'));
    }
    public function run() {
        global $wpdb;
        $lock = new \SuiteEdge\Migrator\Jobs\LockManager();
        try { $lock->acquire('retention'); } catch (\RuntimeException $e) { return; }
        try {
            $cursor = (int)get_option('suiteedge_cleanup_cursor',0);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared private-option or session-lock query; live cross-worker state must not be cached.
            $keys = $wpdb->get_results($wpdb->prepare("SELECT option_id, option_name FROM {$wpdb->options} WHERE option_id > %d AND (option_name LIKE %s OR option_name LIKE %s) ORDER BY option_id ASC LIMIT 100",$cursor,$wpdb->esc_like('suiteedge_job_').'%',$wpdb->esc_like('suiteedge_source_').'%'));
            foreach ($keys as $row) {
                $cursor = (int)$row->option_id; $record = get_option($row->option_name,null);
                if (!is_array($record) || !is_int($record['created'] ?? null) || $record['created'] > time()) { continue; }
                if (strpos($row->option_name,'suiteedge_job_') === 0) {
                    $days = (int)get_option('suiteedge_job_retention_days',90);
                    if (!$days || $record['created'] > time()-max(30,$days)*DAY_IN_SECONDS || !in_array($record['status'] ?? '',['completed','completed_with_errors','cancelled','rolled_back'],true)) { continue; }
                    $job_id = substr($row->option_name,strlen('suiteedge_job_')); $job_lock = new \SuiteEdge\Migrator\Jobs\LockManager();
                    try {
                        $job_lock->acquire('job:'.$job_id);
                        // Any backup, including a restored one, protects its report and source indefinitely.
                        if (!$this->has_backup($job_id) && !array_filter($record['items'] ?? [],function ($item) { return !empty($item['created_target']) || in_array($item['status'] ?? '',['processing','needs_recovery','converted'],true); })) { delete_option($row->option_name); }
                    } catch (\RuntimeException $e) { /* A live job is protected. */ }
                    finally { $job_lock->release(); }
                } else {
                    $days = (int)get_option('suiteedge_source_retention_days',30);
                    if ($days && $record['created'] <= time()-max(2,$days)*DAY_IN_SECONDS && !$this->referenced(substr($row->option_name,strlen('suiteedge_source_')))) { delete_option($row->option_name); }
                }
            }
            update_option('suiteedge_cleanup_cursor',count($keys) < 100 ? 0 : $cursor,false);
        } finally { $lock->release(); }
    }
    public static function deactivate() { wp_clear_scheduled_hook(self::HOOK); }
}
