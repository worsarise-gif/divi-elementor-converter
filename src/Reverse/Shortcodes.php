<?php
namespace SuiteEdge\Migrator\Reverse;

/** Generate quoted Divi 4 attributes without allowing shortcode injection. */
class Shortcodes {
    public function module($tag, array $attributes = [], $content = '') {
        if (!preg_match('/^et_pb_[a-z_]+$/D', $tag) || !is_string($content)) { throw new \InvalidArgumentException('Invalid Divi module.'); }
        $output = '[' . $tag;
        foreach ($attributes as $key => $value) {
            if (!preg_match('/^[a-z_][a-z0-9_]*$/D', $key) || !is_scalar($value)) { throw new \InvalidArgumentException('Invalid Divi attribute.'); }
            $encoded = htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $encoded = str_replace(['[', ']'], ['&#91;', '&#93;'], $encoded);
            $output .= ' ' . $key . '="' . $encoded . '"';
        }
        return $output . ']' . $content . '[/' . $tag . ']';
    }
    public function literal($content) {
        if (!is_string($content)) { throw new \InvalidArgumentException('Widget content must be text.'); }
        // Only builder shortcode delimiters are neutralized; authored HTML stays editable.
        return preg_replace('/\[(\/?(?:et_pb_|dgbm_))/i', '&#91;$1', $content);
    }
    public function validate(array $output) {
        if (($output['engine'] ?? '') !== 'divi4' || !is_string($output['content'] ?? null) || strlen($output['content']) > 20971520 || empty($output['content']) || !is_array($output['blockers'] ?? null)) { throw new \UnexpectedValueException('Saved Divi output is invalid.'); }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Preserve domain diagnostics; admin HTML/JSON consumers escape at their output boundary.
        if ($output['blockers']) { throw new \UnexpectedValueException('Conversion blocked: ' . implode(' ', $output['blockers'])); }
        $nodes = (new \SuiteEdge\Migrator\Divi\ShortcodeTokenizer())->tokenize($output['content']);
        if (!$nodes || array_filter($nodes, function ($n) { return $n['type'] !== 'et_pb_section'; })) { throw new \UnexpectedValueException('Divi output must contain complete sections.'); }
    }
}
