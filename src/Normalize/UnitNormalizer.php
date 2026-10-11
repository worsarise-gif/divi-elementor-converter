<?php
namespace SuiteEdge\Migrator\Normalize;
class UnitNormalizer {
    public function slider($value, $default = 'px') {
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
        if (!preg_match('/^\s*(-?(?:\d+(?:\.\d+)?|\.\d+))\s*(px|%|em|rem|vh|vw|deg)?\s*$/', (string)$value, $m)) { throw new \UnexpectedValueException('Unsupported size: ' . $value); }
        return ['unit'=>$m[2] ?? $default, 'size'=>(float)$m[1]];
    }
    public function dimensions($value) {
        $parts = explode('|', $value); if (count($parts) === 1) { $parts = array_fill(0,4,$value); }
        if (count($parts) < 4) { throw new \UnexpectedValueException('Invalid spacing.'); }
        $result = ['unit'=>'px','isLinked'=>false]; $unit = null;
        foreach (['top','right','bottom','left'] as $i=>$side) {
            if ($parts[$i] === '') { $result[$side] = ''; continue; }
            $size = $this->slider($parts[$i]);
            if ($unit !== null && $unit !== $size['unit']) { throw new \UnexpectedValueException('Mixed spacing units require CSS review.'); }
            $unit = $size['unit']; $result[$side] = (string)$size['size'];
        }
        $result['unit'] = $unit ?? 'px'; return $result;
    }
}
