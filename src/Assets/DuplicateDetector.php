<?php
namespace SuiteEdge\Migrator\Assets;
class DuplicateDetector {
    public function find_existing($url) {
        $id = attachment_url_to_postid($url); if ($id && get_post_type($id) === 'attachment') { return $id; }
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Bounded one-attachment provenance lookup avoids duplicate media; exact source URL matching requires this metadata.
        $ids = get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'fields'=>'ids','meta_key'=>'_suiteedge_migrator_source_url','meta_value'=>$url]);
        return $ids ? (int)$ids[0] : false;
    }
}
