<?php
namespace SuiteEdge\Migrator\Support;
/** In-memory logging only; source HTML and credentials are never sent to PHP logs. */
class Logger {
    private $entries = [];
    public function add($level,$message) { $this->entries[] = ['level'=>$level,'message'=>$message]; }
    public function entries() { return $this->entries; }
}
