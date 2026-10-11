<?php
namespace SuiteEdge\Migrator\Support;
class Json {
    public static function decode($json) { return json_decode($json,true,128,JSON_THROW_ON_ERROR); }
    public static function encode($data) { return json_encode($data,JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); }
}
