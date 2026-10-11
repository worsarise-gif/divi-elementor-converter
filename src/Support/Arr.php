<?php
namespace SuiteEdge\Migrator\Support;
class Arr {
    public static function get(array $values,$key,$default = null) { return array_key_exists($key,$values) ? $values[$key] : $default; }
    public static function only(array $values,array $keys) { return array_intersect_key($values,array_flip($keys)); }
}
