/**
 * Simple Booking Form Frontend Script
 * -----------------------------
 * This script handles:
 * - Submitting the booking form via AJAX
 * - Refreshing the datepicker after booking
 */

jQuery(document).ready(function($)
{

    $('#simple-booking-form').on('submit', function(e){
        
        e.preventDefault();

        let name = $('#simple-booking-form-name').val();
        let email = $('#simple-booking-form-email').val();
        let phone = $('#simple-booking-form-phone').val();
        let date = $('#simple-booking-form-date').val();
        let time = $('#simple-booking-form-time').val(); 

        $.ajax({
            type: 'POST',
            url: ajaxBooking.ajax_url,
            data: {
                action: 'save_booking',
                nonce: ajaxBooking.nonce,
                booking_date: date,
                booking_time: time,
                booking_name: name,
                booking_email: email,
                booking_phone: phone
            },
            success: function(response){
                $('#simple-booking-form-message').text(response.data.message);
                if(response.success){
                    $("#simple-booking-form-date").datepicker("refresh");
                    $('#simple-booking-form')[0].reset();
                    $('#simple-booking-form-time').html('<option value="">-- Select time --</option>');
                }

            },
            error: function(){
                $('#simple-booking-form-message').text("Booking failed.");
            }
        });
    });
});