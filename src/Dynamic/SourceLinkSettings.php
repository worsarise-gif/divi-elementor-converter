<?php
namespace SuiteEdge\Migrator\Dynamic;
/** Normalize an explicitly supplied reference URL without inferring foreign page IDs. */
class SourceLinkSettings {
    public function origin($value) {
        if (!is_string($value) || strlen($value) > 2048) { throw new \InvalidArgumentException('Enter a valid public source site URL.'); }
        $value = trim($value);
        if ($value === '') { return ''; }
        $parts = wp_parse_url($value);
        if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''),['http','https'],true) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || preg_match('/[\s<>]/',$value)) { throw new \InvalidArgumentException('Source site URL must use http or https and contain no credentials.'); }
        $scheme = strtolower($parts['scheme']); $host = strtolower($parts['host']);
        $port = isset($parts['port']) && $parts['port'] !== ($scheme === 'https' ? 443 : 80) ? ':'.$parts['port'] : '';
        return $scheme.'://'.$host.$port;
    }
    public function enabled(array $request,$origin) {
        // The hidden marker preserves a deliberate unchecked checkbox. Older forms
        // and integrations supplying a URL receive the useful resolver default.
        return isset($request['resolve_source_links_present']) ? !empty($request['resolve_source_links']) : (!empty($request['resolve_source_links']) || $origin !== '');
    }
}
