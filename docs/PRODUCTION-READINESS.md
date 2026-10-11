# Production readiness - 1.2.0-rc.12, October 7, 2026

**Release decision: hardened Classic/Flexbox release candidate. Native Home content conversion and recovery pass automated QA; final live migration approval remains open.** No production pages were changed. Public reference-site REST metadata was read to verify source links.

RC.2 corrects the user-reported `image.align` dry-run failure by accepting Elementor's declared selector aliases without rewriting physical alignment. The original error was reproduced with full admin control metadata; the real Home pilot, integration and production regressions now also pass in that context. See [REPORT-REGRESSION.md](REPORT-REGRESSION.md) for diagnosis, the expanded test coverage and retry instructions.

RC.3 adds recommended media/source/link defaults and explicit Analyze / Apply mode selection. History, HTML reports and JSON outcomes distinguish completed analysis from assigned-page writes and expose blocking modules. See [RECOMMENDED-SETTINGS.md](RECOMMENDED-SETTINGS.md). Its 39 new regressions pass on each database; source REST checks in this increment use controlled fixtures.

RC.6 repairs theme-inherited button/title underlines, including existing scoped imports without page writes or reconversion. Source decoration and ordinary links remain editable. See [LINK-DECORATION-REGRESSION.md](LINK-DECORATION-REGRESSION.md).

## RC.10 header

The real Header fixture passes explicit destination-menu conversion, assigned Apply/rollback and library insertion on both databases. Desktop/mobile navigation and a disabled migrator pass bounded Edge QA. Missing/empty/deleted/cyclic menus block application. Source button stacking no longer lets a wrapper block menu links. See HEADER-REGRESSION.md. Actual staging menu links, custom breakpoints and global Theme Builder display assignments remain acceptance items.

## RC.9 requested improvements

Accordion and button padding fidelity, individual/bulk saved-result Apply, checked-source bulk conversion and job clearing/restoration pass bounded automated QA. Migration screens avoid the WordPress 7.1 cross-document transition conflict during polling. See [MIGRATION-IMPROVEMENTS-RC9.md](MIGRATION-IMPROVEMENTS-RC9.md) and current test-evidence.json. Exact staging pixels, custom fonts and licensed integrations remain acceptance items.

## Real Home pilot

The supplied `C:\Users\ay\Downloads\Home.json` is a Divi 4 portability export with builder versions up to 4.27.6. It contains 239 module instances and 26 URL-keyed embedded images. Its raw schema exposed gaps that synthetic tests had missed.

| Source module | Instances | Result |
|---|---:|---|
| Section | 12 | Native containers, gradient overlays and slanted dividers |
| Row | 29 | Native containers, preset merge, gutters and mobile stacking |
| Column | 66 | Native containers and fractional tracks |
| Text | 80 | Native text editor, numeric font weights, scoped H1-H6 styles and headline device variants |
| Button | 4 | Native buttons, real colors/borders and resolved page URLs |
| Image | 21 | Native media widgets and source alt/title data; tooltip titles never become visible captions |
| Icon | 13 | Native icons using the installed Font Awesome glyph mapping |
| Video | 2 | Native hosted video controls and preview/play styles; actual playback unverified |
| Blurb | 12 | Native icon boxes with actual glyphs, text and links |

All 239 modules are represented without a Divi shortcode fallback when their source page link mappings are supplied. One additional native HTML widget stores document-scoped supplemental CSS. All 26 real embedded images were MIME-validated and copied into the Media Library without fetching source image URLs. Target draft creation, real Elementor frontend rendering, CSS generation and rollback pass the pilot suite.

Fourteen distinct source page IDs were independently verified through the reference site's public WordPress REST API. The plugin accepts explicit target URL maps or an opt-in public source REST lookup, checking ID, type, published state and origin. General dynamic post/ACF/Woo bindings remain unsupported. Internal URL rewrites are applied only when a corresponding target page resolves; otherwise the working source URL remains and is reported.

