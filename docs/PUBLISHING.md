# Publishing on WordPress.org

This workspace prepares an installable 1.4.0 package and directory assets. It does not submit, publish, or claim approval from WordPress.org.

The contributor is **KERVEE**, as provided by the publisher. The plugin uses the Ace author/brand and GPL-2.0-or-later license. Confirm the WordPress.org account belongs to you and that you may distribute the plugin under this brand/license.

## Package and listing

The ZIP has one top-level directory, `suiteedge-divi-elementor-migrator`, with the runtime code, local frontend/admin assets, translation template, GPL license, directory `readme.txt`, and public user/compatibility documentation. Tests, private exports, QA sites, reports, credentials, and internal evidence are excluded. Build through `tools/build-release.py`; keep versions identical in the header, version constant, and readme Stable tag.

The display name is **Ace Migrator for Divi to Elementor**. The current package keeps its original internal folder and text domain so existing installations update correctly. The public download is named `ace-divi-to-elementor-migrator-1.4.0.zip`. If WordPress.org assigns a different slug, align the package folder and text domain with that assigned slug before the first directory release; preserve or migrate existing settings and job keys explicitly. Do not claim affiliation. The publisher-supplied migration logo and refreshed listing screenshot assets are supplied separately in `wordpress-directory-assets`.

The readme describes installation, conversion/application differences, UAE templates, rollback, privacy, dependencies, and accepted limitations. Five descriptive tags are used. No unsupported claim of pixel-perfect or universal conversion is made. `Tested up to: 7.1` reflects the disposable WordPress 7.1.2 integration environment, not a claim of every WordPress/PHP/Elementor combination.

## Required acceptance before your public release

1. Install the final ZIP on your own staging site and complete a real page, Header, and Footer migration. Compare desktop/tablet/mobile and test every interaction. Local QA is not acceptance on your staging environment.
2. Confirm support scope. Provider/mail integrations and Elementor Pro acceptance are incomplete and explicitly excluded from release claims. Test them before advertising support for those integrations.
3. Review final Plugin Check output and the documented context annotations in PRODUCT-RELEASE.md. Run `wp plugin check` on the actual packaged plugin, including runtime checks with `--require=.../plugin-check/cli.php`. Passing automated checks does not guarantee approval.
4. Confirm the account, author/brand, third-party asset permissions, and GPL redistribution rights. User source media and private client exports are not bundled.
5. Upload the final ZIP while signed in to the official [Add Your Plugin](https://wordpress.org/plugins/developers/add/) form. Use a working developer contact attached to your account. Respond to reviewer feedback before public release.
6. After approval, use the assigned slug/SVN repository. Place runtime files in `trunk` and `tags/1.4.0`; put directory screenshots/icon/banner in top-level `assets`, outside the plugin. Keep `Stable tag: 1.4.0` in both matching readmes.
7. Review the listing and readme validator. Verify installation from the directory and links from the plugin list. Create the tagged release only after your acceptance checks pass.
8. Maintain the plugin: reproduce support reports with sanitized examples, release fixes with increasing versions, and update the tested WordPress version only after verification.

No automated updater, license activation, telemetry, onboarding redirect, vendor service, or dashboard advertising was added. Optional HTTP requests go to administrator-selected source/asset hosts; their purposes are disclosed before conversion and in the readme. Deletion retains private migration records intentionally; document this in support responses. Retention does not erase protected backups.

## Official references

* [Detailed Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)
* [Plugin Readmes](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/)
* [Header Requirements](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/)
* [Plugin Check](https://wordpress.org/plugins/plugin-check/)
* [Plugin directory assets](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/)

Instructions were checked against the official WordPress documentation on 2026-10-07. Directory review policies can change; check them again before submission.
