<?php
namespace SuiteEdge\Migrator;
class Requirements {
    public function check() {
        $errors = [];
        if (version_compare(PHP_VERSION,'7.4','<')) { $errors[] = 'PHP 7.4 or newer is required.'; }
        if (!did_action('elementor/loaded')) { $errors[] = 'Elementor must be installed and active.'; }
        return $errors;
    }
}
