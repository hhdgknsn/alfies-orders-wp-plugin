<?php
/**
* Plugin Name: Alfies Orders
* Version: 1.0
*/

require_once plugin_dir_path(__FILE__) . 'utils.php';

register_activation_hook(__FILE__, 'alfies_create_table');

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
    
    // Send customer confirmation
    $customer_email = build_customer_email($name, $items, $no_people, $pricing);
    wp_mail($email, "Order Received - Alfie's Deli", $customer_email, ['Content-Type: text/html']);
    
    // Send admin notification
    $admin_email = build_admin_email($name, $email, $phone, $items, $no_people, $message, $pricing);
    wp_mail('hollyhodgkinson11@gmail.com', "New Order - Alfie's Deli", $admin_email, ['Content-Type: text/html']);
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

function alfies_orders_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'alfies_orders';
    
    if (isset($_GET['view_order'])) {
        $order_id = sanitize_text_field($_GET['view_order']);
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE order_id = %s",
            $order_id
        ));
        
        if (!$order) {
            echo '<div class="wrap"><p>Order not found.</p></div>';
            return;
        }
        ?>
        <div class="wrap">
            <h1>Order Details</h1>
            <p><a href="<?php echo admin_url('admin.php?page=alfies-orders'); ?>">&larr; Back to Orders</a></p>
            
            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2><?php echo esc_html($order->order_id); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th>Status:</th>
                        <td><strong><?php echo esc_html($order->status); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Customer:</th>
                        <td><?php echo esc_html($order->name); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><a href="mailto:<?php echo esc_attr($order->email); ?>"><?php echo esc_html($order->email); ?></a></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?php echo esc_html($order->phone); ?></td>
                    </tr>
                    <tr>
                        <th>Number of People:</th>
                        <td><?php echo esc_html($order->no_people); ?></td>
                    </tr>
                    <tr>
                        <th>Event Date:</th>
                        <td><?php echo esc_html($order->event_date ?: 'Not specified'); ?></td>
                    </tr>
                    <tr>
                        <th>Items Ordered:</th>
                        <td><pre style="white-space: pre-wrap;"><?php echo esc_html($order->items); ?></pre></td>
                    </tr>
                    <tr>
                        <th>Message:</th>
                        <td><?php echo esc_html($order->message ?: 'No message'); ?></td>
                    </tr>
                    <tr>
                        <th>Price:</th>
                        <td><strong>£<?php echo number_format($order->price, 2); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Order Created:</th>
                        <td><?php echo date('M j, Y g:i A', strtotime($order->created_at)); ?> 
                            <em>(<?php echo alfies_time_ago($order->created_at); ?>)</em>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
        return;
    }
    
    $orders = $wpdb->get_results(
        "SELECT * FROM {$table} ORDER BY created_at DESC"
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
                    <th>People</th>
                    <th>Price</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $order): 
                    $view_url = add_query_arg('view_order', $order->order_id);
                ?>
                <tr style="cursor: pointer;" onclick="window.location='<?php echo esc_url($view_url); ?>'">
                    <td><strong><?php echo esc_html($order->order_id); ?></strong></td>
                    <td><?php echo esc_html($order->name); ?></td>
                    <td><?php echo esc_html($order->email); ?></td>
                    <td><?php echo esc_html($order->no_people); ?></td>
                    <td>£<?php echo number_format($order->price, 2); ?></td>
                    <td><?php echo alfies_time_ago($order->created_at); ?></td>
                    <td><?php echo esc_html($order->status); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}