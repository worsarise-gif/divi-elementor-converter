<?php
namespace SuiteEdge\Migrator\Reports;
class ExportReport {
    public function json(\SuiteEdge\Migrator\Jobs\Job $job) { return \SuiteEdge\Migrator\Support\Json::encode((new ReportBuilder())->build($job)); }
    public function template(array $data,$title = 'Converted Divi layout') {
        (new \SuiteEdge\Migrator\Elementor\Validator())->validate($data);
        return \SuiteEdge\Migrator\Support\Json::encode(['title'=>$title,'type'=>'page','version'=>'0.4','page_settings'=>new \stdClass(),'content'=>$data]);
    }
}
