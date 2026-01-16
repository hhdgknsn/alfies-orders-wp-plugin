<?php
/**
* Plugin Name: Alfies Orders
* Version: 1.0
*/
register_activation_hook(__FILE__, 'alfies_create_table');

add_action('admin_menu', 'alfies_admin_menu');
add_action('elementor_pro/forms/new_record', 'handle_alfies_order', 10, 2);

function handle_alfies_order($record, $handler) {
    global $wpdb;
    
    $form_name = $record->get_form_settings('form_name');

    $raw_fields = $record->get('fields');
    
    $data = [
        'order_id' => 'ORD-' . time(),
        'name' => sanitize_text_field($raw_fields['name']['value'] ?? ''),
        'email' => sanitize_email($raw_fields['email']['value'] ?? ''),
        'phone' => sanitize_text_field($raw_fields['phone']['value'] ?? ''),
        'items' => sanitize_textarea_field($raw_fields['items']['value'] ?? ''),
        'no_people' => intval($raw_fields['no_people']['value'] ?? 0),
        'message' => sanitize_textarea_field($raw_fields['message']['value'] ?? ''),
        'event_date' => '',
        'price' => 0
    ];
    
    $wpdb->insert($wpdb->prefix . 'alfies_orders', $data);
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

function alfies_time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return $diff . ' seconds ago';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return floor($diff / 86400) . ' days ago';
}

function alfies_orders_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'alfies_orders';
    $orders = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$table} ORDER BY created_at DESC")
    );
    ?>
    <div class="wrap">
        <h1>Alfies Orders</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Time Ago</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $order): ?>
                <tr>
                    <td><?php echo esc_html($order->order_id); ?></td>
                    <td><?php echo esc_html($order->name); ?></td>
                    <td><?php echo esc_html($order->email); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($order->created_at)); ?></td>
                    <td><?php echo alfies_time_ago($order->created_at); ?></td>
                    <td><?php echo esc_html($order->status); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}