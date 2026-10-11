<?php
namespace SuiteEdge\Migrator\Import;

/** Capture supported local Divi content without executing a builder or shortcode. */
class SitePageSource {
    public function id($value) {
        if (!(is_string($value) || is_int($value)) || !preg_match('/^[1-9][0-9]{0,9}$/D', (string)$value)) { throw new \InvalidArgumentException('Choose a valid page ID.'); }
        return (int)$value;
    }
    public function page($id) {
        if (!current_user_can('manage_options') || !current_user_can('unfiltered_html')) { throw new \RuntimeException('Page conversion requires administrator and unfiltered_html permissions.'); }
        return (new \SuiteEdge\Migrator\Elementor\TargetResolver())->validate($this->id($id), 'page');
    }
    public function available($post) {
        return $post && $post->post_type === 'page' && !in_array($post->post_status, ['trash','auto-draft','inherit'], true)
            && current_user_can('edit_post', $post->ID) && strpos($post->post_content, '[et_pb_') !== false
            && !preg_match('/<!--\s*wp:divi\b|_builder_version=["\']5\./i', $post->post_content)
            && get_post_meta($post->ID, '_elementor_edit_mode', true) !== 'builder';
    }
    public function capture($id) {
        $post = $this->page($id);
        if (get_post_meta($post->ID, '_elementor_edit_mode', true) === 'builder') { throw new \UnexpectedValueException('The source is already enabled in Elementor. Retained Divi content is not its current Elementor design.'); }
        if (preg_match('/<!--\s*wp:divi\b|_builder_version=["\']5\./i', $post->post_content)) { throw new \UnexpectedValueException('Divi 5 content is not supported by this Divi 4 converter.'); }
        if (strpos($post->post_content, '[et_pb_') === false) { throw new \UnexpectedValueException('The source page has no supported Divi 4 shortcode layout.'); }
        $data = [(string)$post->ID => ['post_title'=>$post->post_title, 'post_content'=>$post->post_content]];
        $bytes = strlen($post->post_content); $visited = [(string)$post->ID=>true];
        $collect = function ($content) use (&$collect,&$data,&$bytes,&$visited) {
            if ($bytes > DiviExportReader::MAX_BYTES || count($visited)>100) { throw new \LengthException('Source page and global modules exceed the snapshot limit.'); }
            $walk = function ($nodes) use (&$walk,&$collect,&$data,&$bytes,&$visited) {
                foreach ($nodes as $node) {
                    $id = $node['attributes']['global_module'] ?? '';
                    if ($id !== '') {
                        $id = (string)$this->id($id);
                        if (!isset($visited[$id])) {
                            $global = get_post((int)$id);
                            if (!$global || $global->post_type !== 'et_pb_layout' || in_array($global->post_status,['trash','auto-draft','inherit'],true) || !current_user_can('edit_post',$global->ID)) { throw new \UnexpectedValueException('A referenced Divi Library global layout is missing or unreadable. Activate its Divi library provider and check access before converting.'); }
                            if (preg_match('/<!--\s*wp:divi\b|_builder_version=["\']5\./i', $global->post_content)) { throw new \UnexpectedValueException('A referenced global layout contains unsupported Divi 5 content.'); }
                            $visited[$id] = true; $bytes += strlen($global->post_content);
                            $data[$id] = ['post_title'=>$global->post_title,'post_content'=>$global->post_content];
                            $collect($global->post_content);
                        }
                    }
                    $walk($node['children']);
                }
            };
            $walk((new \SuiteEdge\Migrator\Divi\ShortcodeTokenizer())->tokenize($content));
        };
        $collect($post->post_content);
        // Divi 4 storage key documented by the preset utility linked by Elegant Themes.
        // Its shape still passes the existing strict, versioned preset resolver.
        $presets = get_option('et_divi_builder_global_presets_ng', []);
        if (is_string($presets)) {
            if (strlen($presets)>DiviExportReader::MAX_BYTES) { throw new \LengthException('Divi preset storage exceeds the snapshot limit.'); }
            $presets = json_decode($presets,true,128,JSON_THROW_ON_ERROR);
        }
        if (!is_array($presets)) { throw new \UnexpectedValueException('Divi preset storage has an unsupported schema. Use a portability export including presets until this storage format is supported.'); }
        $json = wp_json_encode(['context'=>'ace_site_page','data'=>$data,'presets'=>$presets], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        (new DiviExportReader())->read_json($json);
        $hash = hash('sha256',$json);
        return ['json'=>$json,'site_page'=>['source_page_id'=>$post->ID,'source_page_title'=>$post->post_title,'source_page_status'=>$post->post_status,'site_url'=>home_url('/'),'captured_at'=>time(),'snapshot_hash'=>$hash,'fingerprint'=>hash('sha256',wp_json_encode([$post->ID,$post->post_type,$post->post_status,home_url('/'),$hash]))]];
    }
    public function assert_current(array $record) {
        if (!isset($record['site_page'])) { return; }
        $context = $record['site_page'];
        if (!is_array($context) || !is_string($record['json'] ?? null) || !is_string($context['snapshot_hash'] ?? null) || !hash_equals($context['snapshot_hash'], hash('sha256',$record['json'])) || ($context['site_url'] ?? '') !== home_url('/')) { throw new \RuntimeException('The local page snapshot is invalid or belongs to another site. Select the source page again.'); }
        $current = $this->capture($context['source_page_id'] ?? 0)['site_page'];
        if (!is_string($context['fingerprint'] ?? null) || !hash_equals($context['fingerprint'],$current['fingerprint'])) { throw new \RuntimeException('The source page or its Divi presets/global layouts changed after selection. Select it again and review a fresh conversion.'); }
    }
    public function suggestions(array $record) {
        $export = (new DiviExportReader())->read_json($record['json']); $result = ['menu_map'=>[],'blog_category_map'=>[],'link_map'=>[]];
        $walk = function ($nodes) use (&$walk,&$result,$export) {
            foreach ($nodes as $node) {
                try { $a=(new \SuiteEdge\Migrator\Divi\PresetResolver())->resolve_export($node['type'],$node['attributes'],$export['presets']); }
                catch (\UnexpectedValueException $e) { $a=$node['attributes']; }
                if ($node['type']==='et_pb_menu') { try { $id=(new MenuMapping())->target($a['menu_id']??0);$result['menu_map'][$id]=$id; } catch(\UnexpectedValueException $e) { /* Missing menus remain explicit blockers. */ } }
                if ($node['type']==='et_pb_blog') { try { foreach((new BlogCategoryMapping())->ids($a['include_categories']??'') as $id){(new BlogCategoryMapping())->target($id);$result['blog_category_map'][$id]=$id;} } catch(\UnexpectedValueException $e) { /* Invalid categories remain explicit blockers. */ } }
                foreach ($a as $key=>$value) {
                    $binding=(new \SuiteEdge\Migrator\Dynamic\PostLinkBinding())->parse($node['type'],$key,$value);
                    if (!$binding) { continue; } $post=get_post((int)$binding['id']);
                    if ($post && $post->post_type===$binding['type'] && in_array($post->post_status,$binding['type']==='attachment'?['inherit','publish']:['publish'],true)) { $result['link_map'][(int)$post->ID]=get_permalink($post); }
                }
                $walk($node['children']);
            }
        };
        foreach($export['layouts'] as $layout){$walk((new \SuiteEdge\Migrator\Divi\ShortcodeTokenizer())->tokenize($layout['post_content']));}
        return $result;
    }
}
