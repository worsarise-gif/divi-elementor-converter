<?php
namespace SuiteEdge\Migrator\Reverse;

use SuiteEdge\Migrator\Jobs\Job;
use SuiteEdge\Migrator\Jobs\JobRepository;
use SuiteEdge\Migrator\Jobs\LockManager;

/** Owner-bound review sessions create one saved job, on the shared job lifecycle. */
class Service {
    public function reverse(Job $job) { return ($job->settings['direction']??'divi_to_elementor')==='elementor_to_divi'; }
    private function key($token) {
        if(!is_string($token)||!preg_match('/^[a-f0-9-]{36}$/D',$token)){throw new \InvalidArgumentException('Invalid reverse conversion session.');}
        return 'ace_reverse_selection_'.get_current_user_id().'_'.$token;
    }
    public function prepare($source,$target) {
        $snapshot=(new Source())->capture($source);$target=$target==='create_new'?0:(new \SuiteEdge\Migrator\Import\SitePageSource())->id($target);
        if($target){(new \SuiteEdge\Migrator\Elementor\TargetResolver())->validate($target,'page');}
        $token=wp_generate_uuid4();$record=['owner'=>get_current_user_id(),'source'=>$snapshot,'target'=>$target,'target_hash'=>$target?(new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($target):null,'target_context'=>$target?$this->context($target):null,'job_id'=>wp_generate_uuid4()];
        set_transient($this->key($token),$record,HOUR_IN_SECONDS);
        if(get_transient($this->key($token))!==$record){throw new \RuntimeException('Could not save private page selection.');}return $token;
    }
    public function create($token) {
        $key=$this->key($token);$lock=new LockManager();$lock->acquire('reverse-selection:'.$key);
        try{
            $record=get_transient($key);if(!is_array($record)||($record['owner']??0)!==get_current_user_id()){throw new \RuntimeException('Selection expired. Choose the pages again.');}
            $repo=new JobRepository();$existing=$repo->get($record['job_id']);if($existing){return $existing;}
            (new Source())->assert_current($record['source']);$target=$record['target'];
            if($target&&($this->context($target)!==$record['target_context']||!hash_equals($record['target_hash'],(new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($target)))){throw new \RuntimeException('Destination changed after selection. Choose the pages again.');}
            $job=new Job();$job->id=$record['job_id'];$job->settings=['direction'=>'elementor_to_divi','output_engine'=>'divi4','dry_run'=>true,'source_origin'=>home_url('/'),'assets'=>[]];
            $job->items=[['name'=>$record['source']['title'],'status'=>'pending','input_source'=>'elementor_site_page','reverse_source'=>$record['source'],'target_page_id'=>$target,'target_kind'=>'page','target_hash'=>$record['target_hash'],'target_context'=>$record['target_context'],'conversion_target'=>$target,'conversion_target_kind'=>'page']];$job->summary['total']=1;$repo->save($job);return $job;
        }finally{$lock->release();}
    }
    public function context($id) {
        $post=(new \SuiteEdge\Migrator\Elementor\TargetResolver())->validate($id,'page');return hash('sha256',wp_json_encode([$post->ID,$post->post_type,$post->post_status,$post->post_title,$post->post_name]));
    }
    public function eligible(Job $job) {
        if($job->archived||!in_array($job->status,['completed','completed_with_errors'],true)){return [];}
        return array_keys(array_filter($job->items,function($item){return ($item['status']??'')==='analyzed'&&!empty($item['divi_data']['content'])&&empty($item['divi_data']['blockers']);}));
    }
    public function validate(array $item) {
        (new Source())->assert_current($item['reverse_source']??[]);
        $output=$item['saved_output']??$item['divi_data']??[];(new Shortcodes())->validate($output);
        $id=(int)($item['target_page_id']??0);
        if(($item['target_kind']??'')!=='page'||($item['conversion_target_kind']??'')!=='page'||$id!==(int)($item['conversion_target']??-1)||empty($item['output_hash'])||!hash_equals($item['output_hash'],hash('sha256',wp_json_encode($output)))){throw new \RuntimeException('Saved Divi output or destination assignment changed. Reconvert before applying.');}
        if($id&&($this->context($id)!==($item['target_context']??null)||!hash_equals($item['target_hash']??'',(new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($id)))){throw new \RuntimeException('The Divi destination changed after analysis. Its newer edits are protected.');}
    }
    public function process(array $item,Job $job,LockManager $page_lock,$guard,JobRepository $repo) {
        $source=$item['reverse_source'];(new Source())->assert_current($source);$id=(int)($item['target_page_id']??0);
        if(empty($job->settings['saved_apply'])){
            $output=(new Document())->convert($source);
            if($id&&($this->context($id)!==($item['target_context']??null)||!hash_equals($item['target_hash']??'',(new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($id)))){throw new \RuntimeException('Destination changed during analysis. Select it again.');}
            $job->items[$job->current_index]['divi_data']=$output;$job->items[$job->current_index]['output_hash']=hash('sha256',wp_json_encode($output));$job->items[$job->current_index]['warnings']=$output['warnings'];$job->items[$job->current_index]['information']=$output['information'];$repo->save($job);
            return ['target_page_id'=>$id,'divi_data'=>$output,'warnings'=>$output['warnings'],'information'=>$output['information']];
        }
        $this->validate($item);$output=$item['saved_output'];(new Writer())->requirements($output);$guard();
        if($id&&get_post_status($id)==='publish'&&$output['warnings']&&empty($job->settings['reviewed_warnings'])){throw new \RuntimeException('Review the Divi report and acknowledge differences before applying to a published page.');}
        if(!$id){
            $id=(new \SuiteEdge\Migrator\Elementor\TargetResolver())->draft($source['title'],'page');if(is_wp_error($id)||!$id){throw new \RuntimeException('Could not create Divi page draft.');}
            $job->items[$job->current_index]['target_page_id']=$id;$job->items[$job->current_index]['created_target']=true;$job->items[$job->current_index]['conversion_target']=$id;$job->items[$job->current_index]['target_hash']=(new \SuiteEdge\Migrator\Import\BackupEngine())->fingerprint($id);$job->items[$job->current_index]['target_context']=$this->context($id);$repo->save($job);$page_lock->acquire('page:'.$id);
        }
        (new Source())->assert_current($source);$guard();$job->items[$job->current_index]['write_started']=true;$repo->save($job);
        (new Writer())->write($id,$output,$job->id,$guard,$job->items[$job->current_index]['target_hash']);
        return ['target_page_id'=>$id,'divi_data'=>$output,'warnings'=>$output['warnings'],'information'=>$output['information']];
    }
}
