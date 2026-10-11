<?php
namespace SuiteEdge\Migrator\Assets;
class UrlRewriter {
    public function rewrite($url,$source_origin,$target_origin) {
        $source = rtrim($source_origin,'/'); $target = rtrim($target_origin,'/');
        if ($source === '' || $target === '') { return $url; }
        if ($url === $source || strpos($url,$source . '/') === 0 || strpos($url,$source . '?') === 0 || strpos($url,$source . '#') === 0) { return $target . substr($url,strlen($source)); }
        // REST page URLs commonly use the canonical www/non-www form. Treat that
        // alias as internal while retaining host, port and HTTPS boundary checks.
        // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Pure URL transformation also runs before WordPress; runtime uses wp_parse_url.
        $a = function_exists('wp_parse_url') ? wp_parse_url($source) : parse_url($source);
        // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Pure URL transformation also runs before WordPress; runtime uses wp_parse_url.
        $b = function_exists('wp_parse_url') ? wp_parse_url($url) : parse_url($url);
        if ($a && $b && !isset($b['user']) && !isset($b['pass']) && empty($a['path']) && in_array($a['scheme'] ?? '',['http','https'],true) && in_array($b['scheme'] ?? '',['http','https'],true) && preg_replace('/^www\./','',strtolower($a['host'] ?? '')) === preg_replace('/^www\./','',strtolower($b['host'] ?? '')) && (($b['scheme'] === $a['scheme']) || ($a['scheme'] === 'http' && $b['scheme'] === 'https')) && ($a['port'] ?? ($b['scheme'] === 'https' ? 443 : 80)) === ($b['port'] ?? ($b['scheme'] === 'https' ? 443 : 80))) {
            return $target.($b['path'] ?? '').(isset($b['query']) ? '?'.$b['query'] : '').(isset($b['fragment']) ? '#'.$b['fragment'] : '');
        }
        return $url;
    }
}
