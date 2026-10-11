# Function QA inventory - 1.6.0

401 declared method/class rows. Scores inherit feature scope, not individual-method coverage. [Site page QA](SITE-PAGE-QA.md) records the new subset.

| Source | Function | Line | Score | Status | Scope |
| --- | --- | ---: | --- | --- | --- |
| Admin/AjaxHandler.php | __construct | 4 | 5/5 | PASS | Admin AJAX authorization / nonces |
| Admin/AjaxHandler.php | runner | 13 | 5/5 | PASS | Admin AJAX authorization / nonces |
| Admin/AjaxHandler.php | process_batch | 18 | 5/5 | PASS | Admin AJAX authorization / nonces |
| Admin/AjaxHandler.php | cancel_job | 24 | 5/5 | PASS | Admin AJAX authorization / nonces |
| Admin/AjaxHandler.php | rollback_job | 30 | 5/5 | PASS | Admin AJAX authorization / nonces |
| Admin/AjaxHandler.php | json_input | 36 | 5/5 | PASS | Admin AJAX authorization / nonces |
| Admin/AjaxHandler.php | apply_saved | 44 | 5/5 | PASS | Admin AJAX authorization / nonces |
| Admin/AjaxHandler.php | clear_jobs | 50 | 5/5 | PASS | Admin AJAX authorization / nonces |
| Admin/AjaxHandler.php | recover_job | 56 | 5/5 | PASS | Admin AJAX authorization / nonces |
| Admin/AjaxHandler.php | review_recovery | 62 | 5/5 | PASS | Admin AJAX authorization / nonces |
| Admin/AnalysisPage.php | render | 4 | 4/5 | PASS | Source module / attribute / asset scan |
| Admin/DashboardPage.php | render | 4 | 4/5 | PASS | Guided product administration / user guide |
| Admin/ExportHandler.php | __construct | 4 | 4/5 | PASS | Reports / JSON / Elementor template downloads |
| Admin/ExportHandler.php | download | 5 | 4/5 | PASS | Reports / JSON / Elementor template downloads |
| Admin/GuidePage.php | render | 4 | 4/5 | PASS | Guided product administration / user guide |
| Admin/GuidePage.php | section | 90 | 4/5 | PASS | Guided product administration / user guide |
| Admin/ImportPage.php | render | 4 | 4/5 | PASS | JSON file upload / nonce / size limit |
| Admin/JobsPage.php | render | 4 | 4/5 | PASS | Job IDs / history / owner isolation |
| Admin/JobsPage.php | request | 89 | 4/5 | PASS | Job IDs / history / owner isolation |
| Admin/JobsPage.php | next | 97 | 4/5 | PASS | Job IDs / history / owner isolation |
| Admin/JobsPage.php | apply | 110 | 4/5 | PASS | Job IDs / history / owner isolation |
| Admin/MappingPage.php | render | 4 | 4/5 | PASS | Page mapping / job creation |
| Admin/Menu.php | __construct | 4 | 4/5 | PASS | Guided product administration / user guide |
| Admin/Menu.php | admin_styles | 10 | 4/5 | PASS | Guided product administration / user guide |
| Admin/Menu.php | action_links | 16 | 4/5 | PASS | Guided product administration / user guide |
| Admin/Menu.php | parent_file | 21 | 4/5 | PASS | Guided product administration / user guide |
| Admin/Menu.php | register_menus | 25 | 4/5 | PASS | Guided product administration / user guide |
| Admin/Menu.php | render_dashboard | 42 | 4/5 | PASS | Guided product administration / user guide |
| Admin/ReportPage.php | render | 4 | 4/5 | PASS | Reports / JSON / Elementor template downloads |
| Admin/RequestGuard.php | __construct | 5 | 4/5 | PASS | JSON file upload / nonce / size limit |
| Admin/RequestGuard.php | verify | 6 | 4/5 | PASS | JSON file upload / nonce / size limit |
| Admin/SettingsPage.php | render | 4 | 4/5 | PASS | Guided product administration / user guide |
| Admin/SettingsPage.php | field | 27 | 4/5 | PASS | Guided product administration / user guide |
| Admin/SitePagePage.php | render | 6 | 4/5 | PASS | Local source/target page selection |
| Admin/SitePagePage.php | steps | 42 | 4/5 | PASS | Local source/target page selection |
| Admin/SitePagePage.php | selection | 43 | 4/5 | PASS | Local source/target page selection |
| Admin/ThemeBuilderPage.php | render | 4 | 4/5 | PASS | UAE Header / Footer migration |
| Admin/ThemeBuilderPage.php | mapping | 5 | 4/5 | PASS | UAE Header / Footer migration |
| Admin/View.php | url | 6 | 4/5 | PASS | Guided product administration / user guide |
| Admin/View.php | start | 7 | 4/5 | PASS | Guided product administration / user guide |
| Admin/View.php | empty_state | 32 | 4/5 | PASS | Guided product administration / user guide |
| Assets/AssetProcessor.php | process | 6 | 4/5 | PASS | Image / caption / alt |
| Assets/AssetProcessor.php | collect_attachment_links | 13 | 4/5 | PASS | Image / caption / alt |
| Assets/AssetProcessor.php | menu_images | 19 | 4/5 | PASS | Source menu label image icons |
| Assets/AssetProcessor.php | elements | 36 | 4/5 | PASS | Image / caption / alt |
| Assets/AssetProcessor.php | rewrite_link | 96 | 4/5 | PASS | Image / caption / alt |
| Assets/AssetResolver.php | resolve_image | 4 | 4/5 | PASS | Media import / exact duplicate reuse |
| Assets/AssetScanner.php | scan | 4 | 4/5 | PASS | Source module / attribute / asset scan |
| Assets/DuplicateDetector.php | find_existing | 4 | 4/5 | PASS | Media import / exact duplicate reuse |
| Assets/EmbeddedImageImporter.php | import | 5 | 4/5 | PASS | Embedded Divi4 image import |
| Assets/MediaImporter.php | import_image | 4 | 4/5 | PASS | Media import / exact duplicate reuse |
| Assets/UrlRewriter.php | rewrite | 4 | 4/5 | PASS | Internal URL rewriting |
| Backup/BackupManager.php | (inherited implementation) | 3 | 5/5 | PASS | Job-scoped backups |
| Backup/Rollback.php | restore | 4 | 5/5 | PASS | Rollback / newer-edit protection |
| Backup/Snapshot.php | capture | 4 | 5/5 | PASS | Job-scoped backups |
| Database/SchemaInstaller.php | install | 5 | 4/5 | PASS | Source/job/backup retention |
| Divi/AttributeParser.php | parse | 4 | 4/5 | PASS | Shortcode lexer / parser / attributes |
| Divi/DynamicContentParser.php | is_dynamic | 14 | 1/5 | PARTIAL | Dynamic post / ACF / Woo bindings |
| Divi/DynamicContentParser.php | parse | 28 | 1/5 | PARTIAL | Dynamic post / ACF / Woo bindings |
| Divi/GlobalModuleResolver.php | resolve | 4 | 2/5 | PARTIAL | Global modules |
| Divi/PresetResolver.php | resolve_export | 5 | 3/5 | PARTIAL | Presets |
| Divi/PresetResolver.php | resolve | 16 | 3/5 | PARTIAL | Presets |
| Divi/Scanner.php | scan | 4 | 4/5 | PASS | Source module / attribute / asset scan |
| Divi/ShortcodeParser.php | parse | 4 | 4/5 | PASS | Shortcode lexer / parser / attributes |
| Divi/ShortcodeTokenizer.php | tokenize | 5 | 4/5 | PASS | Shortcode lexer / parser / attributes |
| Divi/ShortcodeTokenizer.php | append | 71 | 4/5 | PASS | Shortcode lexer / parser / attributes |
| Dynamic/AcfMapper.php | map | 4 | 1/5 | PARTIAL | Dynamic post / ACF / Woo bindings |
| Dynamic/DynamicMapper.php | map | 13 | 1/5 | PARTIAL | Dynamic post / ACF / Woo bindings |
| Dynamic/DynamicMapper.php | build_elementor_tag | 49 | 1/5 | PARTIAL | Dynamic post / ACF / Woo bindings |
| Dynamic/PageLinkResolver.php | diagnostics | 7 | 4/5 | PASS | Public source dynamic page link resolver |
| Dynamic/PageLinkResolver.php | reason | 8 | 4/5 | PASS | Public source dynamic page link resolver |
| Dynamic/PageLinkResolver.php | result | 9 | 4/5 | PASS | Public source dynamic page link resolver |
| Dynamic/PageLinkResolver.php | resolve | 13 | 4/5 | PASS | Public source dynamic page link resolver |
| Dynamic/PageLinkResolver.php | absolute_link | 40 | 4/5 | PASS | Public source dynamic page link resolver |
| Dynamic/PageLinkResolver.php | request | 44 | 4/5 | PASS | Public source dynamic page link resolver |
| Dynamic/PostLinkBinding.php | parse | 5 | 2/5 | REVIEWED | Shared models / utility facades |
| Dynamic/PostMapper.php | map | 4 | 1/5 | PARTIAL | Dynamic post / ACF / Woo bindings |
| Dynamic/SiteLinkResolver.php | resolve | 5 | 4/5 | PASS | Destination site-home link binding |
| Dynamic/SourceLinkSettings.php | origin | 5 | 4/5 | PASS | Public source dynamic page link resolver |
| Dynamic/SourceLinkSettings.php | enabled | 15 | 4/5 | PASS | Public source dynamic page link resolver |
| Dynamic/WooMapper.php | map | 4 | 0/5 | UNAVAILABLE | Woo cart / checkout clustering |
| Elementor/Atomic/ElementBuilder.php | build | 4 | 0/5 | UNAVAILABLE | Atomic output |
| Elementor/Atomic/Serializer.php | serialize | 4 | 0/5 | UNAVAILABLE | Atomic output |
| Elementor/Atomic/StyleBuilder.php | build | 4 | 0/5 | UNAVAILABLE | Atomic output |
| Elementor/Classic/ContainerBuilder.php | build | 4 | 4/5 | PASS | Native Elementor containers / widgets |
| Elementor/Classic/ResponsiveCss.php | compile | 6 | 4/5 | PASS | Responsive spacing / dimensions |
| Elementor/Classic/Serializer.php | serialize | 11 | 4/5 | PASS | Native Elementor containers / widgets |
| Elementor/Classic/Serializer.php | serialize_node | 46 | 4/5 | PASS | Native Elementor containers / widgets |
| Elementor/Classic/WidgetBuilder.php | build | 4 | 4/5 | PASS | Native Elementor containers / widgets |
| Elementor/ConvertedLinkStyles.php | register | 5 | 4/5 | PASS | Button / linked title text decoration |
| Elementor/ConvertedLinkStyles.php | enqueue | 9 | 4/5 | PASS | Button / linked title text decoration |
| Elementor/CssRegenerator.php | regenerate | 4 | 4/5 | PASS | CSS generation / cache invalidation |
| Elementor/DocumentBuilder.php | build | 13 | 4/5 | PASS | Native Elementor containers / widgets |
| Elementor/DocumentBuilder.php | fallback | 35 | 4/5 | PASS | Native Elementor containers / widgets |
| Elementor/DocumentBuilder.php | convert | 41 | 4/5 | PASS | Native Elementor containers / widgets |
| Elementor/FontAvailability.php | warnings | 7 | 4/5 | PASS | Scoped font loading / destination registration |
| Elementor/IdGenerator.php | generate | 4 | 5/5 | PASS | Deterministic element IDs / serialization |
| Elementor/PageWriter.php | write | 4 | 5/5 | PASS | Checked page writes / automatic recovery |
| Elementor/Posts/Excerpt.php | text | 6 | 4/5 | PASS | Blog post target/listing mapping |
| Elementor/Posts/Excerpt.php | plain | 28 | 4/5 | PASS | Blog post target/listing mapping |
| Elementor/Posts/GridQuery.php | args | 6 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/GridQuery.php | pages | 47 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/GridQuery.php | integer | 50 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/GridWidget.php | get_name | 6 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/GridWidget.php | get_title | 7 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/GridWidget.php | get_icon | 8 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/GridWidget.php | get_categories | 9 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/GridWidget.php | get_style_depends | 10 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/GridWidget.php | get_script_depends | 11 | 4/5 | PASS | Blog post target/listing mapping |
| Elementor/Posts/GridWidget.php | is_dynamic_content | 13 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/GridWidget.php | register_controls | 14 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/GridWidget.php | render | 75 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/GridWidget.php | meta | 126 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/GridWidget.php | icon | 147 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/Register.php | hooks | 5 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/Register.php | widgets | 10 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/Posts/Register.php | styles | 11 | 4/5 | PASS | Ace Post Grid / destination queries and pagination |
| Elementor/TargetResolver.php | kind | 4 | 4/5 | PASS | UAE Header / Footer migration |
| Elementor/TargetResolver.php | validate | 10 | 4/5 | PASS | UAE Header / Footer migration |
| Elementor/TargetResolver.php | draft | 17 | 4/5 | PASS | UAE Header / Footer migration |
| Elementor/TargetResolver.php | templates | 26 | 4/5 | PASS | UAE Header / Footer migration |
| Elementor/Validator.php | validate | 4 | 4/5 | PASS | Runtime schema / target validation |
| Elementor/Validator.php | control_value | 64 | 4/5 | PASS | Runtime schema / target validation |
| Forms/DiviContactFormParser.php | parse_fields | 4 | 2/5 | UNVERIFIED | Contact form / field parsing |
| Forms/ElementorFormBuilder.php | build | 4 | 2/5 | UNVERIFIED | Contact form / field parsing |
| Forms/EmailOptinConverter.php | convert | 4 | 0/5 | UNAVAILABLE | Email opt-in |
| Forms/FormReviewReport.php | required_checks | 4 | 2/5 | UNVERIFIED | Contact form / field parsing |
| Import/BackupEngine.php | key | 6 | 5/5 | PASS | Job-scoped backups |
| Import/BackupEngine.php | state | 7 | 5/5 | PASS | Job-scoped backups |
| Import/BackupEngine.php | fingerprint | 18 | 5/5 | PASS | Job-scoped backups |
| Import/BackupEngine.php | state_hash | 19 | 5/5 | PASS | Job-scoped backups |
| Import/BackupEngine.php | plan_write | 20 | 5/5 | PASS | Job-scoped backups |
| Import/BackupEngine.php | interrupted_state | 29 | 5/5 | PASS | Job-scoped backups |
| Import/BackupEngine.php | create_backup | 37 | 5/5 | PASS | Job-scoped backups |
| Import/BackupEngine.php | mark_written | 46 | 5/5 | PASS | Job-scoped backups |
| Import/BackupEngine.php | restore_backup | 52 | 5/5 | PASS | Job-scoped backups |
| Import/BatchImport.php | create | 4 | 4/5 | PASS | Bulk source selection / Convert Selected |
| Import/BatchImport.php | create_selected | 9 | 4/5 | PASS | Bulk source selection / Convert Selected |
| Import/BlogCategoryMapping.php | ids | 6 | 4/5 | PASS | Blog post target/listing mapping |
| Import/BlogCategoryMapping.php | pending | 13 | 4/5 | PASS | Blog post target/listing mapping |
| Import/BlogCategoryMapping.php | target | 35 | 4/5 | PASS | Blog post target/listing mapping |
| Import/BlogCategoryMapping.php | validate | 41 | 4/5 | PASS | Blog post target/listing mapping |
| Import/DiviExportDetector.php | detect | 4 | 4/5 | PASS | Divi envelope detection / recursive reading |
| Import/DiviExportReader.php | read_file | 5 | 4/5 | PASS | Divi envelope detection / recursive reading |
| Import/DiviExportReader.php | read_json | 11 | 4/5 | PASS | Divi envelope detection / recursive reading |
| Import/MenuMapping.php | references | 5 | 4/5 | PASS | WordPress navigation menu / Header conversion |
| Import/MenuMapping.php | pending | 21 | 4/5 | PASS | WordPress navigation menu / Header conversion |
| Import/MenuMapping.php | validate | 33 | 4/5 | PASS | WordPress navigation menu / Header conversion |
| Import/MenuMapping.php | fingerprint | 49 | 4/5 | PASS | Source menu label image icons |
| Import/MenuMapping.php | target | 54 | 4/5 | PASS | WordPress navigation menu / Header conversion |
| Import/RecommendedSettings.php | source_origin | 5 | 5/5 | PASS | Dry-run / analyze-only |
| Import/RecommendedSettings.php | for_pending | 28 | 5/5 | PASS | Dry-run / analyze-only |
| Import/RecommendedSettings.php | dry_run | 46 | 5/5 | PASS | Dry-run / analyze-only |
| Import/SitePageSelection.php | key | 6 | 4/5 | PASS | Local source/target page selection |
| Import/SitePageSelection.php | prepare | 10 | 4/5 | PASS | Local source/target page selection |
| Import/SitePageSelection.php | files | 19 | 4/5 | PASS | Local source/target page selection |
| Import/SitePageSelection.php | consume | 24 | 4/5 | PASS | Local source/target page selection |
| Import/SitePageSource.php | id | 6 | 4/5 | PARTIAL | Local source snapshot dependencies |
| Import/SitePageSource.php | page | 10 | 4/5 | PARTIAL | Local source snapshot dependencies |
| Import/SitePageSource.php | available | 14 | 4/5 | PARTIAL | Local source snapshot dependencies |
| Import/SitePageSource.php | capture | 20 | 4/5 | PARTIAL | Local source snapshot dependencies |
| Import/SitePageSource.php | assert_current | 62 | 4/5 | PARTIAL | Local source snapshot dependencies |
| Import/SitePageSource.php | suggestions | 69 | 4/5 | PARTIAL | Local source snapshot dependencies |
| Import/SourceMenuPresentation.php | read | 6 | 4/5 | PASS | Source menu label image icons |
| Import/SourceMenuPresentation.php | path | 14 | 4/5 | PASS | Source menu label image icons |
| Import/SourceMenuPresentation.php | label | 22 | 4/5 | PASS | Source menu label image icons |
| Import/SourceMenuPresentation.php | parse | 23 | 4/5 | PASS | Source menu label image icons |
| Import/SourceRepository.php | save | 6 | 4/5 | PASS | Private source ownership / path safety |
| Import/SourceRepository.php | get | 23 | 4/5 | PASS | Private source ownership / path safety |
| Import/TemplateReplacement.php | pending | 5 | 4/5 | PASS | Explicit unsupported-module template replacements |
| Import/TemplateReplacement.php | validate | 24 | 4/5 | PASS | Explicit unsupported-module template replacements |
| Import/TemplateReplacement.php | read | 48 | 4/5 | PASS | Explicit unsupported-module template replacements |
| Import/TemplateReplacement.php | convert | 69 | 4/5 | PASS | Explicit unsupported-module template replacements |
| Import/UploadValidator.php | validate | 4 | 4/5 | PASS | JSON file upload / nonce / size limit |
| Jobs/BatchRunner.php | __construct | 5 | 4/5 | PASS | Batch bounds / progress / failure states |
| Jobs/BatchRunner.php | response | 6 | 4/5 | PASS | Batch bounds / progress / failure states |
| Jobs/BatchRunner.php | process_batch | 7 | 4/5 | PASS | Batch bounds / progress / failure states |
| Jobs/BatchRunner.php | process_item | 63 | 4/5 | PASS | Direct Apply / newer edits during media preparation |
| Jobs/BatchRunner.php | apply_saved_item | 126 | 4/5 | PASS | Batch bounds / progress / failure states |
| Jobs/BatchRunner.php | recover | 163 | 4/5 | PASS | Batch bounds / progress / failure states |
| Jobs/BatchRunner.php | review_recovery | 181 | 4/5 | PASS | Batch bounds / progress / failure states |
| Jobs/BatchRunner.php | cancel | 193 | 4/5 | PASS | Batch bounds / progress / failure states |
| Jobs/BatchRunner.php | rollback | 202 | 4/5 | PASS | Batch bounds / progress / failure states |
| Jobs/Job.php | __construct | 29 | 4/5 | PASS | Job IDs / history / owner isolation |
| Jobs/Job.php | hydrate | 40 | 4/5 | PASS | Job IDs / history / owner isolation |
| Jobs/Job.php | to_array | 48 | 4/5 | PASS | Job IDs / history / owner isolation |
| Jobs/JobRepository.php | save | 4 | 4/5 | PASS | Job IDs / history / owner isolation |
| Jobs/JobRepository.php | get | 11 | 4/5 | PASS | Job IDs / history / owner isolation |
| Jobs/JobRepository.php | owner_pattern | 19 | 4/5 | PASS | Job IDs / history / owner isolation |
| Jobs/JobRepository.php | archive_pattern | 23 | 4/5 | PASS | Job IDs / history / owner isolation |
| Jobs/JobRepository.php | all | 27 | 4/5 | PASS | Job IDs / history / owner isolation |
| Jobs/JobRepository.php | count | 41 | 4/5 | PASS | Job IDs / history / owner isolation |
| Jobs/JobRepository.php | get_latest_active | 50 | 4/5 | PASS | Job IDs / history / owner isolation |
| Jobs/LockBusyException.php | (inherited implementation) | 3 | 4/5 | PASS | Interrupted-write review and restoration |
| Jobs/LockLostException.php | (inherited implementation) | 3 | 4/5 | PASS | Interrupted-write review and restoration |
| Jobs/LockManager.php | name | 7 | 4/5 | PASS | Concurrency locking |
| Jobs/LockManager.php | acquire | 11 | 4/5 | PASS | Concurrency locking |
| Jobs/LockManager.php | assert_owned | 37 | 4/5 | PASS | Concurrency locking |
| Jobs/LockManager.php | release | 44 | 4/5 | PASS | Concurrency locking |
| Jobs/SavedOutputApplier.php | eligible | 5 | 5/5 | PASS | Clear / restore migration jobs |
| Jobs/SavedOutputApplier.php | validate_output | 9 | 5/5 | PASS | Clear / restore migration jobs |
| Jobs/SavedOutputApplier.php | queue | 18 | 5/5 | PASS | Clear / restore migration jobs |
| Jobs/SavedOutputApplier.php | clear | 78 | 5/5 | PASS | Clear / restore migration jobs |
| Mapping/AttributeCoverage.php | unknown | 4 | 4/5 | PASS | Unsupported module preservation |
| Mapping/BaseConverter.php | get_target_type | 6 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/BaseConverter.php | map_content | 7 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/BaseConverter.php | convert | 8 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/BaseConverter.php | get_attr | 22 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/BaseConverter.php | consumes_children | 23 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/CommonConverter.php | link | 6 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/CommonConverter.php | image | 7 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/CommonConverter.php | map_content | 8 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/Core/AccordionConverter.php | get_target_type | 4 | 4/5 | PASS | Accordion fidelity |
| Mapping/Core/AccordionConverter.php | map_content | 5 | 4/5 | PASS | Accordion fidelity |
| Mapping/Core/BarCounterConverter.php | get_target_type | 9 | 3/5 | PARTIAL | Bar Counters native Free mapping |
| Mapping/Core/BarCounterConverter.php | convert | 10 | 3/5 | PARTIAL | Bar Counters native Free mapping |
| Mapping/Core/BarCounterConverter.php | map_content | 25 | 3/5 | PARTIAL | Bar Counters native Free mapping |
| Mapping/Core/BarCounterConverter.php | typography | 103 | 3/5 | PARTIAL | Bar Counters native Free mapping |
| Mapping/Core/BarCounterConverter.php | size | 128 | 3/5 | PARTIAL | Bar Counters native Free mapping |
| Mapping/Core/BarCounterConverter.php | color | 132 | 3/5 | PARTIAL | Bar Counters native Free mapping |
| Mapping/Core/BarCounterConverter.php | rule | 135 | 3/5 | PARTIAL | Bar Counters native Free mapping |
| Mapping/Core/BarCountersConverter.php | get_target_type | 7 | 3/5 | PARTIAL | Bar Counters native Free mapping |
| Mapping/Core/BarCountersConverter.php | convert | 8 | 3/5 | PARTIAL | Bar Counters native Free mapping |
| Mapping/Core/BarCountersConverter.php | map_content | 13 | 3/5 | PARTIAL | Bar Counters native Free mapping |
| Mapping/Core/BlogConverter.php | get_target_type | 10 | 3/5 | PARTIAL | Blog post target/listing mapping |
| Mapping/Core/BlogConverter.php | convert | 11 | 3/5 | PARTIAL | Blog post target/listing mapping |
| Mapping/Core/BlogConverter.php | map_content | 19 | 3/5 | PARTIAL | Blog post target/listing mapping |
| Mapping/Core/BlurbConverter.php | get_target_type | 4 | 4/5 | PASS | Blurb / image box / icon box |
| Mapping/Core/BusinessComposition.php | get_target_type | 9 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | convert | 10 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | start | 27 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | container | 37 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | surface | 40 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | theme | 50 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | font | 53 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | color | 59 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | heading | 63 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | body | 79 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | raw | 91 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | font_states | 92 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | typography_css | 99 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | surface_css | 116 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/BusinessComposition.php | child | 125 | 3/5 | PARTIAL | Business composition surface routing |
| Mapping/Core/ButtonConverter.php | get_target_type | 4 | 4/5 | PASS | Button / links |
| Mapping/Core/CallToActionConverter.php | convert | 7 | 3/5 | PARTIAL | Call To Action composition |
| Mapping/Core/CallToActionConverter.php | default_padding | 26 | 3/5 | PARTIAL | Call To Action composition |
| Mapping/Core/CallToActionConverter.php | map_content | 37 | 3/5 | PARTIAL | Call To Action composition |
| Mapping/Core/CodeConverter.php | get_target_type | 4 | 4/5 | PASS | Text / HTML / code |
| Mapping/Core/ColumnConverter.php | get_target_type | 4 | 4/5 | PASS | Section / row / inner row / column |
| Mapping/Core/DividerConverter.php | get_target_type | 4 | 4/5 | PASS | Divider |
| Mapping/Core/GalleryConverter.php | get_target_type | 4 | 3/5 | PARTIAL | Gallery |
| Mapping/Core/HeadingConverter.php | get_target_type | 6 | 4/5 | PASS | Standalone Divi Heading module |
| Mapping/Core/HeadingConverter.php | map_content | 7 | 4/5 | PASS | Standalone Divi Heading module |
| Mapping/Core/IconConverter.php | get_target_type | 4 | 4/5 | PASS | Standalone icon module |
| Mapping/Core/ImageConverter.php | get_target_type | 4 | 4/5 | PASS | Image / caption / alt |
| Mapping/Core/ImageConverter.php | convert | 5 | 4/5 | PASS | Image / caption / alt |
| Mapping/Core/MapConverter.php | get_target_type | 4 | 2/5 | PARTIAL | Map / pins |
| Mapping/Core/MenuConverter.php | get_target_type | 7 | 4/5 | PASS | WordPress navigation menu / Header conversion |
| Mapping/Core/MenuConverter.php | map_content | 8 | 4/5 | PASS | WordPress navigation menu / Header conversion |
| Mapping/Core/NumberCounterConverter.php | get_target_type | 4 | 4/5 | PASS | Number counter |
| Mapping/Core/PersonConverter.php | map_content | 7 | 3/5 | PARTIAL | Person composition |
| Mapping/Core/RowConverter.php | get_target_type | 4 | 4/5 | PASS | Section / row / inner row / column |
| Mapping/Core/SectionConverter.php | get_target_type | 4 | 4/5 | PASS | Section / row / inner row / column |
| Mapping/Core/SectionConverter.php | map_content | 5 | 4/5 | PASS | Section / row / inner row / column |
| Mapping/Core/SocialMediaFollowConverter.php | get_target_type | 4 | 3/5 | PARTIAL | Social icons |
| Mapping/Core/TabsConverter.php | get_target_type | 4 | 3/5 | PARTIAL | Tabs / toggle / accordion |
| Mapping/Core/TestimonialConverter.php | get_target_type | 4 | 4/5 | PASS | Testimonial |
| Mapping/Core/TextConverter.php | get_target_type | 4 | 4/5 | PASS | Text / HTML / code |
| Mapping/Core/TextConverter.php | map_content | 5 | 4/5 | PASS | Text / HTML / code |
| Mapping/Core/ToggleConverter.php | get_target_type | 4 | 3/5 | PARTIAL | Tabs / toggle / accordion |
| Mapping/Core/VideoConverter.php | get_target_type | 4 | 4/5 | PASS | Video |
| Mapping/IconMapper.php | map | 6 | 4/5 | PASS | Standalone icon module |
| Mapping/IdMapper.php | get_mapped_id | 4 | 2/5 | PARTIAL | Global modules |
| Mapping/IdMapper.php | set_mapped_id | 8 | 2/5 | PARTIAL | Global modules |
| Mapping/MappingResult.php | __construct | 5 | 2/5 | REVIEWED | Shared models / utility facades |
| Mapping/ModuleRegistry.php | __construct | 9 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/ModuleRegistry.php | register_defaults | 13 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/ModuleRegistry.php | all | 133 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/ModuleRegistry.php | get_mapping | 135 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/ModuleRegistry.php | get_converter | 145 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/ModuleRegistry.php | register_mapping | 156 | 4/5 | PASS | Native Elementor containers / widgets |
| Mapping/Pro/ContactFormConverter.php | get_target_type | 4 | 2/5 | UNVERIFIED | Contact form / field parsing |
| Mapping/Pro/PricingTableConverter.php | get_target_type | 4 | 2/5 | UNVERIFIED | Pricing table / pricing group |
| Mapping/Pro/PricingTablesConverter.php | get_target_type | 4 | 2/5 | UNVERIFIED | Pricing table / pricing group |
| Mapping/ThirdParty/BlogGridConverter.php | get_target_type | 10 | 4/5 | PARTIAL | Third-party Divi blog grid conversion (dgbm) |
| Mapping/ThirdParty/BlogGridConverter.php | convert | 11 | 4/5 | PARTIAL | Third-party Divi blog grid conversion (dgbm) |
| Mapping/ThirdParty/BlogGridConverter.php | map_content | 22 | 4/5 | PARTIAL | Third-party Divi blog grid conversion (dgbm) |
| Mapping/ThirdParty/BlogGridConverter.php | typography | 90 | 4/5 | PARTIAL | Third-party Divi blog grid conversion (dgbm) |
| Mapping/ThirdParty/BlogGridConverter.php | color | 105 | 4/5 | PARTIAL | Third-party Divi blog grid conversion (dgbm) |
| Mapping/ThirdParty/BlogGridConverter.php | handled | 109 | 4/5 | PARTIAL | Third-party Divi blog grid conversion (dgbm) |
| Mapping/Woo/ProductTitleConverter.php | get_target_type | 4 | 1/5 | UNVERIFIED | Woo product title |
| Model/AssetReference.php | __construct | 5 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/AssetReference.php | to_array | 6 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/ConversionResult.php | __construct | 5 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/ConversionResult.php | to_array | 6 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/Document.php | __construct | 27 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/Document.php | add_node | 31 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/Document.php | to_array | 35 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/DynamicBinding.php | __construct | 5 | 1/5 | PARTIAL | Dynamic post / ACF / Woo bindings |
| Model/DynamicBinding.php | to_array | 6 | 1/5 | PARTIAL | Dynamic post / ACF / Woo bindings |
| Model/Node.php | __construct | 51 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/Node.php | add_child | 57 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/Node.php | to_array | 61 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/ResponsiveValue.php | __construct | 5 | 4/5 | PASS | Responsive spacing / dimensions |
| Model/ResponsiveValue.php | get | 6 | 4/5 | PASS | Responsive spacing / dimensions |
| Model/ResponsiveValue.php | to_array | 7 | 4/5 | PASS | Responsive spacing / dimensions |
| Model/StyleBag.php | __construct | 22 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/StyleBag.php | set | 29 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/StyleBag.php | get | 40 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/StyleBag.php | to_elementor | 47 | 2/5 | REVIEWED | Shared models / utility facades |
| Model/StyleBag.php | to_array | 59 | 2/5 | REVIEWED | Shared models / utility facades |
| Normalize/AttributeNormalizer.php | normalize | 5 | 4/5 | PASS | Inactive device / hover settings |
| Normalize/BlurbContentNormalizer.php | normalize | 5 | 4/5 | PASS | Blurb / image box / icon box |
| Normalize/ContentNormalizer.php | normalize | 4 | 2/5 | REVIEWED | Shared models / utility facades |
| Normalize/DocumentNormalizer.php | normalize | 4 | 0/5 | UNAVAILABLE | Woo cart / checkout clustering |
| Normalize/ResponsiveNormalizer.php | normalize | 4 | 4/5 | PASS | Responsive spacing / dimensions |
| Normalize/StyleNormalizer.php | normalize | 4 | 4/5 | PASS | Responsive spacing / dimensions |
| Normalize/UnitNormalizer.php | slider | 4 | 4/5 | PASS | Responsive spacing / dimensions |
| Normalize/UnitNormalizer.php | dimensions | 9 | 4/5 | PASS | Responsive spacing / dimensions |
| Plugin.php | get_instance | 15 | 4/5 | PASS | Plugin bootstrap / dependency check |
| Plugin.php | __construct | 22 | 4/5 | PASS | Plugin bootstrap / dependency check |
| Plugin.php | run | 26 | 4/5 | PASS | Plugin bootstrap / dependency check |
| Plugin.php | meets_requirements | 38 | 4/5 | PASS | Plugin bootstrap / dependency check |
| Plugin.php | admin_notice_missing_elementor | 47 | 4/5 | PASS | Plugin bootstrap / dependency check |
| Plugin.php | setup_admin | 58 | 4/5 | PASS | Plugin bootstrap / dependency check |
| Reports/AssetReport.php | build | 4 | 4/5 | PASS | Reports / JSON / Elementor template downloads |
| Reports/ExportReport.php | json | 4 | 4/5 | PASS | Reports / JSON / Elementor template downloads |
| Reports/ExportReport.php | template | 5 | 4/5 | PASS | Reports / JSON / Elementor template downloads |
| Reports/JobOutcome.php | describe | 5 | 4/5 | PASS | Reports / JSON / Elementor template downloads |
| Reports/ModuleCoverageReport.php | build | 4 | 4/5 | PASS | Reports / JSON / Elementor template downloads |
| Reports/ReportBuilder.php | build | 4 | 4/5 | PASS | Reports / JSON / Elementor template downloads |
| Reports/WarningReport.php | build | 4 | 4/5 | PASS | Reports / JSON / Elementor template downloads |
| Reports/WarningReport.php | diagnostics | 10 | 4/5 | PASS | Reports / JSON / Elementor template downloads |
| Reports/WarningReport.php | classify | 28 | 4/5 | PASS | Reports / JSON / Elementor template downloads |
| Requirements.php | check | 4 | 4/5 | PASS | Plugin bootstrap / dependency check |
| Storage/Retention.php | register | 6 | 4/5 | PASS | Source/job/backup retention |
| Storage/Retention.php | has_backup | 10 | 4/5 | PASS | Source/job/backup retention |
| Storage/Retention.php | referenced | 15 | 4/5 | PASS | Source/job/backup retention |
| Storage/Retention.php | run | 20 | 4/5 | PASS | Source/job/backup retention |
| Storage/Retention.php | deactivate | 49 | 4/5 | PASS | Source/job/backup retention |
| Style/AccordionTranslator.php | supports | 6 | 4/5 | PASS | Accordion fidelity |
| Style/AccordionTranslator.php | translate | 9 | 4/5 | PASS | Accordion fidelity |
| Style/AccordionTranslator.php | rule | 21 | 4/5 | PASS | Accordion fidelity |
| Style/AccordionTranslator.php | color | 24 | 4/5 | PASS | Accordion fidelity |
| Style/AccordionTranslator.php | font | 29 | 4/5 | PASS | Accordion fidelity |
| Style/AccordionTranslator.php | apply | 44 | 4/5 | PASS | Accordion fidelity |
| Style/BackgroundTranslator.php | translate | 16 | 3/5 | PARTIAL | Background color / image / gradient / video |
| Style/BackgroundTranslator.php | gradient_surface | 107 | 3/5 | PARTIAL | Background color / image / gradient / video |
| Style/BackgroundTranslator.php | get_attr | 112 | 3/5 | PARTIAL | Background color / image / gradient / video |
| Style/BorderTranslator.php | translate | 15 | 4/5 | PARTIAL | Borders / radius / shadows |
| Style/BorderTranslator.php | get_attr | 116 | 4/5 | PARTIAL | Borders / radius / shadows |
| Style/ButtonIconTranslator.php | translate | 6 | 4/5 | PASS | Button icons / placement / hover visibility |
| Style/ButtonIconTranslator.php | rule | 44 | 4/5 | PASS | Button icons / placement / hover visibility |
| Style/ButtonIconTranslator.php | handled | 47 | 4/5 | PASS | Button icons / placement / hover visibility |
| Style/CssFallbackTranslator.php | translate | 5 | 3/5 | PARTIAL | Custom CSS / fallback CSS |
| Style/CustomCssTranslator.php | translate | 5 | 3/5 | PARTIAL | Custom CSS / fallback CSS |
| Style/FilterTranslator.php | translate | 5 | 3/5 | PARTIAL | CSS filters |
| Style/FontFamily.php | normalize | 7 | 4/5 | PASS | Scoped font loading / destination registration |
| Style/FontFamily.php | css | 14 | 4/5 | PASS | Scoped font loading / destination registration |
| Style/FontFamily.php | weight | 15 | 4/5 | PASS | Scoped font loading / destination registration |
| Style/FontFamily.php | properties | 21 | 4/5 | PASS | Scoped font loading / destination registration |
| Style/FontFamily.php | declarations | 38 | 4/5 | PASS | Scoped font loading / destination registration |
| Style/FontFamily.php | from_css | 43 | 4/5 | PASS | Scoped font loading / destination registration |
| Style/FontFamily.php | in_node | 58 | 4/5 | PASS | Scoped font loading / destination registration |
| Style/FontFamily.php | is_generic | 68 | 4/5 | PASS | Scoped font loading / destination registration |
| Style/HoverTranslator.php | translate | 5 | 3/5 | PARTIAL | Hover |
| Style/ListTypographyTranslator.php | translate | 6 | 4/5 | PASS | UL / OL typography |
| Style/ListTypographyTranslator.php | font | 42 | 4/5 | PASS | UL / OL typography |
| Style/MenuTranslator.php | color | 5 | 4/5 | PASS | WordPress navigation menu / Header conversion |
| Style/MenuTranslator.php | rule | 9 | 4/5 | PASS | WordPress navigation menu / Header conversion |
| Style/MenuTranslator.php | translate | 13 | 4/5 | PASS | WordPress navigation menu / Header conversion |
| Style/MotionTranslator.php | translate | 7 | 3/5 | PARTIAL | Background parallax / horizontal scroll motion |
| Style/MotionTranslator.php | curve | 72 | 3/5 | PARTIAL | Background parallax / horizontal scroll motion |
| Style/PositionTranslator.php | translate | 5 | 3/5 | PARTIAL | Position / transforms |
| Style/RowLayoutTranslator.php | translate | 6 | 4/5 | PASS | Divi compact row grid / empty spacer columns |
| Style/ScopedCssTranslator.php | rule | 6 | 3/5 | PARTIAL | Supplemental scoped CSS / shape dividers |
| Style/ScopedCssTranslator.php | translate | 11 | 3/5 | PARTIAL | Supplemental scoped CSS / shape dividers |
| Style/ScopedCssTranslator.php | link_color | 102 | 3/5 | PARTIAL | Supplemental scoped CSS / shape dividers |
| Style/ScopedCssTranslator.php | font | 108 | 3/5 | PARTIAL | Supplemental scoped CSS / shape dividers |
| Style/ShadowTranslator.php | (inherited implementation) | 3 | 4/5 | PARTIAL | Borders / radius / shadows |
| Style/ShapeDividerTranslator.php | translate | 5 | 3/5 | PARTIAL | Supplemental scoped CSS / shape dividers |
| Style/SizingTranslator.php | translate | 5 | 3/5 | PARTIAL | Visibility / sizing / z-index |
| Style/SourceThemeProfile.php | recommended | 5 | 4/5 | PARTIAL | Source theme defaults |
| Style/SourceThemeProfile.php | validate | 18 | 4/5 | PARTIAL | Source theme defaults |
| Style/SourceThemeProfile.php | attributes | 35 | 4/5 | PARTIAL | Source theme defaults |
| Style/SpacingTranslator.php | translate | 5 | 4/5 | PASS | Button surface padding |
| Style/SpacingTranslator.php | button_padding | 36 | 4/5 | PASS | Button surface padding |
| Style/StickyTranslator.php | translate | 5 | 0/5 | UNAVAILABLE | Sticky |
| Style/TextDecorationTranslator.php | translate | 7 | 4/5 | PASS | Button / linked title text decoration |
| Style/TextDecorationTranslator.php | decoration | 39 | 4/5 | PASS | Button / linked title text decoration |
| Style/TextDecorationTranslator.php | rule | 49 | 4/5 | PASS | Button / linked title text decoration |
| Style/TransformTranslator.php | translate | 5 | 3/5 | PARTIAL | Position / transforms |
| Style/TypographyTranslator.php | translate | 5 | 4/5 | PARTIAL | Typography |
| Style/VisibilityTranslator.php | translate | 5 | 3/5 | PARTIAL | Visibility / sizing / z-index |
| Support/Arr.php | get | 4 | 2/5 | REVIEWED | Shared models / utility facades |
| Support/Arr.php | only | 5 | 2/5 | REVIEWED | Shared models / utility facades |
| Support/Json.php | decode | 4 | 2/5 | REVIEWED | Shared models / utility facades |
| Support/Json.php | encode | 5 | 2/5 | REVIEWED | Shared models / utility facades |
| Support/Logger.php | add | 6 | 2/5 | REVIEWED | Shared models / utility facades |
| Support/Logger.php | entries | 7 | 2/5 | REVIEWED | Shared models / utility facades |
| Support/Strings.php | classes | 4 | 2/5 | REVIEWED | Shared models / utility facades |
