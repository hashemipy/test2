<?php
/**
 * Smart Plugin Loader with Lazy Loading
 * 
 * This class optimizes plugin performance by:
 * - Loading only necessary files on demand
 * - Caching class instances
 * - Deferring non-critical initialization
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Loader {
    
    private static $instances = [];
    private static $loaded_files = [];
    
    /**
     * Lazy load a class
     */
    public static function get($class_name) {
        if (isset(self::$instances[$class_name])) {
            return self::$instances[$class_name];
        }
        
        // Map class names to file paths
        $file_map = [
            'KK_Helper' => 'class-helper.php',
            'KK_Cache' => 'class-cache.php',
            'KK_Admin' => 'admin/class-admin.php',
            'KK_Admin_Tabs' => 'admin/class-admin-tabs.php',
            'KK_Google_Auth' => 'auth/class-google-auth.php',
            'KK_SMS_Auth' => 'auth/class-sms-auth.php',
            'KK_Shortcodes' => 'shortcodes/class-shortcodes.php',
            'KK_Ajax_Handler' => 'ajax/class-ajax-handler.php',
        ];
        
        if (!isset($file_map[$class_name])) {
            return null;
        }
        
        $file_path = KK_PLUGIN_DIR . 'includes/' . $file_map[$class_name];
        
        if (!file_exists($file_path)) {
            trigger_error("[KK] File not found: {$file_path}");
            return null;
        }
        
        if (!isset(self::$loaded_files[$file_path])) {
            require_once $file_path;
            self::$loaded_files[$file_path] = true;
        }
        
        // Create and cache instance
        if (method_exists($class_name, 'instance')) {
            self::$instances[$class_name] = call_user_func([$class_name, 'instance']);
        } else {
            self::$instances[$class_name] = new $class_name();
        }
        
        return self::$instances[$class_name];
    }
    
    /**
     * Load file once
     */
    public static function load_file($file_path) {
        if (!isset(self::$loaded_files[$file_path])) {
            if (file_exists($file_path)) {
                require_once $file_path;
                self::$loaded_files[$file_path] = true;
                return true;
            }
        }
        return isset(self::$loaded_files[$file_path]);
    }
    
    /**
     * Get loaded status
     */
    public static function is_loaded($file_path) {
        return isset(self::$loaded_files[$file_path]);
    }
}
