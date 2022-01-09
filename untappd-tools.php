<?php
/*
Plugin Name: Untappd Tools
Text Domain: untappd-tools
Description: Untappd tools for WordPress
Author: Viacheslav Radionov
Author URI: https://rdnv.me
Version: 3.2.1
*/

// Exit
defined('ABSPATH') OR die();

define('UT_DIR', plugin_dir_path(__FILE__));

require __DIR__ . '/autoload.php';

new UntappdTools\Hooks();