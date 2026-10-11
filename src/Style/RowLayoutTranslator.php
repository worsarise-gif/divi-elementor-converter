<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;
/** Divi 4's grid breakpoints differ from Elementor's tablet container defaults. */
class RowLayoutTranslator {
    public function translate(Node $row) {
        if (!in_array($row->source_type,['et_pb_row','et_pb_row_inner'],true)) { return; }
        $gap = (float)($row->content['flex_gap']['column'] ?? 0);
        $count = count($row->children);
        $compact_grid = in_array($count,[4,5,6],true);
        foreach ($row->children as $child) {
            $compact_grid = $compact_grid && ($child->source_trace['attributes']['type'] ?? '') === '1_'.$count;
        }
        $row->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}}.e-con','css'=>'--flex-direction:row;--flex-wrap:nowrap;--column-gap:'.$gap.'%;--row-gap:'.$gap.'%;','device'=>'desktop','min_width'=>981];
        // Divi uses column margins for vertical separation. A fixed row gap would
        // add extra space when authored margin:auto overrides those defaults.
        $row->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}}.e-con','css'=>'--flex-direction:'.($compact_grid ? 'row' : 'column').';--flex-wrap:'.($compact_grid ? 'wrap' : 'nowrap').';--column-gap:0px;--row-gap:0px;','device'=>'tablet','max_width'=>980,'min_width'=>768];
        $row->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}}.e-con','css'=>'--flex-direction:column;--flex-wrap:nowrap;--column-gap:0px;--row-gap:0px;','device'=>'mobile','max_width'=>767];
        foreach ($row->children as $index=>$child) {
            $styles = $child->styles->to_elementor();
            // Divi resets the final column in uniform 4/6-column rows, then the
            // final tablet group. This affects vertical distribution when custom
            // CSS uses margin:auto; desktop and tablet have different last groups.
            // Authored builder margins have priority over this theme default.
            $last_group = $compact_grid && in_array($count,[4,6],true) && $index >= $count/2;
            $explicit_bottom = false;
            foreach (['margin','margin_tablet','margin_mobile'] as $key) {
                $explicit_bottom = $explicit_bottom || (isset($styles[$key]['bottom']) && $styles[$key]['bottom'] !== '');
            }
            if ($last_group && !$explicit_bottom) {
                $rule = ['selector'=>'{{WRAPPER}}.e-con.elementor-element','css'=>'margin-bottom:0px;','device'=>$index === $count-1 ? 'desktop' : 'tablet'];
                if ($index !== $count-1) { $rule['min_width'] = 768; $rule['max_width'] = 980; }
                $child->advanced['layout_css'][] = $rule;
            }
            if (!$explicit_bottom) {
                $tablet_last = $compact_grid ? $index >= $count-($count === 6 ? 3 : ($count === 4 ? 2 : 1)) : $index === $count-1;
                $child->advanced['layout_css'][] = ['selector'=>$tablet_last ? '{{WRAPPER}}.e-con.elementor-element' : '{{WRAPPER}}','css'=>'margin-bottom:'.($tablet_last ? '0' : '30').'px;','device'=>'tablet','min_width'=>768,'max_width'=>980];
                $child->advanced['layout_css'][] = ['selector'=>$compact_grid || $index === $count-1 ? '{{WRAPPER}}.e-con.elementor-element' : '{{WRAPPER}}','css'=>'margin-bottom:'.($index === $count-1 ? '0' : '30').'px;','device'=>'mobile','max_width'=>767];
            }
            $width = $styles['width'] ?? $child->content['width'] ?? null;
            if ($width) {
                $child->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}}.e-con','css'=>'--width:'.$width['size'].$width['unit'].';flex-shrink:0;','device'=>'desktop','min_width'=>981];
            }
            $tablet = $compact_grid ? '47.25%' : '100%';
            if (isset($styles['width_tablet'])) { $tablet = $styles['width_tablet']['size'].$styles['width_tablet']['unit']; }
            $mobile = isset($styles['width_mobile']) ? $styles['width_mobile']['size'].$styles['width_mobile']['unit'] : '100%';
            $gutter = $compact_grid && $index % 2 === 0 && $index !== $count-1 ? '5.5%' : '0px';
            $child->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}}.e-con.elementor-element','css'=>'--width:'.$tablet.';flex-shrink:0;margin-right:'.$gutter.';','device'=>'tablet','max_width'=>980,'min_width'=>768];
            $child->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}}.e-con.elementor-element','css'=>'--width:'.$mobile.';flex-shrink:0;margin-right:0px;','device'=>'mobile','max_width'=>767];
            if (!$child->children) {
                // Divi retains spacer columns on desktop, but hides them below its grid breakpoint.
                $child->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}}.e-con','css'=>'display:none;','device'=>'tablet','max_width'=>980];
            }
        }
    }
}
