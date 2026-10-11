<?php
namespace SuiteEdge\Migrator\Mapping;
/** Resolve the actual Font Awesome Unicode glyph against the installed Elementor library. */
class IconMapper {
    private static $libraries = [];
    public function map($value) {
        $parts = explode('|',(string)$value);
        if (($parts[2] ?? '') !== 'fa') { throw new \UnexpectedValueException('Legacy Divi icon font requires an explicit icon mapping.'); }
        $glyph = html_entity_decode($parts[0],ENT_QUOTES,'UTF-8');
        if (!preg_match('/^"\\\\u([a-f0-9]{4})"$/i',json_encode($glyph),$match)) { throw new \UnexpectedValueException('Unrecognized Divi icon glyph.'); }
        $unicode = strtolower($match[1]);
        $kinds = ($parts[4] ?? '') === '400' ? ['brands','regular','solid'] : ['solid','brands','regular'];
        // Divi format is glyph||fa||weight: empty pipe fields are intentional.
        foreach ($kinds as $kind) {
            if (!defined('ELEMENTOR_PATH')) { break; }
            $path = ELEMENTOR_PATH . 'assets/lib/font-awesome/json/'.$kind.'.json';
            if (!isset(self::$libraries[$path])) { $decoded = is_readable($path) ? json_decode(file_get_contents($path),true) : []; self::$libraries[$path] = $decoded['icons'] ?? []; }
            foreach (self::$libraries[$path] as $name=>$icon) {
                if (strtolower($icon[3] ?? '') === $unicode) {
                    return ['value'=>['solid'=>'fas','regular'=>'far','brands'=>'fab'][$kind].' fa-'.$name,'library'=>'fa-'.$kind];
                }
            }
        }
        throw new \UnexpectedValueException('Icon glyph is not available in the target Font Awesome library.');
    }
}
