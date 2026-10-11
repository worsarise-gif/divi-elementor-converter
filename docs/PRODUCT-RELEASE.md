# Product release verification - 1.2.0

The current bidirectional workflow is documented in [REVERSE-RELEASE-1.7.0.md](REVERSE-RELEASE-1.7.0.md), including its tested subset and pending licensed Divi acceptance.

The current Ace font/branding update is documented in [ACE-RELEASE.md](ACE-RELEASE.md). The previous branding update is documented in [KERVEE-RELEASE.md](KERVEE-RELEASE.md). This file retains the verification history of the original 1.2.0 product release.

The admin experience and distribution are prepared for publisher review. This is not a claim of WordPress.org approval, universal conversion, or acceptance on the real staging site.

## What changed

* One scoped, responsive Ace interface with Overview, Pages, Headers & Footers, Migration Jobs, User Guide, and Settings.
* Contextual mapping/coverage/report screens retain their original URLs while the sidebar avoids duplicate and intermediate navigation entries.
* Guided upload, destinations and settings, clear conversion/application instructions, source-default and advanced-link disclosures, empty states, status labels, and saved-setting feedback.
* Convert for review is the primary first action. Warning acknowledgement is beside Apply. Conversion does not update targets or import media; Apply performs media preparation and the protected write.
* In-plugin user guide with quick start, UAE assignments, settings, result meanings, troubleshooting, requirements, data/privacy, and support reproduction instructions.
* 1.2.0 directory readme/header, contributor KERVEE, GPL license, Elementor dependency, matching text domain and POT, clean release builder, public documentation, original icon/banner and real sample-content screenshots.

## Verification performed on this release

| Check | Result |
| --- | --- |
| PHP syntax | 179 files on PHP 8.3.35 and 8.0.30 passed. |
| Standalone conversion suite | 300 counted assertions per runtime plus the pipeline smoke check passed. |
| WordPress integration | 564 assertions per SQLite/MariaDB backend passed, including real Home, Footer, Header, UAE, accordion, button padding, saved/bulk Apply and warning guards. |
| Final installed regression | 227 assertions per backend passed again on the packaged plugin after SQL/input review. |
| HTTP | 28 login, multipart upload, nonce, analysis, report and download assertions passed. |
| Admin responsive | 336 checks across 1920, 1440, 1024, 782, 768, 390, and 320px. No document overflow; one heading/current nav; scoped styles. |
| Admin workflow | 19 checks: settings persistence, two-layout upload/subset mapping, recommended values, saved Apply, report/download availability, archive/restore, unrelated-screen CSS isolation and no-JavaScript help. |
| Missing Elementor | Seven browser guidance checks passed; exact QA activation state restored. |
| Plugin Check | No outstanding findings in static or runtime-enabled checks on the distribution. |
| ZIP | 163 packaged files, 191093 bytes. Both QA installations match every archive byte. Tests/private exports/credentials/internal QA documents excluded. |

SHA256: `6711fb1f43039f2042ff4158332037a6de965e1f27e60706779a2b218773071d`.

Tests used disposable local WordPress 7.1.2, Elementor 4.3.3, UAE 2.9.5, MariaDB 11.4 and SQLite. Existing frontend/browser/crash evidence remains from RC.12; this UI change does not claim those checks were rerun as new pixel-parity tests. New CSS is confined to the plugin admin screen and is absent on unrelated WordPress screens. The real staging site was not modified.

## Static-analysis review

Actual changes address license/readme headers, dependency metadata, the text domain, escaped admin option/notice output, input sanitization/unslashing, native WordPress URL/file/tag helpers and fully static prepared SQL branches with an identifier placeholder.

Narrow, explained `phpcs:ignore` annotations remain for context-sensitive findings: raw domain exceptions are escaped by HTML/JSON consumers; a JSON attachment must not be HTML-escaped; shared nonce checks authenticate each AJAX handler; structured JSON/theme/menu/upload inputs receive their domain validators; read-only navigation does not require a write nonce; live private-option/session-lock queries cannot use stale caches; attachment provenance lookup is bounded; SQLite QA requires an OS file descriptor for flock. No rule category is disabled globally. A clean tool result includes these reviewed exceptions and does not replace manual review.

## Remaining publisher acceptance

Install the ZIP on your own staging site and accept a real page, Header, and Footer across devices and interactions. WordPress/PHP minimum versions and every third-party/theme combination have not all been runtime-tested. Pro provider/mail integrations remain outside accepted release claims. Confirm the KERVEE account and Ace branding/redistribution rights, then submit the ZIP for WordPress.org review. See PUBLISHING.md and COMPATIBILITY.md.
