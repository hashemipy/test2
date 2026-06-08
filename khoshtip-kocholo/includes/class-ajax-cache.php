<?php
/**
 * AJAX Response Caching
 * 
 * Reduces database queries by caching AJAX responses
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_AJAX_Cache {
    
    const CACHE_DURATION = 3600; // 1 hour
    
    /**
     * Get cached AJAX response
     */
    public static function get($action, $params = []) {
        $cache_key = self::generate_key($action, $params);
        return get_transient($cache_key);
    }
    
    /**
     * Set AJAX response cache
     */
    public static function set($action, $params = [], $data, $duration = self::CACHE_DURATION) {
        $cache_key = self::generate_key($action, $params);
        set_transient($cache_key, $data, $duration);
    }
    
    /**
     * Clear AJAX cache
     */
    public static function clear($action, $params = []) {
        $cache_key = self::generate_key($action, $params);
        delete_transient($cache_key);
    }
    
    /**
     * Clear all AJAX caches
     */
    public static function clear_all() {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_k_ajax_%'"
        );
    }
    
    /**
     * Generate cache key
     */
    private static function generate_key($action, $params = []) {
        $hash = md5(json_encode($params));
        return "k_ajax_{$action}_{$hash}";
    }
}
