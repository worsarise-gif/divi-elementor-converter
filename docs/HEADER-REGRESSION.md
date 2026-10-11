# Header conversion repair - 1.2.0-rc.10

The supplied report `99af704d-6290-47a1-850c-137d216e5019` did not apply anything to target page 235. Both `et_pb_menu` modules were preserved as Divi shortcodes: one was unsupported, and the mobile variant's `logo_url` homepage binding was unresolved. The writer then correctly blocked the unresolved output. Its six informational messages were not failures.

`Header.json` contains desktop and mobile references to source menu **32**, but no menu links or hierarchy. Source menu numbers cannot identify a menu on another WordPress database. The user's selected destination is **Main Menu**. RC.10 converts the modules using Elementor's native WordPress Navigation Menu widget (`wp-widget-nav_menu`) and an explicit, uploaded-file-specific destination mapping. It reads the destination menu live; it never creates or overwrites its links. A unique existing menu named Main Menu is visibly suggested for a single-menu Header export. Other exports require a selection.

## Install and reconvert

1. Install RC.10 and re-upload the original Header.json. The old job contains unresolved output and must be analyzed again through a fresh conversion.
2. On Page Mapping, choose the intended page/draft and select **Main Menu** under **Navigation menu mappings**. The menu must already contain valid links in Appearance > Menus.
3. Review source origin, images and the separate dynamic “Let's Talk” page link. Its source page ID is 52164. Existing opt-in source REST resolution remains available; if its destination is missing, supply a deliberate URL mapping or resolve the reported warning. The header menu mapping does not invent that page URL.
4. Run **Convert Selected / download templates**, review the report, then use **Apply to Page** on the saved result. Published targets with actual media/link warnings still require review. Importing the downloaded template through Elementor also preserves the navigation widget and portable styles.
5. To use this as a global header, insert it into an Elementor Theme Builder Header and configure its display conditions. Applying to an ordinary page does not assign a global header. These licensed Theme Builder conditions were not changed or tested on the user's staging site.

## Fidelity and safeguards

The observed 16px/18px/14.5px menu typography, normal/hover/active colors, submenu backgrounds, border colors, 15px radius, desktop 400px dropdown width, responsive automatic width and 48px menu icon size are translated. Responsive widths override a desktop `!important` width. The mobile logo remains a native image capped at the source's 275px width and links to the destination homepage. The source logo margin and native image assets survive real template insertion.

The menu module preserves horizontal margins without inheriting Elementor's 100% container width. Button stacking and custom CSS apply to the actual button anchor, matching Divi's button selector, so a full-width wrapper cannot block menu links or move the login button out of alignment. Logo/menu sizing avoids phone overflow. Desktop nested dropdowns reposition at the viewport edge. Portable controls provide keyboard/touch disclosure, synchronized expanded states, Escape/outside-click dismissal, unique control IDs and the Divi menu's 980px hamburger breakpoint. Narrow-phone native links remain available without JavaScript.

Missing, empty, deleted, malformed or cyclic destination menus block application. Saved output revalidates its destination menu before writes. Mappings cannot leak between uploaded source files; malformed source IDs cannot be coerced into an existing ID. Search/cart-enabled menus and unsupported active menu layouts remain whole-module fallbacks with warnings rather than silently losing those controls.

## Verified scope

See current `test-evidence.json` for counts and runtime versions. Real Header export tests on SQLite and MariaDB cover native widget controls, explicit mapping, no analysis writes, assigned-page saved Apply, exact rollback, unchanged menu links, invalid/deleted/empty/cyclic menus, malformed CSS/IDs, and actual Elementor library save/insertion. Edge checks cover eight widths from 320px to 1920px, native destination links, desktop/nested dropdowns, keyboard/mobile state, local logos, no horizontal overflow, the authenticated upload/mapping/Apply workflow, a disabled migrator, and an older imported motion runtime loading first.

QA uses a controlled destination Main Menu with two top-level links and a nested hierarchy. It does not establish pixel parity with the user's full staging menu. Header container visibility and responsive typography use destination Elementor breakpoints (1024px/767px in QA); the hamburger changes at Divi's 980px. Custom breakpoints, all actual staging links, manual editor persistence, theme stacking/CSP and global Theme Builder assignments remain acceptance items. The native WordPress widget has a different editor panel from Elementor Pro's branded menu widget. Mega menus, search/cart controls, all Divi menu variants and multisite are not certified by this fixture.
