<?php
/*
Plugin Name:       Advanced Appointment Booking & Scheduling
Description:       Advanced Appointment Booking & Scheduling: Effortlessly manage appointments with a simple, user-friendly scheduling system.
Version:           1.2
Requires at least: 5.2
Requires PHP:      7.2
Author:            themespride
Author URI:        https://www.themespride.com/
Plugin URI:
Text Domain:       advanced-appointment-booking
License:           GPL-2.0+
*/


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('ABP_VERSION', '1.2');
define('ABP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ABP_PLUGIN_URL', plugin_dir_url(__FILE__));
define( 'ABP_LICENCE_API_ENDPOINT', 'https://license.themespride.com/api/general/' );

// Include necessary files.
include_once(plugin_dir_path(__FILE__) . 'includes/class-appointment-admin.php');


// Register activation hook to create the database table
register_activation_hook(__FILE__, 'abp_create_services_table');
function abp_create_services_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'appointment_services';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        service_name varchar(255) NOT NULL,
        duration int(11) NOT NULL,
        price decimal(10,2) NOT NULL,
        description text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function abp_create_appointment_booking_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'appointment_booking';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        service_id bigint(20) NOT NULL,
        booking_date date NOT NULL,
        booking_time time NOT NULL,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(15) NOT NULL,
        price float NOT NULL,
        status varchar(20) DEFAULT 'pending',
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'abp_create_appointment_booking_table');


register_activation_hook(__FILE__, 'abp_create_appointment_booking_pages');

function abp_create_appointment_booking_pages() {
    // Create Login page
    if (!get_page_by_path('login')) {
        wp_insert_post([
            'post_title' => 'Login',
            'post_name' => 'login',
            'post_content' => '[appointment_login_form]',
            'post_status' => 'publish',
            'post_type' => 'page'
        ]);
    }

    // Create Register page
    if (!get_page_by_path('register')) {
        wp_insert_post([
            'post_title' => 'Register',
            'post_name' => 'register',
            'post_content' => '[appointment_register_form]',
            'post_status' => 'publish',
            'post_type' => 'page'
        ]);
    }

    // Create Book Appointment page
    if (!get_page_by_path('book-appointment')) {
        wp_insert_post([
            'post_title' => 'Book Appointment',
            'post_name' => 'book-appointment',
            'post_content' => '[book_appointment_form]',
            'post_status' => 'publish',
            'post_type' => 'page'
        ]);
    }

    // Create Bookings page
    if (!get_page_by_path('abp-bookings')) {
        wp_insert_post([
            'post_title' => 'Bookings',
            'post_name' => 'abp-bookings',
            'post_content' => '[abp_bookings_page]',
            'post_status' => 'publish',
            'post_type' => 'page'
        ]);
    }
}



add_action('admin_enqueue_scripts', 'abp_enqueue_admin_assets');
function abp_enqueue_admin_assets() {
    $screen = get_current_screen();
    $style_version = filemtime(plugin_dir_path(__FILE__) . 'assets/css/style.css');

    if (isset($_GET['page']) && ($_GET['page'] == 'appointment-booking-admin' || $_GET['page'] == 'appointment-bookings' || $_GET['page'] == 'appointment-booking-themes' )) {
    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css');
    wp_enqueue_style('abp-style', plugins_url('/assets/css/style.css', __FILE__), [], $style_version);
    // Enqueue Bootstrap JS
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js', ['jquery'], null, true);

    }

}
add_action('wp_enqueue_scripts', 'abp_enqueue_assets');
function abp_enqueue_assets() {
    $style_version = filemtime(plugin_dir_path(__FILE__) . 'assets/css/abp-front.css');
   $script_version = filemtime(plugin_dir_path(__FILE__) . 'assets/js/booking.js');

    wp_enqueue_style('abp-style', plugins_url('/assets/css/abp-front.css', __FILE__), [], $style_version);

   wp_enqueue_script('abp-script', plugins_url('/assets/js/booking.js', __FILE__), ['jquery'], $script_version, true);
}