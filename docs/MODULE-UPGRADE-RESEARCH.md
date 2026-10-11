# Module conversion upgrade research

Research date: October 8, 2026. Baseline: Ace 1.2.5, current `ace-divi-elementor-migrator` checkout. This is a research/backlog artifact; no new runtime converter is implemented by this document.

## Implementation update

The following research counts describe the 1.2.5 baseline. Version 1.4.0 adds a PARTIAL Bar Counters adapter alongside the earlier PARTIAL Call To Action and Person adapters (35 registered tags; 49 remaining missing additions). The current inventory/CSV reflects those limited changes; [P01 QA](MODULE-UPGRADE-P01-QA.md) and [P05 QA](MODULE-UPGRADE-P05-QA.md) record what was actually tested and what remains open. Other proposals below remain fixture-gated research.

## Findings and scope

The published Divi 4 collection contains 44 core and 25 Woo module articles: **69 module families**, not 89 modules. Its 89-article heading also counts provider/setup articles. Against the current 31 registered source tags, 47 of those 69 families lack an automatic converter. Five documented fullwidth/Theme Builder additions bring the missing-module/variant backlog to **52**. A sixth historical entry, Legacy Shop, is a schema review under Woo Products, not a second missing converter. There are **53 candidate entries** in total. These are named source families/variants, not verified distinct shortcode counts: parent/child/layout/alias tags are not interchangeable counts. Module identity and registry absence are confirmed for the named gaps; new shortcode fields/control mappings remain fixture-gated. [Divi 4 catalogue](https://help.elegantthemes.com/en/collections/165949-divi-modules).

The inventory has **122 rows**: 75 Divi 4 module/variant entries, seven structural rows, the existing DiviGear grid, 15 optional vendor candidates and 24 optional Divi 5 candidates. It is complete for the named catalogue snapshot and six cited extras. It does not claim to enumerate every third-party Divi module or every historical export schema. Each row has a source reference, status, proposed target, chunk, fixture, tasks, acceptance and limits in [inventory.json](module-upgrades/inventory.json); [CSV](MODULE-UPGRADE-INVENTORY.csv) is provided for filtering.

No adapter name, class presence, registry fidelity letter or screenshot proves complete functionality. Current forms emit empty submit actions and are preview-only; pricing/product-title maps need licensed runtime acceptance. Existing bounded support and research candidates are kept distinct.

The generic legacy Divi modules URL redirects to a Divi 5 collection at research time. The Divi 4 collection above was used for this comparison. Divi 5 is a separate future track. Proposed targets below are engineering recommendations; exact parity, installed controls and plan/provider entitlement still require verification. [Elementor catalogue](https://elementor.com/resources/site-builder/), [widget data structure](https://developers.elementor.com/docs/data-structure/widget-element.html).

## Highest-value order

1. F00 evidence/capability truth, then P01 static compositions and P02/P03 fullwidth structures.
2. P07 core Blog after F01 context preparation; keep existing DiviGear output unchanged. Source core Blog is a different schema from the supported third-party grid. [Core Blog documentation](https://help.elegantthemes.com/en/articles/8617111-the-divi-blog-module).
3. P05 counters and P06 search/sidebar; H01 cross-site media and H02 nested interactions.
4. P08-P13 project/slider/dynamic template work after identity, context and interaction foundations pass.
5. P14-P17 audio/time/auth/forms/providers after functional fixture and dependency setup.
6. W00, then W01-W10 for commerce. Cart/checkout need a reviewed compound design, not destructive sibling clustering. [Divi cart/checkout](https://www.elegantthemes.com/blog/theme-releases/divi-woocommerce-cart-checkout), [Elementor Cart](https://elementor.com/blog/how-to-use-woocommerce-cart-widget/).
7. V00 and D5-00 only as explicitly scoped later research. Do not let optional provider/new-format work delay fixing proven Divi 4 gaps.

Priority is an engineering recommendation based on implementation reuse, semantic risk and current scope. Usage frequency for new modules has not been measured; F00 scans actual fixtures before reprioritizing. Each implementation unit changes at most three adapters. H03 is a shared-style/preservation audit and can inspect more modules; any resulting changes must be narrowed into tested units.

## Status definitions

- **MISSING:** Module family has no automatic registered converter; presence of a stub is not support.
- **AVAILABLE_BOUNDED:** Current native mapping exists with tested/declared limits. Maintain it; not universal fidelity.
- **PARTIAL:** Current mapping handles a subset or approximation; the remaining modes need scoped work.
- **PREVIEW_ONLY:** Can prepare a preview, but current output cannot pass automatic Apply's form-action guard.
- **IMPLEMENTED_UNVERIFIED:** Code exists but the relevant licensed/runtime acceptance remains pending.
- **STRUCTURE_REVIEW:** Uses an existing section type, but special structure/options need source fixtures and acceptance.
- **RESEARCH_ONLY:** Vendor module identity is documented; exact schema/version/use and target equivalence are not established.
- **OUT_OF_SCOPE_DIVI5:** Future input-format expansion; current Divi 4 parser does not support it.
- **LEGACY_VARIANT_REVIEW:** Historical Shop schema/label review under Woo Products; a distinct missing converter is not assumed.

## Confirmed new core Divi 4 converter gaps

| ID | Source module / variant | Current status | Chunk | Proposed Elementor target | Evidence |
|---|---|---|---|---|---|
| D4-02 | Audio | MISSING | [P14](module-upgrades/chunks/P14.md) | Editable WordPress audio/player composition; SoundCloud only for matching URLs | [source](https://help.elegantthemes.com/en/articles/8616756-the-divi-audio-module) |
| D4-03 | Bar Counters | PARTIAL | [P05](module-upgrades/chunks/P05.md) | Verified native Free Progress Bar subset; geometry/timing and source visual acceptance remain open | [source](https://help.elegantthemes.com/en/articles/8616758-the-divi-bar-counters-module) |
| D4-04 | Blog | MISSING | [P07](module-upgrades/chunks/P07.md) | Pro Posts/Loop Grid or an extended Ace query renderer, chosen by actual capabilities | [source](https://help.elegantthemes.com/en/articles/8617111-the-divi-blog-module) |
| D4-07 | Call To Action | MISSING | [P01](module-upgrades/chunks/P01.md) | Container + Heading + Text Editor + Button (Free); Pro Call to Action only after parity checks | [source](https://help.elegantthemes.com/en/articles/8624032-the-divi-call-to-action-module) |
| D4-10 | Comments | MISSING | [P13](module-upgrades/chunks/P13.md) | Pro Post Comments or WordPress comments adapter | [source](https://help.elegantthemes.com/en/articles/8624029-the-divi-comments-module) |
| D4-12 | Countdown Timer | MISSING | [P14](module-upgrades/chunks/P14.md) | Pro Countdown or explicit editable timer adapter | [source](https://help.elegantthemes.com/en/articles/8656101-the-divi-countdown-timer-module) |
| D4-14 | Email Optin | MISSING | [P17](module-upgrades/chunks/P17.md) | Pro Form or explicitly selected provider-backed opt-in widget | [source](https://help.elegantthemes.com/en/articles/8642902-the-divi-email-optin-module) |
| D4-15 | Filterable Portfolio | MISSING | [P08](module-upgrades/chunks/P08.md) | Pro Portfolio/Taxonomy Filter + Loop Grid, if equivalent; otherwise a dedicated adapter | [source](https://help.elegantthemes.com/en/articles/8637362-the-divi-filterable-portfolio-module) |
| D4-20 | Login | MISSING | [P15](module-upgrades/chunks/P15.md) | Pro Login or WordPress login adapter | [source](https://help.elegantthemes.com/en/articles/8637939-the-divi-login-module) |
| D4-22 | Fullwidth Map | MISSING | [P04](module-upgrades/chunks/P04.md) | Google Maps for a single resolvable address; provider-backed adapter for more | [source](https://help.elegantthemes.com/en/articles/8637565-the-divi-fullwidth-map-module) |
| D4-24 | Fullwidth Menu | MISSING | [P04](module-upgrades/chunks/P04.md) | Container + mapped WordPress Navigation Menu + optional Image | [source](https://help.elegantthemes.com/en/articles/8637614-the-divi-fullwidth-menu-module) |
| D4-26 | Person | MISSING | [P01](module-upgrades/chunks/P01.md) | Container + Image + Heading + Text Editor + Social Icons | [source](https://help.elegantthemes.com/en/articles/8643122-the-divi-person-module) |
| D4-27 | Portfolio | MISSING | [P08](module-upgrades/chunks/P08.md) | Pro Portfolio/Loop Grid or a validated editable project-grid adapter | [source](https://help.elegantthemes.com/en/articles/8643123-the-divi-portfolio-module) |
| D4-28 | Fullwidth Portfolio | MISSING | [P11](module-upgrades/chunks/P11.md) | Fullwidth project carousel/grid chosen from actual source layout | [source](https://help.elegantthemes.com/en/articles/8637730-the-divi-fullwidth-portfolio-module) |
| D4-29 | Post Navigation | MISSING | [P12](module-upgrades/chunks/P12.md) | Pro Post Navigation or a current-post-aware adapter | [source](https://help.elegantthemes.com/en/articles/8643125-the-divi-post-navigation-module) |
| D4-30 | Post Slider | MISSING | [P10](module-upgrades/chunks/P10.md) | Pro Loop Carousel or query-capable editable slider adapter | [source](https://help.elegantthemes.com/en/articles/8643126-the-divi-post-slider-module) |
| D4-31 | Post Title | MISSING | [P12](module-upgrades/chunks/P12.md) | Post Title + Post Info + Featured Image composition | [source](https://help.elegantthemes.com/en/articles/8645331-the-divi-post-title-module) |
| D4-33 | Search | MISSING | [P06](module-upgrades/chunks/P06.md) | Pro Search if available; otherwise an explicitly selected WordPress/Ace search adapter | [source](https://help.elegantthemes.com/en/articles/8646276-the-divi-search-module) |
| D4-34 | Sidebar | MISSING | [P06](module-upgrades/chunks/P06.md) | Sidebar | [source](https://help.elegantthemes.com/en/articles/8650069-the-divi-sidebar-module) |
| D4-35 | Slider | MISSING | [P09](module-upgrades/chunks/P09.md) | Pro Slides/Nested Carousel or an editable carousel adapter | [source](https://help.elegantthemes.com/en/articles/8650071-the-divi-slider-module) |
| D4-36 | Fullwidth Slider | MISSING | [P09](module-upgrades/chunks/P09.md) | Fullwidth container + validated Slider output | [source](https://help.elegantthemes.com/en/articles/8650073-the-divi-fullwidth-slider-module) |
| D4-43 | Video Slider | MISSING | [P11](module-upgrades/chunks/P11.md) | Pro Media Carousel/Video Playlist or an editable video-slider adapter | [source](https://help.elegantthemes.com/en/articles/8650800-the-divi-video-slider-module) |
| D4-44 | Fullwidth Header | MISSING | [P02](module-upgrades/chunks/P02.md) | Native container composition with logo/image, heading, subtitle, body and up to two buttons | [source](https://help.elegantthemes.com/en/articles/8637489-the-divi-fullwidth-header-module) |

## Confirmed WooCommerce gaps

These are candidate conversions, not 24 independent Elementor widgets. Several source checkout/cart components belong inside one native target form. A standalone native equivalent is not invented for components or reviews. The existing Product Title is listed separately as unverified. Actual Pro plan/widget availability is checked before Apply. [Elementor Woo widgets](https://elementor.com/help/woocommerce-widgets-pro/).

| ID | Source module / variant | Current status | Chunk | Proposed Elementor target | Evidence |
|---|---|---|---|---|---|
| W4-01 | Woo Breadcrumbs | MISSING | [W05](module-upgrades/chunks/W05.md) | Pro WooCommerce Breadcrumbs | [source](https://help.elegantthemes.com/en/articles/8679275-the-divi-woo-breadcrumbs-module) |
| W4-02 | Woo Cart Products | MISSING | [W08](module-upgrades/chunks/W08.md) | Pro Cart constituent within an explicit compound layout plan | [source](https://help.elegantthemes.com/en/articles/8679276-the-divi-woo-cart-products-module) |
| W4-03 | Woo Cart Totals | MISSING | [W08](module-upgrades/chunks/W08.md) | Pro Cart totals constituent, not a fabricated standalone widget | [source](https://help.elegantthemes.com/en/articles/8679273-the-divi-woo-cart-totals-module) |
| W4-04 | Woo Checkout Billing | MISSING | [W09](module-upgrades/chunks/W09.md) | Pro Checkout billing constituent | [source](https://help.elegantthemes.com/en/articles/8679274-the-divi-woo-checkout-billing-module) |
| W4-05 | Woo Checkout Details | MISSING | [W10](module-upgrades/chunks/W10.md) | Pro Checkout order-review constituent | [source](https://help.elegantthemes.com/en/articles/8679271-the-divi-woo-checkout-details-module) |
| W4-06 | Woo Checkout Information | MISSING | [W09](module-upgrades/chunks/W09.md) | Pro Checkout additional-information constituent | [source](https://help.elegantthemes.com/en/articles/8679272-the-divi-woo-checkout-information-module) |
| W4-07 | Woo Checkout Payment | MISSING | [W10](module-upgrades/chunks/W10.md) | Pro Checkout payment constituent | [source](https://help.elegantthemes.com/en/articles/8679268-the-divi-woo-checkout-payment-module) |
| W4-08 | Woo Checkout Shipping | MISSING | [W09](module-upgrades/chunks/W09.md) | Pro Checkout shipping constituent | [source](https://help.elegantthemes.com/en/articles/8679269-the-divi-woo-checkout-shipping-module) |
| W4-09 | Woo Cross Sells | MISSING | [W08](module-upgrades/chunks/W08.md) | Cart cross-sell constituent or dedicated WooCommerce cross-sells adapter | [source](https://help.elegantthemes.com/en/articles/8676300-the-divi-woo-cross-sells-module) |
| W4-10 | Woo Notice | MISSING | [W05](module-upgrades/chunks/W05.md) | Pro WooCommerce Notices | [source](https://help.elegantthemes.com/en/articles/8680481-the-divi-woo-notice-module) |
| W4-11 | Woo Product Add to Cart | MISSING | [W05](module-upgrades/chunks/W05.md) | Pro Add To Cart or Custom Add To Cart by context | [source](https://help.elegantthemes.com/en/articles/8683132-the-divi-woo-product-add-to-cart-module) |
| W4-12 | Woo Product Description | MISSING | [W03](module-upgrades/chunks/W03.md) | Pro Short Description or Product Content, selected by captured source mode | [source](https://help.elegantthemes.com/en/articles/8676299-the-divi-woo-product-description-module) |
| W4-13 | Woo Product Gallery | MISSING | [W01](module-upgrades/chunks/W01.md) | Pro Product Images gallery or separate product-gallery adapter | [source](https://help.elegantthemes.com/en/articles/9556136-the-divi-woo-product-gallery-module) |
| W4-14 | Woo Product Images | MISSING | [W01](module-upgrades/chunks/W01.md) | Pro Product Images | [source](https://help.elegantthemes.com/en/articles/8676297-the-divi-woo-product-images-module) |
| W4-15 | Woo Product Information | MISSING | [W03](module-upgrades/chunks/W03.md) | Pro Additional Information | [source](https://help.elegantthemes.com/en/articles/8676114-the-divi-woo-product-information-module) |
| W4-16 | Woo Product Meta | MISSING | [W03](module-upgrades/chunks/W03.md) | Pro Product Meta | [source](https://help.elegantthemes.com/en/articles/8683260-the-divi-woo-product-meta-module) |
| W4-17 | Woo Product Price | MISSING | [W02](module-upgrades/chunks/W02.md) | Pro Product Price | [source](https://help.elegantthemes.com/en/articles/8675708-the-divi-woo-product-price-module) |
| W4-18 | Woo Product Reviews | MISSING | [W04](module-upgrades/chunks/W04.md) | Pro Product Data Tabs review portion or dedicated reviews adapter | [source](https://help.elegantthemes.com/en/articles/8676111-the-divi-woo-product-reviews-module) |
| W4-19 | Woo Product Stock | MISSING | [W02](module-upgrades/chunks/W02.md) | Pro Product Stock | [source](https://help.elegantthemes.com/en/articles/8675709-the-divi-woo-product-stock-module) |
| W4-20 | Woo Product Tabs | MISSING | [W04](module-upgrades/chunks/W04.md) | Pro Product Data Tabs | [source](https://help.elegantthemes.com/en/articles/8679277-the-divi-woo-product-tabs-module) |
| W4-22 | Woo Product Upsell | MISSING | [W07](module-upgrades/chunks/W07.md) | Pro Upsells | [source](https://help.elegantthemes.com/en/articles/8683300-the-divi-woo-product-upsell-module) |
| W4-23 | Woo Products | MISSING | [W06](module-upgrades/chunks/W06.md) | Pro Products/Archive Products or validated product-query adapter | [source](https://help.elegantthemes.com/en/articles/8637353-the-divi-woo-products-module) |
| W4-24 | Woo Related Products | MISSING | [W07](module-upgrades/chunks/W07.md) | Pro Product Related | [source](https://help.elegantthemes.com/en/articles/8675706-the-divi-woo-related-products-module) |
| W4-25 | Woo Product Rating | MISSING | [W02](module-upgrades/chunks/W02.md) | Pro Product Rating | [source](https://help.elegantthemes.com/en/articles/8676113-the-divi-woo-product-rating-module) |

## Documented legacy / template additions

These six families are outside the current collection's ordinary module-article set. Record their actual source version/export before implementing a normalization alias. A module name from an older official release is not evidence that a current export uses the same attributes.

| ID | Source module / variant | Current status | Chunk | Proposed Elementor target | Evidence |
|---|---|---|---|---|---|
| L4-01 | Fullwidth Code | MISSING | [P03](module-upgrades/chunks/P03.md) | HTML | [source](https://www.elegantthemes.com/blog/theme-releases/divi-2-4) |
| L4-02 | Fullwidth Image | MISSING | [P03](module-upgrades/chunks/P03.md) | Image | [source](https://www.elegantthemes.com/blog/theme-releases/divi-2-4) |
| L4-03 | Fullwidth Post Title | MISSING | [P12](module-upgrades/chunks/P12.md) | Fullwidth container + validated Post Title composition | [source](https://www.elegantthemes.com/blog/theme-releases/divi-2-4) |
| L4-04 | Fullwidth Post Slider | MISSING | [P10](module-upgrades/chunks/P10.md) | Fullwidth container + validated Post Slider output | [source](https://www.elegantthemes.com/blog/theme-releases/divi-2-6) |
| L4-05 | Post Content | MISSING | [P13](module-upgrades/chunks/P13.md) | Pro Post Content in a supported single template | [source](https://www.elegantthemes.com/blog/divi-resources/how-to-use-the-post-content-module-in-the-divi-theme-builder) |
| L4-06 | Legacy Shop | LEGACY_VARIANT_REVIEW | [W06](module-upgrades/chunks/W06.md) | Same verified Products adapter after legacy schema normalization | [source](https://www.elegantthemes.com/blog/theme-releases/divi-feature-update-1000-new-foundational-options-improved-options-usability-clarity) |

## Existing mappings that need bounded acceptance or extension

| ID | Source module / variant | Current status | Chunk | Proposed Elementor target | Evidence |
|---|---|---|---|---|---|
| D4-01 | Accordion | PARTIAL | [H02](module-upgrades/chunks/H02.md) | Legacy Accordion or verified Nested Accordion, chosen by actual content | [source](https://help.elegantthemes.com/en/articles/8611169-the-divi-accordion-module) |
| D4-05 | Blurb | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | image-box/icon-box | [source](https://help.elegantthemes.com/en/articles/8617524-the-divi-blurb-module) |
| D4-06 | Button | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | button | [source](https://help.elegantthemes.com/en/articles/8617547-the-divi-button-module) |
| D4-08 | Circle Counter | PARTIAL | [P05](module-upgrades/chunks/P05.md) | Editable circular-progress adapter; existing numeric Counter retained as explicit approximation | [source](https://help.elegantthemes.com/en/articles/8617719-the-divi-circle-counter-module) |
| D4-09 | Code | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | html | [source](https://help.elegantthemes.com/en/articles/8608209-the-divi-code-module) |
| D4-11 | Contact Form | PREVIEW_ONLY | [P16](module-upgrades/chunks/P16.md) | Pro Form, with explicitly reviewed configured actions | [source](https://help.elegantthemes.com/en/articles/8624843-the-divi-contact-form-module) |
| D4-13 | Divider | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | divider | [source](https://help.elegantthemes.com/en/articles/8637248-the-divi-divider-module) |
| D4-16 | Gallery | PARTIAL | [H01](module-upgrades/chunks/H01.md) | Image Gallery/Image Carousel/Pro Gallery, selected by source mode | [source](https://help.elegantthemes.com/en/articles/8655973-the-divi-gallery-module) |
| D4-17 | Heading | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | heading | [source](https://help.elegantthemes.com/en/articles/9552416-the-divi-heading-module) |
| D4-18 | Icon | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | icon | [source](https://help.elegantthemes.com/en/articles/8675703-the-divi-icon-module) |
| D4-19 | Image | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | image | [source](https://help.elegantthemes.com/en/articles/8637837-the-divi-image-module) |
| D4-21 | Map | PARTIAL | [H04](module-upgrades/chunks/H04.md) | google_maps | [source](https://help.elegantthemes.com/en/articles/8637947-the-divi-map-module) |
| D4-23 | Menu | PARTIAL | [H04](module-upgrades/chunks/H04.md) | container + wp-widget-nav_menu + optional image | [source](https://help.elegantthemes.com/en/articles/9955331-the-divi-menu-module) |
| D4-25 | Number Counter | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | counter | [source](https://help.elegantthemes.com/en/articles/8642911-the-divi-number-counter-module) |
| D4-32 | Pricing Tables | IMPLEMENTED_UNVERIFIED | [P16](module-upgrades/chunks/P16.md) | Container + Pro Price Table children | [source](https://help.elegantthemes.com/en/articles/8645334-the-divi-pricing-tables-module) |
| D4-37 | Social Media Follow | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | social-icons | [source](https://help.elegantthemes.com/en/articles/8650238-the-divi-social-media-follow-module) |
| D4-38 | Tabs | PARTIAL | [H02](module-upgrades/chunks/H02.md) | Legacy Tabs or verified Nested Tabs | [source](https://help.elegantthemes.com/en/articles/8650239-the-divi-tabs-module) |
| D4-39 | Testimonial | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | testimonial | [source](https://help.elegantthemes.com/en/articles/8650240-the-divi-testimonial-module) |
| D4-40 | Text | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | text-editor | [source](https://help.elegantthemes.com/en/articles/8650241-the-divi-text-module) |
| D4-41 | Toggle | PARTIAL | [H02](module-upgrades/chunks/H02.md) | Legacy Toggle or one-item Nested Accordion where semantically equivalent | [source](https://help.elegantthemes.com/en/articles/8650242-the-divi-toggle-module) |
| D4-42 | Video | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | video | [source](https://help.elegantthemes.com/en/articles/8650243-the-divi-video-module) |
| W4-21 | Woo Product Title | IMPLEMENTED_UNVERIFIED | [W04](module-upgrades/chunks/W04.md) | Pro Product Title | [source](https://help.elegantthemes.com/en/articles/8675707-the-divi-woo-product-title-module) |

## Structural variants and existing extension coverage

| ID | Source module / variant | Current status | Chunk | Proposed Elementor target | Evidence |
|---|---|---|---|---|---|
| S4-01 | Section | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | Flexbox Container | [source](https://help.elegantthemes.com/en/articles/8671187-using-divi-sizing-options) |
| S4-02 | Row | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | Flexbox Container | [source](https://help.elegantthemes.com/en/articles/8671187-using-divi-sizing-options) |
| S4-03 | Inner Row | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | Flexbox Container | [source](https://help.elegantthemes.com/en/articles/8671187-using-divi-sizing-options) |
| S4-04 | Column | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | Flexbox Container | [source](https://help.elegantthemes.com/en/articles/8671187-using-divi-sizing-options) |
| S4-05 | Inner Column | AVAILABLE_BOUNDED | [H03](module-upgrades/chunks/H03.md) | Flexbox Container | [source](https://help.elegantthemes.com/en/articles/8671187-using-divi-sizing-options) |
| S4-06 | Fullwidth Section | STRUCTURE_REVIEW | [F00](module-upgrades/chunks/F00.md) | Validated nested container structure | [source](https://help.elegantthemes.com/en/articles/8671187-using-divi-sizing-options) |
| S4-07 | Specialty Section | STRUCTURE_REVIEW | [F00](module-upgrades/chunks/F00.md) | Validated nested container structure | [source](https://help.elegantthemes.com/en/articles/8671187-using-divi-sizing-options) |
| T4-01 | DiviGear Blog Grid | PARTIAL | [P07](module-upgrades/chunks/P07.md) | Current Ace Post Grid with separately gated future modes | [source](https://www.divigear.com/docs/blog-module-overview/) |

## Child-module accounting

Existing code consumes `et_pb_tab` under Tabs, `et_pb_accordion_item` under Accordion, `et_pb_contact_field` under Contact Form, `et_pb_map_pin` under Map, social network items under Social Media Follow and `et_pb_pricing_table` under Pricing Tables. These are not all independent renderers or accepted full-fidelity features. New bar/slide/video/opt-in child tags must come from fixtures; do not infer names from UI labels. Existing registered inner row/column aliases are included in the structural inventory.

## Optional vendor candidates

The 15 identities below appear in the inspected DiviGear docs navigation. Their compatible version, actual export use, schema and target parity remain unconfirmed. This is a bounded vendor sample, not a complete marketplace inventory. Other vendors get entries only when scanner/export evidence supplies an identity. [Vendor documentation](https://www.divigear.com/docs/blog-module-overview/).

| ID | Source module / variant | Current status | Chunk | Proposed Elementor target | Evidence |
|---|---|---|---|---|---|
| V-01 | DiviGear Product Carousel | RESEARCH_ONLY | [V01](module-upgrades/chunks/V01.md) | Pro product loop carousel / provider adapter | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-02 | DiviGear Blog Carousel | RESEARCH_ONLY | [V01](module-upgrades/chunks/V01.md) | Pro Loop Carousel / Ace dynamic slider | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-03 | DiviGear Carousel | RESEARCH_ONLY | [V01](module-upgrades/chunks/V01.md) | Pro Nested Carousel / compatible adapter | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-04 | DiviGear Advanced Tabs | RESEARCH_ONLY | [V02](module-upgrades/chunks/V02.md) | Nested Tabs if semantically equivalent | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-05 | DiviGear Advanced Heading | RESEARCH_ONLY | [V02](module-upgrades/chunks/V02.md) | Heading composition | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-06 | DiviGear Animated Heading | RESEARCH_ONLY | [V02](module-upgrades/chunks/V02.md) | Pro Animated Headline only for equivalent behavior | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-07 | DiviGear Advanced Gallery | RESEARCH_ONLY | [V03](module-upgrades/chunks/V03.md) | Pro Gallery / provider adapter | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-08 | DiviGear Justified Gallery | RESEARCH_ONLY | [V03](module-upgrades/chunks/V03.md) | Verified justified gallery provider | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-09 | DiviGear Packery Gallery | RESEARCH_ONLY | [V03](module-upgrades/chunks/V03.md) | Provider-specific packery adapter | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-10 | DiviGear CPT Grid | RESEARCH_ONLY | [V04](module-upgrades/chunks/V04.md) | Pro Loop Grid / generalized destination query renderer | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-11 | DiviGear Filterable CPT | RESEARCH_ONLY | [V04](module-upgrades/chunks/V04.md) | Loop Grid + actual taxonomy filter or provider adapter | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-12 | DiviGear CPT Carousel | RESEARCH_ONLY | [V04](module-upgrades/chunks/V04.md) | Loop Carousel / query-capable adapter | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-13 | DiviGear Popup | RESEARCH_ONLY | [V05](module-upgrades/chunks/V05.md) | Pro Popup or explicitly supported provider | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-14 | DiviGear Table | RESEARCH_ONLY | [V05](module-upgrades/chunks/V05.md) | Native semantic table / editable table adapter | [source](https://www.divigear.com/docs/blog-module-overview/) |
| V-15 | DiviGear Data Table | RESEARCH_ONLY | [V05](module-upgrades/chunks/V05.md) | Validated data-table provider with real sorting/filtering | [source](https://www.divigear.com/docs/blog-module-overview/) |

## Optional Divi 5 candidates

These 24 documented names need D5-00 format acceptance before adapter work. Ordinary Divi 4 families will also need re-auditing under the new format; this table is the additional candidate set, not every Divi 5 module. [Divi 5 catalogue](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules).

| ID | Source module / variant | Current status | Chunk | Proposed Elementor target | Evidence |
|---|---|---|---|---|---|
| D5-01 | Group | OUT_OF_SCOPE_DIVI5 | [D5-01](module-upgrades/chunks/D5-01.md) | Container | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-02 | Group Carousel | OUT_OF_SCOPE_DIVI5 | [D5-01](module-upgrades/chunks/D5-01.md) | Nested Carousel | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-03 | Hero | OUT_OF_SCOPE_DIVI5 | [D5-01](module-upgrades/chunks/D5-01.md) | Native hero container composition | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-04 | Icon List | OUT_OF_SCOPE_DIVI5 | [D5-02](module-upgrades/chunks/D5-02.md) | Icon List | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-05 | Lottie | OUT_OF_SCOPE_DIVI5 | [D5-02](module-upgrades/chunks/D5-02.md) | Pro Lottie / verified provider | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-06 | SVG | OUT_OF_SCOPE_DIVI5 | [D5-02](module-upgrades/chunks/D5-02.md) | Sanitized SVG/media adapter | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-07 | Pagination | OUT_OF_SCOPE_DIVI5 | [D5-03](module-upgrades/chunks/D5-03.md) | Query-associated pagination adapter | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-08 | Post Carousel | OUT_OF_SCOPE_DIVI5 | [D5-03](module-upgrades/chunks/D5-03.md) | Loop Carousel / query slider | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-09 | Post Filter | OUT_OF_SCOPE_DIVI5 | [D5-03](module-upgrades/chunks/D5-03.md) | Taxonomy Filter / query filter adapter | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-10 | Post Filter Items | OUT_OF_SCOPE_DIVI5 | [D5-04](module-upgrades/chunks/D5-04.md) | Children of a verified filter adapter | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-11 | Link | OUT_OF_SCOPE_DIVI5 | [D5-04](module-upgrades/chunks/D5-04.md) | Link/native composition with semantic link | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-12 | Dropdown | OUT_OF_SCOPE_DIVI5 | [D5-04](module-upgrades/chunks/D5-04.md) | Verified dropdown/menu adapter | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-13 | Before/After Image | OUT_OF_SCOPE_DIVI5 | [D5-05](module-upgrades/chunks/D5-05.md) | Dedicated comparison widget/provider | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-14 | Tooltip | OUT_OF_SCOPE_DIVI5 | [D5-05](module-upgrades/chunks/D5-05.md) | Accessible tooltip adapter | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-15 | Timeline | OUT_OF_SCOPE_DIVI5 | [D5-05](module-upgrades/chunks/D5-05.md) | Timeline provider/native composition | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-16 | Table of Contents | OUT_OF_SCOPE_DIVI5 | [D5-06](module-upgrades/chunks/D5-06.md) | Pro Table of Contents | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-17 | Chart | OUT_OF_SCOPE_DIVI5 | [D5-06](module-upgrades/chunks/D5-06.md) | Chart provider/accessible chart adapter | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-18 | Breadcrumbs | OUT_OF_SCOPE_DIVI5 | [D5-06](module-upgrades/chunks/D5-06.md) | Verified theme/SEO breadcrumbs adapter | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-19 | Contact Form 7 Styler | OUT_OF_SCOPE_DIVI5 | [D5-07](module-upgrades/chunks/D5-07.md) | Registered CF7 form + style adapter | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-20 | Gravity Form | OUT_OF_SCOPE_DIVI5 | [D5-07](module-upgrades/chunks/D5-07.md) | Registered Gravity Forms form + style adapter | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-21 | Imagely Gallery | OUT_OF_SCOPE_DIVI5 | [D5-07](module-upgrades/chunks/D5-07.md) | Registered gallery integration/provider | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-22 | Canvas Portal | OUT_OF_SCOPE_DIVI5 | [D5-08](module-upgrades/chunks/D5-08.md) | Pro Off-Canvas/Popup only if equivalent | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-23 | Instagram Feed | OUT_OF_SCOPE_DIVI5 | [D5-08](module-upgrades/chunks/D5-08.md) | Authorized feed provider | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |
| D5-24 | Payment Button | OUT_OF_SCOPE_DIVI5 | [D5-08](module-upgrades/chunks/D5-08.md) | Actual payment-link/provider widget | [source](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) |

## Not additional mandatory source modules

Spacer, Menu Anchor, Icon List, Site Logo, Author Box, Featured Image, Post Info, Archive Posts, Loop Grid and Loop Carousel may be destination building blocks. Their existence does not imply separate corresponding Divi 4 module tags. Use native compositions where source structure requires them, and record optional providers as dependencies rather than expanding the source list through guessed names. A source stylesheet/DOM screenshot does not recover missing posts, media binaries, projects, menu hierarchy, sidebars, custom-font files, form integration records or secrets.

## Source references and code evidence

- [Divi 4 module catalogue](https://help.elegantthemes.com/en/collections/165949-divi-modules) — Names/existence, not export schema; 89 articles include integration/setup articles, so not 89 modules.
- [Elementor widget catalogue and plan groups](https://elementor.com/resources/site-builder/) — Candidate target names; installed capability/plan detection remains required.
- [Elementor widget data structure](https://developers.elementor.com/docs/data-structure/widget-element.html) — Settings must correspond to actual target controls; do not invent saved JSON.
- [Elementor nested Accordion](https://elementor.com/help/accordion-widget-with-nested-elements/) — Candidate nested output architecture.
- [Elementor nested Tabs](https://elementor.com/help/tabs-with-nested-containers/) — Candidate nested output architecture.
- [Divi cart/checkout modules](https://www.elegantthemes.com/blog/theme-releases/divi-woocommerce-cart-checkout) — Source components are separate; proposed safe compound mapping is an engineering decision.
- [Elementor Cart widget](https://elementor.com/blog/how-to-use-woocommerce-cart-widget/) — Compound target requires verified source-node consumption and styling parity.
- [Elementor WooCommerce widgets](https://elementor.com/help/woocommerce-widgets-pro/) — Candidate target names and context.
- [DiviGear catalogue and blog options](https://www.divigear.com/docs/blog-module-overview/) — Vendor identities/options, not proof of Divi 4 version or actual export use.
- [Divi 5 catalogue](https://help.elegantthemes.com/en/collections/10650978-divi-5-modules) — Optional future format only; not merged into Divi 4 support.

Local code evidence: `src/Mapping/ModuleRegistry.php`, `CommonConverter.php`, `src/Elementor/DocumentBuilder.php`, `TargetResolver.php`, `Validator.php`, `src/Reports/JobOutcome.php`, `src/Forms/DiviContactFormParser.php`, `EmailOptinConverter.php`, `src/Style/StickyTranslator.php`, `docs/FEATURE-QA.csv`, `COMPATIBILITY.md` and existing original-export QA. The installed QA Elementor `includes/widgets/audio.php` is named `audio` but titled SoundCloud: it is not proof of arbitrary MP3 conversion. Nested Tabs/Accordion classes are present locally; this does not prove source nesting parity. Relevant source code and current registry were fingerprinted in private research evidence.

## Completion meaning

Research/list creation is complete. Every implementation checkbox remains open. No converter/runtime was changed, no ZIP rebuilt and no live/staging content was modified. A zero-future-error guarantee cannot be established; fixture contracts, explicit scope, protected fallbacks and per-chunk acceptance reduce the risk and make failures diagnosable.
