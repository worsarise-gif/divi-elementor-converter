<?php
namespace SuiteEdge\Migrator\Divi;
class GlobalModuleResolver {
    public function resolve($id,array $layouts) {
        foreach ($layouts as $layout) { if ((string)($layout['source_id'] ?? '') === (string)$id) { return (new ShortcodeTokenizer())->tokenize($layout['post_content']); } }
        throw new \UnexpectedValueException('Referenced global layout is missing from the export.');
    }
}
