# Reverse conversion repair candidate: 1.8.0-rc.1

This implements the R01-R05 repair contracts from the two report diagnoses. R06 automated and browser projection checks are complete; licensed Divi 4 frontend and Visual Builder acceptance remains pending because the user has no local licensed package. This candidate is for staging, not a claim of complete production visual parity.

| Chunk | Implementation | Evidence / remaining acceptance |
| --- | --- | --- |
| R01: shared normalization | Empty globals retain local values; default kit records are captured; p/span/div labels preserve tags; empty Icon Boxes and module-specific colors map correctly; scheme-less host links use source WordPress normalization. | Focused PHP and official Elementor controls; invalid protocols, attributes and missing globals still block. |
| R02: recursive layout | Accepted Classic containers retain actual nested surfaces, px/percent widths, flex/grid tracks, gaps, overlays, advanced geometry and responsive controls through an in-memory source CSS compiler and a scoped Divi Code composition. | Both complete report fixtures have zero mapping blockers with their dependencies present, and coverage is 53/53 and 130/130. Native module inference remains guarded for unsupported fractions/px widths. |
| R03: lists and FAQ | Icon Lists retain ten actual items. Nested Accordion titles bind to five actual child containers/answers; source-dependent FAQ CSS remains against the same DOM. Independent behavior retains initial/single/multiple states and keyboard focus. | Seven viewport widths, initial styling and click/keyboard checks; licensed Divi editor persistence remains pending. |
| R04: providers / embeds | Independent HTML keeps ordered cascade. Formidable is validated without executing it during review; existing form, fields/actions and version are fingerprinted before Apply. Each output includes its own source font links. CSS and Font Awesome binary resources are checked. | Actual Formidable 6.35 rendering and validation; missing providers/forms, changed fields, fonts or CSS block. No entries, email or automations are sent by QA. Real submission/delivery acceptance remains pending. |
| R05: complete diagnostics | Every descendant is audited. Reports record node dispositions, setting provenance, typed cause codes, source/destination versions, blocking issues, affected paths, coverage and runtime failures separately. | Legacy warning arrays and `blocking_modules` key remain for compatibility. Valid unsupported controls are not described as user styling mistakes. |
| R06: acceptance | Existing forward/reverse lifecycle suites, two PHP versions, SQLite/MariaDB, real-provider checks and browser projection comparison. | Divi writer tests use explicit stubs. No licensed Divi render/editor QA or live-site deployment has occurred. |

Browser projection compares official Elementor Post CSS and original renderer output with the retained Code HTML/CSS at 1440, 1025, 1024, 768, 767, 390 and 320px. It uses actual official Sora/Roboto/Roboto Slab font binaries from a private local cache and deterministic image dimensions. It verifies the page body, not theme framing, original image availability, live provider availability or native Divi rendering.

## Final automated verification - 2026-10-10

- [x] Complete both private report fixtures: 53/53 and 130/130 nodes, zero mapping blockers with their actual dependencies available. The Client Portal Vimeo embed retains a review warning.
- [x] PHP 8.0.30 and 8.3.35: 906 assertions per runtime plus the existing pipeline check; 244 PHP files pass syntax checks on each runtime.
- [x] Exact installed candidate on SQLite and MariaDB: 681 assertions per backend, including 262 full-composition assertions. Review, saved/bulk Apply, bound targets, stale dependencies, interrupted-write protection, exact rollback and forward regressions pass.
- [x] Browser projection: 55,531 comparisons/interaction assertions across fourteen paired projections, zero mismatches and JavaScript errors. FAQ single-expanded state, ARIA synchronization and keyboard navigation pass at all seven widths.
- [x] Real local media metadata is captured. Native images retain alt text; retained full-size images use their verified local attachment renderer instead of discarding the ID. Attachment metadata edits invalidate saved Apply. Foreign fixture IDs are not treated as local media proof.
- [x] Actual Formidable form rendering and email-field validation, and missing/changed provider checks. Existing local font CSS and binaries are fingerprinted; changed/missing files block Apply. Review performs no network fetch, provider execution or mail delivery.
- [x] Candidate installation byte-matches all 208 packaged files on both databases. WordPress Plugin Check, including runtime checks, reports no findings. Private fixtures, test sites and QA font binaries are excluded.
- [ ] Licensed Divi 4 frontend and Visual Builder acceptance, real source-image availability, and authorized form submission/delivery on staging remain pending. Automated writer tests use declared Divi stubs.

The final versioned ZIP is `ace-divi-to-elementor-migrator-1.8.0-rc.1.zip` (1,627,120 bytes), SHA256 `99792ad570202911419eeb1fcf08e831a81bf2f35a44b3994bc46ea0ec19665d`. Detailed local evidence is recorded in `.qa-evidence/reverse-candidate-final-qa.json`. The stable 1.7.0 ZIP remains unchanged.

## Required staging acceptance

1. Install this candidate on staging alongside licensed Divi 4 and the source Elementor/font/Formidable providers.
2. Run fresh conversions; old blocked jobs are immutable historical outputs and are not repaired by Apply.
3. Review the Code editing disclosure, dependency notes and coverage. Apply the saved result to its assigned draft.
4. Compare the real source and Divi frontend around the source breakpoints, test the FAQ, links and form validation, and save/reload in the Visual Builder.
5. Verify real form delivery and configured automations with an authorized test recipient; automated local QA does not establish these.
6. Test rollback. Publish only after these checks pass on the actual destination theme.

The stable 1.7.0 archive and generic download aliases are preserved. Candidate archives are versioned separately. Private reports, HTML projections, font binaries and test sites are excluded from distribution.
