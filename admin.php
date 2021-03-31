<?php
defined( 'ABSPATH' ) OR die();

global $wpdb;

$total_checkins = $wpdb->get_var( 'SELECT count(id) from ' . $wpdb->prefix . 'untappd_checkins' );
$total_rating = $wpdb->get_var( 'SELECT avg(checkin_rating) from ' . $wpdb->prefix . 'untappd_checkins WHERE checkin_rating > 0' );
$total_latest = $wpdb->get_var( 'SELECT checkin_time from ' . $wpdb->prefix . 'untappd_checkins ORDER BY checkin_time DESC LIMIT 1' );

$days7_checkins = $wpdb->get_var( 'SELECT count(id) from ' . $wpdb->prefix . 'untappd_checkins WHERE checkin_time BETWEEN NOW() - INTERVAL 7 DAY AND NOW()' );
$days7_rating = $wpdb->get_var( 'SELECT avg(checkin_rating) from ' . $wpdb->prefix . 'untappd_checkins WHERE checkin_rating > 0 AND checkin_time BETWEEN NOW() - INTERVAL 7 DAY AND NOW()' );

$days30_checkins = $wpdb->get_var( 'SELECT count(id) from ' . $wpdb->prefix . 'untappd_checkins WHERE checkin_time BETWEEN NOW() - INTERVAL 30 DAY AND NOW()' );
$days30_rating = $wpdb->get_var( 'SELECT avg(checkin_rating) from ' . $wpdb->prefix . 'untappd_checkins WHERE checkin_rating > 0 AND checkin_time BETWEEN NOW() - INTERVAL 30 DAY AND NOW()' );

// Users
$popular = $wpdb->get_results( 'SELECT checkin_user_id, count(*) as counter FROM ' . $wpdb->prefix . 'untappd_checkins GROUP BY checkin_user_id ORDER BY count(*) DESC, checkin_user_id DESC LIMIT 20', OBJECT );
foreach ($popular as $user) {
    $users_popular[$user->checkin_user_id]['counter'] = $user->counter;
}

