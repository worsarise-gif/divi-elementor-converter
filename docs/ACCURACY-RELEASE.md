# Conversion accuracy update - 1.2.3

The report `4693f408-abe7-43cd-9fb1-d891d943a5d6` contained five layouts: four applied and Learning Center failed. Its 11 warnings included five lost mappings, five career-page links without destination matches, and the Learning Center fallback. The two supplied team screenshots used different widths/scales, so geometry was checked against the live source at matching browser widths instead of comparing screenshot pixels directly.

## Root causes and changes

| Area | Cause | Correction / remaining scope |
| --- | --- | --- |
| Portrait shadows | Divi shadows belong to its compact image surface; the serializer put them on the full module wrapper. | Use Elementor's image shadow and image border groups. Module width, margin, padding, and background stay separate. |
| Stephen portrait / hidden text | `height="100px"` constrained the module wrapper while the image retained its larger intrinsic dimensions. | Set native image height and preserve `width:auto` aspect ratio. Negative bottom margin retains the original overlap. |
| Body font and color | Blurbs declare `body_font` / `body_text_color`; only the legacy `text_*` aliases were applied. | Preserve body families, size, color, line height, letter spacing, and responsive choices with explicit values taking precedence. |
| Invalid description HTML | Elementor encloses the box description in a paragraph. Passing a Divi paragraph inside it caused browser repair to move text outside its styled element. | Normalize single/multiple paragraphs into valid phrasing content, preserving inline markup and attributes. Complex incompatible blocks remain guarded. |
| Title spacing | Native box margins differed from Divi's ten-pixel heading padding. | Preserve Divi heading semantics and spacing separately from its explicit line height. |
| Card width / wrapped names | The saved report used a 1080px implicit row limit. The live source uses 1280px. | Mixed uploads retain recommendations from one established style origin without inventing provenance for image-free exports. Origin edits refresh untouched defaults; custom edits persist. Reports include effective profiles. |
| Inherited body weight | Empty packed font-weight fields inherited 500 on the source but were forced to 400. | Add editable body-weight defaults and resolve omitted weights before typography mapping. Explicit weights remain authoritative. |
| Tablet / mobile grid | Elementor stacks at a different breakpoint. Divi uses a two-column compact grid for four/five/six equal columns and hides empty spacer columns below 980px. | Preserve source grid transitions, gutters, actual spacer structure, and explicit responsive column widths in portable scoped CSS. |
| Auto-aligned final columns | Divi resets the last column's bottom margin on desktop and the final compact group on tablet. Elementor's two auto margins divided the available vertical space differently. | Preserve those breakpoint-specific defaults while retaining explicit builder margins. No page-specific offsets are added. |
| Vertical grid spacing | A fixed row gap added space even when source column margins removed it. | Preserve per-column margins and gutter priority instead of adding a second gap. |
| Responsive native controls | Elementor activated tablet controls at 1024px; the source activated them at 980px. | Compile the installed native control definitions into portable source-breakpoint CSS. Keep editable controls and the destination kit unchanged. Stored CSS must also be updated after manual responsive edits. |
| Heading label and careers button | Text inherited the body's computed line height on the source; the converter recomputed it from local font size. Elementor buttons defaulted to a shorter line height. | Preserve inherited pixel line height for text and the Divi button surface's 1.7em default; explicit values retain precedence. |
| Skewed label | Transform values were stored without enabling Elementor's conditional transform group. | Activate the corresponding native group so its CSS and class are emitted, including responsive transforms. |
| Careers separators | Directional top width/color lacked a mapper. | Add validated per-side width/style/color, responsive values and hover-side declarations. |
| Inactive settings | Saved animation direction / parallax survived after their effect was disabled or the image was removed. | Ignore inactive residue; informational diagnostics explain inactive parallax. Active invalid/mixed effects remain guarded. |
| Link diagnostics | The word “media” in a career-page URL incorrectly selected image troubleshooting. | Classify unresolved internal links explicitly and retain their source URLs until the destination resolves. |
| Learning Center | Ignoring inactive parallax reveals a `dgbm_blog_module` third-party dynamic grid. | Identify it in parsing, Module Coverage and actionable diagnostics. There is no automatic grid adapter; Apply stays blocked rather than losing the dynamic blog. |

## Verification and limits

Private source exports, browser evidence, and all source-to-package comparisons are stored under `.qa-evidence/accuracy-*`; none are distributed with the plugin. The installed distribution is tested on both disposable SQLite and MariaDB sites. Tests exercise actual Elementor controls, exact-target application, the partial five-layout batch, library ID regeneration, and rollback.

Release checks pass 399 standalone assertions plus the pipeline on each of PHP 8.0.30 and 8.3.35. Each database backend passes 3,926 regression assertions and 45 actual-export assertions (3,971 total). The installed final ZIP passes WordPress Plugin Check including runtime with no findings. The package contains 169 files; syntax checks pass for 188 development PHP files on both runtimes. These counts describe the recorded test scope, not a guarantee for untested modules or environments.

The final admin browser check passes 283 assertions: eight screens at 1440/390/320px, source-profile recommendations and explicit edits, conversion/report/template download, same-job saved Apply, clear/restore history, and keyboard/no-JavaScript guide navigation. No document overflow or JavaScript errors were recorded. The published directory screenshots use disposable sample content.

The updated inventories rank 92 feature areas (62 PASS, 20 PARTIAL, four UNVERIFIED, five UNAVAILABLE, one REVIEWED) and list 307 method/class rows. Method scores inherit their feature scope; this is not individual-method coverage. See FEATURE-QA.md, FUNCTION-QA.md and TODO.md for the remaining items.

The source team section is measured at 1920, 1440, 1024, 800, and 390px. The converted section is tested at ten widths, including 981/980 and 768/767 transitions and 320px. Browser tests use real official Latin Google Font files with the full variable weight range and locally intercepted font responses; they do not verify remote-service uptime or every font glyph. Active and deactivated-plugin runs each pass 1,116 assertions, checking all fifteen portrait/card dimensions, positions, relative row spacing, typography, image loading, shadows, headings, the skewed label and careers button. Source custom line heights can still wrap at narrow desktop widths; the converter retains those valid values.

The supported Get Started, Careers, Why Suite Edge and Reviews layouts apply and roll back. Learning Center's third-party grid remains an explicit blocker. Career detail pages must exist with matching slugs or mappings before their five source-link warnings clear. Testing does not modify the user's staging or production site and does not replace licensed Pro/provider acceptance.

## Use the update

Install 1.2.3, upload the original exports again, verify the source origin and theme defaults, and create a fresh conversion. For this source use the measured 1280px row maximum, 18px body size and 500 body weight unless its theme settings have changed. Review the report, then apply the new saved output to the correct targets. Existing saved JSON keeps its old styles. Regenerate Elementor CSS and clear page/CDN caches before the final staging comparison.

Rebuild the Learning Center blog grid with an Elementor Posts/Loop Grid or compatible post-grid widget and configure its live query, pagination and styling before replacing the original page. Do not use a warning acknowledgement to bypass the source-content guard.
