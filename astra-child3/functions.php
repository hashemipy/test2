<?php
/**
 * Khoshtip Kocholo Child Theme Functions
 */

function khoshtip_kocholo_register_templates($templates) {
    $templates['page-products.php'] = 'Products Page';
    return $templates;
}
add_filter('theme_page_templates', 'khoshtip_kocholo_register_templates');

function khoshtip_kocholo_load_template($template) {
    global $post;
    
    if ($post) {
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ($page_template === 'page-products.php') {
            $custom_template = get_stylesheet_directory() . '/page-products.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
    }
    
    return $template;
}
add_filter('template_include', 'khoshtip_kocholo_load_template', 99);

function khoshtip_kocholo_ensure_products_page() {
    $products_page = get_page_by_path('products');
    
    if (!$products_page) {
        // Create the products page if it doesn't exist
        $page_id = wp_insert_post(array(
            'post_title' => 'محصولات',
            'post_name' => 'products',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '',
        ));
        
        if ($page_id && !is_wp_error($page_id)) {
            update_post_meta($page_id, '_wp_page_template', 'page-products.php');
        }
    } else {
        // Make sure it has the correct template
        $current_template = get_post_meta($products_page->ID, '_wp_page_template', true);
        if ($current_template !== 'page-products.php') {
            update_post_meta($products_page->ID, '_wp_page_template', 'page-products.php');
        }
        
        // Make sure page is published (not private or draft)
        if ($products_page->post_status !== 'publish') {
            wp_update_post(array(
                'ID' => $products_page->ID,
                'post_status' => 'publish'
            ));
        }
    }
}
add_action('init', 'khoshtip_kocholo_ensure_products_page');

// Enqueue Parent and Child Theme Styles
function khoshtip_kocholo_enqueue_styles() {
    // Enqueue parent theme style
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');
    
    // Enqueue child theme style
    wp_enqueue_style('khoshtip-kocholo-child-style', get_stylesheet_directory_uri() . '/style.css', array('astra-parent-style'), '1.0.0');
    
    // Load Vazirmatn font based on settings (default: local for Iran servers)
    $font_source = get_option('khoshtip_font_source', 'local');
    if ($font_source === 'google') {
        // Load from Google Fonts (for servers with international access)
        wp_enqueue_style('vazirmatn-google', 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap', array(), null);
    } else {
        // Load local font (default - faster for Iran servers)
        wp_enqueue_style('vazirmatn-local', get_stylesheet_directory_uri() . '/assets/css/vazirmatn-local.css', array(), '1.0.0');
    }
    
    // Enqueue component styles
    wp_enqueue_style('khoshtip-kocholo-components', get_stylesheet_directory_uri() . '/assets/css/components.css', array('khoshtip-kocholo-child-style'), '1.0.0');
    
    // Enqueue authentication styles
    wp_enqueue_style('khoshtip-kocholo-auth', get_stylesheet_directory_uri() . '/assets/css/auth-styles.css', array('khoshtip-kocholo-child-style'), '1.0.0');
    
    // Enqueue categories panel styles
    wp_enqueue_style('khoshtip-categories-panel', get_stylesheet_directory_uri() . '/assets/css/categories-panel.css', array('khoshtip-kocholo-child-style'), '1.0.0');
    
    wp_enqueue_style('khoshtip-product-video', get_stylesheet_directory_uri() . '/assets/css/product-video.css', array('khoshtip-kocholo-child-style'), '1.0.0');
    
    // Enqueue Swiper CSS for carousels (local version for Iran servers)
    wp_enqueue_style('swiper-css', get_stylesheet_directory_uri() . '/assets/css/swiper-bundle.min.css', array(), '11.0.0');
    
    // Enqueue Swiper JS (local version for Iran servers)
    wp_enqueue_script('swiper-js', get_stylesheet_directory_uri() . '/assets/js/swiper-bundle.min.js', array(), '11.0.0', true);
    
    // Enqueue custom JavaScript
    wp_enqueue_script('khoshtip-kocholo-main', get_stylesheet_directory_uri() . '/assets/js/main.js', array('jquery', 'swiper-js'), '1.0.1', true);
    
    // Localize script for AJAX categories panel
    wp_localize_script('khoshtip-kocholo-main', 'khoshtipAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('khoshtip_ajax_nonce')
    ));
    
    wp_localize_script('khoshtip-kocholo-main', 'khoshtipSizeSearch', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('khoshtip_size_search_nonce'),
        'debug' => WP_DEBUG
    ));
    
    // Enqueue Google authentication script
    wp_enqueue_script('khoshtip-google-auth', get_stylesheet_directory_uri() . '/assets/js/google-auth.js', array('jquery'), '1.0.0', true);
    wp_localize_script('khoshtip-google-auth', 'khoshtipAuth', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('google_auth_nonce'),
        'smsNonce' => wp_create_nonce('sms_auth_nonce')
    ));
    
    wp_enqueue_script('khoshtip-product-video', get_stylesheet_directory_uri() . '/assets/js/product-video.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('khoshtip-video-hover-preview', get_stylesheet_directory_uri() . '/assets/js/video-hover-preview.js', array(), '1.0.0', true);
    
    // Enqueue phone request script for logged-in users
    if (is_user_logged_in()) {
        wp_enqueue_script('khoshtip-phone-request', get_stylesheet_directory_uri() . '/assets/js/phone-request.js', array('jquery'), '1.0.1', true);
        
        // Get user ID and phone number
        $user_id = get_current_user_id();
        $phone = get_user_meta($user_id, 'billing_phone', true);
        
        // Localize script with phone check data
        wp_localize_script('khoshtip-phone-request', 'khoshtipPhoneCheck', array(
            'needsPhone' => empty($phone) ? 'true' : 'false',
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('save_phone_nonce')
        ));
    }
    
    if (get_option('khoshtip_sms_enabled') === '1') {
        wp_enqueue_script('khoshtip-sms-auth', get_stylesheet_directory_uri() . '/assets/js/sms-auth.js', array('jquery'), '1.0.0', true);
    }
}
add_action('wp_enqueue_scripts', 'khoshtip_kocholo_enqueue_styles');

