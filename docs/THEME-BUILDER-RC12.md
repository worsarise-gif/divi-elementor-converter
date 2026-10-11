# Separate UAE Header / Footer migration - RC.12

The missing targets were a post-type issue: the previous migrator accepted WordPress `page` posts, while UAE stores its templates as `elementor-hf`, with `type_header` or `type_footer` identity. A downloaded page template does not establish a site's Header/Footer assignment.

The sample Header references source menu 32 but includes no menu items or image decorations. Public source navigation contains twelve image icons inside dropdown link labels. Those decorations were omitted when the destination native Main Menu rendered plain labels. The destination screenshot also contains Home and Learning Center, differing from the source's six top links and Learn label. These are destination menu contents; conversion does not overwrite a shared menu to conceal those differences.

## Workflow

1. Install RC.12 on staging with Elementor and UAE active. Open Divi Migrator > Header & Footer.
2. Upload separate Divi Theme Builder Header/Footer layout exports. A normal page export does not contain global templates.
3. Choose Map Header / Footer templates. Select each existing UAE template or create a new draft of the correct Header/Footer type. Templates have their own destination dropdown. Unchecked layouts need no target; selected layouts require an explicit choice.
4. For Header.json, map source menu 32 to the existing destination Main Menu. Review its labels, parent relationships and URLs in Appearance > Menus. Remove Home or rename Learning Center to Learn there if the source's navigation is desired.
5. Leave source image-icon lookup enabled with source origin https://suiteedge.com, plus image import/reuse. The lookup reads public Divi header/footer navigation from the source homepage. It is recommended by default in this dedicated workflow when a source origin is inferred, and can be disabled. An ordinary page import does not enable it automatically.
6. Analyze, inspect the report, then Apply to UAE Template or bulk Apply Selected to Pages / Templates. Saved Apply uses the completed output and original target on the same job. Immediate Apply to UAE templates is also available. Published targets with warnings require reviewed-differences acknowledgement; unsupported shortcode/form output remains blocked.
7. Existing UAE conditions, exclusions, user roles, canvas setting, title, status and template settings are preserved. New templates remain drafts with no site-wide assignment. Set their display rules and publish through UAE when ready. Divi display conditions are not included in layout JSON and are not inferred.
8. Review the Header/Footer on the actual staging site. Clearing jobs does not change templates. Rollback restores only migration-owned content/meta and refuses newer content or changed UAE assignments. New drafts and imported media are retained.

Create fresh jobs for the new adapters. Updating the plugin does not rewrite historical saved conversion outputs or reassign previously converted page content to a UAE template.

## Conversion and guards

- Explicit provider/type targets prevent page/Header/Footer mixups. Both individual and bulk saved Apply keep the original target and output hash. UAE identity and display-rule changes after analysis protect newer assignments.
- Native WordPress navigation keeps destination links, labels and hierarchy editable. Source image decorations are matched by URL path and parent labels, then attached to destination item IDs in scoped portable CSS. No menu term or item is created, renamed or overwritten by conversion.
- Safe public WordPress HTTP APIs, a 2 MB response limit, DOM parsing without network access, at most 16 candidate menus, 1000 items and 30 hierarchy levels bound source lookup. Duplicate desktop/mobile copies are deduplicated; ambiguous distinct menus require review. Icon URLs must be safe and share the source origin.
- The actual twelve PNGs import/reuse locally, once per URL across desktop/mobile variants. The carrier stylesheet is updated to the local URLs. Icons and submenu CSS survive migrator deactivation and disabled JavaScript.
- The dedicated reference-site Header/Footer recommendation uses the inherited Inter family observed in public Header CSS; the existing page profile remains separate. Explicit module fonts and editable source defaults retain priority. Source submenu padding is 6px 20px with outer 20px item padding, inherited line height, a 400px rounded desktop dropdown in this Header and source arrow space. Mobile controls preserve viewport bounds. Native focus/hover submenu access remains available without JavaScript.
- Changed menu labels, URLs, hierarchy/order or identity after icon analysis block stale saved Apply. The applied menu remains live WordPress content; later deliberate edits are not overwritten.
- Failed source lookup, extra/missing menu items, label differences, unimported remote icons, ambiguous navigation, multiple images and unsupported foreign/SVG/font icon decorations are actionable warnings. The optional lookup failing retains valid native navigation.

## Verification and limits

Tested with the supplied Header JSON (SHA256 `7b7af6399daee1075efc425de9543317f800afc4cb5e96644cd994651cf39050`), real Footer JSON, cached public source markup and the twelve actual image assets, on disposable WordPress 7.1.2 / Elementor 4.3.3 / free UAE 2.9.5 sites using SQLite and MariaDB. Controlled HTTP fixtures exercise actual WordPress import APIs without depending on external availability. The browser checks exercise UAE's actual assignment/rendering, desktop/mobile dropdowns, local icons, subset uploads, same-job Apply and disabled-JavaScript/inactive-migrator operation. See test-evidence.json for final counts and the full regression suites.

This is a bounded single safe image-decoration adapter, not a general mega-menu or custom theme CSS importer. It uses the source homepage; a conditional header absent there cannot be reconstructed by that lookup. SVG/font icons, unrelated providers, custom mega menus and arbitrary global CSS need manual work or additional adapters. Image boxes follow the observed square-icon pattern; other aspect ratios/custom image CSS need visual review. Source/destination menu labels and hierarchy must be reviewed independently.

The actual staging site has not been changed or accepted visually. Licensed Elementor Pro/provider features remain outside the recorded QA scope. Existing UAE rules decide where a converted template appears; applying its content does not repair an unrelated site's display-rule configuration.
