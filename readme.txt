=== Ace Migrator for Divi and Elementor ===
Contributors: KERVEE
Tags: migration, page builder, layout, conversion, templates
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: elementor
Stable tag: 1.8.0-rc.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Convert supported Divi 4 and Classic Elementor pages in both directions, with coverage reports, saved Apply and rollback.

== Description ==

Release candidate: licensed Divi 4 frontend and Visual Builder acceptance remains pending. Test on staging before production publication.

Ace Migrator helps you move supported Divi 4 layouts into Elementor while keeping control over what is applied to your site.

Select a Divi 4 page already on this site, or upload a Divi portability JSON export. Choose the destination and convert for review. Each job saves an Elementor result and a report. Apply the completed result to its original destination when you are ready, or download the JSON template for manual Elementor import.

**What you can do**

* Choose a local source Divi page and target Elementor page without exporting or uploading JSON. Convert in place only when explicitly selected, or create a new draft.
* Protect changed local sources, stored presets and referenced Divi Library globals with private snapshots and single-use selection sessions.
* Convert one layout or select several layouts from multiple JSON exports.
* Map each layout to a distinct WordPress page, blog post or new draft; keep existing post metadata.
* Migrate separate Divi Theme Builder headers and footers to Ultimate Addons for Elementor (UAE) Header/Footer templates.
* Apply an individual completed result or several selected results without creating duplicate conversion jobs.
* Review module coverage, errors, warnings, causes, and recommended actions.
* Preserve supported responsive spacing, typography, button padding, accordion settings, and menu styling.
* Import embedded or external images and reuse exact Media Library matches.
* Map source menu references to existing WordPress menus; optionally recover public source menu-label image icons.
* Use protected backups and rollback for fields changed by a migration.
* Clear finished jobs from the list without deleting pages, posts, templates, or backups.

The WordPress admin includes a guided workflow, environment information, field instructions, and a User Guide with troubleshooting.

**Elementor to Divi 4**

Choose Elementor to Divi in the admin. Select the source and destination, convert for review, resolve coverage blockers, then Apply the saved result. Reverse output is Divi 4 shortcode content. Classic layouts use bounded semantic mappings; unchanged proven local forward migrations created by this release can restore their original Divi source. Complex accepted Classic flex/grid trees, Icon Lists and Nested Accordions can retain their original structure and compiled controls in Divi Code. These children are edited as HTML/CSS rather than separate native Divi modules. Formidable embeds retain the existing verified local form/provider; submissions and delivery are not reimplemented. Font/CSS/provider freshness is checked before Apply. Every required provider must be active. Pro forms/loops, unknown widgets/settings, unaccepted nested compositions, reverse post/template targets, Divi 5 and Atomic output remain protected. Native Divi editor/rendering acceptance is still required on staging.

**Blog posts**

Select an existing WordPress post to preserve its post details, or create a new post draft during Apply. Core Divi Blog listings use editable Ace Post Grid with mapped destination categories, fullwidth lists/responsive masonry, character excerpts and independent pagination. Keep Ace Migrator active. Layout JSON does not import the post database; use WordPress post export/import first when moving sites. Full-content/archive/other-post-type modes remain protected.

**Supported scope**

Forward inputs are local Divi 4 pages and portability JSON. Output uses Classic Elementor Flexbox Containers and native widgets, with scoped portable HTML/CSS where needed. Common layouts, text, headings, images, buttons, blurbs, accordions/toggles, and navigation are supported. Scan your own export for module coverage. Call To Action and Person now have bounded native Free compositions; whole-module links, complex portrait modes and source visual/editor acceptance still require review.

Visual fidelity depends on the source modules, theme defaults, fonts, assets, and destination theme. Compare desktop, tablet, and mobile and test interactions before publishing. Unsupported shortcode fallbacks and unconfigured forms block application. Elementor Pro provider integrations have not completed release acceptance.

Divi 5 block exports, Atomic output, arbitrary dynamic bindings, shared global synchronization, and cross-site gallery IDs are outside the accepted scope. This plugin is independently developed by Ace and is not affiliated with or endorsed by Elegant Themes, Elementor, or Brainstorm Force.

**Requirements**

WordPress 6.5+, PHP 7.4+, and active Elementor. Your Elementor version may require newer WordPress/PHP versions. Administrators need manage_options and unfiltered_html; media import also needs upload_files. UAE (formerly Elementor Header & Footer Builder) is optional and required only for direct Header/Footer template application. Production locks require MySQL 5.7.5+ or MariaDB 10+ session advisory locks.

**Privacy and external requests**

There is no telemetry and no vendor migration service. Exports, jobs, and rollback backups are stored privately in non-autoloaded database options, associated with the importing administrator. Reports and templates can contain source content, URLs, and metadata. Imported images are stored in your Media Library.

