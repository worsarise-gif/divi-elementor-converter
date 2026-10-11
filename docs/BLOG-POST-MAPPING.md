# Blog posts and core Blog module mapping

Evidence before implementation: the official Divi 4.8.2 Brokerage style guide contains `et_pb_blog`, whose selected preset sets `fullwidth=off`, separate grid tile background, h3 titles and responsive header/body/meta/pagination typography. The installed Elementor 4.3.3 widget/control schema and WordPress destination post type are the runtime contract. A customer individual post export is tested privately; customer content is not distributed.

## Target contract

An ordinary WordPress `post` is a separate explicit target kind from pages and UAE Header/Footer templates. Existing destinations keep post content, title, slug, author, dates, excerpt, categories, tags, featured image, SEO and publication state; migration writes Elementor layout and disables Divi rendering only. New post destinations are drafts, created only on Apply. Layout JSON does not import a site's post database or Theme Builder single-post display rules. Use WordPress export/import first to bring across post records where necessary, then map each layout to its corresponding post.

Saved Apply binds both destination ID and kind, verifies the saved output and target fingerprint, uses existing locks and backups, and never creates another conversion job. Ordinary post status is included in the fingerprint to protect a post published after analysis. Rollback restores only migrated fields, retaining independent metadata. Clearing jobs does not delete posts.

## Core Blog adapter contract

`et_pb_blog` is distinct from `dgbm_blog_module`. Convert ordinary published-post listings to the editable Ace Post Grid widget, with fullwidth list and responsive masonry layouts, bounded count/offset, independent pagination, featured image, title HTML level, author/date/categories/comments, manual/automatic character excerpts, read-more and source typography. Module background and grid-tile surface must remain separate. Grid columns adapt to the source column context. Destination category IDs require explicit source-file-scoped mapping and are revalidated before Apply and rendering; source IDs are never copied automatically. Missing mappings retain the complete source module and block Apply.

Full-content feeds, current-archive queries, other post types, custom image overlay icons and unknown query modes remain protected until fixture-backed adapters exist. Do not run nested full-post rendering as an excerpt fallback. When an Elementor-converted post has no manual excerpt, derive text safely from its saved text/heading widgets without invoking dynamic widgets, shortcode handlers or content filters. Keep the migrator active for Ace Post Grid rendering.

## Acceptance

Test the actual customer post, official preset, responsive typography, list/masonry, zero offsets, Unicode excerpts, source-to-destination categories and deleted mappings, independent offset-aware pagination, published-only results, post metadata preservation, existing/new post targets, mixed page/post batches, saved Apply, duplicate targets, wrong target kinds, permission/support checks, rollback and cleared jobs. Re-run existing DiviGear and page/UAE tests. Frontend and native-control checks do not certify an untested live Divi or Elementor editor session.

Primary references: [Divi Blog module](https://help.elegantthemes.com/en/articles/8617111-the-divi-blog-module), [grid and fullwidth layouts](https://www.elegantthemes.com/blog/divi-resources/comparing-divis-grid-fullwidth-blog-module-layouts), [WordPress WP_Query](https://developer.wordpress.org/reference/classes/wp_query/).

Featured images use Divi's documented default 400x250 (8:5) crop, with editable ratio/fit controls for source sites whose custom cropping filters are outside JSON. Reference: [Divi featured-image cropping](https://help.elegantthemes.com/en/articles/2208372-how-to-remove-the-default-cropping-of-the-post-s-featured-image-in-the-blog-module).
