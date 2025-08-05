<?php 
/**
 * Creates the admin menu and generates the necessary menu pages.
 * This class extends `impleBookingFormViewClass` to render the page views.
 * @package simple-booking-form
 */

namespace SimpleBookingForm\Classes;

class SimpleBookingFormAdminClass extends SimpleBookingFormViewClass
{

    public function __construct() 
    {
        add_action( 'admin_menu', [$this, 'simpleBookingFormSetupMenu']);
    }

    /** Generate plugin backend menu */
    public function simpleBookingFormSetupMenu()
    {
        add_menu_page('Simple Booking Form', 'Simple Booking Form', 'edit_pages', 'simple_booking_form_main_settings',[$this, 'simpleBookingFormMainSettings'],'dashicons-email-alt2',8);
        add_submenu_page( 'simple_booking_form_main_settings', 'Booking Table', 'Booking Table', 'edit_pages', 'simple_booking_form_booking_table', [$this, 'simpleBookingFormBookingsTable']);
    }

    /** Settings page */
    public function simpleBookingFormMainSettings()
    {
        $this->renderView('admin-simple-booking-form-settings');
    }

    /** Bookings table page */
    public function simpleBookingFormBookingsTable()
    {
        /** Check if there are delete actions before render the view*/
        global $wpdb;

        if(isset($_GET['action'], $_GET['id'], $_GET['_wpnonce']) && $_GET['action'] === 'delete' && wp_verify_nonce($_GET['_wpnonce'], 'simple_booking_form_delete')){
            $wpdb->delete($wpdb->prefix . 'simple_booking_form', ['id' => absint($_GET['id'])]);
        }   

        if(isset($_POST['action']) && $_POST['action'] === 'bulk-delete' && check_admin_referer('bulk_delete_bookings', '_wpnonce_bulk_delete') && !empty($_POST['bookings']) && is_array($_POST['bookings'])){

            $ids = array_map('absint', $_POST['bookings']);

            foreach($ids as $id){
                $wpdb->delete($wpdb->prefix . 'simple_booking_form', ['id' => $id]);
            }

        }

        $this->renderView('admin-simple-booking-form-bk-table');
    }

}