<?php
/**
* Plugin Name: Alfies Orders
* Version: 1.0
*/

require_once plugin_dir_path(__FILE__) . 'utils.php';
require_once plugin_dir_path(__FILE__) . 'alfies-orders-page.php';

register_activation_hook(__FILE__, function() {
    alfies_create_table();
    alfies_create_changelog_table();
});

add_action('admin_menu', 'alfies_admin_menu');
add_action('elementor_pro/forms/new_record', 'handle_alfies_order', 10, 2);

function handle_alfies_order($record, $handler) {
    global $wpdb;
    
    $form_name = $record->get_form_settings('form_name');
    if ($form_name !== 'Order Form') return;
    
    $fields = $record->get('fields');
    
    // Extract data
    $name = sanitize_text_field($fields['name']['value'] ?? '');
    $email = sanitize_email($fields['email']['value'] ?? '');
    $phone = sanitize_text_field($fields['phone']['value'] ?? '');
    $items = sanitize_textarea_field($fields['buffet_items']['value'] ?? '');
    $no_people = intval($fields['no_people']['value'] ?? 0);
    $message = sanitize_textarea_field($fields['message']['value'] ?? '');
    
    // Calculate pricing
    $pricing = calculate_order_price($items, $no_people);
    
    // Build order data
    $data = [
        'order_id' => 'ORD-' . time(),
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'items' => $items,
        'no_people' => $no_people,
        'message' => $message,
        'event_date' => sanitize_text_field($fields['event_date']['value'] ?? ''),
        'price' => $pricing['total']
    ];
    
    // Save to database
    $wpdb->insert($wpdb->prefix . 'alfies_orders', $data);
    
    /*
    // Send customer confirmation
    $customer_email = build_customer_email($name, $items, $no_people, $pricing);
    wp_mail($email, "Order Received - Alfie's Deli", $customer_email, ['Content-Type: text/html']);
    
    // Send admin notification
    $admin_email = build_admin_email($name, $email, $phone, $items, $no_people, $message, $pricing);
    wp_mail('hollyhodgkinson11@gmail.com', "New Order - Alfie's Deli", $admin_email, ['Content-Type: text/html']);
    */
}

function alfies_create_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'alfies_orders';
    
    $sql = "CREATE TABLE $table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        order_id varchar(50) NOT NULL,
        name varchar(255),
        email varchar(255),
        phone varchar(50),
        items text,
        no_people int(11),
        event_date date,
        message text,
        price decimal(10,2),
        status varchar(50) DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY order_id (order_id)
    ) {$wpdb->get_charset_collate()};";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function alfies_admin_menu() {
    add_menu_page(
        'Alfies Orders',
        'Orders',
        'manage_options',
        'alfies-orders',
        'alfies_orders_page',
        'dashicons-cart',
        26
    );
}

function alfies_create_changelog_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'alfies_order_changelog';
    
    $sql = "CREATE TABLE $table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        order_id varchar(50) NOT NULL,
        user_id bigint(20) NOT NULL,
        field_name varchar(100) NOT NULL,
        old_value text,
        new_value text,
        changed_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY order_id (order_id)
    ) {$wpdb->get_charset_collate()};";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function alfies_log_changes($order_id, $old_data, $new_data) {
    global $wpdb;
    $table = $wpdb->prefix . 'alfies_order_changelog';
    $user_id = get_current_user_id();
    
    $fields = ['name', 'email', 'phone', 'items', 'no_people', 'event_date', 'message', 'price', 'status'];
    
    foreach ($fields as $field) {
        $old_val = $old_data->$field ?? '';
        $new_val = $new_data[$field] ?? '';
        
        if ($old_val != $new_val) {
            $wpdb->insert($table, [
                'order_id' => $order_id,
                'user_id' => $user_id,
                'field_name' => $field,
                'old_value' => $old_val,
                'new_value' => $new_val
            ]);
        }
    }
}