# Get More Customers report diagnosis - 1.2.0-rc.8

Report `3bbd80fe-99ed-4fc8-a9e4-fb986c9fe91f` was an Apply job targeting published page 220. It processed one item but wrote no conversion. Its nine conversion warnings were six saved UL typography settings, an unsupported `et_pb_heading`, and the accordion's `toggle_icon` and `toggle_level`. The reported error was the published-page warning guard. The heading also created an unresolved shortcode, so acknowledging warnings alone could not make this a complete migration.

## Repair

- Standalone Divi 4 Heading modules become native Elementor Heading widgets. Title, H1-H6 semantics, source/default family, weight, size, spacing, alignment, color and responsive sizes are preserved. Supplemental responsive colors and source heading spacing remain portable. Invalid or unimplemented active settings remain guarded or reported.
- UL and OL typography uses separate, element-scoped CSS. Font flags, colors, size, line height, spacing, alignment, tablet/mobile values and active hover are supported. Inactive saved values remain inactive. The three actual modules use checkmarked paragraphs, so their UL settings must not restyle those paragraphs. Synthetic real lists also test the active styling path. External font availability still needs review.
- All five FAQ items become a native accordion. Explicit child H3 levels override the parent's H2 default. Child presets are resolved before mapping. The actual first-open/remaining-closed state matches native behavior. Unknown item styling, nested content, dynamic bindings, mixed levels, unsupported initial states and malformed icons preserve the whole parent as a blocking fallback.
- The observed legacy `;||divi||400` icon uses a native Font Awesome chevron-circle-down equivalent, on the right and hidden when open. This is a bounded shape adaptation, not an exact reproduction of the ETModules outline. Other legacy icons are not guessed.
- Native FAQ title/body fonts, colors, spacing and open/closed surfaces are explicit. A destination theme's heading line box cannot enlarge the native title anchor's line box. Native title typography remains editable.
- Source defaults now distinguish the inherited Inter headings/dark heading color from Home's explicit Montserrat settings. Existing explicit module fonts and colors win. The mapping form exposes inherited heading color, and older saved profiles receive its validated default.
- Apply reports unresolved shortcode/form blockers before warning acknowledgement. Neither guard is weakened. Known embedded-image omissions are reported before published-page protection instead of after a page write.

## Verification

The exact export has 61 source instances, including five consumed accordion children, seven embedded images, and 57 serialized native nodes including the style carrier. All nine reported conversion warnings are resolved with an explicit source page-link map. No source JSON or original report was modified.

| Check | Result |
|---|---|
| Standalone customers regression | 44 assertions on PHP 8.3.35 and 8.0.30 |
| Real WordPress export | 39 assertions on SQLite and MariaDB 11.4.11, WordPress 7.1.2 / Elementor 4.3.3 |
| Apply / rollback | Assigned draft, reviewed published page, warning-free native published FAQ and new draft |
| Protection | Analysis leaves page untouched; unreviewed published warnings, unsupported modules and missing image import block writes; acknowledgement cannot bypass unsupported modules |
| Template insertion | Actual Elementor library insertion regenerates IDs; styles remain scoped correctly |
| Edge browser | 115 assertions at 1920, 1280, 1024, 768, 767 and 390 pixels |
| Portability | 20 browser assertions at 390 pixels with the migrator deactivated |
| Existing conversions | Home 40 and Footer 34 WordPress assertions per backend; Home 32 and Footer 96 browser assertions |

Browser checks cover native title semantics, verified heading/FAQ font and color declarations, open/closed surfaces, pointer/Enter/Space interaction, responsive heading/list styles, independent paragraphs/ordered lists, all six local module images including lazy loading, overflow and JavaScript errors. Desktop/mobile FAQ screenshots were inspected. Exact pixel equality and manual Elementor editor acceptance are not claimed.

Media and link review warnings remain separate from conversion support. In the isolated test site, reused attachment alt differences and a missing local Get Started page correctly generate review warnings. The migrator preserves the source URL instead of inventing a destination. The tests verify that published targets remain unchanged until those differences are explicitly reviewed.

## Installing and rerunning

1. Install `suiteedge-divi-elementor-migrator-1.2.0-rc.8.zip` on staging and clear page/plugin caches.
2. Re-upload Get More Customers.json. Select the intended page, source origin and recommended settings. An old failed job is historical and is not rewritten by upgrading.
3. Run Analyze. Review any remaining media, source-link or unsupported-setting warnings.
4. Click Apply to pages. For a published target with remaining warnings, acknowledge reviewed differences only after reviewing them. Unresolved shortcodes still block Apply.
5. Check the result in Elementor and on mobile. Rollback remains available for plugin-owned page changes.

No live or staging page was changed during this repair. Global header/footer assignment, arbitrary modules, unsupported motion and licensed Divi/Elementor Pro/WooCommerce integrations remain outside this acceptance scope; see TODO.md.

## References

The implementation was checked against the installed official Elementor Heading/Accordion widget controls and frontend handlers. Primary documentation: [Divi Heading module](https://help.elegantthemes.com/en/articles/9552416-the-divi-heading-module), [Elementor Heading widget](https://elementor.com/help/heading-widget/), [Elementor Accordion source](https://github.com/elementor/elementor/blob/main/includes/widgets/accordion.php), and [Elegant Themes icon Unicode guide](https://www.elegantthemes.com/blog/resources/elegant-icon-font). Inherited FAQ fonts/colors were read from the [public source page](https://suiteedge.com/get-more-customers/) on October 6, 2026; they are editable recommendations because portability JSON omits Theme Customizer settings.
