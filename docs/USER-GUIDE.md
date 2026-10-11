# Ace Migrator user guide

Convert supported Divi 4 layouts into editable Elementor pages, blog posts and UAE Header/Footer templates. Start on staging and keep a full-site backup.

## First page migration

1. Activate Elementor. Open the source page in Divi Builder and use **Portability > Export** to save its JSON layout.
2. Open **Ace Migrator > Pages & Posts**. Choose up to 24 JSON files and click **Upload and Scan**. Scanning validates and stores exports privately; it does not update pages or import media.
3. Open **Module Coverage** if the source uses third-party modules. Use **Map pages / posts** to continue. Mapping sessions last one hour.
4. Check the layouts you want. Assign each one to a distinct destination page or a new draft. Verify the source origin and import settings.
5. Use **Convert Selected / download templates** for your first run. This creates an Analyze only job and saves converted output. It changes neither the assigned target nor the Media Library. Image import happens during Apply when enabled. Optional public REST and menu lookup can still contact the source site during conversion.
6. Open **Report** in Migration Jobs. Review errors, warnings, informational notes, and recommended actions. Unsupported shortcode fallbacks and unconfigured forms block Apply.
7. Use **Apply to Page** on the completed result. For several results, select their checkboxes and use **Apply Selected to Pages / Posts / Templates**. The same jobs and original destinations are used.
8. Open each destination in Elementor. Check desktop, tablet, mobile, links, media, accordions, menus, buttons, and forms. New targets stay drafts until you publish them.

**Apply to pages** on the mapping screen converts and applies immediately. A published target with warnings requires explicit review acknowledgement. A warning acknowledgement cannot bypass a blocker. A downloaded JSON template is for manual Elementor import and insertion; downloading or importing it does not update the target selected in this plugin.

## Headers and footers

1. Export each layout from Divi Theme Builder separately; page JSON does not include global templates.
2. Activate **Ultimate Addons for Elementor (UAE)**, formerly Elementor Header & Footer Builder.
3. Open **Ace Migrator > Headers & Footers**. Upload, scan, then choose **Map Header / Footer templates**.
4. Choose a Header or Footer target explicitly. Existing UAE templates must be editable and have the matching type; new templates are unassigned drafts.
5. Map source menu references to a destination menu from **Appearance > Menus**. Review a suggested Main Menu before conversion. Source JSON contains menu IDs, not links or hierarchy.
6. Optional source menu image lookup can recover supported menu-label images from the public source homepage. Review label/hierarchy differences. Shared menus are not rewritten.
7. Convert, review, then **Apply to UAE Template**. Existing UAE display conditions, exclusions, roles, and publication state are preserved. Configure display conditions and publish new drafts in UAE before they appear on your site.

## Import settings

| Setting | When to use it |
| --- | --- |
| Import images | Usually enabled. Tries embedded export images first, then safe external URLs. Imports may contact source/asset hosts and create Media Library attachments. |
| Reuse Media Library | Usually enabled. Reuses exact image matches instead of unnecessary duplicates. |
| Source site URL | Enter the original Divi site URL. A pasted page URL is normalized to its scheme and host. Verify suggestions inferred from media URLs. Entering a URL enables link lookup unless you explicitly switch it off. |
| Resolve source links | Queries the public source WordPress REST API for supported Divi page, post and attachment links. Disable if unavailable and supply explicit mappings. |
| Advanced link mappings | JSON object, for example `{"51689":"/contact/"}`. Keys are source page, post or attachment IDs; values are destination HTTP(S) URLs or site-relative paths. |
| Source theme defaults | Match missing Theme Customizer fonts, inherited body font weight, colors, and row width to the source site. Explicit module values take precedence. |
| Preserve menu image icons | Header/Footer lookup from the public source homepage. Unsupported or ambiguous decorations need manual editing. |
| Reviewed warnings | Your acknowledgement of documented differences on published destinations. It does not suppress errors. |

