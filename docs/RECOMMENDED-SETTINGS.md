# Recommended settings - 1.2.0-rc.4

The supplied reports showed a completed analysis being mistaken for an applied conversion, followed by unresolved page links when the source settings were left blank. RC.3 makes the mode explicit and preselects the settings exercised by the Home pilot.

| Setting | Default |
|---|---|
| Mode | Analyze only; choose Apply to pages to update assigned pages |
| Import images | Checked; embedded images first, then safe remote import |
| Reuse Media Library matches | Checked; exact provenance matches |
| Source origin | Suggested only when all uploaded exports identify one WordPress media origin |
| Resolve source page links | Checked when a source origin is suggested |
| Acknowledge published-page warnings | Remains the administrator's explicit review decision |

Home's media identifies `https://suiteedge.com`. Arbitrary HTML links do not influence the suggestion. Unknown or conflicting origins leave the field blank and link resolution unchecked; enter the correct origin or supply explicit mappings. Recommendations do not make network requests while rendering the form. Starting a job uses the selected settings. Use recommended settings restores media/origin/resolution and theme controls and leaves warning acknowledgement unchanged.

The form now has two visible submit buttons: Analyze only / download template and Apply to pages. The analysis button comes first. A submission without a mode stays analysis; invalid modes are rejected. The server uses the clicked button's mode independently of JavaScript. Existing completed analysis jobs remain terminal; start a new job to apply.

Source theme defaults are editable because Divi portability layouts omit Theme Customizer values. Recognized reference-site media origins recommend the measured 1280px row maximum, Arial body font, Inter inherited heading font (explicit Home Montserrat values remain unchanged), 18px default body size, #333333 inherited heading color and blue accent; other origins receive standard Divi defaults. Explicit exported values take precedence. Use recommended settings also restores these theme fields. See VISUAL-REGRESSION.md for import portability and the remaining browser acceptance limit.

Job history and reports say Analysis completed â€” no page changes. An analysis containing shortcode fallbacks or unconfigured forms says conversion blocked. JSON reports include the mode, analyzed/converted counts and blocking-module count. Published-page protection and unresolved-module blocking continue to apply.

## Verification

39 recommended-settings/mode assertions on each of SQLite and MariaDB 11.4.11 cover origin inference, mixed/unknown origins, owner isolation, real form controls, missing/invalid mode handling, assigned-page writes and rollback, report outcomes and real Home defaults. A controlled REST fixture resolves all 14 actual Home page IDs through the plugin resolver, producing all four native buttons and twelve native icon boxes without fallback. This is fixture evidence; it does not assert that every host permits outbound REST traffic.

The authenticated HTTP suite exercises the explicit modes, checked defaults, report outcomes, embedded image import, draft writes and rollback. Existing standalone, WordPress integration, production and Home suites also pass. Counts and syntax/package evidence are in test-evidence.json.

Browser interaction/visual acceptance, scroll/parallax fidelity and licensed integrations remain open. This release does not apply any change to the user's live site automatically.
