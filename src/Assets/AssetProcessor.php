<?php
namespace SuiteEdge\Migrator\Assets;
class AssetProcessor {
    private $menu_urls=[];
    private $attachment_links=[];
    public function process(array &$data,$id,array $settings,array &$warnings,array $embedded = [],array &$information = []) {
        $this->menu_urls=[];
        $this->attachment_links=[];
        $this->collect_attachment_links($data);
        $this->menu_images($data,$id,$settings,$warnings,$information);
        $this->elements($data,$id,$settings,$warnings,$embedded,$information);
    }
    private function collect_attachment_links(array $data) {
        foreach ($data as $element) {
            foreach ((array)($element['settings']['_suiteedge_attachment_links'] ?? []) as $url) { if (is_string($url)) { $this->attachment_links[$url] = true; } }
            $this->collect_attachment_links($element['elements']);
        }
    }
    private function menu_images(array &$data,$id,array $settings,array &$warnings,array &$information) {
        foreach ($data as &$element) {
            if (is_array($element['settings']['_suiteedge_menu_images'] ?? null)) {
                foreach ($element['settings']['_suiteedge_menu_images'] as &$image) {
                    $url=$image['url'];
                    if (!isset($this->menu_urls[$url])) {
                        $resolved=(new AssetResolver())->resolve_image($url,$id,['reuse_library'=>in_array('reuse_library',$settings['assets']??[],true),'import_external'=>in_array('import_external',$settings['assets']??[],true)]);
                        $this->menu_urls[$url]=$resolved['url'];
                        if (empty($resolved['id'])) {$warnings[]='Source menu icon retained remotely; enable image import/reuse and check the image URL: '.$url;}
                    }
                    $image['url']=$this->menu_urls[$url];
                }unset($image);
                $information[]='Source menu icons were prepared without changing the shared WordPress menu.';
            }
            $this->menu_images($element['elements'],$id,$settings,$warnings,$information);
        }unset($element);
    }
    private function elements(array &$data,$id,array $settings,array &$warnings,array $embedded,array &$information) {
        $assets = $settings['assets'] ?? [];
        $options = ['reuse_library'=>in_array('reuse_library',$assets,true),'import_external'=>in_array('import_external',$assets,true)];
        foreach ($data as &$element) {
            $s = (array)$element['settings'];
            if (is_array($s['tabs'] ?? null)) {
                foreach ($s['tabs'] as &$tab) {
                    if (!is_string($tab['tab_content'] ?? null)) { continue; }
                    $inline = [['settings'=>['editor'=>$tab['tab_content']],'elements'=>[]]];
                    $this->elements($inline,$id,$settings,$warnings,$embedded,$information);
                    $tab['tab_content'] = $inline[0]['settings']['editor'];
                } unset($tab);
            }
            $keys = array_filter(array_keys($s),function ($key) { return preg_match('/^(image|testimonial_image|image_overlay|_?background_image(?:_tablet|_mobile)?)$/D',$key); });
            foreach ($keys as $key) {
                if (empty($s[$key]['url']) || !empty($s[$key]['id'])) { continue; }
                $resolved = (new AssetResolver())->resolve_image($s[$key]['url'],$id,$options,$s['_suiteedge_alt'] ?? '',$embedded);
                if (!$resolved['id']) { $warnings[] = 'Image URL retained without a target attachment: ' . $s[$key]['url']; }
                if ($key === 'image' && array_key_exists('_suiteedge_alt',$s)) { $resolved['alt'] = $s['_suiteedge_alt']; }
                $s[$key] = $resolved;
            }
            if (array_key_exists('_suiteedge_alt',$s) && isset($s['image'])) {
                // Elementor ignores image.alt for attachment-backed images. Its native
                // URL format honors per-instance alt, including explicitly decorative images.
                $alt = sanitize_text_field($s['_suiteedge_alt']);
                $s['image']['alt'] = $s['_suiteedge_alt'] = $alt;
                if (!empty($s['image']['id']) && (string)get_post_meta($s['image']['id'],'_wp_attachment_image_alt',true) !== $alt) {
                    $s['_suiteedge_attachment_id'] = (int)$s['image']['id'];
                    $s['image']['url'] = wp_get_attachment_url($s['image']['id']);
                    $s['image']['id'] = '';
                    $information[] = 'Source alt text preserved per image using the local attachment URL; shared Media Library metadata was kept. This image uses native URL rendering without attachment srcset.';
                }
            }
            foreach (['link','button_link'] as $key) {
                if (!empty($s[$key]['url'])) { $s[$key]['url'] = $this->rewrite_link($s[$key]['url'],$settings,$warnings); }
            }
            foreach (['editor','description_text','testimonial_content','html'] as $key) {
                if (empty($s[$key]) || strpos($s[$key],'<') === false) { continue; }
                if ($key==='html' && ($s['_css_classes'] ?? '')==='suiteedge-style-carrier') {
                    foreach ($this->menu_urls as $source=>$local) {
                        $flags=JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG;
                        $s[$key]=str_replace('url('.json_encode($source,$flags).')','url('.json_encode($local,$flags).')',$s[$key]);
                    }
                }
                $tags = new \WP_HTML_Tag_Processor($s[$key]);
                while ($tags->next_tag()) {
                    if ($tags->get_tag() === 'IMG' && is_string($tags->get_attribute('src'))) {
                        $url = $tags->get_attribute('src'); $alt = $tags->get_attribute('alt') ?? '';
                        $image = (new AssetResolver())->resolve_image($url,$id,$options,$alt,$embedded);
                        if ($image['id']) { $tags->set_attribute('src',$image['url']); $tags->remove_attribute('srcset'); }
                        elseif ($options['import_external']) { $warnings[] = 'Inline image could not be imported: '.$url; }
                    }
                    if ($tags->get_tag() === 'A' && is_string($tags->get_attribute('href'))) { $tags->set_attribute('href',$this->rewrite_link($tags->get_attribute('href'),$settings,$warnings)); }
                }
                $s[$key] = $tags->get_updated_html();
            }
            $element['settings'] = $s ?: new \stdClass();
            $this->elements($element['elements'],$id,$settings,$warnings,$embedded,$information);
        }
    }
    private function rewrite_link($url,array $settings,array &$warnings) {
        if (isset($this->attachment_links[$url])) { return $url; }
        if (empty($settings['source_origin'])) { return $url; }
        $candidate = (new UrlRewriter())->rewrite($url,$settings['source_origin'],home_url());
        if ($candidate === $url) { return $url; }
        if (url_to_postid($candidate) || rtrim(strtok($candidate,'?#'),'/') === rtrim(home_url(),'/')) { return $candidate; }
        $warnings[] = 'Internal link kept on source site because no target page resolves: '.$url;
        return $url;
    }
}
