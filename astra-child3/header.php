<?php
/**
 * Custom Header Template
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?> style="background-color: <?php echo esc_attr($main_bg_color); ?>;">
<?php wp_body_open(); ?>

<?php
// Get header data from shortcode
$header_data = json_decode(do_shortcode('[k_header_data]'), true);
$site_title = $header_data['site_title'] ?? get_bloginfo('name');
$site_tagline = $header_data['site_tagline'] ?? '';
$site_title_color = $header_data['site_title_color'] ?? '#ffffff';
$site_title_size = $header_data['site_title_size'] ?? '24';
$site_tagline_color = $header_data['site_tagline_color'] ?? 'rgba(255,255,255,0.9)';
$site_tagline_size = $header_data['site_tagline_size'] ?? '12';
$nav_links = $header_data['nav_links'] ?? array();
$mobile_menu_links = $header_data['mobile_menu_links'] ?? $nav_links;

$header_gradient_start = get_option('k_header_gradient_start', '#ff6b9d');
$header_gradient_end = get_option('k_header_gradient_end', '#ffc3d7');

// Get background colors
$main_bg_color = get_option('k_main_background_color', '#ffffff');
$accordion_bg_color = get_option('k_accordion_background_color', '#f5f5f5');
$blog_bg_color = get_option('k_blog_background_color', '#ffffff');
$banner_bg_color = get_option('k_banner_background_color', '#f9fafb');

$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
$google_picture = $is_logged_in ? get_user_meta(get_current_user_id(), 'google_profile_picture', true) : '';
$avatar_url = $google_picture ? $google_picture : ($is_logged_in ? get_avatar_url(get_current_user_id(), array('size' => 40)) : '');

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

<!-- Applied gradient background to header -->
<header class="site-header sticky top-0 z-50 w-full border-b text-primary-foreground" style="position: sticky; top: 0; z-index: 50; width: 100%; border-bottom: 1px solid hsl(var(--border)); background: linear-gradient(135deg, <?php echo esc_attr($header_gradient_start); ?>, <?php echo esc_attr($header_gradient_end); ?>); color: #fff;">
    <div class="container mx-auto flex items-center justify-between" style="display: flex; height: 60px; align-items: center; justify-content: space-between; padding: 0 1rem;">
        
        <div class="flex items-center gap-6" style="display: flex; align-items: center; gap: 1.5rem;">
            <!-- اعمال رنگ و سایز از تنظیمات -->
            <div>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="text-2xl font-bold" style="font-size: <?php echo esc_attr($site_title_size); ?>px; font-weight: 700; color: <?php echo esc_attr($site_title_color); ?>; text-decoration: none; display: block;">
                    <?php echo esc_html($site_title); ?>
                </a>
                <?php if (!empty($site_tagline)) : ?>
                    <p style="font-size: <?php echo esc_attr($site_tagline_size); ?>px; color: <?php echo esc_attr($site_tagline_color); ?>; margin: 0; margin-top: 2px;">
                        <?php echo esc_html($site_tagline); ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <nav class="hidden md:flex gap-6 desktop-nav" style="display: none; gap: 1.5rem;">
                <?php foreach ($nav_links as $link) : ?>
                    <a href="<?php echo esc_url($link['url']); ?>" class="text-md font-medium nav-link" style="font-size: 1rem; font-weight: 500; color: #fff; opacity: 0.8; text-decoration: none;">
                        <?php echo esc_html($link['text']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
        
        <div class="flex items-center gap-2" style="display: flex; align-items: center; gap: 0.5rem;">
            <!-- Search Icon -->
            <button aria-label="جستجو" data-search-trigger class="p-2 hover:opacity-80" style="padding: 0.5rem; background: none; border: none; cursor: pointer; color: #fff;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </button>
            
            <!-- Updated user profile icon to show actual profile picture when logged in -->
            <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" aria-label="حساب کاربری" class="p-2 hover:opacity-80" style="padding: 0.5rem; color: #fff; text-decoration: none; position: relative; display: flex; align-items: center; justify-content: center;">
                <?php if ($is_logged_in && $avatar_url) : ?>
                    <!-- Show user profile picture when logged in -->
                    <img src="<?php echo esc_url($avatar_url); ?>" 
                         alt="<?php echo esc_attr($current_user->display_name); ?>" 
                         style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #fff;" />
                <?php else : ?>
                    <!-- Show default user icon when not logged in -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                <?php endif; ?>
            </a>
            
            <!-- حذف آیکون سبد خرید از هدر -->
            
            <!-- Mobile Menu Toggle -->
            <button class="md:hidden mobile-menu-toggle p-2" aria-label="منو" data-mobile-menu-trigger style="padding: 0.5rem; background: none; border: none; cursor: pointer; color: #fff;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Mobile Menu (Off-canvas) -->
    <div class="mobile-menu-content" data-mobile-menu style="position: fixed; top: 0; right: -100%; width: 80%; max-width: 300px; height: 100vh; background-color: hsl(var(--background)); box-shadow: -2px 0 10px rgba(0,0,0,0.1); transition: right 0.3s ease; z-index: 100; overflow-y: auto;">
        <div style="padding: 2rem;">
            <button class="mobile-menu-close" data-mobile-menu-close style="position: absolute; top: 1rem; left: 1rem; background: none; border: none; cursor: pointer; font-size: 1.5rem; color: #000;">×</button>
            
            <!-- Add user info in mobile menu -->
            <?php if ($is_logged_in) : ?>
                <div style="margin-top: 1rem; padding: 1rem; background: #f7fafc; border-radius: 12px; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($current_user->display_name); ?>" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;" />
                        <div>
                            <div style="font-weight: 600; color: #1a202c; margin-bottom: 4px;"><?php echo esc_html($current_user->display_name); ?></div>
                            <div style="font-size: 12px; color: #718096;"><?php echo esc_html($current_user->user_email); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <nav style="margin-top: 2rem;">
                <?php foreach ($mobile_menu_links as $link) : ?>
                    <a href="<?php echo esc_url($link['url']); ?>" style="display: block; padding: 1rem 0; color: hsl(var(--foreground)); text-decoration: none; border-bottom: 1px solid hsl(var(--border));">
                        <?php echo esc_html($link['text']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
    
    <!-- Search Modal -->
    <div class="search-modal" data-search-modal style="position: fixed; top: 0; left: 0; width: 100%; height: 100vh; background-color: #ffffff; display: none; z-index: 200; overflow-y: auto;">
        <div style="width: 100%; height: 100%; display: flex; flex-direction: column;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, <?php echo esc_attr($header_gradient_start); ?>, <?php echo esc_attr($header_gradient_end); ?>); padding: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                    <button class="search-modal-close" data-search-modal-close style="background: none; border: none; color: #fff; font-size: 28px; cursor: pointer; padding: 0; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">×</button>
                    <h2 style="color: #fff; font-size: 18px; font-weight: 700; margin: 0; flex: 1; text-align: center;"><?php echo esc_html($site_title); ?></h2>
                    <div style="width: 36px; flex-shrink: 0;"></div>
                </div>
            </div>
            
            <!-- Content area -->
            <div style="flex: 1; overflow-y: auto; padding: 20px;">
                <!-- Regular Search -->
                <h3 style="margin: 0 0 1rem 0; font-size: 1.125rem; font-weight: 700; color: #333;">جستجوی محصولات</h3>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                    <input type="search" id="k-search-input-name" placeholder="نام محصول را وارد کنید..." style="width: 100%; padding: 1rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem;" />
                    <button type="button" id="k-search-name-btn" style="padding: 1rem 2rem; background: linear-gradient(135deg, <?php echo esc_attr($header_gradient_start); ?>, <?php echo esc_attr($header_gradient_end); ?>); color: #fff; border: none; border-radius: 8px; font-size: 1rem; cursor: pointer; white-space: nowrap; font-weight: 600;">جستجو</button>
                </div>
                
                <!-- Name Search Results -->
                <div id="k-name-search-results" style="margin-bottom: 1.5rem; display: none;"></div>
                
                <?php if (!empty($size_terms) && !is_wp_error($size_terms)) : ?>
                <!-- Size Search Section -->
                <div class="k-size-search-section" style="padding-top: 1.5rem; border-top: 2px solid #f0f0f0;">
                    <h3 style="margin: 0 0 1rem 0; font-size: 1.125rem; font-weight: 700; color: #333; display: flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                        جستجو بر اساس سایز
                    </h3>
                    
                    <!-- Grid layout برای 7 سایز در هر ردیف با سایز کوچکتر -->
                    <div class="k-size-checkboxes" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; margin-bottom: 1rem;">
                        <?php foreach ($size_terms as $term) : ?>
                            <label class="k-size-checkbox" style="display: flex; align-items: center; justify-content: center; padding: 8px 4px; background: linear-gradient(135deg, #f8f8f8 0%, #fff 100%); border: 2px solid #e5e5e5; border-radius: 6px; cursor: pointer; transition: all 0.2s ease; font-weight: 600; font-size: 13px; text-align: center;">
                                <input type="checkbox" name="search_sizes[]" value="<?php echo esc_attr($term->slug); ?>" style="display: none;">
                                <span><?php echo esc_html($term->name); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="button" id="k-search-by-size-btn" style="width: 100%; padding: 12px 20px; background: linear-gradient(135deg, <?php echo esc_attr($header_gradient_start); ?>, <?php echo esc_attr($header_gradient_end); ?>); color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        جستجو بر اساس سایز
                    </button>
                    
                    <!-- Results Container -->
                    <div id="k-size-search-results" style="margin-top: 1rem; display: none;"></div>
                    
                    <!-- Loading State -->
                    <div id="k-size-search-loading" style="display: none; text-align: center; padding: 30px;">
                        <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid #f0f0f0; border-top-color: <?php echo esc_attr($header_gradient_start); ?>; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                        <p style="margin-top: 10px; color: #666;">در حال جستجو...</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <style>
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .k-size-checkbox:has(input:checked) {
            background: linear-gradient(135deg, <?php echo esc_attr($header_gradient_start); ?>, <?php echo esc_attr($header_gradient_end); ?>) !important;
            border-color: <?php echo esc_attr($header_gradient_start); ?> !important;
            color: #fff !important;
        }
        .k-size-checkbox:hover {
            border-color: <?php echo esc_attr($header_gradient_start); ?>;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        #k-search-by-size-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .k-size-product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .k-size-product-card:hover img {
            transform: scale(1.05);
        }
    </style>
</header>
