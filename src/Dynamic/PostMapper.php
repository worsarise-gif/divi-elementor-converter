<?php
namespace SuiteEdge\Migrator\Dynamic;
class PostMapper {
    public function map(array $payload) {
        if (!in_array($payload['dynamic_content'] ?? '',['post_title','post_excerpt','post_date','post_featured_image','custom_meta'],true)) { return null; }
        return (new DynamicMapper())->map($payload);
    }
}