When you enable the corresponding conversion settings, the plugin may contact:

* Source or asset hosts to import images. Embedded export images are tried first.
* The source site's public WordPress REST API to resolve Divi page-ID links.
* The source site's public homepage to inspect header/footer menu-label images.

These are user-specified source hosts, not a fixed third-party service. They receive your server IP and requested URLs; their own privacy policies apply. Disable these settings to avoid the corresponding requests. Source font references retained in converted layouts may contact font hosts when visitors view the destination page.

Defaults: 256 MB source storage, 30-day cleanup for unreferenced sources, and 90-day cleanup for eligible jobs. Backups protect their jobs and sources indefinitely. Active/recovery jobs and created target records are protected. Cleanup requires WP-Cron. Deactivation stops cleanup and retains data. Deletion also retains migration records to preserve rollback evidence; reinstalling restores access. Converted pages/templates and imported media remain intact.

== Installation ==

1. Back up your site and test the first migration on staging.
2. Install and activate Elementor.
3. Upload this ZIP through Plugins > Add New > Upload Plugin, then activate it.
4. Open Ace Migrator > Overview or User Guide.
5. For global templates, also activate Ultimate Addons for Elementor and use Headers & Footers.

== Frequently Asked Questions ==

= How do I migrate a page? =

Export its layout using Divi Builder > Portability > Export. In Ace Migrator > Pages, upload the JSON and select Map pages. Choose a destination, verify the suggested settings, then use Convert Selected. Open the report in Migration Jobs. After review, use Apply to Page on that saved result. New targets stay drafts until you publish them.

= Why did a completed job not update my page? =

Analyze only saves a report and converted output. It does not update the assigned target. Use Apply to Page on the completed job. A downloaded JSON template must be imported and inserted manually in Elementor; it is a separate workflow.

= Can I migrate several pages? =

Yes. Upload up to 24 JSON files (20 MB per file; hosting limits may be lower), select layouts, and map each to a distinct destination. In Migration Jobs, select completed results and use Apply Selected to Pages / Templates. Each result stays bound to its original target.

= How do I migrate headers and footers? =

Export each Header/Footer layout separately from Divi Theme Builder. Use Headers & Footers to map it to an existing UAE template or a new draft of the correct type. Convert, review, then Apply to UAE Template. Existing UAE display rules are preserved. New drafts need UAE display conditions and publication. Divi assignment conditions are not included in layout JSON.

= Does JSON include my menu links and dropdown icons? =

Divi exports contain menu references, not the WordPress menu hierarchy. Select a destination menu from Appearance > Menus. Optional public source menu image lookup can restore supported image icons omitted from the JSON. Differences and unsupported decorations are reported; shared menus are not rewritten.

= Does the converted blog grid need this plugin active? =

Yes, Ace Post Grid requires Ace Migrator to remain active. Standard DiviGear grids convert automatically to this editable Elementor widget with destination posts and older/newer pagination. Page JSON does not contain the blog posts. Unsupported layouts and source category-ID queries need a configured native template replacement; its widget provider remains required. Other portable native elements do not gain this dependency.

= Is Elementor Pro required? =

The supported page workflow uses Elementor Free. Pro features and provider integrations are not accepted as release-verified. Configure and test forms separately; a converted form must not be assumed to send mail.

= Can I undo an applied conversion? =

Use Rollback in Migration Jobs. Fields owned by the migration are restored from its backup when no newer target edits conflict. Imported media and new draft posts are retained. An interrupted write pauses for explicit recovery review.

= What does Clear Migration Jobs do? =

It archives selected eligible jobs from the visible list. It does not delete or modify converted pages/templates or rollback backups. View cleared jobs to restore entries.

= Is a pixel-perfect migration guaranteed? =

No. Builder structures, themes, fonts, and third-party modules differ. Review reports and compare the result at desktop, tablet, and mobile sizes before publishing. Unsupported content requires manual work.

= Does it preserve header fonts and custom fonts? =

Font families and responsive choices are preserved. Menus and scoped styles include native Elementor font-loading controls that survive template imports. Keep the conversion styles and Source font widgets in the template. Google fonts follow Elementor loading/local-hosting settings; system fonts use the visitor's installed fonts. Custom fonts must be registered on the destination under the same family name because layout JSON does not contain their files. The report identifies unavailable font registrations and disabled loading. Verify source theme defaults for Divi global font references.

= Where are the instructions? =

Open Ace Migrator > User Guide for setup, settings, result meanings, troubleshooting, supported scope, and data handling. The package also includes docs/USER-GUIDE.md and docs/COMPATIBILITY.md.

