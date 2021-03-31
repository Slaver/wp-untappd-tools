<?php
/*
Plugin Name: Untappd Tools
Text Domain: untappd-tools
Description: Untappd tools for WordPress
Author: Viacheslav Radionov
Author URI: https://rdnv.me
Version: 0.1
*/

// Exit
defined( 'ABSPATH' ) OR die();

// Constants
define( 'UT_DIR', plugin_dir_path(__FILE__) );
define( 'UT_GAP', 30 * MINUTE_IN_SECONDS );
define( 'UT_LOG', UT_DIR . 'debug.log' );

// Admin
function untappd_tools_admin_menu() {
    add_management_page( 'Untappd Tools', 'Untappd Tools', 'manage_options', 'untappd-tools', 'untappd_tools_admin_page' );
}

function untappd_tools_admin_page() {
    if ( is_admin() ) :
        require_once(UT_DIR . 'admin.php' );
    endif;
}
add_action( 'admin_menu', 'untappd_tools_admin_menu' );

// Cron
if ( WP_PRODUCTION ) :
    add_filter( 'cron_schedules', function ( $schedules ) {
        if ( ! isset($schedules['untappd_tools'] ) ){
            $schedules['untappd_tools'] = [
                'interval' => UT_GAP,
                'display'  => __( 'Every ' . UT_GAP . ' minutes' )
            ];
        }
        return $schedules;
    });

    if ( ! wp_next_scheduled( 'untappd_tools' )) {
        wp_schedule_event( time(), 'untappd_tools', 'untappd_tools' );
    }
    add_action( 'untappd_tools', 'untappd_tools_cron_run' );
endif;

function untappd_tools_cron_run() {
    global $wpdb;

    // @TODO options page
    $options = [
        'brewery_id' => '',
        'client_id' => '',
        'client_secret' => '',
        'access_token' => '',
    ];

    if ( ! empty( $options['client_id'] ) && $options['client_secret'] ) :
        require_once( UT_DIR . 'vendor/gregavola/untappdphp/lib/untappdPHP.php' );
        require_once( UT_DIR . 'api/load-checkins.php' );
        require_once( UT_DIR . 'api/toast-checkins.php' );
    endif;
}