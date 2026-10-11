<?php
namespace SuiteEdge\Migrator\Import;
/** Suggest an origin from WordPress media provenance, never from arbitrary HTML links. */
class RecommendedSettings {
    public function source_origin(array $export) {
        $urls = [];
        foreach ($export['images'] ?? [] as $key=>$image) {
            if (is_string($key)) { $urls[] = $key; }
            if (is_array($image) && is_string($image['url'] ?? null)) { $urls[] = $image['url']; }
        }
        foreach ($export['layouts'] ?? [] as $layout) {
            preg_match_all('~(?:^|\s)(?:src|image|image_src|background_image|portrait_url)=["\'](https?://[^"\'\s]+)["\']~i',$layout['post_content'],$matches);
            $urls = array_merge($urls,$matches[1]);
        }
        $origins = [];
        foreach ($urls as $url) {
            if (strlen($url) > 2048) { continue; }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Pure parser fallback before WordPress loads; WordPress runtime uses wp_parse_url.
            $parts = (function_exists('wp_parse_url') ? wp_parse_url(html_entity_decode($url,ENT_QUOTES,'UTF-8')) : parse_url(html_entity_decode($url,ENT_QUOTES,'UTF-8')));
            if (!$parts || isset($parts['user']) || isset($parts['pass']) || strpos($parts['path'] ?? '', '/wp-content/uploads/') === false) { continue; }
            $scheme = strtolower($parts['scheme'] ?? ''); $host = strtolower($parts['host'] ?? '');
            if (!in_array($scheme,['http','https'],true) || filter_var($host,FILTER_VALIDATE_IP) || strlen($host) > 253 || !preg_match('/^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,}$/D',$host) || preg_match('/\.(?:local|localhost|test|invalid)$/D',$host)) { continue; }
            if (isset($parts['port']) && $parts['port'] !== ($scheme === 'https' ? 443 : 80)) { continue; }
            $origins[$scheme.'://'.$host] = true;
        }
        return count($origins) === 1 ? key($origins) : '';
    }
    public function for_pending(array $pending,$context = 'page') {
        $origins = []; $seen = []; $complete = (bool)$pending;
        foreach ($pending as $file) {
            $id = $file['source_id'] ?? '';
            if (!is_string($id) || $id === '') { $complete = false; continue; }
            if (isset($seen[$id])) { continue; } $seen[$id] = true;
            try {
                $source = (new SourceRepository())->get($id);
                $origin = isset($source['site_page'])?(new \SuiteEdge\Migrator\Dynamic\SourceLinkSettings())->origin(home_url('/')):$this->source_origin((new DiviExportReader())->read_json($source['json']));
            } catch (\Throwable $e) { $origin = ''; }
            if ($origin === '') { $complete = false; } else { $origins[$origin] = true; }
        }
        $origin = $complete && count($origins) === 1 ? key($origins) : '';
        // An image-free layout cannot establish link provenance, but must not erase the
        // style recommendation from other exports from one consistent source.
        $theme_origin = count($origins) === 1 ? key($origins) : '';
        return ['dry_run'=>true,'assets'=>['import_external','reuse_library'],'source_origin'=>$origin,'resolve_source_links'=>$origin !== '','reviewed_warnings'=>false,'source_theme_origin'=>$theme_origin,'source_theme'=>(new \SuiteEdge\Migrator\Style\SourceThemeProfile())->recommended($theme_origin,$context)];
    }
    public function dry_run(array $request) {
        // Missing mode remains analysis; only an explicit apply selection can write pages.
        $mode = $request['execution_mode'] ?? 'analyze';
        if (!is_string($mode) || !in_array($mode,['analyze','apply'],true)) { throw new \InvalidArgumentException('Choose Analyze or Apply to pages.'); }
        return $mode !== 'apply';
    }
}
