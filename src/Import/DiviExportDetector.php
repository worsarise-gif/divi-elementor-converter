<?php
namespace SuiteEdge\Migrator\Import;
class DiviExportDetector {
    public function detect($json) {
        try { $data = (new DiviExportReader())->read_json($json); return ['valid'=>true,'context'=>$data['context'],'layouts'=>count($data['layouts'])]; }
        catch (\Throwable $e) { return ['valid'=>false,'error'=>$e->getMessage()]; }
    }
}
