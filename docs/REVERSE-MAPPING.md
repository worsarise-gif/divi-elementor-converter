# Elementor to Divi 4 mapping - 1.8.0-rc.1

The reverse direction reads the current Classic Elementor page tree and source kit on the same site. It builds Divi 4 shortcode content with independent layout, widget and style translators. It shares owner-scoped migration jobs, saved/bulk Apply, target locks, backups, interrupted-write recovery, clearing and rollback with the forward direction.

Choose **Ace Migrator > Elementor to Divi**, select a source and destination, then **Convert for review**. Both pages remain unchanged during analysis. Open the report, resolve its blockers, and apply the saved result in Migration Jobs. Existing target details remain in place; new destinations stay drafts. Select the same source and destination only for an intentional in-place migration. The installed Divi 4 provider and every required output module are checked before application.

## Three reverse paths

* **Verified round trip:** a local Divi-to-Elementor migration made by 1.7.0 records a private source proof. If its current Elementor tree, page settings, global kit and original local Divi page/presets/globals still match, reverse conversion restores the exact original Divi content. This covers the complete applied source tree, including portable compositions and source module attributes. It does not infer a different DOM from compiled Elementor styles. A bounded per-page index retains ten proof candidates, avoiding report-body scans. Changed or unavailable dependencies invalidate the proof. JSON-upload migrations and older migrations without proof use the semantic path.
* **Semantic mapping:** Classic sections, columns and supported Flexbox compositions become native Divi sections, rows and columns. Widget adapters map content to native modules or a documented portable Code representation. Each element records its path, destination and mapped/unmapped settings. Enabled unsupported settings block Apply. Source snapshots remain available for review; unsupported content is never silently accepted as a complete migration.

* **Retained composition:** when native geometry cannot represent an accepted Classic tree, preserve its nested DOM and compiled source CSS in a scoped Divi Code module. This supports source flex/grid surfaces, responsive geometry, Icon Lists, indexed Nested Accordions and bound FAQ CSS. The independent FAQ/tab adapter does not need Elementor frontend JavaScript. The retained children are edited as HTML/CSS in Code, not individually in native Divi controls. Unknown active controls, dynamic bindings, unsupported widgets/skins and unavailable providers still block.

## Widget scope

| Elementor input | Reverse output |
| --- | --- |
| Heading, Text Editor, Button | Heading, Text, Button |
| Image, Image Box, Icon Box, Icon | Image, Blurb, Icon; installed Font Awesome glyphs |
| Image Gallery | Gallery with verified local image attachment IDs |
| Divider, Spacer | Divider with visible line or height |
| Video, Testimonial, Counter | Video, Testimonial, Number Counter within supported controls |
| Progress | Bar Counters / Counter with bounded percentage |
| Social Icons | Social Media Follow / Network |
| Classic Tabs, Accordion, Toggle | Tabs or Accordion composition; interaction defaults need review |
| HTML | Code for independent HTML; Elementor-dependent DOM/CSS/scripts block semantic Apply |
| Google Maps | Scoped Google embed iframe in Code, preserving address, zoom and responsive height |
| WordPress Nav Menu | Menu referencing an existing local WordPress menu |
| Price Table, Call To Action | Pricing Tables / Table and Call To Action within accepted controls |
| Ace Post Grid | Blog for supported local categories, recent post query and supported listing settings |
| Icon List, Nested Accordion | Retained Code DOM, indexed children and independent interaction |
| Formidable Shortcode | Existing local Formidable form in Code; provider/form/field/action freshness checked |
| Form, Posts skins, Loop Grid, Nested Tabs, product context and unknown third-party widgets | Explicit guarded analysis; no automatic application of unsupported behavior |

The admin catalog is generated from the live adapter registry. The original forward registry remains unchanged; provider-dependent and unsupported forward modules keep their existing guards. Verified round trips preserve all successfully applied local forward source modules; this does not make unsupported forward modules or arbitrary Elementor widgets supported.

