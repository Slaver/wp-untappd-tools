<?php
defined( 'ABSPATH' ) OR die();

// Find all beers
$beers = [];
$query = new WP_Query( [
    'post_type' => 'beers',
    'posts_per_page' => -1,
    'meta_key' => 'beer_untappd_url'
] );
foreach ( $query->posts as $post ) :
    $untappd_data = get_field( 'beer_untappd_url', $post->ID );
    $untappd_url = explode( '/', $untappd_data );
    $untappd_id = end( $untappd_url );
    if ( is_numeric($untappd_id) ):
        $beers[$untappd_id] = $post->ID;
    endif;
endforeach;

// Load data
$untappd = new UntappdPHP( $options['client_id'], $options['client_secret'], '' );
$data = $untappd->get( '/brewery/checkins/' . $options['brewery_id'] . '?compact=false' );
if ( ! empty( $data->response->checkins ) ) :
    foreach ( $data->response->checkins->items as $checkin ) :
        if ( ! empty($beers[$checkin->beer->bid] ) ) :
            $exists = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'untappd_checkins WHERE checkin_id = "' . $checkin->checkin_id . '"', OBJECT );

            $fileds = [
                'post_id' => $beers[$checkin->beer->bid],
                'checkin_beer_id' => $checkin->beer->bid,
                'checkin_rating' => $checkin->rating_score,
                'checkin_comment' => ( $checkin->checkin_comment ) ?: NULL,
                'checkin_venue_id' => ( ! empty( $checkin->venue->venue_id ) ) ? $checkin->venue->venue_id : NULL,
                'checkin_media' => ( $checkin->media->count > 0 ) ? json_encode( $checkin->media ) : NULL,
                'checkin_retail_venue_id' => ( ! empty( $checkin->retail_venue->venue_id ) ) ? $checkin->retail_venue->venue_id : NULL,
            ];

            if ( ! empty( $checkin->venue->venue_id ) ) :
                $exists_venue = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'untappd_venues WHERE venue_id = "' . $checkin->venue->venue_id . '"', OBJECT );

                if ( ! $exists_venue ) :
                    $wpdb->insert($wpdb->prefix . 'untappd_venues', [
                        'venue_id' => $checkin->venue->venue_id,
                        'venue_name' => $checkin->venue->venue_name,
                        'venue_city' => (!empty($checkin->venue->location->venue_city)) ? $checkin->venue->location->venue_city : NULL,
                        'venue_country' => (!empty($checkin->venue->location->venue_country)) ? $checkin->venue->location->venue_country : NULL,
                        'venue_data' => (!empty($checkin->venue)) ? json_encode($checkin->venue) : NULL,
                    ]);
                endif;
            endif;

            if ( ! empty( $checkin->retail_venue->venue_id ) ) :
                $exists_retail_venue = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'untappd_venues WHERE venue_id = "' . $checkin->retail_venue->venue_id . '"', OBJECT );

                if ( ! $exists_retail_venue ) :
                    $wpdb->insert($wpdb->prefix . 'untappd_venues', [
                        'venue_id' => $checkin->retail_venue->venue_id,
                        'venue_name' => $checkin->retail_venue->venue_name,
                        'venue_city' => (!empty($checkin->retail_venue->location->venue_city)) ? $checkin->retail_venue->location->venue_city : NULL,
                        'venue_country' => (!empty($checkin->retail_venue->location->venue_country)) ? $checkin->retail_venue->location->venue_country : NULL,
                        'venue_data' => (!empty($checkin->retail_venue)) ? json_encode($checkin->retail_venue) : NULL,
                    ]);
                endif;
            endif;

            if ( ! $exists ):
                $insert = [
                    'added_date' => date('Y-m-d H:i:s', time()),
                    'checkin_id' => $checkin->checkin_id,
                    'checkin_time' => date('Y-m-d H:i:s', strtotime($checkin->created_at)),
                    'checkin_user_id' => $checkin->user->uid,
                    'checkin_user_data' => json_encode($checkin->user),
                ];
                $wpdb->insert($wpdb->prefix . 'untappd_checkins', array_merge($insert, $fileds));
            else :
                if ( $exists->checkin_rating != $checkin->rating_score || $exists->checkin_comment != $checkin->checkin_comment ) :
                    $update = [
                        'updated_date' => date('Y-m-d H:i:s', time()),
                        'updated_data' => json_encode([
                            'checkin_rating' => $exists->checkin_rating,
                            'checkin_comment' => $exists->checkin_comment,
                        ]),
                    ];
                    $wpdb->update( $wpdb->prefix . 'untappd_checkins', array_merge($update, $fileds), [ 'id' => $exists->id ] );
                endif;
            endif;
        endif;
    endforeach;
else:
    error_log( $data->meta->error_detail, 3, UT_LOG );
endif;