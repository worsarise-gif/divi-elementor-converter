<?php
namespace SuiteEdge\Migrator\Mapping;
class AttributeCoverage {
    public function unknown(array $attributes) {
        $known = ['module_id','module_class','admin_label','_builder_version','_module_preset','global_colors_info','type','src','url','url_new_window','alt','title_text','title','image','use_icon','author','job_title','company_name','portrait_url','button_text','button_url','submit_button_text','success_message','gallery_ids','gallery_columns','divider_style','image_src','number','percent_sign','address','zoom_level','social_network','subtitle','currency','sum','per','disabled_on','z_index','positioning','position_type','background_color__hover','button_text_color__hover'];
        $translated = '/^(custom_padding|custom_margin|width|min_height|horizontal_offset|vertical_offset|text_font|text_font_size|text_letter_spacing|text_line_height|text_text_color|text_orientation|button_font|button_font_size|button_letter_spacing|button_line_height|button_text_color|button_orientation|background_color|background_image|background_size|background_position|background_repeat|background_video_mp4|use_background_color_gradient|background_color_gradient_start|background_color_gradient_end|background_color_gradient_type|background_color_gradient_direction|border_radii|border_width_all|border_color_all|border_style_all|box_shadow_style|box_shadow_horizontal|box_shadow_vertical|box_shadow_blur|box_shadow_spread|box_shadow_color|box_shadow_position|filter_blur|filter_brightness|filter_contrast|filter_saturate|filter_hue_rotate|custom_css_main|custom_css_before|custom_css_after|transform_translate|transform_rotate|transform_scale|transform_skew)(_tablet|_phone)?$/';
        $unknown = [];
        $additional = '/^(?:header(?:_[2-6])?_(?:font|font_size|letter_spacing|line_height|text_color|text_align)|custom_css_main_element|height|max_width|module_alignment|button_alignment|button_text_size|align)(?:_tablet|_phone)?$/';
        $handled = ['fb_built','locked','column_structure','use_custom_gutter','gutter_width','make_equal','custom_button','button_use_icon','button_bg_color','button_bg_color__hover','button_bg_enable_color__hover','button_border_width','button_border_color','button_border_radius','font_icon','icon_color','icon_color__hover','icon_width','link_option_url','_dynamic_attributes','background_enable_color','background_enable_image','background_enable_video_mp4','background_color_gradient_stops','background_color_gradient_overlays_image','background_image_width','link_text_color','disabled','preset'];
        foreach (array_keys($attributes) as $key) {
            if ($key==='theme_builder_area' && $attributes[$key]==='post_content') { continue; }
            if ($key==='custom_padding__hover' && preg_match('/^\|{3}(?:\|(?:true|false|on|off)){0,2}$/D',(string)$attributes[$key])) { continue; }
            if (preg_match('/^(?:(?:top|bottom)_divider_(?:style|color|height|flip)(?:_tablet|_phone)?|content_(?:tablet|phone)|image_icon_(?:width|custom_margin|custom_padding)|body_text_align|play_icon_color(?:__hover)?|icon_font_size|use_icon_font_size|thumbnail_overlay_color|box_shadow_color__hover|transform_scale__hover|transform_skew_linked|background_enable_color__hover)$/',$key)) { continue; }
            if ($key === 'animation' && $attributes[$key] === 'off') { continue; }
            if (preg_match('/^animation_(?:direction|duration|delay|intensity|starting_opacity|repeat)$/D',$key) && in_array($attributes['animation'] ?? 'off',['off','none',''],true)) { continue; }
            if (preg_match('/(_last_edited|__hover_enabled)$/',$key)) { continue; }
            if (!in_array($key,$known,true) && !in_array($key,$handled,true) && !preg_match($translated,$key) && !preg_match($additional,$key)) { $unknown[] = $key; }
        }
        return $unknown;
    }
}
