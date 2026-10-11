# Latest report diagnosis and repairs - 1.2.0-rc.5

The supplied report `a2c983eb-16f5-41aa-83a2-8f03a1d35a08` records a successful **Apply to pages** job: page **219**, one converted page, zero failed items and zero blocking modules. Its 15 messages are deduplicated by text; they do not represent 15 failed modules.

| Recorded messages | Cause | RC.5 repair |
|---|---|---|
| Eight supplemental scoped CSS messages | Working portable styles were counted as warnings | Store these as information. The HTML style carrier remains editable, scoped and retained through actual Elementor template insertion. |
| One image-title message | Titles survived only as metadata | A portable runtime restores the actual `img.title` for all 21 Home images. Captions remain disabled. JSON escaping prevents titles from escaping the script. |
| `parallax` and `parallax_method` | Background effects had no adapter | Preserve Divi's default true-parallax image height/0.3 scroll factor and its `parallax_method="off"` CSS fixed-background mode. Five exported backgrounds are configured; three are visible and two belong to intentionally hidden sections. |
| Text horizontal enable/curve | Enabled source curve was not applied | Parse and validate the seven fields, preserve viewport keypoints and three signed offsets, use 100 pixels per offset unit, and retain top/middle/bottom trigger semantics. The Home effect is inside an intentionally hidden section; that visibility remains unchanged. |
| Column horizontal curve/trigger | Six columns retain saved values with the enable switch off | Leave them inactive, consume only the handled settings, and record an informational note. Saved values alone never activate movement. |

Browser QA additionally reproduced Elementor's `margin-block-end:0` reset overriding partial negative margins. Per-side fallback styles now match that selector's specificity; service badges overlap photos by the exported 45 pixels.

The runtime is embedded in the same zero-footprint HTML widget as the styles. It uses stable classes instead of regenerated Elementor IDs, works after migrator deactivation, and reads native computed backgrounds so Media Library URL rewrites still apply. Passive scroll events share one animation frame; duplicate imports share one runtime. Lazy background class changes, element resizes, responsive flags and reduced-motion changes are handled. Reduced motion restores static backgrounds and removes displacement; fixed backgrounds use a static compact-screen fallback. Horizontal movement uses the independent CSS `translate` property to preserve the base transform.

Malformed active curves, unknown parallax methods and unsupported background combinations preserve the source shortcode and block applying an incomplete conversion. Other unsupported scroll effects remain actionable warnings. This adapter covers the observed Divi 4 fields, not every Divi motion effect. Browser support for individual CSS translate and permission to execute inline HTML-widget scripts are required. Preserve the conversion styles widget and its custom classes.

Reports now include severity counts, causes and recommended actions. Informational notes do not trigger the published-target warning acknowledgement. Unknown warnings, actual asset losses and unresolved modules retain their protection. Existing historical messages/counts are preserved; the report view classifies the exact known style-note text as information while old missing motion/title behavior remains a warning. A failed apply that converted no pages says **Conversion not applied**.

## Verification

- PHP 8.3.35 and 8.0.30: 117 existing standalone assertions plus 23 motion assertions each; 152 PHP files linted on both runtimes.
- WordPress 7.1.2 / Elementor 4.3.3 on SQLite and MariaDB 11.4.11: 119 integration, 29 production, 40 real Home, 39 recommended-settings, 14 actual template-insertion and 20 motion/diagnostic assertions per backend.
- Authenticated HTTP workflow: 24 assertions covering upload, analysis/apply, reports, template downloads, nonces and rollback.
- Edge: 32 layout assertions at 1920, 1280, 1024, 768, 767 and 390 pixels; 31 motion assertions including lazy backgrounds, scroll interpolation, tooltips, reduced motion, duplicate registration, base-transform preservation and compact fallback. Hidden effects are exercised only by temporarily revealing them in disposable browser DOM; stored page visibility is unchanged. Public Google fonts loaded in the layout run. Pixel equality is not certified.
- The original Home fixture produces **zero actionable conversion warnings**, with 12 distinct informational notes. Its 239 modules remain native, and all 26 embedded images import without source-image network requests.

The earlier browser process/sign-in blocker is resolved. Manual editor interaction, final staging visual acceptance, video playback, other exports and licensed Divi/Elementor Pro/WooCommerce integrations remain open. Public source/staging inspection was read-only; this update did not modify page 219 remotely.

Install RC.5 and re-upload the original Home export. Run **Analyze only**, compare the result, then choose **Apply to pages** for the intended target. Existing converted page data is not automatically rewritten by a plugin update. Alternatively, import the regenerated `Home.elementor.json` into an empty Elementor page; retain the conversion styles widget.
