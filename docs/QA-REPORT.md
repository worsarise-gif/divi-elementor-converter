## Version 1.3.2 visual update

Text blocks retain source widths/caps and horizontal alignment without vertical-margin resets. Divi Sections retain their white default and explicit transparent/gradient/hover surfaces. Page reports disclose absent sitewide content and inherited theme defaults. The exact export, public original geometry, regression and release verification are recorded in [FIBERGLASS-VISUAL-1.3.2.md](FIBERGLASS-VISUAL-1.3.2.md). Existing staging acceptance remains open; the plugin does not reconstruct missing global templates or automatically read all Theme Customizer styles from a source URL.

## Version 1.3.1 update

The exact Fiberglass Pools report/export reproduction, attachment link fix, Force Fullwidth mapping and widget size-cap correction are recorded in [FIBERGLASS-POOLS-1.3.1.md](FIBERGLASS-POOLS-1.3.1.md). Existing protected Apply/rollback checks remain active; staging acceptance and source document localization remain publisher tasks.

# Plugin QA report - updated October 8, 2026

Current 1.3.0 bounded module expansion: native Call To Action and Person composites, source context/default spacing, Person media discovery and native short-ID validation. Both modules remain PARTIAL pending source/editor/staging acceptance. Local syntax, semantics, both database regressions, responsive browser, packaged Apply/rollback and Plugin Check pass; see [MODULE-UPGRADE-P01-QA.md](MODULE-UPGRADE-P01-QA.md). Earlier release evidence below is retained.

Current 1.2.5 grid repair: report 4d1353dc still contained an unresolved dgbm_blog_module and no replacement mapping. The standard grid now converts automatically to the registered Ace Post Grid widget. Original-export analysis, assigned-page saved Apply, rollback, offset-aware post queries and native template insertion are tested on both local backends. Ace must remain active for this widget. Source posts are not imported, unsupported query/layout modes remain protected and staging/publishing acceptance remains open. See [BLOG-GRID-1.2.5.md](BLOG-GRID-1.2.5.md).

Final 1.2.5 checks: 19 integration suites and 4,100 assertions per database backend, 96 browser assertions across eight widths and the real upload/analyze/Apply flow, and installed-package WordPress Plugin Check with no findings. PHP 8.0.30 and 8.3.35 syntax/standalone checks pass. The ZIP and both installed copies match the runtime source byte-for-byte. Detailed evidence is in test-evidence.json.

Earlier 1.2.4 verdict: the direct-Apply edit race and code parser-limit defects are corrected. The newer report 807178f3 confirms the source URL resolved three earlier failures; only Learning Center's third-party grid remained blocked. An explicit saved native Elementor template replacement now allows the configured five-page batch to apply and roll back on both QA backends. Source-link settings/recommendations and actionable REST/report diagnostics are corrected. This does not establish automatic extension fidelity or staging/publishing acceptance. See [BATCH-FIX-1.2.4.md](BATCH-FIX-1.2.4.md); the earlier diagnosis remains in [BATCH-REPORT-384C-ANALYSIS.md](BATCH-REPORT-384C-ANALYSIS.md).

Earlier 1.2.3 accuracy evidence: the reported team layout defects were corrected. Four layouts applied and rolled back on both disposable database backends with verified explicit source-ID mappings; Learning Center's third-party grid remained blocked. That configured test does not establish the default mixed-export workflow or cover the newly reproduced edit race. See [ACCURACY-RELEASE.md](ACCURACY-RELEASE.md) and [PRODUCTION-READINESS.md](PRODUCTION-READINESS.md).

Historical RC.12 verdict: the original archive was an incomplete scaffold. The hardened Classic/Flexbox release candidate passed the supplied Home export's native conversion, embedded image import, actual template insertion, rollback and bounded browser layout/motion QA. The following RC findings retain their original scope.

RC.10 resolves the two reported Header menu/logo blockers with explicit Main Menu mapping, native WordPress widgets, portable responsive controls and source button-anchor stacking. See HEADER-REGRESSION.md and current test-evidence.json. Global Theme Builder conditions and the real staging menu still require acceptance.

RC.9 implements all six requested workflow/fidelity areas, with native controls, saved-output guards, safe archival, per-page statuses and direct/saved crash recovery. See MIGRATION-IMPROVEMENTS-RC9.md and test-evidence.json.

RC.4 repaired missing portable styles, optimized selectors, source spacing/gutters, font registration and image/button controls. RC.5 adds the observed true/CSS parallax and horizontal scroll adapters, actual image tooltips, severity/action diagnostics and negative-margin specificity. The earlier browser/sign-in blocker is resolved: 32 layout and 31 motion assertions pass. See REPORT-WARNINGS-RC5.md.

RC.6 repairs theme-inherited button/title underlines, preserving source decoration and ordinary links. The active compatibility stylesheet also repairs earlier scoped imports without page writes or reconversion. Native control validation, actual template insertion and 83 browser assertions verify the change; see LINK-DECORATION-REGRESSION.md.

RC.7 resolves the real footer's unsupported logo-home link and missing contact-button icon settings. All five reported conversion warnings are resolved, with assigned-page and new-draft Apply/rollback coverage and retained published/unsupported-binding guards. See FOOTER-REGRESSION.md.

