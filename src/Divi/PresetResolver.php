<?php
namespace SuiteEdge\Migrator\Divi;
/** Accept only explicitly normalized attribute dictionaries; raw Divi preset schemas need a version adapter. */
class PresetResolver {
    public function resolve_export($type,array $attributes,array $presets) {
        $group = $presets[$type] ?? null;
        $id = $attributes['preset'] ?? $attributes['_module_preset'] ?? 'default';
        if ($group === null && $id === 'default') { return $attributes; }
        if (!is_array($group) || !is_array($group['presets'] ?? null)) { throw new \UnexpectedValueException('Preset schema has not been normalized.'); }
        if ($id === 'default') { $id = $group['default'] ?? ''; }
        $preset = $group['presets'][$id] ?? null;
        if (!is_array($preset) || !preg_match('/^4\./',(string)($preset['version'] ?? '')) || !is_array($preset['settings'] ?? null)) { throw new \UnexpectedValueException('Unsupported or missing Divi 4 preset definition.'); }
        foreach ($preset['settings'] as $key=>$value) { if (!is_string($key) || !is_scalar($value)) { throw new \UnexpectedValueException('Unsupported preset setting.'); } }
        return array_merge($preset['settings'],$attributes);
    }
    public function resolve(array $attributes,array $normalized_presets) {
        $id = $attributes['preset'] ?? '';
        if ($id === '') { return $attributes; }
        if (!isset($normalized_presets[$id]) || !is_array($normalized_presets[$id])) { throw new \UnexpectedValueException('Preset schema has not been normalized.'); }
        return array_merge($normalized_presets[$id],$attributes);
    }
}