== Screenshots ==


1. Overview with a guided migration workflow and environment information.
2. Layout mapping, import settings, and separate Convert / Apply actions.
3. Migration Jobs with saved-result application and protected history management.

== Changelog ==

= 1.8.0-rc.1 =
* Preserve accepted nested Classic flex/grid layouts and responsive surfaces in a guarded Divi Code composition.
* Add Icon List, indexed Nested Accordion and independent keyboard interaction preservation.
* Repair empty globals/icons, valid text tags, blurb colors, source URL normalization, pixel-width guards and default kit capture.
* Validate existing Formidable forms, fields/actions and source font/CSS resources before application.
* Record complete descendant coverage, typed causes, inactive/compiled setting dispositions and separate issue/runtime counts.
* Native licensed Divi acceptance is pending; this is a staging candidate.

= 1.7.0 =
* Add local Elementor-to-Divi 4 page selection, private source snapshots, per-element coverage and protected unsupported behavior.
* Reuse saved/bulk Apply, target protection, drafts, backups, clearing, interrupted-write recovery and rollback.
* Add responsive styles, semantic widget adapters and a verified exact-original round trip for unchanged local forward migrations created by this release.
* Check Divi 4 and every required module provider before applying. Licensed Divi rendering/editor acceptance remains required on staging.

= 1.6.1 =
* Complete the Ace branding cleanup in product documentation and the source-only demo.
* Refresh getting-started instructions for local page selection.
* Retain upgrade identifiers, stored migration data and original source content.

= 1.6.0 =
* Convert a local Divi 4 page to a selected Elementor page or new draft without uploading JSON.
* Capture private source snapshots with presets and readable Divi Library globals; protect changed sources and destinations.
* Suggest verified local menu/category mappings and resolve local dynamic links without a public REST lookup.
* Apply completed output on its original job, with existing validation, backups and rollback.

= 1.5.0 =
* Add explicit WordPress post targets, new post drafts, saved/bulk Apply to Posts and post-specific reporting.
* Add a separate core Divi Blog adapter with fullwidth lists, responsive masonry, mapped destination categories, character excerpts and independent offset-aware pagination.
* Preserve post metadata and avoid resetting draft dates during rollback.
* Read automatic excerpts safely from converted Elementor text without executing nested widgets.
* Protect unsupported full-content, archive and non-post queries; disclose runtime requirements and update migration instructions.

= 1.4.0 =
* Convert verified Divi Bar Counters and child presets to editable native Elementor Free Progress Bars.
* Preserve zero/fractional percentages, item order, track/fill colors, separate typography and device heights.
* Retain unsupported track modes, critical item settings and invalid percentages as protected source fallbacks; existing Apply and rollback guards remain active.
* Disclose native default geometry and fill timing adaptations for visual review.

= 1.3.2 =
* Keep Divi Text modules full-width by default, including centered caption surfaces, while preserving explicit source widths and maximum widths.
* Preserve horizontal module alignment without resetting authored vertical spacing.
* Preserve Divi section white defaults and explicit transparent/color backgrounds, including solid surfaces underneath alpha gradients and source hover colors.
* Explain page export scope in reports: global headers/footers, external overlays and Theme Customizer fonts need separate source data or destination configuration.


= 1.3.1 =
* Resolve supported Divi page, post and attachment links through matching public WordPress REST endpoints, with typed diagnostics and protected fallback.
* Preserve attachment permalinks through saved Apply and template insertion; disclose source document dependencies instead of guessing destination page paths.
* Preserve Image Force Fullwidth, responsive on/off states and explicit module size constraints.

= 1.3.0 =
* Add bounded Divi 4 Call To Action and Person conversion using editable Elementor Free containers and widgets.
* Keep title, body, button, portrait, role and social styling on their own surfaces; preserve source column/device defaults and spacing.
* Retain unsupported whole-module links, nested modules and critical unaccepted surface settings for review instead of silently discarding them.
* Accept valid short hexadecimal IDs generated by Elementor template insertion while retaining malformed and duplicate-ID protection.
* Keep existing saved Apply, bulk target protection and rollback. Reconvert affected source layouts; compare the result on staging before publishing.


= 1.2.5 =

* Added automatic standard DiviGear blog-grid conversion to an editable Ace Post Grid widget.
* Preserves supported card/image borders, spacing, typography, metadata, image overlays, read-more buttons and responsive columns.
* Added bounded published-post queries, offset-aware older/newer pagination, empty-query diagnostics and runtime-dependency guidance.
* Retains optional native-template overrides and protected fallback for unsupported grid layouts/queries.
* Ace Migrator must remain active to render Ace Post Grid; page JSON does not import source posts.


