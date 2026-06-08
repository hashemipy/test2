<?php
/**
 * Custom Footer Template
 */

// Get footer data from shortcode
$footer_data = json_decode(do_shortcode('[k_footer_data]'), true);
$site_title = $footer_data['site_title'] ?? 'دنیای رنگارنگ کودکان';
$site_description = $footer_data['site_description'] ?? 'فروشگاه تخصصی لباس و اکسسوری کودکان با کیفیت بالا و قیمت مناسب';
$useful_links = $footer_data['useful_links'] ?? array();
$customer_service_links = $footer_data['customer_service_links'] ?? array();
$contact_address = $footer_data['contact_address'] ?? '';
$contact_phone = $footer_data['contact_phone'] ?? '';
$copyright = $footer_data['copyright'] ?? '';

$bottom_bar = json_decode(do_shortcode('[k_bottom_bar]'), true);
$show_on_home = $bottom_bar['show_on_home'] ?? true;
$show_on_products = $bottom_bar['show_on_products'] ?? true;
$show_on_all_products = $bottom_bar['show_on_all_products'] ?? true;
$show_on_profile = $bottom_bar['show_on_profile'] ?? true;
$show_on_cart = $bottom_bar['show_on_cart'] ?? true;
$show_on_sendcode = $bottom_bar['show_on_sendcode'] ?? true; // Added sendcode page visibility check
$show_on_sales = $bottom_bar['show_on_sales'] ?? true; // Added sales page visibility check
$buttons = $bottom_bar['buttons'] ?? array();
$text_color = $bottom_bar['text_color'] ?? '#374151';

$is_sales_page = is_page_template('page-products.php') && isset($_GET['type']) && $_GET['type'] === 'sale';

$should_show_bottom_bar = false;
if ((is_front_page() && $show_on_home) || 
    (is_product() && $show_on_products) ||
    (is_page_template('page-products.php') && $show_on_all_products) ||
    (is_account_page() && $show_on_profile) ||
    (function_exists('is_cart') && is_cart() && $show_on_cart) ||
    (is_page_template('page-sendcode.php') && $show_on_sendcode) ||
    ($is_sales_page && $show_on_sales)) {
    $should_show_bottom_bar = true;
}

$cart_count = 0;
if (function_exists('WC') && WC()->cart) {
    $cart_count = WC()->cart->get_cart_contents_count();
}

$panel_data = json_decode(do_shortcode('[k_categories_panel_data]'), true);
$panel_enabled = $panel_data['enabled'] ?? false;
$panel_auto_open = $panel_data['auto_open'] ?? false;

// دریافت رنگ‌های فوتر از تنظیمات
$footer_bg_start = get_option('k_footer_bg_start', '#667eea');
$footer_bg_end = get_option('k_footer_bg_end', '#764ba2');
$footer_text_color = get_option('k_footer_text_color', '#ffffff');
?>

