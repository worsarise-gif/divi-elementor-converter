<?php
namespace SuiteEdge\Migrator\Normalize;
class DocumentNormalizer {
    public function normalize(\SuiteEdge\Migrator\Model\Document $document) {
        // Keep source hierarchy intact. Woo compound modules remain explicit fallbacks
        // until grouping can be proven from real exports without discarding styles.
        return $document;
    }
}
