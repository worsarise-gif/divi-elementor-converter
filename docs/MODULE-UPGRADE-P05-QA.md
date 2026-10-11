# Bar Counters upgrade QA - 1.4.0

The source-verified subset is **PARTIAL**, scored 3/5. This release adds the actual Divi 4 parent and child tags, not a general guarantee for every Bar Counters configuration or for the separate Circle Counter module. See [mapping evidence](module-upgrades/P05-MAPPING.md).

Evidence gathered before implementation: public official brokerage portability export (parent 4.8.2, six children 4.8.1, normalized parent preset), eight legacy 3.29.1 responsive bars, official module documentation, and the installed Elementor Free 4.3.3 control stacks and PHP/editor rendering templates. Original public export hashes and target controls are recorded privately, with a sanitized manifest alongside final QA evidence.

Validated behavior includes item order, zero/fractional/full values, child and parent preset resolution, separate title/percentage typography, empty/inactive device inheritance, track/fill routing, native font controls/carriers, per-item sizing/spacing and responsive percentage display. Invalid values, missing presets, unknown critical surface settings and unsupported nested/background/link states retain the complete original collection. Native template insertion regenerates IDs while portable styling survives. Saved bulk Apply uses the corresponding existing output; exact rollback restores each original target.

Local checks use PHP 8.0.30/8.3.35, WordPress 7.1.2, Elementor 4.3.3, SQLite and MariaDB 11.4.11. Destination browser checks cover 320, 390, 767, 768, 980, 981, 1440 and 1920 pixels, semantic values, track heights, fractional fill geometry, typography, visibility, overflow and reduced-motion presentation. Final numeric totals and packaged-install results are recorded in `MODULE-UPGRADE-P05-EVIDENCE.json` when verification finishes.

Limitations: default 22px geometry and native fill timing are explicit adaptations. Source-versus-destination visual comparison on a licensed Divi 4 runtime remains open. Original theme/custom font binaries are not imported by a layout. Full default/custom/hover mode acceptance, actual editor interaction and publisher staging acceptance are not assumed from schema or template tests. The first local interactive editor navigation timed out; no successful editor acceptance is claimed unless the final evidence records it. A vendor Elementor API deprecation occurred on one isolated MariaDB run after its request returned false; plugin assertions passed, and vendor code was left unchanged.

The remaining inventory has 49 missing Divi 4 module/variant additions. Other families require their own actual source fixtures and destination capability/provider checks. No Pro, WooCommerce, subscription/mail provider or Divi 5 acceptance was added by this batch.

## Final local verification

- 214 PHP files linted on PHP 8.0.30 and 8.3.35.
- 706 standalone assertions plus pipeline on each PHP runtime.
- 23 WordPress suites: 4,331 assertions on each database backend.
- Exact final archive installed and byte-verified on both backends; 31 bar-specific workflow assertions repeated on each installed archive.
- 114 browser assertions with Ace active, repeated with Ace inactive; plugin activation restored.
- Installed Plugin Check including runtime: no findings.
- Interactive editor remained at LOADING with an empty preview; editor and source Divi visual acceptance remain open. No source parity claim was made.
- Package: `ace-divi-to-elementor-migrator-1.4.0.zip`, 182 public files. Private source JSON, fixtures, reports and tests are excluded.

Additional official source fixtures were captured for core Blog, Slider/Slide, Email Opt-in, Countdown and Circle Counter. They are evidence for subsequent mapping work, not new support claims in this release.
