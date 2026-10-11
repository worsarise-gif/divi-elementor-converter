# Module upgrade TODO — implementation chunks

Baseline: Ace 1.2.5. Date: October 8, 2026. **First implementation increment: 1.3.0 adds bounded CTA/Person adapters; F00/P01/R00 remain PARTIAL until source visual/editor/staging acceptance. 1.4.0 adds a bounded P05 Bar Counters adapter; its remaining visual/editor acceptance and Circle Counter work stay open. Other chunks remain TODO.**

Read [research and complete inventory](MODULE-UPGRADE-RESEARCH.md), [filterable inventory CSV](MODULE-UPGRADE-INVENTORY.csv) and [machine-readable specification](module-upgrades/inventory.json). The 1.2.5 inventory began with 52 missing Divi 4 module/variant entries. Four now have PARTIAL adapters; 48 remain missing, alongside one legacy Shop schema review, plus existing improvements and separate optional tracks. There are 50 bounded chunk files.

## How to execute a chunk

1. Open only the selected chunk and the common contract below; read its predecessor evidence. Choose at most three adapter changes.
2. Capture actual source JSON/version and rendered behavior. Record parent/child source keys, active variants and missing external data; no guessed module/control schemas.
3. Probe actual target widget/control/provider capabilities. Write a property mapping table: source path/type → target control/path/type → normalization → fallback → fixture assertion.
4. Implement the bounded supported subset; keep other modes protected and actionable. Preserve exact-target and saved-output behavior.
5. Complete semantic, schema, editor, browser and functional checks. Update evidence and limitations. Passing parsing is not visual or behavioral acceptance.
6. Run R00 for the increment. Continue to the next chunk only when this chunk's accepted scope is documented.

If a fixture/runtime is missing, mark the specific module BLOCKED_FOR_IMPLEMENTATION and continue independent research or another ready chunk. Do not fill unknowns with target defaults or mark a provider test passed without running it.

## Common mapping and fixture contract

- [ ] Capture source defaults separately from explicitly saved values, including zeros, empty sides, inactive hover/device values and inherited presets/theme values.
- [ ] Preserve semantic title levels, valid body HTML, item/slide order, URLs/targets, IDs/classes and intentional hidden content.
- [ ] Map desktop/tablet/mobile typography, four-side padding/margins, gap, widths/heights, per-side borders/radius/shadows to the correct surface.
- [ ] Verify actual source 980px/767px transitions versus native editable controls and supplemental CSS; test after editor value changes, not only immediately after import.
- [ ] Keep font family names and source weight/style; validate provider registration and required files rather than assuming a font catalogue supplies binaries.
- [ ] Preserve active hover/focus/open/closed/disabled/selected states. Report unavailable effects rather than activating residual source settings.
- [ ] Map foreign media/post/term/menu/sidebar/product identities explicitly; do not reuse numeric IDs across sites or import unrelated databases implicitly.
- [ ] Preserve query intent, offsets, ordering, filters, current context, stable pagination and multiple independent instances; test empty results and cache updates.
- [ ] Validate actual controls, child/nested structures, repeater IDs and target/provider versions; reject unavailable widgets before any write.
- [ ] Make forms/auth/comments/media/cart/checkout function with the real WordPress/provider mechanisms; include validation and failure paths, not just screenshots.
- [ ] Preserve current analysis/no-write semantics, assigned-page identity, single/bulk saved Apply, lock/conflict guards, backups and exact rollback.
- [ ] Disclose continued runtime/provider requirements, unavailable external data, approximation choices and non-equivalent properties in the report.

## Chunk index

