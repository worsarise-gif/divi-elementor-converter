# Button and linked-title decoration - 1.2.0-rc.6

The reported underlines were reproduced on the public staging page. All four converted Home buttons and all twelve linked icon-box titles inherited an underline. The live Divi page used `text-decoration:none` for its links. Hello Elementor 3.5.1 added `.comments-area a, .page-content a { text-decoration:underline; }` in its [theme stylesheet](https://suiteedge-staging.mystagingwebsite.com/wp-content/themes/hello-elementor/assets/css/theme.css?ver=3.5.1). The converter had omitted explicit decoration, so valid Elementor data still inherited the destination theme's appearance. Attribute validation cannot detect every stylesheet cascade; the absence of conversion warnings did not establish visual equality.

## Repair

RC.6 stores explicit native Elementor button/title typography decoration and portable, widget-scoped CSS. Linked icon-box and image-box titles inherit their title's decoration. Ordinary paragraph and description links retain the destination theme's styling. Divi underline and line-through flags, including combined and responsive values, are preserved; malformed active flags retain the original module as a guarded fallback. The informational report records that source decoration was preserved, without treating routine styling as a conversion failure.

An additional frontend/Elementor preview stylesheet repairs templates imported with earlier versions that retain their `suiteedge-*` scope classes. It targets only converted buttons and linked box titles, uses no `!important`, and leaves native typography edits and keyboard focus outlines available. Enqueueing it does not change page content or metadata. New exports include their own portable rules and retain this styling when the migrator is deactivated.

For the current converted Home page, install and activate the RC.6 ZIP, clear the site's page/asset cache, and reload the Elementor editor and frontend. Reimporting or reconverting the existing page is unnecessary. Older templates rely on the active plugin for the compatibility stylesheet; use a new conversion if they must retain the repair after plugin removal. Templates whose conversion scope classes were manually removed need separate review.

## Verification

- 23 standalone assertions on each of PHP 8.3.35 and PHP 8.0.30.
- 29 installed WordPress assertions on each of SQLite and MariaDB 11.4.11, including registered controls, actual template-library save/insertion, generated DOM, frontend/preview enqueue hooks, and unchanged page state.
- 83 Edge browser assertions at 1920, 1024, 768, 767 and 390 pixels: the earlier Home import reproduced 16 unwanted underlines, then all 16 were repaired without reconversion. New fixtures cover ordinary links, intentional underline/strike combinations, responsive overrides, hover, active state, visible keyboard focus, unrelated widgets and subsequent native typography changes.
- The existing standalone, Home, production, settings, template and motion suites remain passing. Six-width layout and portable-motion browser checks are rerun for this release; full pixel parity and manual editor UI acceptance remain open.

WordPress 7.1.2 / Elementor 4.3.3 were used locally. Public staging used Elementor 4.3.4 / Hello Elementor 3.5.1 and was inspected read-only. No live or staging page was modified.
