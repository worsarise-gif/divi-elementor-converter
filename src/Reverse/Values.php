<?php
namespace SuiteEdge\Migrator\Reverse;

/** Shared source normalization; never make an executable URL acceptable. */
class Values {
    public function url($value) {
        if (!is_string($value) || preg_match('/[\x00-\x20<>"\x27]/', $value)) { throw new \UnexpectedValueException('Unsupported or unsafe link URL.'); }
        if ($value === '') { return ''; }
        if (!preg_match('~^(?:[a-z][a-z0-9+.-]*:|/|\#|\?)~i', $value)) {
            if (!preg_match('~^(?:[a-z0-9-]+\.)+[a-z]{2,}(?::[0-9]+)?(?:[/?#]|$)~i', $value)) { throw new \UnexpectedValueException('Ambiguous relative link URL.'); }
            // Elementor uses esc_url's default http scheme for a scheme-less host.
            $value = 'http://' . $value;
        }
        if (!preg_match('~^(?:https?://|/(?!/)|\#|\?|mailto:|tel:)~i', $value)) { throw new \UnexpectedValueException('Unsupported or unsafe link URL.'); }
        if (function_exists('esc_url_raw')) { $clean = esc_url_raw($value, ['http','https','mailto','tel']); if ($clean === '') { throw new \UnexpectedValueException('Unsupported or unsafe link URL.'); } return $clean; }
        return $value;
    }
}
