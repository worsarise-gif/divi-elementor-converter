<?php
namespace SuiteEdge\Migrator\Import;
class DiviExportReader {
    const MAX_BYTES = 20971520;
    public function read_file($path) {
        try {
            if (!is_file($path) || !is_readable($path) || filesize($path) > self::MAX_BYTES) { return false; }
            return $this->read_json(file_get_contents($path));
        } catch (\Throwable $e) { return false; }
    }
    public function read_json($json) {
        if (!is_string($json) || strlen($json) > self::MAX_BYTES) { throw new \InvalidArgumentException('Export exceeds the 20 MB limit.'); }
        if (substr($json, 0, 3) === "\xEF\xBB\xBF") { $json = substr($json, 3); }
        $data = json_decode($json, true, 128, JSON_THROW_ON_ERROR);
        if (!is_array($data)) { throw new \UnexpectedValueException('Export must be a JSON object.'); }
        $source = $data['data'] ?? $data;
        $layouts = [];
        if (is_string($source)) { $source = [['post_content' => $source]]; }
        elseif (is_array($source) && isset($source['post_content'])) { $source = [$source]; }
        if (!is_array($source)) { throw new \UnexpectedValueException('Invalid Divi export data.'); }
        $collect = function ($value, $path = [], $depth = 0) use (&$collect, &$layouts) {
            if ($depth > 100 || count($layouts) > 1000) { throw new \LengthException('Export layout limit exceeded.'); }
            if (is_string($value) && strpos($value,'[et_pb_') !== false) { $value = ['post_content'=>$value]; }
            if (!is_array($value)) { return; }
            if (array_key_exists('post_content',$value)) {
                if (!is_string($value['post_content']) || strpos($value['post_content'],'[et_pb_') === false) { throw new \UnexpectedValueException('Layout has no Divi shortcodes.'); }
                $layouts[] = ['source_id'=>implode('/', $path),'title'=>(string)($value['post_title'] ?? $value['title'] ?? 'Divi layout'),'post_content'=>$value['post_content']];
                return;
            }
            foreach ($value as $key=>$child) { $collect($child,array_merge($path,[(string)$key]),$depth + 1); }
        };
        $collect($source);
        if (!$layouts) { throw new \UnexpectedValueException('No supported Divi layouts in this export.'); }
        return ['post_content' => $layouts[0]['post_content'], 'layouts' => $layouts, 'images' => is_array($data['images'] ?? null) ? $data['images'] : [], 'context' => $data['context'] ?? 'unknown', 'presets' => $data['presets'] ?? [], 'defaults' => $data['defaults'] ?? []];
    }
}
