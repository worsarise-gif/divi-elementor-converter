# Ace font migration update - 1.2.2

The header font mismatch came from storing the correct family in scoped menu CSS without a native Elementor font control to load its files. Divi global font references also needed to resolve through the verified source theme defaults for menus and buttons.

The converter now adds hidden native font-loading widgets for families used in scoped CSS. Elementor handles Google/early-access fonts and registered custom providers, respects its Google-font loading setting, and retains loading controls after library insertion or migrator deactivation. Font names permit punctuation and non-Latin characters; responsive families, hover states, and variable weights are preserved where mapped. Unavailable registrations and disabled loading produce actionable warnings. Missing custom font binaries still require installation on the destination.

The product name and author are **Ace**. The admin uses the publisher-supplied `assets/icons/logo.png`; its source image is retained unchanged. Listing icons, banner, and screenshots are refreshed. The WordPress.org contributor remains the previously supplied account KERVEE. Internal folder, text domain, namespaces, and data keys retain their original names to preserve upgrades and stored migration data.

## Verification

* PHP syntax: 183 files on PHP 8.0.30 and 8.3.35.
* Standalone: 350 assertions plus the pipeline on each PHP runtime, including 50 new font assertions.
* WordPress: 14 suites on both SQLite and MariaDB, including the real Home, Header, Footer, FAQ, saved Apply/rollback, UAE, and library-insertion checks.
* Font catalogue: all 1,667 installed Elementor family names pass preservation and menu translation checks; the real Header queues Inter, Kaushan Script, and Montserrat after library insertion. A registered custom-provider loading hook is verified, and disabled Google loading is preserved and diagnosed.
* Browser: the three real Header fonts are loaded at 1440, 800, and 390 pixels with the migrator active and inactive. Tests intercept font responses using real official Latin font binaries for reproducibility; they do not verify remote-service availability or every catalogue font's glyphs on every operating system.
* Final package: source/archive/installed bytes match on both sites; installed Plugin Check including runtime checks and Ace admin/logo checks are recorded separately under `.qa-evidence/ace-*`.

The full licensed Elementor Pro font provider and the user's staging page are not runtime-verified in this local environment. Update the plugin, reconvert affected headers, review font warnings, apply the new saved output, and clear/regenerate CSS and page/CDN caches. Compare dropdowns and device typography before publishing.
