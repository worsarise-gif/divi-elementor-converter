# Compatibility and conversion scope

Ace Migrator converts supported **local Divi 4 page content or portability JSON** into **Classic Elementor Flexbox Containers**. A captured page or layout export is not a complete site backup. Fonts, global theme defaults, menus, template assignments, and provider settings may live outside the captured content.

**Local page workflow (1.6.0):** choose a source and destination in Convert Site Page. Supported stored Divi 4 presets and readable referenced `et_pb_layout` globals are captured without executing source shortcodes. Review theme defaults and verified local menu/category suggestions. Changed source dependencies or destination edits block stale application. Source selection supports the 100 newest pages plus explicit IDs for older pages. Apply and rollback retain the existing page-preservation contract. Divi 5 is not supported by this local forward workflow. Reverse page conversion is a separate workflow added in 1.7.0. Actual Divi runtime/customizer integration and complete visual parity still require staging acceptance.

| Requirement | Details |
| --- | --- |
| WordPress / PHP | Declared minimums: WordPress 6.5 and PHP 7.4. Integration QA uses WordPress 7.1.2 with PHP 8.3; standalone checks also run on PHP 8.0. Minimum versions have not all received runtime acceptance. |
| Elementor | Active Elementor required. Current integration QA uses 4.3.3, Classic containers. Installed Elementor may require newer WordPress/PHP than this plugin's minimums. |
| UAE | Optional for pages; required for direct Header/Footer application. QA uses Ultimate Addons for Elementor 2.9.5 and its `elementor-hf` template type. |
| Database | Production: MySQL 5.7.5+ / MariaDB 10+ session advisory locks. MariaDB 11.4 QA and single-host SQLite QA are available. |
| Permissions | `manage_options`, `unfiltered_html`; `upload_files` for media. Multisite may restrict `unfiltered_html` to super administrators. |
| Upload | Divi 4 JSON, up to 24 files, 20 MB per file. PHP/web-server limits may be lower. |

Common native mappings cover layouts, text, headings, images, buttons, blurbs, accordions/toggles, and menus. Version 1.3.0 adds bounded native Free Call To Action and Person compositions. Explicit supported module values take precedence over source defaults. Module Coverage scans the uploaded layout; an available mapping does not mean every possible attribute combination has been visually verified.

Review responsive appearance, interactions, external links, media, inherited fonts, motion, and complex custom CSS. Unsupported shortcode fallbacks and unconfigured forms block application. Source Divi menu IDs require explicit destination menu mapping. Optional public source image lookup cannot recover every type of mega-menu or decoration.

Divi 4 layouts retain their 980px tablet and 767px mobile transitions using portable scoped CSS. Native responsive styles are compiled from the installed Elementor control definitions; controls remain editable and the destination kit is unchanged. Uniform four-, five-, and six-column grids retain tablet columns and hidden spacer behavior. Source margins, inherited text line height, button line height, and transform activation are preserved. Keep the conversion style widget when inserting or editing a template. Reconvert after changing responsive values; the stored CSS reflects the values at conversion time. Full-page acceptance on customized destination themes remains necessary.

Image surface shadows/borders and native image height are mapped separately from module width, spacing, and backgrounds. Blurb paragraphs are normalized to valid native box-description HTML; complex block structures that do not fit that control require a Text Editor/container migration. Version 1.2.5 automatically converts the standard DiviGear dgbm_blog_module grid to the editable Ace Post Grid. It uses published destination posts, source card/image borders, spacing, fonts, metadata switches, overlay, buttons and older/newer pagination. The export omits standard defaults; the adapter makes 12 newest posts, all categories and 3/2/1 columns explicit for review. Ace Migrator must remain active to render this widget. Source posts/media/categories are not imported from page JSON. Masonry/full-width, related/custom-type and source category-ID queries require an explicitly configured saved native Elementor template replacement. Other extension modules remain protected. The replacement provider remains required; Pro-specific acceptance is incomplete.

