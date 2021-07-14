<?php

declare(strict_types=1);

namespace UntappdTools\Models;

use UntappdTools\Models;
use WP_Query;

class Posts extends Models
{
    const POSTS_META_UNTAPPD_URL = 'beer_untappd_url';

    /**
     * Beers constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->table = $this->wpdb->prefix.'posts';
    }

    /**
     * Get list of beers from WP DB
     * Using ACF Function `get_field`
     *
     * @return array|null
     */
    public function beers(): ?array
    {
        $beers = [];
        $query = new WP_Query([
            'post_type' => 'beers',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => self::POSTS_META_UNTAPPD_URL,
        ]);

        foreach ($query->posts as $post) {
            $untappdData = get_field(self::POSTS_META_UNTAPPD_URL, $post->ID);
            $untappdUrl = explode('/', $untappdData);
            $untappdId = end($untappdUrl);
            if (is_numeric($untappdId)) {
                $beers[$untappdId] = $post->ID;
            }
        }
        return $beers;
    }

    /**
     * Get post by title from WP DB
     * Better way is using `wpm_get_page_by_title` for getting page title
     * for WPML, but it doesn't work at API mode
     *
     * @param string $title
     * @return object|null
     */
    public function beer(string $title): ?object
    {
        $like = '%'.$this->wpdb->esc_like(esc_sql('[:be]'.$title.'[:')).'%';
        $page = $this->wpdb->get_var($this->wpdb->prepare("
            SELECT ID, post_title
            FROM $this->table
            WHERE post_title LIKE %s
            AND post_type = %s
        ", $like, 'beers'));

        return get_post($page, OBJECT);
    }
}