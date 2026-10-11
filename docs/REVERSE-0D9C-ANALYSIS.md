# Custom Aluminum Welding reverse conversion diagnosis

Report: `migration-report-0d9c58e8-d1a7-4649-9fab-fc4a0ab6af72.json`. Source page **3223**, destination page **6959**, site `https://key-screening.com/`. The destination is Divi 4.x, as confirmed by the user in the preceding investigation.

## What happened

This was **Convert for review**, not an application attempt: `dry_run=true`, page status `analyzed`, one completed analysis, **zero runtime failures**, **396 warnings**, and **390 mapping blockers**. The report records zero converted targets. The current Apply eligibility and output validation correctly refuse this incomplete result. Changing review mode, increasing timeouts, or bypassing the blockers would not supply the missing layout or behavior.

The 390 issues affect **98 paths**, including structural/page paths; they are not 390 failed modules. The source has **130 elements**: 43 containers, 37 Headings, 18 Text Editors, six Buttons, six Images, seven HTML widgets, five Icon Boxes, three Icons, three Icon Lists, one Shortcode and one Nested Accordion.

All blockers, warnings, coverage records and generated content reproduce exactly with the current 1.7.0 semantic mapper on PHP 8.0.30 and PHP 8.3.35. Report SHA256: `dc572ac0ecc065ac39ab3168eb9acaee1eba3031624c70901e7b685220145e48`. Generated content SHA256: `737e725fde919e31e90b84ce4fa2f4525b1e490c9d8d5821a9ece67306909c5e`.

Private evidence: workspace `.qa-evidence/reverse-0d9c-analysis.json`, `reverse-0d9c-replay.json`, `analyze-reverse-0d9c.py`, and `replay-reverse-0d9c.php`. The analysis JSON contains the classification and path of **every** blocker. User content and source snapshots remain outside the distribution.

## Complete classification

Each blocker belongs to exactly one row below. Counts sum to 390. Categorization identifies the responsible code path; it does not mean every issue in that category can safely be ignored.

| Root-cause category | Issues | Evidence and repair responsibility |
| --- | ---: | --- |
| Nested container layout/surfaces | 95 | `Document::modules()` flattens 14 groups without resolving globals or translating their surfaces. Authored padding, margin, backgrounds, borders and layout controls become unmapped. Retain and translate these groups through a recursive layout plan. |
| Section/row/column layout | 87 | Boxed width, flex alignment, gaps, grid tracks, background positioning/overlays and responsive layout values have no accepted composition. This includes two actual CSS Grid containers. |
| Widget geometry/surfaces/effects | 106 | Advanced width modes, responsive widths, `_border_*` controls, image `object-fit`, icon sizing/indent, title/description colors, alignment and button hover effects lack accepted mappings. These are properties on otherwise recognized widgets. |
| Valid non-heading HTML tags rejected | 18 | Fifteen `span` and three `p` Headings are valid Elementor configurations. The reverse adapter accepts only h1-h6. Preserve their tags, markup and typography with an appropriate Text composition. |
| Empty global references rejected | 28 | All 28 reported unresolved references are empty strings, not missing kit records. Preserve their local values. Four more empty references occur in flattened groups; the complete tree contains 32. Six nonempty references point to available kit colors. |
| Empty Icon Box selections rejected | 5 | Every affected Icon Box has `selected_icon={value:"",library:""}`. The source displays title/description without an icon. The reverse adapter calls its Font Awesome/SVG validator unconditionally and labels these as custom-icon failures. |
| Icon List adapter missing | 29 | Three lists contain ten total check-circle items. The missing adapter produces three unsupported-widget blockers plus 26 unmapped-control blockers. Preserve list semantics, typography, icons, spacing and responsive alignment. |
| Nested Accordion adapter missing | 10 | Widget `93228e9` has five titles in `settings.items` and five answer containers in `elements`. The generic widget adapter does not compose these children. |
| Accordion-dependent CSS | 1 | HTML widget `df91b7c` styles `elementor-element-93228e9`, `.e-n-accordion-*`, `details[open]`, icons, focus states and mobile sizing. It is a genuine dependency on the FAQ's DOM, not an independent embed. |
| Formidable provider contract missing | 1 | Shortcode widget `aab5188` contains `[formidable id=1 title=false]`. The mapper blocks every third-party shortcode rather than checking whether its provider/form can be preserved. |
| Scheme-less URL normalization mismatch | 2 | Buttons `aa32fcb` and `3470d41` use `app.jobtread.com/sign-in`. Elementor normalizes links through WordPress; the reverse helper rejects this source value before normalization. The other four button links use accepted `tel:` or relative URLs. |
| Editor preset metadata reported as style | 4 | Two Grid containers store `presetTitle` and `presetIcon`. Installed official Elementor code declares these in panel preset configuration. They need verified editor-metadata handling, while actual Grid controls remain rendering dependencies. |
| Native column constraints | 3 | A 40%/50% row fails the sum-to-100 assumption. A separate 35%/65% row sums correctly but neither width is in the native fraction table. Do not replace these authored widths with arbitrary fractions. |
| Page title presentation | 1 | `hide_title=yes` has no accepted destination presentation contract. Preserve the WordPress title/slug/SEO and map its frontend visibility separately. |