Not accepted for automatic fidelity: Divi 5 block exports, Atomic output, arbitrary dynamic bindings, shared global synchronization, cross-site gallery IDs, and third-party provider integrations. Elementor Pro integration acceptance remains incomplete. A converted form is not evidence of working mail or provider delivery.

Backups cover migration-owned fields, not your entire WordPress installation. New drafts and imported attachments remain after rollback. Existing titles, slugs, publication state, SEO/FAQ metadata, and UAE assignment settings are preserved. Later conflicting changes block application or rollback rather than silently overwriting newer work.

## Call To Action and Person

The dedicated Divi 4 tags `et_pb_cta` and `et_pb_team_member` convert to editable native Free compositions. Title/body/button and portrait/name/role/biography/social styles remain on separate surfaces. CTA background/column context determines its default module padding; explicit four-side device values override those defaults. Person portrait stacking follows source column and row context at Divi breakpoints. Standalone portraits without their source column receive a placement warning. Portrait URLs are included in asset discovery.

Supported body markup and social destinations remain editable. Native social glyphs approximate Divi ETicons; discontinued Google+ links and missing default CTA arrows are reported. Missing source palettes require inherited-default review. Whole-module links, nested modules, compound responsive background/palette changes, hover padding and critical unaccepted surface variants preserve the original and block Apply; Person's ordinary background-color hover is supported. Complex crop/overlap/custom CSS, full source visual parity and interactive editor acceptance remain bounded pilot work. They are not universal module-fidelity claims. These compositions need no additional runtime widget provider; retain the conversion stylesheet and reconvert after changing responsive values.

## Text blocks and section surfaces

Version 1.3.2 preserves Divi Text modules as full-width blocks by default. Explicit desktop/tablet/mobile widths, maximum widths and zero dimensions remain source constraints. Horizontal module alignment changes only left/right margins. Divi Sections retain their default white base; rows and columns remain transparent unless styled explicitly. Active colors, images, transparent backgrounds and bounded gradients override defaults. Alpha gradients retain their solid base independently of the first stop; source hover colors remain effective.

Global Theme Builder templates, external overlays and Theme Customizer fonts are not contained in page-only JSON. Reports identify this scope as information. A warning-free result still needs comparison against the supplied source and the destination site. Reconvert after upgrading to rebuild affected output; existing pages and saved jobs are not silently changed.

## Font compatibility

Native Elementor font controls load families used by converted menus and scoped selectors, including fonts used only at responsive breakpoints or on hover. Family names are preserved independently of a fixed migrator whitelist. The destination Elementor font catalogue and registered custom-font providers determine file availability. Missing custom registrations and disabled Google loading are reported; unavailable font binaries are not invented or downloaded from arbitrary sources. System-font appearance depends on the visitor's operating system. Existing saved templates need a fresh conversion to gain the loading controls. Pro font-provider acceptance still requires testing against the publisher's licensed Pro installation.

## Bar Counters

Version 1.4.0 converts the verified `et_pb_counters` / `et_pb_counter` subset into a native Free container and Progress Bars. Parent and child presets are resolved first. Item order, decimal values from 0 through 100, zero values, labels, track/fill palettes, independent typography and responsive track heights are preserved. Keep the conversion styles widget; CSS surface states and native fonts travel with the template. No additional widget provider is needed to render these bars.

A report information note identifies native fill timing and default 22px geometry as adaptations. Compare these against the original, including the source theme defaults. Critical unaccepted track backgrounds, surface CSS/links, nested modules, unknown item settings or invalid values retain the complete source collection and block Apply. Reconvert old jobs to use the new adapter. Circle Counter remains an explicitly reported numeric approximation. This release does not claim all Divi modules or full visual parity.

## Individual blog posts and core Divi Blog listings (1.5.0)