// AJAX handler to save phone number
add_action('wp_ajax_save_user_phone', 'khoshtip_save_user_phone');
function khoshtip_save_user_phone() {
    check_ajax_referer('save_phone_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('کاربر وارد نشده است');
        return;
    }
    
    $phone = sanitize_text_field($_POST['phone']);
    
    // Validate phone number (Iranian format)
    if (!preg_match('/^09[0-9]{9}$/', $phone)) {
        wp_send_json_error('شماره موبایل معتبر نیست');
        return;
    }
    
    $user_id = get_current_user_id();
    
    update_user_meta($user_id, 'billing_phone', $phone);
    
    // Delete phone_required flag
    delete_user_meta($user_id, 'phone_required');
    
    wp_send_json_success('شماره موبایل با موفقیت ثبت شد');
}

// Add WooCommerce Support
function khoshtip_kocholo_add_woocommerce_support() {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    
    add_image_size('product-card-mobile', 400, 500, true);    // 2x quality for mobile retina displays
    add_image_size('product-card-tablet', 500, 625, true);    // Tablet/medium screens
    add_image_size('product-card-desktop', 600, 750, true);   // Desktop screens
}
add_action('after_setup_theme', 'khoshtip_kocholo_add_woocommerce_support');

// Custom function to get WooCommerce product card HTML
function khoshtip_kocholo_get_product_card($product_id) {
    $product = wc_get_product($product_id);
    
    if (!$product || !$product->is_in_stock()) {
        return '';
    }
    
    $price_html = '';
    if ($product->is_type('variable')) {
        // For variable products
        $min_regular_price = $product->get_variation_regular_price('min');
        $min_sale_price = $product->get_variation_sale_price('min');
        
        // بررسی اینکه قیمت معتبر است یا نه
        if (empty($min_regular_price) || $min_regular_price <= 0) {
            return ''; // اگر قیمت معتبر نیست، محصول رو نمایش نده
        }
        
        if (!empty($min_sale_price) && $min_sale_price < $min_regular_price) {
            $price_html = '<del>' . number_format($min_regular_price) . '</del> <ins>' . wc_price($min_sale_price) . '</ins>';
        } else {
            $price_html = wc_price($min_regular_price);
        }
    } else {
        // For simple products
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();
        
        // بررسی اینکه قیمت معتبر است یا نه
        if (empty($regular_price) || $regular_price <= 0) {
            return ''; // اگر قیمت معتبر نیست، محصول رو نمایش نده
        }
        
        if (!empty($sale_price) && $sale_price < $regular_price) {
            $price_html = '<del>' . number_format($sale_price) . '</del> <ins>' . wc_price($sale_price) . '</ins>';
        } else {
            $price_html = wc_price($regular_price);
        }
    }
    
    ob_start();
    ?>
    <div class="product-card">
        <a href="<?php echo esc_url($product->get_permalink()); ?>" class="product-card-link">
            <div class="product-card-image">
                <?php 
                $image_id = $product->get_image_id();
                
                if ($image_id) {
                    $image_html = wp_get_attachment_image($image_id, 'product-card-desktop', false, array(
                        'loading' => 'lazy',
                        'sizes' => '(max-width: 640px) 400px, (max-width: 1024px) 500px, 600px',
                        'class' => 'product-card-img',
                        'onload' => 'this.classList.add("loaded")'
                    ));
                    echo $image_html;
                } else {
                    echo $product->get_image('product-card-desktop', array(
                        'loading' => 'lazy',
                        'onload' => 'this.classList.add("loaded")'
                    ));
                }
                ?>
                <?php if ($product->is_on_sale()) : ?>
                    <span class="sale-badge">حراج</span>
                <?php endif; ?>
                
                <!-- Moved play button inside image container for proper positioning -->
                <?php 
                $has_video = function_exists('astra_product_has_video') ? astra_product_has_video($product_id) : false;
                if ($has_video) {
                    $video_data = astra_get_product_video($product_id);
                    if ($video_data && !empty($video_data['url'])) {
                        ?>
                        <button class="product-video-play-btn" 
                                data-video-src="<?php echo esc_url($video_data['url']); ?>" 
                                data-video-type="<?php echo esc_attr($video_data['embed_type']); ?>"
                                aria-label="نمایش ویدیو"
                                type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </button>
                        <?php
                    }
                }
                ?>
            </div>
            <div class="product-card-content">
                <h4 class="product-card-title"><?php echo esc_html($product->get_name()); ?></h4>
                <div class="product-card-price">
                    <?php echo $price_html; ?>
                </div>
            </div>
        </a>
    </div>
    <?php
    return ob_get_clean();
}

