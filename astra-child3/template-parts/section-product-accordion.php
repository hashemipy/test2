<?php
/**
 * Product Accordion Section Template
 */

$accordion_data = json_decode(do_shortcode('[k_accordion_products_data]'), true);

error_log('[v0] Template received data: ' . print_r($accordion_data, true));

$latest_title = $accordion_data['latest_title'] ?? 'همه محصولات';
$latest_view_all_text = $accordion_data['latest_view_all_text'] ?? 'نمایش همه ←';
$latest_gradient_start = $accordion_data['latest_gradient_start'] ?? '#f093fb';
$latest_gradient_end = $accordion_data['latest_gradient_end'] ?? '#f5576c';
$latest_button_bg = $accordion_data['latest_button_bg'] ?? 'rgba(255, 255, 255, 0.3)';
$latest_button_text = $accordion_data['latest_button_text'] ?? '#ffffff';
$latest_icon = $accordion_data['latest_icon'] ?? '✨';
$latest_layout = $accordion_data['latest_layout'] ?? 'scroll';
$latest_default_open = ($accordion_data['latest_default_open'] ?? '1') == '1';
$latest_scroll_limit = $accordion_data['latest_scroll_limit'] ?? 20;
$latest_grid_limit = $accordion_data['latest_grid_limit'] ?? 10;
$latest_order = $accordion_data['latest_order'] ?? 0;

$girls_title = $accordion_data['girls_title'] ?? 'محصولات دخترانه';
$girls_view_all_text = $accordion_data['girls_view_all_text'] ?? 'نمایش همه ←';
$boys_title = $accordion_data['boys_title'] ?? 'محصولات پسرانه';
$boys_view_all_text = $accordion_data['boys_view_all_text'] ?? 'نمایش همه ←';
$sport_title = $accordion_data['sport_title'] ?? 'محصولات ورزشی';
$sport_view_all_text = $accordion_data['sport_view_all_text'] ?? 'نمایش همه ←';

$girls_gradient_start = $accordion_data['girls_gradient_start'] ?? '#ff6b9d';
$girls_gradient_end = $accordion_data['girls_gradient_end'] ?? '#ffc3d7';
$girls_button_bg = $accordion_data['girls_button_bg'] ?? '#ff6b9d';
$girls_button_text = $accordion_data['girls_button_text'] ?? '#ffffff';
$girls_icon = $accordion_data['girls_icon'] ?? '👧';
$girls_layout = $accordion_data['girls_layout'] ?? 'scroll';
$girls_default_open = ($accordion_data['girls_default_open'] ?? '0') == '1';
$girls_scroll_limit = $accordion_data['girls_scroll_limit'] ?? 20;
$girls_grid_limit = $accordion_data['girls_grid_limit'] ?? 10;
$girls_order = $accordion_data['girls_order'] ?? 1;

$boys_gradient_start = $accordion_data['boys_gradient_start'] ?? '#4dabf7';
$boys_gradient_end = $accordion_data['boys_gradient_end'] ?? '#d0ebff';
$boys_button_bg = $accordion_data['boys_button_bg'] ?? '#4dabf7';
$boys_button_text = $accordion_data['boys_button_text'] ?? '#ffffff';
$boys_icon = $accordion_data['boys_icon'] ?? '👦';
$boys_layout = $accordion_data['boys_layout'] ?? 'scroll';
$boys_default_open = ($accordion_data['boys_default_open'] ?? '0') == '1';
$boys_scroll_limit = $accordion_data['boys_scroll_limit'] ?? 20;
$boys_grid_limit = $accordion_data['boys_grid_limit'] ?? 10;
$boys_order = $accordion_data['boys_order'] ?? 2;

$sport_gradient_start = $accordion_data['sport_gradient_start'] ?? '#51cf66';
$sport_gradient_end = $accordion_data['sport_gradient_end'] ?? '#d3f9d8';
$sport_button_bg = $accordion_data['sport_button_bg'] ?? '#51cf66';
$sport_button_text = $accordion_data['sport_button_text'] ?? '#ffffff';
$sport_icon = $accordion_data['sport_icon'] ?? '⚽';
$sport_layout = $accordion_data['sport_layout'] ?? 'scroll';
$sport_default_open = ($accordion_data['sport_default_open'] ?? '0') == '1';
$sport_scroll_limit = $accordion_data['sport_scroll_limit'] ?? 20;
$sport_grid_limit = $accordion_data['sport_grid_limit'] ?? 10;
$sport_order = $accordion_data['sport_order'] ?? 3;

$latest_products = array();
if (function_exists('wc_get_products')) {
    $cache_key_latest = 'k_latest_products_ids';
    $latest_products = get_transient($cache_key_latest);
    
    if (false === $latest_products) {
        $latest_products = wc_get_products(array(
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'status' => 'publish',
            'stock_status' => 'instock',
            'return' => 'ids'
        ));
        set_transient($cache_key_latest, $latest_products, 15 * MINUTE_IN_SECONDS);
    }
}

