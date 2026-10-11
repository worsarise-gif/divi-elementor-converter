<?php
namespace SuiteEdge\Migrator\Backup;
class Rollback {
    public function restore($id,$job) { return (new BackupManager())->restore_backup($id,$job); }
}
