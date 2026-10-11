# Ace branding update - 1.6.1

Product documentation, the migration plan and the source-only pipeline demo now use Ace. The current admin name, author, publisher-supplied logo and WordPress listing artwork already use Ace and remain in place. Getting-started instructions now include the local source/target page workflow.

The installed plugin basename, translation domain, namespaces, hooks, selectors and stored-data keys retain their existing identifiers for upgrade compatibility. Original customer URLs, fixture titles and migration content remain source evidence. The WordPress.org contributor remains KERVEE.

Verification compares the complete packaged source and assets against 1.6.0, allowing only the source-profile provenance comment to change. PHP syntax checks and the pipeline smoke check run on PHP 8.0 and 8.3. Package integrity, matching source bytes and the preceding ZIP are checked. Results are in `.qa-evidence/ace-branding-release.json`. Conversion logic and live sites are unchanged; prior runtime QA remains historical evidence and is not claimed as a new full regression run.
