<?php
/**
 * Load all plugin classes during this loader initialization within the main plugin file.
 * @package Simple-Booking-Form
 */

namespace SimpleBookingForm\Src;

class SimpleBookingFormAutoloaderClass
{

    public static function init()
    {
        new \SimpleBookingForm\Classes\SimpleBookingFormLoadScriptsClass();
        new \SimpleBookingForm\Classes\SimpleBookingFormAdminClass();
        new \SimpleBookingForm\Classes\SimpleBookingFormPostRequestsClass();
        
    }

}