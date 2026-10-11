<?php
namespace SuiteEdge\Migrator\Dynamic;
class AcfMapper {
    public function map(array $payload) {
        if (!in_array($payload['dynamic_content'] ?? '',[],true)) { return null; }
        return (new DynamicMapper())->map($payload);
    }
}
