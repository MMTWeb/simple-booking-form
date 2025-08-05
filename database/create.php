<?php 

function simpleBookingFormTableCreate() 
{
    global $wpdb;
    $tableName = $wpdb->prefix . 'simple_booking_form';
    $charset_collate = $wpdb->get_charset_collate();

    if($wpdb->get_var( "show tables like '$tableName'" ) != $tableName) 
    {

        $sql = "CREATE TABLE $tableName(
            id bigint(11) NOT NULL AUTO_INCREMENT,
            booking_date date NOT NULL,
            booking_time time NOT NULL,
            client_name varchar(100),
            client_email varchar(100),
            client_phone varchar(100),
            duration int DEFAULT 30, 
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

    }

}

?>