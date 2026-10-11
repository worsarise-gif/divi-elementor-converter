<?php
namespace SuiteEdge\Migrator\Normalize;
class ContentNormalizer {
    public function normalize($content) {
        if (!is_string($content)) { throw new \InvalidArgumentException('Content must be a string.'); }
        return str_replace(["\r\n","\r"],"\n",$content);
    }
}
