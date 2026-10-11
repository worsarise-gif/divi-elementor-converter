<?php
namespace SuiteEdge\Migrator\Jobs;
/** Locks belong to a live database session, never a timestamp that expires mid-write. */
class LockManager {
    private static $owners = [];
    private $held = [];
    private function name($resource) {
        global $wpdb;
        return 'se_' . substr(hash('sha256',DB_NAME . ':' . $wpdb->prefix . ':' . $resource),0,60);
    }
    public function acquire($resource) {
        global $wpdb;
        $name = $this->name((string)$resource);
        // A pre-upgrade 1.1 request may still own its option lock. Never overlap it or expire it by age.
        if (get_option('suiteedge_lock_'.hash('sha256',(string)$resource),null) !== null) { throw new \RuntimeException('Legacy migration lock detected. Confirm all pre-upgrade migration requests have stopped before manually clearing that lock.'); }
        if (isset(self::$owners[$name])) { throw new LockBusyException('Migration resource is locked by another request.'); }
        if (defined('DB_ENGINE') && DB_ENGINE === 'sqlite') {
            // SQLite is a single-host test backend. OS locks also release on process termination.
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- SQLite QA needs an OS descriptor for flock; WP_Filesystem cannot provide advisory file locking. Production uses database locks.
            $handle = fopen(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name . '.lock','c');
            if (!$handle) { throw new \RuntimeException('Migration locking is unavailable.'); }
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- SQLite QA needs an OS descriptor for flock; WP_Filesystem cannot provide advisory file locking. Production uses database locks.
            if (!flock($handle,LOCK_EX | LOCK_NB)) { fclose($handle); throw new LockBusyException('Migration resource is locked by another request.'); }
            $this->held[$name] = $handle;
        } else {
            if (version_compare($wpdb->db_version(),'5.7.5','<')) { throw new \RuntimeException('Migration locking requires MySQL 5.7.5+ or MariaDB 10+.'); }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared private-option or session-lock query; live cross-worker state must not be cached.
            $connection = $wpdb->get_var('SELECT CONNECTION_ID()');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared private-option or session-lock query; live cross-worker state must not be cached.
            $result = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)',$name));
            if ((string)$result === '0') { throw new LockBusyException('Migration resource is locked by another request.'); }
            if ((string)$result !== '1') { throw new \RuntimeException('Database advisory locks are unavailable.'); }
            $this->held[$name] = (string)$connection;
        }
        self::$owners[$name] = $this;
    }
    public function assert_owned() {
        global $wpdb;
        foreach ($this->held as $name=>$handle) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared private-option or session-lock query; live cross-worker state must not be cached.
            if (!is_resource($handle) && (string)$wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s)',$name)) !== (string)$handle) { throw new LockLostException('Database connection changed; migration lock lost. Stop and review this item.'); }
        }
    }
    public function release() {
        global $wpdb;
        foreach ($this->held as $name=>$handle) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- SQLite QA needs an OS descriptor for flock; WP_Filesystem cannot provide advisory file locking. Production uses database locks.
            if (is_resource($handle)) { flock($handle,LOCK_UN); fclose($handle); }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared private-option or session-lock query; live cross-worker state must not be cached.
            elseif ((string)$wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s)',$name)) === (string)$handle) { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$name)); }
            unset(self::$owners[$name]);
        }
        $this->held = [];
    }
}
