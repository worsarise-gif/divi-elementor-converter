---
updated: 2026-10-12
phase: Native Divi 4 acceptance and repair
---

# Current state

The user supplied Divi 4.27.9. It is installed on both disposable QA sites; the earlier statement that a licensed package is unavailable is superseded. No live site has been modified.

Historical 1.8.0-rc.1 packaging and automated/projection checks passed on 2026-10-10. That ZIP predates the subsequent native repairs and no longer matches current source. Preserve it and stable 1.7.0; build a new candidate after acceptance.

Implemented native repairs: deterministic responsive kit capture; FitVids opt-out on retained compositions; source Icon List line-height inheritance; retain the actual Shortcode widget DOM/defaults while intercepting validated provider execution during review. Source/media/provider freshness and application guards remain active. The real Divi saved Apply succeeds for both complete fixtures, covering 53/53 and 130/130 nodes. The latest full composition WordPress run passed 264 assertions with native Divi on MariaDB (2026-10-10).

Not yet accepted: latest full native browser comparison still has FAQ typography/cascade and form-related geometry differences. Visual Builder booted without page errors, but save/reload has not been tested. Real form submission/delivery and original remote image/provider availability remain separate staging checks.

Immediate priorities: trace exact matched CSS and source DOM; repair confirmed differences; verify native frontend and Visual Builder save/reload; rerun relevant regressions and package checks on the exact new ZIP. Test environments have offline HTTP/cron isolation to avoid unrelated update requests blocking the single-worker local server.

Evidence: `.qa-evidence/native-divi-browser.json`, `native-divi-fixtures.json`, `private-native-editor-probe.json`, and the historical `reverse-candidate-final-qa.json` under the workspace root. Source verification resumed 2026-10-12; older test dates are retained accurately.

MemPalace status event: `evt_20261011T231205_b3b89e5cf9ee`, stream `project/divi-json-to-elementor`; event storage succeeds, semantic Chroma storage is unavailable. See [[PROJECT]] and [[HANDOFF]].
