# Learning Center grid repair - 1.2.5

Report `4d1353dc-c54d-4eb2-818d-3af3ed04d7d2` contains one failed item, target page 233. Its source checksum matches the supplied Learning Center export. It is an Apply job with a valid source origin, link lookup disabled and no module replacements. Link lookup is unrelated here: there are no unresolved page-ID bindings. The only blocking node is the preserved `dgbm_blog_module` shortcode. The report's other entries describe retained portable styles or inactive parallax settings; they are not conversion errors.

## Root cause and source evidence

The previous converter did not register a DiviGear grid adapter. It deliberately preserved the shortcode and blocked Apply, so a source URL could not repair this page. A manual saved-template override was available in 1.2.4, but this job did not select one. Suppressing the fallback guard would produce a page still dependent on DiviGear rather than an Elementor conversion.

The export supplies the grid's 40px spacing, equal-height switch, image hover overlay, hidden author/excerpt, metadata icons, full-width orange read-more button, typography, separate image/card radii and image borders. The live public Learning Center grid confirms omitted standard values: 12 recent posts, three/two/one columns, images/date/categories and older/newer pagination. Its default metadata position is inside the content. Saved top/bottom metadata padding and hover-shadow values without a shadow preset are inactive. Disabled hover transforms must not become active effects.

Source references: [Learning Center](https://suiteedge.com/learning-center/) and [DiviGear's module documentation](https://www.divigear.com/docs/blog-module-overview/). Public source markup/CSS were inspected read-only; vendor code is not included in the release. The widget and SVG metadata primitives are original implementation. Detailed local evidence is private and excluded from the ZIP.

## Implementation

- Register `dgbm_blog_module` with a bounded conventional-grid adapter and register an editable `ace-post-grid` Elementor widget on frontend/editor/CLI requests. The widget needs Ace Migrator active; DiviGear, Pro and UAE are not required.
- Map per-card backgrounds, typography, button styling, padding/margins and distinct card/image borders into the widget controls. Do not put those properties on the outer widget wrapper. Empty Divi spacing sides inherit valid element defaults. Explicit zeros and normalized inactive states are retained.
- Preserve source responsive columns, gap, spacing, font family/weight/style/decoration, colors and borders. Compile source 980px/767px breakpoint values without changing the destination kit. All font families, including responsive-only families, receive availability checks/loading carriers. Custom files still require destination registration.
- Query published destination posts only, with bounded counts/offsets, deterministic recent ordering, category-slug filters and safe empty-state output. Offset is included in each page's starting position and subtracted from the total page count.
- Use widget-specific read-only pagination arguments instead of assuming `/page/N` routes. Do not change the global post loop. Disable Elementor element caching for dynamic content, so the next page and later posts render current results.
- Keep explicit native-template overrides for the same module type. Unsupported layout/query combinations and malformed values retain the original shortcode and remain blocked. Other extension modules are not automatically claimed supported. Reports disclose the runtime dependency and missing destination posts.

## Validation and remaining acceptance

The exact supplied source now analyzes, applies saved output to its assigned draft and rolls back on both disposable database backends. The five original exports apply without a grid replacement. Native template insertion regenerates IDs and keeps valid controls. Query tests verify published-only category filtering, offset-aware pagination, malformed arguments, empty results and no global-loop mutation. Unsupported source category IDs cannot overwrite the target. Browser evidence checks eight widths, source typography/spacing/borders/button styling, loaded destination media, card hover and both pagination directions.

See `fix125-release.json`, `fix125-standalone.json`, `fix125-tests-{sqlite,mariadb}.json`, `fix125-browser.json`, installed Plugin Check and package equality evidence for final counts. Minimum-runtime, licensed provider, multisite/MySQL8, full source-post equality and the user's staging acceptance remain open. No staging page was changed.

## On staging

1. Install the 1.2.5 update. Leave Ace Migrator active for Ace Post Grid.
2. Upload Learning Center.json again and select the correct destination page. Leave **Convert automatically with Ace Post Grid** selected unless you deliberately want a native-template override.
3. Convert for review. Check the destination post query and device layouts in Elementor; make sure its blog posts and featured images exist.
4. Apply the new saved output. Old failed jobs and previously downloaded templates are not rewritten by an update. Verify page-two navigation and the actual source design before publishing.

Page JSON contains no blog-post database. An empty destination grid needs posts migrated/published separately. Automatic scope is the standard grid; masonry/full-width, related/custom-type and numeric source category-ID queries need an explicitly configured native template replacement. Unknown settings remain visible in reports. The adapter does not promise fidelity for every DiviGear version or site customization.