**Use recommended settings** restores suggested image/menu options and untouched theme suggestions. It preserves your entered source URL, explicit link-lookup choice, edited theme defaults and warning acknowledgement.

An export without media cannot prove its source origin. When other selected exports establish one consistent source, their style defaults are suggested without automatically assigning link provenance to the entire batch. Verify the source origin. Changing it updates untouched suggested theme fields; manual theme edits are retained. Reports record the effective profile so width and typography differences can be traced.

## Jobs, reports, and recovery

Analyze only / converted for review means output is saved but not applied. Applied means the result was written to its exact destination. Failed / Conversion not applied means at least one item could not complete; read its error and recommended action. A bulk job can contain both successful and failed items. Status is recorded per layout.

If the target or menu changed since analysis, create a fresh conversion against the current state. These checks protect newer work and icon mappings.

**Rollback** restores migration-owned fields when no newer changes conflict. It does not remove new draft posts or imported media. **Needs recovery** pauses an interrupted write: inspect the current target and original backup in the report, then use **Restore interrupted item** after review. Refreshing the recovery review is explicit.

**Clear Migration Jobs** archives eligible selected jobs; it does not modify pages, templates, or backups. Use **View cleared jobs** and **Restore Selected Jobs** to return entries to the list.

A successful conversion confirms that the supplied layout was processed; it does not prove visual parity with the entire source site. Page-only exports omit sitewide Theme Builder headers/footers and plugin-injected chat overlays. Inspect those separately and verify UAE display conditions. Confirm the source Theme Customizer body/heading fonts in **Source theme defaults**; a source URL used for link resolution does not extract all inherited theme styles.

## Troubleshooting

| Symptom | Action |
| --- | --- |
| Completed conversion did not update a page | Check mode. Analyze only needs saved-result Apply. Manual JSON template import is a separate workflow. |
| UAE target missing | Use Headers & Footers; activate UAE; set the template type to Header/Footer and ensure edit permission. |
| Dropdown links differ | Choose/edit the correct destination menu in Appearance > Menus. JSON does not contain the source hierarchy. |
| Caption backgrounds shrink or source sections become transparent in an older result | Reconvert with 1.3.2 or later and apply the new output. Divi Text blocks retain full width unless explicitly constrained; sections retain their default white surface. Keep the conversion styles widget. |
| Source header/footer or footer service-area text is missing | Check whether it exists in the supplied page layout. Export each Divi Theme Builder template separately, convert in Headers & Footers, apply to UAE and review its display conditions. |
| Fonts/spacing differ | Check source theme defaults and responsive values; review warnings; clear caches and regenerate Elementor CSS if needed. |
| Portrait shadows or cards differ in an older saved result | Reconvert with version 1.2.3 or later, verify the source row maximum width/body weight, then apply the new output. Updating the plugin does not rewrite old saved templates. |
| Third-party blog grid blocks Apply | Reconvert standard DiviGear grids with 1.2.5 or later to use Ace Post Grid. Keep Ace Migrator active. Unsupported layouts/queries can use a configured native template under Module template replacements (optional). See below. |
| Dynamic page-ID link blocks Apply | Check the recorded source URL and lookup flag. Enable public source link lookup or map the named source ID in Advanced link mappings. HTTP failure details appear in the report. Create a fresh conversion after correcting settings; Apply cannot repair old unresolved output. |
| Attachment link blocks Apply in an older report | Upgrade to 1.3.1 and run a fresh conversion with the original source URL and public lookup enabled. Reports identify attachment IDs and media REST failures; explicit URL mappings work when lookup is unavailable. |
| Source attachment link preserved | The original attachment permalink remains; it can differ from the direct file URL. Image import does not copy documents. Before retiring the source site, upload the document separately, map its source ID to the destination URL, and reconvert. |
| Images ignore Force Fullwidth in an older result | Reconvert with 1.3.1. Module width/max-width and active desktop/tablet/mobile values still constrain images. |
| Internal link remains on source | Create/migrate the matching destination page and verify its slug or explicit page-ID mapping. Reconvert once the destination resolves. |
| Saved result no longer applies | Reconvert after target/menu edits. Do not bypass the change guard. |
| Upload fails | Check JSON format, 20 MB file limit, 24-file count, PHP upload limits, and private storage quota. |
| Form does not send mail | Configure and test actions, recipients, mail delivery, and providers separately. Pro acceptance is incomplete. |

