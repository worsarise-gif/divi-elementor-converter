<?php
namespace SuiteEdge\Migrator\Mapping;
use SuiteEdge\Migrator\Model\Node;
/** Shared content mapping; individual classes keep registry extension points. */
abstract class CommonConverter extends BaseConverter {
    protected function link($url, $target = '') { return ['url' => $url, 'is_external' => $target === 'on' ? 'on' : '', 'nofollow' => '']; }
    protected function image($url) { return ['url' => $url, 'id' => '']; }
    protected function map_content(Node $node, array $raw) {
        $a = $raw['attributes'] ?? []; $text = $raw['text_content'] ?? ''; $children = $raw['children'] ?? [];
        switch ($raw['type']) {
            case 'et_pb_section': case 'et_pb_row': case 'et_pb_row_inner': case 'et_pb_column': case 'et_pb_column_inner': case 'et_pb_pricing_tables':
                $node->content = ['content_width' => 'full', 'flex_direction' => in_array($raw['type'], ['et_pb_row', 'et_pb_row_inner', 'et_pb_pricing_tables'], true) ? 'row' : 'column'];
                $node->content['padding'] = ['unit'=>'px','top'=>'0','right'=>'0','bottom'=>'0','left'=>'0','isLinked'=>true];
                $node->content['flex_gap'] = ['unit'=>'px','size'=>0,'column'=>'0','row'=>'0','isLinked'=>true];
                if (strpos($raw['type'], 'column') !== false) {
                    $sizes = ['4_4'=>100,'1_2'=>50,'1_3'=>100/3,'2_3'=>200/3,'1_4'=>25,'3_4'=>75,'1_5'=>20,'2_5'=>40,'3_5'=>60,'4_5'=>80,'1_6'=>100/6,'5_6'=>500/6];
                    $node->content['width'] = ['unit'=>'%', 'size'=>$sizes[$a['type'] ?? '4_4'] ?? 100];
                    $node->content['width_tablet'] = ['unit'=>'%', 'size'=>100];
                    $node->content['width_mobile'] = ['unit'=>'%', 'size'=>100];
                }
                if (strpos($raw['type'], 'row') !== false) {
                    $row_width = (new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->slider($a['width'] ?? '80%','%');
                    $node->content['width'] = $row_width;
                    // Elementor's mobile container width does not inherit the desktop control.
                    $node->content['width_mobile'] = $row_width;
                    $node->content['flex_direction_tablet'] = 'column';
                    $node->content['flex_direction_mobile'] = 'column';
                    $node->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}}','css'=>'margin-left:auto;margin-right:auto;','device'=>'desktop'];
                }
                if (strpos($raw['type'],'row') !== false) {
                    $gap = ['1'=>0,'2'=>3,'3'=>5.5,'4'=>8][$a['gutter_width'] ?? '3'];
                    $node->content['flex_gap'] = ['unit'=>'%','size'=>$gap,'column'=>(string)$gap,'row'=>(string)$gap,'isLinked'=>true];
                    $node->content['flex_gap_tablet'] = ['unit'=>'px','size'=>30,'column'=>'0','row'=>'30','isLinked'=>false];
                    $node->content['flex_gap_mobile'] = ['unit'=>'px','size'=>0,'column'=>'0','row'=>'0','isLinked'=>true];
                    $node->content['flex_align_items'] = ($a['make_equal'] ?? '') === 'on' ? 'stretch' : 'flex-start';
                }
                break;
            case 'et_pb_text':
                $node->content['editor'] = $text;
                $node->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}} h1,{{WRAPPER}} h2,{{WRAPPER}} h3,{{WRAPPER}} h4,{{WRAPPER}} h5,{{WRAPPER}} h6','css'=>'margin:0;padding-bottom:10px;','device'=>'desktop'];
                $node->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}} p','css'=>'margin:0;padding-bottom:1em;','device'=>'desktop'];
                $node->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}} p:last-child','css'=>'padding-bottom:0;','device'=>'desktop'];
                if (isset($a['content_tablet']) || isset($a['content_phone'])) {
                    $tablet = $a['content_tablet'] ?? $text; $mobile = $a['content_phone'] ?? $tablet;
                    $node->content['editor'] = '<div class="suiteedge-content-desktop">'.$text.'</div><div class="suiteedge-content-tablet">'.$tablet.'</div><div class="suiteedge-content-mobile">'.$mobile.'</div>';
                    foreach ([['desktop','.suiteedge-content-tablet,.suiteedge-content-mobile','none'],['tablet','.suiteedge-content-desktop,.suiteedge-content-mobile','none'],['tablet','.suiteedge-content-tablet','block'],['mobile','.suiteedge-content-desktop,.suiteedge-content-tablet','none'],['mobile','.suiteedge-content-mobile','block']] as $rule) {
                        $selectors = array_map(function ($selector) { return '{{WRAPPER}} '.$selector; },explode(',',$rule[1]));
                        $node->advanced['scoped_css'][] = ['selector'=>implode(',',$selectors),'css'=>'display:'.$rule[2].';','device'=>$rule[0]];
                    }
                }
                break;
            case 'et_pb_code': $node->content['html'] = $text; $this->force_consume_children = true; break;
            case 'et_pb_button':
                $node->content = ['text'=>$a['button_text'] ?? '', 'link'=>$this->link($a['button_url'] ?? '', $a['url_new_window'] ?? '')]; break;
            case 'et_pb_icon':
                $node->content = ['selected_icon'=>(new IconMapper())->map($a['font_icon'] ?? ''),'link'=>$this->link($a['url'] ?? $a['link_option_url'] ?? '')];
                if (isset($a['icon_color'])) { $node->content['primary_color'] = $a['icon_color']; }
                if (isset($a['icon_color__hover'])) { $node->content['hover_primary_color'] = $a['icon_color__hover']; }
                if (isset($a['icon_width'])) { $node->content['size'] = (new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->slider($a['icon_width']); }
                if (isset($a['align'])) { $node->content['align'] = $a['align']; }
                break;
            case 'et_pb_image':
                // Divi title_text is an HTML title/tooltip, not a visible caption.
                $node->content = ['image'=>array_merge($this->image($a['src'] ?? ''),['alt'=>$a['alt'] ?? '']), 'image_size'=>'full', 'link_to'=>empty($a['url']) ? 'none' : 'custom', 'link'=>$this->link($a['url'] ?? '', $a['url_new_window'] ?? ''), '_suiteedge_alt'=>$a['alt'] ?? '', '_suiteedge_image_title'=>$a['title_text'] ?? '', 'caption_source'=>'none'];
                // Missing alt inherits library metadata; an explicit empty alt is decorative.
                if (!array_key_exists('alt',$a)) { unset($node->content['_suiteedge_alt'],$node->content['image']['alt']); }
                foreach ([''=>'','_tablet'=>'_tablet','_phone'=>'_mobile'] as $source=>$target) { if (isset($a['align'.$source])) { $node->content['align'.$target] = $a['align'.$source]; } }
                $node->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}}.elementor-widget-image','css'=>'line-height:0;','device'=>'desktop'];
                $node->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}}.elementor-widget-image img','css'=>'vertical-align:bottom;','device'=>'desktop'];
                break;
            case 'et_pb_blurb':
                $node->type = ($a['use_icon'] ?? 'off') === 'on' ? 'icon-box' : 'image-box';
                $description = (new \SuiteEdge\Migrator\Normalize\BlurbContentNormalizer())->normalize($text);
                $node->content = ['title_text'=>$a['title'] ?? '', 'description_text'=>$description, 'link'=>$this->link($a['url'] ?? '', $a['url_new_window'] ?? '')];
                $level = $a['header_level'] ?? 'h4';
                if (!in_array($level,['h1','h2','h3','h4','h5','h6'],true)) { throw new \UnexpectedValueException('Unsupported blurb title heading level.'); }
                $node->content['title_size'] = $level;
                if (isset($a['header_level'])) { $node->advanced['handled_attributes'][] = 'header_level'; }
                if ($node->type === 'image-box') { $node->content['image'] = $this->image($a['image'] ?? ''); }
                else { $node->content['selected_icon'] = (new IconMapper())->map($a['font_icon'] ?? ''); }
                if (!empty($a['link_option_url'])) { $node->content['link'] = $this->link($a['link_option_url']); }
                if ($node->type === 'icon-box') {
                    if (isset($a['icon_color'])) { $node->content['primary_color'] = $a['icon_color']; }
                    if (isset($a['icon_color__hover'])) { $node->content['hover_primary_color'] = $a['icon_color__hover']; }
                    $size = $a['icon_font_size'] ?? $a['image_icon_width'] ?? null;
                    if ($size !== null) { $node->content['icon_size'] = (new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->slider($size); }
                }
                if (isset($a['body_text_align'])) { $node->content['text_align'] = $a['body_text_align']; }
                // Divi blurb headings have ten pixels of spacing independent of line height.
                $node->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}} .elementor-icon-box-title,{{WRAPPER}} .elementor-image-box-title','css'=>'margin:0;padding-bottom:10px;','device'=>'desktop'];
                $node->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}} .elementor-icon-box-description,{{WRAPPER}} .elementor-image-box-description,{{WRAPPER}} .elementor-icon-box-description p,{{WRAPPER}} .elementor-image-box-description p','css'=>'margin:0;','device'=>'desktop'];
                $node->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}} .suiteedge-blurb-paragraph','css'=>'display:block;','device'=>'desktop'];
                $node->advanced['layout_css'][] = ['selector'=>'{{WRAPPER}} .suiteedge-blurb-paragraph:not(:last-child)','css'=>'padding-bottom:1em;','device'=>'desktop'];
                break;
            case 'et_pb_gallery':
                $node->type = 'image-gallery'; $images = [];
                if (empty($raw['_same_site_assets'])) { throw new \UnexpectedValueException('Gallery IDs cannot be trusted across sites; confirm the source site origin or map the exported assets.'); }
                $gallery_ids=$a['gallery_ids']??'';
                if (!is_string($gallery_ids)||strlen($gallery_ids)>6000) { throw new \UnexpectedValueException('Gallery attachment IDs are invalid or exceed the supported limit.'); }
                $gallery_ids=array_map('trim',explode(',',$gallery_ids));
                if (count($gallery_ids)>500) { throw new \UnexpectedValueException('Gallery exceeds 500 attachment references.'); }
                foreach ($gallery_ids as $id) {
                    if (!preg_match('/^[1-9][0-9]{0,9}$/D',$id)) { throw new \UnexpectedValueException('Gallery attachment IDs must be positive whole numbers.'); }
                    $url = function_exists('wp_get_attachment_url') ? wp_get_attachment_url((int)$id) : false;
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                    if (!$url) { throw new \UnexpectedValueException('Gallery attachment ' . $id . ' is not available on the target.'); }
                    $images[] = ['id'=>(int)$id, 'url'=>$url];
                }
                if (!$images) { throw new \UnexpectedValueException('Gallery has no resolvable images.'); }
                $node->content = ['wp_gallery'=>$images, 'gallery_columns'=>(int)($a['gallery_columns'] ?? 4)]; break;
            case 'et_pb_divider': $node->content = ['style'=>$a['divider_style'] ?? 'solid']; break;
            case 'et_pb_video':
                $url = $a['src'] ?? ''; $type = strpos($url, 'youtu') !== false ? 'youtube' : (strpos($url, 'vimeo.com') !== false ? 'vimeo' : 'hosted');
                $node->content = ['video_type'=>$type, $type . '_url'=>$type === 'hosted' ? $this->image($url) : $url];
                if (!empty($a['image_src'])) { $node->content += ['show_image_overlay'=>'yes', 'image_overlay'=>$this->image($a['image_src']),'show_play_icon'=>'yes']; }
                if (isset($a['play_icon_color'])) { $node->content['play_icon_color'] = $a['play_icon_color']; }
                if (($a['use_icon_font_size'] ?? '') === 'on' && isset($a['icon_font_size'])) { $node->content['play_icon_size'] = (new \SuiteEdge\Migrator\Normalize\UnitNormalizer())->slider($a['icon_font_size']); }
                break;
            case 'et_pb_testimonial':
                $node->content = ['testimonial_content'=>$text, 'testimonial_name'=>$a['author'] ?? '', 'testimonial_job'=>trim(($a['job_title'] ?? '') . ' ' . ($a['company_name'] ?? '')), 'testimonial_image'=>$this->image($a['portrait_url'] ?? '')]; break;
            case 'et_pb_tabs': case 'et_pb_accordion': case 'et_pb_toggle':
                $this->force_consume_children = true; $tabs = [];
                foreach ($raw['type'] === 'et_pb_toggle' ? [$raw] : $children as $i=>$child) {
                    $expected = $raw['type'] === 'et_pb_tabs' ? 'et_pb_tab' : ($raw['type'] === 'et_pb_accordion' ? 'et_pb_accordion_item' : 'et_pb_toggle');
                    if ($child['type'] !== $expected || !empty($child['children'])) { throw new \UnexpectedValueException('Nested tab or accordion content requires manual migration.'); }
                    $tabs[] = ['_id'=>substr(hash('sha256', $i . ($child['original_shortcode'] ?? '')),0,7), 'tab_title'=>$child['attributes']['title'] ?? '', 'tab_content'=>$child['text_content'] ?? ''];
                }
                $node->content = ['tabs'=>$tabs]; break;
            case 'et_pb_number_counter': case 'et_pb_circle_counter':
                $node->content = ['title'=>$a['title'] ?? '', 'starting_number'=>0, 'ending_number'=>(float)($a['number'] ?? 0), 'suffix'=>($a['percent_sign'] ?? 'on') === 'on' ? '%' : '']; break;
            case 'et_pb_map': case 'et_pb_map_pin':
                $this->force_consume_children = true;
                if (count($children) > 1) { throw new \UnexpectedValueException('Multiple map pins require manual migration.'); }
                $node->content = ['address'=>$children[0]['attributes']['pin_address'] ?? $a['address'] ?? '', 'zoom'=>['unit'=>'px','size'=>(int)($a['zoom_level'] ?? 10)]]; break;
            case 'et_pb_social_media_follow': case 'et_pb_social_media_follow_network':
                $this->force_consume_children = true; $icons = [];
                foreach ($raw['type'] === 'et_pb_social_media_follow_network' ? [$raw] : $children as $i=>$child) {
                    $attr = $child['attributes']; $network = preg_replace('/[^a-z0-9-]/', '', strtolower($attr['social_network'] ?? 'facebook'));
                    $icons[] = ['_id'=>substr(hash('sha256',(string)$i . $network),0,7), 'social_icon'=>['value'=>'fab fa-' . $network,'library'=>'fa-brands'], 'link'=>$this->link($attr['url'] ?? '',$attr['url_new_window'] ?? '')];
                }
                $node->content = ['social_icon_list'=>$icons]; break;
            case 'et_pb_contact_form':
                $this->force_consume_children = true;
                $node->content = ['form_name'=>$a['title'] ?? 'Divi contact form', 'form_fields'=>(new \SuiteEdge\Migrator\Forms\DiviContactFormParser())->parse_fields($children), 'button_text'=>$a['submit_button_text'] ?? 'Send', 'submit_actions'=>[], 'custom_messages'=>'yes', 'success_message'=>$a['success_message'] ?? 'Thank you.']; break;
            case 'et_pb_pricing_table':
                $features = [];
                // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- Standalone parser fallback; WordPress runtime uses wp_strip_all_tags.
                foreach (preg_split('/\r?\n/', trim(function_exists('wp_strip_all_tags') ? wp_strip_all_tags($text) : strip_tags($text))) as $i=>$line) { if ($line !== '') { $features[] = ['_id'=>substr(hash('sha256',(string)$i.$line),0,7),'item_text'=>ltrim($line,'+-')]; } }
                $node->content = ['heading'=>$a['title'] ?? '', 'sub_heading'=>$a['subtitle'] ?? '', 'currency_symbol'=>'custom', 'currency_symbol_custom'=>$a['currency'] ?? '', 'price'=>$a['sum'] ?? '', 'period'=>$a['per'] ?? '', 'features_list'=>$features, 'button_text'=>$a['button_text'] ?? '', 'link'=>$this->link($a['button_url'] ?? '')]; break;
            case 'et_pb_wc_title': $node->content = []; break;
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            default: throw new \UnexpectedValueException('No content mapper for ' . $raw['type']);
        }
    }
}
