<?php
namespace SuiteEdge\Migrator\Admin;
class ImportPage {
    public function render($theme_builder = false) {
        if (!current_user_can('manage_options')) { wp_die('Unauthorized.'); }
        $errors = []; $pending = [];
        if (isset($_POST['submit_upload'])) {
            check_admin_referer('suiteedge_import');
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- UploadValidator checks is_uploaded_file, extension, size and Divi JSON before use; WordPress upload tmp paths must stay intact.
            $files = $_FILES['divi_exports'] ?? []; $names = $files['name'] ?? [];
            if (!is_array($names) || count($names) > 24) { $errors[] = 'Select at most 24 JSON files.'; $names = []; }
            foreach ($names as $i=>$name) {
                try {
                    $file = ['name'=>$name,'tmp_name'=>$files['tmp_name'][$i] ?? '', 'error'=>$files['error'][$i] ?? -1];
                    $json = (new \SuiteEdge\Migrator\Import\UploadValidator())->validate($file);
                    $export = (new \SuiteEdge\Migrator\Import\DiviExportReader())->read_json($json);
                    foreach ($export['layouts'] as $layout) { (new \SuiteEdge\Migrator\Divi\ShortcodeTokenizer())->tokenize($layout['post_content']); }
                    $id = (new \SuiteEdge\Migrator\Import\SourceRepository())->save($json,$name);
                    foreach ($export['layouts'] as $index=>$layout) { $pending[] = ['source_id'=>$id,'layout_index'=>$index,'name'=>sanitize_file_name($name) . ' / ' . $layout['title']]; }
                } catch (\Throwable $e) { $errors[] = (string)$name . ': ' . $e->getMessage(); }
            }
            if (!$pending && !$errors) { $errors[] = 'Select at least one JSON export.'; }
            set_transient('suiteedge_migrator_pending_files_' . get_current_user_id(),$pending,HOUR_IN_SECONDS);
        }
        View::start($theme_builder ? __('Header & Footer Migrator','suiteedge-divi-elementor-migrator') : __('Import & Scan Divi Exports','suiteedge-divi-elementor-migrator'),__('Start with a Divi 4 portability JSON export. Upload and scan before choosing a destination.','suiteedge-divi-elementor-migrator'),$theme_builder ? 'templates' : 'pages',1);
        echo '<section class="suiteedge-panel"><h2>'.esc_html__('Prepare your export','suiteedge-divi-elementor-migrator').'</h2><p>'.esc_html($theme_builder ? __('In Divi Theme Builder, export each Header or Footer layout separately using Portability. Activate UAE on this destination site to apply the result to a Header or Footer template.','suiteedge-divi-elementor-migrator') : __('Open your source page or blog post in Divi Builder, choose Portability > Export, and save the layout as JSON. Upload several files to migrate a batch. On the mapping screen, choose Pages or Blog Posts explicitly. Import existing post records through WordPress first when moving sites; a layout JSON does not contain the post database.','suiteedge-divi-elementor-migrator')).'</p><p><a href="'.esc_url(View::url('-guide').'#quick-start').'">'.esc_html__('See the migration instructions','suiteedge-divi-elementor-migrator').' &rarr;</a></p></section>';
        if (!$theme_builder) { echo '<p><a class="button" href="'.esc_url(View::url('-site-page')).'">'.esc_html__('Convert a page already on this site','suiteedge-divi-elementor-migrator').'</a> '.esc_html__('Choose its source and destination without exporting JSON.','suiteedge-divi-elementor-migrator').'</p>'; }
        if ($theme_builder) { echo '<p>Export each global Header/Footer from Divi Theme Builder, upload it here, then map it to an existing UAE Header/Footer template or a new draft. Existing UAE display rules are preserved. New drafts have no display rules until you configure them in UAE.</p>'; }
        foreach ($errors as $error) { echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>'; }
        echo '<form method="post" enctype="multipart/form-data" class="suiteedge-panel"><h2>'.esc_html__('Upload Divi layouts','suiteedge-divi-elementor-migrator').'</h2>'; wp_nonce_field('suiteedge_import');
        echo '<div class="suiteedge-field"><label for="suiteedge-exports">'.esc_html__('Choose JSON exports','suiteedge-divi-elementor-migrator').'</label><input id="suiteedge-exports" type="file" name="divi_exports[]" accept=".json" multiple required aria-describedby="suiteedge-upload-help"><p id="suiteedge-upload-help" class="description">'.esc_html__('Up to 24 files, 20 MB per file. Hosting limits may be lower. Files are validated and stored privately in the database. Scanning does not change pages or import media.','suiteedge-divi-elementor-migrator').'</p></div>'; submit_button(__('Upload and Scan','suiteedge-divi-elementor-migrator'),'primary','submit_upload'); echo '</form>';
        $pending = get_transient('suiteedge_migrator_pending_files_' . get_current_user_id());
        if ($pending) { echo '<p>' . count($pending) . ' layouts ready for analysis and mapping.</p><a class="button" href="?page=suiteedge-migrator-analysis'.($theme_builder ? '&amp;templates=uae' : '').'">Analyze only</a> <a class="button button-primary" href="?page='.($theme_builder ? 'suiteedge-migrator-template-mapping' : 'suiteedge-migrator-mapping').'">'.($theme_builder ? 'Map Header / Footer templates' : 'Map pages / posts').'</a>'; }
        echo '</div>';
    }
}
