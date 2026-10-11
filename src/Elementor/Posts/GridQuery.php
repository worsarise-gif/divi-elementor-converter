<?php
namespace SuiteEdge\Migrator\Elementor\Posts;

/** Bounded destination-only post query. Never uses source numeric IDs. */
class GridQuery {
    public function args(array $settings, $page = 1) {
        $count = $this->integer($settings['posts_per_page'] ?? 12, 1, 100);
        $offset = $this->integer($settings['post_offset'] ?? 0, 0, 10000);
        $page = $this->integer($page, 1, 10000);
        $sort = $settings['post_order'] ?? 'recent';
        if (!in_array($sort, ['recent', 'popular', 'random'], true)) {
            throw new \UnexpectedValueException('Post grid: unsupported ordering.');
        }
        if ($sort === 'random' && ($settings['show_pagination'] ?? 'yes') === 'yes') {
            throw new \UnexpectedValueException('Post grid: random ordering cannot use stable pagination. Disable pagination or select recent/popular posts.');
        }
        $args = [
            'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => $count,
            'offset' => $offset + ($page - 1) * $count, 'ignore_sticky_posts' => true,
            'orderby' => $sort === 'popular' ? ['comment_count' => 'DESC', 'date' => 'DESC', 'ID' => 'DESC'] : ($sort === 'random' ? 'rand' : ['date' => 'DESC', 'ID' => 'DESC']),
            'order' => 'DESC', 'no_found_rows' => ($settings['show_pagination'] ?? 'yes') !== 'yes',
        ];
        $slugs = $settings['category_slugs'] ?? '';
        if (!is_string($slugs) || strlen($slugs) > 2000) {
            throw new \UnexpectedValueException('Post grid: invalid destination category slugs.');
        }
        $terms = array_values(array_unique(array_filter(array_map('trim', explode(',', $slugs)))));
        if (count($terms) > 50) { throw new \UnexpectedValueException('Post grid: too many category filters.'); }
        foreach ($terms as $term) {
            if (!preg_match('/^(?:[a-zA-Z0-9_-]|%[a-fA-F0-9]{2})+$/D', $term)) {
                throw new \UnexpectedValueException('Post grid: use destination category slugs, not IDs or query expressions.');
            }
        }
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Explicit category-slug filtering is required by the destination grid; capped at 50 terms and 100 published posts per page using WordPress taxonomy indexes.
        if ($terms) { $args['tax_query'] = [['taxonomy' => 'category', 'field' => 'slug', 'terms' => $terms, 'include_children' => true]]; }
        $ids=$settings['category_ids']??'';
        if(!is_string($ids)||strlen($ids)>600||($ids!==''&&!preg_match('/^[1-9][0-9]{0,9}(?:,[1-9][0-9]{0,9})*$/D',$ids))) {throw new \UnexpectedValueException('Core Blog: invalid mapped destination categories.');}
        if($ids!=='') {
            if($terms){throw new \UnexpectedValueException('Core Blog: choose mapped category IDs or category slugs, not both.');}
            $ids=array_values(array_unique(array_map('intval',explode(',',$ids))));if(count($ids)>50){throw new \UnexpectedValueException('Core Blog: too many categories.');}
            foreach($ids as $id){(new \SuiteEdge\Migrator\Import\BlogCategoryMapping())->target($id);}
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Explicit destination category IDs, capped at 50 and validated against category terms.
            $args['tax_query']=[['taxonomy'=>'category','field'=>'term_id','terms'=>$ids,'include_children'=>true]];
        }
        return $args;
    }
    public function pages($found, array $settings) {
        return (int) ceil(max(0, (int) $found - $this->integer($settings['post_offset'] ?? 0, 0, 10000)) / $this->integer($settings['posts_per_page'] ?? 12, 1, 100));
    }
    private function integer($value, $min, $max) {
        if (!(is_string($value) || is_int($value) || is_float($value)) || !preg_match('/^\d+$/D', (string) $value) || $value < $min || $value > $max) {
            throw new \UnexpectedValueException('Post grid: invalid post count, offset or page number.');
        }
        return (int) $value;
    }
}
