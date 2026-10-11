# QA repair todo - 1.3.2

## Elementor to Divi workflow - 1.7.0

- [x] Add a separate current Elementor source / exact Divi destination page workflow with review-first conversion, older-page IDs and safe draft defaults.
- [x] Add independent Classic layout, widget and style adapters; record element paths and block unaccepted controls instead of silently omitting them.
- [x] Share private jobs, saved/bulk Apply, owner checks, target locks, durable backups, conflict protection, clearing and exact rollback.
- [x] Check source/global-kit freshness, saved-output integrity, destination identity and all required Divi 4 shortcode providers before a write.
- [x] Add a bounded per-page index for verified local forward migrations; restore original Divi content only when all recorded source/output dependencies still match.
- [x] Verify semantic guards, installed icon data, both databases, reverse browser forms/exports, source preservation and the exact distribution; resolve Plugin Check findings.
- [ ] Accept licensed Divi 4 Visual Builder/frontend output and responsive parity for each adapter on staging. Writer test shortcode providers are stubs.
- [ ] Expand boxed/compound layouts, surface/state styling, Pro loops/forms, reverse Posts/Theme Builder and Divi 5 only with paired builder fixtures and dependency/delivery contracts. See [reverse mapping](REVERSE-MAPPING.md) and [release evidence](REVERSE-RELEASE-1.7.0.md).

## Local source/target page workflow - 1.6.0

- [x] Add Convert Site Page with Divi 4 source and exact page destination, safe draft default, older-page IDs and explicit in-place selection.
- [x] Capture private page/preset/global snapshots without executing source shortcodes; protect source changes, unavailable globals, cycles, unknown schemas, Divi 5 and active Elementor sources.
- [x] Isolate owner-bound, expiring, single-use selection sessions from other tabs and JSON uploads.
- [x] Reuse conversion, media, native validation, saved Apply, backups and rollback; preserve source and destination metadata and avoid duplicate jobs.
- [x] Suggest verified local menus/categories and supported published page/post/attachment links; handle subdirectory local lookups without REST.
- [x] Fix native gallery control mapping, bounded attachment ID parsing and deleted-attachment protection; discover preset-only menu references.
- [x] Verify the installed ZIP on both databases, existing regressions, PHP 8.0/8.3, native browser forms at four widths, source preservation and rollback; resolve Plugin Check findings.
- [ ] Accept actual Divi runtime/storage variants, inherited Customizer defaults and complete source/editor visual parity on staging. See SITE-PAGE-QA.md.
- [x] Add the separate bounded Classic Elementor-to-Divi 4 workflow in 1.7.0; its native Divi acceptance and broader feature work remain above. Divi 5 still requires a separate schema and fixtures.

The original critical repair list is documented in QA-REPORT.md. Production hardening and the real Home pilot are detailed in PRODUCTION-READINESS.md.

## Module expansion research and implementation chunks

The October 8 catalogue/code comparison is in [MODULE-UPGRADE-RESEARCH.md](MODULE-UPGRADE-RESEARCH.md). [MODULE-UPGRADE-TODO.md](MODULE-UPGRADE-TODO.md) links 50 bounded chunks, and [MODULE-UPGRADE-INVENTORY.csv](MODULE-UPGRADE-INVENTORY.csv) lists every researched module/variant with its current status and proposed target. The 52 missing Divi 4 module/variant entries, one legacy Shop schema review, existing partial/unverified mappings, optional vendor candidates and separate Divi 5 track are distinguished. The baseline is retained. In 1.3.0 CTA and Person move to PARTIAL native compositions; 50 additions remain missing. [P01 QA](MODULE-UPGRADE-P01-QA.md) records the tested subset and pending publisher visual/editor/staging acceptance. Checking a research task alone is not evidence of module support.

- [ ] Complete F00 source fixtures/capability truth and select the first bounded module chunk.
- [ ] Complete P01 Call To Action/Person mappings and R00 acceptance before advancing the support matrix.
- [ ] Keep unavailable fixtures, licensed runtimes and non-equivalent target behavior explicit in each chunk's handoff.

## Publication blockers found in the October 8 batch review

See BATCH-FIX-1.2.4.md for implemented corrections and the newer 807178f3 report (four applied, one unsupported grid). The earlier reproduction remains in BATCH-REPORT-384C-ANALYSIS.md. Bounded local verification is not staging or publication acceptance.

