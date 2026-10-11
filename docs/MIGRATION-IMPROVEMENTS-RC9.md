# Migration workflow and fidelity improvements - 1.2.0-rc.9

This increment addresses the requested accordion, button padding, individual Apply, job clearing, multi-convert and bulk Apply workflows. Test counts and remaining acceptance limits are recorded in test-evidence.json and FEATURE-QA.md.

## Diagnosis and changes

The earlier accordion used Elementor's joined borders, lacked Divi's item gaps, and kept a separator above answers. The source FAQ measured on October 6 uses separate 1px borders, 20px outer item padding, 50px title space for the icon, no answer separator, a 5.5% item gap above 980px and a 10px gap below it. The last item has no default bottom gap. These defaults now survive conversion. Shared title/body typography and borders use native controls; supported child overrides, closed/open surfaces, responsive values, item spacing and state-specific styling use scoped CSS. All-closed and nonfirst-open initial states use the embedded runtime once; pointer/keyboard interaction and later resizing remain under Elementor's handler. Source child presets are resolved before mapping.

Button surface padding now preserves all four source sides. Complete dimensions with a native unit use Elementor's responsive text_padding controls. Mixed units, partial inherited sides and enabled hover padding use portable per-side CSS with enough specificity to override Elementor's surface reset. Responsive CSS preserves the desktop → tablet → mobile cascade. Blank sides inherit; explicit zero stays zero; inactive saved values stay inactive. When no surface padding is exported, Divi's 0.3em vertical / 1em horizontal defaults replace Elementor's preset dimensions. Fractional values such as .5em are accepted. Invalid active spacing retains the whole source module and remains a write blocker.

Completed jobs expose Apply to Page beside each compatible analysis result. Selecting several completed results exposes Apply Selected to Pages. The server queues the existing immutable output on its original job and target; it never rebuilds shortcodes or creates another migration job. Embedded media and destination links are prepared during application. Output hashes, original target IDs, page fingerprints, capabilities, runtime controls, job/page locks and published warning acknowledgement are checked. A repeated successful Apply is a no-op; a new draft is allocated once. A failure before writing retains the analysis for review/retry. Once a write starts, existing backup/recovery protections apply. Partial jobs may apply their completed analyses while failed/unconverted items remain untouched. Reports distinguish applied pages from remaining analyses.

Mapping now supports selecting any subset of uploaded layouts and Convert Selected. Unchecked layouts remain in the mapping session with their original indices. Double submissions cannot remap consumed indices to another source. Duplicate destination pages are rejected. Each job lists the source, fixed destination and current state of every page; polling returns per-page outcomes.

Clear Migration Jobs archives the selected jobs. It never deletes a post, changes page metadata or deletes a rollback backup. View cleared jobs provides reports, rollback and Restore Selected Jobs. Unused pending jobs are cancelled before clearing. Running, processing and recovery items cannot be cleared until resolved; partial clear failures are displayed individually. Normal pagination excludes archived records, with owner filtering applied in both views. Existing bounded retention still applies to eligible records; every backup protects its job and source.

The active-job lookup now matches the job's state instead of nested page states and excludes cleared jobs in SQL. A regression with 105 newer cancelled/cleared records verifies that an older active bulk job remains discoverable. Migration admin screens also disable WordPress's cross-document view transitions to avoid navigation errors during polling.

An actual RC.8 portable runtime loaded before a new accordion reproduced a second initial-state defect: the older motion registry consumed newer definitions without applying their accordion state. Accordion initialization now has an independent portable registry. Nine desktop/mobile browser checks verify all-closed/nonfirst-open states, native interaction and resize preservation when both runtime generations coexist.

## Using the update

1. Install RC.9 on staging and upload/scan the Divi exports.
2. Map each checked layout to its intended page or a new draft. Use Convert Selected to save results without changing pages.
3. Review the reports. Use Apply to Page for one result, or select completed result checkboxes and use Apply Selected to Pages. Explicitly acknowledge reviewed differences for published pages when required.
4. Inspect desktop, tablet, mobile, links and accordion interaction in Elementor. Older saved outputs retain their original styling; convert again to generate the new accordion/padding mappings.
5. Select finished/unneeded jobs and use Clear Migration Jobs. Reports and rollback remain available under View cleared jobs.

## Boundaries requiring review

Mixed child heading tags, multiple initially open accordion items, nested module content, unknown glyphs and unimplemented advanced child settings remain guarded fallbacks. Exact ETModules outlines are represented by bounded native Font Awesome equivalents and require visual review. Shared native typography loads its fonts through Elementor; distinct child fonts and custom fonts must be available on the destination. Device controls use destination Elementor breakpoints; the measured Divi default accordion gap explicitly retains its 980px threshold. Theme Customizer values and custom site CSS are not included in Divi layout JSON. Exact staging pixels and manual editor acceptance remain open.

Older analysis jobs lacking RC.9 fingerprints are validated and snapshotted at queue time. Edits made before that snapshot cannot be compared with their original analysis; verify their current target before applying. Changes after queueing are protected. Completed output with unsupported modules or unconfigured forms cannot be written. Imported attachments remain in the Media Library during rollback or clearing.

The accordion model was checked against the [source FAQ](https://suiteedge.com/get-more-customers/), the [Elegant Themes font conversion outline](https://dev.elegantthemes.com/docs/explanations/module/conversion-outline/advanced-properties/fonts/) and the installed official Elementor widget/handler source. The [Elegant Themes initial-state guidance](https://help.elegantthemes.com/en/articles/4748294-how-to-close-all-accordion-module-s-items-upon-initial-page-load) confirms Divi's default first-open behavior. No live/staging pages were modified during this QA increment.
