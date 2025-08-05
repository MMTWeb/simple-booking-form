<?php

namespace SimpleBookingForm\Classes;
use WP_List_Table;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class SimpleBookingFormListTableClass extends WP_List_Table 
{

    public function __construct()
    {
        parent::__construct([
            'singular' => 'Booking',
            'plural'   => 'Bookings',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'           => '<input type="checkbox" />',
            'client_name'  => 'Name',
            'client_email' => 'Email',
            'client_phone' => 'Phone',
            'booking_date' => 'Date',
            'booking_time' => 'Time',
            'created_at'   => 'Created',
        ];
    }

    public function get_sortable_columns() 
    {
        return [
            'client_name'  => ['client_name', true],
            'booking_date' => ['booking_date', false],
        ];
    }

    public function get_bulk_actions()
    {
        return [
            'bulk-delete' => 'Delete',
        ];
    }

    public function column_cb($item) 
    {
        return sprintf('<input type="checkbox" name="bookings[]" value="%s" />', $item['id']);
    }

    public function column_client_name($item) 
    {
        $deleteNonce = wp_create_nonce('simple_booking_form_delete');

        $title = '<strong>' . $item['client_name'] . '</strong>';

        $actions = [
            'delete' => sprintf(
                '<a href="?page=%s&action=delete&id=%s&_wpnonce=%s" onclick="return confirm(\'Are you sure?\')">Delete</a>',
                esc_attr($_REQUEST['page']),
                absint($item['id']),
                $deleteNonce
            ),
        ];

        return $title . $this->row_actions($actions);
    }

    public function prepare_items()
    {

        global $wpdb;
        $tableName = $wpdb->prefix.'simple_booking_form';

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $offset = ($currentPage - 1) * $perPage;

        $orderby = isset($_GET['orderby']) ? sanitize_sql_orderby($_GET['orderby']) : 'booking_date';
        $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';

        $filterDate = sanitize_text_field($_GET['filter_date'] ?? '');
        $upcomingDay = sanitize_text_field($_GET['upcoming_day'] ?? '');

        if(!empty($filterDate)){

            $query      = $wpdb->prepare("SELECT * FROM $tableName WHERE booking_date = %s ORDER BY $orderby $order LIMIT %d OFFSET %d",$filterDate, $perPage, $offset);
            $countSql   = $wpdb->prepare("SELECT COUNT(*) FROM $tableName WHERE booking_date = %s",$filterDate);
            $totalItems = $wpdb->get_var($countSql);

        }elseif(!empty($upcomingDay) || $upcomingDay !== ''){

            $query = $wpdb->prepare("SELECT * FROM $tableName WHERE WEEKDAY(booking_date) = %d AND booking_date >= CURDATE() ORDER BY $orderby $order LIMIT %d OFFSET %d",$upcomingDay, $perPage, $offset);
            $countSql   = $wpdb->prepare("SELECT COUNT(*) FROM $tableName WHERE WEEKDAY(booking_date) = %d AND booking_date >= CURDATE()", $upcomingDay);
            $totalItems = $wpdb->get_var($countSql);

        }else{

            $countSql   = "SELECT COUNT(*) FROM $tableName";
            $totalItems = $wpdb->get_var($countSql);
            $query      = $wpdb->prepare("SELECT * FROM $tableName ORDER BY $orderby $order LIMIT %d OFFSET %d", $perPage, $offset);

        }

        $this->items = $wpdb->get_results($query, ARRAY_A);

        $this->set_pagination_args([
            'total_items' => $totalItems,
            'per_page'    => $perPage,
            'total_pages' => ceil($totalItems / $perPage),
        ]);
    }

    public function column_default($item, $column_name) {
        return $item[$column_name] ?? '<em>n/a</em>';
    }
}
