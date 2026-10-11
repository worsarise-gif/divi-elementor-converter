# Home template import regression - 1.2.0-rc.6

The user imported the downloaded JSON into Elementor on the staging site. Public browser inspection confirmed three visible headline variants, default Roboto headings, extra container padding/gaps and missing supplemental styles. The reference measurements are from [Reference Home page](https://suiteedge.com/); the baseline was read without changing either public site.

## Causes and repairs

| Failure | Repair | Verification |
|---|---|---|
| Template insertion changes every Elementor ID | Scope supplemental CSS to document-specific custom classes that survive insertion | Actual Elementor template library save/insertion, both ID rewrites |
| Supplemental stylesheet exported as a root widget; absent on staging | Store the HTML stylesheet inside the first valid root container and hide its layout footprint | Home exports twelve root containers; stylesheet retained and rendered after insertion |
| Heading selectors require the removed widget-container wrapper | Target H1-H6 and links directly beneath the scoped widget | Optimized/legacy selector regression; native heading font controls register the font |
| Elementor defaults add container padding and widget gaps | Set explicit zero container padding and gaps; preserve exported overrides | Serialized controls and registered-control validation |
| Blank dimension sides cause Elementor to omit supplied margins/padding | Supplemental per-side declarations preserve specified sides and inheritance | Partial 75px row margin and negative badge margins |
| Unequal Divi columns lose internal gutters | Use Divi's spanning-column calculation: 3/5 + 2/5 with 8% gutter gives 56.8% + 35.2% | Standalone regression; matches measured 727px / 451px reference tracks at 1280px |
| Badge width creates a narrow wrapper at the start of its column; Elementor resets its negative bottom margin | Align the capped wrapper and match widget-reset specificity for partial dimensions | Six-width browser checks; 45px overlap restored at desktop widths |
| Image border is applied to the widget wrapper | Use Elementor's actual image border-radius control | Native control validation and source photo radius regression |
| Button padding/borders affect both wrapper and button | Apply them to the native button surface | Native controls and standalone regression |
| Divi theme defaults are absent from the layout export | Expose editable source theme defaults; suggest measured reference-site values for its source origin | Validated fields, origin isolation and injection rejection |
| Apply action is difficult to discover | Show separate Analyze and Apply to pages submit buttons | Actual form handler and authenticated HTTP analysis/apply/rollback |

## Source theme defaults

For the recognized reference-site origin, recommendations use a 1280px row maximum, Arial body font, Montserrat heading font, 18px default body size, #222222 default body color and #2BAFD8 accent. Explicit exported sizes, colors, font families and maximum widths take precedence. Other sources receive standard Divi defaults and must review their own Theme Customizer settings. No public HTML/CSS is fetched while rendering these suggestions.

Native heading-only text controls register the source font so Elementor can include its font stylesheet. A target host may block font downloads; font availability still requires checking. Supplemental styles stay inside the template and work with the migrator deactivated. Preserve their custom classes and the conversion styles widget when editing.

## Scope and remaining acceptance

The supplied Home export contains 239 page modules, including the final call to action. It does not contain the global navigation header, dark footer or third-party chat integration visible on the source page. Obtain the corresponding Divi Theme Builder exports and assign separate Elementor templates to reproduce those site elements. Header/footer assignment is not inferred from a page import.

Fourteen assertions exercise Elementor 4.3.3's actual server-side template save and insertion path on each disposable database backend. Home rendering, registered controls, media and rollback pass. These checks do not claim a manual editor interaction or pixel-perfect rendering.

The earlier browser/sign-in blocker is resolved. RC.5 passes 32 Edge layout assertions at six widths and 31 motion assertions, including public font loading in the layout run and all lazy parallax backgrounds. Current public staging inspection confirms the RC.4 headline styles, single responsive variant and 1280px centered tracks. Exact source/staging pixel equality, manual editor/video behavior and licensed integrations remain open. See REPORT-WARNINGS-RC5.md for source visibility details and the motion adapter's limits.

Use the regenerated Home.elementor.json for a fresh import into an empty staging page, or upload Home.json through this plugin and click Apply to pages after reviewing the analysis. RC.6 repairs button/title underlines on existing scoped imports through its active compatibility stylesheet; see LINK-DECORATION-REGRESSION.md. Earlier imports missing layout or heading styles still require a new conversion/import to obtain those stored settings.
