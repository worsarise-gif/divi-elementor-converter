# Ace Divi to Elementor Migrator 1.2.0-rc.12

Converts supported Divi 4 JSON layouts to native Classic Elementor Flexbox Containers and Widgets. Read [the QA report](docs/QA-REPORT.md), [feature ranking](docs/FEATURE-QA.md) and [remaining todo](docs/TODO.md) before using it on published pages.

RC.2 fixes the reported `Invalid target control value: image.align` dry-run failure. Validation now accepts Elementor's declared legacy alignment aliases while preserving physical left/right values. See [the regression report](docs/REPORT-REGRESSION.md) for reproduction and verification.

RC.3 preselects recommended image import/reuse and suggests the source origin from WordPress media URLs. An unambiguous origin also preselects source REST page-link resolution. Home suggests `https://suiteedge.com`. The mapping form now offers explicit Analyze only / Apply to pages choices, and results distinguish an analysis from page updates. See [recommended settings](docs/RECOMMENDED-SETTINGS.md).

RC.5 resolves the latest Home report's missing parallax, horizontal motion and image tooltips; disabled source effects remain inactive. Informational styling notes are separated from actionable warnings, and reports explain causes and corrective actions. Browser QA also repaired Elementor's negative-margin reset so service badges overlap their photos. See [report diagnosis and verification](docs/REPORT-WARNINGS-RC5.md).

RC.4 repaired portable styles, heading selectors, font registration, source spacing/gutters and image/button controls. Editable source defaults suggest the reference site's measured 1280px row width, fonts and accent. Analyze and Apply to pages are separate visible buttons.

RC.6 removes theme-inherited underlines from converted buttons and linked icon/image-box titles while preserving source decoration and ordinary text links. Updating the active plugin repairs earlier scoped imports without reconversion. Clear caches and reload Elementor after upgrading. See [link decoration diagnosis and verification](docs/LINK-DECORATION-REGRESSION.md).

RC.7 resolves Footer.json's logo home_url binding against the destination homepage and preserves contact-button icons, colors, placement and hover/focus visibility. Re-upload Footer.json, analyze and apply a new job after reviewing any remaining source-link/media warnings. RC.12 also supports direct Apply to a UAE Footer template through the separate Header & Footer workflow. See [footer diagnosis and verification](docs/FOOTER-REGRESSION.md).

RC.8 resolves Get More Customers.json's standalone Heading, list typography and FAQ settings. It preserves inherited source fonts/colors, validates consumed accordion children and reports true blockers before warning review. Re-upload and create a fresh job; published pages with remaining media/link warnings still require explicit review. See [page diagnosis and verification](docs/CUSTOMERS-REGRESSION.md).

RC.9 adds individual/bulk saved-result Apply, checked-source bulk conversion, clearing/restoring jobs and accordion/button padding fidelity. See [workflow and verification](docs/MIGRATION-IMPROVEMENTS-RC9.md).

RC.10 converts the supplied Header using an explicit Main Menu mapping and native WordPress navigation widgets. It preserves logo homepage links and responsive menu styling/controls, protects menu identity and fixes button-wrapper stacking. Re-upload Header.json and create a fresh conversion. See [header diagnosis and verification](docs/HEADER-REGRESSION.md).

RC.11 fixes the latest Header/E-Commerce report warnings: scoped text-link hover colors and per-image source alt without shared media edits. Re-upload and create fresh output; see [diagnosis and verification](docs/REPORT-WARNINGS-RC11.md).

RC.12 adds a separate Header & Footer migrator for UAE templates, preserving existing display rules and creating unassigned drafts. It can restore safe source menu-label image icons omitted from Header.json, report menu differences and guard stale icon matches. See [workflow and verification](docs/THEME-BUILDER-RC12.md).

## Use

