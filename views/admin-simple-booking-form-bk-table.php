<?php 
    $table = new \SimpleBookingForm\Classes\SimpleBookingFormListTableClass();
    $table->prepare_items();
?>

<div id="wrap" class="sbf-booking-table-page">

    <h1>Booking list table</h1>

    <hr>

    <table class="form-table">
		<tbody>
            <tr>
		        <th scope="row">
				    <label for="simple-booking-form-filter-date">Show by date</label>
		        </th>
			    <td>
                    <form method="get">
                        <input type="hidden" name="page" value="simple_booking_form_booking_table" />
                        <input type="text" id="simple-booking-form-filter-date" class="regular-text" name="filter_date" value="<?php echo esc_attr($_GET['filter_date'] ?? ''); ?>" placeholder="Select a date" autocomplete="off" required/>
                        <input type="submit" class="button" value="Apply" />
                    </form>
			    </td>
		    </tr>
			<tr>
				<th scope="row"><label for="simple-booking-form-upcoming-day">Upcoming bookings by day</label></th>
				<td>
                    <form method="get">
                        <input type="hidden" name="page" value="simple_booking_form_booking_table" />
                        <select name="upcoming_day" id="simple-booking-form-upcoming-day" class="regular-text" required>
                            <option value="">Select a day</option>
                            <option value="0" <?php if(isset($_GET['upcoming_day']) && $_GET['upcoming_day'] == 0): ?> selected <?php endif; ?> >Monday</option>
                            <option value="1" <?php if(isset($_GET['upcoming_day']) && $_GET['upcoming_day'] == 1): ?> selected <?php endif; ?> >Tuesday</option>
                            <option value="2" <?php if(isset($_GET['upcoming_day']) && $_GET['upcoming_day'] == 2): ?> selected <?php endif; ?> >Wednesday</option>
                            <option value="3" <?php if(isset($_GET['upcoming_day']) && $_GET['upcoming_day'] == 3): ?> selected <?php endif; ?> >Thursday</option>
                            <option value="4" <?php if(isset($_GET['upcoming_day']) && $_GET['upcoming_day'] == 4): ?> selected <?php endif; ?> >Friday</option>
                            <option value="5" <?php if(isset($_GET['upcoming_day']) && $_GET['upcoming_day'] == 5): ?> selected <?php endif; ?> >Saturday</option>
                            <option value="6" <?php if(isset($_GET['upcoming_day']) && $_GET['upcoming_day'] == 6): ?> selected <?php endif; ?> >Sunday</option>
                        </select>
                        <input type="submit" class="button" value="Apply" />
                    </form>
				</td>
			</tr>
	    </tbody>
    </table>

    <?php if(isset($_GET['filter_date']) || isset($_GET['upcoming_day']) ): ?>
         <form method="get">
            <input type="hidden" name="page" value="simple_booking_form_booking_table" />
            <input type="submit" class="button" value="Reset" />
        </form>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="page" value="simple_booking_show">
        <?php wp_nonce_field('bulk_delete_bookings', '_wpnonce_bulk_delete'); ?>
        <?php $table->display(); ?>
    </form>

</div>