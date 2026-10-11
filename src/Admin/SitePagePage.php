<?php
namespace SuiteEdge\Migrator\Admin;

/** Choose local pages; the shared mapping screen handles conversion settings. */
class SitePagePage {
    public function render() {
        if (!current_user_can('manage_options')) { wp_die(esc_html__('Unauthorized.','suiteedge-divi-elementor-migrator')); }
        $error='';$token='';$source='';$target='create_new';
        if (isset($_POST['prepare_site_page'])) {
            check_admin_referer('suiteedge_site_page');
            try {
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- All selections pass the fixed selector/positive numeric ID allowlist in selection() before database access.
                $source=$this->selection(wp_unslash($_POST['source_page']??''),wp_unslash($_POST['source_page_id']??''));
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Same strict selector/ID validation; no HTML or arbitrary query enters the page reader.
                $target=$this->selection(wp_unslash($_POST['target_page']??''),wp_unslash($_POST['target_page_id']??''),true);
                $token=(new \SuiteEdge\Migrator\Import\SitePageSelection())->prepare($source,$target);
            } catch(\Throwable $e) { $error=$e->getMessage(); }
        }
        View::start(__('Convert a Site Page','suiteedge-divi-elementor-migrator'),__('Choose a Divi 4 page on this WordPress site and the page that should receive its Elementor layout. No export or upload is needed.','suiteedge-divi-elementor-migrator'),'site-pages',1,$this->steps());
        if ($error) { echo '<div class="notice notice-error" role="alert"><p>'.esc_html($error).'</p></div>'; }
        if ($token!=='') {
            $files=(new \SuiteEdge\Migrator\Import\SitePageSelection())->files($token);
            $record=(new \SuiteEdge\Migrator\Import\SourceRepository())->get($files[0]['source_id']);
            echo '<section class="suiteedge-panel"><h2>'.esc_html__('Your pages are selected','suiteedge-divi-elementor-migrator').'</h2><p>'.esc_html($record['site_page']['source_page_title'].' / Source #'.$source).' &rarr; '.esc_html($target==='create_new'?__('New Elementor page draft','suiteedge-divi-elementor-migrator'):get_the_title((int)$target).' / Target #'.$target).'</p><p>'.esc_html__('A private source copy is ready for review. Neither page has been changed. Check source theme defaults and suggested local menu/category mappings, then analyze or apply. Changes to the source after selection require a fresh selection.','suiteedge-divi-elementor-migrator').'</p><a class="button button-primary" href="'.esc_url(add_query_arg('site_session',$token,View::url('-mapping'))).'">'.esc_html__('Review settings and convert','suiteedge-divi-elementor-migrator').'</a></section>';
        }
        $pages=get_posts(['post_type'=>'page','post_status'=>['publish','draft','private','pending','future'],'numberposts'=>100,'orderby'=>'ID','order'=>'DESC','update_post_meta_cache'=>true]);
        $reader=new \SuiteEdge\Migrator\Import\SitePageSource();$sources=array_filter($pages,[$reader,'available']);
        echo '<form method="post" class="suiteedge-panel" id="suiteedge-site-page"><h2>'.esc_html__('Source and destination','suiteedge-divi-elementor-migrator').'</h2>';wp_nonce_field('suiteedge_site_page');
        echo '<div class="suiteedge-form-grid">';
        foreach (['source'=>$sources,'target'=>$pages] as $side=>$list) {
            $value=$side==='source'?$source:$target;
            echo '<div><div class="suiteedge-field"><label for="suiteedge-'.esc_attr($side).'-page">'.esc_html($side==='source'?__('Source Divi page','suiteedge-divi-elementor-migrator'):__('Target Elementor page','suiteedge-divi-elementor-migrator')).'</label><select id="suiteedge-'.esc_attr($side).'-page" name="'.esc_attr($side).'_page" required aria-describedby="suiteedge-'.esc_attr($side).'-help"><option value="">'.esc_html__('Choose a page','suiteedge-divi-elementor-migrator').'</option>';
            if ($side==='target') { echo '<option value="create_new"'.selected($value,'create_new',false).'>'.esc_html__('Create a new page draft','suiteedge-divi-elementor-migrator').'</option>'; }
            foreach($list as $page) { if(current_user_can('edit_post',$page->ID)){echo '<option value="'.(int)$page->ID.'"'.selected((string)$value,(string)$page->ID,false).'>'.esc_html($page->post_title.' (#'.$page->ID.' / '.$page->post_status.')').'</option>';}}
            echo '<option value="by_id"'.selected(is_string($value)&&ctype_digit($value)&&!in_array((int)$value,wp_list_pluck($list,'ID'),true)?'by_id':$value,'by_id',false).'>'.esc_html__('Choose another page by ID','suiteedge-divi-elementor-migrator').'</option></select><p class="description" id="suiteedge-'.esc_attr($side).'-help">'.esc_html($side==='source'?__('Supported Divi pages among the 100 newest pages are listed. Use a page ID for an older source. Divi 5 and active Elementor sources are excluded.','suiteedge-divi-elementor-migrator'):__('Only the selected destination receives the converted layout. Choose the source itself for an in-place migration, or another page to keep its Divi rendering.','suiteedge-divi-elementor-migrator')).'</p></div><div class="suiteedge-field"><label for="suiteedge-'.esc_attr($side).'-id">'.esc_html__('Page ID (only for “another page by ID”)','suiteedge-divi-elementor-migrator').'</label><input id="suiteedge-'.esc_attr($side).'-id" name="'.esc_attr($side).'_page_id" type="text" inputmode="numeric" pattern="[1-9][0-9]*" value="'.esc_attr(ctype_digit((string)$value)?$value:'').'" aria-describedby="suiteedge-'.esc_attr($side).'-help"></div></div>';
        }
        echo '</div><p class="suiteedge-note" id="suiteedge-in-place" role="status">'.esc_html__('Analysis changes neither page. Apply replaces the target’s builder layout with a backup; its title, URL, publication state and SEO are preserved. A new destination stays a draft.','suiteedge-divi-elementor-migrator').'</p>';
        submit_button(__('Select pages and review settings','suiteedge-divi-elementor-migrator'),'primary','prepare_site_page');echo '</form>';
        if (!$sources) { echo '<p role="status">'.esc_html__('No supported Divi sources were found among the newest pages. Enter an older source page ID, or import a portability export through Pages & Posts.','suiteedge-divi-elementor-migrator').'</p>'; }
        echo '<p>'.esc_html__('This converts page content. Theme Builder headers/footers, global display conditions and sitewide custom CSS need their separate workflows. Review inherited fonts and theme defaults before Apply.','suiteedge-divi-elementor-migrator').'</p></div>';
    }
    public function steps() { return [__('Choose pages','suiteedge-divi-elementor-migrator'),__('Settings & convert','suiteedge-divi-elementor-migrator'),__('Review results','suiteedge-divi-elementor-migrator'),__('Apply & verify','suiteedge-divi-elementor-migrator')]; }
    private function selection($value,$override,$new = false) {
        if ($new && $value==='create_new') { return $value; }
        return (string)(new \SuiteEdge\Migrator\Import\SitePageSource())->id($value==='by_id'?$override:$value);
    }
}
