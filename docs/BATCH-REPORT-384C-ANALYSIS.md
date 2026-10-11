# Five-page batch analysis - October 8, 2026

Report: `384c83d8-efde-4e81-a6e9-0288d93e9400`. Audited runtime source: Ace 1.2.3. The report records one applied page, four failed items, nine actionable warnings and twenty informational entries. The four errors are deliberate application guards for unresolved shortcodes, not PHP fatal errors.

Publication should remain on hold: this review also reproduced a direct-Apply concurrent-edit defect and a parser resource-limit defect. Neither caused this batch's four blocked pages. Runtime code and the installable ZIP were not changed during this analysis.

## What happened to each page

| Export | Assigned page | Result in report | Exact blocker | Reproduction with verified page-ID mappings |
| --- | ---: | --- | --- | --- |
| Get-Started | 236 | Applied | None reported | No fallback |
| Careers | 235 | Blocked | Five dynamic button URLs: source IDs 5136, 4403, 53868, 53884, 53882 | All five buttons become native; destination links still require review |
| Why-Suite-Edge | 234 | Blocked | Two dynamic button URLs: source IDs 4398 and 52164 | Both buttons become native |
| Learning-Center | 233 | Blocked | Third-party `dgbm_blog_module` | Grid remains unsupported and protected |
| Reviews | 232 | Blocked | Dynamic button URL: source ID 52164 | Button becomes native |

There are eight unresolved button instances referring to seven unique source page IDs, plus one unsupported grid. One incomplete module prevents the entire corresponding page from being written. Processing five items does not mean five pages were applied.

The twenty information entries describe portable styles, restored image tooltips, preserved link decoration and inactive effects. They are not additional conversion failures. This report contains no unmapped-style or font-registration warnings; that does not establish visual fidelity or form delivery for every page.

## Root cause of the three link-blocked pages

`conversion_settings.source_origin` is empty. `RecommendedSettings::for_pending()` requires every uploaded export to establish the same source origin from trustworthy WordPress-media URLs. Four of the local exports establish `https://suiteedge.com`; Reviews has no such media provenance. Consequently, the batch leaves link provenance blank and recommends disabling public source link resolution, while retaining the independently established style profile.

This is a conservative safeguard with an inadequate preflight workflow. A missing source origin should be explained and resolved before Apply. The converter should not silently assume that an image-free file belongs to another file's site. The correct improvement is an explicit same-source confirmation or per-layout source mapping, followed by dependency validation.

`DocumentBuilder` can resolve `post_link_url_page` through an explicit source-ID map or the opted-in source WordPress REST API. With no usable origin or map, it preserves the original button shortcode. `BatchRunner` correctly rejects applying that incomplete result before media import or page writes.

The report does not record `resolve_source_links`, explicit mapping counts, resolver outcomes or runtime versions, so its checkbox state and installed versions cannot be reconstructed from the report alone. A blank origin prevents remote resolution even if the checkbox was selected.

The prior four-of-five integration result used explicit verified source-ID mappings. This new default-settings path is a separate case: the unresolved dependencies were not handled before the user chose Apply. It must be added to end-to-end regression coverage.

## Verified source links and immediate configuration

All seven public source REST endpoints returned the matching published page ID, type and source-origin permalink during this review. These are source IDs, not destination IDs.

