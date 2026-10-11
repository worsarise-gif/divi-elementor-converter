<?php
namespace SuiteEdge\Migrator\Support;
class Strings {
    public static function classes($value) { return preg_split('/\s+/',trim($value),-1,PREG_SPLIT_NO_EMPTY); }
}
