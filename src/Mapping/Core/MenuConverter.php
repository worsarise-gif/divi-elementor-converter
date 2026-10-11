<?php
namespace SuiteEdge\Migrator\Mapping\Core;
use SuiteEdge\Migrator\Model\Node;
/** Native Elementor WordPress menu widget, plus a native image when the menu has a logo. */
class MenuConverter extends \SuiteEdge\Migrator\Mapping\BaseConverter {
    protected $force_consume_children = true;
    protected function get_target_type() { return 'container'; }
    protected function map_content(Node $node,array $raw) {
        $a = $raw['attributes'];
        if (!empty($raw['children']) || trim($raw['raw_content']) !== '') { throw new \UnexpectedValueException('Nested menu content requires review.'); }
        foreach (['show_cart_icon','show_search_icon','menu_style'] as $key) {
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            if (!empty($a[$key]) && !in_array($a[$key],['off','left_aligned'],true)) { throw new \UnexpectedValueException('Unsupported active menu option '.$key.'.'); }
        }
        $node->content = ['content_width'=>'full','flex_direction'=>'row','flex_align_items'=>'center','flex_wrap'=>'nowrap','flex_gap'=>['unit'=>'px','size'=>0,'column'=>'0','row'=>'0','isLinked'=>true],'padding'=>['unit'=>'px','top'=>'0','right'=>'0','bottom'=>'0','left'=>'0','isLinked'=>true]];
        $node->advanced['css_classes'][] = 'suiteedge-menu-module';
        $node->content['z_index'] = 3;
        // Divi block modules shrink around horizontal margins; Elementor containers default to 100%.
        if (!isset($a['width'])) { $node->advanced['scoped_css'][]=['selector'=>'{{WRAPPER}}','css'=>'width:auto;align-self:stretch;'.(!isset($a['max_width'])?'max-width:100%;':''),'device'=>'desktop']; }
        $node->advanced['scoped_css'][]=['selector'=>'{{WRAPPER}}:not(.suiteedge-navigation-initialized)','css'=>'flex-wrap:wrap;','device'=>'tablet','max_width'=>980];
        $node->advanced['scoped_css'][]=['selector'=>'{{WRAPPER}}:not(.suiteedge-navigation-initialized) .suiteedge-navigation','css'=>'flex-basis:100%;min-width:0;','device'=>'tablet','max_width'=>980];
        if (!empty($a['logo'])) {
            $logo = (new ImageConverter())->convert(['type'=>'et_pb_image','attributes'=>['src'=>$a['logo'],'url'=>$a['logo_url'] ?? '','url_new_window'=>$a['logo_url_new_window'] ?? 'off','alt'=>$a['logo_alt'] ?? '','align'=>'left'],'children'=>[],'text_content'=>'','raw_content'=>'','original_shortcode'=>'']);
            if (!array_key_exists('logo_alt',$a)) { unset($logo->content['_suiteedge_alt'],$logo->content['image']['alt']); }
            $max = (new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->slider($a['logo_max_width'] ?? '100%');
            if ($max['size'] < 0) { throw new \UnexpectedValueException('Invalid menu logo width.'); }
            $logo->advanced['css_classes'][] = 'suiteedge-menu-logo';
            $logo->styles->set('width',$max);
            $logo->content['width'] = $max;
            // Match Elementor container-widget specificity without a global override.
            $logo->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}}.elementor-widget.elementor-widget','css'=>'flex:0 1 auto;max-width:'.$max['size'].$max['unit'].';','device'=>'desktop'];
            $node->add_child($logo);
        }
        $menu = new Node('et_pb_menu','wp-widget-nav_menu');
        $menu->content = ['wp'=>['title'=>'','nav_menu'=>$raw['_target_menu_id']]];
        $menu->advanced['css_classes'][] = 'suiteedge-navigation';
        $menu->advanced['portable_effects']['navigation'] = ['breakpoint'=>980,'label'=>wp_get_nav_menu_object($raw['_target_menu_id'])->name];
        (new \SuiteEdge\Migrator\Style\MenuTranslator())->translate($menu,$a,$raw['_source_theme']);
        if (!empty($raw['_menu_presentation']['icons'])) {
            $menu->content['_suiteedge_menu_images']=$raw['_menu_presentation']['icons'];
            $menu->content['_suiteedge_menu_fingerprint']=$raw['_menu_presentation']['menu_fingerprint'];
            foreach ($raw['_menu_presentation']['icons'] as $icon) {
                $selector='{{WRAPPER}} .menu-item-'.(int)$icon['item_id'].'>a';
                $menu->advanced['scoped_css'][]=['selector'=>$selector,'css'=>'display:flex;align-items:center;','device'=>'desktop'];
                $menu->advanced['scoped_css'][]=['selector'=>$selector.'::before','css'=>'content:"";display:inline-block;flex:0 0 '.(int)$icon['width'].'px;width:'.(int)$icon['width'].'px;height:'.(int)$icon['width'].'px;margin-right:5px;background:url('.json_encode($icon['url'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG).') center/contain no-repeat;','device'=>'desktop'];
            }
            $node->advanced['information'][]='Source menu-label image icons restored with portable scoped CSS; destination menu links and labels were retained.';
        }
        $node->add_child($menu);
        $node->advanced['handled_attributes'] = array_merge($menu->advanced['handled_attributes'],['menu_id','logo','logo_url','logo_alt','logo_max_width','logo_url_new_window','show_cart_icon','show_search_icon','menu_style','cart_icon_color','search_icon_color']);
        $node->advanced['information'][] = 'Menu uses the explicitly mapped destination WordPress menu. Edit its links in Appearance > Menus; styles and responsive controls are portable.';
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) {
            if (!empty($a['custom_css_menu_logo'.$suffix])) {
                $css = str_replace('||',';',$a['custom_css_menu_logo'.$suffix]);
                if (preg_match('/[<>{}@]/',$css)) { throw new \UnexpectedValueException('Menu logo custom CSS requires review.'); }
                $node->advanced['scoped_css'][] = ['selector'=>'{{WRAPPER}} .suiteedge-menu-logo','css'=>$css,'device'=>$device];
                $node->advanced['handled_attributes'][] = 'custom_css_menu_logo'.$suffix;
            }
        }
    }
}