| Source ID | Verified source path | Endpoint |
| ---: | --- | --- |
| 5136 | `/careers-brand-manager/` | [Source page](https://suiteedge.com/wp-json/wp/v2/pages/5136?_fields=id,link,status,type) |
| 4403 | `/careers-social-media-specialist/` | [Source page](https://suiteedge.com/wp-json/wp/v2/pages/4403?_fields=id,link,status,type) |
| 53868 | `/careers-seo-specialist/` | [Source page](https://suiteedge.com/wp-json/wp/v2/pages/53868?_fields=id,link,status,type) |
| 53884 | `/careers-digital-ads-specialist/` | [Source page](https://suiteedge.com/wp-json/wp/v2/pages/53884?_fields=id,link,status,type) |
| 53882 | `/careers-web-developer/` | [Source page](https://suiteedge.com/wp-json/wp/v2/pages/53882?_fields=id,link,status,type) |
| 4398 | `/careers/` | [Source page](https://suiteedge.com/wp-json/wp/v2/pages/4398?_fields=id,link,status,type) |
| 52164 | `/get-started/` | [Source page](https://suiteedge.com/wp-json/wp/v2/pages/52164?_fields=id,link,status,type) |

For this confirmed source, enter `https://suiteedge.com` under Source site origin and enable public REST dynamic-link resolution. Run a fresh conversion for review. Alternatively, use the verified explicit map in `.qa-evidence/report-384c-link-map.json`. It retains real source URLs; it does not invent staging destinations. During Apply, URL rewriting uses a local equivalent only when a real destination resolves. Missing career pages remain source links with actionable warnings until migrated or explicitly mapped.

Failed saved outputs still contain their original fallback shortcodes. Changing settings does not repair those already-saved trees; a fresh conversion is necessary. A warning acknowledgement cannot bypass incomplete source-content guards.

## Broader codebase findings and priorities

| Priority | Finding | Evidence / proper correction |
| --- | --- | --- |
| P1 | Direct Apply can overwrite edits made during media preparation | `BatchRunner` records the target fingerprint before media import but does not recheck it before the direct write. A controlled `add_attachment` callback changed the disposable target's Elementor data; the job completed and overwrote that edit. Saved Apply has a corresponding check. Add the check to direct Apply, test both routes, and retain newer edits without creating a write backup or replaying the item. |
| P1 | Dynamic dependencies reach Apply unresolved | The exact five-export reproduction yields the report's 0/5/2/1/1 fallbacks. Add per-layout dependency preflight, an explicit same-source choice for image-free exports, missing-ID mapping controls and a readiness summary. Resolve against validated source identity; never reuse source database IDs as destination IDs. |
| P1 for this layout | Learning Center has no supported blog-grid adapter | The parser identifies and preserves `dgbm_blog_module`; the registry has no converter. A real adapter must preserve query, post count, exclusions, pagination, ordering, category behavior, card markup and responsive styles against an installed compatible widget. Choosing a text/HTML replacement or suppressing the warning would lose its dynamic behavior. |
| P2 | Code-module parsing bypasses the node limit | The `et_pb_code` fast path executes before the node-count guard. A 250,025-byte fixture containing 10,001 code modules was accepted; the equivalent text-module fixture was rejected. Enforce shared limits before both branches and add boundary tests. This is an authenticated-import resource issue, not evidence of an unauthenticated attack. |
| P2 | Report diagnostics omit resolution context | Record effective options, mapping/resolution outcomes and plugin/WordPress/Elementor/PHP versions. Give unresolved page links their own diagnostic code and actionable controls instead of generic unsupported-module guidance. |
| P2 | Recommendations do not follow layout selection | Settings are computed from all pending uploads before the selection UI; selection changes only update counts/target controls. Recalculate or validate selected-layout provenance and defaults, especially for mixed-source batches. This is a static workflow finding, not the cause of this all-selected batch. |
| P2 | Native responsive CSS snapshots can override later editor changes | The source-breakpoint compiler saves portable CSS from the values at conversion time. The existing compatibility guide documents reconversion after responsive edits. Add supported editor-save regeneration or a clear guided refresh mechanism before claiming unrestricted native editing. This review did not perform a new visual editor reproduction. |

## Codebase review scope

The source-tree inventory reads 157 PHP files / 5,174 lines. Manual review traced admin authentication, JSON upload/private storage, origin recommendations, parsing/presets, module conversion, style/serialization paths, target/control validation, asset/link processing, bulk/saved jobs, backup/rollback/locking, reports, Theme Builder targets and known unavailable adapters. Thin converter wrappers and extension facades were cross-checked against the actual runtime routes and existing feature inventory.

Upload size/depth limits, private owner-scoped storage, nonces/capabilities, safe public HTTP resolution, duplicate-target checks, source-shortcode preservation, saved-output hashes and published-page guards are present. These safeguards do not eliminate the separately reproduced direct-write issue. Module Coverage means a converter is registered; it does not mean every dynamic dependency or style variant is ready to apply. General post/ACF/Woo dynamic mapper facades are not wired into `DocumentBuilder` as general runtime adapters. Atomic output, unsupported provider integrations and licensed-runtime acceptance remain explicitly limited.

This was a source review and targeted reproduction, not a fresh full security audit, full-site pixel comparison or live staging acceptance. The previous release checks retain their original tested scope; the newly reproduced failures require new regressions and corrections before publication.

## Original files and evidence

The five available files are `C:\Users\ay\Downloads\Get Started.json`, `Careers.json`, `Why Suite Edge.json`, `Learning Center.json`, and `Reviews.json`. Every preserved blocker matches the report's raw shortcode exactly. Filename punctuation differs in three report labels; full file identity cannot be established from a report that omits source-file hashes. Confirm that these are the uploaded copies; provide locations only if newer/different copies were used.

Private evidence is in `.qa-evidence/report-384c-audit.json`, `report-384c-reproduction.json`, `report-384c-public-links.json`, `report-384c-direct-apply-edit-race.json`, and `report-384c-parser-limit.json`. No user staging or production page was changed. The race reproduction created and removed only its own disposable QA page and attachment.
