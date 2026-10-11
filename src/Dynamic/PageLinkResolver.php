<?php
namespace SuiteEdge\Migrator\Dynamic;
/** Resolve known WordPress link types; never bind source IDs on a different site. */
class PageLinkResolver {
    private $cache = [];
    private $results = [];
    public function diagnostics() { return array_values($this->results); }
    public function reason($id,$type = 'page') { return $this->results[$type.':'.$id]['message'] ?? 'Provide an explicit target URL mapping.'; }
    private function result($id,$type,$code,$message,$link = null) {
        $this->results[$type.':'.$id] = ['source_page_id'=>(string)$id,'source_item_id'=>(string)$id,'source_type'=>$type,'code'=>$code,'message'=>$message,'resolved_url'=>$link];
        return $link;
    }
    public function resolve($id,$origin,array $explicit = [],$type = 'page',$same_site_source = false) {
        $routes = ['page'=>'pages','post'=>'posts','attachment'=>'media'];
        if (!is_string($type) || !isset($routes[$type])) { return $this->result(is_scalar($id)?$id:'','unknown','unsupported_link_type','This source link type requires manual migration.'); }
        if ((!is_int($id) && !is_string($id)) || !ctype_digit((string)$id) || (int)$id < 1 || (string)(int)$id !== ltrim((string)$id,'0')) { return $this->result(is_scalar($id)?$id:'',$type,'invalid_'.$type.'_id','The source '.$type.' ID is invalid.'); }
        $id = (string)(int)$id;
        if (isset($explicit[$id])) {
            $link = $explicit[$id];
            if (!is_string($link) || preg_match('/[\\\\\x00-\x20\x7f<>]/',$link) || (!preg_match('~^/(?!/)~',$link) && !$this->absolute_link($link))) { return $this->result($id,$type,'invalid_explicit_mapping','Provide a safe HTTP(S) target URL or a site-relative path for this source item.'); }
            return $this->result($id,$type,'explicit_mapping','Explicit target URL mapping used.',$link);
        }
        if (!$origin) { return $this->result($id,$type,'missing_source_origin','Enter the original Divi site URL and run a fresh conversion.'); }
        $origin = (new SourceLinkSettings())->origin($origin);
        $statuses = $type === 'attachment' ? ['inherit','publish'] : ['publish'];
        if ($same_site_source || rtrim($origin,'/') === rtrim(home_url(),'/')) {
            $post = get_post((int)$id);
            return $post && $post->post_type === $type && in_array($post->post_status,$statuses,true) ? $this->result($id,$type,'same_site_'.$type,'Public '.$type.' resolved on the same site.',get_permalink($post)) : $this->result($id,$type,'source_'.$type.'_unavailable','The same-site source '.$type.' is missing or not public. Provide its target URL mapping.');
        }
        $route = '/wp/v2/'.$routes[$type].'/'.$id;
        $url = rtrim($origin,'/').'/wp-json'.$route.'?_fields=id,link,status,type';
        if (array_key_exists($url,$this->cache)) {
            $cached = $this->cache[$url];
            return $this->result($id,$type,$cached['code'],$cached['message'],$cached['resolved_url']);
        }
        $link = $this->request($id,$origin,$type,$statuses,$route,$url);
        $this->cache[$url] = $this->results[$type.':'.$id];
        return $link;
    }
    private function absolute_link($link) {
        $parts = wp_parse_url($link);
        return $parts && in_array(strtolower($parts['scheme'] ?? ''),['http','https'],true) && !empty($parts['host']) && !isset($parts['user']) && !isset($parts['pass']);
    }
    private function request($id,$origin,$type,array $statuses,$route,$url) {
        if (!wp_http_validate_url($url)) { return $this->result($id,$type,'unsafe_source_url','The source URL is not available for safe public requests. Provide an explicit target URL mapping.'); }
        $response = wp_safe_remote_get($url,['timeout'=>8,'redirection'=>2,'limit_response_size'=>65536]);
        // WordPress sites without pretty REST permalinks still expose the standard route.
        if (!is_wp_error($response) && in_array(wp_remote_retrieve_response_code($response),[404,405],true)) {
            $response = wp_safe_remote_get(rtrim($origin,'/').'/?rest_route='.$route.'&_fields=id,link,status,type',['timeout'=>8,'redirection'=>2,'limit_response_size'=>65536]);
        }
        if (is_wp_error($response)) { return $this->result($id,$type,'source_request_failed','Public source REST lookup failed ('.$response->get_error_code().'). Check server access to the source, or provide an explicit target URL mapping.'); }
        $status = wp_remote_retrieve_response_code($response);
        if ($status !== 200) { return $this->result($id,$type,'source_http_'.$status,'Public source REST lookup returned HTTP '.$status.'. Check that this source '.$type.' is public and the REST API is accessible, or provide its target URL mapping.'); }
        $page = json_decode(wp_remote_retrieve_body($response),true);
        if (!is_array($page) || !is_scalar($page['id'] ?? null) || (string)$page['id'] !== $id || ($page['type'] ?? '') !== $type || !in_array($page['status'] ?? '',$statuses,true) || !is_string($page['link'] ?? null)) { return $this->result($id,$type,'invalid_source_response','The source API did not return the requested public '.$type.'. Provide an explicit target URL mapping.'); }
        $link = $page['link'];
        // Accept only this origin or its conventional www canonical alias; never an
        // unrelated host, credentials, changed port, or HTTPS downgrade.
        $source = wp_parse_url($origin); $target = wp_parse_url($link);
        $host_matches = $target && preg_replace('/^www\./','',strtolower($target['host'] ?? '')) === preg_replace('/^www\./','',strtolower($source['host']));
        $scheme_matches = $target && (($target['scheme'] ?? '') === $source['scheme'] || ($source['scheme'] === 'http' && ($target['scheme'] ?? '') === 'https'));
        $ports_match = $target && ($target['port'] ?? (($target['scheme'] ?? '') === 'https' ? 443 : 80)) === ($source['port'] ?? (($target['scheme'] ?? '') === 'https' ? 443 : 80));
        if (!$host_matches || !$scheme_matches || !$ports_match || isset($target['user']) || isset($target['pass']) || !wp_http_validate_url($link)) { return $this->result($id,$type,'source_link_origin_mismatch','The returned '.$type.' URL does not match the source site. Check its canonical URL or provide an explicit target URL mapping.'); }
        return $this->result($id,$type,'resolved_public_'.$type,'Public '.$type.' resolved through the source REST API.',$link);
    }
}