## Data and privacy

Exports, jobs, and backups are private database options associated with the importing administrator. Reports/templates may include private content and URLs. Redact them before sharing. No telemetry or vendor migration service is used. Optional image, REST, and source menu requests go to user-specified hosts and disclose your server IP/requested URL. Source font references may contact font hosts when visitors view a converted page.

Settings defaults: one layout per request, 256 MB source quota, 30 days for unreferenced sources, 90 days for eligible jobs. Daily cleanup is bounded and needs WP-Cron. Backups protect their jobs/sources indefinitely; active/recovery/created-target records are protected. Deactivation stops cleanup. Deactivation and deletion retain private records; reinstall to regain access. Converted content and imported media remain.

Read [compatibility and scope](COMPATIBILITY.md) for limitations. For support, include versions, job mode, error, reproducible steps, and a sanitized sample export. Test on staging before publishing.

## DiviGear blog-grid conversion

Upload and reconvert a standard `dgbm_blog_module` page with version 1.2.5 or later. Leave **Convert automatically with Ace Post Grid** selected. Review **Ace Post Grid** in Elementor: destination query, 12-post default, columns, metadata, fonts, card/image borders, button spacing and pagination are editable. The initial conversion retains Divi's 980px/767px responsive thresholds. The source layout JSON has no post database; migrate blog posts, featured images and categories separately if they are absent. An empty destination query produces an actionable warning.

Keep **Ace Migrator active** to render Ace Post Grid. It needs neither DiviGear nor Elementor Pro/UAE. Updating the plugin does not rebuild old failed jobs or previously downloaded templates; upload/reconvert, review, then Apply the new output. The source-reference URL controls optional link lookup and cannot import missing blog posts or fix an unsupported grid.

The automatic adapter covers the conventional grid layout with recent/popular ordering (comment count), optional random ordering without pagination, offset, published destination posts, metadata icons, optional excerpts, image overlay and older/newer navigation. Source category IDs are never reused across sites. Masonry/full-width layouts, related/custom-post-type queries and numeric source category filters stay protected and need the template workflow below. Unknown active settings are reported. Omitted defaults and inactive residual settings are recorded as information.

## Optional blog-grid template replacement

1. Make sure the destination contains the blog posts and media you want to display. Page layout exports do not include those posts.
2. In Elementor, build a container with an installed native post-grid widget. UAE Basic Posts can provide a simple grid; use a provider with category filtering and pagination if your source needs those features, such as Elementor Pro Posts / Loop Grid or an appropriate UAE Pro widget. Confirm the provider's features before choosing it.
3. Configure the destination query, post count/order, pagination, featured images, metadata, buttons, typography, spacing and device layouts. Save the container as an Elementor template.
4. Upload the original page JSON again. In **Map pages > Module template replacements (optional)**, select that template for the reported `dgbm_blog_module` in the correct source file. The 100 newest templates are shown. Use **Or enter template ID** for an older saved template; that ID takes priority over the dropdown and receives the same permission/content checks. Without a replacement, standard grids convert automatically; unsupported module layouts/queries remain protected and block Apply.
5. Convert for review, then apply the saved result. The selected template's native elements are copied into the output, with new element IDs. Later edits to the library template do not change the saved result. Reconvert to incorporate them.

