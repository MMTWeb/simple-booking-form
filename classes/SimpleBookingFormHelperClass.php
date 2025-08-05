<?php 
/**
 * This helper class provides functions for retrieving booked dates, completely disabling specific dates, checking for conflicts with upcoming dates after settings submission, and finally sending booking email confirmations.
 * @package simple-booking-form 
*/

namespace SimpleBookingForm\Classes;

class SimpleBookingFormHelperClass
{

    public static function getWorkingDays()
    {
        $days = array();
        $WorkingDaysAndTimesJson = json_decode(get_option('_sbf_working_days', '{}'), true);

        foreach($WorkingDaysAndTimesJson as $key => $times){
            $days[] = $key;
        }

        return $days;
    }

    public static function getBookedDates()
    {
        // Select all booked slots grouped by date
        global $wpdb;
        $bookingTable   = $wpdb->prefix . 'simple_booking_form';
        $results        = $wpdb->get_results("SELECT booking_date, COUNT(*) as total FROM $bookingTable GROUP BY booking_date", ARRAY_A);

        // Load working slots per day from option
        $duration       = get_option('_sbf_booking_duration');
        $workingDays    = get_option('_sbf_working_days', '{}');
        $workingDatas   = json_decode($workingDays , true);

        // indexed by weekday
        $maxSlotsPerDay = []; 

        // Calculate max slots per day
        foreach($workingDatas as $dayIndex => $data){

            $slots = 0;

            if(!empty($data['morning_start']) && !empty($data['morning_end'])){
                $start = strtotime($data['morning_start']);
                $end   = strtotime($data['morning_end']);
                $slots += floor(($end - $start) / ($duration * 60)) + 1;
            }

            if(!empty($data['afternoon_start']) && !empty($data['afternoon_end'])){
                $start = strtotime($data['afternoon_start']);
                $end   = strtotime($data['afternoon_end']);
                $slots += floor(($end - $start) / ($duration * 60)) + 1;
            }

            $maxSlotsPerDay[$dayIndex] = $slots;
        }       

        // Identify fully booked dates
        $fullyBookedDates = [];

        foreach($results as $row){
            $date = $row['booking_date'];
            $weekday = date('w', strtotime($date));

            if(isset($maxSlotsPerDay[$weekday]) && $row['total'] >= $maxSlotsPerDay[$weekday]){
                $fullyBookedDates[] = $date;
            }
        }

        return $fullyBookedDates;
        
    }

    public static function checkBeforeEdit($newConfigValues)
    {

        global $wpdb;
        $bookingTable = $wpdb->prefix . 'simple_booking_form';

        $existingSchedules = json_decode(get_option('_sbf_working_days', '{}'), true);
        $submitted = $newConfigValues ?? [];

        $changedDays = [];

        foreach(range(0, 6) as $day){

            $old = $existingSchedules[$day] ?? null;
            $new = $submitted[$day] ?? null;

            $oldEnabled = !empty($old);
            $newEnabled = !empty($new) && !empty($new['enabled']);

            $hasChange = false;

            //Day removed or disabled
            if($oldEnabled && !$newEnabled){
                $hasChange = true;
            }

            //Time slots changed
            if($oldEnabled && $newEnabled){

                $timeFields = ['morning_start', 'morning_end', 'afternoon_start', 'afternoon_end'];

                foreach($timeFields as $field){

                    if(($old[$field] ?? '') !== ($new[$field] ?? '')) {
                        $hasChange = true;
                        break;
                    }

                }
            }

            if($hasChange){
                $changedDays[] = $day;
            }
        }

        //Check bookings only for changed days
        $blockedDays = [];

        foreach($changedDays as $day){

            //Convert PHP's 0=Sunday to MySQL 0=Monday
            $mysqlDay   = ($day + 6) % 7;
            $hasBooking = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$bookingTable} WHERE WEEKDAY(booking_date) = %d AND booking_date >= CURDATE()",$mysqlDay));

            if($hasBooking > 0){
                $blockedDays[] = ucfirst(date('l', strtotime("Sunday +{$day} days")));
            }
            
        }

        return !empty($blockedDays) ? $blockedDays : false;

    }

    public static function sendEmail($userEmail, array $bookingInfos)
    {

        $to = $userEmail;
        $subject = 'Booking Confirmation';
        $message =  get_option('_sbf_booking_mail_confirmation').'<br> Your Name : '.$bookingInfos['name'].'<br> Your Email : '.$bookingInfos['email'].'<br> Your Phone :'.$bookingInfos['phone'].'<br> Date : '.$bookingInfos['date'].'<br> Time :'.$bookingInfos['time'];
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail( $to, $subject, $message, $headers );


    }

}