<?php
namespace SuiteEdge\Migrator\Assets;
/** Divi 4 portability schema: source URL => { encoded: raw base64, url, id }. */
class EmbeddedImageImporter {
    public function import($url,array $asset,$post_id = 0,$alt = '') {
        if (!current_user_can('upload_files') || !is_string($asset['encoded'] ?? null) || strlen($asset['encoded']) > 13981016) { return false; }
        $bytes = base64_decode($asset['encoded'],true);
        if ($bytes === false || strlen($bytes) > 10485760 || $bytes === '') { return false; }
        require_once ABSPATH.'wp-admin/includes/media.php'; require_once ABSPATH.'wp-admin/includes/file.php'; require_once ABSPATH.'wp-admin/includes/image.php';
        $name = sanitize_file_name(basename((string)wp_parse_url($url,PHP_URL_PATH)));
        if (!$name) { return false; }
        $tmp = wp_tempnam($name); if (!$tmp) { return false; }
        try {
            if (file_put_contents($tmp,$bytes) !== strlen($bytes) || !wp_get_image_mime($tmp)) { return false; }
            $checked = wp_check_filetype_and_ext($tmp,$name);
            if (empty($checked['ext']) || strpos((string)$checked['type'],'image/') !== 0) { return false; }
            $id = media_handle_sideload(['name'=>$name,'tmp_name'=>$tmp],$post_id);
            if (is_wp_error($id)) { return false; }
            update_post_meta($id,'_suiteedge_migrator_source_url',$url);
            if ($alt !== '') { update_post_meta($id,'_wp_attachment_image_alt',sanitize_text_field($alt)); }
            return ['id'=>$id,'url'=>wp_get_attachment_url($id)];
        } finally { if (is_file($tmp)) { wp_delete_file($tmp); } }
    }
}
