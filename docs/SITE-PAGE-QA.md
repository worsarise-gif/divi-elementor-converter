# Local Divi page conversion - 1.6.0

The new **Convert Site Page** workflow converts a local Divi 4 page body into an existing Elementor page, an explicitly selected in-place destination, or a new draft. It uses the existing converter, validator, media pipeline, job worker, saved Apply and rollback. No JSON export/upload is required; the source is captured privately in the database. Reverse Elementor-to-Divi conversion is not implemented.

The initial wording named both directions. Implementation follows the specific source Divi / target Elementor page flow; the optional direction clarification was unanswered.

## Evidence and implementation decisions

- Installed WordPress 7.1.2 / Elementor 4.3.3 / UAE 2.9.5 source code and actual registered widget controls were inspected before mapping target controls. QA uses PHP 8.3.35 on SQLite and MariaDB 11.4.11; syntax and standalone checks also use PHP 8.0.30.
- Divi 4 sources contain `et_pb_` shortcodes. A real customer article layout was loaded as a local source and converted through the new flow. Private content and customer fixtures are excluded from the plugin ZIP.
- Elegant Themes documents [global presets](https://help.elegantthemes.com/en/articles/8630728-divi-global-presets), [portability](https://help.elegantthemes.com/en/articles/8626073-importing-exporting-divi-builder-layouts-library-collections), and [separate Theme Customizer portability](https://help.elegantthemes.com/en/articles/8548302-how-to-migrate-the-theme-customizer-settings-in-divi). These establish that a page body is insufficient to reconstruct all inherited styling. The workflow captures supported presets and readable referenced library globals and explicitly asks the user to review theme defaults.
- The preset-reset utility [linked by Elegant Themes](https://help.elegantthemes.com/en/articles/9910009-how-to-delete-all-presets-and-global-variables-on-a-divi-website) documents the Divi 4 storage key `et_divi_builder_global_presets_ng` in its [repository](https://github.com/eduard-un/reset-divi-presets-and-global-variables). Its reset code was not executed. Captured data passes the existing strict Divi 4 versioned preset resolver; unknown schemas remain blocked. Storage-key evidence does not establish compatibility with every storage variant.
- Private snapshots include source title/body/status, the site URL, presets and referenced `et_pb_layout` content. Fingerprints are checked at job creation, analysis, saved Apply queueing and before writing. Source edits, preset/global changes, another site, unavailable globals, cycles, active Elementor sources and Divi 5 are protected.
- Every selection has an owner-bound, expiring, single-use UUID. Two tabs and the existing upload queue stay independent. The shared job and page locks, target assignment/output hashes, nonce checks, permissions, backups and rollback remain in force.
- Only verified same-site menu/category IDs are suggested. Supported published page/post/attachment dynamic links resolve locally, including subdirectory installations. Uploaded foreign numeric IDs retain their existing explicit mapping contract.

## Additional defects found while testing

The native Elementor Basic Gallery registers and renders `wp_gallery`. The converter emitted `gallery`, causing native validation to fail. The corrected mapping is checked against real registered controls and rendered attachments. ID parsing is bounded and strict; malformed IDs cannot bind accidentally to a different local attachment, and deleted/non-attachment records block application. Full Divi gallery design and slider parity remain partial.

Menu mapping discovery inspected only inline attributes. A menu ID supplied by a preset could therefore be absent from the mapping form although the converter later needed it. Discovery now resolves supported presets before presenting references.

Long page titles could enlarge native selectors at 320px. The new selection form now constrains its grid children and native inputs; this was reproduced and retested in the browser.

## Validation record

[SITE-PAGE-EVIDENCE.json](SITE-PAGE-EVIDENCE.json) records final assertion counts, the exact ZIP hash and installed-package checks. The suite includes local source/target selection, new draft, in-place conversion, analysis preservation, saved Apply without reconversion/duplicate jobs, rollback, clearing jobs, stale sources/targets, session ownership/expiry/replay, separate tabs/uploads, presets/globals, galleries, local links and a real customer layout. Existing page, post, bulk, header/footer, typography, motion, provider fallback and concurrency regressions are retained.

Real browser QA covers 320, 768, 1024 and 1440px, native labels and keyboard order, nonce/invalid-ID errors, exact older-page ID selection, review, completed analysis, saved Apply on the same job, consumed-session feedback and frontend rendering. A subsequent WordPress audit verifies the source stayed unchanged and rollback restores the exact target. This is focused browser and native integration acceptance, not a complete accessibility certification.

## Remaining acceptance

Actual Divi runtime storage variants, Theme Customizer inheritance, full frontend/editor/source visual parity, Elementor Pro/Woo providers and declared minimum WordPress/PHP runtimes require additional acceptance. Sitewide CSS, custom-font registrations, Theme Builder templates and display conditions use separate workflows or manual review. Divi 5 and reverse conversion require independent parsers, mappings and fixtures. No staging/live site was modified.
