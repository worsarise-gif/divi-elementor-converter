<?php
/** Default settings and explicit mode regressions on a disposable WordPress site. */
if (!defined('ABSPATH')) { exit(1); }
wp_set_current_user(get_user_by('login','qa_admin')->ID);
set_current_screen('suiteedge-migrator-mapping');
$checks = 0;
$check = function ($ok,$name) use (&$checks) { if (!$ok) { throw new RuntimeException('FAIL: '.$name); } $checks++; };
$rejects = function ($callback,$name) use ($check) { try { $callback(); } catch (InvalidArgumentException $e) { $check(true,$name); return; } $check(false,$name); };
$recommended = new SuiteEdge\Migrator\Import\RecommendedSettings();
$check($recommended->dry_run([]),'missing mode is analysis');
$check($recommended->dry_run(['execution_mode'=>'analyze']),'explicit analysis');
$check(!$recommended->dry_run(['execution_mode'=>'apply']),'explicit apply');
$rejects(function () use ($recommended) { $recommended->dry_run(['execution_mode'=>'invented']); },'invalid mode rejected');
$rejects(function () use ($recommended) { $recommended->dry_run(['execution_mode'=>['apply']]); },'array mode rejected');
$fixture = ['layouts'=>[['post_content'=>'[et_pb_image src="https://origin.example/wp-content/uploads/a.png" /]<a href="https://unrelated.example">Link</a>']],'images'=>[]];
$check($recommended->source_origin($fixture) === 'https://origin.example','infer from WordPress media, ignore HTML links');
$fixture['images'] = ['https://other.example/wp-content/uploads/b.png'=>['url'=>'https://other.example/wp-content/uploads/b.png']];
$check($recommended->source_origin($fixture) === '','mixed media origins require explicit origin');
foreach (['http://127.0.0.1','http://192.168.1.2','http://localhost','http://host.local','https://user:pass@origin.example','https://origin.example:8443'] as $origin) {
    $check($recommended->source_origin(['images'=>[$origin.'/wp-content/uploads/a.png'=>[]]]) === '','not auto-selected '.$origin);
}
$check($recommended->source_origin(['images'=>['https://origin.example:443/wp-content/uploads/a.png'=>[]]]) === 'https://origin.example','default port normalized');
$source = (new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['data'=>'[et_pb_image src="https://origin.example/wp-content/uploads/a.png" /]']),'defaults.json');
$pending = [['source_id'=>$source,'layout_index'=>0,'name'=>'Defaults']];
$defaults = $recommended->for_pending($pending);
$check($defaults['source_origin'] === 'https://origin.example' && $defaults['resolve_source_links'],'recommended source resolver enabled');
$check($defaults['assets'] === ['import_external','reuse_library'] && $defaults['dry_run'] && !$defaults['reviewed_warnings'],'recommended media and analysis; review acknowledgement stays explicit');
$check($recommended->for_pending([['source_id'=>'missing']])['source_origin'] === '','unavailable source not inferred');
$unknown = (new SuiteEdge\Migrator\Import\SourceRepository())->save(wp_json_encode(['data'=>'[et_pb_text]Unknown origin[/et_pb_text]']),'unknown.json');
$check($recommended->for_pending(array_merge($pending,[['source_id'=>$unknown]]))['source_origin'] === '','unknown export among mixed uploads requires explicit origin');
$admin = get_current_user_id(); $other = wp_insert_user(['user_login'=>'defaults_other_'.wp_rand(),'user_pass'=>'local-test','role'=>'administrator']); wp_set_current_user($other);
$check($recommended->for_pending($pending)['source_origin'] === '','source owner isolation for recommendations'); wp_set_current_user($admin);
set_transient('suiteedge_migrator_pending_files_'.$admin,$pending,HOUR_IN_SECONDS);
ob_start(); (new SuiteEdge\Migrator\Admin\MappingPage())->render(); $html = ob_get_clean();
$check(strpos($html,'name="source_origin" value="https://origin.example"') !== false && strpos($html,'name="resolve_source_links" value="1" checked') !== false,'rendered origin and checked resolver');
$check(strpos($html,'name="assets[]" value="import_external" checked') !== false && strpos($html,'name="assets[]" value="reuse_library" checked') !== false,'rendered checked media defaults');
$check(strpos($html,'name="execution_mode" value="analyze"') !== false && strpos($html,'name="execution_mode" value="apply"') !== false && strpos($html,'>Apply to pages / posts</button>') !== false,'visible analysis and apply submit buttons');
$check(strpos($html,'name="reviewed_warnings" value="1" checked') === false,'published warning review never pre-acknowledged');
// Exercise the actual form handler: a missing mode cannot accidentally update the selected page.
$page = wp_insert_post(['post_type'=>'page','post_status'=>'draft','post_title'=>'Recommended target']);
$before = (new SuiteEdge\Migrator\Import\BackupEngine())->state($page);
$repo = new SuiteEdge\Migrator\Jobs\JobRepository(); $runner = new SuiteEdge\Migrator\Jobs\BatchRunner($repo);
foreach (['missing','analyze','apply'] as $mode) {
    set_transient('suiteedge_migrator_pending_files_'.$admin,[['source_id'=>$unknown,'layout_index'=>0,'name'=>'Mode fixture']],HOUR_IN_SECONDS);
    $_GET = []; $_POST = ['submit_conversion'=>'Start','_wpnonce'=>wp_create_nonce('suiteedge_create_job'),'target_page'=>[$page]];
    if ($mode !== 'missing') { $_POST['execution_mode'] = $mode; }
    $_REQUEST = $_POST;
    ob_start(); (new SuiteEdge\Migrator\Admin\JobsPage())->render(); ob_end_clean();
    $job = $repo->get_latest_active(); $check($job && !empty($job->settings['dry_run']) === ($mode !== 'apply'),'form mode persisted '.$mode);
    $response = $runner->process_batch($job->id); $job = $repo->get($job->id);
    $outcome = (new SuiteEdge\Migrator\Reports\JobOutcome())->describe($job);
    if ($mode !== 'apply') {
        $check($response['status'] === 'completed' && (new SuiteEdge\Migrator\Import\BackupEngine())->state($page) === $before && $outcome['converted_pages'] === 0 && strpos($outcome['status_label'],'no page changes') !== false,'analysis completion is not a write '.$mode);
    } else {
        $check($response['status'] === 'completed' && $outcome['converted_pages'] === 1 && $job->items[0]['status'] === 'converted','explicit apply updates assigned target');
        $check($runner->rollback($job->id)['status'] === 'rolled_back' && (new SuiteEdge\Migrator\Import\BackupEngine())->state($page) === $before,'recommended mode rollback');
    }
    $report = (new SuiteEdge\Migrator\Reports\ReportBuilder())->build($job);
    $check($report['outcome'] === $outcome,'downloaded report carries explicit outcome '.$mode);
}
$job->status = 'completed'; $job->settings['dry_run'] = true; $job->items = [['status'=>'analyzed','elementor_data'=>[['widgetType'=>'shortcode','settings'=>['shortcode'=>'[unresolved]'],'elements'=>[]]]]];
$outcome = (new SuiteEdge\Migrator\Reports\JobOutcome())->describe($job);
$check($outcome['blocking_modules'] === 1 && strpos($outcome['status_label'],'conversion blocked') !== false,'analysis fallback blocks apply in displayed outcome');
$_GET = []; $_POST = []; $_REQUEST = [];
if (getenv('SUITEEDGE_HOME_FIXTURE')) {
    $json = file_get_contents(getenv('SUITEEDGE_HOME_FIXTURE'));
    $export = (new SuiteEdge\Migrator\Import\DiviExportReader())->read_json($json);
    $check($recommended->source_origin($export) === 'https://suiteedge.com','real Home origin inferred');
    if (getenv('SUITEEDGE_HOME_LINKS')) {
        $links = json_decode(file_get_contents(getenv('SUITEEDGE_HOME_LINKS')),true)['links']; $requests = 0;
        $rest = function ($pre,$args,$url) use ($links,&$requests) {
            if (!preg_match('~^https://suiteedge\.com/wp-json/wp/v2/pages/(\d+)\?~',$url,$match)) { return $pre; }
            $requests++; $id = (int)$match[1];
            return ['headers'=>[],'body'=>wp_json_encode(['id'=>$id,'type'=>'page','status'=>'publish','link'=>$links[$id] ?? '']),'response'=>['code'=>200],'cookies'=>[]];
        };
        add_filter('pre_http_request',$rest,10,3);
        $home_source = (new SuiteEdge\Migrator\Import\SourceRepository())->save($json,'Home.json');
        $home = new SuiteEdge\Migrator\Jobs\Job();
        $home->settings = $recommended->for_pending([['source_id'=>$home_source]]);
        $home->items = [['source_id'=>$home_source,'layout_index'=>0,'name'=>'Home.json','target_page_id'=>0,'status'=>'pending']]; $home->summary['total'] = 1; $repo->save($home);
        $result = $runner->process_batch($home->id); $home = $repo->get($home->id);
        $check($result['status'] === 'completed','real Home with recommended defaults completes');
        $counts = []; $walk = function ($nodes) use (&$walk,&$counts) { foreach ($nodes as $node) { $type = $node['widgetType'] ?? $node['elType']; $counts[$type] = ($counts[$type] ?? 0)+1; $walk($node['elements']); } }; $walk($home->items[0]['elementor_data']);
        $check(empty($counts['shortcode']) && ($counts['button'] ?? 0) === 4 && ($counts['icon-box'] ?? 0) === 12,'recommended Home resolver replaces all 16 fallback modules');
        $check($requests === 14,'all 14 Home IDs resolved once through controlled REST fixture');
        $check(!array_filter($home->items[0]['warnings'],function ($warning) { return strpos($warning,'Dynamic binding') !== false || strpos($warning,'Enable image import') !== false; }),'recommended Home has no unresolved link or disabled image import warnings');
        remove_filter('pre_http_request',$rest,10);
    }
}
file_put_contents(ABSPATH.'../recommended-settings-results'.(defined('DB_ENGINE') ? '-sqlite' : '-mariadb').'.json',wp_json_encode(['assertions'=>$checks,'database'=>defined('DB_ENGINE') ? DB_ENGINE : $GLOBALS['wpdb']->db_version(),'wordpress'=>get_bloginfo('version'),'elementor'=>ELEMENTOR_VERSION,'plugin'=>SUITEEDGE_MIGRATOR_VERSION,'browser_visual_qa'=>false],JSON_PRETTY_PRINT));
echo 'PASS: '.$checks.' recommended settings/mode assertions'.PHP_EOL;
