<?php 
/**
 * This class handles all plugin forms by leveraging the admin_post hook.
 * @package simple-booking-form 
*/

namespace SimpleBookingForm\Classes;

class SimpleBookingFormPostRequestsClass
{

    public  $wpdb;
    private $bookingTable;
    
    /**
     * Constructs the BookingPostRequestsClass.
	*/

    public function __construct() 
    {

        global $wpdb;
        $this->wpdb = $wpdb;
        $this->bookingTable = $this->wpdb->prefix . 'simple_booking_form';

        add_action( 'wp_ajax_get_available_times', [$this, 'getAvailableTimes']);
        add_action( 'wp_ajax_nopriv_get_available_times', [$this, 'getAvailableTimes']);

        add_action('admin_post_save_booking_config', [$this, 'handleBookingAdminSetup']);
        add_action('admin_post_nopriv_save_booking_config', [$this,'handleBookingAdminSetup']);

        add_action('wp_ajax_save_booking', [$this, 'handleBookingForm']);
        add_action('wp_ajax_nopriv_save_booking', [$this,'handleBookingForm']);

    }

    /** Ajax request to get available times on date select */

    public function getAvailableTimes()
    {

        check_ajax_referer('booking_nonce', 'nonce');

        $slots  = [];
        $ranges = [];
        
        $duration           = get_option('_sbf_booking_duration', 30); // slot interval in minutes
        $bookingDate        = sanitize_text_field($_POST['booking_date']);
        $dayIndex           = date('w', strtotime($bookingDate)); // 0 (Sunday) to 6 (Saturday)
        $workingDays        = get_option('_sbf_working_days', '{}');
        $workingDaysDatas   = json_decode($workingDays, true);

        if(!isset($workingDaysDatas[$dayIndex])){
            wp_send_json_success(['times' => []]); // No working hours for this day
        }

        $dayData = $workingDaysDatas[$dayIndex];

        if(!empty($dayData['morning_start']) && !empty($dayData['morning_end'])){
            $ranges[] = [$dayData['morning_start'], $dayData['morning_end']];
        }

        if(!empty($dayData['afternoon_start']) && !empty($dayData['afternoon_end'])) {
            $ranges[] = [$dayData['afternoon_start'], $dayData['afternoon_end']];
        }

        foreach($ranges as [$start, $end]){
            for($time = strtotime($start); $time <= strtotime($end); $time += $duration * 60) {
                $slots[] = date('H:i', $time);
            }
        }

        /** Get Selected Day fro booking table to get booked times*/
        $bookedRaw = $this->wpdb->get_col($this->wpdb->prepare("SELECT booking_time FROM $this->bookingTable WHERE booking_date = %s", $bookingDate));

        /** Normalize time strings to H:i format (e.g., 11:30:00 -> 11:30) */
        $bookedTimes = array_map(function($time) { 
            return date('H:i', strtotime($time)); 
        }, $bookedRaw);

        /** Return available times for the selected day */
        $availableTimes = array_values(array_diff($slots, $bookedTimes));
        wp_send_json_success(['times' => $availableTimes]);

    }

    /** Handle Admin plugin setup values */

