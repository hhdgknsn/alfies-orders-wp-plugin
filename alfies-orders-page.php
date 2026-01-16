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
        <style>
            .wrap {
                font-family: 'Poppins', Arial, sans-serif !important;
                background: #3B5049;
                height: auto;
                width: auto;
                margin: -10px -20px -20px -20px;
                padding: 3rem;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .card {
                width: 100%;
                border: solid blue;
            }

            h1 {
                font-size: 2rem;
                margin-bottom: 20px;
                color: #fff;
                text-transform: uppercase;
                font-family: 'Poppins', Arial, Helvetica, sans-serif;
                font-weight: 600;
            }
           
        </style>
        <div class="wrap">
            <div style="max-width: 900px; width: 100%; position: relative; margin-bottom: 20px;">
                <a href="<?php echo admin_url('admin.php?page=alfies-orders'); ?>" 
                style="color: #BD8D4B; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; position: absolute; left: 0;">
                    <span style="font-size: 18px;">←</span> Back to Orders
                </a>
                <h1 style="color: #fff; font-family: 'Poppins', Arial, sans-serif !important; font-weight: 500; text-align: center; margin: 0;">Order Details</h1>
            </div>
            
            
            <form method="post" action="" style="background: #3B5049; display: flex; flex-direction: column; align-items: center; width: 100%;">
                <?php wp_nonce_field('save_order_' . $order_id); ?>
                
                <div class="card" style="max-width: 900px; padding: 30px; margin-top: 20px; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); width: 100%;">
                    <h2 style="margin: 0 0 25px 0; padding-bottom: 15px; border-bottom: 3px solid #BD8D4B; color: #2F403A; font-size: 24px;"><?php echo esc_html($order->order_id); ?></h2>
                    
                    <table class="form-table" style="margin-top: 0;">
                        <tr>
                            <th style="padding: 15px 10px 15px 0; width: 180px; color: #3B5049; font-weight: 600;">Status:</th>
                            <td style="padding: 15px 0;">
                                <select name="status" style="width: 200px; padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px;">
                                    <option value="pending" <?php selected($order->status, 'pending'); ?>>Pending</option>
                                    <option value="confirmed" <?php selected($order->status, 'confirmed'); ?>>Confirmed</option>
                                    <option value="completed" <?php selected($order->status, 'completed'); ?>>Completed</option>
                                    <option value="cancelled" <?php selected($order->status, 'cancelled'); ?>>Cancelled</option>
                                </select>
                            </td>
                        </tr>
                        <tr style="border-top: 1px solid #f3f4f6;">
                            <th style="padding: 15px 10px 15px 0; color: #3B5049; font-weight: 600;">Customer:</th>
                            <td style="padding: 15px 0;">
                                <input type="text" name="name" value="<?php echo esc_attr($order->name); ?>" class="regular-text" style="padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                            </td>
                        </tr>
                        <tr style="border-top: 1px solid #f3f4f6;">
                            <th style="padding: 15px 10px 15px 0; color: #3B5049; font-weight: 600;">Email:</th>
                            <td style="padding: 15px 0;">
                                <input type="email" name="email" value="<?php echo esc_attr($order->email); ?>" class="regular-text" style="padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                            </td>
                        </tr>
                        <tr style="border-top: 1px solid #f3f4f6;">
                            <th style="padding: 15px 10px 15px 0; color: #3B5049; font-weight: 600;">Phone:</th>
                            <td style="padding: 15px 0;">
                                <input type="text" name="phone" value="<?php echo esc_attr($order->phone); ?>" class="regular-text" style="padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                            </td>
                        </tr>
                        <tr style="border-top: 1px solid #f3f4f6;">
                            <th style="padding: 15px 10px 15px 0; color: #3B5049; font-weight: 600;">Number of People:</th>
                            <td style="padding: 15px 0;">
                                <input type="number" name="no_people" value="<?php echo esc_attr($order->no_people); ?>" min="1" style="width: 100px; padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                            </td>
                        </tr>
                        <tr style="border-top: 1px solid #f3f4f6;">
                            <th style="padding: 15px 10px 15px 0; color: #3B5049; font-weight: 600;">Event Date:</th>
                            <td style="padding: 15px 0;">
                                <input type="date" name="event_date" value="<?php echo esc_attr($order->event_date); ?>" style="padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px;">
                            </td>
                        </tr>
                        <tr style="border-top: 1px solid #f3f4f6;">
                            <th style="padding: 15px 10px 15px 0; color: #3B5049; font-weight: 600; vertical-align: top; padding-top: 18px;">Items Ordered:</th>
                            <td style="padding: 15px 0;">
                                <textarea name="items" rows="6" class="large-text" style="padding: 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-family: monospace; font-size: 13px;"><?php echo esc_textarea($order->items); ?></textarea>
                            </td>
                        </tr>
                        <tr style="border-top: 1px solid #f3f4f6;">
                            <th style="padding: 15px 10px 15px 0; color: #3B5049; font-weight: 600; vertical-align: top; padding-top: 18px;">Message:</th>
                            <td style="padding: 15px 0;">
                                <textarea name="message" rows="4" class="large-text" style="padding: 12px; border: 2px solid #e5e7eb; border-radius: 6px;"><?php echo esc_textarea($order->message); ?></textarea>
                            </td>
                        </tr>
                        <tr style="border-top: 1px solid #f3f4f6;">
                            <th style="padding: 15px 10px 15px 0; color: #3B5049; font-weight: 600;">Price:</th>
                            <td style="padding: 15px 0;">
                                <span style="font-size: 18px; font-weight: 600; color: #BD8D4B; margin-right: 5px;">£</span>
                                <input type="number" name="price" value="<?php echo esc_attr($order->price); ?>" step="0.01" min="0" style="width: 120px; padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 16px;">
                            </td>
                        </tr>
                        <tr style="border-top: 1px solid #f3f4f6;">
                            <th style="padding: 15px 10px 15px 0; color: #3B5049; font-weight: 600;">Order Created:</th>
                            <td style="padding: 15px 0; color: #6b7280;">
                                <?php echo date('M j, Y g:i A', strtotime($order->created_at)); ?> 
                                <em style="color: #9ca3af;">(<?php echo alfies_time_ago($order->created_at); ?>)</em>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit" style="margin: 25px 0 0 0; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                        <input type="submit" name="save_order" class="button button-primary" value="Save Changes" style="background: #fff; color: #3B5049; border-color: #3B5049; padding: 7px 20px; font-size: 14px; font-weight: 600; border-radius: 6px;">
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
                <div class="card" style="max-width: 900px; padding: 30px; margin-top: 20px; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                    <h2 style="margin: 0 0 20px 0; padding-bottom: 15px; border-bottom: 3px solid #BD8D4B; color: #2F403A; font-size: 20px;">Change Log</h2>
                    <div style="background: #fff; border-radius: 8px; overflow: hidden;">
                        <table class="wp-list-table widefat fixed striped" style="border: 1px solid #e5e7eb;">
                            <thead>
                                <tr style="background: #f9fafb;">
                                    <th style="width: 15%; padding: 12px; font-weight: 600; color: #3B5049; border-bottom: 2px solid #e5e7eb;">Date</th>
                                    <th style="width: 15%; padding: 12px; font-weight: 600; color: #3B5049; border-bottom: 2px solid #e5e7eb;">User</th>
                                    <th style="width: 15%; padding: 12px; font-weight: 600; color: #3B5049; border-bottom: 2px solid #e5e7eb;">Field</th>
                                    <th style="width: 27%; padding: 12px; font-weight: 600; color: #3B5049; border-bottom: 2px solid #e5e7eb;">Old Value</th>
                                    <th style="width: 27%; padding: 12px; font-weight: 600; color: #3B5049; border-bottom: 2px solid #e5e7eb;">New Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($changelog as $log): ?>
                                <tr>
                                    <td style="padding: 10px 12px; color: #6b7280; font-size: 13px;"><?php echo date('M j, g:i A', strtotime($log->changed_at)); ?></td>
                                    <td style="padding: 10px 12px; color: #2F403A;"><?php echo esc_html($log->display_name); ?></td>
                                    <td style="padding: 10px 12px; color: #2F403A; font-weight: 500;"><?php echo esc_html(ucwords(str_replace('_', ' ', $log->field_name))); ?></td>
                                    <td style="padding: 10px 12px;"><code style="background: #f3f4f6; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #ef4444;"><?php echo esc_html(substr($log->old_value, 0, 50)) . (strlen($log->old_value) > 50 ? '...' : ''); ?></code></td>
                                    <td style="padding: 10px 12px;"><code style="background: #f3f4f6; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #10b981;"><?php echo esc_html(substr($log->new_value, 0, 50)) . (strlen($log->new_value) > 50 ? '...' : ''); ?></code></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
    <style>
            .wrap {
                font-family: 'Poppins', Arial, sans-serif !important;
                background: #3B5049;
                min-height: 100vh;
                width: auto;
                margin: -10px -20px -20px -20px;
                padding: 6rem 7rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 2rem;
            }

            .card {
                width: 100%;
                border: solid blue;
            }

            h1 {
                font-size: 2rem;
                margin-bottom: 20px;
                color: #fff;
                text-transform: uppercase;
                font-family: 'Poppins', Arial, Helvetica, sans-serif;
                font-weight: 600;
            }
           
        </style>
    <div class="wrap">
        <h1 style="font-size: 2rem; margin-bottom: 20px; color: #fff; text-transform: uppercase; font-family: 'Poppins', Arial, Helvetica, sans-serif; font-weight: 500;">Alfies Orders</h1>
        <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
            <table class="wp-list-table widefat fixed striped" style="border: none;">
                <thead>
                    <tr style="background: #fff; border-bottom: 3px solid #BD8D4B !important;">
                        <th style="padding: 15px; font-weight: 600; border: none; color: #2F403A; border-bottom: 3px solid #BD8D4B;">Order ID</th>
                        <th style="padding: 15px; font-weight: 600; border: none; color: #2F403A; border-bottom: 3px solid #BD8D4B;">Name</th>
                        <th style="padding: 15px; font-weight: 600; border: none; color: #2F403A; border-bottom: 3px solid #BD8D4B;">Email</th>
                        <th style="padding: 15px; font-weight: 600; border: none; color: #2F403A; border-bottom: 3px solid #BD8D4B;">People</th>
                        <th style="padding: 15px; font-weight: 600; border: none; color: #2F403A; border-bottom: 3px solid #BD8D4B;">Price</th>
                        <th style="padding: 15px; font-weight: 600; border: none; color: #2F403A; border-bottom: 3px solid #BD8D4B;">Date</th>
                        <th style="padding: 15px; font-weight: 600; border: none; color: #2F403A; border-bottom: 3px solid #BD8D4B;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): 
                        $view_url = add_query_arg('view_order', $order->order_id);
                        $status_color = [
                            'pending' => '#f59e0b',
                            'confirmed' => '#3b82f6',
                            'completed' => '#10b981',
                            'cancelled' => '#ef4444'
                        ][$order->status] ?? '#6b7280';
                    ?>
                    <tr style="cursor: pointer; transition: background 0.2s;" 
                        onclick="window.location='<?php echo esc_url($view_url); ?>'"
                        onmouseover="this.style.background='#f9fafb'" 
                        onmouseout="this.style.background='inherit'">
                        <td style="padding: 12px 15px;"><strong style="color: #3B5049;"><?php echo esc_html($order->order_id); ?></strong></td>
                        <td style="padding: 12px 15px;"><?php echo esc_html($order->name); ?></td>
                        <td style="padding: 12px 15px; color: #6b7280;"><?php echo esc_html($order->email); ?></td>
                        <td style="padding: 12px 15px;"><?php echo esc_html($order->no_people); ?></td>
                        <td style="padding: 12px 15px; font-weight: 600; color: #BD8D4B;">£<?php echo number_format($order->price, 2); ?></td>
                        <td style="padding: 12px 15px; color: #6b7280;"><?php echo alfies_time_ago($order->created_at); ?></td>
                        <td style="padding: 12px 15px;">
                            <span style="display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; color: #fff; background: <?php echo $status_color; ?>;">
                                <?php echo esc_html(ucfirst($order->status)); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}