- [x] **P1: Protect direct Apply against newer edits during media preparation.** Shared writer/backup checks preserve controlled concurrent edits for direct, saved and new-draft paths; 12 assertions per backend.
- [x] **P1: Resolve and validate dynamic page-ID links before each item's write.** Preserve explicit source/lookup choices, normalize pasted page URLs, and report missing URL, disabled lookup and REST causes. Unresolved dependencies still block that item before media/page mutation; partial batch outcomes stay explicit.
- [x] **Learning Center replacement workflow.** Source-scoped saved native templates copy a configured grid into the conversion; 37 assertions per backend include the real five-page batch and native post querying. Configure/accept the source-equivalent query, pagination and design on staging; the standard automatic adapter is now tested in 1.2.5.
- [x] **P2: Enforce the parser's shared node limit for code modules.** 10,001 paired/self-closing code and mixed modules reject; literal code content and self-closing boundaries are preserved.
- [x] **P2: Improve report context and link diagnostics.** Record effective settings, source checksums, explicit replacements/maps and REST outcomes; distinguish unsupported grids and retained destination links.
- [ ] **P2: Refresh recommendations for selected layouts and responsive CSS after supported editor changes.** Add targeted regression and editor acceptance coverage.

## Fiberglass Pools correction (1.3.1)

- [x] Dispatch supported page/post/attachment link tokens to matching public REST routes.
- [x] Preserve attachment permalinks through asset preparation, native template insertion and saved Apply; disclose document source dependencies.
- [x] Preserve Image Force Fullwidth, active responsive resets and outer width/max-width caps.
- [x] Retain protected fallback for malformed/unsupported bindings and verify failed lookup leaves the assigned page untouched.
- [ ] Publisher: install 1.3.2 or later on staging, reconvert the export, compare all devices and decide whether to upload/map the comparison document locally. See [diagnosis and QA](FIBERGLASS-POOLS-1.3.1.md).

## Fiberglass Pools visual correction (1.3.2)

- [x] Reproduce content-sized caption bars caused by flex auto margins; retain Divi Text block defaults without overriding explicit widths/caps.
- [x] Preserve horizontal alignment without resetting source vertical margins.
- [x] Preserve Divi Section default white and explicit transparent/color surfaces, including alpha-gradient bases and hover colors.
- [x] Record page export scope as information and explain separate global template/overlay/font configuration.
- [x] Verify native templates, direct/saved Apply, exact rollback and destination settings preservation on both QA databases; compare responsive caption geometry against the public original.
- [ ] Publisher: install 1.3.2 on staging, reconvert and apply the new result; verify inherited source fonts and separately migrate/configure the original Header/Footer exports. See [visual diagnosis](FIBERGLASS-VISUAL-1.3.2.md).

## Completed this increment

- [x] 1.3.0 bounded Call To Action and Person native compositions, source context/default spacing, media discovery and valid native short-ID acceptance; protected fallback, saved exact-target Apply/rollback and local regression (MODULE-UPGRADE-P01-QA.md). Full source/editor/staging acceptance remains open.

- [x] 1.2.5 standard DiviGear grid adapter, editable Ace Post Grid, source surface mappings, responsive typography, offset-aware destination pagination, explicit runtime dependency, empty-query diagnostics and original upload/analyze/Apply regression (BLOG-GRID-1.2.5.md).

- [x] 1.2.3 team portrait surface shadows, native image height/aspect ratio, blurb body typography and valid description HTML; measured grid breakpoints, empty spacer behavior, directional borders, body-weight defaults and source-profile reporting (ACCURACY-RELEASE.md).
- [x] Identify the actual third-party Learning Center grid in Module Coverage and diagnostics; keep Apply protected. The standard grid adapter is now tested; unsupported query/layout modes retain protected fallback.
- [x] Compile native responsive styles at the source breakpoints; preserve inherited text/button line heights, transform activation, and source margin/gutter priority.
- [ ] Accept Learning Center's automatically converted standard grid on the user's staging site, with its real post database/media and font loading. Unsupported variants still need a native-template override.
- [ ] Migrate or map the five career detail destinations before clearing their retained source-link warnings.
- [ ] Accept the new converted pages on the user's staging site at its actual breakpoints and loaded fonts; local browser QA is not staging approval.

