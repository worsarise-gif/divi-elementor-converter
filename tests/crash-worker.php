<?php
/** Process fixture only: receives disposable WP path, job UUID, stage and marker path. */
require rtrim($argv[1],'/\\').'/wp-load.php';
wp_set_current_user(get_user_by('login','qa_admin')->ID);
$job_id = $argv[2]; $stage = $argv[3]; $marker = $argv[4];
$pause = function () use ($marker) { file_put_contents($marker,'ready'); while (true) { usleep(100000); } };
if ($stage === 'partial') {
    add_filter('update_post_metadata',function ($pre,$id,$key) use ($pause) { if ($key === '_elementor_edit_mode') { $pause(); } return $pre; },10,3);
} elseif ($stage === 'written') {
    add_filter('pre_update_option_suiteedge_job_'.$job_id,function ($value) use ($pause) { if ($value['current_index'] === 1) { $pause(); } return $value; });
} elseif ($stage === 'lock') {
    $lock = new SuiteEdge\Migrator\Jobs\LockManager(); $lock->acquire('crash-probe'); $pause();
}
(new SuiteEdge\Migrator\Jobs\BatchRunner(new SuiteEdge\Migrator\Jobs\JobRepository()))->process_batch($job_id);