RC.8 resolves the reported Get More Customers conversion warnings and preserves native FAQ semantics/appearance. Both database backends exercise real Apply/rollback and write protections; 115 browser checks and 20 deactivated-plugin checks pass. See CUSTOMERS-REGRESSION.md.

RC.3 adds recommended source/media/link settings and explicit Analyze/Apply mode selection, verified with real Home and assigned-page regression cases. Completed analysis results no longer imply page writes. See RECOMMENDED-SETTINGS.md.

The user's failed Home dry-run report exposed an admin-only alignment validation defect in RC.1. RC.2 accepts Elementor's declared left/right aliases and adds full admin/optimized frontend coverage on both databases, plus responsive image alignment in the authenticated AJAX workflow. The exact failure was reproduced before repair. See [REPORT-REGRESSION.md](REPORT-REGRESSION.md).

RC.11 resolves the two actionable warnings in the successful two-page Header/E-Commerce report. Native URL images preserve differing source alt without shared Media Library edits, while scoped text CSS preserves the actual hover color. See REPORT-WARNINGS-RC11.md and current test-evidence.json.

RC.12 adds bounded UAE template targets and source menu presentation. Actual UAE frontend assignment, conditions, saved Apply and rollback are verified on disposable sites. Source menu links/labels are shared content and not silently overwritten. See THEME-BUILDER-RC12.md.

## Baseline failures

The original ZIP contained 120 PHP files: 28 empty files and 58 constructor-only TODO files. Every registered module converter and BackupEngine was empty. The original standalone pipeline exited with code 255 because it unconditionally required a missing vendor/autoload.php.

Other audited failures included upload and conversion forms without nonces; client-supplied filesystem paths; only the first layout being read; timestamp job-ID collisions; incomplete shortcode fallback reconstruction; invalid nested responsive style settings; HTML written under the wrong Elementor control; destructive Woo clustering; missing backup/validation; unchecked metadata writes; no crash/concurrent-request safeguards; ignored conversion options; and fabricated analysis, history and report rows. The settings form advertised retention it did not implement.

## Implemented repair

The plugin now validates and stores source JSON privately, identifies all layouts, uses a quote-aware stack parser, shares one conversion pipeline across preview and writing, implements core module classes, emits deterministic native Elementor data, validates actual installed widgets/controls, defaults to dry-run, verifies target permissions, saves job-scoped backups, checks every metadata write, restores on write failure, uses job/page locks, supports cancellation, and protects newer edits during rollback.

Existing titles, slugs, publication state, source post_content, page settings, Yoast and FAQ metadata were verified untouched. New pages are drafts. Rollback restores plugin-owned fields and keeps newly created pages as drafts; imported attachments are retained. The admin screens now show real data and provide authenticated report/template downloads. Atomic, dynamic, sticky and provider-integration limitations are explicit. Unknown modules retain their full source shortcode subtree.

## Initial 1.1.0 evidence (historical)

- Standalone suite: 95 assertions on PHP 8.3.35 and PHP 8.0.30.
- Real WordPress integration: 108 assertions, WordPress 7.1.2, Elementor 4.3.3, PHP 8.3.35, SQLite Database Integration.
- HTTP workflow: 21 assertions covering login, real multipart upload, two layouts, analysis, CSRF rejection, dry-run and actual draft conversion, rollback, reports and Elementor template downloads.
- Actual Elementor frontend rendering was tested, including the CSS ID and native text/button content. This is not browser visual QA or proof of editor interactions.
- Real WordPress media sideloading and duplicate detection were tested using a controlled HTTP image fixture. Live external hosting/provider behavior was not tested.
- Two PHP runtimes were used for syntax checks. See test-evidence.json for final file counts.

These October 3 checks used ../../.qa-runtime/wordpress. The October 6 increment adds an isolated MariaDB site, real Home fixture and public source-link verification. No live site content was changed. Browser/editor, Pro delivery, real map/video interaction, Divi theme interoperability, PHP 7.4, MySQL 8 server and multisite still need acceptance.

## Rankings and remaining work

FEATURE-QA.md / FEATURE-QA.csv rank current feature areas. FUNCTION-QA.md / FUNCTION-QA.csv inventory current declared method/class rows and inherit the relevant feature's score. Inheritance rows point to shared converter implementations. Scores apply to the stated tests and limits, not universal fidelity or individual-method coverage; current counts and evidence are in test-evidence.json.

TODO.md records completed repairs and every remaining non-pass feature. Home passes the automated pilot; an unattended live 24-page conversion is not signed off.

## Technical references

The target data/control design was checked against the installed official Elementor package and its [data structure documentation](https://developers.elementor.com/docs/data-structure/) and [page content documentation](https://developers.elementor.com/docs/data-structure/page-content/). WordPress metadata handling follows the [post metadata documentation](https://developer.wordpress.org/plugins/metadata/managing-post-metadata/). Exact matching, private storage and safe HTTP validation replace the unsafe scaffold assumptions.

## Bar Counters increment - 1.4.0

The source-verified native Free bar subset is implemented and locally tested. It remains PARTIAL: source visual/default geometry and interactive editor/staging acceptance are open. [Mapping](module-upgrades/P05-MAPPING.md), [QA](MODULE-UPGRADE-P05-QA.md) and [machine-readable evidence](MODULE-UPGRADE-P05-EVIDENCE.json) record scope, assertions, exact package and outstanding gates. The module/variant missing backlog is 49; official fixtures for several subsequent families have been captured without adding unsupported mappings.
