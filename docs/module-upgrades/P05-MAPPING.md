# Bar Counters mapping evidence

Implementation scope: Divi 4 `et_pb_counters` and its `et_pb_counter` children to an editable Free container with one native Elementor Progress Bar per source item. Circle Counter remains a separate, unaccepted upgrade.

Source evidence:

- [Official brokerage preset style guide](https://www.elegantthemes.com/blog/divi-resources/download-a-free-global-presets-style-guide-for-divis-brokerage-firm-layout-pack): downloaded public portability archive; parent 4.8.2, six children 4.8.1. The parent preset supplies fill/track colors, PT Sans title and Work Sans percentage typography. Child default metadata must not overwrite an explicit parent preset.
- [Official image-toggle tutorial](https://www.elegantthemes.com/blog/divi-resources/how-to-toggle-between-images-in-a-stunning-divi-split-section-design): legacy 3.29.1 reference, eight children, hidden percentages, individual `height=1.5vw`, responsive title sizes and parent width. This supplements the Divi 4 fixture; it is not described as a Divi 4 export.
- [Divi Bar Counters reference](https://help.elegantthemes.com/en/articles/8616758-the-divi-bar-counters-module): title and percentage are separate surfaces, percentage visibility is configurable, and items have independent values.

Destination evidence: installed WordPress 7.1.2, Elementor Free 4.3.3; full admin control stacks and source/render templates captured privately. `progress` has `percent` (% slider), `title`, `title_tag`, `title_display`, `display_percentage` (`show` or empty), `inner_text`, `bar_color` (fill), `bar_bg_color` (track), `bar_height`, title `typography_*`. Its inner typography controls require nonempty inner text, so percentage-only text must use scoped CSS and native font carriers. `bar_height` is nonresponsive and does not offer vw; responsive/vw source heights require scoped surface CSS. A zero-value bar with empty title is omitted by Elementor, so an accessible hidden title is required for that case.

| Source | Destination / rule |
| --- | --- |
| Child order and shortcode body | Ordered native progress widgets; editable `title` |
| `percent` | Finite decimal 0 through 100; preserve zero and fractional values; invalid values retain the complete original parent |
| `use_percentages` | `display_percentage`; child explicit state wins over parent, inactive states removed first |
| Parent `bar_bg_color` | Child `bar_color` fill; verified inherited child `bar_background_color_default` is used only when no explicit fill is available. Other child fill schemas require their own fixture acceptance. |
| Parent/child solid `background_color` | `bar_bg_color` track, never painted across the parent container or title |
| Child `*_default` | Fallback only, after child explicit and parent explicit values |
| `title_*`, `percent_*` | Independent title/native typography and scoped percentage typography; source device and hover states preserved |
| Child `height` | Native height for supported desktop units; scoped track/fill height for all devices, including vw |
| Parent geometry/spacing/ID/classes | Outer container; child geometry/spacing/ID/classes remain per item |
| Radius | Track surface; never copied as a border around the whole collection |
| Presets | Resolve both parent and every child before conversion; unresolved child preset retains complete parent |

Boundaries: nested modules, invalid percentages, unaccepted background image/video/gradient/mask/pattern, surface CSS/links and unrecognized child settings retain the complete source module and block Apply. Default bar geometry/animation is an adaptation to native Progress Bar, reported for visual review; a live Divi 4 default-state comparison is still required before claiming exact parity. No provider, network request or source script runs during analysis.

Acceptance evidence and source hashes are recorded with the implementation tests. Native editor controls/template persistence can be tested locally; interaction with a user's licensed Divi editor remains unverified.