| Done | Chunk | Scope | Kind | Predecessors |
|---|---|---|---|---|
| [ ] | [F00](module-upgrades/chunks/F00.md) | Evidence and source schema before adapters | FOUNDATION | None |
| [~] | [F01](module-upgrades/chunks/F01.md) | Dynamic and Theme Builder target context | FOUNDATION | F00 |
| [ ] | [P01](module-upgrades/chunks/P01.md) | Static business compositions | MODULE_IMPLEMENTATION | F00 |
| [ ] | [P02](module-upgrades/chunks/P02.md) | Fullwidth Header composition | MODULE_IMPLEMENTATION | F00, P01 |
| [ ] | [P03](module-upgrades/chunks/P03.md) | Fullwidth Code and Image variants | MODULE_IMPLEMENTATION | F00 |
| [ ] | [P04](module-upgrades/chunks/P04.md) | Fullwidth Map and Menu variants | MODULE_IMPLEMENTATION | F00, H04 |
| [~] | [P05](module-upgrades/chunks/P05.md) | Bar Counters and real circular progress | MODULE_IMPLEMENTATION | F00 |
| [ ] | [P06](module-upgrades/chunks/P06.md) | Search and Sidebar identity | MODULE_IMPLEMENTATION | F00 |
| [~] | [P07](module-upgrades/chunks/P07.md) | Core Blog and existing DiviGear query limits | MODULE_IMPLEMENTATION | F00, F01 |
| [ ] | [P08](module-upgrades/chunks/P08.md) | Projects and filterable portfolios | MODULE_IMPLEMENTATION | F00, F01, P07 |
| [ ] | [P09](module-upgrades/chunks/P09.md) | Static Slider and Fullwidth Slider | MODULE_IMPLEMENTATION | F00 |
| [ ] | [P10](module-upgrades/chunks/P10.md) | Dynamic Post Slider and fullwidth variant | MODULE_IMPLEMENTATION | P07, P09 |
| [ ] | [P11](module-upgrades/chunks/P11.md) | Video Slider and Fullwidth Portfolio | MODULE_IMPLEMENTATION | P08, P09 |
| [ ] | [P12](module-upgrades/chunks/P12.md) | Dynamic titles and post navigation | MODULE_IMPLEMENTATION | F00, F01 |
| [ ] | [P13](module-upgrades/chunks/P13.md) | Theme Builder body content and comments | MODULE_IMPLEMENTATION | F01, P12 |
| [ ] | [P14](module-upgrades/chunks/P14.md) | Audio and countdown behavior | MODULE_IMPLEMENTATION | F00 |
| [ ] | [P15](module-upgrades/chunks/P15.md) | Login behavior | MODULE_IMPLEMENTATION | F00 |
| [ ] | [P16](module-upgrades/chunks/P16.md) | Pro form setup and pricing acceptance | MODULE_IMPLEMENTATION | F00 |
| [ ] | [P17](module-upgrades/chunks/P17.md) | Email opt-in provider setup | MODULE_IMPLEMENTATION | P16 |
| [ ] | [H01](module-upgrades/chunks/H01.md) | Cross-site galleries and source mode fidelity | HARDENING | F00 |
| [ ] | [H02](module-upgrades/chunks/H02.md) | Nested Tabs, Toggle and Accordion fidelity | HARDENING | F00 |
| [ ] | [H03](module-upgrades/chunks/H03.md) | Shared styles and preservation of existing modules | HARDENING | F00 |
| [ ] | [H04](module-upgrades/chunks/H04.md) | Advanced Map and Menu behavior | HARDENING | F00 |
| [ ] | [W00](module-upgrades/chunks/W00.md) | WooCommerce context and safe compound design | FOUNDATION | F00, F01 |
| [ ] | [W01](module-upgrades/chunks/W01.md) | Product images and gallery distinction | WOO_IMPLEMENTATION | W00 |
| [ ] | [W02](module-upgrades/chunks/W02.md) | Price, rating and stock | WOO_IMPLEMENTATION | W00 |
| [ ] | [W03](module-upgrades/chunks/W03.md) | Description, information and metadata | WOO_IMPLEMENTATION | W00 |
| [ ] | [W04](module-upgrades/chunks/W04.md) | Product tabs, standalone reviews and existing title acceptance | WOO_IMPLEMENTATION | W00 |
| [ ] | [W05](module-upgrades/chunks/W05.md) | Add to cart, breadcrumbs and notices | WOO_IMPLEMENTATION | W00 |
| [ ] | [W06](module-upgrades/chunks/W06.md) | Current Products and legacy Shop queries | WOO_IMPLEMENTATION | W00 |
| [ ] | [W07](module-upgrades/chunks/W07.md) | Related products and upsells | WOO_IMPLEMENTATION | W00 |
| [ ] | [W08](module-upgrades/chunks/W08.md) | Cart products, totals and cross-sells | WOO_IMPLEMENTATION | W00 |
| [ ] | [W09](module-upgrades/chunks/W09.md) | Checkout billing, shipping and information | WOO_IMPLEMENTATION | W00 |
| [ ] | [W10](module-upgrades/chunks/W10.md) | Checkout details and payment | WOO_IMPLEMENTATION | W00, W09 |
| [ ] | [V00](module-upgrades/chunks/V00.md) | Third-party discovery and provider contracts | RESEARCH | F00 |
| [ ] | [V01](module-upgrades/chunks/V01.md) | Vendor candidate research batch 1 | RESEARCH | V00 |
| [ ] | [V02](module-upgrades/chunks/V02.md) | Vendor candidate research batch 2 | RESEARCH | V00 |
| [ ] | [V03](module-upgrades/chunks/V03.md) | Vendor candidate research batch 3 | RESEARCH | V00 |
| [ ] | [V04](module-upgrades/chunks/V04.md) | Vendor candidate research batch 4 | RESEARCH | V00 |
| [ ] | [V05](module-upgrades/chunks/V05.md) | Vendor candidate research batch 5 | RESEARCH | V00 |
| [ ] | [D5-00](module-upgrades/chunks/D5-00.md) | Separate Divi 5 input/output research | FUTURE_FORMAT | R00 |
| [ ] | [D5-01](module-upgrades/chunks/D5-01.md) | Divi 5 candidate research batch 1 | FUTURE_RESEARCH | D5-00 |
| [ ] | [D5-02](module-upgrades/chunks/D5-02.md) | Divi 5 candidate research batch 2 | FUTURE_RESEARCH | D5-00 |
| [ ] | [D5-03](module-upgrades/chunks/D5-03.md) | Divi 5 candidate research batch 3 | FUTURE_RESEARCH | D5-00 |
| [ ] | [D5-04](module-upgrades/chunks/D5-04.md) | Divi 5 candidate research batch 4 | FUTURE_RESEARCH | D5-00 |
| [ ] | [D5-05](module-upgrades/chunks/D5-05.md) | Divi 5 candidate research batch 5 | FUTURE_RESEARCH | D5-00 |
| [ ] | [D5-06](module-upgrades/chunks/D5-06.md) | Divi 5 candidate research batch 6 | FUTURE_RESEARCH | D5-00 |
| [ ] | [D5-07](module-upgrades/chunks/D5-07.md) | Divi 5 candidate research batch 7 | FUTURE_RESEARCH | D5-00 |
| [ ] | [D5-08](module-upgrades/chunks/D5-08.md) | Divi 5 candidate research batch 8 | FUTURE_RESEARCH | D5-00 |
| [ ] | [R00](module-upgrades/chunks/R00.md) | Acceptance and packaging for each release increment | RELEASE_GATE | F00 |