The remaining **six nonblocking warnings** belong to independent HTML card widgets `897e737`, `612bac6`, `dbbd127`, `043cda7`, `999bcba` and `3eefe2e`, already retained as Divi Code. Their preservation is not proof of native rendering or browser behavior.

## Additional defects the issue count does not expose

1. **Pixel column widths are silently consumed.** In `Document::row()`, only percent widths influence native fractions. Pixel widths default to equal fractions; the translated `width` is then removed from the column and marked used. This affects three 400px columns in `8f55847`, and the 725px/480px pair in `58d06b0`. The latter becomes equal halves. A repair must account for parent width, shrink, gaps and responsive stacking; calculating percentages from the two numbers alone is insufficient.
2. **Nested child coverage is incomplete.** Coverage contains 121 records including page settings, so only 120 of the 130 actual nodes have records. Five FAQ answer containers and five Text Editors are never visited by the semantic mapper. Their data remains in the private snapshot, and the parent blocker prevents application, but the report does not describe their individual mapping status. Audit every descendant even when its parent is unsupported.
3. **Raw custom-width values may be inactive.** Official Elementor's `_element_custom_width` control is conditional on `_element_width=initial`; `inherit` means full width, and `auto` means inline sizing. This page stores some custom-width values without a custom width mode. Resolve control conditions, inherited device values and source-version defaults before translating or blocking stored controls. Never enable a previously inactive width just to consume its value.
4. **Target attributes need module-specific validation.** The common color translator consumes `title_color` as `title_text_color`, while the Blurb contract elsewhere in the project uses `header_text_color`. Description color is currently unmapped. After handling empty icons, verify both colors and typography on the actual Blurb/Text surfaces; consuming a setting does not prove it affects the intended Divi element.
5. **CSS cascade order matters.** The six independent cards share `.welded-card` rules. The last block includes a global `31px!important` heading rule. This is authored source code, so deduplicating, scoping each card independently or reordering blocks can change the source appearance. Preserve ordered code first, or perform a separately validated CSS transformation.
6. **Fonts and runtime defaults are external dependencies.** The authored font is Sora; storing its family name does not prove all required weights/files load on Divi. The snapshot includes kit thresholds `viewport_md=768` and `viewport_lg=1025`, but no source Elementor version or destination Divi version. It also does not fingerprint external form configuration or font provider state. Capture and validate the dependencies actually used by a planned adapter.

## Robust repair plan

This extends the shared R01-R04 plan in [the Client Portal diagnosis](REVERSE-D820-ANALYSIS.md). Both reports must remain regression fixtures; they expose the same structural failures on different trees.

### R01 - Normalize source values before mapping

