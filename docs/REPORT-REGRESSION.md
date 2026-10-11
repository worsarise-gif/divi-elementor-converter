# Reported alignment regression - 1.2.0-rc.2

The supplied migration report records one failed Home dry run with `Invalid target control value: image.align`. It does not record a successful conversion or apply operation. The plugin defect is reproducible against the installed Elementor 4.3.3 source using full WordPress admin control metadata.

## Cause and repair

Elementor's image alignment control exposes `start`, `center` and `end` as options and declares physical `left`/`right` aliases in `selectors_dictionary`. The validator previously rejected an alias absent from the options. Frontend optimized control stacks omit options, so the earlier Home pilot did not exercise this failing admin branch.

Validation now accepts a declared alias only when its mapped destination is a valid option. Existing canonical options remain valid even when their selector dictionary expands to a CSS expression, as with container flex direction. The stored alignment is unchanged: physical left/right values retain Elementor's own right-to-left mapping. Unknown values and malformed alignment arrays still fail. The repair applies to responsive controls as well.

`tests/control-aliases.php` runs in separate full admin and optimized frontend processes and covers five alignments on desktop/tablet/mobile, rejection of unknown values, native flex directions and a complete image dry-run job without target mutation. `tests/http_workflow.py` now includes responsive image alignment in its authenticated upload, dry-run, draft-write and rollback flow.

## Verification

- Exact original failure reproduced before the fix with full admin metadata.
- 27 admin and 26 frontend control-alias assertions on each of SQLite and MariaDB 11.4.11.
- 107 WordPress/Elementor integration and 29 production assertions on each database in admin mode.
- 40 real Home pilot assertions in admin mode on each database: all 239 modules and 26 embedded images, native rendering and rollback.
- 107 standalone assertions on each of PHP 8.3.35 and PHP 8.0.30.
- Authenticated HTTP results and package syntax counts are recorded in `test-evidence.json`.

These are backend/runtime checks. Visual/editor acceptance and the remaining capabilities in TODO.md remain open. The report does not include installed component versions; its exact remote version was not inferred.

## Retry

Replace the installed plugin with the RC.2 ZIP, then upload/map Home again and start a **new analysis job**. Completed failed jobs are terminal and are not automatically replayed. Review the new report before applying conversion.
