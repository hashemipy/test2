<?php
/**
 * Template Name: Parcel Tracking Page
 * Description: Simple page for parcel code tracking
 */

get_header();

// Get footer data from shortcode
$footer_data = json_decode(do_shortcode('[k_footer_data]'), true);
$useful_links = $footer_data['useful_links'] ?? array();
$customer_service_links = $footer_data['customer_service_links'] ?? array();

// Get latest products data
$products_data = json_decode(do_shortcode('[k_latest_products_data]'), true);
$product_ids = $products_data['product_ids'] ?? array();
// Get only first 4 products
$product_ids = array_slice($product_ids, 0, 4);
?>

<main class="parcel-tracking-page" style="padding: 2rem 0; min-height: 60vh;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
        
        <!-- Page Header -->
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <h1 style="font-size: 2rem; font-weight: 700; color: hsl(var(--foreground)); margin-bottom: 0.75rem;">
                آموزش دریافت کد مرسوله سایت
            </h1>
            <h2 style="font-size: 1.25rem; font-weight: 600; color: hsl(var(--primary)); margin-bottom: 0;">
                لباس کودک خوشتیپ کوچولو
            </h2>
        </div>

        <!-- Instructions Section -->
        <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 16px; padding: 2rem; margin-bottom: 2.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                
                <div style="display: flex; align-items: start; gap: 1rem;">
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; font-size: 1rem;">1</div>
                    <p style="margin: 0; line-height: 1.7; font-size: 0.95rem; color: #2d3748;">
                        لطفا بعد از <strong>72 ساعت کاری</strong> پس از ثبت سفارش و تاریخ اعلامی ارسال، به این صفحه مراجعه کنید و در کادر پایین با وارد کردن <strong>شماره تلفن</strong> کد خود را دریافت کنید.
                    </p>
                </div>

                <div style="display: flex; align-items: start; gap: 1rem;">
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; font-size: 1rem;">2</div>
                    <p style="margin: 0; line-height: 1.7; font-size: 0.95rem; color: #2d3748;">
                        کد مرسوله را <strong>کپی کرده</strong> و وارد لینک اعلامی شوید.
                    </p>
                </div>

                <div style="display: flex; align-items: start; gap: 1rem;">
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; font-size: 1rem;">3</div>
                    <p style="margin: 0; line-height: 1.7; font-size: 0.95rem; color: #2d3748;">
                        سپس با وارد کردن کد در پنل <strong>سامانه رسمی پست</strong>، از وضعیت بسته خود مطلع شوید.
                    </p>
                </div>

                <div style="display: flex; align-items: start; gap: 1rem;">
                    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; font-size: 1rem;">⚠️</div>
                    <p style="margin: 0; line-height: 1.7; font-size: 0.95rem; color: #c53030;">
                        <strong>لطفا به تاریخ ارسال هر محصول در کپشن آن دقت کنید.</strong>
                    </p>
                </div>

                <div style="display: flex; align-items: start; gap: 1rem;">
                    <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; font-size: 1rem;">📱</div>
                    <p style="margin: 0; line-height: 1.7; font-size: 0.95rem; color: #2d3748;">
                        در صورتی که 72 ساعت پس از تاریخ ارسال نوشته شده در کپشن، کد مرسوله شما یافت نشد، به <strong>ادمین سایت در واتساپ</strong> پیام بدهید.
                    </p>
                </div>

            </div>
        </div>

        <!-- Tracking Plugin Shortcode -->
        <div style="background: white; border-radius: 16px; padding: 2rem; margin-bottom: 2.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <!-- Plugin shortcode for tracking -->
            <?php echo do_shortcode('[parcel_code_upload]'); ?>
        </div>

        <!-- Featured Products Section -->
        <?php if (!empty($product_ids)) : ?>
        <div style="margin-bottom: 2.5rem;">
            <h3 style="font-size: 1.75rem; font-weight: 700; text-align: center; color: hsl(var(--foreground)); margin-bottom: 2rem;">
                محصولات پیشنهادی ما
            </h3>
            
            <div class="products-page-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem;">
                <style>
                @media (min-width: 768px) {
                    .products-page-grid {
                        grid-template-columns: repeat(4, 1fr) !important;
                    }
                }
                </style>
                <?php 
                foreach ($product_ids as $product_id) : 
                    echo khoshtip_kocholo_get_product_card($product_id);
                endforeach; 
                ?>
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="<?php echo esc_url(home_url('/products/?type=latest')); ?>" 
                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.875rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 1rem; transition: all 0.3s; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);">
                    مشاهده همه محصولات
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php
get_footer();
?>
