<?php
namespace SuiteEdge\Migrator\Assets;
class MediaImporter {
    public function import_image($url,$post_id = 0,$alt = '') {
        if (!current_user_can('upload_files') || !wp_http_validate_url($url)) { return false; }
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $tmp = wp_tempnam($url); if (!$tmp) { return false; }
        try {
            $response = wp_safe_remote_get($url,['timeout'=>20,'redirection'=>3,'stream'=>true,'filename'=>$tmp,'limit_response_size'=>10485761]);
            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200 || filesize($tmp) > 10485760 || !wp_get_image_mime($tmp)) { return false; }
            $name = sanitize_file_name(basename(wp_parse_url($url,PHP_URL_PATH)));
            if (!$name) { return false; }
            $id = media_handle_sideload(['name'=>$name,'tmp_name'=>$tmp],$post_id);
            if (is_wp_error($id)) { return false; }
            update_post_meta($id,'_suiteedge_migrator_source_url',$url);
            if ($alt !== '') { update_post_meta($id,'_wp_attachment_image_alt',sanitize_text_field($alt)); }
            return ['id'=>$id,'url'=>wp_get_attachment_url($id)];
        } finally { if (is_file($tmp)) { wp_delete_file($tmp); } }
    }
}
