# Ace Migrator for Divi and Elementor

Convert supported Divi 4 pages and portability JSON into editable Elementor pages, posts and UAE Header/Footer templates. Select or upload a source, map its destination, convert for review, then apply the saved result.

The admin includes a guided workflow, bulk conversion and application, module coverage, actionable reports, private history, protected rollback, and an in-plugin User Guide.

## Get started

1. Back up your site and test on staging. Activate Elementor and this plugin.
2. Open **Ace Migrator > Convert Site Page** to select a local Divi page, or **Pages & Posts** to upload Divi 4 JSON exports.
3. Map each selected layout to a distinct page or new draft. Verify import settings and source theme defaults.
4. Use **Convert Selected / download templates**. Review the report in **Migration Jobs**.
5. Use **Apply to Page** or bulk **Apply Selected** on the completed results. Check responsive appearance and interactions before publishing.

For global Divi Theme Builder exports, use **Headers & Footers** and select UAE Header/Footer templates. UAE must be active for template application. Existing UAE display conditions are preserved; new drafts need their assignments configured and must be published.

Read the [user guide](docs/USER-GUIDE.md), [compatibility and scope](docs/COMPATIBILITY.md), and [WordPress directory readme](readme.txt). This is independent software; it is not endorsed by Divi, Elementor, or UAE vendors.

## Requirements and limits

Declared minimums are WordPress 6.5 and PHP 7.4; installed Elementor may require newer versions. Administrators need `manage_options` and `unfiltered_html`, with `upload_files` for media. Production locks use MySQL 5.7.5+ / MariaDB 10+ session advisory locks.

Output is Classic Elementor Flexbox Containers and native widgets with portable scoped HTML/CSS where needed. Divi 5 block exports and Atomic output are unavailable. Third-party modules, shared globals, gallery IDs, arbitrary dynamic bindings, and provider integrations may require manual work. Elementor Pro integration acceptance is incomplete. Unsupported shortcode fallbacks and unconfigured forms block Apply.

Exports/jobs/backups are private non-autoloaded database options; imported media remains in the Media Library. No telemetry or vendor migration service is used. Optional source/asset requests and retained source fonts are disclosed in readme.txt. Deactivation and deletion retain migration records so reinstalling can recover reports and backups.

## Publishing and development

Version **1.8.0-rc.1**, GPL-2.0-or-later, WordPress.org contributor **KERVEE**. Directory approval is a separate WordPress.org review. The source workspace includes publishing steps in `docs/PUBLISHING.md` and product verification in `docs/PRODUCT-RELEASE.md`; those publisher documents are excluded from the installable ZIP.

An entered source URL enables public page/post/attachment link lookup unless explicitly disabled and survives recommendation resets. Reports record lookup settings and failures. Standard dgbm_blog_module grids convert automatically to an editable Ace Post Grid, preserving supported card styles, metadata, query controls and pagination. This widget requires Ace Migrator to remain active. Source posts are not imported. Unsupported extension layouts/queries can still use an explicitly configured native saved Elementor container template; its provider remains required. Reconvert unresolved older jobs after correcting settings.

Source-only QA inventories: `docs/FEATURE-QA.md`, `docs/FUNCTION-QA.md`, `docs/QA-REPORT.md`, `docs/TODO.md`. Release-candidate diagnosis history is in `docs/RELEASE-CANDIDATE-HISTORY.md`. Internal QA documents and tests are excluded from the distribution ZIP.

Version 1.3.0 adds bounded native Call To Action (`et_pb_cta`) and Person (`et_pb_team_member`) compositions. Title/body/button and portrait/name/role/biography/social surfaces remain separately editable. Source column/device context controls their default padding and portrait arrangement. Complex whole-module links, nested source modules and critical unaccepted surface styles retain protected fallback. See [P01 mapping](docs/module-upgrades/P01-MAPPING.md) and [QA handoff](docs/MODULE-UPGRADE-P01-QA.md) in the source checkout. Reconvert affected layouts; existing saved output is not rebuilt automatically.

Version 1.3.2 preserves block-width Text modules, horizontal alignment, source vertical margins and Divi section default white surfaces. Explicit source widths, caps, colors, transparency and gradient base/hover colors retain precedence. Page reports explain which sitewide content is absent from layout JSON. Reconvert affected layouts; updating the plugin does not rebuild earlier saved output. See [visual diagnosis](docs/FIBERGLASS-VISUAL-1.3.2.md) in the source checkout.

Standalone checks, from this plugin directory:

