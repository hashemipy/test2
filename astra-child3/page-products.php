<?php
/**
 * Template Name: Products Page
 * Description: Dynamic page to display products by category or type
 */

get_header();

// Get category or type from URL parameter
$category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
$extra = isset($_GET['extra']) ? sanitize_text_field($_GET['extra']) : '';

// Default values
$page_title = 'محصولات';
$page_icon = '🛍️';
$products = array();

// Query products based on category or type
if ($type === 'sale') {
    $page_title = 'محصولات حراجی';
    $page_icon = '🔥';
    
    if (function_exists('wc_get_product_ids_on_sale')) {
        $sale_ids = wc_get_product_ids_on_sale();
        $parent_ids = array();
        
        foreach ($sale_ids as $id) {
            $product = wc_get_product($id);
            if ($product) {
                // If the product is a variation, get the parent ID
                if ($product->is_type('variation')) {
                    $parent_id = $product->get_parent_id();
                    $parent_ids[] = $parent_id;
                } else {
                    // If the product is a main product, get its own ID
                    $parent_ids[] = $id;
                }
            }
        }
        
        // Remove duplicate IDs
        $parent_ids = array_unique($parent_ids);
        
        usort($parent_ids, function($a, $b) {
            $product_a = wc_get_product($a);
            $product_b = wc_get_product($b);
            
            if (!$product_a || !$product_b) {
                return 0;
            }
            
            $date_a = $product_a->get_date_created();
            $date_b = $product_b->get_date_created();
            
            // Sort descending (newest first)
            return $date_b->getTimestamp() - $date_a->getTimestamp();
        });
        
        $products = $parent_ids;
    }
    
} elseif ($type === 'latest') {
    $page_title = 'جدیدترین محصولات';
    $page_icon = '✨';
    
    // Use wc_get_products which is more optimized than WP_Query
    if (function_exists('wc_get_products')) {
        $latest_products = wc_get_products(array(
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'status' => 'publish',
            'return' => 'ids' // Only return IDs, faster
        ));
        $products = $latest_products;
    }
    
} elseif ($category) {
    // Get information from accordion settings
    $accordion_data = json_decode(do_shortcode('[k_accordion_products_data]'), true);
    
    // Map categories with custom titles and icons
    $category_map = array(
        'girls' => array(
            'title' => $accordion_data['girls_title'] ?? 'محصولات دخترانه', 
            'icon' => $accordion_data['girls_icon'] ?? '👧',
            'products_key' => 'girls_products'
        ),
        'boys' => array(
            'title' => $accordion_data['boys_title'] ?? 'محصولات پسرانه', 
            'icon' => $accordion_data['boys_icon'] ?? '👦',
            'products_key' => 'boys_products'
        ),
        'sports' => array(
            'title' => $accordion_data['sport_title'] ?? 'محصولات ورزشی', 
            'icon' => $accordion_data['sport_icon'] ?? '⚽',
            'products_key' => 'sport_products'
        )
    );
    
    if (isset($category_map[$category])) {
        $page_title = $category_map[$category]['title'];
        $page_icon = $category_map[$category]['icon'];
        $products_key = $category_map[$category]['products_key'];
        
        // Get products from accordion settings
        $selected_products = $accordion_data[$products_key] ?? array();
        
        if (!empty($selected_products)) {
            $products = $selected_products;
        }
    }
} elseif ($extra !== '') {
    $accordion_data = json_decode(do_shortcode('[k_accordion_products_data]'), true);
    $extra_accordions = $accordion_data['extra_accordions'] ?? array();
    
    // Check if unique ID exists in extra_accordions
    if (isset($extra_accordions[$extra])) {
        $extra_accordion = $extra_accordions[$extra];
        $page_title = $extra_accordion['title'] ?? 'محصولات';
        $page_icon = $extra_accordion['icon'] ?? '🎁';
        
        // Get products directly from database, not from cache
        $products = $extra_accordion['products'] ?? array();
    }
}

function render_page_icon($icon_value) {
    if (empty($icon_value)) {
        return '';
    }
    
    // Check if it's a URL or emoji
    if (filter_var($icon_value, FILTER_VALIDATE_URL)) {
        return '<img src="' . esc_url($icon_value) . '" style="width: 2.5rem; height: 2.5rem; object-fit: contain; vertical-align: middle;" alt="icon" />';
    } else {
        return esc_html($icon_value);
    }
}
?>

