<?php
namespace SuiteEdge\Migrator\Admin;
class MappingPage {
    public function render($theme_builder = false) {
        if (!current_user_can('manage_options')) { wp_die('Unauthorized.'); }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Read-only, owner-bound session; SitePageSelection validates a fixed UUID before lookup.
        $site_session=$theme_builder?'':wp_unslash($_GET['site_session']??'');$session_error='';
        try { $files=$site_session!==''?(new \SuiteEdge\Migrator\Import\SitePageSelection())->files($site_session):get_transient('suiteedge_migrator_pending_files_' . get_current_user_id()); }
        catch(\Throwable $e){$files=[];$session_error=$e->getMessage();}
        View::start($theme_builder ? __('Header / Footer Template Mapping','suiteedge-divi-elementor-migrator') : __('Map pages & posts','suiteedge-divi-elementor-migrator'),__('Choose a destination for each selected layout, check the settings, then convert for review.','suiteedge-divi-elementor-migrator'),$theme_builder ? 'templates' : ($site_session!==''?'site-pages':'pages'),2,$site_session!==''?(new SitePagePage())->steps():null);
        if($session_error!==''){echo '<div class="notice notice-error"><p>'.esc_html($session_error).'</p></div>';}
        if(!$files && $site_session!==''){View::empty_state(__('Choose your pages again','suiteedge-divi-elementor-migrator'),__('Local page selections last one hour and can create only one migration job.','suiteedge-divi-elementor-migrator'),'-site-page',__('Select pages','suiteedge-divi-elementor-migrator'));echo '</div>';return;}
        if (!$files) { View::empty_state(__('No layouts to map yet','suiteedge-divi-elementor-migrator'),__('Upload Divi JSON exports first. Your mapping session lasts one hour; upload again if it has expired.','suiteedge-divi-elementor-migrator'),$theme_builder ? '-theme-builder' : '-import',__('Upload layouts','suiteedge-divi-elementor-migrator')); echo '</div>'; return; }
        $templates=[];
        if ($theme_builder) {
            try { $templates=(new \SuiteEdge\Migrator\Elementor\TargetResolver())->templates(); }
            catch (\Throwable $e) { echo '<div class="notice notice-error"><p>'.esc_html($e->getMessage()).'</p></div></div>';return; }
            echo '<p>Choose the UAE template and its Header/Footer type explicitly. Existing display conditions, exclusions and user roles remain unchanged. New templates are drafts with no global assignment. <a href="'.esc_url(admin_url('edit.php?post_type=elementor-hf')).'">Manage UAE templates</a></p>';
        }
        $recommended = (new \SuiteEdge\Migrator\Import\RecommendedSettings())->for_pending($files,$theme_builder ? 'theme-builder' : 'page');
        $recommended['preserve_menu_design']=$theme_builder && $recommended['source_origin']!=='';
        $local=[];$local_links=[];
        if($site_session!=='') {foreach($files as $file){$record=(new \SuiteEdge\Migrator\Import\SourceRepository())->get($file['source_id']);$local[$file['source_id']]=(new \SuiteEdge\Migrator\Import\SitePageSource())->suggestions($record);$local_links=array_replace($local_links,$local[$file['source_id']]['link_map']);}}
        $profiles = [''=>(new \SuiteEdge\Migrator\Style\SourceThemeProfile())->recommended('', $theme_builder ? 'theme-builder' : 'page')];
        foreach (array_unique(array_filter([$recommended['source_origin'],$recommended['source_theme_origin'],'https://suiteedge.com','https://www.suiteedge.com'])) as $origin) {
            $profiles[$origin] = (new \SuiteEdge\Migrator\Style\SourceThemeProfile())->recommended($origin,$theme_builder ? 'theme-builder' : 'page');
        }
        echo '<form id="suiteedge-mapping" method="post" action="?page=suiteedge-migrator-jobs">'; wp_nonce_field('suiteedge_create_job');
        if($site_session!==''){echo '<input type="hidden" name="site_session" value="'.esc_attr($site_session).'"><p class="suiteedge-note">'.esc_html__('Source: a saved copy of a Divi page on this site. Existing local menu/category IDs are suggested for review. Your chosen target is selected below. Source content, presets and referenced globals must still match when applying.','suiteedge-divi-elementor-migrator').'</p>';}
        echo '<section class="suiteedge-panel"><h2>'.esc_html__('1. Select layouts & destinations','suiteedge-divi-elementor-migrator').'</h2><p>'.esc_html__('Each selected layout needs a distinct destination. New targets are created as drafts when you apply.','suiteedge-divi-elementor-migrator').'</p><input type="hidden" name="selection_present" value="1"><div class="suiteedge-table-scroll" role="region" tabindex="0" aria-label="Layout destinations"><table class="widefat striped"><thead><tr><th><input type="checkbox" id="suiteedge-select-layouts" checked aria-label="Select all layouts"></th><th>Source layout</th><th>'.($theme_builder ? 'Target UAE template' : ($site_session!=='' ? 'Target page' : 'Target page / blog post')).'</th></tr></thead><tbody>';
        foreach ($files as $i=>$file) {
            echo '<tr><td><input type="checkbox" name="selected_layouts[]" value="'.(int)$i.'" checked aria-label="'.esc_attr('Convert '.$file['name']).'"></td><td>' . esc_html($file['name']) . '</td><td>';
            if ($theme_builder) {
                echo '<select name="target_page['.(int)$i.']" aria-label="'.esc_attr('Destination for '.$file['name']).'" required><option value="">Choose Header / Footer target</option>';
                foreach (['header'=>'Header','footer'=>'Footer'] as $type=>$label) {
                    echo '<optgroup label="'.esc_attr('UAE '.$label).'"><option value="'.esc_attr('uae-'.$type.':create_new').'">'.esc_html('+ Create new UAE '.$label.' draft').'</option>';
                    foreach ($templates as $template) { if (get_post_meta($template->ID,'ehf_template_type',true)==='type_'.$type) { echo '<option value="'.esc_attr('uae-'.$type.':'.(int)$template->ID).'">'.esc_html($template->post_title.' (#'.$template->ID.' / '.$template->post_status.')').'</option>'; } }
                    echo '</optgroup>';
                }
                echo '</select>';
            } else {
                $preferred=$file['preferred_target']??'create_new';
                echo '<label class="screen-reader-text" for="target_page['.(int)$i.']">'.esc_html('Destination for '.$file['name']).'</label><select id="target_page['.(int)$i.']" name="target_page['.(int)$i.']" required><optgroup label="Pages"><option value="create_new"'.selected($preferred,'create_new',false).'>+ Create new page draft</option>';
                if ($site_session!=='') {
                    // The destination was explicitly chosen on the first screen. Keep
                    // that exact page even when it is older than the selector window.
                    $pages=$preferred==='create_new'?[]:[get_post((int)$preferred)];
                } else { $pages=get_pages(['post_status'=>['publish','draft','private','pending','future']]); }
                foreach($pages as $page) {if($page&&current_user_can('edit_post',$page->ID)){echo '<option value="'.(int)$page->ID.'"'.selected((string)$preferred,(string)$page->ID,false).'>'.esc_html($page->post_title.' (#'.$page->ID.' / '.$page->post_status.')').'</option>';}}
                if($site_session!==''){echo '</optgroup></select></td></tr>';continue;}
                echo '</optgroup><optgroup label="Blog Posts"><option value="post:create_new">+ Create new blog post draft</option>';
                foreach(get_posts(['post_type'=>'post','post_status'=>['publish','draft','private','pending','future'],'numberposts'=>100,'orderby'=>'ID','order'=>'DESC']) as $post) {if(current_user_can('edit_post',$post->ID)){echo '<option value="post:'.(int)$post->ID.'">'.esc_html($post->post_title.' (#'.$post->ID.' / '.$post->post_status.')').'</option>';}}
                echo '<option value="post:by_id">Choose an older post by ID below</option></optgroup></select><div><label>'.esc_html__('Older post ID (only for that option)','suiteedge-divi-elementor-migrator').' <input name="post_target_id['.(int)$i.']" type="text" inputmode="numeric" pattern="[0-9]+" size="8" placeholder="123"></label></div>';
            }
            echo '</td></tr>';
        }
        echo '</tbody></table></div><p>Output: Classic Elementor Flexbox Containers. Atomic output is unavailable.</p><p>Validation and backups are required. Existing titles, slugs, publication state, SEO and FAQ metadata are preserved.</p><p>A page layout export does not include the source theme header or footer. Use the separate Header &amp; Footer migrator for Divi Theme Builder exports and UAE templates. Source Divi display conditions are not included in layout JSON; configure new template assignments in UAE.</p>';
        if(!$theme_builder && $site_session==='') {echo '<p><strong>Blog posts:</strong> enable Posts in Elementor Settings &gt; General. Select an existing post to preserve its author, dates, excerpt, categories, tags, featured image and SEO. Only its builder layout changes. JSON contains layout data, not the post database: import the source posts with WordPress Tools &gt; Import first when migrating between sites. New post targets are drafts; set their post details before publishing. The 100 newest posts are listed; choose the older-post option and enter its ID to map another post.</p>';}
        $category_references=(new \SuiteEdge\Migrator\Import\BlogCategoryMapping())->pending($files);
        if($category_references) {
            $terms=get_terms(['taxonomy'=>'category','hide_empty'=>false]);if(is_wp_error($terms)){$terms=[];}
            echo '<h2>'.esc_html__('Blog category mappings','suiteedge-divi-elementor-migrator').'</h2><p>'.esc_html__('Select the destination category for each source ID. Category IDs differ between sites. Local page conversions suggest verified categories from this site for review. Import/create the destination categories first. Missing mappings preserve the module and block Apply.','suiteedge-divi-elementor-migrator').'</p>';
            foreach($category_references as $source=>$reference) {foreach($reference['ids'] as $id) {
                echo '<p><label>'.esc_html($reference['name'].' / source category '.$id).' <select name="blog_category_map['.esc_attr($source).']['.(int)$id.']"><option value="0">'.esc_html__('Choose destination category','suiteedge-divi-elementor-migrator').'</option>';
                foreach($terms as $term) {echo '<option value="'.(int)$term->term_id.'"'.selected($local[$source]['blog_category_map'][$id]??0,(int)$term->term_id,false).'>'.esc_html($term->name.' / '.$term->slug.' (#'.$term->term_id.')').'</option>';}
                echo '</select></label></p>';
            }}
        }
        $references = (new \SuiteEdge\Migrator\Import\MenuMapping())->pending($files);
        if ($references) {
            echo '<p><label><input type="checkbox" name="preserve_menu_design" value="1"'.checked($theme_builder && $recommended['source_origin']!=='',true,false).'> Preserve omitted menu-label image icons from the public source Header/Footer (uses Source site origin). Menu links and labels remain editable in Appearance &gt; Menus; differences are reported.</label></p>';
            $menus = wp_get_nav_menus(); $main = array_values(array_filter($menus,function ($menu) { return strcasecmp(trim($menu->name),'Main Menu') === 0; }));
            echo '<h2>Navigation menu mappings</h2><p>Divi exports contain menu IDs, not their links. Select the existing destination WordPress menu for each source. A unique Main Menu is suggested for a single-menu Header export; review the selection before conversion. No menu is created or overwritten.</p>';
            foreach ($references as $source=>$reference) {
                $suggested = count($reference['ids']) === 1 && count($main) === 1 && preg_match('/header/i',$reference['name']) ? (int)$main[0]->term_id : 0;
                foreach ($reference['ids'] as $id) {
                    $suggested=$local[$source]['menu_map'][$id]??$suggested;
                    echo '<p><label>'.esc_html($reference['name'].' / source menu '.$id).' <select name="menu_map['.esc_attr($source).']['.(int)$id.']"><option value="0">Choose destination menu</option>';
                    foreach ($menus as $menu) { echo '<option value="'.(int)$menu->term_id.'"'.selected($suggested,(int)$menu->term_id,false).'>'.esc_html($menu->name).' (#'.(int)$menu->term_id.')</option>'; }
                    echo '</select></label></p>';
                }
            }
            if (!$menus) { echo '<p>Create/import the destination menu in Appearance &gt; Menus first, then reload this mapping page. Missing or empty menus block application.</p>'; }
        }
        $replacements = (new \SuiteEdge\Migrator\Import\TemplateReplacement())->pending($files);
        if ($replacements) {
            $library = get_posts(['post_type'=>'elementor_library','post_status'=>['publish','draft','private'],'posts_per_page'=>100,'orderby'=>'ID','order'=>'DESC']);
            echo '<h2>'.esc_html__('Module template replacements (optional)','suiteedge-divi-elementor-migrator').'</h2><p>'.esc_html__('Supported core Divi Blog and DiviGear blog listings convert automatically to the editable Ace Post Grid widget; keep Ace Migrator active to render it. For unsupported layouts/queries or a different widget provider, save a configured Elementor container template and select it here. This explicitly replaces the whole module design and query. Blog posts are not imported.','suiteedge-divi-elementor-migrator').'</p>';
            foreach ($replacements as $source=>$reference) {
                foreach ($reference['modules'] as $tag=>$count) {
                    echo '<p><label>'.esc_html($reference['name'].' / '.$tag.' ('.$count.' module'.($count===1?'':'s').')').' <select name="module_replacements['.esc_attr($source).']['.esc_attr($tag).']"><option value="0">'.(in_array($tag,['dgbm_blog_module','et_pb_blog'],true)?esc_html__('Convert automatically with Ace Post Grid','suiteedge-divi-elementor-migrator'):esc_html__('Keep unresolved — blocks Apply','suiteedge-divi-elementor-migrator')).'</option>';
                    foreach ($library as $template) { if (current_user_can('edit_post',$template->ID)) { echo '<option value="'.(int)$template->ID.'">'.esc_html($template->post_title.' (#'.$template->ID.')').'</option>'; } }
                    echo '</select></label> <label>'.esc_html__('Or enter template ID','suiteedge-divi-elementor-migrator').' <input type="text" inputmode="numeric" pattern="[0-9]+" size="8" name="replacement_template_id['.esc_attr($source).']['.esc_attr($tag).']" value="" placeholder="123"></label></p>';
                }
            }
            echo '<p>'.esc_html__('The 100 newest saved templates are listed. An entered template ID takes priority and can select an older template; permissions and native elements are still validated. All instances of the selected module type in this source file use that replacement. Later template edits do not change saved output; run a fresh conversion to include them.','suiteedge-divi-elementor-migrator').' <a href="'.esc_url(admin_url('edit.php?post_type=elementor_library')).'">'.esc_html__('Manage saved Elementor templates','suiteedge-divi-elementor-migrator').'</a></p>';
        }
        echo '</section><section class="suiteedge-panel"><h2>'.esc_html__('2. Check import settings','suiteedge-divi-elementor-migrator').'</h2><input type="hidden" name="submit_conversion" value="1"><p><strong>Analyze only</strong> generates a report and template. <strong>Apply</strong> writes to the exact targets selected above.</p>';
        echo '<p id="suiteedge-mode-description" role="status">Analysis generates a report and an Elementor template. It does not update the assigned target or import media.</p><h2>Recommended import settings</h2><p>During Apply, these defaults import embedded images first and reuse exact Media Library matches. Verify the suggested source origin before starting. Downloaded templates retain source image URLs for manual Elementor import.</p><p><button type="button" class="button" id="suiteedge-recommended">Use recommended settings</button></p>';

        echo '<p><label><input type="checkbox" name="assets[]" value="import_external" checked> Import embedded images first, then external images (up to 10 MB each)</label></p><p><label><input type="checkbox" name="assets[]" value="reuse_library" checked> Reuse exact Media Library matches</label></p>';
        echo '<details><summary>'.esc_html__('Advanced link mappings','suiteedge-divi-elementor-migrator').'</summary><p>'.esc_html__('Use this when the public source REST API cannot resolve a supported Divi page, post or attachment link. Enter a JSON object mapping source page, post or attachment IDs to destination URLs.','suiteedge-divi-elementor-migrator').'</p><div class="suiteedge-field"><label for="suiteedge-link-map">'.esc_html__('Source item ID to target URL','suiteedge-divi-elementor-migrator').'</label><textarea id="suiteedge-link-map" name="link_map" rows="4" cols="70" placeholder="{&quot;51689&quot;:&quot;/contact/&quot;}">'.($local_links?esc_textarea(wp_json_encode((object)$local_links)):'').'</textarea></div></details>';
        echo '<input type="hidden" name="resolve_source_links_present" value="1"><p><label><input type="checkbox" name="resolve_source_links" value="1"'.checked($recommended['resolve_source_links'],true,false).'> '.esc_html($site_session!==''?__('Resolve supported dynamic links from published pages, posts and attachments on this site','suiteedge-divi-elementor-migrator'):__('Resolve dynamic page, post and attachment links through the public WordPress REST API of the specified source origin','suiteedge-divi-elementor-migrator')).'</label></p>';
        echo '<p><label>Source site URL for link resolution and rewriting <input type="url" name="source_origin" value="'.esc_attr($recommended['source_origin']).'" placeholder="https://source.example" aria-describedby="suiteedge-source-link-help"'.($site_session!==''?' readonly':'').'></label></p>';
        echo '<p id="suiteedge-source-link-help" role="status">'.esc_html($site_session!==''?__('The source is this WordPress site. Supported dynamic links resolve against local published pages, posts and attachments without a public REST request. Advanced link mappings take priority.','suiteedge-divi-elementor-migrator'):__('Enter the original Divi site URL. Entering a URL enables public page-link lookup; you can switch it off explicitly. This lookup reads published source page URLs only. Advanced link mappings take priority.','suiteedge-divi-elementor-migrator')).'</p>';
        if ($recommended['source_origin'] === '') { echo '<p>Some exports have no media provenance, so this batch needs your source URL. Recommended settings preserve a URL you enter. After correcting settings, start a fresh conversion; Apply on an old saved job does not reconvert its unresolved links.</p>'; }
        echo '</section><details><summary>'.esc_html__('Source theme defaults','suiteedge-divi-elementor-migrator').'</summary><p>'.esc_html__('Divi layout exports can omit Theme Customizer settings. Verify these defaults against your source site. Explicit module values take precedence. Recognized source sites use measured defaults; other sites use generic Divi defaults.','suiteedge-divi-elementor-migrator').'</p><div class="suiteedge-form-grid">';
        if ($recommended['source_theme_origin'] !== '' && $recommended['source_origin'] === '') {
            /* translators: %s: Source website origin inferred from exported media URLs. */
            $message = sprintf(__('Style defaults are suggested from media in %s. Some selected exports lack source provenance; verify that these defaults suit every selected layout and enter the source origin for link resolution.','suiteedge-divi-elementor-migrator'),$recommended['source_theme_origin']);
            echo '<p>'.esc_html($message).'</p>';
        }
        foreach (['row_max_width'=>'Row maximum width (px)','body_font'=>'Body font family','heading_font'=>'Inherited heading font family','body_size'=>'Default body size (px)','body_weight'=>'Inherited body font weight (1-1000)','body_color'=>'Default body color','heading_color'=>'Inherited heading color','accent_color'=>'Default icon/accent color'] as $key=>$label) {
            echo '<p class="suiteedge-field"><label for="suiteedge-theme-'.esc_attr($key).'">'.esc_html($label).'</label><input id="suiteedge-theme-'.esc_attr($key).'" name="source_theme['.esc_attr($key).']" value="'.esc_attr($recommended['source_theme'][$key]).'" type="'.(in_array($key,['row_max_width','body_size','body_weight'],true) ? 'number' : 'text').'"></p>';
        }
        echo '</div></details><section class="suiteedge-panel"><h2>'.esc_html__('3. Convert for review or apply now','suiteedge-divi-elementor-migrator').'</h2><p>Analysis preserves unresolved modules as shortcodes. Applying a conversion is blocked when those shortcodes or unconfigured forms remain. Review all warnings before publishing. Warning acknowledgement is your review decision.</p>';
        echo '<p><label><input type="checkbox" name="reviewed_warnings" value="1"> '.esc_html__('For Apply: I reviewed the analysis and accept its documented differences on published targets.','suiteedge-divi-elementor-migrator').'</label></p><p class="description">'.esc_html__('Review acknowledgement is required only when applying to a published target with warnings. It is not needed to convert for review.','suiteedge-divi-elementor-migrator').'</p>';
        echo '<p id="suiteedge-selected-count" role="status"></p><p class="submit"><button type="submit" name="execution_mode" value="analyze" class="button button-primary">'.esc_html($site_session!==''?__('Convert for review','suiteedge-divi-elementor-migrator'):__('Convert Selected / download templates','suiteedge-divi-elementor-migrator')).'</button> <button type="submit" name="execution_mode" value="apply" class="button button-secondary">'.($theme_builder ? 'Apply to UAE templates' : ($site_session!==''?'Convert and apply to page':'Apply to pages / posts')).'</button></p><p class="description">'.esc_html__('Recommended first run: Convert Selected, review the report in Migration Jobs, then apply the saved result. Apply now converts and writes immediately after validation and backups.','suiteedge-divi-elementor-migrator').'</p></section></form>';
        echo '<script>const suiteedgeRecommendedSettings='.wp_json_encode($recommended,JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).';</script>';
        echo '<script>const suiteedgeThemeProfiles='.wp_json_encode($profiles,JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).';</script>';
        ?>
        <script>
        (() => {
            const form = document.getElementById('suiteedge-mapping');
            let currentProfile = suiteedgeRecommendedSettings.source_theme;
            const source = form.querySelector('[name="source_origin"]');
            const resolve = form.querySelector('[name="resolve_source_links"]');
            let resolverEdited = false;
            resolve.addEventListener('change', () => { resolverEdited = true; });
            source.addEventListener('change', e => {
                let url; try { url = new URL(e.target.value); } catch { return; }
                if (!['http:','https:'].includes(url.protocol) || url.username || url.password) return;
                const origin = url.origin; source.value = origin;
                if (!resolverEdited) resolve.checked = true;
                const next = suiteedgeThemeProfiles[origin] || suiteedgeThemeProfiles[''];
                Object.entries(next).forEach(([key,value]) => {
                    const input = form.querySelector('[name="source_theme['+key+']"]');
                    // Retain every explicit user edit; refresh only untouched suggested fields.
                    if (input.value === String(currentProfile[key])) { input.value = value; }
                }); currentProfile = next;
            });
            const selections = [...form.querySelectorAll('[name="selected_layouts[]"]')];
            const update = () => {
                const count = selections.filter(e => e.checked).length;
                document.getElementById('suiteedge-selected-count').textContent = count+' layouts selected. Conversion saves results for review; Apply also writes them to the selected targets.';
                form.querySelectorAll('[type="submit"]').forEach(e => e.disabled = !count);
                selections.forEach(input => { const target=form.querySelector('[name="target_page['+input.value+']"]'); if (target) { target.disabled=!input.checked; target.required=input.checked; } });
                document.getElementById('suiteedge-select-layouts').checked = count === selections.length;
                document.getElementById('suiteedge-select-layouts').indeterminate = count > 0 && count < selections.length;
            };
            document.getElementById('suiteedge-select-layouts').addEventListener('change', e => { selections.forEach(s => s.checked = e.target.checked); update(); });
            selections.forEach(e => e.addEventListener('change',update)); update();
            document.getElementById('suiteedge-recommended').addEventListener('click', () => {
                if (!source.value.trim()) source.value = suiteedgeRecommendedSettings.source_origin;
                if (!resolverEdited) resolve.checked = Boolean(source.value.trim());
                const menuDesign=form.querySelector('[name="preserve_menu_design"]'); if (menuDesign) menuDesign.checked=suiteedgeRecommendedSettings.preserve_menu_design;
                form.querySelectorAll('[name="assets[]"]').forEach(input => input.checked = suiteedgeRecommendedSettings.assets.includes(input.value));
                const next = suiteedgeThemeProfiles[source.value.replace(/\/$/,'')] || suiteedgeRecommendedSettings.source_theme;
                Object.entries(next).forEach(([key,value]) => { const input=form.querySelector('[name="source_theme['+key+']"]'); if (input.value === String(currentProfile[key])) input.value=value; });
                currentProfile = next;
            });
        })();
        </script>
        <?php echo '</div>';
    }
}
