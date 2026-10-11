<?php
namespace SuiteEdge\Migrator\Normalize;
/** Elementor wraps box descriptions in a paragraph; nested Divi paragraphs are invalid HTML. */
class BlurbContentNormalizer {
    public function normalize($html) {
        if (preg_match('~<(?:div|section|article|h[1-6]|ul|ol|li|table|blockquote|pre|form)\b~i',$html)) {
            throw new \UnexpectedValueException('Blurb body contains block content incompatible with the native Elementor box description. Migrate it to a Text Editor/container before applying.');
        }
        if (preg_match('~^\s*<p\s*>([\s\S]*?)</p>\s*$~iD',$html,$match) && !preg_match('~</?p\b~i',$match[1])) { return $match[1]; }
        // Preserve multiple paragraphs and attributes as phrasing content inside the native paragraph.
        $html = preg_replace('~<p\b([^>]*)>~i','<span class="suiteedge-blurb-paragraph"><span$1>',$html);
        return preg_replace('~</p\s*>~i','</span></span>',$html);
    }
}
