<?php 

add_shortcode('simple_booking_form', 'simpleBookingFormShow');

function simpleBookingFormShow() 
{
    ob_start();

    $existingSettings = json_decode(get_option('_sbf_working_days', '{}'), true);

    if(empty($existingSettings)):

    ?>
        <div style="text-align:center;">You need to configure the Simple Booking Form plugin before using it.</div>
    <?php 
    
    else: 
        
    ?>
        <form id="simple-booking-form">
            <div>
                <label for="simple-booking-form-name"> <?= esc_html__('Name', 'simple-booking-form') ?> </label>
                <input type="text" id="simple-booking-form-name" required>
            </div>
            <div>
                <label for="simple-booking-form-email"><?= esc_html__('Email', 'simple-booking-form') ?> </label>
                <input type="email" id="simple-booking-form-email" required>
            </div>
            <div>
                <label for="simple-booking-form-phone"><?= esc_html__('Phone', 'simple-booking-form') ?> </label>
                <input type="text" id="simple-booking-form-phone" required>
            </div>
            <div>
                <label for="simple-booking-form-date"><?= esc_html__('Date', 'simple-booking-form') ?> </label>
                <input type="text" id="simple-booking-form-date" autocomplete="off" required>
            </div>
            <div>
                <label for="simple-booking-form-time"><?= esc_html__('Time (Please select a date before)', 'simple-booking-form') ?> </label>
                <select id="simple-booking-form-time" required>
                    <option value="">-- Select time --</option>
                    <!-- Time options populated by JS -->
                </select>
            </div>
            <div>
                <input type="submit" value="Book Now">
            </div>
            <div id="simple-booking-form-message" style="text-align:center;"></div>
        </form>

    <?php

    endif;

    return ob_get_clean();
}
