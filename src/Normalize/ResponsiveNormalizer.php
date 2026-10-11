<?php
namespace SuiteEdge\Migrator\Normalize;
class ResponsiveNormalizer {
    public function normalize(array $attributes,$key) {
        $result = [];
        foreach ([''=>'desktop','_tablet'=>'tablet','_phone'=>'mobile'] as $suffix=>$device) { if (isset($attributes[$key.$suffix])) { $result[$device] = $attributes[$key.$suffix]; } }
        return $result;
    }
}
