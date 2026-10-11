<?php
namespace SuiteEdge\Migrator\Mapping\Core;
class AccordionConverter extends \SuiteEdge\Migrator\Mapping\CommonConverter {
    protected function get_target_type() { return 'accordion'; }
    protected function map_content(\SuiteEdge\Migrator\Model\Node $node,array $raw) {
        parent::map_content($node,$raw);
        $a = $raw['attributes']; $levels = [];
        foreach (array_keys($a) as $key) {
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            if (preg_match('/^(?:header_|text_|body_|toggle_|open_toggle_|closed_toggle_)/',$key) && !in_array($key,['toggle_level','toggle_icon'],true) && !(new \SuiteEdge\Migrator\Style\AccordionTranslator())->supports($key) && !preg_match('/(?:_last_edited|__hover_enabled)$/D',$key)) { throw new \UnexpectedValueException('Unsupported accordion setting: '.$key.'.'); }
        }
        if (!$raw['children']) { throw new \UnexpectedValueException('Accordion has no items.'); }
        $remainder = $raw['raw_content'];
        foreach ($raw['children'] as $child) { $remainder = str_replace($child['original_shortcode'],'',$remainder); }
        if (trim($remainder) !== '') { throw new \UnexpectedValueException('Accordion contains content outside its items; review it before applying.'); }
        foreach ($raw['children'] as $i=>$child) {
            $ca = $child['attributes'];
            $extra = array_diff(array_keys($ca),['title','open','toggle_level','_builder_version','_module_preset','preset','global_colors_info','admin_label']);
            $extra = array_filter($extra,function ($key) { return !(new \SuiteEdge\Migrator\Style\AccordionTranslator())->supports($key) && !preg_match('/(?:_last_edited|__hover_enabled)$/D',$key); });
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            if ($extra) { throw new \UnexpectedValueException('Accordion item settings require review: '.implode(', ',$extra).'.'); }
            $level = $ca['toggle_level'] ?? $a['toggle_level'] ?? 'h5';
            if (!in_array($level,['h1','h2','h3','h4','h5','h6'],true)) { throw new \UnexpectedValueException('Unsupported accordion title level.'); }
            $levels[] = $level;
            $open = $ca['open'] ?? ($i === 0 ? 'on' : 'off');
            if (!in_array($open,['on','off'],true)) { throw new \UnexpectedValueException('Invalid accordion open state.'); }
            if ($open === 'on') { $opened[] = $i; }
        }
        if (count(array_unique($levels)) !== 1) { throw new \UnexpectedValueException('Mixed accordion title levels require review.'); }
        if (count($opened ?? []) > 1) { throw new \UnexpectedValueException('An accordion can open only one item initially.'); }
        if (($opened ?? []) !== [0]) { $node->advanced['portable_effects']['accordion'] = ['initial'=>($opened ?? [null])[0]]; }
        $node->content['title_html_tag'] = $levels[0];
        $node->content['icon_align'] = 'right';
        $node->content['selected_icon'] = ['value'=>'fas fa-plus-circle','library'=>'fa-solid'];
        $node->content['selected_active_icon'] = ['value'=>'','library'=>''];
        $profile = $raw['_source_theme'] ?? (new \SuiteEdge\Migrator\Style\SourceThemeProfile())->recommended();
        $size = ['h1'=>30,'h2'=>26,'h3'=>22,'h4'=>18,'h5'=>16,'h6'=>14][$levels[0]];
        $node->content += ['title_color'=>$profile['heading_color'],'tab_active_color'=>$profile['heading_color'],
            'title_typography_typography'=>'custom','title_typography_font_family'=>$profile['heading_font'],'title_typography_font_weight'=>'500','title_typography_font_size'=>['unit'=>'px','size'=>$size],'title_typography_line_height'=>['unit'=>'em','size'=>1],'title_typography_text_decoration'=>'none',
            'content_color'=>$profile['body_color'],'content_typography_typography'=>'custom','content_typography_font_family'=>$profile['body_font'],'content_typography_font_weight'=>'500','content_typography_font_size'=>['unit'=>'px','size'=>$profile['body_size']],'content_typography_line_height'=>['unit'=>'em','size'=>1.7],
            'border_color'=>'#d9d9d9','border_width'=>['unit'=>'px','size'=>1],'title_background'=>'#f4f4f4','content_background_color'=>'#ffffff','icon_color'=>'#cccccc',
            'title_padding'=>['unit'=>'px','top'=>'20','right'=>'70','bottom'=>'20','left'=>'20','isLinked'=>false],
            'content_padding'=>['unit'=>'px','top'=>'20','right'=>'20','bottom'=>'20','left'=>'20','isLinked'=>true]];
        $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}} .elementor-tab-title.elementor-active','css'=>'background:#ffffff;padding-bottom:0;','device'=>'desktop'];
        // Keep source em spacing on the wrapper while eliminating the destination
        // heading line-height strut; native inner-anchor typography stays editable.
        $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}} .elementor-tab-title','css'=>'font-size:'.$profile['body_size'].'px;line-height:0;margin:0;','device'=>'desktop'];
        $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}} .elementor-accordion-icon','css'=>'font-size:16px;line-height:1;','device'=>'desktop'];
        $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}} .elementor-accordion-icon svg','css'=>'width:16px;height:16px;','device'=>'desktop'];
        $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}} .elementor-tab-content p','css'=>'margin:0;padding-bottom:1em;','device'=>'desktop'];
        $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}} .elementor-tab-content p:last-child','css'=>'padding-bottom:0;','device'=>'desktop'];
        if (isset($a['toggle_level'])) { $node->advanced['handled_attributes'][] = 'toggle_level'; }
        if (isset($a['toggle_icon'])) {
            // Bounded legacy glyph equivalents; unknown ETModules glyphs stay guarded.
            $parts = explode('|',$a['toggle_icon']);
            $legacy = [';'=>'chevron-circle-down','3'=>'chevron-down','L'=>'plus','K'=>'minus'];
            if (count($parts) === 5 && $parts[1] === '' && $parts[3] === '' && $parts[4] === '400' && $parts[2] === 'divi' && isset($legacy[html_entity_decode($parts[0],ENT_QUOTES,'UTF-8')])) {
                $icon = ['value'=>'fas fa-'.$legacy[html_entity_decode($parts[0],ENT_QUOTES,'UTF-8')],'library'=>'fa-solid'];
                $node->advanced['information'][] = 'Legacy accordion icon mapped to its native Font Awesome equivalent; review the outline visually.';
            } else { $icon = (new \SuiteEdge\Migrator\Mapping\IconMapper())->map($a['toggle_icon']); }
            $node->content['selected_icon'] = $icon;
            $node->content['selected_active_icon'] = ['value'=>'','library'=>''];
            $node->advanced['handled_attributes'][] = 'toggle_icon';
        }
        (new \SuiteEdge\Migrator\Style\AccordionTranslator())->translate($node,$a,$raw['children']);
    }
}
