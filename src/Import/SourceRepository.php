<?php
namespace SuiteEdge\Migrator\Import;
/** JSON is stored in non-autoloaded options, never in public uploads. */
class SourceRepository {
    const MAX_STORAGE_BYTES = 268435456;
    public function save($json, $name, array $site_page = []) {
        if (!current_user_can('manage_options') || !current_user_can('unfiltered_html')) { throw new \RuntimeException('Import requires manage_options and unfiltered_html permissions.'); }
        (new DiviExportReader())->read_json($json);
        $id = wp_generate_uuid4();
        $record = ['json' => $json, 'name' => sanitize_file_name($name), 'owner' => get_current_user_id(), 'created' => time()];
        if ($site_page) { $record['site_page']=$site_page;(new SitePageSource())->assert_current($record); }
        $lock = new \SuiteEdge\Migrator\Jobs\LockManager(); $lock->acquire('source-storage');
        try {
            global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared private-option or session-lock query; live cross-worker state must not be cached.
            $used = (int)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(LENGTH(option_value)),0) FROM {$wpdb->options} WHERE option_name LIKE %s",$wpdb->esc_like('suiteedge_source_').'%'));
            $limit = max(20,min(1024,(int)get_option('suiteedge_source_storage_mb',256)))*1048576;
            if ($used+strlen(serialize($record)) > $limit) { throw new \LengthException('Private source storage limit reached. Retain backup-linked sources; clean unreferenced imports or adjust the storage limit in Settings.'); }
            if (!add_option('suiteedge_source_' . $id, $record, '', false)) { throw new \RuntimeException('Could not store source export.'); }
        } finally { $lock->release(); }
        return $id;
    }
    public function get($id) {
        if (!is_string($id) || !preg_match('/^[a-f0-9-]{36}$/D', $id)) { throw new \InvalidArgumentException('Invalid source ID.'); }
        $record = get_option('suiteedge_source_' . $id, null);
        if (!is_array($record) || ($record['owner'] ?? 0) !== get_current_user_id()) { throw new \RuntimeException('Source export unavailable for this user.'); }
        return $record;
    }
}