<main style="padding: 3rem 0; min-height: 60vh;">
    <div class="container">
        <h1 style="font-size: 2.5rem; font-weight: 700; text-align: center; margin-bottom: 2rem; color: hsl(var(--foreground));">
            <?php echo render_page_icon($page_icon); ?> <?php echo esc_html($page_title); ?>
        </h1>
        
        <?php if (!empty($products)) : ?>
            <!-- Updated grid to use infinite scroll - initially shows 10 products -->
            <div class="products-page-grid" id="products-grid">
                <?php 
                $initial_count = 10;
                $displayed = 0;
                foreach ($products as $product_id) : 
                    if ($displayed < $initial_count) :
                        echo khoshtip_kocholo_get_product_card($product_id);
                        $displayed++;
                    endif;
                endforeach; 
                ?>
            </div>
            
            <!-- Loading indicator for infinite scroll -->
            <div id="loading-indicator" style="text-align: center; padding: 2rem; display: none;">
                <div style="display: inline-flex; align-items: center; gap: 1rem; padding: 1rem 2rem; background: hsl(var(--card)); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div style="width: 30px; height: 30px; border: 3px solid hsl(var(--primary) / 0.2); border-top-color: hsl(var(--primary)); border-radius: 50%; animation: spin 0.6s linear infinite;"></div>
                    <span style="font-size: 1rem; font-weight: 500; color: hsl(var(--foreground));">در حال بارگذاری...</span>
                </div>
            </div>
            
            <!-- Store remaining product IDs in JSON format instead of HTML -->
            <script id="remaining-products-data" type="application/json">
                <?php 
                $remaining_products = array();
                $hidden_index = 0;
                foreach ($products as $product_id) : 
                    if ($hidden_index >= $initial_count) :
                        $remaining_products[] = intval($product_id);
                    endif;
                    $hidden_index++;
                endforeach;
                echo json_encode($remaining_products);
                ?>
            </script>
            
            <!-- Improved infinite scroll script that loads products via AJAX -->
            <script>
            (function() {
                console.log('[v0] Initializing infinite scroll');
                
                const productsGrid = document.getElementById('products-grid');
                const loadingIndicator = document.getElementById('loading-indicator');
                const remainingProductsData = JSON.parse(document.getElementById('remaining-products-data').textContent);
                
                console.log('[v0] Total remaining products:', remainingProductsData.length);
                
                let currentIndex = 0;
                let isLoading = false;
                const productsPerLoad = 10;
                
                function loadMoreProducts() {
                    console.log('[v0] loadMoreProducts called, currentIndex:', currentIndex, 'isLoading:', isLoading);
                    
                    if (isLoading || currentIndex >= remainingProductsData.length) {
                        console.log('[v0] Skipping load - already loading or no more products');
                        return;
                    }
                    
                    isLoading = true;
                    loadingIndicator.style.display = 'block';
                    
                    const endIndex = Math.min(currentIndex + productsPerLoad, remainingProductsData.length);
                    const productIdsToLoad = remainingProductsData.slice(currentIndex, endIndex);
                    
                    console.log('[v0] Loading products from index', currentIndex, 'to', endIndex);
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=load_more_products&product_ids=' + JSON.stringify(productIdsToLoad) + '&nonce=<?php echo wp_create_nonce('load_more_products'); ?>'
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('[v0] Response received:', data);
                        
                        if (data.success && data.data.html) {
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = data.data.html;
                            
                            // اضافه کردن هر کارت محصول به گرید
                            while (tempDiv.firstElementChild) {
                                productsGrid.appendChild(tempDiv.firstElementChild);
                            }
                            
                            currentIndex = endIndex;
                            console.log('[v0] Products loaded successfully, new index:', currentIndex);
                        }
                        
                        isLoading = false;
                        loadingIndicator.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('[v0] خطا در بارگذاری محصولات:', error);
                        isLoading = false;
                        loadingIndicator.style.display = 'none';
                    });
                }
                
                function checkScroll() {
                    const scrollPosition = window.innerHeight + window.scrollY;
                    const pageHeight = document.documentElement.scrollHeight;
                    const triggerPoint = pageHeight - 1200;
                    
                    console.log('[v0] Scroll check - position:', scrollPosition, 'trigger:', triggerPoint, 'page height:', pageHeight);
                    
                    if (scrollPosition >= triggerPoint && !isLoading && currentIndex < remainingProductsData.length) {
                        console.log('[v0] Trigger point reached, loading more products');
                        loadMoreProducts();
                    }
                }
                
                let scrollTimeout;
                window.addEventListener('scroll', function() {
                    if (scrollTimeout) {
                        clearTimeout(scrollTimeout);
                    }
                    scrollTimeout = setTimeout(checkScroll, 100);
                }, { passive: true });
                
                window.addEventListener('load', function() {
                    console.log('[v0] Page loaded, running initial check');
                    setTimeout(checkScroll, 500);
                });
                
                setTimeout(function() {
                    console.log('[v0] Running immediate check');
                    checkScroll();
                }, 100);
            })();
            </script>
            
            <style>
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            </style>
        <?php else : ?>
            <p style="text-align: center; color: hsl(var(--muted-foreground)); font-size: 1.125rem;">
                محصولی یافت نشد.
            </p>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
