<?php
namespace SuiteEdge\Migrator\Divi;
class Scanner {
    public function scan($content) {
        $report = ['modules'=>[],'nodes'=>0,'dynamic'=>0,'globals'=>0,'assets'=>[]];
        $registry = new \SuiteEdge\Migrator\Mapping\ModuleRegistry();
        $walk = function ($nodes) use (&$walk,&$report,$registry) {
            foreach ($nodes as $node) {
                $tag = $node['type']; $report['nodes']++;
                if (!isset($report['modules'][$tag])) { $report['modules'][$tag] = ['count'=>0,'native_mapping'=>$registry->get_converter($tag) !== null,'attributes'=>[]]; }
                $report['modules'][$tag]['count']++;
                foreach ($node['attributes'] as $key=>$value) {
                    $report['modules'][$tag]['attributes'][$key] = ($report['modules'][$tag]['attributes'][$key] ?? 0) + 1;
                    if (strpos($value,'@ET-DC@') !== false) { $report['dynamic']++; }
                    if (preg_match('/^(src|image|image_url|portrait_url|background_image|image_src)$/',$key) && $value !== '') { $report['assets'][] = $value; }
                }
                if (isset($node['attributes']['global_module'])) { $report['globals']++; }
                $walk($node['children']);
            }
        };
        $walk((new ShortcodeTokenizer())->tokenize($content)); $report['assets'] = array_values(array_unique($report['assets']));
        return $report;
    }
}
