<?php
/**
 * بهینه‌سازی برای شبکه اینترانتی بدون دسترسی به اینترنت جهانی
 * Intranet Optimization - Disable all external CDN and API calls
 */

// 1. غیرفعال کردن Google Fonts CDN
add_filter('wp_enqueue_scripts', function() {
    // Remove parent theme Google Fonts if loaded
    wp_dequeue_style('astra-google-fonts');
    wp_dequeue_style('astra-fonts');
}, 100);

// 2. غیرفعال کردن Gravatar (سرویس خارجی)
add_filter('get_avatar', function($avatar) {
    // Return empty avatar to prevent external Gravatar calls
    return str_replace('http://','https://', $avatar);
}, 10, 1);

add_filter('get_avatar_url', function($url) {
    // Check if it's a Gravatar URL and return empty
    if (strpos($url, 'gravatar') !== false || strpos($url, 'wordpress.com') !== false) {
        return '';
    }
    return $url;
}, 10, 1);

// 3. غیرفعال کردن DNS Prefetch برای سرویس‌های خارجی
add_action('wp_head', function() {
    // Remove DNS prefetch for external services
    $output = ob_get_clean();
    $output = preg_replace('/<link rel="dns-prefetch"[^>]*\/>/i', '', $output);
    echo $output;
}, 1);

// 4. غیرفعال کردن Google Analytics و مشابه
add_action('init', function() {
    wp_deregister_script('google-analytics');
    wp_deregister_script('google-ads');
});

// 5. بلاک کردن درخواست‌های CORS خارجی
add_filter('allowed_http_origins', function($origins) {
    // Only allow local requests
    $local = array(
        home_url(),
        network_home_url(),
    );
    return array_unique($local);
}, 10, 1);

// 6. غیرفعال کردن Emoji CDN
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');

// 7. غیرفعال کردن WordPress REST API (برای امنیت اضافی)
// اختیاری - فقط اگر استفاده نمی‌کنید
// add_filter('rest_authentication_errors', function($result) {
//     if (!is_user_logged_in()) {
//         return new WP_Error('rest_not_logged_in', 'You are not currently logged in.', array('status' => 401));
//     }
//     return $result;
// });

// 8. غیرفعال کردن تلمتری WP-CLI
add_filter('wp_doing_wp_cron', '__return_false__');

// 9. بهینه‌سازی image lazy loading - برای کاهش بار شبکه
add_filter('wp_img_tag_add_loading_attr', '__return_true__', 10, 3);

// 10. غیرفعال کردن external link check
add_filter('check_comment_flood', '__return_false__');

// 11. حذف ورژن WordPress از header برای امنیت
remove_action('wp_head', 'wp_generator');

// 12. غیرفعال کردن تحدیثات اتوماتیک (برای اینترانت)
define('AUTOMATIC_UPDATER_DISABLED', true);

// 13. اضافه کردن cache headers برای بهتر بودن عملکرد
add_action('send_headers', function() {
    header('Cache-Control: max-age=3600, must-revalidate');
    header('Pragma: public');
});

// 14. غیرفعال کردن XML-RPC (برای امنیت)
add_filter('xmlrpc_enabled', '__return_false__');

// 15. بهینه‌سازی database queries
add_action('init', function() {
    // Show query time in comments
    if (WP_DEBUG && WP_DEBUG_LOG) {
        add_filter('comments_open', function($open, $post_id) {
            return $open;
        }, 10, 2);
    }
});

// 16. کاهش تعداد revision‌ها
define('WP_POST_REVISIONS', 3);

// 17. تنظیم trash cleanup
define('EMPTY_TRASH_DAYS', 7);

// 18. بهینه‌سازی heartbeat (کاهش درخواست‌های AJAX)
add_action('init', function() {
    wp_deregister_script('heartbeat');
});

// 19. غیرفعال کردن plugin و theme update checks
add_filter('auto_update_plugin', '__return_false__');
add_filter('auto_update_theme', '__return_false__');

// 20. بهینه‌سازی WooCommerce اگر استفاده می‌شود
add_action('wp_enqueue_scripts', function() {
    if (function_exists('is_woocommerce')) {
        // Remove unnecessary WooCommerce scripts
        wp_dequeue_script('wc-add-to-cart');
        wp_dequeue_script('wc-add-to-cart-variation');
    }
}, 100);
?>