$girls_products = $accordion_data['girls_products'] ?? array();
$boys_products = $accordion_data['boys_products'] ?? array();
$sport_products = $accordion_data['sport_products'] ?? array();

function render_icon($icon_value) {
    if (empty($icon_value)) {
        return '';
    }
    
    if (filter_var($icon_value, FILTER_VALIDATE_URL)) {
        return '<img src="' . esc_url($icon_value) . '" style="width: 2rem; height: 2rem; object-fit: contain;" alt="icon" />';
    } else {
        return '<span style="font-size: 2rem;">' . esc_html($icon_value) . '</span>';
    }
}

$all_sections = [];

// Latest products section
if (!empty($latest_products)) {
    $all_sections[] = [
        'type' => 'latest',
        'order' => $latest_order,
        'data' => [
            'title' => $latest_title,
            'icon' => $latest_icon,
            'gradient_start' => $latest_gradient_start,
            'gradient_end' => $latest_gradient_end,
            'button_bg' => $latest_button_bg,
            'button_text' => $latest_button_text,
            'layout' => $latest_layout,
            'default_open' => $latest_default_open,
            'scroll_limit' => $latest_scroll_limit,
            'grid_limit' => $latest_grid_limit,
            'view_all_text' => $latest_view_all_text,
            'products' => $latest_products,
            'url' => home_url('/products/?type=latest'),
        ]
    ];
}

// Girls section
if (!empty($girls_products) && !empty($girls_title)) {
    $all_sections[] = [
        'type' => 'girls',
        'order' => $girls_order,
        'data' => [
            'title' => $girls_title,
            'icon' => $girls_icon,
            'gradient_start' => $girls_gradient_start,
            'gradient_end' => $girls_gradient_end,
            'button_bg' => $girls_button_bg,
            'button_text' => $girls_button_text,
            'layout' => $girls_layout,
            'default_open' => $girls_default_open,
            'scroll_limit' => $girls_scroll_limit,
            'grid_limit' => $girls_grid_limit,
            'view_all_text' => $girls_view_all_text,
            'products' => $girls_products,
            'url' => home_url('/products/?category=girls'),
        ]
    ];
}

// Boys section
if (!empty($boys_products) && !empty($boys_title)) {
    $all_sections[] = [
        'type' => 'boys',
        'order' => $boys_order,
        'data' => [
            'title' => $boys_title,
            'icon' => $boys_icon,
            'gradient_start' => $boys_gradient_start,
            'gradient_end' => $boys_gradient_end,
            'button_bg' => $boys_button_bg,
            'button_text' => $boys_button_text,
            'layout' => $boys_layout,
            'default_open' => $boys_default_open,
            'scroll_limit' => $boys_scroll_limit,
            'grid_limit' => $boys_grid_limit,
            'view_all_text' => $boys_view_all_text,
            'products' => $boys_products,
            'url' => home_url('/products/?category=boys'),
        ]
    ];
}

// Sport section
if (!empty($sport_products) && !empty($sport_title)) {
    $all_sections[] = [
        'type' => 'sport',
        'order' => $sport_order,
        'data' => [
            'title' => $sport_title,
            'icon' => $sport_icon,
            'gradient_start' => $sport_gradient_start,
            'gradient_end' => $sport_gradient_end,
            'button_bg' => $sport_button_bg,
            'button_text' => $sport_button_text,
            'layout' => $sport_layout,
            'default_open' => $sport_default_open,
            'scroll_limit' => $sport_scroll_limit,
            'grid_limit' => $sport_grid_limit,
            'view_all_text' => $sport_view_all_text,
            'products' => $sport_products,
            'url' => home_url('/products/?category=sports'),
        ]
    ];
}

// Extra accordions
$extra_accordions = $accordion_data['extra_accordions'] ?? [];
foreach ($extra_accordions as $id => $extra) {
    $extra_products = $extra['products'] ?? [];
    if (!empty($extra_products)) {
        $all_sections[] = [
            'type' => 'extra',
            'order' => $extra['order'] ?? 999,
            'data' => [
                'id' => $id,
                'title' => $extra['title'] ?? 'محصولات',
                'icon' => $extra['icon'] ?? '🎁',
                'gradient_start' => $extra['gradient_start'] ?? '#9c27b0',
                'gradient_end' => $extra['gradient_end'] ?? '#e1bee7',
                'button_bg' => $extra['button_bg'] ?? '#9c27b0',
                'button_text' => $extra['button_text'] ?? '#ffffff',
                'layout' => $extra['layout'] ?? 'scroll',
                'default_open' => ($extra['default_open'] ?? '0') == '1',
                'scroll_limit' => $extra['scroll_limit'] ?? 20,
                'grid_limit' => $extra['grid_limit'] ?? 10,
                'view_all_text' => $extra['view_all_text'] ?? 'نمایش همه ←',
                'products' => $extra_products,
                'url' => home_url("/products/?extra={$id}"),
            ]
        ];
    }
}

usort($all_sections, function($a, $b) {
    return $a['order'] - $b['order'];
});

?>

