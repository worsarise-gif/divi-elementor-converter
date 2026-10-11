<?php
namespace SuiteEdge\Migrator\Dynamic;
/** Only complete, understood link bindings may replace a URL control. */
class PostLinkBinding {
    public function parse($module,$attribute,$value) {
        $fields = ['et_pb_button'=>['button_url'],'et_pb_cta'=>['button_url'],'et_pb_pricing_table'=>['button_url'],'et_pb_image'=>['url'],'et_pb_blurb'=>['url','link_option_url'],'et_pb_icon'=>['url','link_option_url'],'et_pb_menu'=>['logo_url'],'et_pb_social_media_follow_network'=>['url']];
        if (!in_array($attribute,$fields[$module] ?? [],true) || !is_string($value) || !preg_match('/^@ET-DC@[^@]+@$/D',$value)) { return null; }
        $binding = (new \SuiteEdge\Migrator\Divi\DynamicContentParser())->parse($value);
        $types = ['post_link_url_page'=>'page','post_link_url_post'=>'post','post_link_url_attachment'=>'attachment'];
        if (!is_array($binding) || array_diff(array_keys($binding),['dynamic','content','settings']) || !is_string($binding['content'] ?? null) || !isset($types[$binding['content']]) || (array_key_exists('dynamic',$binding) && $binding['dynamic'] !== true) || !is_array($binding['settings'] ?? null) || array_diff(array_keys($binding['settings']),['post_id'])) { return null; }
        $id = $binding['settings']['post_id'] ?? null;
        if ((!is_int($id) && !is_string($id)) || !ctype_digit((string)$id) || (int)$id < 1 || (string)(int)$id !== ltrim((string)$id,'0')) { return null; }
        // Missing dynamic is accepted for historical exports; explicitly false is not.
        return ['type'=>$types[$binding['content']],'id'=>(string)(int)$id];
    }
}
