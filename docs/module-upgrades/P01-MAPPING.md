# P01 mapping specification - Ace 1.3.0

This increment adds two bounded Divi 4 compositions: Call To Action (`et_pb_cta`) and Person (`et_pb_team_member`). Both use native Elementor Free containers/widgets and the existing portable conversion stylesheet. They do not require a new frontend widget provider. The installed control schema was checked on Elementor 4.3.3; source frontend parity and interactive editor acceptance on the publisher's staging site remain open.

## Evidence and source identity

The source tags were captured from actual Elegant Themes portability downloads, rather than inferred from CSS classes. `.et_pb_promo` is the CTA's CSS class; the exported shortcode is `et_pb_cta`. [CTA documentation](https://help.elegantthemes.com/en/articles/8624032-the-divi-call-to-action-module), [Person documentation](https://help.elegantthemes.com/en/articles/8643122-the-divi-person-module).

| Official source fixture | Export version | Selected source nodes | Use |
| --- | --- | --- | --- |
| [Virtual Summit landing page](https://www.elegantthemes.com/blog/divi-resources/how-to-create-a-virtual-summit-landing-page-with-divi) | 4.4.6 | 2 CTA, 5 Person | Divi 4 identity, portraits/social links, independently styled surfaces |
| [Team hover social icons](https://www.elegantthemes.com/blog/divi-resources/how-to-add-hover-social-icons-to-team-member-images-with-divi) | 4.9.0 | 6 Person | Divi 4 responsive name/role typography and custom spacing |
| [CTA hover effects](https://www.elegantthemes.com/blog/divi-resources/how-to-create-an-eye-catching-cta-in-divi-with-a-few-simple-hover-effects) | 3.24.1 | 3 CTA | Extra legacy regression; not counted as Divi 4 evidence |

Private fixture hashes, every captured attribute name, module versions and original-shortcode hashes are in `.qa-evidence/p01-source-manifest.json`. Source downloads and derived nodes are excluded from the distribution. Controlled negative and device/state fixtures live in the source-only tests. These samples do not supply a complete source-theme backup, every default/hover combination or the publisher's own customized pages.

The public original site's [CTA stylesheet](https://suiteedge.com/wp-content/themes/Divi/includes/builder/feature/dynamic-assets/assets/css/cta.css) and [Person stylesheet](https://suiteedge.com/wp-content/themes/Divi/includes/builder/feature/dynamic-assets/assets/css/team_member.css) were inspected to establish column/device defaults. Their snapshots and hashes are private QA evidence. CSS inspection is not a rendered Divi-versus-Elementor comparison.

## Common source-to-target rules

`_tablet` becomes `_tablet`; `_phone` becomes `_mobile`. The existing serializer compiles native responsive styles at Divi's 980px/767px breakpoints. Explicit values and zeros take precedence. Omitted typography uses the validated source-theme profile; custom font file availability remains the existing font preflight's responsibility. Body HTML stays in Text Editor, including its supported per-device content. Module-level background, width, margin, padding, border, shadow, motion and visibility remain on the outer container through existing translators; they are not copied onto every child.

| Source attribute/path | Native target or portable rule | Normalization / assertion |
| --- | --- | --- |
| `header_font`, `header_font_size`, `header_text_color`, `header_text_align`, `header_letter_spacing`, `header_line_height` | Heading `typography_*`, `title_color`, `align`; supplemental responsive/hover declarations | Preserve family/weight/style and active device values; source module hover reaches its heading child |
| `body_font`, `body_font_size`, `body_text_color`, `body_text_align`, `body_letter_spacing`, `body_line_height` | Text Editor `typography_*`, `text_color`, `align` plus scoped states | Separate from title/role typography; retain source body HTML and inherited weight |
| `text_orientation` and device variants | Child alignment defaults | Explicit per-surface alignment overrides the module default |
| `header_level` | Heading `header_size` | Only h1-h6 accepted; invalid levels preserve the original module |
| Source row/column structure | Internal `_column_context` | Record source fraction and row column count, including expanded global modules; old adapters ignore this context |
| Supported `custom_css_*` surface declarations and device/hover variants | Scoped CSS owned by the correct child/surface | `||` separators normalized; rules/markup containing `< > { } @` require review rather than being silently moved |
| Dynamic source links | Existing page-link resolver before conversion | Unresolved identities preserve the original module and block Apply |

## Call To Action

Structure: outer Container -> description Container -> Heading + Text Editor; sibling Button. Empty surfaces are omitted. No fake button label or destination is added.

| Source | Target | Behavior |
| --- | --- | --- |
| `title` | Heading `title` | Editable title; default level h2 |
| Inner body / `content_tablet` / `content_phone` | Text Editor `editor` | Editable HTML, existing per-device content handling |
| `button_text` + `button_url` | Button `text` + `link.url` | Render only when both are nonempty, matching Divi visibility |
| `url_new_window` | Button `link.is_external` | Preserve target choice |
| `custom_button` | Selection of active button settings | `off` ignores residual inactive custom styles; invalid flag requires review |
| Existing `button_font`, text size/color, alignment, letter spacing, line height, background/border and icon fields | Existing native Button controls + scoped device/hover rules | Source button styling stays separate from module styling; explicit button alignment wins |
| `custom_padding` and device variants | Outer Container `padding` | Resolve each blank side independently; retain explicit inherited sides and link flags |
| `background_color` / `background_enable_color` | Outer background and source padding default | Enabled color: 40px vertical, 60px horizontal in wide source columns; narrow columns/mobile: 40px all sides. Disabled/absent color: zero default padding. Explicit padding still wins |
| `custom_css_promo_title` | Heading title surface | Never applied to the entire card |
| `custom_css_promo_description` | Description Container | Includes the description's source 20px bottom spacing |
| `custom_css_promo_button` | Button anchor surface | Never applied to the title/body |

Unexported default button glyphs are reported for review. Native buttons keep their source default surface padding (0.3em vertical / 1em horizontal), independent of CTA module padding. A CTA with no declared background/text palette receives a warning to verify inherited source defaults. Complex whole-module links are protected instead of generating nested anchors. This increment does not claim Pro Call to Action equivalence.

## Person

Structure: outer Container -> optional Image + description Container -> name Heading, role text (native Heading with div tag), biography Text Editor, optional Social Icons.

| Source | Target | Behavior |
| --- | --- | --- |
| `name` | Heading `title` | Editable name; default h4; supplies portrait alt text |
| `position` / `position_*` typography | Separate native Heading `title`, `header_size=div`, typography/color | Body-sized role independent of name; source default gray and 7px bottom gap |
| `image_url` | Image `image.url` | Scanned as media; no image is invented when absent |
| Supported `border_radii_image`, uniform image border/shadow fields | Image border/radius/shadow controls | Remain on portrait surface; unsupported image variants preserve the original module |
| Source column + row context | Container `flex_direction` for each device and portrait sizing/gaps | Wide >=768: side by side; narrow and three-quarter desktop: stacked. Tablet returns side by side except four-column quarter rows. Mobile: stacked. Portrait wrapper capped at 320px except four-column quarter tablet rows; source 30px side/12px bottom gaps. Portrait alignment is centered in narrow desktop/mobile/quarter tablet surfaces, left otherwise |
| Inner body / supported per-device content | Text Editor `editor` | Retains all biography HTML; never flattened into a testimonial |
| `facebook_url`, `twitter_url`, `google_url`, `linkedin_url` | Social Icons repeater `social_icon`, `link`, colors | Keep all nonempty URLs/external links and native accessible network labels; Google+ deprecation warning |
| `custom_css_member_image` | Portrait wrapper | Independent of description/card styles |
| `custom_css_title` / `custom_css_member_position` | Name/role text | Separate from biography |
| `custom_css_member_description` | Description Container | Applies to the full source description surface |
| `custom_css_member_social_links` | Social Icons wrapper | Separate social surface; defaults 16px glyph, 15px gap, 20px top margin |

Native Font Awesome social glyphs approximate Divi ETicons rather than reproduce its font binary. Standalone module exports without their source column receive a placement warning. Custom linked names, whole-module links, nested modules and unaccepted portrait/surface modes remain protected review cases. Arbitrary image crop/overflow/overlap designs require source acceptance; they are not advertised as universally matched.

## Protected behavior and remaining acceptance

Unsupported critical surface fields and unsafe composition structures retain the exact original shortcode, with an actionable report. Compound responsive backgrounds/palettes and hover padding remain protected until those combinations are verified; Person's ordinary background-color hover is supported. Existing direct/saved Apply refuses that fallback. Supported saved output can be applied to the original assigned target without reconversion; fingerprints, locks and rollback remain in force.

Native template insertion exposed an existing validator mismatch: Elementor generates variable-length hexadecimal IDs. Validation now accepts 1-8 lowercase hexadecimal characters for native element/repeater IDs, while continuing to reject malformed and duplicate IDs. Ace's own deterministic IDs remain seven characters.

Automated semantics, installed schema/template insertion, destination frontend, correct-target saved bulk Apply and exact rollback are accepted for the tested subset. The publisher's source/converted visual comparison, actual editor interaction and long-copy/RTL/keyboard/slow-asset/custom-theme matrix remain open. See [P01 QA](../MODULE-UPGRADE-P01-QA.md); keep both modules classified PARTIAL until those gaps are accepted.