Observed Divi4 horizontal curves and true/CSS image parallax now have portable adapters; hidden/disabled effects retain source visibility. The Home pilot has zero actionable conversion warnings and 14 distinct informational notes. Exact pixel equality, divider/hover behavior, video playback and manual editor interaction still need acceptance. The supplied HTML also includes a link to `https://sungaitoto.id`; it is preserved and must be reviewed for intended ownership/content. This report does not certify that source link.

## Real Footer pilot

The supplied Footer.json contains 52 source modules and six embedded images. RC.7 resolves its dynamic logo link to the destination frontend homepage and maps four contact-button glyphs, colors, physical placement and visibility. The five reported conversion warnings are resolved; ordinary source-page/media review warnings remain truthful. Assigned-page and new-draft Apply/rollback pass, as do unsupported-binding and published-page protections. There are 31 standalone assertions per PHP runtime, 34 WordPress assertions per backend and 96 browser assertions at six widths. See [FOOTER-REGRESSION.md](FOOTER-REGRESSION.md).

Apply writes a page; creating a global Theme Builder Footer and configuring display conditions remains a separate, unverified assignment step. No live or staging footer was changed. Home and Footer now have real-export coverage; other exports and licensed integrations remain open.

## Implemented release protections

- Unsupported shortcode fallbacks and forms with disabled delivery actions are retained for analysis, but block applying conversion before page creation/writing.
- Published targets with conversion/media/link warnings are protected unless an administrator explicitly acknowledges reviewed differences. Acknowledgement cannot bypass unresolved modules.
- Database-session advisory locks replace permanent option locks on MySQL/MariaDB. SQLite uses single-host OS locks for QA. Process termination releases locks; busy pages remain pending; connection loss stops writes.
- A persisted write plan distinguishes a completed interrupted write from a partial/changed state. Complete writes are reconciled without replay. Partial states pause for explicit original/current-state review and restoration; changes after the reviewed fingerprint are protected.
- Owner-filtered history is paginated. Cleanup scans at most 100 records per daily cron run. Every backup protects its linked job and source indefinitely; active/recovery jobs and created targets are retained. Sources have a configurable 20-1024 MB site quota (default 256 MB). Deactivation preserves data and unschedules cleanup.
- Target validation checks actual registered controls plus media, slider, dimensions, URL and repeater shapes. Optimized Elementor stacks omit some UI options, so full enum validation is not claimed.
- Supplemental CSS is self-contained inside native Elementor HTML nested in a valid root container, scoped by document-specific custom classes that survive template insertion, and rejects markup/nested declarations. No Elementor Pro dependency is needed for these plain scoped rules. Optimized heading selectors and native heading font registration are exercised by the RC.4 template import regressions.

## Real Get More Customers pilot

The exact page export's nine conversion warnings are resolved by native Heading, scoped list typography and validated five-item accordion support. Source defaults preserve inherited Inter/dark headings while Home's explicit Montserrat remains unchanged. Apply and rollback pass for assigned and new drafts and reviewed published pages; warning-free native FAQs apply to published pages without acknowledgement. Unsupported nodes and unreviewed media/link warnings still block writes. See [CUSTOMERS-REGRESSION.md](CUSTOMERS-REGRESSION.md).

## Acceptance evidence

| Check | Verified scope |
|---|---|
| Standalone | 118 assertions on PHP 8.3.35 and 8.0.30 |
| WordPress/Elementor integration | See current per-backend counts in test-evidence.json; WP 7.1.2 / Elementor 4.3.3 / PHP 8.3.35 |
| Production regressions | 29 assertions per database: published guards, fallback blocking, contention, legacy lock protection, recovery, schema shapes, icons, REST validation/cache and retention/pagination |
| Alignment alias regressions | 27 assertions with full admin metadata and 26 with optimized frontend metadata per database; desktop/tablet/mobile and complete dry-run target preservation |
| Hard termination | 3 scenarios per database: killed lock owner, partial write and completed write before job bookkeeping |
| Real Home pilot | 40 assertions; 239 native modules; 26 real embedded images; rendering/CSS/rollback |
| Authenticated HTTP | 24 assertions: login, multipart upload, multiple layouts, CSRF, visible mode buttons, dry-run/apply, reports, downloads and rollback |
| Actual template save/insertion | 14 assertions per database: both Elementor ID rewrites, stable CSS classes, nested style preservation, frontend rendering and malformed profile rejection |
| Motion / severity diagnostics | 23 standalone assertions on each PHP runtime and 20 WordPress assertions per database |
| Button / linked-title decoration | 23 standalone assertions per PHP runtime, 29 WordPress assertions per database, and 83 Edge assertions at five widths |
| Get More Customers | 44 standalone per PHP, 39 WordPress per backend, 115 browser checks at six widths and 20 with migrator deactivated |
| Footer | 31 standalone per PHP, 34 WordPress per backend and 96 browser assertions at six widths |
| Edge browser | 32 layout assertions at six widths and 31 motion assertions; lazy backgrounds, reduced motion, tooltips, duplicate imports, transforms and compact fallback |
| Live public link resolver | See test-evidence.json; checked separately from offline fixture tests |
| Syntax and package | See test-evidence.json and package manifest for final counts/hashes |

