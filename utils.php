
<?php

add_filter('wp_mail_from', function() {
    return 'noreply@holly.sandbox.bubbledesign.co.uk';
});

add_filter('wp_mail_from_name', function() {
    return "Alfie's Deli";
});


function calculate_order_price($items, $no_people) {
    $items_lower = strtolower($items);
    $people = intval($no_people);
    
    $pricing_map = [
        'classic-sandwich' => 8.50,
        'deluxe-sandwich' => 12.50,
        'breakfast-pastry' => 6.00,
        'continental-breakfast' => 9.50,
        'hot-breakfast' => 14.00,
        'mixed-platter' => 10.00,
        'vegetarian' => 9.50,
        'vegan' => 10.50,
        'salad-bar' => 8.00,
        'dessert' => 5.50
    ];
    
    $total = 0;
    foreach ($pricing_map as $key => $price) {
        if (strpos($items_lower, $key) !== false) {
            $total += ($price * $people);
        }
    }
    
    return [
        'per_person' => $people > 0 ? $total / $people : 0,
        'total' => $total
    ];
}


function alfies_time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return $diff . ' seconds ago';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return floor($diff / 86400) . ' days ago';
}

function build_customer_email($name, $items, $no_people, $pricing) {
    $items_html = nl2br($items);
    $price_display = '£' . number_format($pricing['total'], 2);
    $per_person_display = '£' . number_format($pricing['per_person'], 2);
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: 'Poppins', Arial, Helvetica, sans-serif; background-color: #3B5049;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #eaf1ed; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="80%" cellpadding="0" cellspacing="0" border="0" style="max-width: 900px; background-color: #ffffff; border-radius: 16px; box-shadow: 0 20px 60px rgba(43, 64, 58, 0.15);">
                    
                    <tr>
                        <td style="background-color: #3B5049; padding: 32px 16px; text-align: center;">
                            <img src="https://holly.sandbox.bubbledesign.co.uk/wp-content/uploads/2026/01/Logo-whitegold1.png" alt="Alfies Deli" width="180" style="max-width: 100%; height: auto; margin-bottom: 20px;">
                            <div style="font-size: 28px; font-weight: 700; color: #ffffff; margin-top: 15px; letter-spacing: -0.5px; text-transform: uppercase;">Order Received!</div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 50px 40px;">
                            
                            <h1 style="margin: 0 0 16px 0; font-size: 32px; font-weight: 700; color: #2F403A; line-height: 1.2;">Hello {$name}!</h1>
                            
                            <p style="margin: 0 0 40px 0; font-size: 16px; color: #5a6c7d; line-height: 1.7;">
                                Thank you for choosing Alfie's Deli for your catering needs. A member of our team will contact you shortly to confirm your order.
                            </p>
                            
                            <h2 style="margin: 0 0 20px 0; font-size: 20px; font-weight: 600; color: #2F403A; padding-bottom: 12px; border-bottom: 2px solid #BD8D4B; display: inline-block;">Order Summary</h2>
                            
                            <table width="100%" cellpadding="30px" cellspacing="20px" border="0" style="background-color: #f8f9fa; border-radius: 12px; border-left: 4px solid #BD8D4B; margin: 30px 0;">
                                <tr>
                                    <td style="padding: 0 0 20px 0; font-size: 14px; font-weight: 600; color: #3B5049; text-transform: uppercase; letter-spacing: 0.5px; width: 140px; vertical-align: top;">Guest Count:</td>
                                    <td style="padding: 0 0 20px 15px; font-size: 15px; color: #2F403A; font-weight: 500; vertical-align: top;">{$no_people} people</td>
                                </tr>
                                <tr>
                                    <td style="padding: 0 0 20px 0; font-size: 14px; font-weight: 600; color: #3B5049; text-transform: uppercase; letter-spacing: 0.5px; width: 140px; vertical-align: top;">Selection:</td>
                                    <td style="padding: 0 0 20px 15px; font-size: 15px; color: #2F403A; font-weight: 500; vertical-align: top; word-wrap: break-word;">{$items_html}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 0; font-size: 14px; font-weight: 600; color: #3B5049; text-transform: uppercase; letter-spacing: 0.5px; width: 140px; vertical-align: top;">Estimated Price:</td>
                                    <td style="padding: 0 0 0 15px; font-size: 18px; color: #BD8D4B; font-weight: 700; vertical-align: top;">{$price_display} <span style="font-size: 13px; color: #5a6c7d; font-weight: 400;">({$per_person_display} per person)</span></td>
                                </tr>
                            </table>
                            
                            <div style="height: 1px; background-color: #BD8D4B; margin: 40px 0;"></div>
                            
                            <p style="margin: 30px 0; font-size: 15px; color: #5a6c7d; text-align: center;">
                                Have questions? We're here to help make your event perfect.
                            </p>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
                                    <tr>
                                        <td style="background-color: #BD8D4B; border-radius: 8px; padding: 16px 30px;">
                                            <a href="tel:01234567890" style="color: #ffffff; text-decoration: none; font-weight: 600; font-size: 15px; text-transform: uppercase; letter-spacing: 1px; display: block;">CALL US</a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="background-color: #3B5049; padding: 40px 20px; text-align: center;">
                            
                            <img src="https://holly.sandbox.bubbledesign.co.uk/wp-content/uploads/2026/01/Logo-whitegold1.png" alt="Alfies Deli" width="200" style="max-width: 100%; height: auto; margin-bottom: 25px;">
                            
                            <div style="margin: 25px 0;">
                                <a href="#" style="display: inline-block; margin: 0 12px; vertical-align: middle;"><img src="https://holly.sandbox.bubbledesign.co.uk/wp-content/uploads/2026/01/find-us-on.png" width="100" alt="Find Us On" style="opacity: 0.9;"></a>
                                <a href="#" style="display: inline-block; margin: 0 8px; vertical-align: middle;"><img src="https://holly.sandbox.bubbledesign.co.uk/wp-content/uploads/2026/01/New-Project-3.png" width="32" height="32" alt="Facebook"></a>
                                <a href="#" style="display: inline-block; margin: 0 8px; vertical-align: middle;"><img src="https://holly.sandbox.bubbledesign.co.uk/wp-content/uploads/2026/01/instagram-border.png" width="32" height="32" alt="Instagram"></a>
                                <a href="#" style="display: inline-block; margin: 0 8px; vertical-align: middle;"><img src="https://holly.sandbox.bubbledesign.co.uk/wp-content/uploads/2026/01/linkedin-border.png" width="32" height="32" alt="LinkedIn"></a>
                                <a href="#" style="display: inline-block; margin: 0 8px; vertical-align: middle;"><img src="https://holly.sandbox.bubbledesign.co.uk/wp-content/uploads/2026/01/New-Project-3.png" width="32" height="32" alt="TikTok"></a>
                            </div>
                            
                            <div style="color: #BD8D4B; font-size: 11px; margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(189, 141, 75, 0.2); line-height: 1.6;">
                                © Alfie's Deli All rights reserved. Registered in England and Wales<br>
                                <a href="#" style="color: #BD8D4B; text-decoration: none;">Privacy Policy</a> | <a href="#" style="color: #BD8D4B; text-decoration: none;">Terms &amp; Conditions</a>
                            </div>
                            
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}