1. Install the supplied plugin ZIP on a staging WordPress site with Elementor active. The plugin header requires PHP 7.4+ and WordPress 6.5+; the installed Elementor version may require a newer WordPress version. Current integration testing used WordPress 7.1.2 / Elementor 4.3.3.
2. Open Divi Migrator > Import & Scan and upload Divi portability JSON files. All layouts are listed. The importing user needs manage_options and conversion needs unfiltered_html; image imports also need upload_files.
3. Review module coverage, map each layout to a distinct page or choose a new draft. If Navigation menu mappings appears, select the existing destination WordPress menu; Header suggests a unique Main Menu. Check the layouts you want and click Convert Selected / download templates for the first run. Image import/reuse are preselected; verify the suggested source origin, link resolution and source theme defaults. Use recommended settings restores those defaults and leaves your warning acknowledgement unchanged.
4. Read the actual report and warnings. Download a report or native Elementor template if needed.
5. If no single source origin is suggested, enter it and enable source REST link resolution or supply explicit dynamic page URL mappings. After reviewing the analysis, use Apply to Page beside a completed result or select results and use Apply Selected to Pages on the same jobs. The mapping screen also supports immediate Apply to pages. Published targets with warnings require explicit acknowledgement of reviewed differences. Unresolved Divi shortcodes and unconfigured forms block all page writes. Titles, slugs, publication state, SEO/FAQ data and original post_content are preserved. New pages are drafts.
6. Review the result in Elementor and test responsive appearance and all interactions. Supplemental heading/custom/filter CSS is stored in an editable HTML widget inside a container and uses classes that survive template insertion. It does not require Pro or this migrator to remain active. Source scroll/parallax effects and font availability require manual review. A Home layout export does not include global theme header/footer templates or third-party chat configuration. Pro forms/provider integrations have not been accepted for release.
7. Clear Migration Jobs archives selected finished/unneeded jobs without modifying pages. View cleared jobs retains reports and rollback; Restore Selected Jobs returns them to the list.
8. Roll back from Migration Jobs if needed. A page with newer edits is protected. New drafts and imported Media Library attachments are retained.

Source exports, jobs and backups are private non-autoloaded database options. Source storage defaults to a 256 MB site limit, configurable from 20 to 1024 MB. Settings configure daily bounded cleanup (default 30 days for unreferenced sources, 90 for eligible jobs). Every backup protects its job and source indefinitely. Active/recovery jobs and created target records are protected. Deactivation stops cleanup and retains all records. WP-Cron must run for cleanup to execute.

Production locks use live MySQL/MariaDB session advisory locks (MySQL 5.7.5+ / MariaDB 10+); a terminated session releases its locks. Lock loss stops writes. SQLite is supported as a single-host QA backend using OS file locks. An interrupted item with a completed planned write is recognized without replay; partial/changed state pauses at needs_recovery. Inspect the current page and original backup in its report, then use Restore interrupted item. Later edits block restoration; refreshing recovery review is explicit.

A mapping session expires after one hour. Job creation consumes checked layouts; unchecked layouts retain their original indices for later selection. Atomic is unavailable. Cross-site gallery IDs, shared global synchronization, arbitrary dynamic bindings and provider integrations remain outside the accepted conversion scope. The Home pilot and release limitations are documented in [PRODUCTION-READINESS.md](docs/PRODUCTION-READINESS.md). This is a release candidate pending browser/staging acceptance.

Before upgrading from 1.1, allow every active migration request to finish. A legacy option lock is preserved and blocks the new lock backend; clear it manually only after confirming those old requests have stopped. Age alone is not evidence that an old worker stopped.

## UAE headers and footers

Open **Divi Migrator > Header & Footer**, upload separate Divi Theme Builder layout exports, then choose **Map Header / Footer templates**. Select an existing UAE Header/Footer or a new draft of the correct type. Map source menu 32 to your destination Main Menu and review its links/labels. For the supplied Header, leave source menu image lookup and image import/reuse enabled with source origin https://suiteedge.com. Analyze, review the report, then use **Apply to UAE Template** or bulk Apply on the same job.

Existing UAE display conditions and user roles are preserved. New drafts require UAE display rules and publication before they appear on the site. Page content is not used as the Header/Footer destination. Source Divi assignment conditions are absent from layout JSON. Create fresh jobs for these new adapters; historical saved outputs are not rewritten.

## Development checks

No Composer install is required for the standalone suite:

```text
php tests/run.php
php tests/motion.php
php tests/text-decoration.php
php tests/footer.php
php tests/customers.php
php tests/improvements.php
php tests/report-warnings.php
php test_pipeline.php
```

On an isolated disposable WordPress site with the plugin and Elementor activated, a qa_admin user and localhost URL as configured in this workspace:

```text
wp eval-file tests/wordpress.php
wp eval-file tests/production.php
python tests/http_workflow.py
python tests/crash_workflow.py /absolute/path/to/disposable-wordpress
```

The suites create test pages/users/media, change permalink settings, inject failures and hard-terminate disposable PHP workers. Never run them on production. The HTTP suite expects localhost:8097 and its declared disposable credentials. Distribution ZIPs exclude tests and QA runtimes. The optional real-export pilot uses SUITEEDGE_HOME_FIXTURE and SUITEEDGE_HOME_LINKS environment variables with tests/home.php.

The full source remains in this directory. The original pre-repair archive evidence is outside the distribution in ../.qa-evidence. See docs/FUNCTION-QA.csv for every declared method/class and its current feature scope.
