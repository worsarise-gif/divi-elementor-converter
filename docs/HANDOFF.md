# Immediate native acceptance handoff

Continue the authorized two-report repair and native acceptance using the user-supplied Divi ZIP. Do not stop at documentation or treat the old candidate as a new build.

Modified production components since rc.1: `src/Reverse/Source.php` (deterministic responsive kit capture) and `src/Reverse/Composition.php` (FitVids parent opt-out, Icon List isolation, actual Shortcode widget retained; `pre_do_shortcode_tag` returns the validated untouched shortcode during review and is removed in `finally`). Tests `reverse-wordpress.php` and `reverse-composition-wordpress.php` now use actual Divi when installed, preserving declared stub mode when absent.

Latest native frontend failures are recorded in `.qa-evidence/native-divi-browser.json`. The Vimeo duplication and Icon List line-height failures were resolved in the second run. Remaining source/target differences include FAQ text and downstream heights; investigate selector specificity and matched rules before changing mappings. The native CSS inspection helper was expanded to trace nodes `52fc4c3` and `18de10c`. Their actual Elementor post stylesheet includes authored local controls, but computed source styles also reflect companion FAQ CSS; confirm the exact cascade.

Resume commands from the workspace root:

1. Inspect `.qa-evidence/accuracy-services.json` and verify ports/process ownership. Start only when the QA ports are free; use `.qa-evidence/start-accuracy-services.ps1` with hidden processes. The server may have stopped during interruption. Stop only verified task-owned services before ending.
2. Run `node .qa-runtime/browser-qa/verify-native-divi.cjs --probe`. Read the private probe JSON rather than printing entire client fixtures. The QA site's MU isolation blocks external PHP requests, and `DISABLE_WP_CRON` is set only on test sites.
3. After confirmed fixes, copy affected source to both installed QA plugin folders, regenerate paired native fixtures with `.qa-evidence/setup-native-divi.php`, then rerun the native browser comparison. It uses real Divi and local real font binaries with deterministic remote-image dimensions; disclose these limits.
4. Use the actual Visual Builder iframe `#et-fb-app-frame` on the cloned editor pages in `native-divi-fixtures.json`. Login through the existing private synthetic QA credential helper. Never print or persist credentials. Verify Code editing/save/reload and actual saved content with the licensed parser; opening the editor alone is insufficient.
5. Run relevant WordPress lifecycle/forward regressions on SQLite/MariaDB, PHP 8.0/8.3 standalone checks, release syntax checks, exact installed-ZIP byte verification, and Plugin Check. Preserve stable 1.7.0/generic aliases; increment the candidate rather than overwriting rc.1.
6. Update [[CURRENT]], acceptance documentation, and scoped MemPalace history with actual results. Do not distribute private fixtures, fonts, test sites, or the supplied licensed theme.

The root has no Git checkout; preserve unknown edits. Existing roadmap and decisions remain in the diagnosis/mapping documents linked from [[PROJECT]]. MemPalace event storage works in `project/divi-json-to-elementor`; semantic Chroma retrieval is unavailable. No memory repair/migration or Obsidian configuration changes are authorized by this task.
