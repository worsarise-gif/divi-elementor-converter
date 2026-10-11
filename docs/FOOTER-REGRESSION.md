# Footer conversion regression - 1.2.0-rc.7

The supplied report `76699037-3a5c-4c18-9432-3816d8334893` records an Apply run with one failed item, five warnings and no converted pages. Its target ID is zero, meaning it requested a new draft. This was reproduced on RC.6 using the actual Footer.json: conversion was blocked before draft creation or page writes.

## Cause and repair

The footer logo's URL is a Divi dynamic token with `content: home_url`, `dynamic: true` and empty settings. The converter previously supported page-ID link bindings but did not recognize this site-home binding. It retained the entire image shortcode; the unresolved-shortcode guard correctly stopped Apply. The four other warnings were the contact buttons' missing `button_icon`, `button_icon_color`, `button_icon_placement` and `button_on_hover` adapters.

RC.7 resolves exact, unmodified home_url tokens in supported link attributes to the destination WordPress frontend home address, including a configured subdirectory. It does not reuse the source domain or WordPress installation URL. This follows [WordPress home_url semantics](https://developer.wordpress.org/reference/functions/home_url/). Unknown bindings, nonempty settings, mixed token/text values and unsafe URLs remain unresolved and block Apply through the existing guard. No source REST lookup is needed for the site-home binding.

Contact buttons now use native Elementor Font Awesome glyphs and physical left/right icon placement. Portable scoped CSS retains normal/responsive/hover icon colors, including the transparent spacer icon on the second address line. Icons configured for hover appear on pointer hover and keyboard focus; disabled icons remain disabled despite saved values. [Divi's button options](https://help.elegantthemes.com/en/articles/8617547-the-divi-button-module) define this visibility distinction. Missing or unsupported active glyphs and malformed active settings retain a guarded fallback instead of silently dropping the icon.

All five warnings in the supplied report are resolved for the real footer. This does not suppress media or link-resolution warnings: links to source pages still produce review warnings when corresponding destination pages are absent, and reused attachment alt differences remain reviewable. Published-page warning acknowledgement and unresolved-module/form protections remain enforced.

## Verification

- The actual source contains 52 modules: 17 containers, 12 text modules, 20 buttons, one image, one icon and one code module. Conversion retains all of them plus one portable style/effects carrier. All six actual embedded images are imported or reused without external requests. Sixteen dynamic page links are mapped from the source IDs to URLs recorded in the user's report; these are controlled test fixtures, not newly fetched source REST evidence.
- 31 standalone assertions pass on each of PHP 8.3.35 and PHP 8.0.30: destination subdirectory semantics, supported attributes, unsupported/malformed tokens, active/disabled glyphs, color validation, hover/focus and inactive responsive settings.
- 34 WordPress assertions pass on each of SQLite and MariaDB 11.4.11. They cover actual native control validation, all real contact glyphs, embedded media, analysis without writes, assigned-page Apply and rollback, new-draft Apply and rollback, blocked unsupported bindings, published-page review protection, and actual Elementor template-library insertion.
- 96 Edge assertions pass at 1920, 1280, 1024, 768, 767 and 390 pixels: destination logo link and loaded image, twenty buttons without inherited underlines, contact icon geometry/colors, telephone/email URLs, hover/focus, no overflow and no unresolved source tokens.
- Existing Home, integration, production, settings, template, motion and decoration regressions remain passing. Local testing uses WordPress 7.1.2 / Elementor 4.3.3. Manual Theme Builder display conditions and licensed integrations remain outside this acceptance scope.

No live or staging page was changed. The private export and report are excluded from the plugin ZIP.

## Retry and footer placement

Install RC.7, upload Footer.json again, run Analyze, and review the fresh report before Apply. A failed completed job is not automatically retried. Choose the intended page or a new draft. The former home_url blocker and icon warnings should be absent; resolve or review any remaining source-page/media differences.

Apply to pages writes an Elementor page, not a global Theme Builder footer. To display this footer across the site, use the converted template in an Elementor Theme Builder Footer and set its display conditions, or assign it through the site's existing footer system. The plugin does not infer or change global footer assignments. See [Elementor footer display conditions](https://elementor.com/help/how-to-fix-a-missing-header-or-footer-in-elementor/). This release repairs the content conversion and page-write failure; it does not claim automated Theme Builder assignment.
