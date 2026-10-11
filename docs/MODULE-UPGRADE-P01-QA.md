# P01 QA handoff - Ace 1.3.0

Date: October 8, 2026. Baseline: 1.2.5. Increment: bounded Call To Action and Person adapters, source column context, Person media discovery and native Elementor ID validation. Earlier release evidence is retained. This is not full backlog completion or publisher staging acceptance.

| Area | Result / limits |
| --- | --- |
| Source identity | 13 actual Divi 4 nodes: 2 CTA + 5 Person from 4.4.6, 6 Person from 4.9.0; plus 3 legacy CTA nodes from 3.24.1. Attribute/version/hash manifest retained privately. |
| Mapping status | Both new families PARTIAL: native editable compositions work for the tested subset; protected unsupported modes and remaining visual/editor acceptance are explicit. |
| Standalone | 552 counted assertions plus pipeline smoke check on each PHP 8.3.35 and 8.0.30. Includes 88 new semantic/schema/negative fixture assertions. |
| Existing WordPress regression | 19 existing suites, 4100 assertions on each isolated SQLite and MariaDB backend. Original exports, source links, headers/footer/UAE, fonts, grids, menus, apply locks and failures remain covered. |
| New WordPress integration | 42 assertions per backend after final composition fixes: real native controls, 16 official source nodes, ID regeneration/short IDs, render, two-target saved bulk Apply without reconversion, protected fallback and exact rollback. |
| Destination browser | 185 assertions across 320/390/767/768/980/981/1024/1440px; readable CTA, source four-side padding/font sizes, valid non-underlined links, role gap, wide/quarter portrait directions, alignment, bounds and gaps, loaded portraits, no horizontal overflow or JavaScript errors. Desktop/mobile screenshots inspected. |
| Local environment | WordPress 7.1.2, Elementor 4.3.3, UAE 2.9.5, PHP 8.3.35, MariaDB 11.4.11 plus SQLite. No licensed Pro/Woo tests claimed. |
| Original frontend / editor / staging | Not run: no installed Divi runtime or publisher CTA/Person exports. Native template insertion/schema is verified; interactive Elementor editor parity remains open. Actual staging was not modified. |

The broad regression ran after the shared builder/validator changes. The final child-surface spacing/hover changes were followed by another standalone run, focused WordPress suite on both databases and the responsive browser run. Tests mutate only disposable local QA sites. Downloaded source samples and private exports are excluded from the ZIP.

## Root causes addressed

- Missing real source identities: CTA uses `et_pb_cta`, not the `.et_pb_promo` CSS class; Person uses `et_pb_team_member`, not a generic blurb/testimonial.
- Module-only styling cannot reproduce editable child surfaces. Title/body/button and portrait/name/role/bio/social styles are now routed independently.
- Source default padding and Person arrangement depend on source column/device context. Builder propagation and per-side inheritance preserve that context instead of substituting destination defaults.
- A destination widget gap reset could override the portrait's own source margin. The reset precedes source spacing now.
- Native Elementor template insertion can produce short hexadecimal IDs. The validator no longer rejects those valid native IDs; malformed/duplicate IDs still fail.

See [the property specification](module-upgrades/P01-MAPPING.md), [P01 chunk](module-upgrades/chunks/P01.md) and [current TODO](MODULE-UPGRADE-TODO.md). Package hashes, exact installed-byte comparison and WordPress Plugin Check results are recorded in the private final release evidence and appended below after packaging.

## Remaining pilot checklist

- [ ] Obtain the publisher's Divi 4 CTA and Person exports, with original screenshots at the source breakpoints and inherited theme/font defaults.
- [ ] Compare the original and converted pages on staging, including portrait crop/overlap, long mobile copy, source default arrows and custom declarations.
- [ ] Exercise the actual Elementor editor, save/reload after native control changes and review the retained conversion stylesheet.
- [ ] Complete applicable keyboard/touch/RTL/reduced-motion/no-JS/slow-asset and custom destination-theme cases.
- [ ] Accept the pilot before expanding P01 claims or starting the next rollout chunk. P02 Fullwidth Header is the next suggested module chunk; its own source fixture gate remains required.


## Packaged distribution verification

Ace 1.3.0: 179 public files; 205 PHP syntax checks passed on each PHP 8.3.35 and 8.0.30. Both isolated WordPress installations were updated from the actual ZIP and every installed archive byte was compared. The packaged plugin passed the 42 focused assertions on each backend again. Installed WordPress Plugin Check, including runtime checks, reports no findings. Tests, downloaded source fixtures, private exports and internal QA files are excluded.

Public download: `ace-divi-to-elementor-migrator-1.3.0.zip`. Internal installed basename stays `suiteedge-divi-elementor-migrator` to preserve upgrades.

SHA256: `870067110bf14f39d2ee0518f3239803c6489b38ceffe19c1c2f245688bef4f2`.

The pre-change PHP/JS/CSS baseline confirms 200 existing files unchanged. Changed existing runtime files: `suiteedge-divi-elementor-migrator.php`, `src/Divi/Scanner.php`, `src/Elementor/DocumentBuilder.php`, `src/Elementor/Validator.php`, `src/Mapping/ModuleRegistry.php`. Added source runtime/test files: `tests/business-compositions-wordpress.php`, `tests/business-compositions.php`, `src/Mapping/Core/BusinessComposition.php`, `src/Mapping/Core/CallToActionConverter.php`, `src/Mapping/Core/PersonConverter.php`. The release builder was updated to discover the renamed Ace source folder while retaining the original installed basename.