This explicitly replaces the whole source module with your configured native design. The explicit replacement bypasses the automatic adapter: source query values and styles are not merged into the replacement. All occurrences of that module type in the selected source file use the same replacement. Configure separate exports/templates when those occurrences need different designs. Replacement containers use their destination Elementor responsive controls. Keep the widget provider active; Ace is required if the chosen replacement contains Ace Post Grid, otherwise it is not needed to render the copied native widgets.

Replacement templates must be editable Classic containers/native widgets. Missing providers, unknown controls, unresolved shortcode widgets, nested template widgets, invalid data and unconfigured form actions are rejected. Direct and saved Apply protect target edits made during media preparation; restore/reconvert after a stale-target rejection.

## Header and custom fonts

Divi 4 uses tablet styles through 980px and mobile styles through 767px. The conversion style widget preserves those thresholds even when the Elementor site uses different breakpoints. It also preserves inherited text line height and Divi's button surface line height. Keep that widget in the imported template. Its CSS stores the converted values; reconvert if you change responsive values, or update the corresponding conversion CSS when editing them manually.

The converter keeps font family names, weights, styles, and device-specific choices. It uses Elementor's font catalogue and registered font providers; it does not replace a valid source family with an Elementor default. Font loading works after importing the downloaded template. Keep the conversion styles and Source font widgets in the template.

Divi global font references use the source theme defaults you confirm during mapping. Google fonts follow Elementor's loading and local-hosting settings. Custom fonts need their font files installed/registered on the destination under the same family name; layout exports do not include those files. Missing registrations and disabled loading appear as actionable report warnings. Reconvert affected headers after updating the plugin or resolving a missing font, apply the new saved result, then regenerate Elementor CSS and clear page/CDN caches. Compare desktop, tablet, mobile, and dropdown states.

## Bar Counters

Version 1.4.0 converts the verified `et_pb_counters` / `et_pb_counter` subset into a native Free container and Progress Bars. Parent and child presets are resolved first. Item order, decimal values from 0 through 100, zero values, labels, track/fill palettes, independent typography and responsive track heights are preserved. Keep the conversion styles widget; CSS surface states and native fonts travel with the template. No additional widget provider is needed to render these bars.

A report information note identifies native fill timing and default 22px geometry as adaptations. Compare these against the original, including the source theme defaults. Critical unaccepted track backgrounds, surface CSS/links, nested modules, unknown item settings or invalid values retain the complete source collection and block Apply. Reconvert old jobs to use the new adapter. Circle Counter remains an explicitly reported numeric approximation. This release does not claim all Divi modules or full visual parity.

## Individual blog posts and core Divi Blog listings (1.5.0)

1. For a move between sites, use WordPress Tools > Export/Import to migrate the original post records, authors, categories, tags and media first. Divi layout JSON does not contain that database. Review imported featured images and metadata.
2. Enable **Posts** under Elementor Settings > General > Post Types. Export an individual post's layout through Divi Portability and upload it in **Pages & Posts**.
3. Choose its matching destination under **Blog Posts**. The newest 100 posts are listed; choose the older-post option and enter the destination post ID for another record. Page and post IDs are validated against their explicit type and edit permission. A new post destination is created only as a draft during Apply. When the JSON omits the title, its filename supplies an initial draft title; review it before publishing.
4. Convert for review, inspect the report, then use **Apply to Post** or bulk Apply. Existing post title, slug, author, dates, body, excerpt, categories, tags, featured image, SEO and publication state are retained. Builder metadata changes to Elementor and Divi rendering is disabled. Clearing jobs retains posts; rollback restores only migration-owned fields and does not reset draft dates.
5. For an exported layout containing a core **Divi Blog** module, select the destination for every source ID under **Blog category mappings**. IDs differ between sites. Missing/deleted categories protect the source module or stop saved Apply; no source category ID is silently reused. Import/create categories first.
6. The supported core Blog subset uses **Ace Post Grid**: published WordPress posts, fullwidth list or responsive masonry, count, offset, separate pagination per widget, metadata visibility, title level, featured image, character-based excerpts and source typography. A converted Elementor post without a manual excerpt is read from saved text/heading content without running dynamic widgets. Keep Ace Migrator active for this widget. Featured images use Divi's standard 8:5 crop; review **Featured image ratio / fit** in Elementor when your source site used custom cropping filters, which are absent from layout JSON.

