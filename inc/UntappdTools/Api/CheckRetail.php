<?php

declare(strict_types=1);

namespace UntappdTools\Api;

use UntappdTools;
use UntappdTools\Api;

class CheckRetail extends Api
{
    const CACHE_NAME = 'your_beer_on_sale';
    const YOUR_BEER_URL = 'https://your.beer/api/v1/breweries/';
    const POST_META_AVAILABLE_FIELD = 'beer_available_now';

    public function run(int $updateGap = HOUR_IN_SECONDS * 2)
    {
        $transient = get_transient(self::CACHE_NAME);
        $transientTimeout = (int)get_option('_transient_timeout_'.self::CACHE_NAME, 0);

        if (!$transient || ($transientTimeout && ($transientTimeout - time() < $updateGap))) {
            $data = wp_remote_get(self::YOUR_BEER_URL.$this->options['your_beer_id'].'?on-sale=on-sale');
            $transient = $data['body'];
            set_transient(self::CACHE_NAME, $transient, DAY_IN_SECONDS);
            delete_metadata('post', 0, self::POST_META_AVAILABLE_FIELD, false, true);
        }

        $beers = [];
        $data = json_decode($transient);

        foreach ($data->places as $place) {
            foreach ($place->beers as $beer) {
                if (!isset($beers[$beer->name])) {
                    $post = $this->posts->beer($beer->name);
                    if ($post) {
                        $beers[$beer->name] = $post;
                        update_post_meta($post->ID, self::POST_META_AVAILABLE_FIELD, '1');
                    }
                }
            }
        }
    }
}