- [ ] Treat an empty global reference as unset and preserve the authored local value. Real missing/malformed nonempty references still block. Resolve globals at every node, including retained nested surfaces.
- [ ] Accept all valid Heading tags without changing their text semantics. Map span/p/div through a bounded Text composition with equivalent typography and link handling.
- [ ] Preserve icon-free Icon Boxes as icon-free content. Distinguish empty selection, valid Font Awesome, a custom/SVG asset and a missing glyph in diagnostics.
- [ ] Normalize links with source WordPress semantics before protocol validation; do not silently force a different scheme. Retain dangerous-protocol, malformed-object and shortcode-injection guards.
- [ ] Read active control conditions and device inheritance. Handle verified editor preset metadata without treating Grid layout controls as metadata.
- [ ] Capture source/destination builder versions for default resolution and support diagnostics. Use an explicit schema/version contract rather than assuming the disposable QA installation matches the user's source.

### R02 - Plan layout recursively, then serialize

- [ ] Introduce a retained layout tree with source IDs, child relationships, outer/inner surfaces, effective device values, dependencies and a declared output composition.
- [ ] Decide Grid versus Flexbox before interpreting child containers as native columns. Preserve the four-card grid's four desktop/two tablet tracks and the six-card grid's two-column/three-row tablet layout with 10px gaps. Resolve the latter's omitted desktop tracks from its source contract.
- [ ] Preserve pixel widths, partial rows, 35%/65% widths and parent alignment; do not remove values after marking them mapped. Represent compatible compositions with native Divi structures and proven scoped styles. An unrepresentable structure retains a precise blocker.
- [ ] Translate backgrounds, overlays, padding, margins, borders, clipping and alignment on every retained surface. Empty wrappers may flatten only after verifying that they cannot change layout, identity, styling or behavior.
- [ ] Apply image heights and `cover` to the image surface: these two images use 595px desktop, 384px tablet and, for one, 215px mobile. Preserve responsive widths and corner clipping separately.
- [ ] Resolve source breakpoints and defaults before generating CSS. Do not interpret stored minimum-width thresholds directly as maximum-width media queries.

### R03 - Add explicit compound-widget adapters

- [ ] Add Icon List support with all ten item texts/icons and original order. Preserve optional per-item links, actual link hit areas, typography, indent, inter-item gap, vertical alignment, mobile offset and hover behavior. Choose a semantic composition after testing Divi geometry, not a heading/icon guess.
- [ ] Add a Nested Accordion adapter that binds `items[index]` to `elements[index]`, validates cardinality and recursively audits each answer. Repeater `_id` values are not answer-container IDs. This sample has five one-Text-Editor answers, so a bounded native Accordion composition is a reasonable first target.
- [ ] Preserve first-open/all-closed and single/multiple-expanded semantics using resolved source defaults. Check keyboard/focus behavior, plus/minus icons, icon circles, answer typography, borders and device spacing. Unsupported rich child structures need an explicit composition, not dropped children.
- [ ] Discover companion CSS dependencies before widget conversion. Translate only understood selectors/declarations against a verified source-to-output DOM map; preserve specificity, media queries and focus/open states. The FAQ's `[open]` selectors cannot be attached to Divi toggles by changing only a classname. Unknown dependencies remain blocked with the affected widget ID and exact reason.
- [ ] Correct module-specific title/body color and typography destinations. Preserve hover title colors and advanced border surface aliases where accepted equivalents exist.

### R04 - Preserve external functionality through provider contracts

- [ ] Preserve the exact Formidable shortcode in a suitable Divi Code module when `formidable` is registered and local form 1 exists. A native form rebuild is unnecessary for this same-site preservation route.
- [ ] Add a dependency record for provider identity/version, referenced form and assets. Revalidate it immediately before Apply; missing/changed dependencies should block with a specific recovery action. Do not execute form submissions during analysis or replace a live form with rendered static HTML.
- [ ] Test form rendering, validation, spam protection and a controlled submission/delivery on staging with the actual provider. Storage tests alone do not prove delivery.
- [ ] Preserve independent HTML blocks in source order. Verify card hover/mobile behavior and font loading without assuming all custom HTML is Elementor-dependent or all external code is portable.

### R05 - Make diagnostics complete and actionable

