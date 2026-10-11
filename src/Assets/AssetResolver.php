<?php
namespace SuiteEdge\Migrator\Assets;
class AssetResolver {
    public function resolve_image($url,$post_id = 0,$settings = [],$alt = '',array $embedded = []) {
        if (!empty($settings['reuse_library'])) {
            $id = (new DuplicateDetector())->find_existing($url);
            if ($id) { return ['id'=>$id,'url'=>wp_get_attachment_url($id)]; }
        }
        if (!empty($settings['import_external'])) {
            if (isset($embedded[$url]) && is_array($embedded[$url])) { $result = (new EmbeddedImageImporter())->import($url,$embedded[$url],$post_id,$alt); if ($result) { return $result; } }
            $result = (new MediaImporter())->import_image($url,$post_id,$alt); if ($result) { return $result; }
        }
        return ['id'=>'','url'=>$url];
    }
}
