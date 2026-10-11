<?php
namespace SuiteEdge\Migrator\Admin;
class JobsPage {
    public function render() {
        if (!current_user_can('manage_options')) { wp_die('Unauthorized.'); }
        $repo = new \SuiteEdge\Migrator\Jobs\JobRepository(); $error = '';
        if (isset($_POST['submit_conversion'])) {
            check_admin_referer('suiteedge_create_job');
            try {
                $links = new \SuiteEdge\Migrator\Dynamic\SourceLinkSettings();
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- SourceLinkSettings strictly validates scalar HTTP(S) syntax, rejects credentials, and normalizes to a scheme/host/port origin before storage or requests.
                $origin = $links->origin(wp_unslash($_POST['source_origin'] ?? ''));
                $assets = array_intersect(['import_external','reuse_library'],array_map('sanitize_key',(array)wp_unslash($_POST['assets'] ?? [])));
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Bounded JSON is decoded and every key and URL is validated below; text sanitization would corrupt the JSON.
                $json = wp_unslash($_POST['link_map'] ?? '');
                if (!is_string($json) || strlen($json) > 65536) { throw new \InvalidArgumentException('Link mapping exceeds the size limit.'); }
                $map = $json === '' ? [] : json_decode($json,true,16,JSON_THROW_ON_ERROR);
                if (!is_array($map) || count($map) > 1000) { throw new \InvalidArgumentException('Link mapping must be a JSON object.'); }
                foreach ($map as $source=>$url) {
                    if (!ctype_digit((string)$source) || !is_string($url) || (!preg_match('~^https?://~i',$url) && !preg_match('~^/(?!/)~',$url)) || preg_match('/[<>\s]/',$url)) { throw new \InvalidArgumentException('Link mapping requires numeric IDs and safe absolute URLs or paths.'); }
                }
                $dry_run = (new \SuiteEdge\Migrator\Import\RecommendedSettings())->dry_run($_POST);
                $theme = new \SuiteEdge\Migrator\Style\SourceThemeProfile();
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- SourceThemeProfile strictly validates keys, numeric bounds, font syntax and hex colors.
                $profile = $theme->validate(isset($_POST['source_theme']) ? wp_unslash($_POST['source_theme']) : $theme->recommended($origin));
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- MenuMapping::validate enforces source IDs, destination menu IDs and safe hierarchy; preserve the structured input for that validator.
                $menu_map = wp_unslash($_POST['menu_map'] ?? []);
                if (!is_array($menu_map)) { throw new \InvalidArgumentException('Invalid menu mappings.'); }
                $menu_map = (new \SuiteEdge\Migrator\Import\MenuMapping())->validate($menu_map);
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Source ownership and each category ID/taxonomy are validated before storage.
                $categories=wp_unslash($_POST['blog_category_map']??[]);
                if(!is_array($categories)){throw new \InvalidArgumentException('Invalid Blog category mappings.');}
                $categories=(new \SuiteEdge\Migrator\Import\BlogCategoryMapping())->validate($categories);
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- TemplateReplacement validates source ownership, module tags, template permissions and the complete native element tree.
                $replacements = wp_unslash($_POST['module_replacements'] ?? []);
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- TemplateReplacement validates numeric overrides, source ownership, template permissions and the native element tree.
                $overrides = wp_unslash($_POST['replacement_template_id'] ?? []);
                if (!is_array($replacements) || !is_array($overrides)) { throw new \InvalidArgumentException('Invalid module replacement mappings.'); }
                $replacements = (new \SuiteEdge\Migrator\Import\TemplateReplacement())->validate($replacements,$overrides);
                $targets=array_map('sanitize_text_field',(array)wp_unslash($_POST['target_page'] ?? []));
                $post_ids=array_map('sanitize_text_field',(array)wp_unslash($_POST['post_target_id']??[]));
                foreach($targets as $index=>$target){if($target==='post:by_id'){if(!isset($post_ids[$index])||!preg_match('/^[1-9][0-9]{0,9}$/D',$post_ids[$index])){throw new \InvalidArgumentException('Enter a positive destination post ID for the older-post option.');}$targets[$index]='post:'.$post_ids[$index];}}
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Owner-bound UUID is validated by SitePageSelection before lookup; arrays and malformed values fail closed.
                $site_session=wp_unslash($_POST['site_session']??'');
                (new \SuiteEdge\Migrator\Import\BatchImport())->create($targets,['blog_category_map'=>$categories,'output_engine'=>'classic','dry_run'=>$dry_run,'assets'=>array_values($assets),'source_origin'=>$origin,'source_theme'=>$profile,'link_map'=>$map,'menu_map'=>$menu_map,'module_replacements'=>$replacements,'preserve_menu_design'=>!empty($_POST['preserve_menu_design']),'resolve_source_links'=>$links->enabled($_POST,$origin),'reviewed_warnings'=>!empty($_POST['reviewed_warnings'])],isset($_POST['selection_present']) ? array_map('sanitize_text_field',(array)wp_unslash($_POST['selected_layouts'] ?? [])) : null,$site_session);
            } catch (\Throwable $e) { $error = $e->getMessage(); }
        }
        $page = max(1,(int)sanitize_text_field(wp_unslash($_GET['history_page'] ?? 1)));
        $archived = !empty($_GET['archived']); $jobs = $repo->all($page,25,$archived); $active = $archived ? null : $repo->get_latest_active();
        View::start(__('Migration Jobs','suiteedge-divi-elementor-migrator'),__('Review saved results, apply them to their original destinations, and manage your migration history.','suiteedge-divi-elementor-migrator'),'jobs',4);
        echo '<noscript><div class="notice notice-warning"><p>'.esc_html__('Enable JavaScript to process jobs, apply results, or use job actions. The User Guide and reports remain available without JavaScript.','suiteedge-divi-elementor-migrator').'</p></div></noscript>';
        echo '<p><a class="button" href="?page=suiteedge-migrator-import">New import</a> <a class="button" href="?page=suiteedge-migrator-theme-builder">Header &amp; Footer import</a></p>';
        if ($error) { echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>'; }
        echo '<p><a href="'.esc_url(admin_url('admin.php?page=suiteedge-migrator-jobs'.($archived ? '' : '&archived=1'))).'">'.($archived ? 'Back to migration jobs' : 'View cleared jobs').'</a></p>';
        echo '<p><button class="button button-primary" id="suiteedge-apply-selected"'.($archived ? ' hidden' : '').'>Apply Selected to Pages / Posts / Templates</button> <button class="button" id="suiteedge-clear-selected">'.($archived ? 'Restore Selected Jobs' : 'Clear Migration Jobs').'</button></p><p><label><input type="checkbox" id="suiteedge-reviewed"> I reviewed the selected reports and accept their documented differences on published targets</label></p><p>Apply uses each completed result and its original target shown below. Clearing removes selected jobs from this list; converted pages/posts/templates and rollback backups remain available in cleared jobs.</p>';
        echo '<p id="suiteedge-job-message" role="status" aria-live="polite"></p><div class="suiteedge-table-scroll" role="region" tabindex="0" aria-label="Migration jobs"><table class="widefat striped"><thead><tr><th><input id="suiteedge-select-jobs" type="checkbox" aria-label="Select all jobs on this page"></th><th>Job / Started / Targets</th><th>Mode</th><th>Status</th><th>Processed</th><th>Warnings / Failures</th><th>Actions</th></tr></thead><tbody>';
        foreach ($jobs as $job) {
            $outcome = (new \SuiteEdge\Migrator\Reports\JobOutcome())->describe($job);
            $eligible = (new \SuiteEdge\Migrator\Jobs\SavedOutputApplier())->eligible($job);
            echo '<tr><td><input class="suiteedge-job-selection" type="checkbox" value="'.esc_attr($job->id).'" aria-label="'.esc_attr('Select job '.$job->id).'"></td><td>'.esc_html($job->id).'<br>'.esc_html(wp_date('Y-m-d H:i:s',$job->created));
            foreach ($job->items as $index=>$item) {
                $id = (int)($item['target_page_id'] ?? 0); $target = $id ? get_the_title($id).' (#'.$id.')' : 'New draft for this layout';
                $kind=$item['target_kind'] ?? 'page';if ($kind!=='page') {$target=($kind==='post'?'Blog Post: ':($kind==='uae-header'?'UAE Header: ':'UAE Footer: ')).$target;}
                if (($job->settings['direction'] ?? '')==='elementor_to_divi') { $target='Divi: '.$target; }
                echo '<p>';
                if (in_array($index,$eligible,true)) { echo '<input type="checkbox" class="suiteedge-page-selection" data-job="'.esc_attr($job->id).'" value="'.(int)$index.'" aria-label="'.esc_attr('Apply '.$item['name'].' to '.$target).'"> '; }
                echo esc_html($item['name']).' &rarr; '.esc_html($target).' — <span class="suiteedge-page-status" data-job="'.esc_attr($job->id).'" data-index="'.(int)$index.'">'.esc_html($item['status'] ?? 'pending').'</span>';
                if (!empty($item['error'])) { echo '<br>'.esc_html($item['error']); }
                if (in_array($index,$eligible,true)) { echo ' <button class="button suiteedge-apply" data-job="'.esc_attr($job->id).'" data-indices="'.esc_attr(wp_json_encode([$index])).'">'.($kind==='page'?'Apply to Page':($kind==='post'?'Apply to Post':'Apply to UAE Template')).'</button>'; }
                echo '</p>';
            }
            echo '</td><td>'.esc_html($outcome['mode']).'</td><td><span class="suiteedge-status'.($job->summary['failed'] ? ' suiteedge-status-error' : ($job->summary['warnings'] ? ' suiteedge-status-review' : ' suiteedge-status-success')).'">'.esc_html($outcome['status_label']).'</span></td><td>'.(int)$job->current_index.'/'.count($job->items).'</td><td>'.(int)$job->summary['warnings'].' / '.(int)$job->summary['failed'].'</td><td><a class="button" href="'.esc_url(admin_url('admin.php?page=suiteedge-migrator-reports&job='.$job->id)).'">Report</a> ';
            if (in_array($job->status,['completed','completed_with_errors','rollback_failed','cancelled'],true) && empty($job->settings['dry_run'])) { echo '<button class="button suiteedge-rollback" data-job="'.esc_attr($job->id).'">Rollback</button>'; }
            if (in_array($job->status,['pending','running'],true)) { echo '<button class="button suiteedge-cancel" data-job="'.esc_attr($job->id).'">Cancel remaining items</button>'; }
            if ($job->status === 'needs_recovery') { echo '<button class="button suiteedge-review-recovery" data-job="'.esc_attr($job->id).'">Refresh recovery review</button> <button class="button suiteedge-recover" data-job="'.esc_attr($job->id).'">Restore interrupted item</button>'; }
            echo '</td></tr>';
        }
        if (!$jobs) { echo '<tr><td colspan="7">No migration jobs yet.</td></tr>'; }
        echo '</tbody></table></div>';
        echo '<p>'.esc_html($repo->count($archived)).' jobs. Page '.(int)$page.'. ';
        if ($page > 1) { echo '<a href="'.esc_url(add_query_arg('history_page',$page-1)).'">Previous</a> '; }
        if ($page*25 < $repo->count($archived)) { echo '<a href="'.esc_url(add_query_arg('history_page',$page+1)).'">Next</a>'; }
        echo '</p>';
        $config = ['archived'=>$archived,'active'=>$active ? $active->id : null,'nonce'=>wp_create_nonce('suiteedge_migrator_nonce'),'batch_size'=>(int)get_option('suiteedge_batch_size',1)];
        echo '<script>const suiteedgeJobConfig=' . wp_json_encode($config,JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>';
        ?>
        <script>
        (() => {
            const message = document.getElementById('suiteedge-job-message');
            async function request(action, job, extra = {}) {
                const body = new FormData();
                for (const [key, value] of Object.entries({action, job_id:job, nonce:suiteedgeJobConfig.nonce, batch_size:suiteedgeJobConfig.batch_size,...extra})) body.append(key,value);
                const response = await fetch(ajaxurl,{method:'POST',body});
                const result = await response.json();
                if (!result.success) throw new Error(result.data.message || 'Request failed.');
                return result.data;
            }
            async function next() {
                try {
                    const result = await request('suiteedge_migrator_process_batch',suiteedgeJobConfig.active);
                    message.textContent = result.message || result.status + ': ' + Math.round(result.progress) + '%; failed items: ' + result.summary.failed;
                    (result.items || []).forEach((item,index) => { document.querySelectorAll('.suiteedge-page-status').forEach(el => { if (el.dataset.job === suiteedgeJobConfig.active && Number(el.dataset.index) === index) el.textContent = item.status+(item.error ? ': '+item.error : ''); }); });
                    if (['pending','running'].includes(result.status)) setTimeout(next,result.retry_after || 500);
                    else location.replace(location.href.split('&job=')[0]);
                } catch (error) { message.textContent = error.message + ' Reload to resume after resolving the error.'; }
            }
            if (suiteedgeJobConfig.active) next();
            const jobs = [...document.querySelectorAll('.suiteedge-job-selection')];
            document.getElementById('suiteedge-select-jobs').addEventListener('change',e => { jobs.forEach(j => j.checked = e.target.checked); document.querySelectorAll('.suiteedge-page-selection').forEach(p => p.checked = e.target.checked); });
            jobs.forEach(j => j.addEventListener('change',() => document.querySelectorAll('.suiteedge-page-selection').forEach(p => { if (p.dataset.job === j.value) p.checked = j.checked; })));
            async function apply(selection,button) {
                button.disabled = true;
                try { const r = await request('suiteedge_migrator_apply_saved','',{selection:JSON.stringify(selection),reviewed_warnings:document.getElementById('suiteedge-reviewed').checked ? '1' : '0'}); message.textContent = r.message; location.href = '?page=suiteedge-migrator-jobs'; }
                catch(error) { message.textContent = error.message; button.disabled = false; }
            }
            document.querySelectorAll('.suiteedge-apply').forEach(b => b.addEventListener('click',() => apply({[b.dataset.job]:JSON.parse(b.dataset.indices)},b)));
            document.getElementById('suiteedge-apply-selected').addEventListener('click',e => { const selection = {}; document.querySelectorAll('.suiteedge-page-selection:checked').forEach(p => (selection[p.dataset.job] ||= []).push(Number(p.value))); if (!Object.keys(selection).length) { message.textContent = 'Select completed results to apply.'; return; } apply(selection,e.target); });
            document.getElementById('suiteedge-clear-selected').addEventListener('click',async e => {
                const selected = jobs.filter(j => j.checked).map(j => j.value); if (!selected.length) { message.textContent = 'Select jobs to clear or restore.'; return; }
                e.target.disabled = true;
                try { const r = await request('suiteedge_migrator_clear_jobs','',{jobs:JSON.stringify(selected),restore:suiteedgeJobConfig.archived ? '1' : '0'}); const errors = r.results.filter(x => !x.success); if (errors.length) message.textContent = r.message+' '+errors.map(x => x.message).join(' '); else location.reload(); }
                catch(error) { message.textContent = error.message; } finally { e.target.disabled = false; }
            });
            document.querySelectorAll('.suiteedge-cancel').forEach(button => button.addEventListener('click',async () => {
                try { await request('suiteedge_migrator_cancel',button.dataset.job); location.reload(); } catch (error) { message.textContent = error.message; }
            }));
            document.querySelectorAll('.suiteedge-recover').forEach(button => button.addEventListener('click',async () => {
                if (!confirm('Review the report and current page first. Restore the interrupted page to its pre-migration backup? This discards the partial write and any edits present when the recovery report was created.')) return;
                button.disabled = true;
                try { await request('suiteedge_migrator_recover',button.dataset.job); location.reload(); }
                catch (error) { message.textContent = error.message; button.disabled = false; }
            }));
            document.querySelectorAll('.suiteedge-review-recovery').forEach(button => button.addEventListener('click',async () => {
                if (!confirm('Refresh the recovery snapshot to the current page state? Inspect the report again before choosing Restore.')) return;
                try { await request('suiteedge_migrator_review_recovery',button.dataset.job); location.href = '?page=suiteedge-migrator-reports&job='+encodeURIComponent(button.dataset.job); }
                catch (error) { message.textContent = error.message; }
            }));
            document.querySelectorAll('.suiteedge-rollback').forEach(button => button.addEventListener('click',async () => {
                if (!confirm('Restore the fields changed by this job? Pages with newer edits will be protected.')) return;
                button.disabled = true;
                try {
                    const result = await request('suiteedge_migrator_rollback',button.dataset.job);
                    message.textContent = result.message + ' ' + result.errors.join(' ');
                    if (!result.errors.length) location.reload();
                } catch (error) { message.textContent = error.message; }
                finally { button.disabled = false; }
            }));
        })();
        </script>
        <?php echo '</div>';
    }
}