function build_admin_email($name, $email, $phone, $items, $no_people, $message, $pricing) {
    $items_html = nl2br($items);
    $message_html = nl2br($message);
    $price_display = '£' . number_format($pricing['total'], 2);
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: 'Poppins', Arial, Helvetica, sans-serif; background-color: #3B5049;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #eaf1ed; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="80%" cellpadding="0" cellspacing="0" border="0" style="max-width: 900px; background-color: #ffffff; border-radius: 16px; box-shadow: 0 20px 60px rgba(43, 64, 58, 0.15);">
                    
                    <tr>
                        <td style="background-color: #3B5049; padding: 32px 16px; text-align: center;">
                            <img src="https://holly.sandbox.bubbledesign.co.uk/wp-content/uploads/2026/01/Logo-whitegold1.png" alt="Alfies Deli" width="180" style="max-width: 100%; height: auto; margin-bottom: 20px;">
                            <div style="font-size: 28px; font-weight: 700; color: #ffffff; margin-top: 15px; letter-spacing: -0.5px; text-transform: uppercase;">New Order Received</div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 50px 40px;">
                            
                            <h2 style="margin: 0 0 20px 0; font-size: 20px; font-weight: 600; color: #2F403A; padding-bottom: 12px; border-bottom: 2px solid #BD8D4B; display: inline-block;">Customer Details</h2>
                            
                            <table width="100%" cellpadding="30px" cellspacing="20px" border="0" style="background-color: #f8f9fa; border-radius: 12px; border-left: 4px solid #BD8D4B; margin: 30px 0;">
                                <tr>
                                    <td style="padding: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #3B5049; text-transform: uppercase; letter-spacing: 0.5px; width: 140px; vertical-align: top;">Name:</td>
                                    <td style="padding: 0 0 15px 15px; font-size: 15px; color: #2F403A; font-weight: 500; vertical-align: top;">{$name}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #3B5049; text-transform: uppercase; letter-spacing: 0.5px; width: 140px; vertical-align: top;">Email:</td>
                                    <td style="padding: 0 0 15px 15px; font-size: 15px; color: #2F403A; font-weight: 500; vertical-align: top;">{$email}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 0; font-size: 14px; font-weight: 600; color: #3B5049; text-transform: uppercase; letter-spacing: 0.5px; width: 140px; vertical-align: top;">Phone:</td>
                                    <td style="padding: 0 0 0 15px; font-size: 15px; color: #2F403A; font-weight: 500; vertical-align: top;">{$phone}</td>
                                </tr>
                            </table>
                            
                            <div style="height: 1px; background-color: #BD8D4B; margin: 40px 0;"></div>
                            
                            <h2 style="margin: 0 0 20px 0; font-size: 20px; font-weight: 600; color: #2F403A; padding-bottom: 12px; border-bottom: 2px solid #BD8D4B; display: inline-block;">Order Summary</h2>
                            
                            <table width="100%" cellpadding="30px" cellspacing="20px" border="0" style="background-color: #f8f9fa; border-radius: 12px; border-left: 4px solid #BD8D4B; margin: 30px 0;">
                                <tr>
                                    <td style="padding: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #3B5049; text-transform: uppercase; letter-spacing: 0.5px; width: 140px; vertical-align: top;">Guest Count:</td>
                                    <td style="padding: 0 0 15px 15px; font-size: 15px; color: #2F403A; font-weight: 500; vertical-align: top;">{$no_people} people</td>
                                </tr>
                                <tr>
                                    <td style="padding: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #3B5049; text-transform: uppercase; letter-spacing: 0.5px; width: 140px; vertical-align: top;">Selection:</td>
                                    <td style="padding: 0 0 15px 15px; font-size: 15px; color: #2F403A; font-weight: 500; vertical-align: top; word-wrap: break-word;">{$items_html}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #3B5049; text-transform: uppercase; letter-spacing: 0.5px; width: 140px; vertical-align: top;">Message:</td>
                                    <td style="padding: 0 0 15px 15px; font-size: 15px; color: #2F403A; font-weight: 500; vertical-align: top; word-wrap: break-word;">{$message_html}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 0; font-size: 14px; font-weight: 600; color: #3B5049; text-transform: uppercase; letter-spacing: 0.5px; width: 140px; vertical-align: top;">Estimated Price:</td>
                                    <td style="padding: 0 0 0 15px; font-size: 18px; color: #BD8D4B; font-weight: 700; vertical-align: top;">{$price_display}</td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="background-color: #3B5049; padding: 40px 20px; text-align: center;">
                            <img src="https://holly.sandbox.bubbledesign.co.uk/wp-content/uploads/2026/01/Logo-whitegold1.png" alt="Alfies Deli" width="200" style="max-width: 100%; height: auto;">
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}
