<?php
/**
 * AJAX Handler for sales and product management
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Ajax_Handler {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_k_search_products', [$this, 'search_products']);
        add_action('wp_ajax_k_get_products_by_category', [$this, 'get_products_by_category']);
        add_action('wp_ajax_k_start_sale', [$this, 'start_sale']);
        add_action('wp_ajax_k_cancel_sale', [$this, 'cancel_sale']);
        add_action('wp_ajax_k_remove_all_discounts', [$this, 'remove_all_discounts']);
        add_action('wp_ajax_k_remove_discount', [$this, 'remove_discount']);
        
        // Price adjustment handlers
        add_action('wp_ajax_k_apply_price_increase', [$this, 'apply_price_increase']);
        add_action('wp_ajax_k_revert_prices', [$this, 'revert_prices']);
        add_action('wp_ajax_k_get_price_history', [$this, 'get_price_history']);
        
        add_action('wp_ajax_khoshtip_search_by_size', [$this, 'search_by_size']);
        add_action('wp_ajax_nopriv_khoshtip_search_by_size', [$this, 'search_by_size']);
        
        add_action('wp_ajax_k_get_category_products', [$this, 'get_category_products']);
        add_action('wp_ajax_nopriv_k_get_category_products', [$this, 'get_category_products']);
        
        add_action('wp_ajax_khoshtip_search_products', [$this, 'search_products_by_name']);
        add_action('wp_ajax_nopriv_khoshtip_search_products', [$this, 'search_products_by_name']);
        
        add_action('wp_ajax_k_get_cart_count', [$this, 'get_cart_count']);
        add_action('wp_ajax_nopriv_k_get_cart_count', [$this, 'get_cart_count']);
    }
    
    public function search_products() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error();
        }
        
        $search = sanitize_text_field($_GET['q'] ?? '');
        $page = absint($_GET['page'] ?? 1);
        
        if (!function_exists('wc_get_products')) {
            wp_send_json(['results' => []]);
            return;
        }
        
        $products = wc_get_products([
            'limit' => 20,
            'page' => $page,
            'status' => 'publish',
            's' => $search
        ]);
        
        $results = [];
        foreach ($products as $product) {
            $results[] = [
                'id' => $product->get_id(),
                'text' => $product->get_name() . ' (#' . $product->get_id() . ')'
            ];
        }
        
        wp_send_json(['results' => $results]);
    }
    
    public function get_products_by_category() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی رد شد', 'khoshtip-kocholo')]);
        }
        
        if (!function_exists('wc_get_products')) {
            wp_send_json_success(['products' => [], 'has_more' => false]);
            return;
        }
        
        $category_id = absint($_POST['category_id'] ?? 0);
        $page = absint($_POST['page'] ?? 1);
        $per_page = -1; // نمایش همه محصولات بدون محدودیت
        
        $args = [
            'status' => 'publish',
            'limit' => $per_page,
            'page' => $page,
            'paginate' => true,
            'return' => 'ids'  // فقط ID ها رو بگیر
        ];
        
        if ($category_id > 0) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_id,
                    'operator' => 'IN'
                ]
            ];
        }
        
        $results = wc_get_products($args);
        $product_ids = $results->products;
        $has_more = false;
        
        $list = [];
        
        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) continue;
            
            $type = $product->get_type();
            $has_discount = false;
            
            if ($type === 'variable') {
                // چک کردن موجودی برای محصولات متغیر
                $children = $product->get_children();
                $has_stock = false;
                
                foreach ($children as $child_id) {
                    $variation = wc_get_product($child_id);
                    if ($variation && $variation->is_in_stock()) {
                        $has_stock = true;
                        break;
                    }
                }
                
                // اگر هیچ variant موجود نداشت، این محصول را نمایش نده
                if (!$has_stock) {
                    continue;
                }
                
                // استفاده از get_post_meta به جای get_available_variations که خیلی سبک‌تره
                foreach ($children as $child_id) {
                    $sale_price = get_post_meta($child_id, '_sale_price', true);
                    if (!empty($sale_price) && $sale_price > 0) {
                        $has_discount = true;
                        break;
                    }
                }
                $price = get_post_meta($product_id, '_min_variation_regular_price', true);
                if (empty($price)) {
                    $price = $product->get_variation_regular_price('min');
                }
            } else {
                // چک کردن موجودی برای محصولات ساده
                if (!$product->is_in_stock()) {
                    continue;
                }
                
                $sale_price = get_post_meta($product_id, '_sale_price', true);
                $has_discount = !empty($sale_price) && $sale_price > 0;
                $price = get_post_meta($product_id, '_regular_price', true);
            }
            
            $list[] = [
                'id' => $product_id,
                'name' => $product->get_name(),
                'regular_price' => wc_price($price),
                'has_discount' => $has_discount,
                'type' => $type
            ];
        }
        
        wp_send_json_success([
            'products' => $list,
            'has_more' => $has_more,
            'page' => $page,
            'total' => $results->total
        ]);
    }
    
    public function start_sale() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی رد شد', 'khoshtip-kocholo')]);
        }
        
        if (!function_exists('wc_get_product')) {
            wp_send_json_error(['message' => __('ووک��مرس فعال نیست', 'khoshtip-kocholo')]);
        }
        
        $product_ids = isset($_POST['product_ids']) ? array_map('absint', $_POST['product_ids']) : [];
        $discount = absint($_POST['discount_percent'] ?? 0);
        $hours = absint($_POST['sale_hours'] ?? 0);
        $minutes = absint($_POST['sale_minutes'] ?? 0);
        
        if (empty($product_ids) || $discount <= 0 || $discount >= 100) {
            wp_send_json_error(['message' => __('اطلاعات نامعتبر', 'khoshtip-kocholo')]);
        }
        
        if ($hours <= 0 && $minutes <= 0) {
            wp_send_json_error(['message' => __('مدت زمان وارد کنید', 'khoshtip-kocholo')]);
        }
        
        $duration = ($hours * 3600) + ($minutes * 60);
        $end_timestamp = time() + $duration;
        
        update_option('k_sale_hours', $hours);
        update_option('k_sale_minutes', $minutes);
        update_option('k_sale_end_timestamp', $end_timestamp);
        
        $discounted = [];
        
        foreach ($product_ids as $id) {
            $product = wc_get_product($id);
            if (!$product) continue;
            
            if ($product->get_type() === 'variable') {
                $variations = $product->get_children();
                foreach ($variations as $var_id) {
                    $variation = wc_get_product($var_id);
                    if ($variation) {
                        $regular = $variation->get_regular_price();
                        if ($regular > 0) {
                            $sale = $regular * (1 - ($discount / 100));
                            $variation->set_sale_price(wc_format_decimal($sale, 2));
                            $variation->save();
                        }
                    }
                }
                $discounted[] = $id;
            } else {
                $regular = $product->get_regular_price();
                if ($regular > 0) {
                    $sale = $regular * (1 - ($discount / 100));
                    $product->set_sale_price(wc_format_decimal($sale, 2));
                    $product->save();
                    $discounted[] = $id;
                }
            }
        }
        
        // Save to k_discounted_products for the list display
        update_option('k_discounted_products', $discounted);
        update_option('k_sale_product_ids', $discounted);
        KK_Cache::clear_products_cache();
        
        wp_send_json_success([
            'message' => sprintf(__('%d محصول تخفیف خوردند', 'khoshtip-kocholo'), count($discounted)),
            'end_timestamp' => $end_timestamp
        ]);
    }
    
    public function cancel_sale() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی رد شد', 'khoshtip-kocholo')]);
        }
        
        $product_ids = get_option('k_discounted_products', []);
        
        foreach ($product_ids as $id) {
            $product = wc_get_product($id);
            if (!$product) continue;
            
            if ($product->get_type() === 'variable') {
                $variations = $product->get_children();
                foreach ($variations as $var_id) {
                    $variation = wc_get_product($var_id);
                    if ($variation) {
                        $variation->set_sale_price('');
                        $variation->save();
                    }
                }
            } else {
                $product->set_sale_price('');
                $product->save();
            }
        }
        
        update_option('k_sale_end_timestamp', 0);
        update_option('k_discounted_products', []);
        update_option('k_sale_product_ids', []);
        KK_Cache::clear_products_cache();
        
        wp_send_json_success(['message' => __('حراج لغو شد', 'khoshtip-kocholo')]);
    }
    
    public function remove_all_discounts() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی رد شد', 'khoshtip-kocholo')]);
        }
        
        if (!function_exists('wc_get_products')) {
            wp_send_json_error(['message' => __('ووکامرس فعال نیست', 'khoshtip-kocholo')]);
            return;
        }
        
        $products = wc_get_products(['status' => 'publish', 'limit' => -1]);
        $count = 0;
        
        foreach ($products as $product) {
            $removed = false;
            
            if ($product->get_type() === 'variable') {
                $variations = $product->get_children();
                foreach ($variations as $var_id) {
                    $variation = wc_get_product($var_id);
                    if ($variation && $variation->get_sale_price()) {
                        $variation->set_sale_price('');
                        $variation->save();
                        $removed = true;
                    }
                }
            } else {
                if ($product->get_sale_price()) {
                    $product->set_sale_price('');
                    $product->save();
                    $removed = true;
                }
            }
            
            if ($removed) $count++;
        }
        
        update_option('k_sale_end_timestamp', 0);
        update_option('k_discounted_products', []);
        update_option('k_sale_product_ids', []);
        KK_Cache::clear_products_cache();
        
        wp_send_json_success([
            'message' => sprintf(__('تخفیف %d محصول حذف شد', 'khoshtip-kocholo'), $count),
            'discounted_products' => []
        ]);
    }
    
    public function remove_discount() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی رد شد', 'khoshtip-kocholo')]);
        }
        
        $product_id = absint($_POST['product_id'] ?? 0);
        
        if (!$product_id) {
            wp_send_json_error(['message' => __('محصول نامعتبر', 'khoshtip-kocholo')]);
            return;
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(['message' => __('محصول یافت نشد', 'khoshtip-kocholo')]);
            return;
        }
        
        if ($product->get_type() === 'variable') {
            $variations = $product->get_children();
            foreach ($variations as $var_id) {
                $variation = wc_get_product($var_id);
                if ($variation) {
                    $variation->set_sale_price('');
                    $variation->save();
                }
            }
        } else {
            $product->set_sale_price('');
            $product->save();
        }
        
        // Remove from discounted products list
        $discounted = get_option('k_discounted_products', []);
        $discounted = array_diff($discounted, [$product_id]);
        update_option('k_discounted_products', array_values($discounted));
        update_option('k_sale_product_ids', array_values($discounted));
        
        KK_Cache::clear_products_cache();
        
        wp_send_json_success([
            'message' => __('تخفیف حذف شد', 'khoshtip-kocholo'),
            'discounted_products' => $this->get_discounted_products_for_js()
        ]);
    }
    
    /**
     * Search products by size - Public AJAX handler
     * Logic:
     * 1. Get all variable products
     * 2. For each product, check variations
     * 3. For each variation:
     *    - Check if attribute size equals selected size
     *    - Check if is_in_stock() is true
     *    - Check if stock_quantity > 0
     * 4. Return only products that have available variants with that size
     */
    public function search_by_size() {
        // Verify nonce for security
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        
        if (empty($nonce)) {
            wp_send_json_error(['message' => 'Nonce خالی است']);
            return;
        }
        
        if (!wp_verify_nonce($nonce, 'khoshtip_size_search_nonce')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[Khoshtip] Nonce verification failed. Received: ' . $nonce);
            }
            wp_send_json_error(['message' => 'خطای امنیتی - لطفاً صفحه رفرش کنید']);
            return;
        }
        
        // Get selected sizes
        $sizes = isset($_POST['sizes']) && is_array($_POST['sizes']) 
            ? array_map('sanitize_text_field', $_POST['sizes']) 
            : [];
        
        if (empty($sizes)) {
            wp_send_json_error(['message' => 'لطفاً حداقل یک سایز انتخاب کنید']);
            return;
        }
        
        if (!function_exists('wc_get_products')) {
            wp_send_json_error(['message' => 'ووکامرس فعال نیست']);
            return;
        }
        
        // Get all variable products (only IDs for performance)
        $product_ids = wc_get_products([
            'status' => 'publish',
            'type' => 'variable',
            'limit' => -1,
            'return' => 'ids'
        ]);
        
        $matching_products = [];
        
        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) continue;
            
            // Get variation IDs (children)
            $variation_ids = $product->get_children();
            
            foreach ($variation_ids as $variation_id) {
                $variation = wc_get_product($variation_id);
                if (!$variation) continue;
                
                // Get size attribute from variation
                $variation_size = $variation->get_attribute('pa_size');
                
                // Check if variation size matches any of selected sizes
                $size_matches = false;
                foreach ($sizes as $selected_size) {
                    if (strtolower(trim($variation_size)) === strtolower(trim($selected_size))) {
                        $size_matches = true;
                        break;
                    }
                }
                
                if (!$size_matches) continue;
                
                // First check: is_in_stock must be true
                if (!$variation->is_in_stock()) continue;
                
                // Second check: if managing stock, quantity must be > 0
                if ($variation->managing_stock()) {
                    $stock_qty = $variation->get_stock_quantity();
                    if ($stock_qty === null || $stock_qty <= 0) continue;
                }
                
                // Third check: stock_status must be 'instock'
                $stock_status = $variation->get_stock_status();
                if ($stock_status !== 'instock') continue;
                
                // Product has at least one matching variation with stock
                $matching_products[$product_id] = $product;
                break; // No need to check other variations of this product
            }
        }
        
        if (empty($matching_products)) {
            wp_send_json_success([
                'html' => '<div class="k-no-results" style="text-align: center; padding: 40px 20px; color: #666;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 15px; opacity: 0.5;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                        <path d="M8 8l6 6"></path>
                        <path d="M14 8l-6 6"></path>
                    </svg>
                    <p style="font-size: 16px; font-weight: 600; margin: 0;">محصولی با این سایز یافت نشد</p>
                    <p style="font-size: 14px; margin-top: 8px;">سایز دیگری را امتحان کنید</p>
                </div>',
                'count' => 0
            ]);
            return;
        }
        
        // Generate HTML for products
        $html = '<div class="k-size-search-results-grid">';
        
        foreach ($matching_products as $product_id => $product) {
            $image_url = $this->get_product_image_url($product);
            
            $price_html = '';
            $regular_price = $product->get_variation_regular_price('min');
            $sale_price = $product->get_variation_sale_price('min');
            
            if ($sale_price && $sale_price < $regular_price) {
                $price_html = '<span style="text-decoration: line-through; color: #999; font-size: 12px;">' . wc_price($regular_price) . '</span> ';
                $price_html .= '<span style="color: #e53935; font-weight: bold;">' . wc_price($sale_price) . '</span>';
            } else {
                $price_html = '<span style="font-weight: bold;">' . wc_price($regular_price) . '</span>';
            }
            
            $html .= '<a href="' . esc_url(get_permalink($product_id)) . '" class="k-product-card" style="display: block; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s;">';
            $html .= '<div style="position: relative; padding-top: 120%; background: #f5f5f5;">';
            $html .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($product->get_name()) . '" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;" loading="lazy">';
            $html .= '</div>';
            $html .= '<div style="padding: 10px;">';
            $html .= '<h4 style="margin: 0 0 6px; font-size: 13px; font-weight: 600; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">' . esc_html($product->get_name()) . '</h4>';
            $html .= '<div style="font-size: 12px;">' . $price_html . '</div>';
            $html .= '</div>';
            $html .= '</a>';
        }
        
        $html .= '</div>';
        
        wp_send_json_success([
            'html' => $html,
            'count' => count($matching_products)
        ]);
    }
    
    /**
     * Get products by category for categories panel
     */
    public function get_category_products() {
        $category_id = isset($_POST['category_id']) ? sanitize_text_field($_POST['category_id']) : 0;
        $page = absint($_POST['page'] ?? 1);
        $per_page = 20; // pagination - 20 محصول در هر صفحه
        
        if (!$category_id) {
            wp_send_json_error(['message' => 'دسته‌بندی نامعتبر']);
            return;
        }
        
        if (!function_exists('wc_get_products')) {
            wp_send_json_error(['message' => 'ووکامرس فعال نیست']);
            return;
        }
        
        if ($category_id === 'sale') {
            // دریافت تمام محصولات حراجی
            $sale_product_ids = wc_get_product_ids_on_sale();
            
            if (empty($sale_product_ids)) {
                // پیام اختصاصی وقتی محصول حراجی نداریم
                $html = '<div style="text-align: center; padding: 60px 20px; color: #666;">
                            <div style="font-size: 48px; margin-bottom: 16px;">🛍️</div>
                            <p style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">در حال حاضر محصول حراجی نداریم</p>
                            <p style="font-size: 14px; color: #999;">ولی افزایش قیمت هم نداریم</p>
                        </div>';
                wp_send_json_success(['html' => $html, 'count' => 0, 'has_more' => false]);
                return;
            }
            
            // گرفتن اطلاعات کامل محصولات برای مرتب‌سازی
            $sale_products_data = [];
            foreach ($sale_product_ids as $product_id) {
                $product = wc_get_product($product_id);
                if (!$product) continue;
                
                // چک موجودی
                $is_available = false;
                if ($product->is_type('simple') && $product->is_in_stock()) {
                    $is_available = true;
                } elseif ($product->is_type('variable')) {
                    $variations = $product->get_children();
                    foreach ($variations as $variation_id) {
                        $variation = wc_get_product($variation_id);
                        if ($variation && $variation->is_in_stock() && $variation->get_sale_price()) {
                            $is_available = true;
                            break;
                        }
                    }
                }
                
                if ($is_available) {
                    $sale_products_data[] = [
                        'id' => $product_id,
                        'date' => strtotime($product->get_date_created())
                    ];
                }
            }
            
            if (empty($sale_products_data)) {
                $html = '<div style="text-align: center; padding: 60px 20px; color: #666;">
                            <div style="font-size: 48px; margin-bottom: 16px;">🛍️</div>
                            <p style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">در حال حاضر محصول حراجی نداریم</p>
                            <p style="font-size: 14px; color: #999;">ولی افزایش قیمت هم نداریم</p>
                        </div>';
                wp_send_json_success(['html' => $html, 'count' => 0, 'has_more' => false]);
                return;
            }
            
            // مرتب‌سازی بر اساس تاریخ (جدیدترین اول)
            usort($sale_products_data, function($a, $b) {
                return $b['date'] - $a['date'];
            });
            
            // استخراج فقط ID ها بعد از مرتب‌سازی
            $filtered_sale_ids = array_map(function($item) {
                return $item['id'];
            }, $sale_products_data);
            
            // صفحه‌بندی دستی
            $total_products = count($filtered_sale_ids);
            $offset = ($page - 1) * $per_page;
            $product_ids = array_slice($filtered_sale_ids, $offset, $per_page);
            $has_more = ($page * $per_page) < $total_products;
            
        } else {
            // برای سایر دسته‌بندی‌ها از روش قبلی استفاده می‌کنیم
            $args = [
                'status' => 'publish',
                'limit' => $per_page,
                'page' => $page,
                'paginate' => true,
                'return' => 'ids',
                'stock_status' => 'instock'
            ];
            
            if ($category_id === 'all') {
                // همه محصولات - فقط args پایه
                $args['post_parent'] = 0;
            } else {
                // دسته‌بندی خاص
                $category_id = absint($category_id);
                $args['tax_query'] = [
                    [
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $category_id,
                        'operator' => 'IN'
                    ]
                ];
            }
            
            $args['post_parent'] = 0;
            
            $results = wc_get_products($args);
            $product_ids = $results->products;
            
            $filtered_product_ids = [];
            foreach ($product_ids as $product_id) {
                $product = wc_get_product($product_id);
                if (!$product) continue;
                
                // چک موجودی برای محصولات ساده
                if ($product->is_type('simple')) {
                    if ($product->is_in_stock()) {
                        $filtered_product_ids[] = $product_id;
                    }
                }
                // چک موجودی برای محصولات متغیر
                elseif ($product->is_type('variable')) {
                    $has_available_variation = false;
                    $variations = $product->get_children();
                    
                    foreach ($variations as $variation_id) {
                        $variation = wc_get_product($variation_id);
                        if (!$variation) continue;
                        
                        // چک موجودی variation
                        if ($variation->is_in_stock()) {
                            $has_available_variation = true;
                            break;
                        }
                    }
                    
                    // فقط اگر حداقل یک variation موجود داشت، محصول رو اضافه کن
                    if ($has_available_variation) {
                        $filtered_product_ids[] = $product_id;
                    }
                }
            }
            
            $product_ids = $filtered_product_ids;
            $has_more = ($page * $per_page) < $results->total;
        }
        
        if (empty($product_ids)) {
            $html = '<div style="text-align: center; padding: 40px 20px; color: #999;">
                        <p style="font-size: 16px;">هیچ محصولی یافت نشد</p>
                    </div>';
            wp_send_json_success(['html' => $html, 'count' => 0, 'has_more' => false]);
            return;
        }
        
        // Generate HTML for products
        $html = '<div class="k-category-products-grid">';
        
        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) continue;
            
            $image_url = $this->get_product_image_url($product);
            $title = $product->get_name();
            
            // Get price with proper formatting
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            
            if ($product->is_type('variable')) {
                $regular_price = $product->get_variation_regular_price('min');
                $sale_price = $product->get_variation_sale_price('min');
            }
            
            if ($sale_price && $sale_price < $regular_price) {
                $price_html = '<span style="text-decoration: line-through; color: #999; font-size: 12px;">' . wc_price($regular_price) . '</span> ';
                $price_html .= '<span style="color: #e53935; font-weight: bold;">' . wc_price($sale_price) . '</span>';
            } else {
                $price_html = '<span style="font-weight: bold;">' . wc_price($regular_price) . '</span>';
            }
            
            $html .= '<a href="' . esc_url(get_permalink($product_id)) . '" class="k-product-card" style="display: block; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s;">';
            $html .= '<div style="position: relative; padding-top: 120%; background: #f5f5f5;">';
            $html .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($title) . '" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;" loading="lazy">';
            $html .= '</div>';
            $html .= '<div style="padding: 10px;">';
            $html .= '<h4 style="margin: 0 0 6px; font-size: 13px; font-weight: 600; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">' . esc_html($title) . '</h4>';
            $html .= '<div style="font-size: 12px;">' . $price_html . '</div>';
            $html .= '</div>';
            $html .= '</a>';
        }
        
        $html .= '</div>';
        
        // Add "Load More" button if there are more products
        if ($has_more) {
            $html .= '<div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">';
            $html .= '<button class="k-load-more-products" data-category-id="' . esc_attr($category_id) . '" data-page="' . ($page + 1) . '" style="background: #ff6b9d; color: white; border: none; padding: 10px 30px; border-radius: 6px; font-size: 14px; cursor: pointer; font-weight: 600; transition: background 0.2s;">';
            $html .= 'نمایش محصولات بیشتر ↓';
            $html .= '</button>';
            $html .= '</div>';
        }
        
        wp_send_json_success(['html' => $html, 'count' => count($product_ids), 'has_more' => $has_more]);
    }
    
    /**
     * Search products by name - Public AJAX handler
     */
    public function search_products_by_name() {
        // Verify nonce for security
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        
        if (empty($nonce)) {
            wp_send_json_error(['message' => 'Nonce خالی است']);
            return;
        }
        
        if (!wp_verify_nonce($nonce, 'khoshtip_size_search_nonce')) {
            wp_send_json_error(['message' => 'خطای امنیتی - لطفاً صفحه رفرش کنید']);
            return;
        }
        
        // Get search query
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        
        if (empty($query) || strlen($query) < 2) {
            wp_send_json_error(['message' => 'لطفاً حداقل 2 کاراکتر وارد کنید']);
            return;
        }
        
        if (!function_exists('wc_get_products')) {
            wp_send_json_error(['message' => 'ووکامرس فعال نیست']);
            return;
        }
        
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 50,
            's' => $query,
            'meta_query' => [
                'relation' => 'OR',
            ]
        ];
        
        // Add filter to search in excerpt and content too
        add_filter('posts_search', [$this, 'extend_product_search'], 10, 2);
        
        $product_query = new WP_Query($args);
        
        // Remove filter after query
        remove_filter('posts_search', [$this, 'extend_product_search'], 10);
        
        if (!$product_query->have_posts()) {
            wp_send_json_success([
                'html' => '<div class="k-no-results" style="text-align: center; padding: 40px 20px; color: #999;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 15px; opacity: 0.5;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                        <path d="M8 8l6 6"></path>
                        <path d="M14 8l-6 6"></path>
                    </svg>
                    <p style="font-size: 16px; font-weight: 600; margin: 0;">محصولی یافت نشد</p>
                    <p style="font-size: 14px; margin-top: 8px;">کلمه دیگری را جستجو کنید</p>
                </div>',
                'count' => 0
            ]);
            return;
        }
        
        // Generate HTML for products
        $html = '<div class="k-category-products-grid">';
        
        while ($product_query->have_posts()) {
            $product_query->the_post();
            $product_id = get_the_ID();
            $product = wc_get_product($product_id);
            if (!$product) continue;
            
            $image_url = $this->get_product_image_url($product);
            $title = $product->get_name();
            
            // Get price with proper formatting
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            
            if ($product->is_type('variable')) {
                $regular_price = $product->get_variation_regular_price('min');
                $sale_price = $product->get_variation_sale_price('min');
            }
            
            if ($sale_price && $sale_price < $regular_price) {
                $price_html = '<span style="text-decoration: line-through; color: #999; font-size: 12px;">' . wc_price($regular_price) . '</span> ';
                $price_html .= '<span style="color: #e53935; font-weight: bold;">' . wc_price($sale_price) . '</span>';
            } else {
                $price_html = '<span style="font-weight: bold;">' . wc_price($regular_price) . '</span>';
            }
            
            $html .= '<a href="' . esc_url(get_permalink($product_id)) . '" class="k-product-card" style="display: block; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s;">';
            $html .= '<div style="position: relative; padding-top: 120%; background: #f5f5f5;">';
            $html .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($title) . '" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;" loading="lazy">';
            $html .= '</div>';
            $html .= '<div style="padding: 10px;">';
            $html .= '<h4 style="margin: 0 0 6px; font-size: 13px; font-weight: 600; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">' . esc_html($title) . '</h4>';
            $html .= '<div style="font-size: 12px;">' . $price_html . '</div>';
            $html .= '</div>';
            $html .= '</a>';
        }
        
        $html .= '</div>';
        
        wp_reset_postdata();
        
        wp_send_json_success([
            'html' => $html,
            'count' => $product_query->found_posts
        ]);
    }
    
    public function extend_product_search($search, $wp_query) {
        global $wpdb;
        
        if (empty($search) || empty($wp_query->query_vars['s'])) {
            return $search;
        }
        
        $search_term = $wp_query->query_vars['s'];
        
        // Search in title, content, and excerpt
        $search = " AND (";
        $search .= "({$wpdb->posts}.post_title LIKE '%" . esc_sql($wpdb->esc_like($search_term)) . "%')";
        $search .= " OR ({$wpdb->posts}.post_content LIKE '%" . esc_sql($wpdb->esc_like($search_term)) . "%')";
        $search .= " OR ({$wpdb->posts}.post_excerpt LIKE '%" . esc_sql($wpdb->esc_like($search_term)) . "%')";
        $search .= ")";
        
        return $search;
    }

    /**
     * تابع جدید برای گرفتن URL عکس محصول با پشتیبانی از افزونه WebP Optimizer
     * این تابع به ترتیب چک میکنه:
     * 1. WebP path از meta افزونه
     * 2. WebP path از attachment metadata
     * 3. عکس اصلی محصول
     * 4. گالری محصول
     * 5. عکس واریانت
     * 6. Placeholder
     */
    private function get_product_image_url($product) {
        $product_id = $product->get_id();
        $image_url = '';
        
        // Step 1: Get featured image ID
        $image_id = $product->get_image_id();
        
        if ($image_id && $image_id > 0) {
            $image_url = $this->get_attachment_url_with_webp($image_id);
        }
        
        // Step 2: Try gallery images
        if (empty($image_url) || $this->is_placeholder($image_url)) {
            $gallery_ids = $product->get_gallery_image_ids();
            if (!empty($gallery_ids)) {
                foreach ($gallery_ids as $gallery_id) {
                    $gallery_url = $this->get_attachment_url_with_webp($gallery_id);
                    if (!empty($gallery_url) && !$this->is_placeholder($gallery_url)) {
                        $image_url = $gallery_url;
                        break;
                    }
                }
            }
        }
        
        // Step 3: Try variation images
        if (empty($image_url) || $this->is_placeholder($image_url)) {
            $variation_ids = $product->get_children();
            if (!empty($variation_ids)) {
                foreach ($variation_ids as $var_id) {
                    $var_image_id = get_post_thumbnail_id($var_id);
                    if ($var_image_id && $var_image_id > 0) {
                        $var_url = $this->get_attachment_url_with_webp($var_image_id);
                        if (!empty($var_url) && !$this->is_placeholder($var_url)) {
                            $image_url = $var_url;
                            break;
                        }
                    }
                }
            }
        }
        
        // Step 4: Final fallback
        if (empty($image_url)) {
            $image_url = wc_placeholder_img_src('medium');
        }
        
        return $image_url;
    }
    
    /**
     * گرفتن URL عکس با چک کردن WebP path از افزونه بهینه‌ساز
     */
    private function get_attachment_url_with_webp($attachment_id) {
        if (!$attachment_id || $attachment_id <= 0) {
            return '';
        }
        
        // Check for WebP version first (from WooCommerce Image Optimizer plugin)
        $webp_path = get_post_meta($attachment_id, '_wio_webp_path', true);
        
        if ($webp_path && file_exists($webp_path)) {
            // Convert file path to URL
            $webp_url = str_replace(WP_CONTENT_DIR, content_url(), $webp_path);
            return $webp_url;
        }
        
        // Check attachment metadata for sized WebP versions
        $metadata = wp_get_attachment_metadata($attachment_id);
        if (is_array($metadata) && isset($metadata['sizes'])) {
            // Try medium size first, then woocommerce_thumbnail
            $sizes_to_check = ['medium', 'woocommerce_thumbnail', 'medium_large', 'large'];
            foreach ($sizes_to_check as $size) {
                if (isset($metadata['sizes'][$size]['webp_path'])) {
                    $size_webp_path = $metadata['sizes'][$size]['webp_path'];
                    if (file_exists($size_webp_path)) {
                        return str_replace(WP_CONTENT_DIR, content_url(), $size_webp_path);
                    }
                }
            }
        }
        
        // Fallback to regular image URL
        $image_data = wp_get_attachment_image_src($attachment_id, 'medium');
        if ($image_data && !empty($image_data[0])) {
            return $image_data[0];
        }
        
        // Try woocommerce_thumbnail size
        $image_data = wp_get_attachment_image_src($attachment_id, 'woocommerce_thumbnail');
        if ($image_data && !empty($image_data[0])) {
            return $image_data[0];
        }
        
        // Try full size as last resort
        $image_data = wp_get_attachment_image_src($attachment_id, 'full');
        if ($image_data && !empty($image_data[0])) {
            return $image_data[0];
        }
        
        return '';
    }
    
    /**
     * چک کردن اینکه URL عکس placeholder هست یا نه
     */
    private function is_placeholder($url) {
        if (empty($url)) {
            return true;
        }
        
        $placeholder_patterns = [
            'woocommerce-placeholder',
            'placeholder.png',
            'placeholder.jpg',
            'placeholder.webp'
        ];
        
        foreach ($placeholder_patterns as $pattern) {
            if (strpos($url, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function get_discounted_products_for_js() {
        $discounted_products_ids = get_option('k_discounted_products', []);
        
        if (empty($discounted_products_ids)) {
            return [];
        }
        
        $product_list = [];
        
        foreach ($discounted_products_ids as $product_id) {
            $product = wc_get_product($product_id);
            
            if ($product) {
                $product_type = $product->get_type();
                $regular_price = $product->get_regular_price();
                $sale_price = $product->get_sale_price();
                $discount_percent = 0;
                $display_regular_price = wc_price($regular_price);
                $display_sale_price = '';
                
                if ($product_type === 'variable') {
                    // حذف پارامتر true برای استفاده از cache داخلی WooCommerce
                    $min_reg_price = $product->get_variation_regular_price('min');
                    $max_reg_price = $product->get_variation_regular_price('max');
                    
                    $display_regular_price = ($min_reg_price == $max_reg_price) ? wc_price($min_reg_price) : wc_format_price_range($min_reg_price, $max_reg_price);
                    
                    $lowest_sale_price = PHP_INT_MAX;
                    // استفاده از get_post_meta به جای load کردن کامل variation
                    $children = $product->get_children();
                    foreach ($children as $variation_id) {
                        $var_sale_price = get_post_meta($variation_id, '_sale_price', true);
                        if (!empty($var_sale_price) && floatval($var_sale_price) < $lowest_sale_price) {
                            $lowest_sale_price = floatval($var_sale_price);
                        }
                    }
                    
                    if ($lowest_sale_price != PHP_INT_MAX) {
                        $display_sale_price = wc_price($lowest_sale_price);
                        if ($min_reg_price > 0) {
                            $discount_percent = round((($min_reg_price - $lowest_sale_price) / $min_reg_price) * 100);
                        }
                    }
                    
                } else {
                    $display_sale_price = !empty($sale_price) ? wc_price($sale_price) : '';
                    if ($regular_price > 0 && !empty($sale_price)) {
                        $discount_percent = round((($regular_price - $sale_price) / $regular_price) * 100);
                    }
                }
                
                $product_list[] = [
                    'id' => $product_id,
                    'name' => $product->get_name(),
                    'regular_price' => $display_regular_price,
                    'sale_price' => $display_sale_price,
                    'discount_percent' => $discount_percent
                ];
            }
        }
        
        return $product_list;
    }
    
    /**
     * Get cart count - AJAX handler
     * New handler to get current cart item count
     */
    public function get_cart_count() {
        $count = 0;
        
        if (function_exists('WC') && WC()->cart) {
            $count = WC()->cart->get_cart_contents_count();
        }
        
        wp_send_json_success(['count' => $count]);
    }

    // This function is no longer needed as we use get_products_by_category instead

    /**
     * Apply price increase to selected products
     */
    public function apply_price_increase() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی رد شد']);
        }

        check_ajax_referer('k_save_nonce', 'nonce');

        $product_ids = isset($_POST['product_ids']) ? array_map('absint', $_POST['product_ids']) : [];
        $increase_percent = floatval($_POST['increase_percent'] ?? 25);

        if (empty($product_ids)) {
            wp_send_json_error(['message' => 'هیچ محصولی انتخاب نشده']);
        }

        if ($increase_percent < 1 || $increase_percent > 500) {
            wp_send_json_error(['message' => 'درصد نامعتبر']);
        }

        $updated_count = 0;

        // دریافت backup قیمت‌های قبلی
        $price_backup = get_option('k_price_backup_history', []);
        if (!is_array($price_backup)) {
            $price_backup = [];
        }

        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) continue;

            $type = $product->get_type();

            if ($type === 'variable') {
                // برای محصولات متغیر، تمام variants را بروزرسانی کنیم
                $children = $product->get_children();
                foreach ($children as $child_id) {
                    $variation = wc_get_product($child_id);
                    if (!$variation) continue;

                    $regular_price = (float) $variation->get_regular_price();
                    if ($regular_price > 0) {
                        // ذخیره قیمت قبلی
                        if (!isset($price_backup[$child_id])) {
                            $price_backup[$child_id] = [
                                'original_price' => $regular_price,
                                'last_update' => time(),
                            ];
                        }

                        $new_price = $regular_price * (1 + $increase_percent / 100);
                        $variation->set_regular_price($new_price);
                        $variation->save();
                        $updated_count++;
                    }
                }
            } else {
                // برای محصولات ساده
                $regular_price = (float) $product->get_regular_price();
                if ($regular_price > 0) {
                    if (!isset($price_backup[$product_id])) {
                        $price_backup[$product_id] = [
                            'original_price' => $regular_price,
                            'last_update' => time(),
                        ];
                    }

                    $new_price = $regular_price * (1 + $increase_percent / 100);
                    $product->set_regular_price($new_price);
                    $product->save();
                    $updated_count++;
                }
            }
        }

        // ذخیره backup
        update_option('k_price_backup_history', $price_backup);

        // ذخیره در تاریخچه
        $history = get_option('k_price_change_history', []);
        if (!is_array($history)) {
            $history = [];
        }

        $history[] = [
            'timestamp' => time(),
            'action' => 'افزایش قیمت',
            'percent' => $increase_percent,
            'product_count' => count($product_ids),
        ];

        update_option('k_price_change_history', $history);

        // Clear cache if exists
        if (function_exists('KK_Cache') && method_exists('KK_Cache', 'clear_products_cache')) {
            KK_Cache::clear_products_cache();
        }

        wp_send_json_success([
            'message' => 'قیمت ' . $updated_count . ' قلم با موفقیت افزایش یافت.',
            'updated_count' => $updated_count,
        ]);
    }

    /**
     * Revert prices to original
     */
    public function revert_prices() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی رد شد']);
        }

        check_ajax_referer('k_save_nonce', 'nonce');

        $price_backup = get_option('k_price_backup_history', []);
        if (!is_array($price_backup) || empty($price_backup)) {
            wp_send_json_error(['message' => 'هیچ backup قیمتی وجود ندارد']);
        }

        $reverted_count = 0;

        foreach ($price_backup as $product_id => $backup) {
            $product = wc_get_product($product_id);
            if ($product) {
                $product->set_regular_price($backup['original_price']);
                $product->save();
                $reverted_count++;
            }
        }

        // ثبت در تاریخچه
        $history = get_option('k_price_change_history', []);
        if (!is_array($history)) {
            $history = [];
        }

        $history[] = [
            'timestamp' => time(),
            'action' => 'بازگشت به قیمت اصلی',
            'percent' => 0,
            'product_count' => count($price_backup),
        ];

        update_option('k_price_change_history', $history);
        delete_option('k_price_backup_history');

        // Clear cache if exists
        if (function_exists('KK_Cache') && method_exists('KK_Cache', 'clear_products_cache')) {
            KK_Cache::clear_products_cache();
        }

        wp_send_json_success([
            'message' => 'قیمت ' . $reverted_count . ' قلم با موفقیت برگردانده شد.',
            'reverted_count' => $reverted_count,
        ]);
    }

    /**
     * Get price change history
     */
    public function get_price_history() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی رد شد']);
        }

        check_ajax_referer('k_save_nonce', 'nonce');

        $history = get_option('k_price_change_history', []);
        if (!is_array($history)) {
            $history = [];
        }

        // معکوس کردن ترتیب (جدیدترین اول)
        $history = array_reverse($history);

        wp_send_json_success(['history' => $history]);
    }
}
