<?php
namespace SuiteEdge\Migrator\Import;
/** Explicit, source-file scoped replacements for unsupported extension modules. */
class TemplateReplacement {
    public function pending(array $files) {
        $result = [];
        foreach ($files as $file) {
            $id = $file['source_id']; $source = (new SourceRepository())->get($id);
            $export = (new DiviExportReader())->read_json($source['json']);
            $layout = $export['layouts'][$file['layout_index']];
            $walk = function ($nodes) use (&$walk,&$result,$id,$source) {
                foreach ($nodes as $node) {
                    if (strpos($node['type'],'dgbm_') === 0 || $node['type']==='et_pb_blog') {
                        if (!isset($result[$id])) { $result[$id] = ['name'=>$source['name'],'modules'=>[]]; }
                        $tag = $node['type']; $result[$id]['modules'][$tag] = ($result[$id]['modules'][$tag] ?? 0) + 1;
                    }
                    $walk($node['children']);
                }
            };
            $walk((new \SuiteEdge\Migrator\Divi\ShortcodeTokenizer())->tokenize($layout['post_content']));
        }
        return $result;
    }
    public function validate(array $mapping,array $overrides = []) {
        if (count($overrides) > 100) { throw new \InvalidArgumentException('Too many replacement template overrides.'); }
        foreach ($overrides as $source=>$modules) {
            if (!is_string($source) || !preg_match('/^[a-f0-9-]{36}$/D',$source) || !is_array($modules) || count($modules) > 100 || (isset($mapping[$source]) && !is_array($mapping[$source]))) { throw new \InvalidArgumentException('Invalid replacement template override.'); }
            foreach ($modules as $tag=>$id) {
                if (!is_string($tag) || !preg_match('/^(?:dgbm_[a-zA-Z0-9_-]+|et_pb_blog)$/D',$tag) || !is_string($id)) { throw new \InvalidArgumentException('Invalid replacement template ID.'); }
                if (trim($id) === '') { continue; }
                if (!ctype_digit(trim($id)) || (int)$id < 1) { throw new \InvalidArgumentException('Enter a positive replacement template ID.'); }
                $mapping[$source][$tag] = trim($id);
            }
        }
        if (count($mapping) > 100) { throw new \InvalidArgumentException('Too many source replacement mappings.'); }
        $result = [];
        foreach ($mapping as $source=>$modules) {
            if (!is_string($source) || !preg_match('/^[a-f0-9-]{36}$/D',$source) || !is_array($modules) || count($modules) > 100) { throw new \InvalidArgumentException('Invalid module replacement mapping.'); }
            (new SourceRepository())->get($source);
            foreach ($modules as $tag=>$id) {
                if (!is_string($tag) || !preg_match('/^(?:dgbm_[a-zA-Z0-9_-]+|et_pb_blog)$/D',$tag) || !(is_int($id) || is_string($id)) || !ctype_digit((string)$id)) { throw new \InvalidArgumentException('Invalid replacement template selection.'); }
                if ((int)$id === 0) { continue; }
                $this->read((int)$id); $result[$source][$tag] = (int)$id;
            }
        }
        return $result;
    }
    private function read($id) {
        if (!current_user_can('manage_options') || !current_user_can('unfiltered_html')) { throw new \UnexpectedValueException('Native template replacement requires administrator and unfiltered_html permissions.'); }
        $post = get_post($id);
        if (!$post || $post->post_type !== 'elementor_library' || !in_array($post->post_status,['publish','draft','private'],true) || !current_user_can('edit_post',$id)) { throw new \UnexpectedValueException('Choose an editable saved Elementor template for the unsupported module.'); }
        $json = get_post_meta($id,'_elementor_data',true);
        if (!is_string($json) || strlen($json) > 2097152) { throw new \UnexpectedValueException('Replacement template is empty or exceeds 2 MB.'); }
        try { $data = json_decode($json,true,100,JSON_THROW_ON_ERROR); }
        catch (\JsonException $e) { throw new \UnexpectedValueException('Replacement template has invalid Elementor data.'); }
        if (!is_array($data)) { throw new \UnexpectedValueException('Replacement template has no Elementor elements.'); }
        (new \SuiteEdge\Migrator\Elementor\Validator())->validate($data,true);
        $count = 0;
        $walk = function ($nodes) use (&$walk,&$count) {
            foreach ($nodes as $node) {
                if (++$count > 1000) { throw new \UnexpectedValueException('Replacement template exceeds 1000 elements.'); }
                if (in_array($node['widgetType'] ?? '',['shortcode','template'],true)) { throw new \UnexpectedValueException('Replacement must contain native elements, not unresolved shortcodes or nested template references.'); }
                if (($node['widgetType'] ?? '') === 'form' && empty($node['settings']['submit_actions'])) { throw new \UnexpectedValueException('Configure replacement form delivery actions before migration.'); }
                $walk($node['elements']);
            }
        }; $walk($data);
        return $data;
    }
    public function convert($tag,$id) {
        $data = $this->read($id); $wrapper = new \SuiteEdge\Migrator\Model\Node($tag,'container');
        $wrapper->content = ['content_width'=>'full','flex_direction'=>'column','flex_gap'=>['unit'=>'px','row'=>0,'column'=>0,'isLinked'=>true],'_suiteedge_replacement_template'=>(int)$id,'_suiteedge_replacement_hash'=>hash('sha256',wp_json_encode($data))];
        $copy = function ($element) use (&$copy) {
            $node = new \SuiteEdge\Migrator\Model\Node('replacement:'.($element['widgetType'] ?? 'container'),$element['elType'] === 'container' ? 'container' : $element['widgetType']);
            $node->content = (array)$element['settings'];
            $node->advanced['native_template'] = true;
            $key = $node->type === 'container' ? 'css_classes' : '_css_classes';
            // Native IDs are regenerated. Preserve explicit ID selectors used in an
            // existing template stylesheet via a class on the copied element.
            $node->content[$key] = trim(($node->content[$key] ?? '').' elementor-element-'.$element['id']);
            foreach ($element['elements'] as $child) { $node->add_child($copy($child)); }
            $node->status = 'converted_native';
            return $node;
        };
        foreach ($data as $element) { $wrapper->add_child($copy($element)); }
        $wrapper->status = 'explicit_replacement';
        return $wrapper;
    }
}