<section class="product-accordion-section" style="padding: 3rem 0;">
    <div class="container">
        <div class="accordion-container" style="display: flex; flex-direction: column; gap: 1rem;">
            
            <?php foreach ($all_sections as $section) : 
                $type = $section['type'];
                $data = $section['data'];
                $is_default_open = $data['default_open'];
                $layout = $data['layout'];
            ?>
                <div class="accordion-item">
                    <button class="accordion-trigger accordion-<?php echo esc_attr($type === 'extra' ? 'extra-' . $data['id'] : $type); ?><?php echo $is_default_open ? ' active' : ''; ?>" data-accordion-trigger style="width: 100%; text-align: right; padding: 1.5rem; background: linear-gradient(135deg, <?php echo esc_attr($data['gradient_start']); ?> 0%, <?php echo esc_attr($data['gradient_end']); ?> 100%); border: 2px solid <?php echo esc_attr($data['gradient_start']); ?>; border-radius: var(--radius); font-size: 1.5rem; font-weight: 700; cursor: pointer; display: flex; justify-content: space-between; align-items: center; color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); transition: all 0.3s ease; position: relative;">
                        <span style="display: flex; align-items: center; gap: 0.75rem;">
                            <?php echo render_icon($data['icon']); ?>
                            <span><?php echo esc_html($data['title']); ?></span>
                        </span>
                        <span style="display: flex; align-items: center; gap: 0.75rem;">
                            <a href="<?php echo esc_url($data['url']); ?>" onclick="event.stopPropagation();" style="padding: 0.5rem 1rem; background: <?php echo esc_attr($data['button_bg']); ?>; color: <?php echo esc_attr($data['button_text']); ?>; border-radius: 0.5rem; font-weight: 700; text-decoration: none; font-size: 0.85rem; white-space: nowrap; border: 2px solid rgba(255, 255, 255, 0.4); transition: all 0.3s ease; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);" onmouseover="this.style.opacity='0.9'; this.style.transform='scale(1.05)'" onmouseout="this.style.opacity='1'; this.style.transform='scale(1)'">
                                <?php echo esc_html($data['view_all_text']); ?>
                            </a>
                            <span class="accordion-icon" style="transition: transform 0.3s ease;<?php echo $is_default_open ? ' transform: rotate(180deg);' : ''; ?> font-size: 1.2rem;">▼</span>
                        </span>
                    </button>
                    <div class="accordion-content" data-accordion-content style="display: <?php echo $is_default_open ? 'block' : 'none'; ?>; padding-top: 1rem;">
                        <?php if ($layout === 'grid') : ?>
                            <!-- Grid layout -->
                            <div class="accordion-products-grid">
                                <?php 
                                $display_count = 0;
                                foreach ($data['products'] as $product_id) : 
                                    if ($display_count >= $data['grid_limit']) break;
                                    $product_card = khoshtip_kocholo_get_product_card($product_id);
                                    if (!empty($product_card)) :
                                        $display_count++;
                                ?>
                                    <?php echo $product_card; ?>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                            <div style="text-align: center; margin-top: 1.5rem;">
                                <a href="<?php echo esc_url($data['url']); ?>" style="display: inline-block; padding: 0.75rem 2rem; background: <?php echo esc_attr($data['button_bg']); ?>; color: <?php echo esc_attr($data['button_text']); ?>; border-radius: 0.5rem; font-weight: 700; text-decoration: none; transition: all 0.3s ease;">
                                    دیدن محصولات بیشتر ←
                                </a>
                            </div>
                        <?php else : ?>
                            <!-- Scroll layout -->
                            <div class="swiper accordion-swiper-<?php echo esc_attr($type === 'extra' ? 'extra-' . $data['id'] : $type); ?>">
                                <div class="swiper-wrapper">
                                    <?php 
                                    $scroll_count = 0;
                                    foreach ($data['products'] as $product_id) : 
                                        if ($scroll_count >= $data['scroll_limit']) break;
                                        $product_card = khoshtip_kocholo_get_product_card($product_id);
                                        if (!empty($product_card)) :
                                            $scroll_count++;
                                    ?>
                                        <div class="swiper-slide">
                                            <?php echo $product_card; ?>
                                        </div>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
        </div>
    </div>
</section>

<style>
.accordion-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .accordion-products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
}

@media (max-width: 480px) {
    .accordion-products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
}

.accordion-products-grid .product-card {
    margin: 0;
}

/* Accordion trigger styles */
.accordion-trigger {
    transition: transform 0.2s, box-shadow 0.2s;
}

.accordion-trigger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25) !important;
}

.accordion-trigger:active {
    transform: translateY(0);
}

/* Reduced accordion button height on mobile */
@media (max-width: 768px) {
    .accordion-trigger {
        padding: 1rem !important;
        font-size: 1.2rem !important;
    }
    
    .accordion-trigger span {
        font-size: 0.9rem;
    }
    
    .accordion-trigger a {
        font-size: 0.75rem !important;
        padding: 0.4rem 0.8rem !important;
    }
    
    .accordion-icon {
        font-size: 1rem !important;
    }
}

.accordion-item.active .accordion-icon {
    transform: rotate(180deg);
}
</style>
