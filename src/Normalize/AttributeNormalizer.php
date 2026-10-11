<?php
namespace SuiteEdge\Migrator\Normalize;
/** Divi keeps inactive device/hover values in exports; those must not become active styles. */
class AttributeNormalizer {
    public function normalize(array $a) {
        foreach ($a as $key=>$value) {
            if (substr($key,-12) === '_last_edited' && strpos((string)$value,'off|') === 0) {
                $base = substr($key,0,-12); unset($a[$base.'_tablet'],$a[$base.'_phone']);
            }
            if (substr($key,-15) === '__hover_enabled' && strpos((string)$value,'off|') === 0) { unset($a[substr($key,0,-15).'__hover']); }
        }
        return $a;
    }
}