## First increment handoff - 1.3.0

- [x] Capture actual exported CTA/Person identities and attributes from official Divi 4.4.6/4.9.0 fixtures; retain extra legacy CTA regression and private hashes.
- [x] Probe native Elementor 4.3.3 controls and implement two editable Free compositions with per-surface styles, source context and protected negative modes.
- [x] Verify standalone semantics, schema/native template insertion, responsive destination frontend, saved bulk exact-target Apply and exact rollback; run existing regression on both local databases.
- [x] Update the mapping/status documentation and prepare the versioned distribution. Final package/Plugin Check evidence is in [P01 QA](MODULE-UPGRADE-P01-QA.md).
- [ ] Obtain the publisher's customized source exports and original device screenshots; run Divi-versus-Elementor visual and actual editor/staging acceptance.

Both families remain **PARTIAL**, not fully accepted. Read [P01 mapping](module-upgrades/P01-MAPPING.md) and [QA limits](MODULE-UPGRADE-P01-QA.md) before selecting P02/P03. The F00 work for other module families and the full R00 matrix remain open.

## Recommended first increment

- [ ] F00: capability truth and real source fixtures for Call To Action and Person.
- [ ] P01: both native compositions, with per-child source styles and protected negative cases.
- [ ] R00: editor/import, responsive frontend, correct Apply/rollback and existing original-export regression.

Then select P02/P03 or the observed next-largest gap. P07 is valuable but cannot simply reuse the DiviGear schema. Woo work starts only after W00; vendor/new-format candidates remain research until their entry gates are satisfied. Versions/timelines are intentionally not promised before fixtures and capability checks.

## Per-chunk handoff record

Use this small record for every completed unit:

```text
Chunk / accepted modules:
Source versions + fixture paths + hashes:
Target WordPress/PHP/Elementor/Pro/Woo/provider versions:
Actual source tags/child schema + target controls:
Property mapping spec + explicitly unsupported modes:
Changed files + unchanged baseline evidence:
Semantic / schema / editor / browser / functional / Apply-rollback results:
Runtime dependencies + data setup requirements:
Remaining failures or unavailable environments:
Release package hash + staging acceptance:
Next eligible chunk:
```

Keep earlier docs/TODO.md evidence intact. Checked research tasks or an existing class do not imply checked conversion implementation.

## P05 handoff - 1.4.0

- [x] Capture official Divi 4.8.2/4.8.1 parent/child preset fixture and a clearly labeled 3.29.1 responsive legacy regression fixture.
- [x] Probe native Progress Bar controls and render behavior before writing the mapping.
- [x] Implement the bounded native Free bar collection/item subset with protected compound failures.
- [x] Verify native template insertion, responsive frontend, saved bulk Apply and exact rollback locally. Evidence is in [P05 QA](MODULE-UPGRADE-P05-QA.md).
- [ ] Obtain default/customized Divi 4 exports and original rendered device baselines for exact geometry/animation/interactive editor acceptance.
- [ ] Implement the separate Circle Counter upgrade; Pro/WooCommerce and remaining missing families retain their own fixture/runtime gates.

## P07 / ordinary-post handoff - 1.5.0

- [x] Register a separate fixture-backed core `et_pb_blog` adapter; preserve the DiviGear schema.
- [x] Add explicit ordinary post targets, metadata-safe Apply/rollback, source category mappings and safe converted-post excerpts.
- [x] Test list/masonry, source typography, offset/pagination and protected invalid/deleted categories.
- [ ] Accept full-content/current-archive/non-post query modes only with new fixtures and target contexts.
- [ ] Complete live source visual comparison and interactive editor/provider acceptance; current integration/browser checks are bounded local evidence.
