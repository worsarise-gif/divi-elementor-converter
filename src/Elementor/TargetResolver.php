<?php
namespace SuiteEdge\Migrator\Elementor;
class TargetResolver {
    public function kind($kind) {
        if (!in_array($kind,['page','post','uae-header','uae-footer'],true)) { throw new \InvalidArgumentException('Unsupported migration target type.'); }
        if ($kind === 'post' && !post_type_supports('post','elementor')) { throw new \RuntimeException('Enable Posts in Elementor Settings > General > Post Types before migrating blog posts.'); }
        if (strpos($kind,'uae-') === 0 && (!defined('HFE_VER') || !post_type_exists('elementor-hf'))) { throw new \RuntimeException('Activate UAE (Ultimate Addons for Elementor / Header & Footer Builder) before migrating its templates.'); }
        return $kind;
    }
    public function validate($id,$kind = 'page') {
        $this->kind($kind);
        $post = get_post((int)$id);
        if (!$post || $post->post_type !== (in_array($kind,['page','post'],true) ? $kind : 'elementor-hf') || in_array($post->post_status,['trash','auto-draft','inherit'],true) || !current_user_can('edit_post',$post->ID)) { throw new \RuntimeException('Target must be an editable destination of the selected page, post or UAE template type.'); }
        if (strpos($kind,'uae-') === 0 && get_post_meta($post->ID,'ehf_template_type',true) !== ($kind === 'uae-header' ? 'type_header' : 'type_footer')) { throw new \RuntimeException('UAE template type changed or does not match the selected Header/Footer target.'); }
        return $post;
    }
    public function draft($title,$kind = 'page',$source_name = '') {
        $this->kind($kind);$type=in_array($kind,['page','post'],true)?$kind:'elementor-hf';
        $object=get_post_type_object($type);
        if (!$object || !current_user_can($object->cap->create_posts)) { throw new \RuntimeException('You cannot create a draft of the selected target type.'); }
        if($kind==='post' && $title==='Divi layout' && $source_name!=='') {$title=pathinfo($source_name,PATHINFO_FILENAME);}
        $args=['post_title'=>$title,'post_type'=>$type,'post_status'=>'draft'];
        if (strpos($kind,'uae-') === 0) { $args['meta_input']=['ehf_template_type'=>$kind === 'uae-header' ? 'type_header' : 'type_footer']; }
        return wp_insert_post($args,true);
    }
    public function templates() {
        $this->kind('uae-header');
        return array_values(array_filter(get_posts(['post_type'=>'elementor-hf','post_status'=>['publish','draft','private','pending'],'numberposts'=>-1,'orderby'=>'title','order'=>'ASC']),function ($post) {return current_user_can('edit_post',$post->ID)&&in_array(get_post_meta($post->ID,'ehf_template_type',true),['type_header','type_footer'],true);}));
    }
}
