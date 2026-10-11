<?php
namespace SuiteEdge\Migrator\Jobs;
class JobRepository {
    public function save(Job $job) {
        if (!current_user_can('manage_options') || $job->owner !== get_current_user_id()) { throw new \RuntimeException('Job cannot be saved by this user.'); }
        if (!$job->id) { $job->id = wp_generate_uuid4(); }
        $key = 'suiteedge_job_' . $job->id; $record = $job->to_array();
        update_option($key,$record,false);
        if (get_option($key) !== $record) { throw new \RuntimeException('Could not persist migration job.'); }
    }
    public function get($id) {
        if (!is_string($id) || !preg_match('/^[a-f0-9-]{36}$/D',$id)) { return null; }
        $record = get_option('suiteedge_job_' . $id,null);
        if (!is_array($record) || ($record['owner'] ?? 0) !== get_current_user_id()) { return null; }
        if (isset($record['archived']) && !is_bool($record['archived'])) { return null; }
        if (($record['id'] ?? null) !== $id || !is_array($record['items'] ?? null) || !is_array($record['settings'] ?? null) || !is_array($record['summary'] ?? null) || !is_int($record['created'] ?? null) || !is_int($record['current_index'] ?? null) || $record['current_index'] < 0 || $record['current_index'] > count($record['items']) || !in_array($record['status'] ?? '',['pending','running','needs_recovery','completed','completed_with_errors','cancelled','rolled_back','rollback_failed'],true)) { return null; }
        return new Job($record);
    }
    private function owner_pattern() {
        global $wpdb;
        return 'a:%:' . $wpdb->esc_like('{s:5:"owner";i:' . get_current_user_id() . ';') . '%';
    }
    private function archive_pattern() {
        global $wpdb;
        return 'a:%:' . $wpdb->esc_like('{s:5:"owner";i:' . get_current_user_id() . ';s:8:"archived";b:1;') . '%';
    }
    public function all($page = 1,$limit = 25,$archived = false) {
        global $wpdb;
        $limit = max(1,min(100,(int)$limit)); $offset = (max(1,(int)$page)-1)*$limit;
        if ($archived) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Read current owner-scoped private option keys; a cache could hide another worker's changes.
            $keys = $wpdb->get_col($wpdb->prepare('SELECT option_name FROM %i WHERE option_name LIKE %s AND option_value LIKE %s AND option_value LIKE %s ORDER BY option_id DESC LIMIT %d OFFSET %d',$wpdb->options,$wpdb->esc_like('suiteedge_job_') . '%',$this->owner_pattern(),$this->archive_pattern(),$limit,$offset));
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Read current owner-scoped private option keys; a cache could hide another worker's changes.
            $keys = $wpdb->get_col($wpdb->prepare('SELECT option_name FROM %i WHERE option_name LIKE %s AND option_value LIKE %s AND option_value NOT LIKE %s ORDER BY option_id DESC LIMIT %d OFFSET %d',$wpdb->options,$wpdb->esc_like('suiteedge_job_') . '%',$this->owner_pattern(),$this->archive_pattern(),$limit,$offset));
        }
        $jobs = [];
        foreach ($keys as $key) { $job = $this->get(substr($key,strlen('suiteedge_job_'))); if ($job) { $jobs[] = $job; } }
        return $jobs;
    }
    public function count($archived = false) {
        global $wpdb;
        if ($archived) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Current owner-scoped private option count; freshness is required for pagination.
            return (int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM %i WHERE option_name LIKE %s AND option_value LIKE %s AND option_value LIKE %s',$wpdb->options,$wpdb->esc_like('suiteedge_job_') . '%',$this->owner_pattern(),$this->archive_pattern()));
        }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Current owner-scoped private option count; freshness is required for pagination.
        return (int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM %i WHERE option_name LIKE %s AND option_value LIKE %s AND option_value NOT LIKE %s',$wpdb->options,$wpdb->esc_like('suiteedge_job_') . '%',$this->owner_pattern(),$this->archive_pattern()));
    }
    public function get_latest_active() {
        global $wpdb;
        // Match the job state immediately before its items, never a nested page state.
        // Cleared/cancelled histories can otherwise hide an older queued job behind LIMIT.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared private-option or session-lock query; live cross-worker state must not be cached.
        $keys = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value LIKE %s AND option_value NOT LIKE %s AND (option_value LIKE %s OR option_value LIKE %s) ORDER BY option_id DESC LIMIT 100",$wpdb->esc_like('suiteedge_job_').'%',$this->owner_pattern(),$this->archive_pattern(),'%'.$wpdb->esc_like('s:6:"status";s:7:"pending";s:5:"items";a:').'%','%'.$wpdb->esc_like('s:6:"status";s:7:"running";s:5:"items";a:').'%'));
        foreach ($keys as $key) { $job = $this->get(substr($key,strlen('suiteedge_job_'))); if ($job && !$job->archived && in_array($job->status,['pending','running'],true)) { return $job; } } return null;
    }
}