Full-content feeds, current-archive queries, other post types and custom overlay icons, unaccepted card shadows are protected rather than converted to a different query. Configure and save an appropriate Elementor container template, select it under module replacements, then reconvert if you need these modes. That explicitly replaces the module's design/query. Existing DiviGear standard-grid support retains its own schema and limits. Layout JSON does not supply global single-post Theme Builder templates or display conditions; configure those separately.

Compare the source at desktop, tablet and mobile widths. Source theme defaults and masonry geometry are editable approximations where the export omits inherited values. Conversion checks do not certify every Divi version, live theme, licensed Pro provider or editor session.


## Convert a page on this site (1.6.0)

Open **Ace Migrator â†’ Convert Site Page**. Choose the Divi 4 source and the Elementor destination, or a new draft. Select the source itself only when you intend an in-place migration. An older page can be selected by ID. No export or JSON upload is required.

Select pages and review settings, confirm the destination and local menu/category suggestions, and verify inherited Source theme defaults. **Convert for review** saves a result without changing either page. Use **Apply to Page** in Migration Jobs to apply that completed output without a duplicate job. **Convert and apply to page** performs both steps directly. Existing target titles, URLs, status and SEO are retained; backups and rollback remain available.

Each owner-bound selection lasts one hour and creates one job. Source changes, preset/global-layout changes, or target edits invalidate stale application. Select again and reconvert to include newer edits. Page content, supported stored Divi 4 presets and readable referenced Divi Library globals are captured; local media IDs can be reused. Theme Builder templates, display conditions, custom font registrations and sitewide theme/custom CSS need their existing separate workflows or review. Divi 5 and Elementor-to-Divi conversion are outside this workflow. A currently active Elementor source cannot be used as a Divi source.


## Elementor to Divi (1.7.0)

Open **Ace Migrator > Elementor to Divi**. Choose the current Elementor source and a Divi page or a new draft; review the assignment, then convert for review. Apply the saved result in Migration Jobs after resolving blockers. Apply requires an active Divi 4 theme/Builder and each required module provider. No page is changed during review; existing target details remain intact and new targets stay drafts.

Classic layout/widget/style adapters report unmapped settings by element path. Unchanged local forward migrations created by this release can restore their exact original Divi content only while their recorded Elementor/global/source dependencies still match. Unsupported widgets, arbitrary nesting, compiled Elementor-dependent HTML, dynamic behavior and Pro forms/loops remain protected. Reverse Posts, template conditions, Divi 5 and Atomic output are not accepted. Compare native Divi editing and responsive rendering on staging before publishing.


## Reverse repair candidate (1.8.0-rc.1)

Accepted complex Classic Elementor trees now retain their source flex/grid surfaces, responsive controls, Icon Lists and indexed Nested Accordion answers in scoped Divi Code. Each result reports complete element/setting coverage and its Code editing limitation. Existing Formidable forms and source fonts/CSS are checked before Apply; missing or changed providers remain protected. Default kit globals, empty icons/globals, valid label tags, link normalization and native px-width guards are repaired. Read `docs/REVERSE-MAPPING.md` for the contract.

This is a staging candidate. The automated suites and browser projection pass, but licensed Divi 4 frontend and Visual Builder checks are pending. Reconvert the original source, review dependencies/coverage, Apply the new saved result to a draft, compare devices and interactions, and verify rollback before publication. Old jobs are immutable and do not inherit converter fixes.
