/** Generate a datepicker for the booking table page, enabling admins to filter by date. */
jQuery(document).ready(function($){

  //Show datepicker on booking table page (start)
  $('#simple-booking-form-filter-date').datepicker({ dateFormat: 'yy-mm-dd'});
  //Show datepicker on booking table page (end)

  //Show hide days schedules while cheking days checkbox (start)
  $('.day-checkbox').on('change', function(){

    const $row = $(this).closest('tr');
    const $nextRow = $row.next('.days-time-tr');

    if($(this).is(':checked')){
      $nextRow.show();  
    }else{
      $nextRow.hide();  
    }

  });

  $('.day-checkbox:checked').each(function(){

    const $row = $(this).closest('tr');
    const $nextRow = $row.next('.days-time-tr');
    $nextRow.show();

  });
  //Show hide days schedules while cheking days checkbox (end)

});