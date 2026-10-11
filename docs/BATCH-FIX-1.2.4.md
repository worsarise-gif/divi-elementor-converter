# Five-page batch repair - 1.2.4

The newer report `807178f3-cfca-4550-844d-4349586f779b` was found alongside the supplied `384c83d8` report. It records `https://suiteedge.com` as the source: four pages applied, Learning Center failed. The source URL resolved the three earlier dynamic-button failures. Learning Center still contains the unsupported third-party `dgbm_blog_module`; a reference URL cannot supply that extension's query or implementation. Careers' five retained live-site links are separate warnings because their destination pages do not resolve on staging.

## Implemented repair

* An entered reference page URL is normalized to its origin and enables public page-link lookup unless explicitly switched off. Recommendations preserve the entered source URL, explicit lookup choice and edited theme defaults.
* Public lookup supports the standard plain REST route when the pretty endpoint returns 404/405, and accepts the same site's conventional www canonical alias. It still validates the requested ID, published page type, safe URL, host, port and HTTPS boundary. Returned unrelated URLs cannot become destination bindings.
* Reports include effective lookup settings, explicit link/replacement mappings, per-page REST outcomes and the source checksum. Missing source URL, disabled lookup, HTTP failures and unsupported extension modules have distinct repair instructions. Updating settings does not silently rebuild an older job.
* Unsupported dgbm modules can be explicitly replaced with a saved Elementor container template. Configure its installed native grid widget, destination post query, pagination and responsive design first. Map the replacement by source file and module type, then reconvert. The newest 100 templates are listed; a validated manual ID selects an older template. The whole module design is replaced, source posts are not imported, and every occurrence of that type in the source file uses the chosen template.
* Replacement native elements/settings are copied and validated against installed Elementor controls. New IDs retain explicit stylesheet-selector compatibility. Seven- and eight-character native IDs and destination global style references are supported. Missing/trashed/inaccessible templates, unresolved shortcode widgets, nested template widgets and unconfigured forms remain blocked. Saved Apply uses the copied output even after library edits. The native widget provider remains required; Ace is not required for rendering the copied widgets.
* Direct and saved Apply check the target fingerprint after media preparation and before backup/write. An unrelated editor save during image import is preserved. This is optimistic edit protection with advisory locks, not a transactional guarantee for all third-party writers.
* The tokenizer's code fast path now shares the module/depth limits. A self-closing code node cannot consume a following paired code node, and literal shortcode text inside paired code is preserved.

## Verification and limits

The 18 WordPress suites cover 4,058 assertions per backend on disposable SQLite and MariaDB 11.4.11 with WordPress 7.1.2, Elementor 4.3.3 and UAE 2.9.5. This includes 38 source-link, 37 native replacement and 12 concurrent-edit assertions per backend. All five original exports applied to distinct drafts and rolled back with the explicit Learning Center replacement; the original unsupported module remains blocked when no replacement is selected. A real UAE Basic Posts query renders the destination posts with the copied count/order and responsive column settings.

Standalone checks cover 408 assertions plus the pipeline per PHP 8.0.30 and 8.3.35. Syntax checks include all 194 PHP source/test files on both runtimes. Browser workflow and native-grid evidence is recorded in `fix124-browser.json`; install/package equality and installed Plugin Check evidence are recorded with the 1.2.4 release. Private source fixtures and QA evidence are excluded from the distribution ZIP.

This does not establish automatic Divi extension fidelity. UAE Basic Posts is a simple grid; use a provider with category filtering and pagination when required. Pro-specific provider acceptance, the original source grid's visual/query equivalence, minimum PHP/WordPress execution, MySQL 8/multisite and the user's staging acceptance remain open. The user's staging site was not changed.

## Next steps on staging

1. Install the Ace 1.2.4 ZIP as an update.
2. Build/save the Learning Center native grid container template using a provider that supports its query and pagination. Ensure the destination posts/media exist.
3. Upload Learning Center's JSON again. Select its page, choose that saved template under Unsupported module replacements, and verify the source URL/link lookup.
4. Convert for review, inspect the explicit replacement warning, then apply the new result and compare all devices. Old failed output cannot be repaired by saved Apply.
5. Create/migrate the five career-detail destinations, or explicitly map their source page IDs to the correct existing destination URLs. Reconvert Careers after those targets resolve. Retaining their live URLs until then avoids guessed or broken staging links.

Current feature/function rankings and `test-evidence.json` retain the earlier audit history and report the corrected scope. Publication acceptance remains separate from this bounded fix.
