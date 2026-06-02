<?php
/**
 * Helper functions for the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Helper {
    
    /**
     * Sanitize repeater array data
     */
    public static function sanitize_repeater_array($input) {
        if (!is_array($input)) {
            return [];
        }
        
        $sanitized = [];
        
        foreach ($input as $item) {
            if (!is_array($item)) {
                continue;
            }
            
            $sanitized_item = [];
            $has_content = false;
            
            foreach ($item as $key => $value) {
                if (is_array($value)) {
                    $sanitized_item[$key] = self::sanitize_repeater_array($value);
                    if (!empty($sanitized_item[$key])) {
                        $has_content = true;
                    }
                } else {
                    if (in_array($key, ['url', 'button_url', 'link', 'image', 'modal_image'], true)) {
                        $sanitized_value = esc_url_raw($value);
                    } else {
                        $sanitized_value = sanitize_text_field($value);
                    }
                    $sanitized_item[$key] = $sanitized_value;
                    if (!empty($sanitized_value)) {
                        $has_content = true;
                    }
                }
            }
            
            if ($has_content) {
                $sanitized[] = $sanitized_item;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize integer array
     */
    public static function sanitize_int_array($input) {
        if (!is_array($input)) {
            return [];
        }
        
        return array_filter(array_map('absint', $input));
    }
    
    /**
     * Get products by category
     */
    public static function get_products_by_category($category_id) {
        if (!function_exists('wc_get_products') || empty($category_id)) {
            return [];
        }
        
        $category_ids = is_array($category_id) ? $category_id : [absint($category_id)];
        
        return wc_get_products([
            'limit' => -1,
            'status' => 'publish',
            'stock_status' => 'instock',
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_ids,
                ]
            ]
        ]);
    }
    
    /**
     * Filter in-stock products
     */
    public static function filter_in_stock($product_ids) {
        return array_values(array_filter($product_ids, function($product_id) {
            $product = wc_get_product($product_id);
            return $product && $product->is_in_stock();
        }));
    }
    
    /**
     * Get WooCommerce categories
     */
    public static function get_product_categories() {
        if (!function_exists('get_terms')) {
            return [];
        }
        
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ]);
        
        return is_wp_error($terms) ? [] : $terms;
    }
    
    /**
     * Check if option value is array (for backward compatibility)
     */
    public static function ensure_array($value) {
        if (!is_array($value) && !empty($value)) {
            return [$value];
        }
        return is_array($value) ? $value : [];
    }
}
