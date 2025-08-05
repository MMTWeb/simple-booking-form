/**
 * Simple Booking Form Frontend Script
 * -----------------------------
 * This script handles:
 * - Initializing the jQuery UI datepicker
 * - Disabling fully booked dates and non-working days
 * - Loading available time slots via AJAX when a date is selected
 */

jQuery(document).ready(function($)
{

    /**
     * Disable dates that are either fully booked or not in the allowed working days.
     * @param {Date} date - The date being rendered by the datepicker.
     * @returns {Array} - [true/false] to allow/disallow selection.
    */

    function disableDates(date) {
        
        const workingDays = ajaxBooking.working_days.map(Number);
        const bookedDates = ajaxBooking.booked_dates;

        const formattedDate = $.datepicker.formatDate('yy-mm-dd', date);
        const day = date.getDay();

        const isWorkingDay = workingDays.includes(day);
        const isFullyBooked = bookedDates.includes(formattedDate);

        // Enable only if it's a working day and not fully booked
        return [isWorkingDay && !isFullyBooked];

    }

    /**
     * Initialize the datepicker with rules:
     * - Disable non-working and fully booked days
     * - Load available times when a valid date is picked
    */

    $('#simple-booking-form-date').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: 0,
        beforeShowDay: disableDates,
        onSelect: function(dateText){
            loadAvailableTimeSlots(dateText);
        }
    });

    /**
     * Load available time slots from the server when a date is selected.
     * Populates the <select> dropdown.
    */

    function loadAvailableTimeSlots(date)
    {

        $('#simple-booking-form-time').html('<option value="">Loading...</option>');

        $.ajax({
            url: ajaxBooking.ajax_url,
            type: 'POST',
            data: {
                action: 'get_available_times',
                nonce: ajaxBooking.nonce,
                booking_date: date
            },
            success: function(response) {
                if(response.success) {
                    const times = response.data.times;
                    let options = '<option value="">-- Select time --</option>';
                    times.forEach(function(t) {
                        options += `<option value="${t}">${t}</option>`;
                    });
                    $('#simple-booking-form-time').html(options);
                }else{
                    $('#simple-booking-form-time').html('<option value="">No slots</option>');
                }
            }
        });
    }
});