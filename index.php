<?php
/**
 * @package Sendsmith
 */
/*
  Plugin Name: SendSmith
  Plugin URI: http://www.sendsmith.com/
  Description: SendSmith Makes Email Marketing a Breeze. Get to know more about your email subscribers and deliver professional emails now. Simply use this plugin to connect SendSmith account to your Wordpress website.
  Version: 1.1
  Author: SendSmith
  Author URI: http://www.sendsmith.com/
  Text Domain: sendsmith
  Domain Path: /languages
  License: GPLv2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ){
	die;
}

//Defining constant for use throughout the site
define('SENDSMITH_VERSION', '1.0');
define('SENDSMITH_MIN_WP_REQUIRED', '3.2');
define('SENDSMITH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SENDSMITH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SENDSMITH_TPL', SENDSMITH_PLUGIN_DIR . '/tpl/');
define('SENDSMITH_ASSETS_URL', SENDSMITH_PLUGIN_URL . 'assets/');
define('SENDSMITH_URL','https://api.sendsmith.com');
define('SENDSMITH_DOMAIN','sendsmith.com');

require_once 'inc/classes/sendsmith.php'; //main front class

//only if admin is logged in then include the admin class
if (is_admin()) {
    require_once 'inc/classes/sendsmith.admin.php';
}

//activation
function sendsmith_activate() {
    update_option('sendsmith-doubleopt-form', 0);
    update_option('sendsmith-doubleopt-roles', 0);
}
register_activation_hook( __FILE__, 'sendsmith_activate' );