```text
php tests/block-layout.php
php tests/attachment-links.php
php tests/business-compositions.php
php tests/fonts.php
php tests/accuracy.php
php tests/run.php
php tests/motion.php
php tests/text-decoration.php
php tests/footer.php
php tests/customers.php
php tests/improvements.php
php tests/report-warnings.php
php test_pipeline.php
```

These source-only tests are not included in the ZIP. WordPress integration, HTTP, and crash suites require an isolated disposable site; they create/change data and terminate test workers. Never run them on production. Build and lint the distribution with `python tools/build-release.py` from the workspace root. No Composer install is required at runtime.

Version 1.4.0 adds a bounded Bar Counters adapter: real Divi parent/child presets, item order, zero/fractional values, track/fill palettes, independent title/percentage typography and responsive heights. Each item becomes a native Free Progress Bar. Unsupported critical track/surface modes preserve the complete source collection and block Apply. Native default geometry and fill timing remain adaptations requiring source visual review. See [P05 mapping](docs/module-upgrades/P05-MAPPING.md) and [QA](docs/MODULE-UPGRADE-P05-QA.md).

Version **1.5.0** adds ordinary WordPress post targets and a separate core `et_pb_blog` adapter. Existing post details are preserved; new targets remain drafts. Blog listings use explicitly mapped destination categories, fullwidth lists/responsive masonry and offset-aware independent pagination. Full-content/current-archive/non-post queries remain protected. Keep Ace Migrator active for Ace Post Grid. See [post workflow](docs/USER-GUIDE.md) and [mapping contract](docs/BLOG-POST-MAPPING.md).


## Convert a page on this site (1.6.0)

Open **Ace Migrator Ã¢â€ â€™ Convert Site Page**. Choose the Divi 4 source and the Elementor destination, or a new draft. Select the source itself only when you intend an in-place migration. An older page can be selected by ID. No export or JSON upload is required.

Select pages and review settings, confirm the destination and local menu/category suggestions, and verify inherited Source theme defaults. **Convert for review** saves a result without changing either page. Use **Apply to Page** in Migration Jobs to apply that completed output without a duplicate job. **Convert and apply to page** performs both steps directly. Existing target titles, URLs, status and SEO are retained; backups and rollback remain available.

Each owner-bound selection lasts one hour and creates one job. Source changes, preset/global-layout changes, or target edits invalidate stale application. Select again and reconvert to include newer edits. Page content, supported stored Divi 4 presets and readable referenced Divi Library globals are captured; local media IDs can be reused. Theme Builder templates, display conditions, custom font registrations and sitewide theme/custom CSS need their existing separate workflows or review. Divi 5 is outside this workflow. Use the separate Elementor to Divi screen for reverse conversion. A currently active Elementor source cannot be used as a Divi source.


## Elementor to Divi (1.7.0)

Open **Ace Migrator > Elementor to Divi**. Choose the current Elementor source and a Divi page or a new draft; review the assignment, then convert for review. Apply the saved result in Migration Jobs after resolving blockers. Apply requires an active Divi 4 theme/Builder and each required module provider. No page is changed during review; existing target details remain intact and new targets stay drafts.

Classic layout/widget/style adapters report unmapped settings by element path. Unchanged local forward migrations created by this release can restore their exact original Divi content only while their recorded Elementor/global/source dependencies still match. Unsupported widgets, arbitrary nesting, compiled Elementor-dependent HTML, dynamic behavior and Pro forms/loops remain protected. Reverse Posts, template conditions, Divi 5 and Atomic output are not accepted. Compare native Divi editing and responsive rendering on staging before publishing. See the [reverse mapping contract](docs/REVERSE-MAPPING.md) for the adapter list, guards and acceptance limits.


## Reverse repair candidate (1.8.0-rc.1)

Accepted complex Classic Elementor trees now retain their source flex/grid surfaces, responsive controls, Icon Lists and indexed Nested Accordion answers in scoped Divi Code. Each result reports complete element/setting coverage and its Code editing limitation. Existing Formidable forms and source fonts/CSS are checked before Apply; missing or changed providers remain protected. Default kit globals, empty icons/globals, valid label tags, link normalization and native px-width guards are repaired. Read `docs/REVERSE-MAPPING.md` for the contract.

This is a staging candidate. The automated suites and browser projection pass, but licensed Divi 4 frontend and Visual Builder checks are pending. Reconvert the original source, review dependencies/coverage, Apply the new saved result to a draft, compare devices and interactions, and verify rollback before publication. Old jobs are immutable and do not inherit converter fixes.
