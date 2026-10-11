<?php
namespace SuiteEdge\Migrator\Database;
/** No custom tables: this release uses per-record, non-autoloaded options. */
class SchemaInstaller {
    public function install() { return update_option('suiteedge_migrator_schema_version',1,false); }
}
