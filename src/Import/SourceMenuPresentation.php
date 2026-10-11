<?php
namespace SuiteEdge\Migrator\Import;
/** Read presentation omitted from Divi portability JSON; never edit a shared menu. */
class SourceMenuPresentation {
    private $cache=[];
    public function read($origin,$target,$region='header') {
        $url=rtrim($origin,'/').'/';$key=$url.'|'.$target.'|'.$region;
        if (isset($this->cache[$key])) { return $this->cache[$key]; }
        if (!is_string($origin) || !preg_match('~^https?://[^/?#]+/?$~D',$origin) || !wp_http_validate_url($url)) { throw new \UnexpectedValueException('Source menu design requires a safe public source origin.'); }
        $response=wp_safe_remote_get($url,['timeout'=>10,'redirection'=>2,'limit_response_size'=>2097152]);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response)!==200) { throw new \UnexpectedValueException('Source menu design could not be read. Header JSON does not include its menu-label icons.'); }
        return $this->cache[$key]=$this->parse(wp_remote_retrieve_body($response),$origin,$target,$region);
    }
    private function path($url,$origin) {
        if ($url==='#') { return '#'; }
        if (!is_string($url) || preg_match('/[<>"\x00-\x20\\\\]/',$url)) { return null; }
        if (substr($url,0,1)==='/' && substr($url,0,2)!=='//') { $url=rtrim($origin,'/').$url; }
        $parts=wp_parse_url($url);$source=wp_parse_url($origin);$home=wp_parse_url(home_url());
        if (!$parts || !in_array(strtolower($parts['scheme']??''),['http','https'],true) || !in_array(strtolower($parts['host']??''),[strtolower($source['host']??''),strtolower($home['host']??'')],true)) { return null; }
        return rtrim($parts['path']??'/','/').(!empty($parts['query'])?'?'.$parts['query']:'').(!empty($parts['fragment'])?'#'.$parts['fragment']:'') ?: '/';
    }
    private function label($value) { return trim(preg_replace('/\s+/u',' ',html_entity_decode(wp_strip_all_tags($value),ENT_QUOTES,'UTF-8'))); }
    public function parse($html,$origin,$target,$region='header') {
        if (!class_exists('DOMDocument') || !is_string($html) || strlen($html)>=2097152 || strpos($html,"\0")!==false) { throw new \UnexpectedValueException('Source menu markup exceeds the supported size or DOM parsing is unavailable.'); }
        (new MenuMapping())->target($target);
        $previous=libxml_use_internal_errors(true);$dom=new \DOMDocument();
        try { if (!$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET|LIBXML_NOERROR|LIBXML_NOWARNING)) { throw new \UnexpectedValueException('Source menu markup could not be parsed.'); } }
        finally {libxml_clear_errors();libxml_use_internal_errors($previous);}
        $xpath=new \DOMXPath($dom);$region=in_array($region,['header','footer'],true)?$region:'header';
        $roots=$xpath->query('//div[contains(@class,"_tb_'.$region.'") and contains(concat(" ",normalize-space(@class)," ")," et_pb_menu ")]//ul[not(ancestor::ul)]');
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
        if (!$roots->length || $roots->length>16) { throw new \UnexpectedValueException('No unambiguous Divi '.$region.' navigation was found on the source homepage.'); }
        $candidates=[];$menu=wp_get_nav_menu_object($target);
        foreach ($roots as $root) {
            $entries=[];$walk=function ($ul,$parents=[]) use (&$walk,&$entries,$xpath,$origin) {
                if (count($parents)>30) {throw new \UnexpectedValueException('Source menu hierarchy exceeds the supported depth.');}
                foreach ($xpath->query('./li',$ul) as $li) {
                    $anchor=$xpath->query('./a',$li)->item(0);if (!$anchor) {continue;}
                    $label=$this->label($anchor->textContent);$path=$this->path($anchor->getAttribute('href'),$origin);if ($path===null || $label==='') {continue;}
                    $entry=['label'=>$label,'path'=>$path,'parents'=>$parents];$images=$xpath->query('.//img',$anchor);
                    if ($images->length===1) {
                        $img=$images->item(0);$src=$img->getAttribute('src');if (substr($src,0,1)==='/'&&substr($src,0,2)!=='//') {$src=rtrim($origin,'/').$src;}
                        $host=wp_parse_url($src,PHP_URL_HOST);$source_host=wp_parse_url($origin,PHP_URL_HOST);
                        if ($host && strcasecmp($host,$source_host)===0 && wp_http_validate_url($src)) {
                            $width=$img->getAttribute('width');$entry['icon']=['url'=>$src,'width'=>ctype_digit($width)?max(8,min(128,(int)$width)):50];
                        }
                    }
                    if (empty($entry['icon']) && ($images->length || $xpath->query('.//svg | .//i[@class]',$anchor)->length)) { $entry['decoration_review']=true; }
                    $entries[]=$entry;if (count($entries)>1000) {throw new \UnexpectedValueException('Source menu has too many items.');}
                    foreach ($xpath->query('./ul',$li) as $child) {$walk($child,array_merge($parents,[$label]));}
                }
            };$walk($root);
            $preferred=preg_match('/^menu-'.preg_quote($menu->slug,'/').'(?:-\d+)?$/D',$root->getAttribute('id'));
            $candidates[hash('sha256',wp_json_encode($entries))]=['entries'=>$entries,'preferred'=>$preferred];
        }
        $preferred=array_filter($candidates,function ($c) {return $c['preferred'];});if ($preferred) {$candidates=$preferred;}
        if (count($candidates)!==1) {throw new \UnexpectedValueException('Source navigation is ambiguous. Import the correct menu icons into Appearance > Menus, or disable source menu design lookup.');}
        $source=reset($candidates)['entries'];$items=wp_get_nav_menu_items($target);$byid=[];foreach ($items as $item) {$byid[$item->ID]=$item;}
        $icons=[];$matched=[];$warnings=[];
        foreach ($items as $item) {
            $parents=[];$parent=(int)$item->menu_item_parent;while ($parent) {$parents[]= $this->label($byid[$parent]->title);$parent=(int)$byid[$parent]->menu_item_parent;}$parents=array_reverse($parents);
            $path=$this->path($item->url,$origin);$title=$this->label($item->title);
            $matches=array_keys(array_filter($source,function ($entry) use ($path,$parents,$title) {return $entry['path']===$path&&$entry['parents']===$parents&&($path!=='#'||$entry['label']===$title);}));
            if (count($matches)!==1) {$warnings[]='Destination menu item "'.$title.'" has no unique source match; its destination content was retained.';continue;}
            $i=$matches[0];$matched[$i]=true;$entry=$source[$i];
            if ($title!==$entry['label']) {$warnings[]='Destination menu label "'.$title.'" differs from source "'.$entry['label'].'"; review Appearance > Menus.';}
            if (!empty($entry['decoration_review'])) {$warnings[]='Source decoration for "'.$entry['label'].'" requires manual review; only a single safe same-origin image icon is supported.';}
            // Existing menu-label images already render natively; avoid a second icon.
            if (!empty($entry['icon']) && stripos($item->title,'<img')===false) {$icons[]=['item_id'=>(int)$item->ID,'url'=>$entry['icon']['url'],'width'=>$entry['icon']['width']];}
        }
        if (count($matched)!==count($source)) {$warnings[]='The destination menu omits '.(count($source)-count($matched)).' source items. Source navigation links are not included in Header JSON; review the mapped menu hierarchy.';}
        return ['icons'=>$icons,'warnings'=>array_values(array_unique($warnings)),'menu_fingerprint'=>(new MenuMapping())->fingerprint($target)];
    }
}