- [ ] Count **blocking issues**, **affected elements**, **unvisited/unsupported descendants** and **runtime failures** separately. `JobOutcome` currently names the issue total `blocking_modules`.
- [ ] Give distinct cause codes for an unimplemented converter control, unavailable provider, source-dependent CSS, unsafe value, malformed data and unsupported composition. Do not direct users to remove valid source styling when the converter needs improvement.
- [ ] Record each node as mapped, composed, retained-but-blocked or failed. Require coverage reconciliation against the validated source tree, including children of unsupported widgets.
- [ ] Track each consumed setting to its emitted attribute/style/behavior and destination surface. Avoid reporting a value as mapped after discarding it.
- [ ] Keep current snapshot/output/target binding, ownership, locking, backups and rollback protections. Expand freshness checks only for dependencies that the new plan actually uses.

### R06 - Acceptance before a new release

- [ ] Run both complete private report fixtures and focused malformed/missing-provider/inactive-control cases. Require all 130 new-source nodes to be accounted for; passing with fewer blockers alone is insufficient.
- [ ] Compare actual Elementor and licensed Divi 4 frontend and Visual Builder output at desktop/tablet/mobile, including around the 768/1025 thresholds. Check pixel widths, wrapping, gaps, overlays, image crop, Sora weights, card hover and all five FAQ answers.
- [ ] Verify native editing and save/reload preserve converted content. Declare any Code composition's editing limitations in the report.
- [ ] Verify review leaves both pages untouched, saved/bulk Apply binds the correct page, stale source/target/provider state rejects, failed/interrupted writes recover, and rollback restores exact original metadata/content. Recheck SQLite/MariaDB and existing forward conversions after implementation.
- [ ] Release a new version only after its mappings and native runtime checks pass. Do not replace the existing 1.7.0 artifact with an unaccepted converter or bypass its guards.

## Evidence and limits

The review traced source capture, Document/Styles/Widgets, Diagnostics, saved Apply eligibility, provider checks and target writing. Runtime code and existing release archives were **not changed** in this investigation. All 200 packaged 1.7.0 files still match the source; ZIP SHA256 remains `2bbbd18fefdb19da08fbd2421e2e0efb5e4b8ed87f0f2b3b18d4e6c870fe2247`. No live page was edited, and no native Divi visual acceptance was performed.

The source report embeds enough data to reproduce this conversion; another JSON export is not needed for diagnosis. Actual Formidable availability/form configuration, source builder version, Sora font loading and native Divi rendering remain runtime checks.

Primary references:

- [Elementor nested Accordion structure and controls](https://elementor.com/help/accordion-widget-with-nested-elements/): nested content, interaction and styling controls. Installed official `modules/nested-accordion/widgets/nested-accordion.php` confirms indexed child rendering and `details`/`summary` markup.
- [Elementor Icon List controls](https://elementor.com/help/icon-list-widget/): item icons/text/links, responsive styling and alignment. Installed official `includes/widgets/common-base.php` confirms width-mode conditions; `includes/widgets/icon-box.php` confirms icon-free content rendering.
- [Divi 4 Accordion](https://help.elegantthemes.com/en/articles/8611169-the-divi-accordion-module): native content/interaction contract; first item open and single-expanded behavior.
- [Divi Code module](https://help.elegantthemes.com/en/articles/8608209-the-divi-code-module) and [Formidable form embedding](https://formidableforms.com/knowledgebase/publish-a-form/): preserved provider shortcodes are a documented route, subject to the provider and referenced form being available.
- [WordPress URL normalization](https://developer.wordpress.org/reference/functions/esc_url/): source link cleanup and protocol filtering. Installed official Elementor link helpers use this source renderer behavior.


## Implementation follow-up

The baseline findings above describe 1.7.0 and remain historical evidence. The complete repair implementation and acceptance status are in [REVERSE-1.8-IMPLEMENTATION.md](REVERSE-1.8-IMPLEMENTATION.md). R01-R05 are implemented in the 1.8.0-rc.1 staging candidate; R06 automated/browser projection checks pass, and licensed Divi acceptance remains pending.