add_action('wp_ajax_load_more_products', 'khoshtip_load_more_products_ajax');
add_action('wp_ajax_nopriv_load_more_products', 'khoshtip_load_more_products_ajax');

function khoshtip_load_more_products_ajax() {
    // Verify nonce
    check_ajax_referer('load_more_products', 'nonce');
    
    // Get product IDs from request
    $product_ids = json_decode(stripslashes($_POST['product_ids']), true);
    
    if (!is_array($product_ids) || empty($product_ids)) {
        wp_send_json_error('Invalid product IDs');
        return;
    }
    
    // Generate HTML for products
    $html = '';
    foreach ($product_ids as $product_id) {
        $html .= khoshtip_kocholo_get_product_card($product_id);
    }
    
    wp_send_json_success(array('html' => $html));
}

// Add custom body classes
function khoshtip_kocholo_body_classes($classes) {
    $classes[] = 'khoshtip-kocholo-theme';
    return $classes;
}
add_filter('body_class', 'khoshtip_kocholo_body_classes');

require_once get_stylesheet_directory() . '/includes/product-video.php';

require_once get_stylesheet_directory() . '/khoshtip-kocholo-google-auth.php';
require_once get_stylesheet_directory() . '/khoshtip-kocholo-admin-settings.php';
require_once get_stylesheet_directory() . '/khoshtip-kocholo-sms-auth.php';

// Add phone request modal to footer for all pages
function khoshtip_add_phone_modal_to_footer() {
    if (!is_user_logged_in()) {
        return;
    }
    
    $user_id = get_current_user_id();
    $phone = get_user_meta($user_id, 'billing_phone', true);
    
    // Only display modal if user does not have a phone number
    if (empty($phone)) {
        ?>
        <!-- Phone Request Modal -->
        <div id="phone-request-modal" class="khoshtip-modal">
            <div class="khoshtip-modal-content sms-modal-content">
                <div class="sms-step">
                    <h2>تکمیل پروفایل</h2>
                    <p>لطفاً شماره موبایل خود را وارد کنید</p>
                    
                    <div class="sms-form-group">
                        <input type="tel" 
                               id="phone-request-input" 
                               class="sms-input" 
                               placeholder="09123456789" 
                               pattern="09[0-9]{9}"
                               maxlength="11">
                        <button type="button" id="submit-phone-request" class="sms-button">ثبت شماره موبایل</button>
                    </div>
                    <div class="phone-request-error sms-error" style="display: none;"></div>
                    <p class="phone-hint">شماره موبایل شما به عنوان نام کاربری ثبت می‌شود</p>
                </div>
            </div>
        </div>
        <?php
    }
}
add_action('wp_footer', 'khoshtip_add_phone_modal_to_footer');

function khoshtip_kocholo_flush_rewrite_rules() {
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'khoshtip_kocholo_flush_rewrite_rules');

// Remove Astra default header if needed
function khoshtip_kocholo_remove_astra_header() {
    remove_action('astra_header', 'astra_header_markup');
}
add_action('init', 'khoshtip_kocholo_remove_astra_header');
