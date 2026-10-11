<?php
spl_autoload_register(function ($class) {
    $prefix = 'SuiteEdge\\Migrator\\';
    if (strpos($class, $prefix) === 0) {
        $file = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) { require_once $file; }
    }
});
