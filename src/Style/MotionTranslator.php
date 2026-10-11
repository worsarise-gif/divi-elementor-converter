<?php
namespace SuiteEdge\Migrator\Style;
use SuiteEdge\Migrator\Model\Node;

/** Portable Divi background parallax and horizontal scroll curves. */
class MotionTranslator {
    public function translate(Node $node, array $a) {
        $handled = []; $effects = []; $notes = [];
        foreach (['parallax','scroll_horizontal_motion_enable'] as $flag) {
            $previous = 'off';
            foreach (['desktop'=>'','tablet'=>'_tablet','mobile'=>'_phone'] as $device=>$suffix) {
                $key = $flag.$suffix;
                $value = $a[$key] ?? $previous;
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                if (!in_array($value,['on','off',''],true)) { throw new \UnexpectedValueException('Invalid motion enable flag: '.$key.'.'); }
                $previous = $value === '' ? 'off' : $value;
                $enabled[$flag][$device] = $previous === 'on';
                if (array_key_exists($key,$a)) { $handled[] = $key; }
            }
        }
        // Divi can retain parallax="on" after its image background is removed/disabled.
        // Such a setting has no source effect and must not force a shortcode fallback.
        $image_enabled = '';
        foreach (['desktop'=>'','tablet'=>'_tablet','mobile'=>'_phone'] as $device=>$suffix) {
            $image_enabled = $a['background_enable_image'.$suffix] ?? $image_enabled;
            $image = $a['background_image'.$suffix] ?? $a['background_image'] ?? '';
            if ($enabled['parallax'][$device] && ($image === '' || $image_enabled === 'off')) {
                $enabled['parallax'][$device] = false;
                $notes[] = 'Saved parallax setting ignored because the image background is absent or disabled on '.$device.'.';
            }
        }
        if (in_array(true,$enabled['parallax'],true)) {
            if (empty($a['background_image']) || ($a['background_enable_image'] ?? '') === 'off' || $node->styles->to_elementor()['background_background'] !== 'classic') {
                throw new \UnexpectedValueException('Parallax requires an enabled image background; mixed video/gradient backgrounds need review.');
            }
            $method = 'on';
            foreach (['desktop'=>'','tablet'=>'_tablet','mobile'=>'_phone'] as $device=>$suffix) {
                $key = 'parallax_method'.$suffix; $method = $a[$key] ?? $method;
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                if ($enabled['parallax'][$device] && !in_array($method,['on','off'],true)) { throw new \UnexpectedValueException('Unknown parallax method: '.$key.'.'); }
                $effects['parallax'][$device] = !$enabled['parallax'][$device] ? false : ($method === 'off' ? 'fixed' : 'true');
            }
            $notes[] = 'Background parallax converted with a portable runtime; reduced motion uses a static image, and fixed backgrounds use a static image on compact screens.';
        }
        foreach (['','_tablet','_phone'] as $suffix) { if (isset($a['parallax_method'.$suffix])) { $handled[] = 'parallax_method'.$suffix; } }
        if (in_array(true,$enabled['scroll_horizontal_motion_enable'],true)) {
            $curve = null;
            foreach (['desktop'=>'','tablet'=>'_tablet','mobile'=>'_phone'] as $device=>$suffix) {
                $key = 'scroll_horizontal_motion'.$suffix;
                if (isset($a[$key])) { $curve = $a[$key]; }
                $effects['horizontal'][$device] = $enabled['scroll_horizontal_motion_enable'][$device] ? $this->curve($curve) : false;
            }
            $trigger = $a['motion_trigger_start'] ?? 'middle';
            if (!in_array($trigger,['top','middle','bottom'],true)) { throw new \UnexpectedValueException('Unknown scroll motion trigger.'); }
            $effects['trigger'] = $trigger;
            $notes[] = 'Enabled horizontal scroll motion converted; one Divi offset unit equals 100 pixels. Reduced motion disables the movement.';
        } elseif (isset($a['scroll_horizontal_motion'])) {
            $notes[] = 'Saved horizontal motion values ignored because the source effect is disabled.';
        }
        foreach (['','_tablet','_phone'] as $suffix) { if (isset($a['scroll_horizontal_motion'.$suffix])) { $handled[] = 'scroll_horizontal_motion'.$suffix; } }
        $other_active = false;
        foreach ($a as $key=>$value) { if (!preg_match('/^scroll_horizontal_motion_enable(?:_tablet|_phone)?$/',$key) && preg_match('/^scroll_.*_enable(?:_tablet|_phone)?$/',$key) && $value === 'on') { $other_active = true; } }
        if (!$other_active && isset($a['motion_trigger_start'])) { $handled[] = 'motion_trigger_start'; }
        $node->advanced['handled_attributes'] = array_merge($node->advanced['handled_attributes'] ?? [],$handled);
        $node->advanced['information'] = array_merge($node->advanced['information'] ?? [],$notes);
        if ($effects) { $node->advanced['portable_effects'] = $effects; }
        if ($node->type === 'image' && !empty($a['title_text'])) {
            $node->advanced['portable_effects']['image_title'] = (string)$a['title_text'];
            $node->advanced['information'][] = 'Image tooltip restored on the rendered image without creating a visible caption.';
        }
    }
    private function curve($value) {
        $parts = is_string($value) ? explode('|',$value) : [];
        if (count($parts) !== 7) { throw new \UnexpectedValueException('Horizontal scroll curve must contain four viewport positions and three offsets.'); }
        foreach ($parts as &$part) {
            if (!preg_match('/^-?\d+(?:\.\d+)?$/D',$part) || !is_finite((float)$part)) { throw new \UnexpectedValueException('Horizontal scroll curve contains an invalid number.'); }
            $part = (float)$part;
        } unset($part);
        for ($i=0;$i<4;$i++) { if ($parts[$i] < 0 || $parts[$i] > 100 || ($i && $parts[$i] < $parts[$i-1])) { throw new \UnexpectedValueException('Horizontal scroll viewport positions must be ordered from 0 to 100.'); } }
        foreach (array_slice($parts,4) as $offset) { if (abs($offset) > 100) { throw new \UnexpectedValueException('Horizontal scroll offset exceeds the supported range.'); } }
        return $parts;
    }
}
