<div id="wrap" class="sbf-booking-setting-page">

    <h1>Simple Booking Form Settings</h1>
    <hr>

    <?php
        $saved_data         = json_decode(get_option('_sbf_working_days', '{}'), true);
        $days               = [ 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday', 0 => 'sunday'];
        $duration           = get_option('_sbf_booking_duration', 30); 
        $emailConfirmation  = get_option('_sbf_booking_mail_confirmation','Thank you for booking with us!  If you need to cancel your booking, please let us know at least 24 hours before your scheduled start time. We appreciate your understanding. Thank you.');
    ?>

    <?php if(isset($_GET['settings_updated']) && $_GET['settings_updated'] === 'true'):?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php _e('Settings saved successfully!', 'your-text-domain'); ?>
            </p>
        </div>
     <?php endif; ?>

    <?php if(isset($_GET['settings_conflict_updated']) && $_GET['settings_conflict_updated'] === 'true' ): ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php _e('Settings saved, but the following days could not be updated due to existing future bookings: <strong>'.$_GET['booking_days'].'</strong>. To edit those, delete future bookings first.', 'your-text-domain'); ?>
            </p>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">

        <?php wp_nonce_field('booking_config_save', 'booking_config_nonce'); ?>

        <table class="form-table">
            <tr>
                <th><label for="sbf_booking_duration">Consultation Duration (minutes)</label></th>
                <td>
                    <input type="number" name="sbf_booking_duration" id="sbf_booking_duration" value="<?php echo esc_attr($duration); ?>" min="5" step="5" required>
                </td>
            </tr>
        </table>

        <hr>

        <h4>Select your working days and time slots</h4>

        <table class="form-table" style="width:fit-content;">

            <tbody>

                <?php foreach($days as $key => $day): ?>

                    <tr>
                        <td class="day-times">
                            <label>
                                <input type="checkbox" class="day-checkbox" name="working_days[<?php echo $key; ?>][enabled]" <?php if(!empty($saved_data[$key])): ?> checked <?php endif; ?>>
                                <b><?php echo ucfirst($day); ?></b>
                            </label>
                        </td>
                    </tr>

                    <tr class="days-time-tr" style="display:none;">
                        <td class="day-times">
                            <div style="display:flex; gap:10px; align-items:center;">
                                <span> Morning </span>
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <input type="time" name="working_days[<?php echo $key; ?>][morning_start]" <?php if(!empty($saved_data[$key]['morning_start'])): ?> value="<?php echo esc_attr($saved_data[$key]['morning_start']); ?>" <?php endif; ?>>
                                    <span> To <span>
                                    <input type="time" name="working_days[<?php echo $key; ?>][morning_end]" <?php if(!empty($saved_data[$key]['morning_end'])): ?> value="<?php echo esc_attr($saved_data[$key]['morning_end']); ?>" <?php endif; ?>>
                                </div>
                            </div>
                        </td>

                        <td class="day-times"></td>
                        
                        <td class="day-times">
                            <div style="display:flex; gap:10px; align-items:center">
                                <span> Afternoon (Optional) </span>
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <input type="time" name="working_days[<?php echo $key; ?>][afternoon_start]" <?php if(!empty($saved_data[$key]['afternoon_start'])): ?> value="<?php echo esc_attr($saved_data[$key]['afternoon_start']); ?>" <?php endif; ?>>
                                    To
                                    <input type="time" name="working_days[<?php echo $key; ?>][afternoon_end]" <?php if(!empty($saved_data[$key]['afternoon_end'])): ?> value="<?php echo esc_attr($saved_data[$key]['afternoon_end']); ?>" <?php endif; ?>>
                                </div>
                            </div>
                        </td>
                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

        <hr>

        <table class="form-table">
            <tr>
                <th><label for="sbf_booking_mail_confirmation">Email confirmation message</label></th>
                <td>
                    <textarea rows="5" cols="50" name="sbf_booking_mail_confirmation" id="sbf_booking_mail_confirmation"><?= $emailConfirmation ?></textarea>
                </td>
            </tr>
        </table>

        <input type="hidden" name="action" value="save_booking_config">
        <button class="button button-primary" type="submit"><?= esc_html__('Save Settings', '') ?></button>

    </form>

</div>