# Client Portal reverse conversion diagnosis

Report: `migration-report-d820a7c6-8cc1-4484-9a62-9a3bd1a82207.json`. Source page 3094, target page 6902, site `https://key-screening.com/`. The user confirmed that the destination uses Divi 4.x.

## Confirmed outcome

The job completed its review, not page application: `dry_run=true`, one analyzed item, zero runtime failures, 184 warnings and 183 blocking mapping issues. Target writes were prevented. These are 183 setting/structure issues across 46 paths, not 183 failed modules. The source tree contains 53 elements: 22 containers, 21 Heading widgets, six Images, two Buttons, one Text Editor and one HTML widget. There is no unsupported third-party widget type in this tree.

Replaying the embedded `reverse_source` snapshot against the current 1.7.0 converter reproduces the exact blocker list and generated content. Content SHA256: `10fac65ffe5a78a6f1d3c309eb5c9974c5e11382222a7926cc82490fa0623896`. Report SHA256: `3496f6c52a9acb81938ce07cdd6a6f477b3326d4ee995067aa0da80f4a838e6e`. Evidence: workspace `.qa-evidence/reverse-d820-analysis.json` and `reverse-d820-replay.json`. Private source snapshots remain outside the installable distribution.

## Root causes

| Category | Blocking issues | Actual cause |
| --- | ---: | --- |
| Nested container surfaces/layout | 100 | `Reverse/Document::modules()` flattens nested groups without calling the surface/style translator or resolving their kit references. Padding, backgrounds, borders, clipping and spacing are therefore listed as unmapped even though some individual controls already have native translators. The nested card/grid DOM needs a dedicated layout plan. |
| Top-level container layout | 31 | Boxed widths, center/space-between alignment, background overlay controls and other layout values have no accepted reverse composition. |
| Partial-width row | 1 | The hero contains one 60% content group. `Document::row()` assumes that every group of container children must be complete native columns totaling 100%; that assumption does not fit a partial-width content group. |
| Heading HTML tags | 12 | Ten `p`, one `span` and one `div` are valid Elementor Heading settings. `Widgets::convert()` accepts only h1-h6. These should preserve their text semantics through a Text composition, not be changed into arbitrary headings. |
| Empty global-style references | 10 | `Styles::globals()` rejects `__globals__` entries whose value is an empty string. These denote an unset global reference, not a missing kit record. Keep the authored local color. The entire tree contains 22 empty references; 12 others are inside nested groups that are not yet resolved. |
| Widget geometry/effects | 26 | Advanced widget widths, flex sizing/alignment, image `object-fit`, button shadow and hover controls lack accepted mappings. Six card images use 140px height with `cover`; setting only the outer module height does not preserve that crop. |
| Scheme-less button URL | 2 | Both buttons contain `app.jobtread.com/sign-in`. Elementor's installed `Element_Base::add_link_attributes()` cleans links through WordPress `esc_url()`, while the reverse URL helper rejects this value before normalization. Preserve the source renderer's normalized URL and retain protocol/injection checks. |
| Page presentation | 1 | `hide_title=yes` is intentionally blocked until destination title suppression has a verified mapping. This is presentation; the WordPress post title and SEO data must remain unchanged. |

The extra nonblocking warning is the Vimeo iframe/script preserved as independent Divi Code. Its provider/player behavior still needs a browser check. The first source Text Editor contains only `<!-- wp:divi/placeholder /-->`; this comment does not prove an active Divi 5 runtime. The confirmed destination is Divi 4.

The main defect is insufficient reverse layout support for a valid Classic Elementor design. Setting `dry_run=false` or removing blockers would not supply the missing geometry or container surfaces. The current per-control diagnostics also inflate the apparent module failure count and recommend editing the source when the remedy belongs in the converter.

## Repair chunks and acceptance criteria

### R01 - Source normalization and valid text semantics

- [ ] Ignore empty global references while preserving local values. Resolve nonempty references against the captured kit at every node; missing or malformed real references still block.
- [ ] Support all permitted Heading tags: h1-h6 stay Heading modules; p/span/div preserve their tag, inline markup, typography and link semantics through a bounded native Text composition.
- [ ] Normalize source links using the same WordPress behavior as Elementor before applying the reverse allowlist. Reject executable/obfuscated schemes and malformed URLs. Record normalization; do not choose a different scheme silently.
- [ ] Treat `presetTitle`/`presetIcon` as editor metadata only after verifying they have no frontend effect in the installed Elementor control implementation.
- [ ] Tests: blank global with local color, valid kit references, real missing references, all nine permitted tags, inline colored spans, unsafe tags/shortcodes, the two real portal links and dangerous URL cases.

