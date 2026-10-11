<?php
namespace SuiteEdge\Migrator\Dynamic;
/** Resolve a supported site-home binding against this WordPress site's frontend address. */
class SiteLinkResolver {
    public function resolve($module,$attribute,$value) {
        $links = ['et_pb_menu'=>['logo_url'],'et_pb_image'=>['url'],'et_pb_button'=>['button_url'],'et_pb_icon'=>['url','link_option_url'],'et_pb_blurb'=>['url','link_option_url']];
        if (!in_array($attribute,$links[$module] ?? [],true) || !is_string($value) || !preg_match('/^@ET-DC@[^@]+@$/D',$value)) { return null; }
        $binding = (new \SuiteEdge\Migrator\Divi\DynamicContentParser())->parse($value);
        if (($binding['dynamic'] ?? null) !== true || ($binding['content'] ?? '') !== 'home_url' || !isset($binding['settings']) || !is_array($binding['settings']) || $binding['settings'] || !function_exists('home_url')) { return null; }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Pure parser fallback before WordPress loads; WordPress runtime uses wp_parse_url.
        $url = home_url('/'); $parts = is_string($url) ? (function_exists('wp_parse_url') ? wp_parse_url($url) : parse_url($url)) : false;
        if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''),['http','https'],true) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || preg_match('/[\x00-\x20\x7f]/',$url)) { return null; }
        return $url;
    }
}