1. For a move between sites, use WordPress Tools > Export/Import to migrate the original post records, authors, categories, tags and media first. Divi layout JSON does not contain that database. Review imported featured images and metadata.
2. Enable **Posts** under Elementor Settings > General > Post Types. Export an individual post's layout through Divi Portability and upload it in **Pages & Posts**.
3. Choose its matching destination under **Blog Posts**. The newest 100 posts are listed; choose the older-post option and enter the destination post ID for another record. Page and post IDs are validated against their explicit type and edit permission. A new post destination is created only as a draft during Apply. When the JSON omits the title, its filename supplies an initial draft title; review it before publishing.
4. Convert for review, inspect the report, then use **Apply to Post** or bulk Apply. Existing post title, slug, author, dates, body, excerpt, categories, tags, featured image, SEO and publication state are retained. Builder metadata changes to Elementor and Divi rendering is disabled. Clearing jobs retains posts; rollback restores only migration-owned fields and does not reset draft dates.
5. For an exported layout containing a core **Divi Blog** module, select the destination for every source ID under **Blog category mappings**. IDs differ between sites. Missing/deleted categories protect the source module or stop saved Apply; no source category ID is silently reused. Import/create categories first.
6. The supported core Blog subset uses **Ace Post Grid**: published WordPress posts, fullwidth list or responsive masonry, count, offset, separate pagination per widget, metadata visibility, title level, featured image, character-based excerpts and source typography. A converted Elementor post without a manual excerpt is read from saved text/heading content without running dynamic widgets. Keep Ace Migrator active for this widget. Featured images use Divi's standard 8:5 crop; source custom cropping filters are absent from layout JSON and require review of the editable ratio / fit controls.

Full-content feeds, current-archive queries, other post types and custom overlay icons, unaccepted card shadows are protected rather than converted to a different query. Configure and save an appropriate Elementor container template, select it under module replacements, then reconvert if you need these modes. That explicitly replaces the module's design/query. Existing DiviGear standard-grid support retains its own schema and limits. Layout JSON does not supply global single-post Theme Builder templates or display conditions; configure those separately.

Compare the source at desktop, tablet and mobile widths. Source theme defaults and masonry geometry are editable approximations where the export omits inherited values. Conversion checks do not certify every Divi version, live theme, licensed Pro provider or editor session.


## Elementor to Divi (1.7.0)

Open **Ace Migrator > Elementor to Divi**. Choose the current Elementor source and a Divi page or a new draft; review the assignment, then convert for review. Apply the saved result in Migration Jobs after resolving blockers. Apply requires an active Divi 4 theme/Builder and each required module provider. No page is changed during review; existing target details remain intact and new targets stay drafts.

Classic layout/widget/style adapters report unmapped settings by element path. Unchanged local forward migrations created by this release can restore their exact original Divi content only while their recorded Elementor/global/source dependencies still match. Unsupported widgets, arbitrary nesting, compiled Elementor-dependent HTML, dynamic behavior and Pro forms/loops remain protected. Reverse Posts, template conditions, Divi 5 and Atomic output are not accepted. Compare native Divi editing and responsive rendering on staging before publishing.


## Reverse repair candidate (1.8.0-rc.1)

Accepted complex Classic Elementor trees now retain their source flex/grid surfaces, responsive controls, Icon Lists and indexed Nested Accordion answers in scoped Divi Code. Each result reports complete element/setting coverage and its Code editing limitation. Existing Formidable forms and source fonts/CSS are checked before Apply; missing or changed providers remain protected. Default kit globals, empty icons/globals, valid label tags, link normalization and native px-width guards are repaired. Read `docs/REVERSE-MAPPING.md` for the contract.

This is a staging candidate. The automated suites and browser projection pass, but licensed Divi 4 frontend and Visual Builder checks are pending. Reconvert the original source, review dependencies/coverage, Apply the new saved result to a draft, compare devices and interactions, and verify rollback before publication. Old jobs are immutable and do not inherit converter fixes.
