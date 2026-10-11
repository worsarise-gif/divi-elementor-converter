<?php
namespace SuiteEdge\Migrator\Divi;
class AttributeParser {
    public function parse($text) {
        $attributes = [];
        preg_match_all('/([\w-]+)\s*=\s*(?:"([^"]*)"|\x27([^\x27]*)\x27|([^\s]+))/', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $attributes[strtolower($match[1])] = html_entity_decode($match[2] !== '' ? $match[2] : (($match[3] ?? '') !== '' ? $match[3] : ($match[4] ?? '')), ENT_QUOTES, 'UTF-8');
        }
        return $attributes;
    }
}
