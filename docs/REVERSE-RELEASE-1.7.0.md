# Elementor to Divi release evidence - 1.7.0

Date: October 10, 2026. This adds the separate **Ace Migrator > Elementor to Divi** page workflow and retains the installed basename, namespace, text domain and stored upgrade keys. The product name is Ace Migrator for Divi and Elementor; the WordPress.org contributor remains KERVEE.

Choose the current Elementor source and an ordinary destination page or new draft, convert for review, inspect per-element coverage, then apply the saved output in Migration Jobs. An owner-bound selection creates one job even when submitted repeatedly. Existing target details remain intact; new destinations stay drafts. Explicit in-place conversion, saved/bulk Apply, clearing, recovery and rollback reuse the shared lifecycle.

The semantic adapter catalog has 25 bounded mapped widget types and seven guarded types, including the external Shortcode dependency. This is a content/storage test result, not acceptance of all styles or native rendering. Enabled unaccepted controls block Apply. The full contract is [REVERSE-MAPPING.md](REVERSE-MAPPING.md), which ships in the ZIP.

Verified local forward migrations created in this release can restore their original Divi content byte for byte while current Elementor/page/kit data and original Divi page/preset/global dependencies still match. The private per-page proof index retains ten candidates. Edited trees, older jobs and uploaded JSON use the semantic path; compiled Elementor DOM styles are not attached to an unrelated Divi DOM.

## Verification

| Check | Observed result |
| --- | --- |
| PHP syntax | 235 PHP files passed on PHP 8.3.35 and 8.0.30. The final icon test extension was also checked on PHP 8.0. |
| Standalone suites | 870 assertions plus the pipeline smoke check per runtime; 124 are reverse semantic/safety assertions. |
| WordPress regression | Six suites, 415 assertions on each SQLite and MariaDB backend: reverse, local page, posts/blog, concurrency, shared WordPress and Theme Builder. |
| Final installed reverse tests | 56 assertions per backend on the exact packaged plugin, including actual installed solid/regular icon libraries and unknown/SVG guards. |
| Browser | 35 reverse workflow checks at 1440, 1024, 768 and 320px; four subsequent database assertions confirm source/target preservation and exact analyzed job/target binding. |
| Plugin Check | No static/runtime findings on the installed final ZIP; two domain diagnostic exceptions use narrowly explained output-boundary annotations. |
| Archive | 200 files, 1,606,042 bytes; every installed file matches the ZIP on both sites. Private exports/tests/QA data are excluded. |
| Prior release | The versioned 1.6.1 ZIP hash remains unchanged. |

Final public file: `ace-divi-to-elementor-migrator-1.7.0.zip`.

SHA256: `2bbbd18fefdb19da08fbd2421e2e0efb5e4b8ed87f0f2b3b18d4e6c870fe2247`.

Evidence is in workspace `.qa-evidence/reverse-release.json`, `reverse-standalone.json`, `reverse-wordpress-{sqlite,mariadb}.json`, `reverse-installed-tests.json`, `reverse-browser.json`, `reverse-browser-audit.json`, `reverse-package-install.json` and `reverse-plugin-check.json`. Browser screenshots include the installed version and mobile form/report behavior.

## Acceptance still required

The disposable environment has WordPress 7.1.2, Elementor 4.3.3, UAE 2.9.5, SQLite and MariaDB 11.4.11. Native Divi is not installed. Writer tests explicitly register QA shortcode stubs: they verify lifecycle, exact content and backup behavior, not actual Divi rendering or Visual Builder editing. The real staging/live site was not changed.

Accept a licensed Divi 4 source/target pair in Visual Builder and frontend across desktop/tablet/mobile before publishing this feature as production ready. Font binaries/sitewide styles and template assignments need separate review. Unsupported boxed/compound layouts, dynamic providers, Pro forms/loops, reverse Posts/templates, Divi 5 and Atomic remain guarded or outside this release. WordPress.org approval and full minimum-version/provider acceptance remain separate checks.
