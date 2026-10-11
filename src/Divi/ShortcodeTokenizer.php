<?php
namespace SuiteEdge\Migrator\Divi;
/** Quote-aware stack parser; original shortcode slices are retained for fallbacks. */
class ShortcodeTokenizer {
    public function tokenize($content) {
        if (!is_string($content)) { throw new \InvalidArgumentException('Layout content must be a string.'); }
        $roots = []; $stack = []; $cursor = 0; $length = strlen($content); $count = 0; $top_end = 0;
        while ($cursor < $length) {
            $start = strpos($content, '[', $cursor);
            if ($start === false) { break; }
            if (($start > 0 && $content[$start - 1] === '[') || substr($content, $start, 2) === '[[') { $cursor = $start + 2; continue; }
            if (!preg_match('/\G\[(\/?)((?:et_pb_|dgbm_)[a-zA-Z0-9_-]+)\b/', $content, $m, 0, $start)) { $cursor = $start + 1; continue; }
            $quote = null; $end = $start + strlen($m[0]);
            for (; $end < $length; $end++) {
                $char = $content[$end];
                if ($quote !== null) { if ($char === $quote) { $quote = null; } }
                elseif ($char === '"' || $char === "'") { $quote = $char; }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                elseif ($char === '[') { throw new \UnexpectedValueException('Unclosed shortcode at offset ' . $start); }
                elseif ($char === ']') { break; }
            }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            if ($end === $length) { throw new \UnexpectedValueException('Unclosed shortcode attribute at offset ' . $start); }
            $tag = $m[2]; $closing = $m[1] === '/';
            if (!$stack && !$closing && trim(substr($content,$top_end,$start - $top_end)) !== '') {
                $plain = substr($content,$top_end,$start - $top_end);
                $roots[] = ['type'=>'__html','attributes'=>[],'children'=>[],'raw_content'=>$plain,'text_content'=>$plain,'original_shortcode'=>$plain];
            }
            if ($closing) {
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
                if (!$stack || end($stack)['type'] !== $tag) { throw new \UnexpectedValueException('Mismatched closing shortcode: ' . $tag . ' at ' . $start . '; open: ' . implode(',',array_column($stack,'type'))); }
                $node = array_pop($stack);
                $node['raw_content'] = substr($content, $node['_inner_start'], $start - $node['_inner_start']);
                $node['text_content'] = $node['raw_content'];
                $node['original_shortcode'] = substr($content, $node['_start'], $end + 1 - $node['_start']);
                unset($node['_inner_start'], $node['_start']);
                $this->append($roots, $stack, $node);
            } else {
                if (++$count > 10000 || count($stack) >= 100) { throw new \LengthException('Layout exceeds parser limits.'); }
                $attribute_text = substr($content, $start + strlen($m[0]), $end - $start - strlen($m[0]));
                $self_closing = preg_match('/\/\s*$/', $attribute_text) === 1;
                if ($tag === 'et_pb_code' && !$self_closing) {
                    $close = strpos($content,'[/et_pb_code]',$end + 1);
                    if ($close !== false) {
                        $inner = substr($content,$end + 1,$close - $end - 1);
                        $node = ['type'=>$tag,'attributes'=>(new AttributeParser())->parse(substr($content,$start + strlen($m[0]),$end - $start - strlen($m[0]))),'children'=>[],'raw_content'=>$inner,'text_content'=>$inner,'original_shortcode'=>substr($content,$start,$close + 13 - $start)];
                        $this->append($roots,$stack,$node); $cursor = $close + 13;
                        if (!$stack) { $top_end = $cursor; }
                        continue;
                    }
                }
                $node = ['type' => $tag, 'attributes' => (new AttributeParser())->parse($self_closing ? preg_replace('/\/\s*$/', '', $attribute_text) : $attribute_text), 'children' => [], 'raw_content' => '', 'text_content' => '', '_start' => $start, '_inner_start' => $end + 1];
                if ($self_closing) {
                    $node['original_shortcode'] = substr($content, $start, $end + 1 - $start);
                    unset($node['_start'], $node['_inner_start']);
                    $this->append($roots, $stack, $node);
                } else { $stack[] = $node; }
            }
            $cursor = $end + 1;
            if (!$stack) { $top_end = $cursor; }
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
        if ($stack) { throw new \UnexpectedValueException('Unclosed shortcode: ' . end($stack)['type']); }
        if ($roots && trim(substr($content,$top_end)) !== '') {
            $plain = substr($content,$top_end);
            $roots[] = ['type'=>'__html','attributes'=>[],'children'=>[],'raw_content'=>$plain,'text_content'=>$plain,'original_shortcode'=>$plain];
        }
        if (!$roots && trim($content) !== '') { throw new \UnexpectedValueException('No Divi modules found.'); }
        return $roots;
    }
    private function append(&$roots, &$stack, $node) {
        if ($stack) { $stack[count($stack) - 1]['children'][] = $node; }
        else { $roots[] = $node; }
    }
}
