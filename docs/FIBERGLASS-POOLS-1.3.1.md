# Fiberglass Pools diagnosis and correction — 1.3.1

The supplied 0d983d88 report records one failed Apply item and two unique warnings. The exact supplied export matches its recorded source SHA-256, `afebd8dc3695ce53da85bbf666e32b7e1eba34e9870d5a160b8b5bbd80c47ac9`. It contains 32 Image modules and a Download Comparison Chart button. Private exports, reports, source HTML, downloaded API evidence and QA pages are excluded from the release ZIP.

## Confirmed causes

1. The button contains `post_link_url_attachment`, with source attachment ID 1543. DocumentBuilder previously recognized only `post_link_url_page` and `home_url`. It mislabeled the attachment as a source page, performed no lookup despite the enabled source URL/lookup settings, and preserved a shortcode fallback. The Apply guard correctly refused to replace the assigned page with unresolved content. This was not a missing source URL or a failed REST request.
2. Every image sets `force_fullwidth="on"`. The image converter did not consume or map that property. Deduplicated report warnings conceal how many instances are affected; the reproduced builder emits it for all 32 images.
3. A small-image responsive regression exposed an additional sizing issue: Elementor's flex widget defaults could override an explicit Divi module `max_width`. At 767 pixels, a 180-pixel source cap allowed a 240-pixel widget. Source module cap rules now outrank the default widget rules without changing the image's surface width or adding global CSS.

The freshly retrieved original [Fiberglass Pools page](https://bestpoolsllc.com/fiberglass-pools/) uses `https://bestpoolsllc.com/pool-comparative_digital_opt/` as the button destination. The public media record returns that attachment permalink and a different `source_url` for the PDF. A read-only HEAD request followed the original permalink to the PDF and returned HTTP 200, `application/pdf`. Preserving the permalink matches the actual Divi behavior; substituting the file URL would change it. WordPress documents this distinction in its [Media REST schema](https://developer.wordpress.org/rest-api/reference/media/).

## Implementation and scope

- A dedicated binding parser accepts complete, understood page/post/attachment tokens in supported URL fields. Malformed payloads, inactive explicit dynamic flags, extra binding semantics, wrapped/multiple tokens, invalid/overflow IDs and unsupported types retain protected fallback. Image-source and text fields cannot receive attachment permalinks. Historical tokens without a dynamic flag remain accepted.
- PageLinkResolver retains its default page API and resolves pages, posts and attachments through `/pages`, `/posts` and `/media`. Same-site IDs must match the requested post type and public status. Foreign source IDs are never reused as local IDs. Explicit URL mappings remain available without remote lookup.
- Public requests retain bounded time/response size, safe redirects, host/port/credential checks, response ID/type/status validation and pretty-to-plain REST fallback. Successes and failures cache by request URL; diagnostics also distinguish the source type and item ID. Unsupported custom post types and arbitrary ACF/dynamic text values are not inferred.
- Attachment URL provenance is stored in the native document. It survives template insertion and saved Apply, so media preparation preserves the attachment permalink rather than guessing an unrelated destination page at the same path. The report separately explains the remaining source-site document dependency. Image import does not transfer PDF documents; upload them separately and map their source IDs if removing the source site.
- Image Force Fullwidth uses the native image width control and portable scoped CSS for linked images, supplied heights and active responsive off/on resets. Module width and max-width stay separate. Inactive device overrides do not activate, empty defaults do not generate false warnings, and malformed flags retain protected fallback. Unaccepted hover states remain reported.
- Existing saved output is not silently rebuilt. Source fallback, target assignment, output fingerprint, concurrent-edit, published-page, permission and rollback protections remain in place.

## Verification

Source-only `tests/attachment-links.php` exercises binding shape/type/field protection and full-width sizing states. `tests/attachment-links-wordpress.php` uses the exact supplied export on disposable WordPress sites and controlled public API responses based on the retrieved media record. It tests separate type routes/cache entries, REST/plain-route success, HTTP errors, malformed/wrong/private/foreign responses, explicit mappings, opt-out, native schema, template insertion, source attachment dependency reporting, direct Apply, saved Apply without reconversion, all 32 locally imported images, button padding/destination, frontend output, blocked writes and exact rollback.

- PHP 8.0.30 and 8.3.35: 591 standalone assertions per runtime, plus pipeline checks.
- WordPress 7.1.2 / Elementor 4.3.3 / UAE 2.9.5: 21 integration suites, 4,277 assertions per database backend (SQLite and MariaDB). The attachment/source regression contributes 135 assertions per backend.
- Exact Fiberglass Pools direct and saved Apply results: completed, 32 native images, one native button, zero errors and zero actionable warnings. Source attachment dependency remains visible as information.
- Headless Edge: 321 checks at 320, 390, 767, 768, 900, 980, 981 and 1440 pixels. Actual pool images load and fill their widget content widths, the button retains its destination/target/padding, small images follow on/off/on device states, explicit module caps hold, and the pool layout has no horizontal overflow or JavaScript errors. Native lazy loading was allowed to finish before assessing image geometry.
- Final release packaging and installed Plugin Check evidence is stored privately in `.qa-evidence/package-1.3.1.json` and `.qa-evidence/pools-plugin-check.json`. The final ZIP is installed and byte-compared against its manifest on both disposable sites.

These checks validate the supplied export and bounded regression fixtures. They do not claim pixel equality with a live Divi rendering, staging acceptance, universal module/property support, licensed Pro acceptance or verified PHP 7.4 runtime behavior. No staging or live page is modified.

## Publisher action

Install version 1.3.1 on staging. Upload/scan Fiberglass Pools again, retain `https://bestpoolsllc.com` as the source URL and enable public source link resolution. Convert a fresh result, review its report, then apply it to the intended page. Applying the old blocked job still uses its old unresolved output. Check the page on all devices and decide whether to upload/map the comparison chart to a destination document URL before retiring the source site.