- [x] Separate UAE Header/Footer target workflow, same-job bulk Apply, new drafts, preserved conditions and protected rollback (THEME-BUILDER-RC12.md).
- [x] Twelve actual source dropdown image icons, local import/reuse, source submenu padding, stale menu guards and no-JavaScript submenu fallback. Unsupported decorations and shared-menu differences remain actionable.

- [x] RC.11 text-link normal/hover/device colors and native per-instance alt preservation; real E-Commerce saved Apply/rollback and inactive/no-JavaScript portability (REPORT-WARNINGS-RC11.md).

- [x] Header menu mapping, native WordPress navigation/logo, responsive dropdown/keyboard controls, safe saved Apply and nonblocking button stacking (HEADER-REGRESSION.md).

- [x] Accordion fidelity (MIGRATION-IMPROVEMENTS-RC9.md).
- [x] Button surface padding (MIGRATION-IMPROVEMENTS-RC9.md).
- [x] Saved-result Apply to Page (MIGRATION-IMPROVEMENTS-RC9.md).
- [x] Bulk source selection / Convert Selected (MIGRATION-IMPROVEMENTS-RC9.md).
- [x] Bulk Apply Selected to Pages (MIGRATION-IMPROVEMENTS-RC9.md).
- [x] Clear / restore migration jobs (MIGRATION-IMPROVEMENTS-RC9.md).

- [x] Resolve Get More Customers' nine conversion warnings: native standalone Heading, scoped UL/OL typography and five-item FAQ. Preserve child presets/tags and block discarded styles/states; keep published review explicit and report genuine blockers first. Verified with 44 standalone, 39 WP per backend, 115 browser and 20 deactivated-plugin checks (CUSTOMERS-REGRESSION.md).

- [x] Resolve footer logo home_url binding and four button icon warning types; real assigned/new-draft Apply and rollback, preserved published/fallback guards, 34 WordPress assertions per backend and 96 browser checks (FOOTER-REGRESSION.md).

- [x] Repair button/linked title underlines from destination theme CSS; preserve source decoration, ordinary links and focus. Existing scoped imports repair without reconversion; 83 browser and 29 WordPress assertions per backend pass (LINK-DECORATION-REGRESSION.md).

- [x] Trace all 15 latest report messages; add true/CSS parallax, horizontal curve and all 21 actual image tooltip adapters.
- [x] Preserve six disabled column curves and intentionally hidden motion sections; reject malformed active settings and retain unsupported-effect warnings.
- [x] Separate informational notes from actionable warnings; add severity/cause/action diagnostics and truthful zero-write outcomes while preserving legacy records.
- [x] Correct Elementor's negative bottom-margin reset; pass 32 layout and 31 motion browser assertions, reduced motion and lazy backgrounds.
- [x] Repair supplemental CSS delivery through actual Elementor template save/insertion, stable classes and optimized heading selectors (VISUAL-REGRESSION.md).
- [x] Preserve partial spacing, source gutters, capped badge alignment, native image radius, button surface spacing and editable source theme defaults.
- [x] Show separate Analyze and Apply to pages submit buttons; missing mode remains analysis.
- [x] Recommended image import/reuse, unambiguous source-origin inference, source link resolution, explicit Analyze/Apply mode and truthful completion/blocker outcomes (RECOMMENDED-SETTINGS.md).
- [x] Fix reported image.align alias rejection and verify full admin/frontend metadata plus responsive image AJAX dry-run/apply/rollback (see REPORT-REGRESSION.md).
- [x] Exact Font Awesome standalone/icon-box mappings for Home
- [x] Resolve all 14 Home source page links and support explicit/opt-in REST mapping
- [x] Divi4 default preset schema adapter and inactive device/hover normalization
- [x] Numeric typography weights, responsive headline variants and scoped H1-H6 CSS
- [x] Native gradient overlays, slanted dividers, gutters, button/icon/video styles
- [x] Import all 26 real embedded Home images and handle inline HTML assets
- [x] Reject unresolved shortcode/form writes and protect published targets with warnings
- [x] Live-session/OS locking, connection loss guard and busy-page retry
- [x] Hard-termination tests for lock release, partial and completed write recovery
- [x] Explicit recovery review/restore and newer-edit protection
- [x] Owner-filtered paginated history, protected bounded retention and storage limits

## Remaining partial, unsupported and unverified acceptance items

