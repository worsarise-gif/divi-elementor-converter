<?php
namespace SuiteEdge\Migrator\Elementor\Classic;
use SuiteEdge\Migrator\Model\Document;
use SuiteEdge\Migrator\Model\Node;
class Serializer {
    private $index = 0;
    private $css = [];
    private $effects = [];
    private $fonts = [];
    private $namespace = '';
    public function serialize(Document $document) {
        $this->index = 0; $this->css = []; $this->effects = []; $this->fonts = []; $data = [];
        $this->namespace = hash('sha256',serialize($document->to_array()));
        foreach ($document->nodes as $node) { $data[] = $this->serialize_node($node); }
        if ($this->css || $this->effects || $this->fonts) {
            $css = implode("\n",$this->css);
            $carrier = ['id'=>(new \SuiteEdge\Migrator\Elementor\IdGenerator())->generate(++$this->index,'scoped-css:'.$this->namespace),'elType'=>'widget','widgetType'=>'html','isInner'=>false,'settings'=>['html'=>'<style>'.$css.'</style>','_css_classes'=>'suiteedge-style-carrier','_title'=>'Divi conversion styles'],'elements'=>[]];
            // Elementor's editor imports root containers. A root HTML widget can be discarded.
            // Keep the stylesheet in a valid container and give it no layout footprint.
            $carrier['settings']['html'] = '<style>.elementor-element.suiteedge-style-carrier,.elementor-element.suiteedge-font-carrier{display:none!important;}'.$css.'</style>';
            if ($this->effects) {
                if (array_filter($this->effects,function ($definition) { return !empty($definition['effects']['navigation']); })) {
                    $navigation = file_get_contents(dirname(__DIR__,3).'/assets/js/portable-navigation.js');
                    if ($navigation === false) { throw new \RuntimeException('Portable navigation runtime is missing. Reinstall the complete plugin package.'); }
                    $carrier['settings']['html'] .= '<script>'.$navigation.'('.json_encode($this->effects,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_THROW_ON_ERROR).');</script>';
                }
                $runtime = file_get_contents(dirname(__DIR__,3).'/assets/js/portable-effects.js');
                if ($runtime === false) { throw new \RuntimeException('Portable effects runtime is missing. Reinstall the complete plugin package.'); }
                $carrier['settings']['html'] .= '<script>'.$runtime.'('.json_encode($this->effects,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_THROW_ON_ERROR).');</script>';
            }
            // Native FONT controls let Elementor load Google, early-access and registered custom
            // fonts even when their visible styling belongs to a WordPress menu or scoped selector.
            // These controls survive template insertion and work without the migrator activated.
            $carriers = [];
            foreach (array_keys($this->fonts) as $family) {
                $carriers[] = ['id'=>(new \SuiteEdge\Migrator\Elementor\IdGenerator())->generate(++$this->index,'font:'.$family.':'.$this->namespace),'elType'=>'widget','widgetType'=>'text-editor','isInner'=>false,'settings'=>['editor'=>'','typography_typography'=>'custom','typography_font_family'=>$family,'_css_classes'=>'suiteedge-font-carrier','_title'=>'Source font: '.$family],'elements'=>[]];
            }
            $carriers[] = $carrier;
            if ($data[0]['elType'] === 'container') { $data[0]['elements'] = array_merge($data[0]['elements'],$carriers); }
            else {
                $data[] = ['id'=>(new \SuiteEdge\Migrator\Elementor\IdGenerator())->generate(++$this->index,'style-container:'.$this->namespace),'elType'=>'container','isInner'=>false,'settings'=>['css_classes'=>'suiteedge-style-carrier','content_width'=>'full'],'elements'=>$carriers];
            }
        }
        return $data;
    }
    private function serialize_node(Node $node) {
        foreach (\SuiteEdge\Migrator\Style\FontFamily::in_node($node,true) as $family) { $this->fonts[$family] = true; }
        if ($node->type === 'ace-post-grid') { foreach (\SuiteEdge\Migrator\Style\FontFamily::in_node($node) as $family) { $this->fonts[$family] = true; } }
        $id = (new \SuiteEdge\Migrator\Elementor\IdGenerator())->generate(++$this->index,$node->source_type.':'.$this->namespace);
        // Template insertion regenerates element IDs, but preserves custom classes.
        $scope = 'suiteedge-'.substr($this->namespace,0,12).'-'.$this->index;
        $effects = $node->advanced['portable_effects'] ?? [];
        if ($effects) {
            $breakpoints = ['tablet'=>980,'mobile'=>767];
            $this->effects[] = ['scope'=>$scope,'effects'=>$effects,'breakpoints'=>$breakpoints];
        }
        $rules = array_merge($node->advanced['layout_css'] ?? [],$node->advanced['scoped_css'] ?? []);
        $rule_index = count($this->css);
        foreach ($rules as $rule) {
            $selector = str_replace('{{WRAPPER}}','.elementor-element.'.$scope,$rule['selector']);
            $css = $selector.'{'.$rule['css'].'}';
            $conditions = [];
            if (isset($rule['min_width'])) { $conditions[] = '(min-width:'.(int)$rule['min_width'].'px)'; }
            if ($rule['device'] !== 'desktop' || isset($rule['max_width'])) {
                $size = $rule['device'] === 'mobile' ? 767 : 980;
                $conditions[] = '(max-width:'.(int)($rule['max_width'] ?? $size).'px)';
            }
            if ($conditions) { $css = '@media'.implode(' and ',$conditions).'{'.$css.'}'; }
            $this->css[] = $css;
        }
        $styles = $node->styles->to_elementor();
        if ($node->type !== 'container') {
            foreach (array_keys($styles) as $key) {
                if ($node->type === 'button' && preg_match('/^padding(_tablet|_mobile)?$/',$key,$m)) { $styles['text_padding'.($m[1] ?? '')] = $styles[$key]; unset($styles[$key]); }
                elseif (preg_match('/^(padding|margin|background_|border_|box_shadow_|z_index)/',$key) && !($node->type === 'button' && preg_match('/^(?:background_color$|border_)/',$key))) { $styles['_' . $key] = $styles[$key]; unset($styles[$key]); }
                if (preg_match('/^width(_tablet|_mobile)?$/',$key,$m)) { $suffix = $m[1] ?? ''; $styles['_element_width'.$suffix] = 'initial'; $styles['_element_custom_width'.$suffix] = $styles[$key]; unset($styles[$key]); }
            }
        }
        $settings = array_merge($node->content, $styles);
        $container = $node->type === 'container';
        $prototype = ['id'=>$id,'elType'=>$container ? 'container' : 'widget','settings'=>$settings,'elements'=>[]];
        if (!$container) { $prototype['widgetType'] = $node->type; }
        $responsive_css = !empty($node->advanced['native_template']) ? '' : (new ResponsiveCss())->compile($prototype,$scope);
        if ($responsive_css !== '') { array_splice($this->css,$rule_index,0,[$responsive_css]); }
        if ($node->advanced['css_id'] !== '') { $settings['_element_id'] = $node->advanced['css_id']; }
        $classes = $node->advanced['css_classes'];
        if ($rules || $effects || $responsive_css !== '') { $classes[] = $scope; }
        if ($classes) { $settings[$node->type === 'container' ? 'css_classes' : '_css_classes'] = implode(' ', $classes); }
        $data = ['id'=>$id, 'elType'=>$container ? 'container' : 'widget', 'isInner'=>false, 'settings'=>$settings ?: new \stdClass(), 'elements'=>[]];
        if ($container) { foreach ($node->children as $child) { $data['elements'][] = $this->serialize_node($child); } }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
        else { $data['widgetType'] = $node->type; if ($node->children) { throw new \UnexpectedValueException('Widget children would be discarded: ' . $node->source_type); } }
        return $data;
    }
}
