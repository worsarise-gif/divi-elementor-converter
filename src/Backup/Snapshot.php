<?php
namespace SuiteEdge\Migrator\Backup;
class Snapshot {
    public function capture($id) { return (new BackupManager())->state($id); }
}
