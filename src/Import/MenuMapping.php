<?php
namespace SuiteEdge\Migrator\Import;
/** Destination menu IDs are explicit, source-file scoped, never copied from Divi IDs. */
class MenuMapping {
    public function references($content,array $presets = []) {
        $ids = [];
        $walk = function ($nodes) use (&$walk,&$ids,$presets) {
            foreach ($nodes as $node) {
                if ($node['type'] === 'et_pb_menu') {
                    try { $a=(new \SuiteEdge\Migrator\Divi\PresetResolver())->resolve_export($node['type'],$node['attributes'],$presets); }
                    catch (\UnexpectedValueException $e) { $a=$node['attributes']; }
                    $id = $a['menu_id'] ?? '';
                    if ((is_string($id)||is_int($id)) && ctype_digit((string)$id) && (int)$id > 0) { $ids[(string)(int)$id] = true; }
                }
                $walk($node['children']);
            }
        };
        $walk((new \SuiteEdge\Migrator\Divi\ShortcodeTokenizer())->tokenize($content));
        return array_keys($ids);
    }
    public function pending(array $files) {
        $result = [];
        foreach ($files as $file) {
            $id = $file['source_id'];
            if (isset($result[$id])) { continue; }
            $source = (new SourceRepository())->get($id);
            $export = (new DiviExportReader())->read_json($source['json']); $ids = [];
            foreach ($export['layouts'] as $layout) { $ids = array_merge($ids,$this->references($layout['post_content'],$export['presets'])); }
            if ($ids) { $result[$id] = ['name'=>$source['name'],'ids'=>array_values(array_unique($ids))]; }
        }
        return $result;
    }
    public function validate(array $mapping) {
        if (count($mapping) > 100) { throw new \InvalidArgumentException('Too many source menu mappings.'); }
        $result = [];
        foreach ($mapping as $source=>$menus) {
            if (!is_string($source) || !preg_match('/^[a-f0-9-]{36}$/D',$source) || !is_array($menus) || count($menus) > 100) { throw new \InvalidArgumentException('Invalid source menu mapping.'); }
            $seen = [];
            foreach ($menus as $id=>$target) {
                if (!ctype_digit((string)$id) || (int)$id < 1 || !(is_int($target) || is_string($target)) || !ctype_digit((string)$target)) { throw new \InvalidArgumentException('Invalid destination menu mapping.'); }
                if (isset($seen[(int)$id])) { throw new \InvalidArgumentException('Duplicate source menu mapping.'); }
                $seen[(int)$id] = true;
                if ((int)$target === 0) { continue; }
                $this->target((int)$target); $result[$source][(int)$id] = (int)$target;
            }
        }
        return $result;
    }
    public function fingerprint($id) {
        $id=$this->target($id);$menu=wp_get_nav_menu_object($id);$items=[];
        foreach (wp_get_nav_menu_items($id) as $item) { $items[]=[(int)$item->ID,(int)$item->menu_item_parent,(int)$item->menu_order,$item->title,$item->url]; }
        return hash('sha256',wp_json_encode([$id,$menu->name,$menu->slug,$items]));
    }
    public function target($id) {
        if (!(is_int($id) || is_string($id)) || !ctype_digit((string)$id) || (int)$id < 1 || !function_exists('wp_get_nav_menu_object')) { throw new \UnexpectedValueException('Choose a destination WordPress menu in Navigation menu mappings. Divi menu IDs do not contain menu links.'); }
        $menu = wp_get_nav_menu_object((int)$id);
        if (!$menu || is_wp_error($menu)) { throw new \UnexpectedValueException('Mapped destination menu no longer exists. Choose the correct menu and analyze again.'); }
        $items = wp_get_nav_menu_items($menu->term_id);
        if (!$items || count($items) > 1000) { throw new \UnexpectedValueException('Destination menu must contain between 1 and 1000 valid links.'); }
        $parents = [];
        foreach ($items as $item) {
            if (!empty($item->_invalid) || !is_string($item->url) || $item->url === '' || preg_match('/[\x00-\x20\x7f]/',$item->url) || (preg_match('/^([a-z][a-z0-9+.-]*):/i',$item->url,$m) && !in_array(strtolower($m[1]),['http','https','mailto','tel'],true)) || (!preg_match('~^(?:https?://|mailto:|tel:|/(?!/)|#)~i',$item->url))) { throw new \UnexpectedValueException('Destination menu contains an invalid or unsafe link. Fix it in Appearance > Menus before conversion.'); }
            $parents[(int)$item->ID] = (int)$item->menu_item_parent;
        }
        foreach ($parents as $id=>$parent) {
            $seen = [$id=>true]; $depth = 0;
            while ($parent) {
                if (!isset($parents[$parent]) || isset($seen[$parent]) || ++$depth > 30) { throw new \UnexpectedValueException('Destination menu hierarchy is invalid. Fix its parent relationships before conversion.'); }
                $seen[$parent] = true; $parent = $parents[$parent];
            }
        }
        return (int)$menu->term_id;
    }
}
