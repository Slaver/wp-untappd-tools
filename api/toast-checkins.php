<?php
defined( 'ABSPATH' ) OR die();

$checkins = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'untappd_checkins WHERE is_toasted = 0 AND is_deleted = 0 AND checkin_rating >= 4 AND checkin_time >= ( CURDATE() - INTERVAL 10 DAY ) ORDER BY checkin_time DESC LIMIT 20', OBJECT);

foreach ( $checkins as $checkin ):
    $untappd = new UntappdPHP( $options['client_id'], $options['client_secret'], '', $options['access_token'] );
    $result = $untappd->post( '/checkin/toast/' . $checkin->checkin_id );
    if ( ! empty( $result->response->result ) && $result->response->result == 'success' ) :
        $wpdb->update( $wpdb->prefix . 'untappd_checkins', [ 'is_toasted' => true ], [ 'id' => $checkin->id ] );
    else:
        if ( $result->meta->error_detail == 'This check-in doesn\'t exist. It may have been deleted by the user.' ) :
            $wpdb->update( $wpdb->prefix . 'untappd_checkins', [ 'is_deleted' => true ], [ 'id' => $checkin->id ] );
        elseif ($result->meta->error_detail == 'You are not authorized to toast this checkin.' ) :
            $wpdb->update( $wpdb->prefix . 'untappd_checkins', [ 'is_toasted' => '-1' ], [ 'id' => $checkin->id ] );
            error_log( $checkin->checkin_id.' - '.$result->meta->error_detail, 3, UT_LOG );
        else:
            error_log( $checkin->checkin_id . ' - ' . $result->meta->error_detail, 3, UT_LOG );
            break;
        endif;
    endif;
endforeach;