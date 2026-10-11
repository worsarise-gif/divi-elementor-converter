<?php
namespace SuiteEdge\Migrator\Elementor;
class CssRegenerator {
    public function regenerate($id) {
        delete_post_meta($id,'_elementor_css'); delete_post_meta($id,'_elementor_page_assets');
        if (class_exists('Elementor\\Core\\Files\\CSS\\Post')) { (new \Elementor\Core\Files\CSS\Post($id))->update(); }
        clean_post_cache($id);
    }
}
