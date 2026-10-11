# KERVEE branding update - 1.2.1

The product is now **KERVEE Migrator for Divi to Elementor**. The plugin header/author, WordPress sidebar, shared admin masthead, dependency notice, public documentation, translation template, and directory banner use KERVEE. Refreshed screenshots show the real plugin on the disposable KERVEE Demo WordPress installation.

The public download is `kervee-divi-to-elementor-migrator-1.2.1.zip`. Its internal plugin folder, namespaces, text domain, settings keys, and job storage identifiers retain their original names so upgrading existing installations preserves their data. Source-site detection and imported customer content retain their source values. No conversion or application behavior changed.

The package was syntax-checked under PHP 8.0.30 and 8.3.35, installed and byte-verified on the existing SQLite and MariaDB WordPress QA sites, and passed installed Plugin Check including runtime checks. Branding, navigation, responsive layout, and the saved-result workflow are checked in headless Edge; current results are in `.qa-evidence/kervee-admin-browser.json` and `.qa-evidence/kervee-admin-responsive.json`. Directory screenshots and PNG banner assets are regenerated from the running admin UI and the editable SVG.

The broader conversion QA documented in PRODUCT-RELEASE.md belongs to 1.2.0 and was not repeated in full for this branding update. This release does not claim new migration capabilities or WordPress.org approval.
