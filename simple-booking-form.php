<?php
/**
 * Plugin Name: Simple Booking Form
 * Description: Simple booking plugin for coaches, doctors & consults. Manage appointments, track client details (name, phone, email) & customize your schedule. Includes a booking table & easy settings.
 * Version: 1.0
 * Author: MMTDEV
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

if (!defined('ABSPATH')) exit;

/** Plugin activation and booking table creation*/
require_once __DIR__ . '/database/create.php';
register_activation_hook(__FILE__, 'simpleBookingFormTableCreate');

/** Load Plugin Classes */
require_once __DIR__ . '/vendor/autoload.php';
\SimpleBookingForm\Src\SimpleBookingFormAutoloaderClass::init(); 

/** Form Shortcode */
require_once __DIR__ . '/views/simple-booking-form-shortcode.php';

?>