    public function handleBookingAdminSetup()
    {

        if(isset($_POST['booking_config_nonce']) && wp_verify_nonce($_POST['booking_config_nonce'], 'booking_config_save')){

            $structured     = [];
            $workingDays    = $_POST['working_days'] ?? [];
            $existingConfig = json_decode(get_option('_sbf_working_days', '{}'), true);

            if(isset($_POST['sbf_booking_duration'])){
                update_option('_sbf_booking_duration', intval($_POST['sbf_booking_duration']));
            }

            if(isset($_POST['sbf_booking_mail_confirmation'])){
                update_option('_sbf_booking_mail_confirmation', $_POST['sbf_booking_mail_confirmation']);
            }

            //Check if we have already booking in the modified days
            $blockedDays = \SimpleBookingForm\Classes\SimpleBookingFormHelperClass::checkBeforeEdit($workingDays);

            // Remove the blocked days from the submitted config
            if(!empty($blockedDays)){

                foreach($blockedDays as $dayName){

                    $dayNumber = (string)date('w', strtotime($dayName)); 

                    if(isset($existingConfig[$dayNumber])){
                        $workingDays[$dayNumber] = $existingConfig[$dayNumber];
                        $workingDays[$dayNumber]['enabled'] = 'on';
                    }

                }

            }

            foreach($workingDays as $day => $data){

                if(isset($data['enabled']) && !empty($data['morning_start']) && !empty($data['morning_end']) || isset($data['enabled']) && !empty($data['afternoon_start']) && !empty($data['afternoon_end'])){

                    if(!empty($data['morning_start']) && !empty($data['morning_end']) && !empty($data['afternoon_start']) && !empty($data['afternoon_end']) ){

                        $structured[$day] = [
                            'morning_start' => sanitize_text_field($data['morning_start']),
                            'morning_end'   => sanitize_text_field($data['morning_end']),
                            'afternoon_start' => sanitize_text_field($data['afternoon_start']),
                            'afternoon_end'   => sanitize_text_field($data['afternoon_end']),
                        ];

                    }elseif(!empty($data['morning_start']) && !empty($data['morning_end'])){
                    
                        $structured[$day] = [
                            'morning_start' => sanitize_text_field($data['morning_start']),
                            'morning_end'   => sanitize_text_field($data['morning_end']),
                        ];

                    }elseif(!empty($data['afternoon_start']) && !empty($data['afternoon_end'])){

                        $structured[$day] = [
                            'afternoon_start' => sanitize_text_field($data['afternoon_start']),
                            'afternoon_end'   => sanitize_text_field($data['afternoon_end']),
                        ];

                    }

                }
            }

            update_option('_sbf_working_days', json_encode($structured));

            if(!empty($blockedDays)){
                wp_safe_redirect(add_query_arg(['settings_conflict_updated' => 'true','booking_days' => implode(', ', $blockedDays)], admin_url('admin.php?page=simple_booking_form_main_settings')));
                exit;
            }

            wp_safe_redirect(add_query_arg('settings_updated', 'true', admin_url('admin.php?page=simple_booking_form_main_settings')));
            exit;

        }

    }

    /** Handle new bookings from Ajax request  */
    public function handleBookingForm() 
    {

        check_ajax_referer('booking_nonce', 'nonce');

        $table = $this->bookingTable;

        $date   = sanitize_text_field($_POST['booking_date']);
        $time   = sanitize_text_field($_POST['booking_time']);
        $name   = sanitize_text_field($_POST['booking_name']);
        $email  = sanitize_email($_POST['booking_email']);
        $phone  = sanitize_text_field($_POST['booking_phone']);

        if(!$name || !$email || !$phone || !$date || !$time) {
            wp_send_json_error(['message' => 'Missing required fields.']);
        }

        // Check if this exact time slot is already booked
        $exists = $this->wpdb->get_var($this->wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE booking_date = %s AND booking_time = %s", $date, $time ));

        if($exists > 0){
            wp_send_json_error(['message' => 'This time slot is already booked.']);
        }

        // Count total bookings for this date (for logic elsewhere)
        $countDay = $this->wpdb->get_var($this->wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE booking_date = %s", $date ));

        if($countDay >= 18) {
            wp_send_json_error(['message' => 'This day is fully booked.']);
        }

        $duration = intval(get_option('_sbf_booking_duration'));

        // Insert booking
        $success = $this->wpdb->insert($table, [
            'booking_date'  => $date,
            'booking_time'  => $time,
            'client_name'   => $name,
            'client_email'  => $email,
            'client_phone'  => $phone,
            'duration'      => $duration,
        ]);

        if($success === false){
            wp_send_json_error(['message' => 'Database error. Please try again.']);
        }

        /** Send confirmation email */
        $bookingInfos = [
            'name'  => $name,
            'email' => $email,
            'phone' => $phone,
            'date'  => $date,
            'time'  => $time
        ];

        $sendEmail = \SimpleBookingForm\Classes\SimpleBookingFormHelperClass::sendEmail($email, $bookingInfos);

        if($sendEmail === false){
            wp_send_json_error(['message' => 'Server error. Please try again.']);
        }

        wp_send_json_success([
            'message' => 'Booking successful!',
        ]);

        wp_die();
    }

    private function getLastBookingDate()
    {

        $last_date = $wpdb->get_var("SELECT MAX(booking_date) FROM $table ");
        return $last_date; // format: YYYY-MM-DD or NULL if no bookings 

    }

}