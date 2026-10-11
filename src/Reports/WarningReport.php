<?php
namespace SuiteEdge\Migrator\Reports;
class WarningReport {
    public function build(\SuiteEdge\Migrator\Jobs\Job $job) {
        $warnings = [];
        foreach ($job->items as $item) { foreach ($item['warnings'] ?? [] as $warning) { $warnings[] = ['source'=>$item['name'],'warning'=>$warning]; } }
        return $warnings;
    }
    /** Backward compatible: old jobs retain their raw counts and original messages. */
    public function diagnostics(\SuiteEdge\Migrator\Jobs\Job $job) {
        $entries = []; $counts = ['error'=>0,'warning'=>0,'info'=>0,'blocking'=>0];
        foreach ($job->items as $item) {
            $reverse_issues=[];foreach($item['divi_data']['issues']??[] as $issue){$reverse_issues['Elementor to Divi ['.$issue['path'].']: '.$issue['message']]=$issue;}
            foreach (['warnings'=>'warning','information'=>'info','error'=>'error'] as $field=>$severity) {
                $messages = $field === 'error' ? (empty($item[$field]) ? [] : [$item[$field]]) : ($item[$field] ?? []);
                foreach (array_unique($messages) as $message) {
                    $entry = $this->classify((string)$message,$severity);
                    if(isset($reverse_issues[$message])){$issue=$reverse_issues[$message];$entry['code']=$issue['code'];$entry['path']=$issue['path'];$entry['blocking']=$issue['blocking'];if($issue['blocking']){$entry['severity']='blocking';}}
                    $entry['source'] = $item['name'] ?? ''; $entry['target_page_id'] = (int)($item['target_page_id'] ?? 0);
                    $entries[] = $entry; $counts[$entry['severity']]++;
                }
            }
        }
        foreach ($job->summary['rollback_errors'] ?? [] as $message) {
            $entry = $this->classify((string)$message,'error'); $entry['source'] = 'Rollback'; $entry['target_page_id'] = 0;
            $entries[] = $entry; $counts['error']++;
        }
        return ['counts'=>$counts,'entries'=>$entries];
    }
    private function classify($message,$severity) {
        $code = 'conversion_review'; $action = 'Inspect the affected module in Elementor and compare it with the source before publishing.';
        if (preg_match('/^et_pb_[a-z_]+: supplemental scoped CSS stored in an editable Elementor HTML widget; review selectors and fonts visually\.$/D',$message)) {
            $severity = 'info'; $code = 'portable_styles'; $action = 'Keep the conversion styles widget. Compare fonts and responsive layout after insertion.';
        } elseif (strpos($message,'Export scope:') === 0) {
            $severity = 'info'; $code = 'export_scope'; $action = 'Compare the converted page body with the source. Export the original Divi Theme Builder header and footer separately, use Headers & Footers to map them to UAE templates, and review destination display conditions and overlays. These sitewide settings are not contained in a page-only JSON export.';
        } elseif (strpos($message,'Source attachment link preserved:') === 0) {
            $severity = 'info'; $code = 'source_attachment_dependency'; $action = 'The original attachment permalink is preserved. Image import does not copy this document. Upload it separately and map its source attachment ID to the destination URL if the source site will be removed, then reconvert.';
        } elseif (strpos($message,'Post layout scope:')===0) {
            $severity='info';$code='post_layout_scope';$action='Verify the selected WordPress post. Existing post details are preserved; new drafts need their author, dates, excerpt, categories, tags and featured image configured before publishing. Layout JSON does not import the post database or Theme Builder conditions.';
        } elseif (strpos($message,'Core Blog:')!==false && strpos($message,'categor')!==false) {
            $code='blog_category_mapping';$action='Import/create the destination categories, select each source ID in Blog category mappings, then reconvert. Do not copy source category IDs. Deleted mapped categories require a new analysis; Apply cannot repair an old saved result.';
        } elseif (strpos($message,'Core Blog:')!==false) {
            $code='core_blog_configuration';$action='Review the protected Blog query or style. Ordinary published-post lists/masonry and excerpts are supported. Full-content, archive and other post types require an explicitly configured Elementor template replacement before reconversion.';
        } elseif ($severity === 'info') {
            $code = strpos($message,'disabled') !== false ? 'inactive_source_setting' : (strpos($message,'parallax') !== false || strpos($message,'scroll motion') !== false ? 'portable_motion' : (strpos($message,'tooltip') !== false ? 'image_tooltip' : 'conversion_info'));
            $action = 'No repair required. Confirm the resulting appearance against the source.';
        } elseif (strpos($message,'Font "') === 0 && strpos($message,'not registered in Elementor') !== false) {
            $code = 'font_registration_required'; $action = 'Install the matching font files under the same family name in Elementor Custom Fonts or a compatible font plugin. Divi layout JSON does not contain custom font binaries. Reconvert after registration and compare the header on all devices.';
        } elseif (strpos($message,'Google font loading is disabled') === 0) {
            $code = 'font_loading_disabled'; $action = 'Review Elementor Settings > Advanced Google Fonts loading, or register matching locally hosted fonts. The migrator respects this setting.';
        } elseif (strpos($message,'Elementor to Divi [') === 0) {
            $code='reverse_conversion_review';$action='Review the element path, cause and setting disposition. Activate/restore a missing provider or request a tested adapter for an unsupported active control. Retain valid source styles. Reconvert after the repair; Apply uses saved output and cannot repair an old result.';
        } elseif (strpos($message,'unmapped setting') !== false) {
            $code = 'unmapped_setting'; $action = 'This setting was not applied. Configure its equivalent in Elementor or disable it in Divi and export again.';
        } elseif (strpos($message,'Published target is protected') !== false) {
            $code = 'published_target_guard'; $action = 'Run Analyze only, review each actionable warning, then select Apply to pages and explicitly acknowledge reviewed differences.';
        } elseif (strpos($message,'Source menu design:') === 0 || strpos($message,'Source menu icon retained remotely') === 0) {
            $code='menu_design_review';$action='Review the destination menu labels/hierarchy in Appearance > Menus and the source origin/icon import options. Header/Footer JSON does not include WordPress menu-label icons or menu entries.';
        } elseif (strpos($message,'UAE') !== false && $severity==='error') {
            $code='uae_target_guard';$action='Activate UAE and select a template of the correct Header/Footer type. If its display rules or content changed after analysis, create a fresh analysis; newer assignments are protected.';
        } elseif (strpos($message,'destination') !== false && strpos($message,'menu') !== false) {
            $code = 'menu_mapping_required'; $action = 'Select the correct populated destination menu in Navigation menu mappings and run a fresh analysis. Source menu IDs do not include links and are not destination IDs.';
        } elseif (strpos($message,'Unsupported third-party Divi module') !== false) {
            $code = 'third_party_module_unsupported'; $action = 'Build and save an Elementor container template with an installed post-grid widget (UAE Basic Posts or Elementor Pro Posts / Loop Grid). Configure its destination post query, pagination and responsive design. Select it in Unsupported module replacements for this source file and run a fresh conversion. The original module remains protected until you choose a replacement.';
        } elseif (strpos($message,'Post grid has no matching destination') === 0) {
            $code = 'post_grid_empty'; $action = 'Migrate or publish destination blog posts with featured images/categories, or edit the Ace Post Grid query. A Divi page JSON contains layout settings, not the post database. This does not block applying the layout.';
        } elseif (strpos($message,'Ace Post Grid requires Ace Migrator') !== false) {
            $code = 'post_grid_runtime_required'; $action = 'Keep Ace Migrator active to render this editable Elementor widget. Verify the destination published posts, query, pagination and responsive card design. Layout JSON does not import source posts; a configured native template replacement can be used instead.';
        } elseif (strpos($message,'Post grid:') !== false) {
            $code = 'post_grid_configuration'; $action = 'Use an explicitly configured native Elementor template replacement for unsupported grid layouts or source category-ID queries. Verify its destination category slugs and post ordering, then reconvert. Do not reuse numeric source IDs.';
        } elseif (strpos($message,'Explicit Elementor template replacement applied') !== false) {
            $code = 'module_replacement_review'; $action = 'Compare the replacement with the original module. Verify destination posts, categories, pagination and all devices. Keep its widget provider active. The selected template design replaces the source module design; it does not migrate source blog posts.';
        } elseif (strpos($message,'Module replacement:') !== false) {
            $code = 'module_replacement_invalid'; $action = 'Check that the selected saved Elementor template exists, is editable, and contains available native widgets with configured form actions. Replace unresolved shortcodes or nested template references, then run a fresh conversion.';
        } elseif (strpos($message,'Dynamic link binding') !== false) {
            $code = 'dynamic_link_binding_unsupported'; $action = 'Review the original token and URL field. Only complete, supported page, post and attachment link bindings are resolved automatically; other dynamic values retain protected fallback. Correct the source and run a fresh conversion.';
        } elseif (strpos($message,'Dynamic binding') !== false && preg_match('/for source (attachment|post) [0-9]+/',$message,$link_type)) {
            $code = 'dynamic_'.$link_type[1].'_link_unresolved'; $action = 'Enter the original source site URL and enable public link resolution, or map the reported source item ID to its target URL. Check link_resolution for media/posts REST failures. Run a fresh conversion; Apply cannot repair old saved output.';
        } elseif (strpos($message,'Dynamic binding') !== false && strpos($message,'target URL mapping') !== false) {
            $code = 'dynamic_page_link_unresolved'; $action = 'Enter the original Divi source URL, enable public link resolution, or map the source page ID in Advanced link mappings. Check the page link_resolution entries for REST failures. Run a fresh conversion; an old saved result cannot be repaired by Apply.';
        } elseif (strpos($message,'Original shortcode preserved') !== false || strpos($message,'Conversion blocked') !== false) {
            $code = 'unsupported_conversion'; $action = 'Resolve the unsupported module, dynamic binding or form delivery configuration before applying. A preserved shortcode still requires Divi.';
        } elseif (strpos($message,'Internal link kept on source site because no target page resolves:') === 0) {
            $code = 'internal_link_unresolved'; $action = 'Create or migrate the matching destination page and verify its slug or link mapping, then reconvert. The source URL is retained until a destination resolves.';
        } elseif (stripos($message,'image') !== false || stripos($message,'media') !== false) {
            $code = 'asset_review'; $action = 'Check image import/reuse settings, attachment permissions and the source URL; rerun analysis after correcting the asset.';
        } elseif ($severity === 'error') {
            $code = 'conversion_error'; $action = 'Correct the reported cause, then create a new analysis job. For interrupted writes, inspect the backup and use the explicit recovery action.';
        }
        return ['severity'=>$severity,'code'=>$code,'cause'=>$message,'action'=>$action];
    }
}
