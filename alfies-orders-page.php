<?php

function alfies_orders_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'alfies_orders';
    
    if (isset($_GET['view_order'])) {
        $order_id = sanitize_text_field($_GET['view_order']);
        
        // Handle form submission
        if (isset($_POST['save_order']) && check_admin_referer('save_order_' . $order_id)) {
            $old_order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table} WHERE order_id = %s",
                $order_id
            ));
            
            $new_data = [
                'name' => sanitize_text_field($_POST['name']),
                'email' => sanitize_email($_POST['email']),
                'phone' => sanitize_text_field($_POST['phone']),
                'items' => sanitize_textarea_field($_POST['items']),
                'no_people' => intval($_POST['no_people']),
                'event_date' => sanitize_text_field($_POST['event_date']),
                'message' => sanitize_textarea_field($_POST['message']),
                'price' => floatval($_POST['price']),
                'status' => sanitize_text_field($_POST['status'])
            ];
            
            alfies_log_changes($order_id, $old_order, $new_data);
            
            $wpdb->update($table, $new_data, ['order_id' => $order_id]);
            
            echo '<div class="notice notice-success"><p>Order updated successfully!</p></div>';
        }
        
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
            
            <form method="post" action="">
                <?php wp_nonce_field('save_order_' . $order_id); ?>
                
                <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                    <h2><?php echo esc_html($order->order_id); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th>Status:</th>
                            <td>
                                <select name="status" style="width: 200px;">
                                    <option value="pending" <?php selected($order->status, 'pending'); ?>>Pending</option>
                                    <option value="confirmed" <?php selected($order->status, 'confirmed'); ?>>Confirmed</option>
                                    <option value="completed" <?php selected($order->status, 'completed'); ?>>Completed</option>
                                    <option value="cancelled" <?php selected($order->status, 'cancelled'); ?>>Cancelled</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Customer:</th>
                            <td><input type="text" name="name" value="<?php echo esc_attr($order->name); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><input type="email" name="email" value="<?php echo esc_attr($order->email); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><input type="text" name="phone" value="<?php echo esc_attr($order->phone); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th>Number of People:</th>
                            <td><input type="number" name="no_people" value="<?php echo esc_attr($order->no_people); ?>" min="1" style="width: 100px;"></td>
                        </tr>
                        <tr>
                            <th>Event Date:</th>
                            <td><input type="date" name="event_date" value="<?php echo esc_attr($order->event_date); ?>"></td>
                        </tr>
                        <tr>
                            <th>Items Ordered:</th>
                            <td><textarea name="items" rows="6" class="large-text"><?php echo esc_textarea($order->items); ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Message:</th>
                            <td><textarea name="message" rows="4" class="large-text"><?php echo esc_textarea($order->message); ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Price:</th>
                            <td>£<input type="number" name="price" value="<?php echo esc_attr($order->price); ?>" step="0.01" min="0" style="width: 120px;"></td>
                        </tr>
                        <tr>
                            <th>Order Created:</th>
                            <td><?php echo date('M j, Y g:i A', strtotime($order->created_at)); ?> 
                                <em>(<?php echo alfies_time_ago($order->created_at); ?>)</em>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="save_order" class="button button-primary" value="Save Changes">
                    </p>
                </div>
            </form>
            
            <?php
            // Display changelog
            $changelog = $wpdb->get_results($wpdb->prepare(
                "SELECT c.*, u.display_name 
                FROM {$wpdb->prefix}alfies_order_changelog c
                LEFT JOIN {$wpdb->prefix}users u ON c.user_id = u.ID
                WHERE c.order_id = %s
                ORDER BY c.changed_at DESC",
                $order_id
            ));
            
            if ($changelog): ?>
                <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                    <h2>Change Log</h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Date</th>
                                <th style="width: 15%;">User</th>
                                <th style="width: 15%;">Field</th>
                                <th style="width: 27%;">Old Value</th>
                                <th style="width: 27%;">New Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($changelog as $log): ?>
                            <tr>
                                <td><?php echo date('M j, g:i A', strtotime($log->changed_at)); ?></td>
                                <td><?php echo esc_html($log->display_name); ?></td>
                                <td><?php echo esc_html(ucwords(str_replace('_', ' ', $log->field_name))); ?></td>
                                <td><code><?php echo esc_html(substr($log->old_value, 0, 50)) . (strlen($log->old_value) > 50 ? '...' : ''); ?></code></td>
                                <td><code><?php echo esc_html(substr($log->new_value, 0, 50)) . (strlen($log->new_value) > 50 ? '...' : ''); ?></code></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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