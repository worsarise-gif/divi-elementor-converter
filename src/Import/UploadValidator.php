<?php
namespace SuiteEdge\Migrator\Import;
class UploadValidator {
    public function validate(array $file) {
        if (($file['error'] ?? -1) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) { throw new \RuntimeException('Upload failed.'); }
        if (strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION)) !== 'json') { throw new \InvalidArgumentException('Only .json exports are accepted.'); }
        if (filesize($file['tmp_name']) > DiviExportReader::MAX_BYTES) { throw new \LengthException('Export exceeds the 20 MB limit.'); }
        $json = file_get_contents($file['tmp_name']);
        (new DiviExportReader())->read_json($json);
        return $json;
    }
}