- [ ] **Gallery (2/5, PARTIAL)** - Native image-gallery only when source origin confirms the same site and IDs resolve. Cross-site/foreign IDs preserve the whole shortcode; embedded asset mapping pending.
- [ ] **Tabs / toggle / accordion (3/5, PARTIAL)** - Native repeaters plus observed FAQ tags, child presets, bounded icon equivalents and default appearance. Supported child/parent styles and one-open/all-closed states now have adapters. Mixed tags, multiple initially open items, nested modules, unknown advanced styles, arbitrary tabs/toggles and exact icon outlines remain review items.
- [ ] **Circle counter (2/5, PARTIAL)** - Numeric content adapted to counter with explicit warning. Circle geometry is not reproduced.
- [ ] **Map / pins (2/5, PARTIAL)** - One address maps to native Google Maps. Multiple pins preserve shortcode. API/provider configuration and real map interaction pending.
- [ ] **Social icons (3/5, PARTIAL)** - Native repeater links and common Font Awesome brand names. Unsupported Divi network/icon variants need review.
- [ ] **Pricing table / pricing group (2/5, UNVERIFIED)** - Group can be a native container; table mapper is implemented but Pro controls/layout have not been exercised. Runtime validation prevents unknown target controls from being written.
- [ ] **Contact form / field parsing (2/5, UNVERIFIED)** - Real fields/options parsed without invented choices. Pro form mapper exists; submit actions intentionally empty and mandatory review warning issued. Delivery, conditional rules, CAPTCHA and integrations not verified.
- [ ] **Email opt-in (0/5, UNAVAILABLE)** - Native provider credentials/actions are not migrated. Preserve original shortcode with warning; dedicated adapter explicitly refuses conversion.
- [ ] **Woo product title (1/5, UNVERIFIED)** - Requires both Pro and WooCommerce. Mapper exists but licensed Woo template context is unavailable for testing.
- [ ] **Woo cart / checkout clustering (0/5, UNAVAILABLE)** - Destructive original clustering removed. Complete source modules remain shortcodes; native compound migration pending.
- [ ] **Global modules (2/5, PARTIAL)** - Definitions actually present in the export expand as editable local copies with cycle protection and a synchronization warning. Missing definitions remain whole shortcodes. Shared template migration pending.
- [ ] **Presets (3/5, PARTIAL)** - Schema adapter for observed Divi4 module groups: presets/id/version/settings/default, local overrides and inactive device values. Actual Home default row preset exercised. Other versions/global inheritance remain unsupported.
- [ ] **Dynamic post / ACF / Woo bindings (1/5, PARTIAL)** - Divi page-link tokens resolve through explicit maps or opted-in public source REST API with identity/type/origin checks. General post/ACF/Woo live bindings remain unavailable. Unresolved nodes block applying conversion.
- [ ] **Typography (4/5, PARTIAL)** - Native families/weights, blurb body aliases, inherited body weight and text/button line heights are corrected. Real team heading, label, descriptions and button metrics pass matching-width checks. Missing font files, licensed providers and other global typography still require destination setup/review.
- [ ] **Background color / image / gradient / video (3/5, PARTIAL)** - Native backgrounds and observed true/CSS parallax pass runtime/browser QA. Multi-stop or unsupported mixed backgrounds fail closed; other source combinations need fixtures.
- [ ] **Borders / radius / shadows (4/5, PARTIAL)** - Image shadows/borders belong to the image surface; native radius including responsive zero and validated per-side desktop/device/hover declarations are preserved. Unsupported variants and untested source-specific presets remain guarded.
- [ ] **Visibility / sizing / z-index (3/5, PARTIAL)** - Disabled modules/device flags, widths, responsive height/max-width and z-index. Inactive source overrides removed. Supplemental CSS retains nonnative sizing. Browser geometry pending.
- [ ] **CSS filters (3/5, PARTIAL)** - Native image sliders use correct stored units. Scoped CSS supports desktop/tablet/mobile/hover blur, brightness, contrast, saturation, hue, grayscale, invert and opacity across widgets. Interaction/render appearance remains unverified.
- [ ] **Hover (3/5, PARTIAL)** - Inactive hover values removed; button backgrounds, icon colors, shadow color, scale and image filters retained in native controls/scoped CSS. Arbitrary source state combinations need visual tests.
- [ ] **Position / transforms (3/5, PARTIAL)** - Recognized offsets and basic pipe transforms activate their native conditional groups. The real skewed label passes active/inactive browser checks. Other anchoring/origin/animation combinations require fixtures and visual QA.
- [ ] **Sticky (0/5, UNAVAILABLE)** - Source module preserved with explicit warning; native sticky semantics need Pro and real device tests.
- [ ] **Custom CSS / fallback CSS (3/5, PARTIAL)** - Stable document-specific classes and nested HTML styles survive actual template insertion. Markup/nested rules rejected. Final selector/font appearance needs browser review.
- [ ] **Atomic output (0/5, UNAVAILABLE)** - Removed misleading selection. Adapter and batch runner reject Atomic before writes. Classic Flexbox is the supported output engine.
- [ ] **Shared models / utility facades (2/5, REVIEWED)** - Implemented data containers/strict JSON and thin facades. Several are extension helpers, not active engine paths; no end-to-end feature claim based only on class existence.
- [ ] **External Page Manager integration (1/5, UNVERIFIED)** - Independent menu avoids collisions, but real Page Manager hooks were not supplied. No existing site was modified.
- [ ] **Responsive visual fidelity / Elementor editor / real forms (3/5, PARTIAL)** - The team section is compared at five matching source widths and tested at ten destination widths active/inactive, including Divi breakpoint boundaries. Earlier bounded Home/header/accordion checks retain their scope. Full staging/editor/video/form acceptance and licensed runtime remain open.
- [ ] **Background parallax / horizontal scroll motion (3/5, PARTIAL)** - Observed Divi4 fields pass portable runtime tests; other motion types, source versions and host CSP constraints need review/adapters.
- [ ] **Source theme defaults (4/5, PARTIAL)** - Editable validated defaults include body weight and mixed-upload style provenance; origin changes refresh untouched recommendations, preserve explicit edits and report effective profiles. Additional source themes and styles missing from exports still require review.
- [ ] **Supplemental scoped CSS / shape dividers (3/5, PARTIAL)** - Plain source CSS, header typography, device content, hover/filter rules in a portable HTML widget; slant mapped to native tilt. Real render/control checks pass, visual geometry not accepted.

