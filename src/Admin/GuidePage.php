<?php
namespace SuiteEdge\Migrator\Admin;
class GuidePage {
    public function render() {
        if (!current_user_can('manage_options')) { wp_die(esc_html__('You do not have permission to access this page.','suiteedge-divi-elementor-migrator')); }
        View::start(__('User Guide','suiteedge-divi-elementor-migrator'),__('A practical guide to preparing, converting, reviewing, and applying your layouts.','suiteedge-divi-elementor-migrator'),'guide');
        $sections = [
            'quick-start'=>__('Quick start','suiteedge-divi-elementor-migrator'),
            'site-pages'=>__('Convert a site page','suiteedge-divi-elementor-migrator'),
            'reverse'=>__('Elementor to Divi','suiteedge-divi-elementor-migrator'),
            'headers-footers'=>__('Headers & footers','suiteedge-divi-elementor-migrator'),
            'settings-guide'=>__('Choosing settings','suiteedge-divi-elementor-migrator'),
            'results'=>__('Understanding results','suiteedge-divi-elementor-migrator'),
            'troubleshooting'=>__('Troubleshooting','suiteedge-divi-elementor-migrator'),
            'requirements'=>__('Requirements & scope','suiteedge-divi-elementor-migrator'),
            'data-privacy'=>__('Your data & privacy','suiteedge-divi-elementor-migrator'),
        ];
        echo '<div class="suiteedge-guide-grid"><nav class="suiteedge-guide-toc" aria-label="'.esc_attr__('Guide contents','suiteedge-divi-elementor-migrator').'">';
        foreach ($sections as $id=>$title) { echo '<a href="#'.esc_attr($id).'">'.esc_html($title).'</a>'; }
        echo '</nav><div class="suiteedge-guide">';
        self::section('quick-start',$sections['quick-start'],[
            __('1. Prepare on staging. Back up the entire site. Activate Elementor and keep your Divi source available for comparison.','suiteedge-divi-elementor-migrator'),
            __('2. Export from Divi 4. Open the page in Divi Builder, choose Portability, then Export. Save the layout as JSON. A page export contains page content; it does not include global Theme Builder templates.','suiteedge-divi-elementor-migrator'),
            __('3. Upload in Pages. Select up to 24 JSON files, then Upload and Scan. Each file may contain several layouts. Review module coverage if you use add-on modules.','suiteedge-divi-elementor-migrator'),
            __('4. Map and convert. Select layouts and assign each one to a distinct destination page or a new draft. Check the source origin and theme defaults. Convert Selected saves a result for review; it changes neither the assigned page nor the Media Library. Image import happens during Apply when enabled.','suiteedge-divi-elementor-migrator'),
            __('5. Review the report. Fix blocking errors and review warnings. Download an Elementor template if you prefer a manual import. Template import does not apply content to the page selected in this plugin.','suiteedge-divi-elementor-migrator'),
            __('6. Apply the saved result. In Migration Jobs, use Apply to Page / Post or select completed results and use Apply Selected. The existing job and original destination are reused. A published target with warnings requires your review acknowledgement.','suiteedge-divi-elementor-migrator'),
            __('7. Verify before publishing. Open the target in Elementor. Check desktop, tablet, and mobile; test links, menus, accordions, buttons, images, and forms. New targets stay as drafts until you publish them.','suiteedge-divi-elementor-migrator'),
        ]);
        self::section('site-pages',$sections['site-pages'],[
            __('Open Convert Site Page. Select a Divi 4 source page and a target page on this WordPress site, or create a new draft. You can explicitly choose the source itself for in-place migration. Older pages can be selected by ID.','suiteedge-divi-elementor-migrator'),
            __('Select pages and review settings captures the page content, stored Divi 4 presets and readable referenced Divi Library globals privately. It does not change either page. Each selection lasts one hour and can create one job; separate browser tabs have separate selections.','suiteedge-divi-elementor-migrator'),
            __('Review the selected destination, verified local menu/category suggestions, dynamic link mappings and inherited Source theme defaults. These defaults are not a complete import of your Divi Theme Customizer or sitewide custom CSS.','suiteedge-divi-elementor-migrator'),
            __('Convert for review saves the result and report without changing the target. In Migration Jobs, Apply to Page uses that result on the same job. Convert and apply to page converts and writes directly after validation. Apply preserves the target title, URL, status and SEO and saves a rollback backup.','suiteedge-divi-elementor-migrator'),
            __('If the source, stored presets or referenced globals change after selection, applying is blocked. Select the pages again for a fresh conversion. New target edits are also protected. Rollback restores migration-owned builder fields; source content, media and drafts are retained.','suiteedge-divi-elementor-migrator'),
            __('Convert Site Page handles Divi 4 to Elementor. Use the separate Elementor to Divi screen for reverse page conversion. Divi 5 and whole-site theme migration are outside these workflows. Use Headers & Footers for Divi Theme Builder exports.','suiteedge-divi-elementor-migrator'),
        ]);
        self::section('reverse',$sections['reverse'],[
            __('Open Elementor to Divi, select a current Elementor page and a Divi destination or a new draft, then review the assignment. No JSON upload is required.','suiteedge-divi-elementor-migrator'),
            __('Convert for review saves Divi output and per-element coverage without changing either page. Resolve blockers, then use the saved Apply action in Migration Jobs. Apply requires Divi 4 and every needed module provider. Backups and rollback cover the written content and builder state.','suiteedge-divi-elementor-migrator'),
            __('Unchanged local forward migrations created with 1.7.0 can restore their exact original Divi source while the recorded Elementor/global/source dependencies still match. Other pages use semantic adapters. Unsupported widgets/settings, nested layouts, Pro form delivery and loops remain protected. Accept native Divi editing, fonts and responsive rendering on staging before publishing.','suiteedge-divi-elementor-migrator'),
        ]);
        self::section('headers-footers',$sections['headers-footers'],[
            __('Export each Header or Footer layout separately from Divi Theme Builder. Open Headers & Footers here, upload the JSON, and choose Map Header / Footer templates.','suiteedge-divi-elementor-migrator'),
            __('Ultimate Addons for Elementor (UAE, formerly Elementor Header & Footer Builder) must be active. Choose an existing UAE Header or Footer, or create a draft of the matching type. Regular pages are separate destinations.','suiteedge-divi-elementor-migrator'),
            __('Divi JSON contains menu IDs, not menu links. Create or import the destination menu in Appearance > Menus, then select it in Navigation menu mappings. Review any Main Menu suggestion.','suiteedge-divi-elementor-migrator'),
            __('Optional source menu image lookup reads the public source header/footer to restore omitted menu-label images. It reports differences in labels or hierarchy and never rewrites your shared WordPress menu.','suiteedge-divi-elementor-migrator'),
            __('Convert, review, then Apply to UAE Template. Existing display rules, exclusions, and roles are preserved. New drafts have no assignments: configure their UAE display conditions and publish them before they appear on your site.','suiteedge-divi-elementor-migrator'),
        ]);
        self::section('settings-guide',$sections['settings-guide'],[
            __('Import images / Reuse Media Library: embedded images are tried first, then allowed external URLs. Exact matches can be reused. Imported attachments are retained after rollback. Leave both enabled for a typical migration.','suiteedge-divi-elementor-migrator'),
            __('Source site URL: enter the original Divi site, for example https://source.example. A pasted page URL is normalized to its scheme and host. Check suggestions inferred from media URLs. Entering a URL enables public page-link lookup unless you explicitly switch it off.','suiteedge-divi-elementor-migrator'),
            __('Resolve dynamic links: optionally query the public source WordPress REST API to translate supported Divi page, post and attachment links. If the API is unavailable, provide explicit source item ID to destination URL mappings in Advanced link mappings.','suiteedge-divi-elementor-migrator'),
            __('Source theme defaults: Divi exports can omit Theme Customizer fonts, colors, and row width. Match these defaults to your source site. Explicit module settings take precedence.','suiteedge-divi-elementor-migrator'),
            __('Use recommended settings restores image/menu options and untouched theme suggestions. It preserves your entered source URL, explicit link-lookup choice, edited theme defaults and warning acknowledgement.','suiteedge-divi-elementor-migrator'),
        ]);
        self::section('results',$sections['results'],[
            __('Converted for review / Analyze only: a saved Elementor result is available, but the target has not been changed. Use Apply on that completed job to write it.','suiteedge-divi-elementor-migrator'),
            __('Applied: the result was written to its exact target. Check its frontend and Elementor editor before accepting the migration.','suiteedge-divi-elementor-migrator'),
            __('Warnings: review a possible visual difference, source dependency, menu mismatch, or unresolved asset/link. Informational notes describe conversion decisions and are separate from warnings.','suiteedge-divi-elementor-migrator'),
            __('No errors or warnings confirms the supplied layout was processed, not that it includes the entire source site. Page exports omit global headers/footers, external overlays and Theme Customizer fonts. Review the Export scope note and verify those separately.','suiteedge-divi-elementor-migrator'),
            __('Conversion not applied / Failed: read the error and its recommended action. Unsupported shortcode fallbacks and unconfigured forms block application. A warning acknowledgement cannot bypass these blockers.','suiteedge-divi-elementor-migrator'),
            __('Needs recovery: inspect the current target and original backup in the report. Use Restore interrupted item only after reviewing them. Later edits are protected.','suiteedge-divi-elementor-migrator'),
            __('Clear Migration Jobs archives selected finished jobs. It does not delete converted pages, templates, or rollback backups. View cleared jobs to restore the entries.','suiteedge-divi-elementor-migrator'),
        ]);
        echo '<section class="suiteedge-panel" id="troubleshooting"><h2>'.esc_html($sections['troubleshooting']).'</h2>';
        $questions = [
            [__('Caption bars shrink or source sections lose their white background in an older result.','suiteedge-divi-elementor-migrator'),__('Reconvert with version 1.3.2 or later, then apply the new result. Explicit widths, maximum widths, responsive settings and transparent backgrounds are retained. Keep the conversion styles widget; updating the plugin does not rebuild old saved output.','suiteedge-divi-elementor-migrator')],
            [__('The Learning Center or third-party blog grid blocks Apply.','suiteedge-divi-elementor-migrator'),__('Reconvert standard DiviGear grids with version 1.2.5 or later; they use the editable Ace Post Grid with destination posts, metadata and pagination. Keep Ace Migrator active. Old failed jobs are not rebuilt by an update. For unsupported masonry, related/custom-type or source category-ID queries, use Module template replacements (optional) and choose a configured native Elementor container. Page JSON does not import blog posts.','suiteedge-divi-elementor-migrator')],
            [__('A dynamic page, post or attachment link still blocks Apply after entering a reference URL.','suiteedge-divi-elementor-migrator'),__('Check the report source URL and public source-link lookup flag. Enable lookup or provide the named source ID in Advanced link mappings. The report records REST errors and mapping outcomes. Create a fresh conversion after correcting settings: Apply uses the old saved output and cannot repair unresolved content.','suiteedge-divi-elementor-migrator')],
            [__('The conversion completed, but my page did not change.','suiteedge-divi-elementor-migrator'),__('Check the job mode. Analyze only saves a template and report. Use Apply to Page on the same job, or Apply to UAE Template for a header/footer. Importing a downloaded JSON into Elementor is a separate manual workflow.','suiteedge-divi-elementor-migrator')],
            [__('My UAE template is missing from the destination list.','suiteedge-divi-elementor-migrator'),__('Use Headers & Footers, activate UAE, and ensure the template has a Header or Footer type. The current user must be able to edit it. Trashed templates and other template types are excluded.','suiteedge-divi-elementor-migrator')],
            [__('The menu links or dropdown icons differ.','suiteedge-divi-elementor-migrator'),__('Choose the correct destination menu in Appearance > Menus. JSON does not carry the original hierarchy. Enable source menu image lookup for public source images; unsupported or ambiguous decorations need manual editing. Create a fresh conversion after changing the menu.','suiteedge-divi-elementor-migrator')],
            [__('Apply says the target or menu changed.','suiteedge-divi-elementor-migrator'),__('The saved result was created against an earlier target or menu state. Create a fresh conversion against the current state. This guard protects newer edits and prevents old menu images from attaching to the wrong item.','suiteedge-divi-elementor-migrator')],
            [__('My layout, typography, or spacing differs.','suiteedge-divi-elementor-migrator'),__('Compare source theme defaults and desktop/tablet/mobile module settings. Review the report. Clear page/CDN caches and regenerate Elementor CSS if old styles remain. Third-party modules and unavailable source fonts may require manual work.','suiteedge-divi-elementor-migrator')],
            [__('How are header and custom fonts preserved?','suiteedge-divi-elementor-migrator'),__('Font families and responsive choices are preserved and loaded through Elementor. Keep the conversion styles and Source font widgets in the template. Verify source theme defaults for Divi global font references. Google fonts follow your Elementor loading settings; custom fonts must be registered on the destination under the same family name. Layout JSON does not include custom font files. The report explains missing registrations or disabled loading.','suiteedge-divi-elementor-migrator')],
            [__('Can I undo or remove the plugin?','suiteedge-divi-elementor-migrator'),__('Use Rollback in Migration Jobs to restore migration-owned fields when the target has no newer edits. Imported media and new drafts are retained. Deactivation stops scheduled cleanup and retains records; deletion also retains private records so reinstalling can recover reports and backups. Converted portable styles remain in the Elementor output.','suiteedge-divi-elementor-migrator')],
        ];
        foreach ($questions as $question) { echo '<details><summary>'.esc_html($question[0]).'</summary><p>'.esc_html($question[1]).'</p></details>'; }
        echo '</section>';
        self::section('requirements',$sections['requirements'],[
            __('WordPress 6.5+ and PHP 7.4+ are declared minimums; your Elementor version may require newer versions. Elementor must be active. Site administrators need manage_options and unfiltered_html; importing media also requires upload_files. On multisite, unfiltered_html may be restricted to super administrators.','suiteedge-divi-elementor-migrator'),
            __('Supported input is Divi 4 portability JSON. Output uses Classic Elementor Flexbox Containers and native widgets, with scoped portable HTML/CSS where needed. Divi 5 block exports and Atomic output are not supported.','suiteedge-divi-elementor-migrator'),
            __('Common text, image, heading, button, accordion/toggle, blurb, layout, and menu conversion is available. Run Module Coverage on your own export for a specific module list. Shared globals, cross-site gallery IDs, arbitrary dynamic bindings, third-party modules, and provider integrations can require manual work.','suiteedge-divi-elementor-migrator'),
            __('Elementor Pro integrations have not completed release acceptance. Do not assume a converted form sends mail or connects to a provider; configure and test it separately before publishing.','suiteedge-divi-elementor-migrator'),
            __('Production concurrency uses MySQL 5.7.5+ or MariaDB 10+ session advisory locks. SQLite is used for single-host QA. Each JSON file is limited to 20 MB, with up to 24 files per upload; your hosting upload limits can be lower.','suiteedge-divi-elementor-migrator'),
        ]);
        self::section('data-privacy',$sections['data-privacy'],[
            __('Exports, jobs, and rollback backups are stored privately in non-autoloaded database options and associated with the importing administrator. Reports and JSON templates can contain page content, URLs, and metadata: share them only with trusted recipients.','suiteedge-divi-elementor-migrator'),
            __('There is no telemetry or vendor migration service. Optional requests go to source sites or asset hosts when you enable image import, public REST link resolution, or source menu image lookup. Those hosts receive your server IP and the requested URL. Converted source font references may contact font hosts when visitors view the destination page.','suiteedge-divi-elementor-migrator'),
            __('Settings controls storage and daily bounded cleanup. Default retention is 30 days for unreferenced sources and 90 days for eligible jobs. Every backup protects its job and source indefinitely; active, interrupted, and created target records are protected. WP-Cron must run for cleanup.','suiteedge-divi-elementor-migrator'),
            __('For support, record the plugin, WordPress, PHP, Elementor, and UAE versions, the job mode, the error, and reproducible steps. Use a sanitized example export and redact personal content from reports.','suiteedge-divi-elementor-migrator'),
        ]);
        echo '</div></div></div>';
    }
    private static function section($id,$title,array $paragraphs) {
        echo '<section class="suiteedge-panel" id="'.esc_attr($id).'"><h2>'.esc_html($title).'</h2>';
        foreach ($paragraphs as $paragraph) { echo '<p>'.esc_html($paragraph).'</p>'; }
        echo '</section>';
    }
}
