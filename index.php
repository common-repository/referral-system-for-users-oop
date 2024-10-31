<?php
/*

Plugin Name: Referral system for users IVD

Description: Users can have units & to increase their

Version: 1.0

Author: Ivan Shcherbyna

Text Domain: referr-system

Domain Path: /languages

License: GPLv3

*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
//const IVD_PLUGIN_URL_REFERRER = WP_PLUGIN_URL . '/Referral-system-IVD/';
define('IVD_PLUGIN_URL_REFERRER',plugin_dir_url (__FILE__));

if ( ! class_exists( 'Referrals' ) ) :
require_once __DIR__ . '/lib/class-menu.php';
require_once __DIR__ . '/lib/class-referrals.php';
require_once __DIR__ . '/lib/class-sms-verif.php';
require_once __DIR__ . '/lib/class-shotcodes.php';
require_once __DIR__ . '/lib/class-safety.php';

$IVD_admin_menu = new IVD_Admin_menu();
$IVD_referalSystem = new IVD_Referrals();
$IVD_safety_debug_mode= new IVD_Safety(true);
$IVD_shortcodes = new IVD_Shortcodes();


endif;