$loyal = $wpdb->get_results( 'SELECT checkin_user_id, AVG(checkin_rating) as average, COUNT(checkin_rating) as counter
    FROM ' . $wpdb->prefix . 'untappd_checkins
    WHERE checkin_rating > 0
    GROUP BY checkin_user_id
    HAVING COUNT(checkin_rating) > 10
    ORDER BY AVG(checkin_rating) DESC, counter DESC
LIMIT 40' );
foreach ($loyal as $user) {
    $users_loyal[$user->checkin_user_id]['average'] = $user->average;
    $users_loyal[$user->checkin_user_id]['counter'] = $user->counter;
}

$user_ids = array_unique(array_merge(array_keys($users_popular), array_keys($users_loyal)));
$users = $wpdb->get_results( 'SELECT checkin_user_id, checkin_user_data FROM ' . $wpdb->prefix . 'untappd_checkins WHERE checkin_user_id IN(' . implode(',', $user_ids) . ')', OBJECT_K );

foreach ($users as $user_id => $user_data) {
    $data = json_decode($user_data->checkin_user_data);
    if ( ! empty( $users_popular[$user_id] ) ) {
        $users_popular[$user_id]['name'] = $data->user_name;
    }
    if ( ! empty( $users_loyal[$user_id] ) ) {
        $users_loyal[$user_id]['name'] = $data->user_name;
    }
}

// All places
$venues = $wpdb->get_results( 'SELECT venue_id, venue_name, venue_city, venue_country, venue_data FROM ' . $wpdb->prefix . 'untappd_venues', OBJECT_K );

// Places
$popular = $wpdb->get_results( 'SELECT checkin_venue_id, count(*) as counter FROM ' . $wpdb->prefix . 'untappd_checkins WHERE checkin_venue_id IS NOT NULL GROUP BY checkin_venue_id ORDER BY count(*) DESC LIMIT 20', OBJECT );
foreach ($popular as $place) {
    $places_popular[$place->checkin_venue_id]['counter'] = $place->counter;
}

$popular = $wpdb->get_results( 'SELECT checkin_venue_id, count(*) as counter FROM ' . $wpdb->prefix . 'untappd_checkins WHERE checkin_venue_id IS NOT NULL AND checkin_time BETWEEN NOW() - INTERVAL 30 DAY AND NOW() GROUP BY checkin_venue_id ORDER BY count(*) DESC LIMIT 10', OBJECT );
foreach ($popular as $place) {
    $places_days30_popular[$place->checkin_venue_id]['counter'] = $place->counter;
}

$purchased = $wpdb->get_results( 'SELECT checkin_retail_venue_id, count(*) as counter FROM ' . $wpdb->prefix . 'untappd_checkins WHERE checkin_retail_venue_id IS NOT NULL GROUP BY checkin_retail_venue_id ORDER BY count(*) DESC LIMIT 20', OBJECT );
foreach ($purchased as $place) {
    $places_purchased[$place->checkin_retail_venue_id]['counter'] = $place->counter;
}

$purchased = $wpdb->get_results( 'SELECT checkin_retail_venue_id, count(*) as counter FROM ' . $wpdb->prefix . 'untappd_checkins WHERE checkin_retail_venue_id IS NOT NULL AND checkin_time BETWEEN NOW() - INTERVAL 30 DAY AND NOW() GROUP BY checkin_retail_venue_id ORDER BY count(*) DESC LIMIT 20', OBJECT );
foreach ($purchased as $place) {
    $places_days30_purchased[$place->checkin_retail_venue_id]['counter'] = $place->counter;
}

$loyal = $wpdb->get_results( 'SELECT checkin_venue_id, AVG(checkin_rating) as average, COUNT(checkin_rating) as counter
    FROM ' . $wpdb->prefix . 'untappd_checkins
    WHERE checkin_rating > 0 AND checkin_venue_id IS NOT NULL
    GROUP BY checkin_venue_id
    HAVING COUNT(checkin_rating) > 25
    ORDER BY AVG(checkin_rating) DESC, counter DESC
LIMIT 20' );
foreach ($loyal as $place) {
    $places_loyal[$place->checkin_venue_id]['average'] = $place->average;
    $places_loyal[$place->checkin_venue_id]['counter'] = $place->counter;
}

$loyal = $wpdb->get_results( 'SELECT checkin_venue_id, AVG(checkin_rating) as average, COUNT(checkin_rating) as counter
    FROM ' . $wpdb->prefix . 'untappd_checkins
    WHERE checkin_rating > 0 AND checkin_venue_id IS NOT NULL AND checkin_time BETWEEN NOW() - INTERVAL 30 DAY AND NOW() 
    GROUP BY checkin_venue_id
    HAVING COUNT(checkin_rating) > 10
    ORDER BY AVG(checkin_rating) DESC, counter DESC
LIMIT 10' );
foreach ($loyal as $place) {
    $places_days30_loyal[$place->checkin_venue_id]['average'] = $place->average;
    $places_days30_loyal[$place->checkin_venue_id]['counter'] = $place->counter;
}

foreach ($venues as $venue) {
    if ( ! empty( $places_popular[$venue->venue_id] ) ) {
        $places_popular[$venue->venue_id]['name'] = $venue->venue_name;
    }
    if ( ! empty( $places_days30_popular[$venue->venue_id] ) ) {
        $places_days30_popular[$venue->venue_id]['name'] = $venue->venue_name;
    }
    if ( ! empty( $places_loyal[$venue->venue_id] ) ) {
        $places_loyal[$venue->venue_id]['name'] = $venue->venue_name;
    }
    if ( ! empty( $places_days30_loyal[$venue->venue_id] ) ) {
        $places_days30_loyal[$venue->venue_id]['name'] = $venue->venue_name;
    }
    if ( ! empty( $places_purchased[$venue->venue_id] ) ) {
        $places_purchased[$venue->venue_id]['name'] = $venue->venue_name;
    }
    if ( ! empty( $places_days30_purchased[$venue->venue_id] ) ) {
        $places_days30_purchased[$venue->venue_id]['name'] = $venue->venue_name;
    }
}
?>
<div class="wrap">
    <h1>Untappd Tools</h1>

    <div id="poststuff" class="metabox-holder has-right-sidebar">
        <div class="inner-sidebar">
            <div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">

                <div id="sm_pnres" class="postbox">
                    <h3 class="hndle"><span>Database stats</span></h3>
                    <div class="inside">
                        <p>
                            Total checkins: <strong><?php echo $total_checkins; ?></strong><br>
                            Average rating: <strong><?php echo round( $total_rating, 2 ); ?></strong><br>
                            Latest checkin: <strong><?php echo $total_latest; ?></strong>
                        </p>
                    </div>
                </div>

                <div id="sm_pnres" class="postbox">
                    <h3 class="hndle"><span>Last 7 days</span></h3>
                    <div class="inside">
                        <p>
                            Total checkins: <strong><?php echo $days7_checkins; ?></strong><br>
                            Average rating: <strong><?php echo round( $days7_rating, 2 ); ?></strong>
                        </p>
                    </div>
                </div>

                <div id="sm_pnres" class="postbox">
                    <h3 class="hndle"><span>Last 30 days</span></h3>
                    <div class="inside">
                        <p>
                            Total checkins: <strong><?php echo $days30_checkins; ?></strong><br>
                            Average rating: <strong><?php echo round( $days30_rating, 2 ); ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="has-sidebar sm-padded">

            <div id="post-body-content" class="has-sidebar-content">

                <div class="meta-box-sortabless">

                    <div class="postbox">
                        <h3 class="hndle"><span>Loyal users</span></h3>
                        <div class="inside" style="display: flex; flex-wrap: wrap;">
                            <div style="margin-right: 5rem">
                                <h3>Checkins</h3>
                                <ol>
                                <?php foreach ( $users_popular as $up ) : ?>
                                    <li><?php echo $up['name'] ?> — <?php echo $up['counter'] ?></li>
                                <?php endforeach; ?>
                                </ol>
                            </div>
                            <div>
                                <h3>Rating</h3>
                                <ol style="column-count: 2; column-gap: 20px;">
                                <?php foreach ( $users_loyal as $up ) : ?>
                                    <li><?php echo $up['name'] ?> — <?php echo round($up['average'], 3) ?> (<?php echo $up['counter'] ?>)</li>
                                <?php endforeach; ?>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle"><span>Loyal places</span></h3>
                        <div class="inside" style="display: flex; flex-wrap: wrap;">
                            <div style="margin-right: 5rem">
                                <h3>Last 30 days checkins</h3>
                                <ol>
                                <?php foreach ( $places_days30_popular as $pp ) : ?>
                                    <li><?php echo $pp['name'] ?> — <?php echo $pp['counter'] ?></li>
                                <?php endforeach; ?>
                                </ol>
                            </div>
                            <div>
                                <h3>Last 30 days rating</h3>
                                <ol>
                                <?php foreach ( $places_days30_loyal as $pp ) : ?>
                                    <li><?php echo $pp['name'] ?> — <?php echo round( $pp['average'], 3 ) ?> (<?php echo $pp['counter'] ?>)</li>
                                <?php endforeach; ?>
                                </ol>
                            </div>
                        </div>
                        <div class="inside" style="display: flex; flex-wrap: wrap;">
                            <div style="margin-right: 5rem">
                                <h3>Total checkins</h3>
                                <ol>
                                <?php foreach ( $places_popular as $pp ) : ?>
                                    <li><?php echo $pp['name'] ?> — <?php echo $pp['counter'] ?></li>
                                <?php endforeach; ?>
                                </ol>
                            </div>
                            <div>
                                <h3>Total rating</h3>
                                <ol>
                                <?php foreach ( $places_loyal as $pp ) : ?>
                                    <li><?php echo $pp['name'] ?> — <?php echo round( $pp['average'], 3 ) ?> (<?php echo $pp['counter'] ?>)</li>
                                <?php endforeach; ?>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle"><span>Purchased</span></h3>
                        <div class="inside" style="display: flex; flex-wrap: wrap;">
                            <div style="margin-right: 5rem">
                                <h3>Last 30 days</h3>
                                <ol>
                                <?php foreach ( $places_days30_purchased as $pp ) : ?>
                                    <li><?php echo $pp['name'] ?> — <?php echo $pp['counter'] ?></li>
                                <?php endforeach; ?>
                                </ol>
                            </div>
                            <div>
                                <h3>Total</h3>
                                <ol>
                                <?php foreach ( $places_purchased as $pp ) : ?>
                                    <li><?php echo $pp['name'] ?> — <?php echo $pp['counter'] ?></li>
                                <?php endforeach; ?>
                                </ol>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
<?php
?>
</div>