## Home-specific acceptance

- [ ] Finish manual staging comparison: exact geometry, gradient opacity, dividers, hover, editor persistence, video playback and accessibility. The browser process blocker is resolved; bounded automated layout/motion checks pass.
- [ ] Extend motion adapters only with fixtures for other enabled effects/source versions. Current true/CSS image parallax and horizontal curves are covered; disabled/hidden effects remain inactive.
- [ ] Review the source HTML link to https://sungaitoto.id. It is present in the supplied export and preserved; ownership/intent was not inferred.
- [ ] Review/rebuild global font settings and imported attachment alt differences. Source links remain on the source site when no equivalent local target page exists.
- [ ] Run remaining source exports; coverage of Home, Footer, Get More Customers, Header and this five-layout batch does not establish full 24-page coverage. Learning Center's standard grid now has a tested adapter; other extension modes require fixtures or configured replacements.
- [ ] Authorized Divi/Elementor Pro/Woo/Page Manager staging runtime for unsupported/licensed features.
- [ ] MySQL 8 server, multisite and PHP 7.4 execution; this increment verifies MariaDB 11.4.11 and SQLite with PHP 8.3.35 plus standalone/lint on PHP 8.0.30.

## Header acceptance

- [ ] Review the actual staging Main Menu links/hierarchy, custom breakpoints and manual Elementor editor appearance.
- [ ] Configure the converted global header in Elementor Theme Builder and review its display conditions. Applying to an ordinary page does not assign the global header.
- [ ] Add fixtures/adapters for active search/cart controls, mega menus and other Divi menu layouts before claiming support.

## Bar Counters increment - 1.4.0

The source-verified native Free bar subset is implemented and locally tested. It remains PARTIAL: source visual/default geometry and interactive editor/staging acceptance are open. [Mapping](module-upgrades/P05-MAPPING.md), [QA](MODULE-UPGRADE-P05-QA.md) and [machine-readable evidence](MODULE-UPGRADE-P05-EVIDENCE.json) record scope, assertions, exact package and outstanding gates. The module/variant missing backlog is 49; official fixtures for several subsequent families have been captured without adding unsupported mappings.
