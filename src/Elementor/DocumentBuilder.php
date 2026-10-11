<?php
namespace SuiteEdge\Migrator\Elementor;
use SuiteEdge\Migrator\Model\Document;
use SuiteEdge\Migrator\Model\Node;
class DocumentBuilder {
    public $warnings = [];
    public $information = [];
    public $link_diagnostics = [];
    private $options = [];
    private $global_stack = [];
    private $links;
    private $menus;
    public function build($content, array $options = []) {
        $this->warnings = []; $this->information = []; $this->link_diagnostics = []; $this->options = $options; $this->global_stack = []; $document = new Document();
        $theme = new \SuiteEdge\Migrator\Style\SourceThemeProfile();
        $this->options['source_theme'] = $theme->validate($options['source_theme'] ?? $theme->recommended($options['source_origin'] ?? ''));
        $this->links = new \SuiteEdge\Migrator\Dynamic\PageLinkResolver();
        $this->menus = new \SuiteEdge\Migrator\Import\SourceMenuPresentation();
        $registry = new \SuiteEdge\Migrator\Mapping\ModuleRegistry();
        foreach ((new \SuiteEdge\Migrator\Divi\ShortcodeTokenizer())->tokenize($content) as $raw) { $document->add_node($this->convert($raw,$registry)); }
        if (!$document->nodes) { throw new \UnexpectedValueException('Empty conversion rejected.'); }
        $this->warnings = array_merge($this->warnings,(new FontAvailability())->warnings($document));
        $this->link_diagnostics = $this->links->diagnostics();
        $attachment_links = [];
        foreach ($this->link_diagnostics as $result) {
            if (($result['source_type'] ?? '') !== 'attachment' || empty($result['resolved_url'])) { continue; }
            $attachment_links[] = $result['resolved_url'];
            if ($result['code'] === 'resolved_public_attachment') { $this->information[] = 'Source attachment link preserved: '.$result['resolved_url'].'. The attachment is not copied by image import. To remove this source-site dependency, upload it to the destination and map source attachment ID '.$result['source_item_id'].' to its destination URL before reconverting.'; }
        }
        // This provenance survives saved Apply and native template insertion. It prevents
        // attachment permalinks from being guessed as destination page paths during media preparation.
        if ($attachment_links) { $document->nodes[0]->content['_suiteedge_attachment_links'] = array_values(array_unique($attachment_links)); }
        return $document;
    }
    private function fallback($raw, $reason) {
        $this->warnings[] = $raw['type'] . ': ' . $reason . ' Original shortcode preserved; Divi may still be required.';
        $node = new Node($raw['type'],'shortcode');
        $node->content['shortcode'] = $raw['original_shortcode']; $node->status = 'fallback';
        return $node;
    }
    private function convert($raw, $registry, array $context = []) {
        if ((strpos($raw['type'],'dgbm_') === 0 || $raw['type']==='et_pb_blog') && !empty($this->options['module_replacements'][$raw['type']])) {
            try { $node = (new \SuiteEdge\Migrator\Import\TemplateReplacement())->convert($raw['type'],$this->options['module_replacements'][$raw['type']]); }
            catch (\UnexpectedValueException $e) { return $this->fallback($raw,'Module replacement: '.$e->getMessage()); }
            $this->warnings[] = $raw['type'].': Explicit Elementor template replacement applied (#'.(int)$this->options['module_replacements'][$raw['type']].'). Review its post query, pagination, responsive design and required widget plugin. Source module design is replaced by the selected template; posts themselves are not imported.';
            return $node;
        }
        try { $attributes = (new \SuiteEdge\Migrator\Divi\PresetResolver())->resolve_export($raw['type'],$raw['attributes'],$this->options['presets'] ?? []); }
        catch (\UnexpectedValueException $e) { return $this->fallback($raw,$e->getMessage()); }
        $source_attributes = $attributes;
        $attributes = (new \SuiteEdge\Migrator\Style\SourceThemeProfile())->attributes($raw['type'],$attributes,$this->options['source_theme']);
        $a = $raw['attributes'] = (new \SuiteEdge\Migrator\Normalize\AttributeNormalizer())->normalize($attributes);
        if (function_exists('current_user_can') && !current_user_can('unfiltered_html')) { throw new \RuntimeException('Conversion requires unfiltered_html permission because imported layouts can contain HTML and shortcodes.'); }
        if ($raw['type'] === '__html') { $node = new Node('__html','text-editor'); $node->content['editor'] = $raw['text_content']; return $node; }
        if (in_array($raw['type'],['et_pb_section','et_pb_row','et_pb_row_inner','et_pb_column','et_pb_column_inner','et_pb_pricing_tables'],true)) {
            $remainder = $raw['raw_content'];
            foreach ($raw['children'] as $child) { $remainder = str_replace($child['original_shortcode'],'',$remainder); }
            if (trim($remainder) !== '') {
                if (preg_match('/\[(dgbm_[a-zA-Z0-9_]+)\b/',$remainder,$third_party)) { return $this->fallback($raw,'Unsupported third-party Divi module '.$third_party[1].'. Replace it with an Elementor Posts/Loop Grid widget or a supported post-grid integration; its dynamic query and styling cannot be inferred as ordinary text.'); }
                return $this->fallback($raw,'Mixed structural content requires review.');
            }
        }
        if (!empty($a['global_module'])) {
            $id = (string)$a['global_module'];
            if (isset($this->global_stack[$id])) { return $this->fallback($raw,'Cyclic global module reference.'); }
            try { $referenced = (new \SuiteEdge\Migrator\Divi\GlobalModuleResolver())->resolve($id,$this->options['layouts'] ?? []); }
            catch (\UnexpectedValueException $e) { return $this->fallback($raw,$e->getMessage()); }
            if (count($referenced) !== 1 || $referenced[0]['type'] !== $raw['type']) { return $this->fallback($raw,'Global layout type differs from its reference.'); }
            $this->global_stack[$id] = true;
            $local = $a; unset($local['global_module']);
            $referenced[0]['attributes'] = array_merge($referenced[0]['attributes'],$local);
            try { $node = $this->convert($referenced[0],$registry,$context); }
            finally { unset($this->global_stack[$id]); }
            $this->warnings[] = 'Global module ' . $id . ' expanded as an editable local copy; shared synchronization is not preserved.';
            return $node;
        }
        foreach ($a as $key=>$value) {
            if (strpos((string)$value,'@ET-DC@') === false) { continue; }
            $binding = (new \SuiteEdge\Migrator\Divi\DynamicContentParser())->parse($value);
            $source_id = '';
            $post_link = (new \SuiteEdge\Migrator\Dynamic\PostLinkBinding())->parse($raw['type'],$key,$value);
            $url = null;
            if ($post_link) {
                $source_id = $post_link['id'];
                $url = $this->options['link_map'][$source_id] ?? null;
                if (($url !== null || !empty($this->options['resolve_source_links'])) && function_exists('home_url')) { $url = $this->links->resolve($source_id,$this->options['source_origin'] ?? '',$this->options['link_map'] ?? [],$post_link['type'],!empty($this->options['same_site_source'])); }
            } elseif (($binding['content'] ?? '') === 'home_url') {
                $url = (new \SuiteEdge\Migrator\Dynamic\SiteLinkResolver())->resolve($raw['type'],$key,$value);
            }
            if ($url !== null) { $a[$key] = $raw['attributes'][$key] = $url; }
            else {
                $reason = '';
                if ($post_link) {
                    $reason = empty($this->options['source_origin']) && empty($this->options['link_map'][$source_id]) ? ' Source site URL is missing. Enter the original Divi site URL and run a fresh conversion.' : (empty($this->options['resolve_source_links']) ? ' Public source link resolution is disabled. Enable it or provide the target URL mapping, then run a fresh conversion.' : ' '.$this->links->reason($source_id,$post_link['type']));
                } elseif (($binding['content'] ?? '') !== 'home_url') {
                    return $this->fallback($raw,'Dynamic link binding '.$key.' is unsupported or malformed for this module. Only complete page, post and attachment link tokens in supported URL fields can be resolved. Review the original binding and run a fresh conversion.');
                }
                return $this->fallback($raw,'Dynamic binding '.$key.' requires a target URL mapping'.($source_id !== '' ? ' for source '.$post_link['type'].' '.$source_id : '').'.'.$reason);
            }
        }
        $own_text = $raw['text_content'];
        foreach ($raw['children'] as $child) { $own_text = str_replace($child['original_shortcode'],'',$own_text); }
        if (strpos($own_text,'@ET-DC@') !== false) { return $this->fallback($raw,'Dynamic text binding requires review.'); }
        $converter = $registry->get_converter($raw['type']);
        if (!$converter) {
            if (strpos($raw['type'],'dgbm_') === 0) { return $this->fallback($raw,'Unsupported third-party Divi module '.$raw['type'].'. Replace it with an Elementor Posts/Loop Grid widget or a supported post-grid integration; its dynamic query and styling cannot be inferred as ordinary text.'); }
            return $this->fallback($raw,'Unsupported module.');
        }
        $pro = in_array($raw['type'],['et_pb_contact_form','et_pb_pricing_table','et_pb_wc_title'],true);
        if ($pro && !defined('ELEMENTOR_PRO_VERSION')) { return $this->fallback($raw,'Elementor Pro is required.'); }
        if ($raw['type'] === 'et_pb_wc_title' && !class_exists('WooCommerce')) { return $this->fallback($raw,'WooCommerce is required.'); }
        if ($raw['type'] === 'et_pb_menu') {
            try {
                $menu_id = $a['menu_id'] ?? '';
                if (!is_string($menu_id) || !ctype_digit($menu_id) || (int)$menu_id < 1) { throw new \UnexpectedValueException('Invalid source menu ID. Choose a valid menu in Divi and export again.'); }
                $raw['_target_menu_id'] = (new \SuiteEdge\Migrator\Import\MenuMapping())->target($this->options['menu_map'][(int)$menu_id] ?? 0);
                if (!empty($this->options['preserve_menu_design'])) {
                    try {
                        $raw['_menu_presentation']=$this->menus->read($this->options['source_origin'] ?? '',$raw['_target_menu_id'],$this->options['menu_region'] ?? 'header');
                        foreach ($raw['_menu_presentation']['warnings'] as $warning) {$this->warnings[]='Source menu design: '.$warning;}
                    } catch (\UnexpectedValueException $e) {$this->warnings[]='Source menu design: '.$e->getMessage();}
                }
                if (class_exists('Elementor\\Plugin') && !isset(\Elementor\Plugin::$instance->widgets_manager->get_widget_types()['wp-widget-nav_menu'])) { throw new \UnexpectedValueException('Elementor WordPress navigation widget is unavailable. Enable it before converting menus.'); }
            } catch (\UnexpectedValueException $e) { return $this->fallback($raw,$e->getMessage()); }
        }
        if ($raw['type'] === 'et_pb_code' && function_exists('current_user_can') && !current_user_can('unfiltered_html')) { return $this->fallback($raw,'Code migration requires unfiltered_html permission.'); }
        $raw['_same_site_assets'] = !empty($this->options['same_site_assets']);
        $raw['_source_theme'] = $this->options['source_theme'];
        $raw['_blog_category_map'] = $this->options['blog_category_map'] ?? [];
        $raw['_column_context'] = $context;
        if (in_array($raw['type'],['et_pb_accordion','et_pb_counters'],true)) {
            try {
                foreach ($raw['children'] as &$child) {
                    $child['attributes'] = (new \SuiteEdge\Migrator\Normalize\AttributeNormalizer())->normalize((new \SuiteEdge\Migrator\Divi\PresetResolver())->resolve_export($child['type'],$child['attributes'],$this->options['presets'] ?? []));
                } unset($child);
            } catch (\UnexpectedValueException $e) { return $this->fallback($raw,$e->getMessage()); }
        }
        try { $node = $converter->convert($raw); }
        catch (\UnexpectedValueException $e) { return $this->fallback($raw,$e->getMessage()); }
        if ($converter->consumes_children() && strpos($raw['raw_content'],'@ET-DC@') !== false) { return $this->fallback($raw,'Dynamic content inside a compound module requires review.'); }
        if (!$converter->consumes_children()) {
            $child_context=$context;
            if (in_array($raw['type'],['et_pb_row','et_pb_row_inner'],true)) { $child_context['row_columns']=count($raw['children']); }
            if (in_array($raw['type'],['et_pb_column','et_pb_column_inner'],true)) { $child_context['column_type']=$a['type'] ?? '4_4'; }
            foreach ($raw['children'] as $child) { $node->add_child($this->convert($child,$registry,$child_context)); }
        }
        if (in_array($raw['type'],['et_pb_row','et_pb_row_inner'],true)) {
            $gap = (float)($node->content['flex_gap']['column'] ?? 0);
            // A spanning Divi column includes the internal gutters of its grid cells.
            // 3/5 + 2/5 with an 8% gutter are 56.8% + 35.2%, not 55.2% + 36.8%.
            foreach ($node->children as $child) { if (isset($child->content['width']) && $child->content['width']['unit'] === '%') { $child->content['width']['size'] = max(0,$child->content['width']['size']*(100+$gap)/100-$gap); } }
            (new \SuiteEdge\Migrator\Style\RowLayoutTranslator())->translate($node);
        }
        if ($raw['type'] === 'et_pb_contact_form') { $this->warnings[] = 'Contact form actions are disabled. Configure and test email delivery, spam protection and conditional rules in Elementor before publishing.'; }
        if ($raw['type'] === 'et_pb_circle_counter') { $this->warnings[] = 'Circle counter adapted to a numeric counter; circle styling requires review.'; }
        foreach ($node->advanced['information'] ?? [] as $note) { $this->information[] = $raw['type'].': '.$note; }
        foreach ($node->advanced['warnings'] ?? [] as $note) { $this->warnings[] = $raw['type'].': '.$note; }
        if ($node->type === 'ace-post-grid' && class_exists('WP_Query')) {
            $query=(new \SuiteEdge\Migrator\Elementor\Posts\GridQuery())->args($node->content);
            $query['posts_per_page']=1;$query['no_found_rows']=true;
            if (!(new \WP_Query($query))->posts) { $this->warnings[]='Post grid has no matching destination published posts. Layout JSON contains no blog posts; migrate the posts and categories separately or adjust the destination query.'; }
        }
        $unhandled = array_diff_key($a,array_flip($node->advanced['handled_attributes'] ?? []));
        foreach ((new \SuiteEdge\Migrator\Mapping\AttributeCoverage())->unknown($unhandled) as $key) { $this->warnings[] = $raw['type'] . ': unmapped setting retained in source export; review ' . $key; }
        if (!empty($node->advanced['scoped_css']) && (!in_array($raw['type'],['et_pb_row','et_pb_row_inner'],true) || array_intersect(['max_width','height','custom_css_main','custom_css_main_element','custom_css_before','custom_css_after'],array_keys($source_attributes)))) { $this->information[] = $raw['type'].': supplemental scoped CSS stored in an editable Elementor HTML widget; review selectors and fonts visually.'; }
        return $node;
    }
}
