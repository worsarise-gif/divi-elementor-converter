<?php
namespace SuiteEdge\Migrator\Admin;

/** A two-step local workflow that never writes a target during conversion review. */
class ReversePage {
    public function render() {
        if(!current_user_can('manage_options')){wp_die(esc_html__('Unauthorized.','suiteedge-divi-elementor-migrator'));}
        $error='';$token='';$job=null;$source='';$target='create_new';$service=new \SuiteEdge\Migrator\Reverse\Service();
        if(isset($_POST['prepare_reverse'])||isset($_POST['convert_reverse'])){
            check_admin_referer('ace_reverse_page');
            try{
                if(isset($_POST['prepare_reverse'])){
                    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- selection() strictly validates the fixed selector and positive page ID; Source validates post type and edit permissions.
                    $source=$this->selection(wp_unslash($_POST['source_page']??''),wp_unslash($_POST['source_id']??''));
                    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Same strict page selection validator; only create_new is additionally accepted.
                    $target=$this->selection(wp_unslash($_POST['target_page']??''),wp_unslash($_POST['target_id']??''),true);
                    $token=$service->prepare($source,$target);
                }else{
                    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Service enforces an owner-bound UUID, expiry and single job ID under a session lock.
                    $job=$service->create(wp_unslash($_POST['reverse_session']??''));
                    (new \SuiteEdge\Migrator\Jobs\BatchRunner(new \SuiteEdge\Migrator\Jobs\JobRepository()))->process_batch($job->id);
                    $job=(new \SuiteEdge\Migrator\Jobs\JobRepository())->get($job->id);
                }
            }catch(\Throwable $e){$error=$e->getMessage();}
        }
        View::start(__('Elementor to Divi','suiteedge-divi-elementor-migrator'),__('Choose an Elementor page and a Divi destination. Convert for review, then apply the saved result after checking its coverage report. No JSON upload is needed.','suiteedge-divi-elementor-migrator'),'reverse');
        if($error){echo '<div class="notice notice-error" role="alert"><p>'.esc_html($error).'</p></div>';}
        if($job){
            $outcome=(new \SuiteEdge\Migrator\Reports\JobOutcome())->describe($job);
            echo '<section class="suiteedge-panel" aria-live="polite"><h2>'.esc_html($outcome['status_label']).'</h2><p>'.esc_html($outcome['message']).'</p><p><a class="button button-primary" href="'.esc_url(add_query_arg('job',$job->id,View::url('-reports'))).'">'.esc_html__('Review conversion report','suiteedge-divi-elementor-migrator').'</a> <a class="button" href="'.esc_url(View::url('-jobs')).'">'.esc_html__('Apply saved result in Migration Jobs','suiteedge-divi-elementor-migrator').'</a></p></section>';
        }
        if($token){
            echo '<form method="post" class="suiteedge-panel"><h2>'.esc_html__('Review your page assignment','suiteedge-divi-elementor-migrator').'</h2><p>'.esc_html(get_the_title((int)$source).' (#'.$source.')').' &rarr; '.esc_html($target==='create_new'?__('New Divi page draft','suiteedge-divi-elementor-migrator'):get_the_title((int)$target).' (#'.$target.')').'</p><p>'.esc_html__('Conversion saves a report and Divi output. It does not modify either page. Unsupported widgets, settings and behaviors block Apply and are listed with their element paths. The selection expires after one hour.','suiteedge-divi-elementor-migrator').'</p>';
            wp_nonce_field('ace_reverse_page');echo '<input type="hidden" name="reverse_session" value="'.esc_attr($token).'">';submit_button(__('Convert for review','suiteedge-divi-elementor-migrator'),'primary','convert_reverse');echo '</form>';
        }
        $pages=get_posts(['post_type'=>'page','post_status'=>['publish','draft','private','pending','future'],'numberposts'=>100,'orderby'=>'ID','order'=>'DESC','update_post_meta_cache'=>true]);$reader=new \SuiteEdge\Migrator\Reverse\Source();
        echo '<form method="post" class="suiteedge-panel" id="ace-reverse-page"><h2>'.esc_html__('Source and destination','suiteedge-divi-elementor-migrator').'</h2>';wp_nonce_field('ace_reverse_page');echo '<div class="suiteedge-form-grid">';
        foreach(['source','target'] as $side){
            $value=$side==='source'?$source:$target;$list=$side==='source'?array_filter($pages,[$reader,'available']):$pages;
            echo '<div><div class="suiteedge-field"><label for="ace-reverse-'.esc_attr($side).'">'.esc_html($side==='source'?__('Source Elementor page','suiteedge-divi-elementor-migrator'):__('Target Divi page','suiteedge-divi-elementor-migrator')).'</label><select id="ace-reverse-'.esc_attr($side).'" name="'.esc_attr($side).'_page" required><option value="">'.esc_html__('Choose a page','suiteedge-divi-elementor-migrator').'</option>';
            if($side==='target'){echo '<option value="create_new"'.selected($value,'create_new',false).'>'.esc_html__('Create a new Divi page draft','suiteedge-divi-elementor-migrator').'</option>';}
            foreach($list as $page){if(current_user_can('edit_post',$page->ID)){echo '<option value="'.(int)$page->ID.'"'.selected((string)$value,(string)$page->ID,false).'>'.esc_html($page->post_title.' (#'.$page->ID.' / '.$page->post_status.')').'</option>';}}
            echo '<option value="by_id">'.esc_html__('Choose another page by ID','suiteedge-divi-elementor-migrator').'</option></select></div><div class="suiteedge-field"><label for="ace-reverse-'.esc_attr($side).'-id">'.esc_html__('Page ID (for another page by ID)','suiteedge-divi-elementor-migrator').'</label><input id="ace-reverse-'.esc_attr($side).'-id" name="'.esc_attr($side).'_id" type="text" inputmode="numeric" pattern="[1-9][0-9]*"></div></div>';
        }
        echo '</div><p class="suiteedge-note">'.esc_html__('The 100 newest editable pages are listed. Use an ID for an older page. Choose the source itself only for an in-place migration. New destinations remain drafts; existing title, URL, status and SEO are preserved.','suiteedge-divi-elementor-migrator').'</p>';submit_button(__('Select pages','suiteedge-divi-elementor-migrator'),'primary','prepare_reverse');echo '</form>';
        echo '<section class="suiteedge-panel"><h2>'.esc_html__('Before you apply','suiteedge-divi-elementor-migrator').'</h2><p>'.esc_html__('Apply requires an active Divi 4 theme or Divi 4 Builder plugin. Classic Elementor layouts are supported within the coverage below. Atomic elements, nested specialty layouts, Pro form delivery, loop templates and unsupported dynamic behavior remain protected. Theme Builder assignments and sitewide styles need separate review.','suiteedge-divi-elementor-migrator').'</p><details><summary>'.esc_html__('Widget conversion coverage','suiteedge-divi-elementor-migrator').'</summary><div class="suiteedge-table-scroll"><table class="widefat striped"><thead><tr><th>'.esc_html__('Elementor widget','suiteedge-divi-elementor-migrator').'</th><th>'.esc_html__('Divi mapping or required review','suiteedge-divi-elementor-migrator').'</th></tr></thead><tbody>';
        foreach((new \SuiteEdge\Migrator\Reverse\Widgets(new \SuiteEdge\Migrator\Reverse\Diagnostics()))->catalog() as $widget=>$mapping){echo '<tr><td>'.esc_html($widget).'</td><td>'.esc_html($mapping).'</td></tr>';}
        echo '</tbody></table></div></details></section></div>';
    }
    private function selection($value,$override,$new=false) { if($new&&$value==='create_new'){return $value;}return (string)(new \SuiteEdge\Migrator\Import\SitePageSource())->id($value==='by_id'?$override:$value); }
}
