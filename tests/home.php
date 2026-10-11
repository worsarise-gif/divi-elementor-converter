<?php
/** Real export pilot. SUITEEDGE_HOME_FIXTURE and SUITEEDGE_HOME_LINKS point to local, private fixtures. */
if (!defined('ABSPATH') || !getenv('SUITEEDGE_HOME_FIXTURE') || !getenv('SUITEEDGE_HOME_LINKS')) { throw new RuntimeException('Run this pilot on a disposable WordPress site with explicit fixture paths.'); }
wp_set_current_user(get_user_by('login','qa_admin')->ID);
$GLOBALS['home_checks'] = 0;
function home_check($condition,$name) { if (!$condition) { throw new RuntimeException('FAIL: '.$name); } $GLOBALS['home_checks']++; }
$json = file_get_contents(getenv('SUITEEDGE_HOME_FIXTURE')); $export = (new SuiteEdge\Migrator\Import\DiviExportReader())->read_json($json);
$links = json_decode(file_get_contents(getenv('SUITEEDGE_HOME_LINKS')),true)['links'];
$GLOBALS['home_external_requests'] = 0;
$offline = function ($pre,$args,$url) { if (strpos($url,'elementor.com') !== false) { return $pre; } $GLOBALS['home_external_requests']++; return new WP_Error('offline_fixture','No external requests in embedded image pilot.'); };
add_filter('pre_http_request',$offline,20,3);
$source = (new SuiteEdge\Migrator\Import\SourceRepository())->save($json,'Home.json');
$repo = new SuiteEdge\Migrator\Jobs\JobRepository(); $runner = new SuiteEdge\Migrator\Jobs\BatchRunner($repo);
$job = new SuiteEdge\Migrator\Jobs\Job(); $job->settings = ['output_engine'=>'classic','dry_run'=>true,'assets'=>['reuse_library','import_external'],'source_origin'=>'https://suiteedge.com','link_map'=>$links];
$job->items = [['source_id'=>$source,'layout_index'=>0,'name'=>'Home.json','target_page_id'=>0,'status'=>'pending']]; $job->summary['total'] = 1; $repo->save($job);
$before = (int)$GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM {$GLOBALS['wpdb']->posts} WHERE post_type='attachment'"); $drafts = wp_count_posts('page')->draft;
home_check($runner->process_batch($job->id)['status'] === 'completed','real Home dry-run completes');
home_check((int)$GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM {$GLOBALS['wpdb']->posts} WHERE post_type='attachment'") === $before && wp_count_posts('page')->draft === $drafts,'real Home dry-run no page/media mutation');
$dry = $repo->get($job->id); $counts = [];
$walk = function ($nodes) use (&$walk,&$counts) { foreach ($nodes as $node) { if (strpos($node['settings']['_css_classes'] ?? '', 'suiteedge-font-carrier') !== false) { continue; } $type = $node['widgetType'] ?? $node['elType']; $counts[$type] = ($counts[$type] ?? 0)+1; $walk($node['elements']); } }; $walk($dry->items[0]['elementor_data']);
home_check(empty($counts['shortcode']),'no Divi fallback in mapped Home');
home_check(($counts['container'] ?? 0) === 107 && ($counts['text-editor'] ?? 0) === 80 && ($counts['image'] ?? 0) === 21 && ($counts['icon'] ?? 0) === 13 && ($counts['icon-box'] ?? 0) === 12 && ($counts['button'] ?? 0) === 4 && ($counts['video'] ?? 0) === 2,'all 239 source modules represented natively');
// Decode every actual embedded image, not a fabricated stand-in.
$imported = [];
foreach ($export['images'] as $url=>$asset) {
    $existing = (new SuiteEdge\Migrator\Assets\DuplicateDetector())->find_existing($url);
    $image = $existing ? ['id'=>$existing,'url'=>wp_get_attachment_url($existing)] : (new SuiteEdge\Migrator\Assets\EmbeddedImageImporter())->import($url,$asset,0);
    home_check(is_array($image) && get_post_type($image['id']) === 'attachment','embedded real image imported '.basename($url));
    $imported[$url] = $image['id'];
}
$job = new SuiteEdge\Migrator\Jobs\Job(); $job->settings = $dry->settings; $job->settings['dry_run'] = false; $job->items = $dry->items;
$job->items[0]['target_page_id'] = 0; $job->items[0]['status'] = 'pending'; unset($job->items[0]['elementor_data'],$job->items[0]['warnings']); $job->summary['total'] = 1; $repo->save($job);
$result = $runner->process_batch($job->id); home_check($result['status'] === 'completed','real Home target draft conversion: '.wp_json_encode($result));
$applied = $repo->get($job->id); $page = $applied->items[0]['target_page_id'];
home_check(get_post_status($page) === 'draft','pilot stays a draft');
$html = Elementor\Plugin::$instance->frontend->get_builder_content_for_display($page,true);
file_put_contents(ABSPATH.'../home-rendered.html',$html);
home_check(strpos($html,'A suite of software') !== false && strpos($html,'Get More Customers') !== false,'real Home frontend content rendered');
home_check((strpos($html,'fa-clock') !== false || strpos($html,'e-fas-clock') !== false) && (strpos($html,'fa-google') !== false || strpos($html,'e-fab-google') !== false),'real Home icon glyphs rendered');
home_check(strpos($html,'[et_pb_') === false && strpos($html,'@ET-DC@') === false,'no Divi shortcode or unresolved dynamic token rendered');
home_check(strpos($html,'suiteedge-content-mobile') !== false && strpos($html,'suiteedge-content-desktop') !== false,'real headline retains device variants');
home_check(strpos($html,'<figcaption') === false,'source image tooltips do not become visible captions');
home_check(strpos($html,'linear-gradient') !== false || strpos(file_get_contents(wp_upload_dir()['basedir'].'/elementor/css/post-'.$page.'.css'),'linear-gradient') !== false,'gradient CSS generated');
home_check($GLOBALS['home_external_requests'] === 0,'all pilot image imports used embedded data with no network request');
file_put_contents(ABSPATH.'../home-rendered.html',$html);
home_check($runner->rollback($job->id)['status'] === 'rolled_back','real Home pilot rollback');
file_put_contents(ABSPATH.'../home-pilot-results'.(defined('DB_ENGINE')?'-sqlite':'-mariadb').'.json',wp_json_encode(['assertions'=>$GLOBALS['home_checks'],'source_sha256'=>hash('sha256',$json),'modules'=>$counts,'embedded_images'=>count($imported),'page_id'=>$page,'job'=>$job->id,'database'=>$GLOBALS['wpdb']->db_version(),'warnings'=>$applied->items[0]['warnings'],'browser_visual_qa'=>false],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
remove_filter('pre_http_request',$offline,20);
echo 'PASS: '.$GLOBALS['home_checks'].' real Home pilot assertions; 239 modules; '.count($imported).' embedded images'.PHP_EOL;