The MariaDB server was downloaded from its official archive and SHA-256 checked against the published checksum. It runs only on loopback in an isolated workspace data directory, without installing a Windows service. Test sites, credentials and uploads are disposable and excluded from distribution.

## Remaining acceptance requirements

The earlier browser process/account-review blocker is resolved. Isolated Edge checks cover six viewport widths and the observed motion adapters, with actual Elementor imports and public Google font loading. These bounded checks do not establish exact pixel equality or manual editor UI acceptance. Full-page screenshots of true-parallax elements outside the current viewport do not represent their appearance when scrolled into view. Licensed Divi/Pro/Woo, external Page Manager and additional export fixtures remain unavailable. See REPORT-WARNINGS-RC5.md and VISUAL-REGRESSION.md.

Atomic output, arbitrary dynamic bindings, shared global synchronization, Woo cart/checkout migration, email opt-in/provider delivery and native sticky are not accepted capabilities. Cross-site numeric gallery mapping still needs its own source fixture/asset map. MySQL 8 server-specific execution, multisite and PHP 7.4 were not tested. Only Home, Footer and Get More Customers have real-corpus coverage; this does not certify all 24 intended reference pages.

See FEATURE-QA.md/CSV for all 87 feature scores, FUNCTION-QA.md/CSV for all 287 method/class rows, and TODO.md for every remaining partial, unsupported or unverified feature.

## Primary references

The implementation was checked against the installed official Elementor source and its [data format](https://developers.elementor.com/docs/data-structure/), [CSS filter controls](https://developers.elementor.com/docs/editor-controls/group-control-css-filter/) and [responsive controls](https://developers.elementor.com/docs/editor-controls/responsive-control/). Live-session locking follows the documented [MySQL locking functions](https://dev.mysql.com/doc/refman/8.4/en/locking-functions.html). The isolated database setup follows [MariaDB's Windows ZIP installation instructions](https://mariadb.com/docs/server/server-management/install-and-upgrade-mariadb/installing-mariadb/binary-packages/installing-mariadb-windows-zip-packages).

## RC.11 latest report

The supplied two-page report already completed both writes. Both remaining warnings now have fixes and bounded real-export/native-rendering regressions. Explicit differing alt uses a local URL image and omits attachment srcset; shared descriptions remain unchanged. Source normal/hover link colors survive responsive styles and disabled migrator/no-JavaScript operation. Existing job output is historical; create fresh conversions. See REPORT-WARNINGS-RC11.md and current test-evidence.json. Exact staging appearance remains an acceptance item.

## RC.12 UAE Header / Footer

The separate UAE workflow is accepted within the tested free UAE 2.9.5 scope. The actual supplied Header and Footer convert into the correct elementor-hf types, preserve existing assignments and render through UAE. Twelve public source menu-label images are restored as local portable decorations. New drafts remain unassigned. 52 real UAE assertions per database; 126 active and 118 inactive browser assertions including no JavaScript; UAE 2.9.5. Full standalone, WordPress, HTTP/crash and existing frontend regressions were rerun. Licensed Pro, arbitrary theme/plugin CSS and the actual staging site remain acceptance items. See THEME-BUILDER-RC12.md.