### R02 - Layout planning before serialization

- [ ] Replace unconditional container-to-column and nested-flatten assumptions with a recursive layout plan that retains each surface and determines a compatible native Divi composition.
- [ ] Keep outer backgrounds/padding/min-height separate from boxed inner content. Preserve the real 1280px cap and responsive width/alignment.
- [ ] Support the hero's single 60% group without inventing empty content columns or normalizing away its width; preserve tablet/mobile width changes.
- [ ] Map vertical gaps and flex alignment where proven native equivalents exist. Empty wrappers may flatten only when they cannot affect rendering or behavior.
- [ ] Detect grid mode before entering the flex/native-column algorithm. Preserve all six cards and source order, including the tablet two-column/three-row settings. Resolve omitted track defaults from the installed source version instead of guessing.
- [ ] Use native Divi sections/rows/columns/modules where the layout is representable. A scoped composition for a grid or compound surface must declare its editing/runtime limitations and receive native Divi acceptance. Unsupported structures retain a precise blocker.
- [ ] Tests: partial-width hero, boxed center-aligned content, multi-surface portal card, six-card grid, responsive order/gaps and unrepresentable layouts. Verify output in actual Divi 4 Visual Builder and frontend.

### R03 - Surfaces and responsive widget controls

- [ ] Apply style/global resolution to every retained container surface. Preserve background overlays independently of the base image/color, with the source opacity and stacking.
- [ ] Map widget custom-width modes and responsive values without replacing empty/inherited values with zero. Respect source kit breakpoint semantics.
- [ ] Preserve each 140px card image crop on the image surface, rounded clipping on its parent, and the separate text panel's padding/background.
- [ ] Map button shadows and `grow` hover behavior using verified native controls or clearly declared generated scoped rules. Retain authored four-side button padding.
- [ ] Implement destination title suppression only with a tested Divi 4/theme contract; retain the original WordPress title, slug, SEO and publication state.
- [ ] Tests: source values for desktop/tablet/mobile, image cropping, overlay opacity, button hit areas/focus/hover, zero and inherited resets, typography and external provider behavior.

### R04 - Diagnostics and end-to-end acceptance

- [ ] Distinguish malformed source data, missing dependencies, unsupported layout and unimplemented controls in report codes/actions. Valid authored styling should not receive a generic instruction to remove it.
- [ ] Report blocking issues and affected element paths separately from module counts; keep detailed per-setting coverage.
- [ ] Record source Elementor and destination Divi versions in new reports. Apply must revalidate the destination provider and saved source/output/target binding.
- [ ] Turn this exact 53-element snapshot into a private regression fixture. A lower blocker count alone is not acceptance: compare every retained surface and element in the native editor/frontend.
- [ ] Exercise review, saved/bulk Apply, stale source/target rejection, exact rollback and clearing on both databases after the new mappings are accepted.

## Primary implementation evidence

The checked-out official Elementor 4.3.3 Heading control permits `h1` through `h6`, `div`, `span` and `p` (`includes/widgets/heading.php`). Its link helper calls `esc_url()` (`includes/base/element-base.php`). Official documentation describes [recursive nested container data](https://developers.elementor.com/docs/data-structure/page-content.html) and [global styles](https://developers.elementor.com/docs/data-structure/global-styles/). WordPress documents [scheme-less URL normalization and protocol filtering](https://developer.wordpress.org/reference/functions/esc_url/). Independent embeds are documented by the [Divi Code module](https://help.elegantthemes.com/en/articles/8608209-the-divi-code-module).

This investigation reproduces and diagnoses the report. Plugin runtime code and release ZIPs have not been changed. Native Divi rendering, the user's live layout and a repaired end-to-end result have not yet been tested in this investigation.


## Implementation follow-up

The baseline findings above describe 1.7.0 and remain historical evidence. The complete repair implementation and acceptance status are in [REVERSE-1.8-IMPLEMENTATION.md](REVERSE-1.8-IMPLEMENTATION.md). R01-R05 are implemented in the 1.8.0-rc.1 staging candidate; R06 automated/browser projection checks pass, and licensed Divi acceptance remains pending.
