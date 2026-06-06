<?php
/**
 * Quick Enable/Disable Authentication Systems
 * تفعیل/غیرفعال‌سازی سریع سیستم‌های احراز هویت
 * 
 * For FILTERED NETWORKS (Iran/Similar):
 * Set KHOSHTIP_ENABLE_GOOGLE_AUTH to false
 * Set KHOSHTIP_ENABLE_SMS_AUTH to false
 * 
 * This will speed up page loads significantly
 */

// Define these in wp-config.php to control Auth systems
// define('KHOSHTIP_ENABLE_GOOGLE_AUTH', false);  // Set to true to enable
// define('KHOSHTIP_ENABLE_SMS_AUTH', false);     // Set to true to enable
// define('KHOSHTIP_ENABLE_CACHING', true);       // Set to false to disable caching

// Default values if not defined
if (!defined('KHOSHTIP_ENABLE_GOOGLE_AUTH')) {
    define('KHOSHTIP_ENABLE_GOOGLE_AUTH', false);
}

if (!defined('KHOSHTIP_ENABLE_SMS_AUTH')) {
    define('KHOSHTIP_ENABLE_SMS_AUTH', false);
}

if (!defined('KHOSHTIP_ENABLE_CACHING')) {
    define('KHOSHTIP_ENABLE_CACHING', true);
}

/**
 * Log configuration for debugging
 */
function khoshtip_log_config() {
    if (WP_DEBUG && WP_DEBUG_LOG) {
        $config = array(
            'Google Auth' => KHOSHTIP_ENABLE_GOOGLE_AUTH ? 'ENABLED' : 'DISABLED',
            'SMS Auth' => KHOSHTIP_ENABLE_SMS_AUTH ? 'ENABLED' : 'DISABLED',
            'Caching' => KHOSHTIP_ENABLE_CACHING ? 'ENABLED' : 'DISABLED',
        );
        error_log('[Khoshtip Config] ' . json_encode($config));
    }
}
add_action('init', 'khoshtip_log_config', 1);

/**
 * How to Enable Auth Systems (for testing):
 * 
 * In wp-config.php, add BEFORE "That's all, stop editing!":
 * 
 * // Enable/Disable Khoshtip Authentication
 * define('KHOSHTIP_ENABLE_GOOGLE_AUTH', true);  // Set to true to enable Google Auth
 * define('KHOSHTIP_ENABLE_SMS_AUTH', true);     // Set to true to enable SMS Auth
 * 
 * Then the plugin will load these systems.
 */