Styles include accepted four-side spacing, responsive values, typography/font flags, global kit colors/type, solid/image backgrounds, borders, shadows, alignment, visibility and bounded z-index. Synthesized structural rows fill their source parent with zero added row padding; authored row dimensions and padding retain precedence. Boxed inner surfaces, asymmetric or unsupported responsive gaps, right-positioned blurb images, unequal button borders/corners, unavailable global references, arbitrary CSS, motion, layout modes and other unaccepted settings produce blockers. Divi and Elementor default geometry, interaction states and responsive breakpoints differ; native render/editor comparison is still required. Font files, sitewide CSS, template conditions and external providers are not imported by page conversion.

## Evidence and limits

Semantic fixtures exercise actual attribute names, responsive button spacing, fractions, global styles, injection boundaries, malformed/Atomic trees, unknown widgets/settings and provider-dependent content. WordPress tests exercise source snapshots, idempotent selection, saved/bulk Apply, destination freshness, drafts, in-place operation, exact rollback, missing providers, private ownership, clearing and verified round trips. The disposable environment has Elementor/UAE, SQLite and MariaDB. Its Divi runtime in writer tests is explicitly stubbed: those checks prove lifecycle and storage behavior, not native Divi rendering.

Licensed Divi 4 Visual Builder editing, native frontend output and source/destination visual parity must be accepted on staging before publication. Divi 5/Atomic output, reverse post/template targets, unaccepted nested compositions, Pro form delivery and loop/query-provider migrations are not accepted by this release.

The schema boundary follows [Elementor page content](https://developers.elementor.com/docs/data-structure/page-content.html). Independent HTML/embeds use the documented [Divi Code module](https://help.elegantthemes.com/en/articles/8608209-the-divi-code-module); the Google embed URL is verified against installed official Elementor `includes/widgets/google-maps.php`.

## Remaining upgrade chunks

1. Accept real licensed Divi 4 fixtures across native editor, frontend and mobile; record source version, font files, CSS defaults and screenshots for each adapter.
2. Add compound/nested layouts, accordion states beyond the accepted retained Classic tree only after paired builder fixtures and failure cases establish their contracts.
3. Add Pro Posts/Loop Grid queries and template expansion with explicit dependencies, categories, pagination and cycle limits.
4. Add forms only with verified field, delivery, spam protection and automation equivalence; test real delivery separately.
5. Add reverse Posts and Theme Builder targets with post metadata and Divi assignment contracts; then consider Divi 5/Atomic with separate schemas and acceptance.

## Candidate verification and dependencies

Version 1.8.0-rc.1 implements the complete two-report repair contracts. Both private fixtures have complete 53/53 and 130/130 coverage and zero mapping blockers when their source providers exist. The Client Portal Vimeo embed retains one review warning. Tests cover PHP 8.0/8.3, SQLite/MariaDB, existing forward workflows, full saved/bulk application and exact rollback. Browser projection checks cover seven widths around the 768/1025 source thresholds, real source fonts and FAQ interaction. Licensed Divi rendering and Visual Builder acceptance are still pending, so this is a staging candidate.

Review records include node dispositions and setting provenance. An inactive stored custom-width value is recorded as inactive, not activated to consume it. Native px/fraction constraints cannot silently discard authored widths. CSS compilation keeps the original source selector priority, kit defaults and conditional last-child widget spacing. All descendants are accounted for even under unsupported widgets.

Retained Code output includes source font links per page; it does not depend on a previous job's font queue. Existing local Google font stylesheets are reused; uncached local-font mode blocks until the original page has generated its files. Unregistered/custom font providers need a verified stylesheet contract and remain protected. Remote Google font links respect Elementor's loading settings. Referenced Elementor CSS/font files must remain installed and unchanged. Source font binaries and provider state are dependencies, not imported/reimplemented services. The Ace title-visibility marker requires Ace Migrator to remain active. Formidable embeds retain the original local form with its validation/actions and provider assets; the conversion does not certify delivery.

Reconvert after installing the update; Apply only writes the saved completed output and cannot amend an old report. Keep the assigned target and inspect its native Divi rendering/editor before production publication.