<footer class="site-footer" style="background: linear-gradient(135deg, <?php echo esc_attr($footer_bg_start); ?> 0%, <?php echo esc_attr($footer_bg_end); ?> 100%); color: <?php echo esc_attr($footer_text_color); ?>; padding: 3rem 0 1rem; <?php echo $should_show_bottom_bar ? 'margin-bottom: 70px;' : ''; ?>"">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
        <div class="footer-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2.5rem; margin-bottom: 2.5rem;">
            
            <!-- About Column -->
            <div class="footer-column">
                <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; color: <?php echo esc_attr($footer_text_color); ?>;"><?php echo esc_html($site_title); ?></h3>
                <p style="opacity: 0.95; line-height: 1.7; font-size: 0.95rem; color: <?php echo esc_attr($footer_text_color); ?>;"><?php echo esc_html($site_description); ?></p>
            </div>
            
            <!-- Useful Links Column -->
            <?php if (!empty($useful_links)) : ?>
            <div class="footer-column">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: <?php echo esc_attr($footer_text_color); ?>;">لینک‌های مفید</h3>
                <nav style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($useful_links as $link) : ?>
                        <a href="<?php echo esc_url($link['url']); ?>" style="color: <?php echo esc_attr($footer_text_color); ?>; opacity: 0.9; text-decoration: none; transition: opacity 0.3s; font-size: 0.95rem;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">
                            <?php echo esc_html($link['text']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <?php endif; ?>
            
            <!-- Customer Service Column -->
            <?php if (!empty($customer_service_links)) : ?>
            <div class="footer-column">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: <?php echo esc_attr($footer_text_color); ?>;">خدمات مشتریان</h3>
                <nav style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($customer_service_links as $link) : ?>
                        <a href="<?php echo esc_url($link['url']); ?>" style="color: <?php echo esc_attr($footer_text_color); ?>; opacity: 0.9; text-decoration: none; transition: opacity 0.3s; font-size: 0.95rem;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">
                            <?php echo esc_html($link['text']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <?php endif; ?>
            
            <!-- Contact Column -->
            <div class="footer-column">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: <?php echo esc_attr($footer_text_color); ?>;">تماس با ما</h3>
                <div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.95rem;">
                    <?php if (!empty($contact_address)) : ?>
                        <div style="display: flex; align-items: start; gap: 0.5rem; opacity: 0.95; color: <?php echo esc_attr($footer_text_color); ?>;">
                            <span style="font-size: 1.2rem;">📍</span>
                            <span style="line-height: 1.6;"><?php echo esc_html($contact_address); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($contact_phone)) : ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem; opacity: 0.95;">
                            <span style="font-size: 1.2rem;">📞</span>
                            <a href="tel:<?php echo esc_attr($contact_phone); ?>" style="color: <?php echo esc_attr($footer_text_color); ?>; text-decoration: none; direction: ltr; display: inline-block;"><?php echo esc_html($contact_phone); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
        <!-- Copyright -->
        <div class="footer-bottom" style="margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid <?php echo esc_attr($footer_text_color); ?> rgba(0,0,0,0.2); text-align: center;">
            <p style="opacity: 0.9; font-size: 0.95rem; color: <?php echo esc_attr($footer_text_color); ?>;"><?php echo esc_html($copyright); ?></p>
        </div>
    </div>
</footer>

<!-- Added floating bottom bar -->
<?php if ($should_show_bottom_bar && !empty($buttons)) : 
    $bg_start_color = get_option('k_bottom_bar_bg_start', '#ffffff');
    $bg_end_color = get_option('k_bottom_bar_bg_end', '#f9fafb');
?>
<!-- Added wave effect container above bottom bar -->
<div class="k-bottom-bar-wave" style="position: fixed; bottom: 60px; left: 0; right: 0; height: 36px; z-index: 998; pointer-events: none;">
    <svg viewBox="0 0 1200 40" preserveAspectRatio="none" style="width: 100%; height: 100%; display: block;">
        <defs>
            <linearGradient id="waveGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" style="stop-color:<?php echo esc_attr($bg_start_color); ?>;stop-opacity:0.9" />
                <stop offset="100%" style="stop-color:<?php echo esc_attr($bg_end_color); ?>;stop-opacity:0.9" />
            </linearGradient>
        </defs>
        <path d="M0,20 Q150,0 300,20 T600,20 T900,20 T1200,20 L1200,40 L0,40 Z" fill="url(#waveGradient)" />
        <path d="M0,25 Q200,10 400,25 T800,25 T1200,25 L1200,40 L0,40 Z" fill="url(#waveGradient)" opacity="0.7" />
    </svg>
</div>

<div class="k-bottom-bar" style="position: fixed; bottom: 0; left: 0; right: 0; background: linear-gradient(135deg, <?php echo esc_attr($bg_start_color); ?> 0%, <?php echo esc_attr($bg_end_color); ?> 100%); box-shadow: 0 -2px 10px rgba(0,0,0,0.1); z-index: 999; padding: 2px 0; border-top: 1px solid #e5e7eb;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-around; align-items: center; gap: 6px;">
            <?php 
            if ($panel_enabled) : 
                $button_label = $panel_data['button_label'] ?? 'دسته بندی';
                $icon_url = $panel_data['icon'] ?? '';
            ?>
                <a href="#" 
                   class="k-categories-panel-trigger"
                   data-panel-trigger="true"
                   style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; color: <?php echo esc_attr($text_color); ?>; transition: color 0.2s; padding: 4px 8px; min-width: 60px; position: relative;">
                    <?php if (!empty($icon_url)) : ?>
                        <img src="<?php echo esc_url($icon_url); ?>" 
                             alt="<?php echo esc_attr($button_label); ?>" 
                             style="width: 48px; height: 34px; object-fit: contain;" />
                    <?php else : ?>
                        <span style="font-size: 24px;">📂</span>
                    <?php endif; ?>
                    <span style="font-size: 11px; text-align: center; line-height: 1.2; color: <?php echo esc_attr($text_color); ?>;"><?php echo esc_html($button_label); ?></span>
                </a>
            <?php endif; ?>
            
            <?php foreach ($buttons as $button) : 
                if (empty($button['label'])) continue;
                $is_cart_button = (isset($button['is_cart']) && $button['is_cart'] === '1');
                $button_url = $button['url'] ?? '#';
                // اگر سبد خرید است، لینک رو به صفحه سبد خرید ووکامرس ست کن
                if ($is_cart_button && function_exists('wc_get_cart_url')) {
                    $button_url = wc_get_cart_url();
                }
            ?>
                <a href="<?php echo esc_url($button_url); ?>" 
                   style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; color: <?php echo esc_attr($text_color); ?>; transition: color 0.2s; padding: 4px 8px; min-width: 60px; position: relative;">
                    <?php if (!empty($button['image'])) : ?>
                        <img src="<?php echo esc_url($button['image']); ?>" 
                             alt="<?php echo esc_attr($button['label']); ?>" 
                             style="width: 32px; height: 32px; object-fit: contain;" />
                    <?php else : ?>
                        <span style="font-size: 28px;">🔘</span>
                    <?php endif; ?>
                        
                    <!-- Changed cart badge background to white and text to black -->
                    <?php if ($is_cart_button) : ?>
                        <span class="k-cart-count" style="position: absolute; top: -2px; right: 8px; background: #fff; color: #000; font-size: 10px; font-weight: bold; min-width: 18px; height: 18px; border-radius: 50%; display: <?php echo $cart_count > 0 ? 'flex' : 'none'; ?>; align-items: center; justify-content: center; padding: 0 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"><?php echo $cart_count > 0 ? esc_html($cart_count) : ''; ?></span>
                    <?php endif; ?>
                        
                    <span style="font-size: 10px; text-align: center; line-height: 1.2; color: <?php echo esc_attr($text_color); ?>;"><?php echo esc_html($button['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Add categories panel modal -->
<?php if ($panel_enabled && !empty($panel_data['categories'])) : 
    $site_title = get_option('k_site_title', get_bloginfo('name'));
    $bg_color = $panel_data['bg_color'] ?? '#ffffff';
    $header_gradient_start = get_option('k_header_gradient_start', '#ff6b9d');
    $header_gradient_end = get_option('k_header_gradient_end', '#ffc3d7');
    
    $search_sizes = get_option('k_search_sizes', []);
    $size_terms = [];
    if (!empty($search_sizes)) {
        $size_terms = get_terms([
            'taxonomy' => 'pa_size',
            'slug' => $search_sizes,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
    }
?>
<?php 
// Check if should auto-open on first visit (for homepage only)
$should_auto_open = $panel_auto_open && is_front_page() && !isset($_COOKIE['k_panel_closed']);
$panel_display = $should_auto_open ? 'flex' : 'none';
$auto_open_category = (int)($panel_data['auto_open_category'] ?? 0);
?>
<div class="k-categories-panel" data-auto-open="<?php echo $should_auto_open ? '1' : '0'; ?>" data-auto-open-category="<?php echo $auto_open_category; ?>" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; height: 100vh; background: <?php echo esc_attr($bg_color); ?>; z-index: 9999; display: <?php echo $panel_display; ?>; overflow: hidden; flex-direction: column;">
    <!-- Restructured header: site title on top, search and size filter below -->
    <div style="background: linear-gradient(135deg, <?php echo esc_attr($header_gradient_start); ?>, <?php echo esc_attr($header_gradient_end); ?>); padding: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); flex-shrink: 0;">
        <!-- First row: Close button and Site title (centered) -->
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
            <button class="k-categories-panel-close" style="background: none; border: none; color: #fff; font-size: 28px; cursor: pointer; padding: 0; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">×</button>
            <!-- Center aligned site title -->
            <h2 style="color: #fff; font-size: 18px; font-weight: 700; margin: 0; flex: 1; text-align: center;"><?php echo esc_html($site_title); ?></h2>
            <!-- Spacer to balance the close button -->
            <div style="width: 36px; flex-shrink: 0;"></div>
        </div>
        
        <!-- Second row: Search input and Size filter button -->
        <div style="display: flex; align-items: center; gap: 10px;">
            <!-- Search input -->
            <div style="flex: 1; position: relative;">
                <input type="search" 
                       class="k-panel-search-input" 
                       placeholder="جستجوی محصولات..."
                       style="width: 100%; padding: 8px 35px 8px 12px; border: none; border-radius: 20px; font-size: 14px; background: rgba(255,255,255,0.95); outline: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </div>
            
            <!-- Size filter button -->
            <button class="k-panel-size-toggle" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: #fff; cursor: pointer; padding: 8px 12px; border-radius: 8px; display: flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 600; flex-shrink: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
                سایز
            </button>
        </div>
    </div>
    
    <!-- Size filter panel (hidden by default) -->
    <?php if (!empty($size_terms) && !is_wp_error($size_terms)) : ?>
    <div class="k-panel-size-filter" style="background: #f8f9fa; padding: 12px 15px; border-bottom: 1px solid #dee2e6; display: none; flex-shrink: 0;">
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; margin-bottom: 8px;">
            <?php foreach ($size_terms as $term) : ?>
                <label class="k-size-checkbox-panel" style="display: flex; align-items: center; justify-content: center; padding: 6px 4px; background: #fff; border: 2px solid #e5e5e5; border-radius: 6px; cursor: pointer; transition: all 0.2s ease; font-weight: 600; font-size: 12px; text-align: center;">
                    <input type="checkbox" class="k-panel-size-input" value="<?php echo esc_attr($term->slug); ?>" style="display: none;">
                    <span><?php echo esc_html($term->name); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Content area with sidebar and products -->
    <div style="display: flex; flex: 1; overflow: hidden;">
        <!-- Categories sidebar with smaller icons -->
        <div class="k-categories-sidebar" style="width: 100px; background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%); overflow-y: auto; border-left: 2px solid #dee2e6; flex-shrink: 0;">
            <?php // اضافه کردن دکمه همه محصولات ?>
            <div class="k-category-item" data-category-id="all" 
                 style="padding: 10px 6px; text-align: center; cursor: pointer; border-bottom: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease;">
                <div style="width: 40px; height: 40px; margin: 0 auto 5px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                    <span style="font-size: 20px;">🛍️</span>
                </div>
                <div style="font-size: 10px; font-weight: 600; color: #495057; line-height: 1.2;">همه محصولات</div>
            </div>
            
            <?php // اضافه کردن دکمه حراج‌ها ?>
            <div class="k-category-item" data-category-id="sale" 
                 style="padding: 10px 6px; text-align: center; cursor: pointer; border-bottom: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease;">
                <div style="width: 40px; height: 40px; margin: 0 auto 5px; border-radius: 50%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                    <span style="font-size: 20px;">🔥</span>
                </div>
                <div style="font-size: 10px; font-weight: 600; color: #495057; line-height: 1.2;">حراج‌ها</div>
            </div>
            
            <?php foreach ($panel_data['categories'] as $index => $category) : ?>
                <div class="k-category-item" data-category-id="<?php echo esc_attr($category['id']); ?>" 
                     style="padding: 10px 6px; text-align: center; cursor: pointer; border-bottom: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease;">
                    <?php if (!empty($category['image'])) : ?>
                        <div style="width: 40px; height: 40px; margin: 0 auto 5px; border-radius: 50%; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                            <img src="<?php echo esc_url($category['image']); ?>" alt="<?php echo esc_attr($category['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    <?php else : ?>
                        <div style="width: 40px; height: 40px; margin: 0 auto 5px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 20px;">📦</span>
                        </div>
                    <?php endif; ?>
                    <div style="font-size: 10px; font-weight: 600; color: #495057; line-height: 1.2;"><?php echo esc_html($category['name']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Products area -->
        <div class="k-categories-products" style="flex: 1; background: #fff; overflow-y: auto; padding: 0;">
            <div class="k-categories-products-inner" style="padding: 20px; text-align: center; color: #999;">
                <p style="font-size: 16px; margin: 40px 0;">یک دسته‌بندی را انتخاب کنید یا جستجو کنید</p>
            </div>
        </div>
    </div>
    
    <!-- Fixed bottom menu in panel -->
    <div style="position: fixed; bottom: 0; left: 0; right: 0; background: linear-gradient(135deg, <?php echo esc_attr($bg_start_color); ?> 0%, <?php echo esc_attr($bg_end_color); ?> 100%); box-shadow: 0 -2px 10px rgba(0,0,0,0.1); padding: 4px 0; border-top: 1px solid #e5e7eb; z-index: 10000;">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-around; align-items: center; gap: 8px;">
                <?php foreach ($buttons as $button) : 
                    if (empty($button['label'])) continue;
                    $is_cart_button = (isset($button['is_cart']) && $button['is_cart'] === '1');
                    $button_url = $button['url'] ?? '#';
                    if ($is_cart_button && function_exists('wc_get_cart_url')) {
                        $button_url = wc_get_cart_url();
                    }
                ?>
                    <a href="<?php echo esc_url($button_url); ?>" 
                       style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; color: <?php echo esc_attr($text_color); ?>; transition: color 0.2s; padding: 4px 8px; min-width: 60px; position: relative;">
                        <?php if (!empty($button['image'])) : ?>
                            <img src="<?php echo esc_url($button['image']); ?>" 
                                 alt="<?php echo esc_attr($button['label']); ?>" 
                                 style="width: 32px; height: 32px; object-fit: contain;" />
                        <?php else : ?>
                            <span style="font-size: 28px;">🔘</span>
                        <?php endif; ?>
                        
                        <!-- Changed cart badge background to white and text to black -->
                        <?php if ($is_cart_button) : ?>
                            <span class="k-cart-count" style="position: absolute; top: -2px; right: 8px; background: #fff; color: #000; font-size: 10px; font-weight: bold; min-width: 18px; height: 18px; border-radius: 50%; display: <?php echo $cart_count > 0 ? 'flex' : 'none'; ?>; align-items: center; justify-content: center; padding: 0 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"><?php echo $cart_count > 0 ? esc_html($cart_count) : ''; ?></span>
                        <?php endif; ?>
                        
                        <span style="font-size: 10px; text-align: center; line-height: 1.2; color: <?php echo esc_attr($text_color); ?>;"><?php echo esc_html($button['label']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php 
// Auto open categories panel - handle initial state
if ($panel_enabled && $panel_auto_open && is_front_page()) : 
?>
<script>
(function() {
    // If panel is auto-opened, set initial states immediately (no delay)
    var panel = document.querySelector('.k-categories-panel');
    if (panel && panel.getAttribute('data-auto-open') === '1') {
        // Get auto-open category ID from data attribute
        var autoOpenCategoryId = parseInt(panel.getAttribute('data-auto-open-category') || '0');
        
        // Hide body scroll immediately
        document.body.style.overflow = 'hidden';
        
        // Hide bottom bar immediately
        var bottomBar = document.querySelector('.k-bottom-bar');
        var bottomWave = document.querySelector('.k-bottom-bar-wave');
        if (bottomBar) bottomBar.classList.add('k-bottom-bar-hidden');
        if (bottomWave) bottomWave.classList.add('k-bottom-bar-hidden');
        
        // Update trigger button and open category when jQuery is ready
        document.addEventListener('DOMContentLoaded', function() {
            var $ = window.jQuery;
            if ($) {
                console.log('[v0] DOMContentLoaded - auto open logic running');
                console.log('[v0] autoOpenCategoryId =', autoOpenCategoryId);
                
                var $trigger = $('.k-categories-panel-trigger');
                if ($trigger.length) {
                    $trigger.data('original-html', $trigger.html());
                    $trigger.html('<span style="font-size: 24px;">&#127968;</span><span style="font-size: 11px; text-align: center; line-height: 1.2;">بازگشت</span>');
                }
                
                // Auto-open the default category if specified
                if (autoOpenCategoryId === 0) {
                    console.log('[v0] Looking for all products button');
                    // Auto-open "همه محصولات" (all products)
                    var $allButton = $('[data-category-id="all"]');
                    console.log('[v0] Found all products button:', $allButton.length);
                    if ($allButton.length) {
                        // Manually trigger the click behavior instead of using trigger()
                        $allButton.css({
                            background: "rgba(255, 107, 157, 0.1)",
                            transform: "scale(1.05)"
                        });
                        
                        // Perform AJAX request directly
                        $.ajax({
                            url: window.khoshtipAjax?.ajaxurl || "/wp-admin/admin-ajax.php",
                            type: "POST",
                            data: {
                                action: "k_get_category_products",
                                category_id: 'all',
                                page: 1
                            },
                            beforeSend: () => {
                                $(".k-categories-products-inner").html('<div style="text-align: center; padding: 40px;"><div style="display: inline-block; width: 50px; height: 50px; border: 4px solid #f0f0f0; border-top-color: #ff6b9d; border-radius: 50%; animation: spin 1s linear infinite;"></div><p style="margin-top: 15px; color: #666;">در حال بارگذاری...</p></div>');
                            },
                            success: (response) => {
                                if (response.success) {
                                    $(".k-categories-products-inner").html(response.data.html);
                                    console.log('[v0] Auto-opened all products');
                                }
                            }
                        });
                    } else {
                        console.log('[v0] All products button not found');
                    }
                } else if (autoOpenCategoryId > 0) {
                    console.log('[v0] Looking for category button with id:', autoOpenCategoryId);
                    // Auto-open specific category
                    var $categoryButton = $('[data-category-id="' + autoOpenCategoryId + '"]');
                    console.log('[v0] Found category button:', $categoryButton.length);
                    if ($categoryButton.length) {
                        // Manually trigger the click behavior
                        $categoryButton.css({
                            background: "rgba(255, 107, 157, 0.1)",
                            transform: "scale(1.05)"
                        });
                        
                        // Perform AJAX request directly
                        $.ajax({
                            url: window.khoshtipAjax?.ajaxurl || "/wp-admin/admin-ajax.php",
                            type: "POST",
                            data: {
                                action: "k_get_category_products",
                                category_id: autoOpenCategoryId,
                                page: 1
                            },
                            beforeSend: () => {
                                $(".k-categories-products-inner").html('<div style="text-align: center; padding: 40px;"><div style="display: inline-block; width: 50px; height: 50px; border: 4px solid #f0f0f0; border-top-color: #ff6b9d; border-radius: 50%; animation: spin 1s linear infinite;"></div><p style="margin-top: 15px; color: #666;">در حال بارگذاری...</p></div>');
                            },
                            success: (response) => {
                                if (response.success) {
                                    $(".k-categories-products-inner").html(response.data.html);
                                    console.log('[v0] Auto-opened category:', autoOpenCategoryId);
                                }
                            }
                        });
                    } else {
                        console.log('[v0] Category not found:', autoOpenCategoryId);
                    }
                } else {
                    console.log('[v0] No category to auto-open');
                }
                
                // Set padding for products area
                $('.k-categories-products').css('padding-bottom', '70px');
            }
        });
    }
})();
</script>
<style>
/* Hide bottom bar when panel is auto-opened */
.k-bottom-bar.k-bottom-bar-hidden,
.k-bottom-bar-wave.k-bottom-bar-hidden {
    display: none !important;
}
</style>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
