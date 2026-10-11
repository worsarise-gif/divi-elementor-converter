<?php
namespace SuiteEdge\Migrator\Elementor\Classic;

/** Compile native controls at Divi 4 breakpoints without changing the destination kit. */
class ResponsiveCss {
    public function compile(array $data,$scope) {
        if (!class_exists('Elementor\\Core\\Files\\CSS\\Post') || !isset(\Elementor\Plugin::$instance->elements_manager)) { return ''; }
        $settings = $data['settings'];
        $names = [];
        foreach ($settings as $key=>$value) {
            if (preg_match('/^(.*)_(tablet|mobile)$/D',$key,$match)) { $names[$match[1]] = true; }
        }
        if (!$names) { return ''; }
        $element = \Elementor\Plugin::$instance->elements_manager->create_element_instance($data);
        if (!$element) { throw new \UnexpectedValueException('The destination cannot compile responsive source styles.'); }
        $controls = $element->get_controls();
        $selected = array_intersect_key($controls,$names);
        $selected = array_filter($selected,function($control) { return !empty($control['selectors']) && (!empty($control['is_responsive']) || array_key_exists('responsive',$control)); });
        if (!$selected) { return ''; }
        $values = $element->get_settings();
        $frames = [];
        foreach (['desktop','tablet','mobile'] as $device) {
            if ($device !== 'desktop') {
                foreach ($settings as $key=>$value) {
                    if (substr($key,-strlen('_'.$device)) === '_'.$device && $value !== '') { $values[substr($key,0,-strlen('_'.$device))] = $value; }
                }
            }
            // This is an in-memory renderer. No post, kit, file or metadata is written.
            $file = new \Elementor\Core\Files\CSS\Post(0);
            foreach ($selected as $name=>$control) {
                if (!$element->is_control_visible($control,$values,$controls)) { continue; }
                $object = \Elementor\Plugin::$instance->controls_manager->get_control($control['type']);
                $control = array_merge($object->get_settings(),$control);
                $control['responsive'] = [];
                $selectors = [];
                foreach ($control['selectors'] as $selector=>$declaration) {
                    $selectors[preg_replace('/^(?:\([a-z_+]+\)){1,2}/i','',$selector)] = $declaration;
                }
                $control['selectors'] = $selectors;
                $file->add_control_rules($control,$controls,function($control) use ($values) { return $values[$control['name']] ?? null; },['{{WRAPPER}}','{{ID}}'],['.elementor-element.'.$scope.'.elementor-element',$data['id']],$values);
            }
            $frames[$device] = $file->get_stylesheet()->get_rules('all') ?? [];
        }
        // Reset missing desktop declarations, otherwise Elementor's wider tablet
        // query can leak a tablet-only value into the source desktop range.
        $properties = [];
        foreach ($frames as $rules) {
            foreach ($rules as $selector=>$declarations) {
                foreach ($declarations as $property=>$value) { $properties[$selector][$property] = true; }
            }
        }
        $css = '';
        foreach (['desktop'=>'(min-width:981px)','tablet'=>'(min-width:768px) and (max-width:980px)','mobile'=>'(max-width:767px)'] as $device=>$query) {
            $rules = [];
            foreach ($properties as $selector=>$declarations) {
                foreach ($declarations as $property=>$unused) {
                    $rules[$selector][$property] = $frames[$device][$selector][$property] ?? $frames[$device === 'mobile' ? 'tablet' : 'desktop'][$selector][$property] ?? $frames['desktop'][$selector][$property] ?? 'unset';
                }
            }
            $body = \Elementor\Stylesheet::parse_rules($rules);
            if ($body !== '') { $css .= '@media'.$query.'{'.$body.'}'; }
        }
        return $css;
    }
}