= 1.2.4 =
* Preserve an entered source URL when restoring recommendations; enable public page-link resolution unless explicitly disabled.
* Normalize reference page URLs and report REST lookup failures, explicit mappings and source checksums. Support standard plain REST routes and www canonical URLs.
* Add explicit saved Elementor template replacements for unsupported dgbm modules. Replacement query, pagination and design require review; source blog posts are not imported.
* Protect direct and saved Apply from target edits during media preparation. Enforce parser limits for code modules and preserve self-closing code boundaries.

= 1.2.3 =

* Corrected portrait shadows, image height/aspect ratio, responsive radius, blurb body typography and invalid nested paragraphs.
* Preserved Divi grid breakpoints, tablet columns, and hidden spacer columns; retained explicit responsive widths.
* Compiled native responsive values at Divi's breakpoints without changing destination settings; preserved inherited text/button line heights and enabled native transform groups.
* Added directional border mappings and inherited source body font weight.
* Improved theme-default recommendations for mixed uploads and source-origin edits; reports include effective source settings.
* Distinguished inactive parallax from unsupported third-party blog grids and unresolved destination links.

= 1.2.2 =

* Fixed loading of menu, responsive, and scoped font families through native Elementor font controls.
* Preserved font names with punctuation and non-Latin characters, and added actionable missing-font/loading diagnostics.
* Updated product branding to Ace and used the supplied logo.

= 1.2.1 =

* Updated product branding, author, admin navigation, documentation, and listing assets to KERVEE.
* Retained existing migration jobs, settings, backups, and upgrade identifiers.

= 1.2.0 =

* Added a consistent, responsive admin interface and simpler navigation.
* Added an in-plugin User Guide, field instructions, troubleshooting, and environment information.
* Improved upload, mapping, report, settings, and empty-state guidance.
* Added WordPress directory metadata, GPL license, privacy disclosure, and publishing documentation.
* Includes bulk conversion, saved individual/bulk Apply, job archiving, UAE template migration, and protected rollback from the tested 1.2.0 release-candidate series.

== Upgrade Notice ==

= 1.7.0 =
Adds Elementor to Divi 4 for supported Classic page layouts. Review coverage first. Unsupported settings block Apply. Use licensed Divi 4 on staging for native editor, frontend and responsive acceptance. Old jobs remain in their original direction.

= 1.6.1 =
Ace branding and documentation update. Existing settings, jobs, backups and saved conversions are retained.

= 1.6.0 =
Adds local Divi 4 source/target page selection. Review source theme defaults and reports before applying. Divi 5 and Elementor-to-Divi conversion are not supported.

= 1.5.0 =
Enable Posts in Elementor Settings. Upload individual post layouts in Pages & Posts and select their matching post. Reconvert core Blog modules and map destination categories. Old saved output is not rebuilt. Keep Ace Migrator active for Ace Post Grid.


= 1.4.0 =
Upload and reconvert layouts with Bar Counters. Previously saved outputs are not rebuilt. Review native bar geometry and fill behavior; unsupported modes remain protected.

= 1.3.2 =
Reconvert affected layouts to correct caption widths and section backgrounds. Existing saved outputs are not rebuilt. Export global headers/footers separately and verify Source theme defaults for inherited fonts.


= 1.3.1 =
Reconvert layouts with attachment links or Image Force Fullwidth. Existing saved outputs are not rebuilt. Attachment documents remain on the source site unless explicitly mapped to separately uploaded destination documents.

= 1.3.0 =

Reconvert Call To Action and Person layouts to gain the new native compositions. Review source defaults and unsupported modes before Apply. Existing saved outputs, jobs, pages and backups are retained.


= 1.2.5 =

Upload and reconvert standard DiviGear blog pages to gain Ace Post Grid. Keep Ace Migrator active for this widget. Blog posts must exist on the destination. Old failed jobs are not rebuilt automatically.


= 1.2.4 =

Reconvert unresolved jobs after correcting source links or choosing a native saved-template replacement. Unsupported blog-grid design/query requires explicit configuration. Existing pages, history and backups are retained.

= 1.2.3 =

Reconvert affected pages to include corrected image, blurb and responsive layout mappings. Review source theme defaults and apply the new saved result. Unsupported third-party blog grids remain blocked.

= 1.2.2 =

Reconvert affected headers to include portable font-loading controls. Install matching custom fonts on the destination. Existing settings and migration history are retained.

= 1.2.1 =

KERVEE branding update. Existing settings, jobs, backups, and saved conversions are retained.

= 1.2.0 =

Finish active migration requests before upgrading. Existing jobs, targets, backups, and saved results are retained. Use fresh conversions when source layouts or destination menus have changed.
