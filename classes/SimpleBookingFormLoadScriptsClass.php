<?php 
/**
 * This class load all necessary CSS and JS for the plugin
 * @package simple-booking-form 
*/

namespace SimpleBookingForm\Classes;

class SimpleBookingFormLoadScriptsClass
{

    public $wpdb;
    private $bookingTable;
    
    /**
     * Constructs the BookingPostRequestsClass.
	*/

    public function __construct() 
    {

        global $wpdb;
        $this->wpdb = $wpdb;
        $this->bookingTable = $this->wpdb->prefix . 'simple_booking_form';

        add_action( 'wp_enqueue_scripts', [$this, 'simpleBookingFormGuestStyleSheet']);
        add_action('wp_enqueue_scripts',  [$this, 'simpleBookingFormGuestJs']);
        add_action('admin_enqueue_scripts', [$this, 'simpleBookingFormAdminJs']);

    }

    /** Load Plugin Stylesheet */
    public function simpleBookingFormGuestStyleSheet()
    {

        wp_register_style('simple-booking-form-stylesheet', plugin_dir_url( __DIR__ . '../' ).'assets/css/main.css');
        wp_enqueue_style ('simple-booking-form-stylesheet');
    
    }

    public function simpleBookingFormGuestJs() 
    {

        $this->loadDatePicker();

        $bookedDates = \SimpleBookingForm\Classes\SimpleBookingFormHelperClass::getBookedDates();
        $workingDays = \SimpleBookingForm\Classes\SimpleBookingFormHelperClass::getWorkingDays();

        wp_enqueue_script('simple-booking-form-ajax-form-handle', plugin_dir_url(__DIR__ . '../').'assets/js/simple-booking-form-handle.ajax.js', ['jquery', 'jquery-ui-datepicker'], null, true);

        wp_localize_script('simple-booking-form-ajax-form-handle', 'ajaxBooking', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('booking_nonce'),
        ]);

        wp_enqueue_script('simple-booking-form-ajax-form-check', plugin_dir_url(__DIR__ . '../').'assets/js/simple-booking-form-check.ajax.js', ['jquery', 'jquery-ui-datepicker'], null, true);
    
        wp_localize_script('simple-booking-form-ajax-form-check', 'ajaxBooking', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('booking_nonce'),
            'booked_dates' => $bookedDates,
            'working_days' => $workingDays
        ]);

    }

    public function simpleBookingFormAdminJs() 
    {

        //Stylesheet
        wp_register_style('simple-booking-form-admin-stylesheet', plugin_dir_url(__DIR__ . '../') .'assets/css/main-admin.css');
        wp_enqueue_style ('simple-booking-form-admin-stylesheet');

        $this->loadDatePicker();
        wp_enqueue_script('simple-booking-form-admin-js', plugin_dir_url(__DIR__ . '../').'assets/js/simple-booking-form-admin.js', ['jquery', 'jquery-ui-datepicker'], null, true);

    }

    /** Load data picker form jQueryUI */
    public function loadDatePicker()
    {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
    }

}