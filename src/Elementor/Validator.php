<?php
namespace SuiteEdge\Migrator\Elementor;
class Validator {
    public function validate(array $data, $runtime = false) {
        if (!$data) { throw new \UnexpectedValueException('Empty Elementor document.'); }
        $ids = []; $widgets = null; $containers = null;
        if ($runtime) {
            if (!did_action('elementor/loaded')) { throw new \RuntimeException('Elementor is not loaded.'); }
            $widgets = \Elementor\Plugin::$instance->widgets_manager->get_widget_types();
            $containers = \Elementor\Plugin::$instance->elements_manager->get_element_types('container');
        }
        $walk = function ($nodes, $depth = 0) use (&$walk, &$ids, $widgets, $containers) {
            if ($depth > 100) { throw new \LengthException('Elementor depth limit exceeded.'); }
            foreach ($nodes as $node) {
                // Elementor's native Utils::generate_random_string() uses dechex(rand()),
                // so valid inserted element IDs can be shorter than seven characters.
                if (!is_array($node) || !is_string($node['id'] ?? null) || !preg_match('/^[a-f0-9]{1,8}$/D',$node['id']) || isset($ids[$node['id']])) { throw new \UnexpectedValueException('Invalid or duplicate element ID.'); }
                $ids[$node['id']] = true;
                if (!isset($node['settings'], $node['elements']) || !(is_array($node['settings']) || is_object($node['settings'])) || !is_array($node['elements'])) { throw new \UnexpectedValueException('Invalid Elementor settings or children.'); }
                if (($node['elType'] ?? '') === 'widget') {
                    if (empty($node['widgetType']) || $node['elements']) { throw new \UnexpectedValueException('Invalid widget.'); }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                    if ($widgets !== null && !isset($widgets[$node['widgetType']])) { throw new \UnexpectedValueException('Target widget unavailable: ' . $node['widgetType']); }
                    if ($node['widgetType'] === 'ace-post-grid') { (new \SuiteEdge\Migrator\Elementor\Posts\GridQuery())->args((array)$node['settings']); }
                    if ($widgets !== null && $node['widgetType'] === 'wp-widget-nav_menu') {
                        $mapping=new \SuiteEdge\Migrator\Import\MenuMapping();$menu=$node['settings']['wp']['nav_menu'] ?? 0;$mapping->target($menu);
                        if (isset($node['settings']['_suiteedge_menu_fingerprint'])) {
                            $expected=$node['settings']['_suiteedge_menu_fingerprint'];
                            if (!is_string($expected) || !hash_equals($expected,$mapping->fingerprint($menu))) { throw new \UnexpectedValueException('Source menu design: destination menu changed since analysis. Analyze again to match icons to its current links and hierarchy.'); }
                        }
                    }
                } elseif (($node['elType'] ?? '') !== 'container') { throw new \UnexpectedValueException('Unsupported element type.'); }
                if ($widgets !== null) {
                    $element = ($node['elType'] === 'widget') ? $widgets[$node['widgetType']] : $containers;
                    if (!$element) { throw new \UnexpectedValueException('Elementor Flexbox Containers are unavailable.'); }
                    $stack = $element->get_stack(); $controls = array_merge($stack['controls'],$stack['style_controls'] ?? []);
                    if ($node['elType'] === 'widget') {
                        foreach (['common','common-optimized'] as $common_name) {
                            if (isset($widgets[$common_name])) { $common = $widgets[$common_name]->get_stack(); $controls = array_merge($controls,$common['controls'],$common['style_controls'] ?? []); }
                        }
                    }
                    foreach ((array)$node['settings'] as $key=>$value) {
                        if ($key === '__globals__') {
                            if (!is_array($value)) { throw new \UnexpectedValueException('Invalid global style references.'); }
                            foreach ($value as $control=>$reference) {
                                if (!is_string($control) || !isset($controls[$control]) || !is_string($reference) || ($reference !== '' && !preg_match('~^globals/(?:colors|typography)\?id=[a-zA-Z0-9_-]+$~D',$reference))) { throw new \UnexpectedValueException('Invalid global style reference.'); }
                            }
                            continue;
                        }
                        if (strpos($key,'_suiteedge_') === 0 || $key === '__dynamic__') { continue; }
                        $base = preg_replace('/_(tablet|mobile)$/','',$key);
                        $control = $controls[$key] ?? null;
                        if (!$control && $base !== $key && isset($controls[$base]) && (!empty($controls[$base]['is_responsive']) || isset($controls[$base]['responsive']))) { $control = $controls[$base]; }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                        if (!$control) { throw new \UnexpectedValueException('Target control unavailable: ' . ($node['widgetType'] ?? 'container') . '.' . $key); }
                        $this->control_value($control,$value,($node['widgetType'] ?? 'container').'.'.$key);
                    }
                }
                $walk($node['elements'],$depth + 1);
            }
        };
        $walk($data); return true;
    }
    private function control_value(array $control,$value,$name) {
        $type = $control['type'] ?? '';
        // Elementor's frontend optimization removes UI options/units from runtime stacks.
        if (!isset($control['options']) && $type === 'choose' && in_array($control['name'] ?? '',['align','text_align'],true)) { $control['options'] = array_fill_keys(['left','center','right','justify','start','end'],true); }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
        $fail = function () use ($name) { throw new \UnexpectedValueException('Invalid target control value: '.$name); };
        if ($type === 'slider') {
            if (!is_array($value) || !preg_match('/^(px|%|em|rem|vh|vw|deg)$/D',$value['unit'] ?? '') || !array_key_exists('size',$value) || ($value['size'] !== '' && (!is_numeric($value['size']) || !is_finite((float)$value['size'])))) { $fail(); }
            if (isset($control['size_units']) && !in_array($value['unit'],$control['size_units'],true)) { $fail(); }
        } elseif ($type === 'dimensions') {
            if (!is_array($value) || !is_string($value['unit'] ?? null)) { $fail(); }
            foreach (['top','right','bottom','left'] as $side) { if (!array_key_exists($side,$value) || ($value[$side] !== '' && (!is_numeric($value[$side]) || !is_finite((float)$value[$side])))) { $fail(); } }
            if (isset($control['size_units']) && !in_array($value['unit'],$control['size_units'],true)) { $fail(); }
        } elseif ($type === 'url') {
            if (!is_array($value) || !is_string($value['url'] ?? null)) { $fail(); }
            if (preg_match('/^\s*([a-z][a-z0-9+.-]*):/i',$value['url'],$m) && !in_array(strtolower($m[1]),['http','https','mailto','tel'],true)) { $fail(); }
        } elseif ($type === 'media') {
            if (!is_array($value) || !is_string($value['url'] ?? null) || (isset($value['id']) && $value['id'] !== '' && (!is_numeric($value['id']) || (int)$value['id'] < 0))) { $fail(); }
        } elseif (in_array($type,['select','choose'],true) && isset($control['options']) && $value !== '') {
            if (!is_scalar($value)) { $fail(); }
            $option = (string)$value;
            // Elementor accepts legacy values (e.g. image left/right) through this dictionary.
            // Validate the mapped option without changing the stored physical alignment.
            if (!array_key_exists($option,$control['options']) && isset($control['selectors_dictionary'][$option])) {
                $mapped = $control['selectors_dictionary'][$option];
                if (!is_scalar($mapped)) { $fail(); }
                $option = (string)$mapped;
            }
            if (!array_key_exists($option,$control['options'])) { $fail(); }
        } elseif ($type === 'gallery') {
            if (!is_array($value) || count($value)>500) { $fail(); }
            foreach ($value as $image) {
                if (!is_array($image)) { $fail(); }
                $id=$image['id']??null;
                if (!(is_int($id)||is_string($id))||!preg_match('/^[1-9][0-9]{0,9}$/D',(string)$id)||get_post_type((int)$id)!=='attachment'||!in_array(get_post_status((int)$id),['inherit','publish'],true)) { $fail(); }
            }
        } elseif ($type === 'repeater') {
            if (!is_array($value)) { $fail(); } $ids = [];
            foreach ($value as $row) {
                if (!is_array($row) || !is_string($row['_id'] ?? null) || !preg_match('/^[a-f0-9]{1,8}$/D',$row['_id']) || isset($ids[$row['_id']])) { $fail(); }
                $ids[$row['_id']] = true;
                foreach ($control['fields'] ?? [] as $field) { if (isset($row[$field['name']])) { $this->control_value($field,$row[$field['name']],$name.'.'.$field['name']); } }
            }
        }
    }
}
