<?php
/**
 * Admin tabs rendering and saving
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Admin_Tabs {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Render specific tab content
     */
    public function render_tab($tab) {
        $method = 'render_' . $tab . '_tab';
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    /**
     * Save tab data
     */
    public function save_tab_data($tab, $data) {
        $method = 'save_' . $tab . '_tab';
        if (method_exists($this, $method)) {
            $this->$method($data);
        }

        KK_Cache::clear_products_cache();
    }

    // ==========================================
    // AUTH TAB
    // ==========================================

    private function render_auth_tab() {
        $google_client_id = get_option('khoshtip_google_client_id', '');
        $google_client_secret = get_option('khoshtip_google_client_secret', '');
        $faraz_api_key = get_option('khoshtip_faraz_api_key', '');
        $faraz_sender_number = get_option('khoshtip_faraz_sender_number', '');
        $sms_enabled = get_option('khoshtip_sms_enabled', '0');
        $require_phone = get_option('khoshtip_require_phone', '0');
        ?>
        <div class="settings-section">
            <h2>🔑 <?php esc_html_e('تنظیمات Google OAuth', 'khoshtip-kocholo'); ?></h2>
            <p class="description"><?php esc_html_e('برای فعال‌سازی ورود با Google، اطلاعات زیر را وارد کنید.', 'khoshtip-kocholo'); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="khoshtip_google_client_id">Client ID</label></th>
                    <td>
                        <input type="text" id="khoshtip_google_client_id" name="khoshtip_google_client_id"
                               value="<?php echo esc_attr($google_client_id); ?>" class="large-text"
                               placeholder="1063548887243-xxx.apps.googleusercontent.com">
                        <p class="description"><?php esc_html_e('Client ID از Google Cloud Console', 'khoshtip-kocholo'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="khoshtip_google_client_secret">Client Secret</label></th>
                    <td>
                        <input type="text" id="khoshtip_google_client_secret" name="khoshtip_google_client_secret"
                               value="<?php echo esc_attr($google_client_secret); ?>" class="large-text"
                               placeholder="GOCSPX-xxxxxxxxxxxxx">
                    </td>
                </tr>
                <tr>
                    <th scope="row">Redirect URI</th>
                    <td>
                        <div class="auth-help-box">
                            <code><?php echo esc_url(home_url('/google-callback/')); ?></code>
                            <p><?php esc_html_e('این آدرس را در Google Cloud Console اضافه کنید.', 'khoshtip-kocholo'); ?></p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="settings-section">
            <h2>📱 <?php esc_html_e('تنظیمات فراز اس‌ام‌اس', 'khoshtip-kocholo'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('فعال‌سازی ورود با موبایل', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="khoshtip_sms_enabled" value="1" <?php checked($sms_enabled, '1'); ?>>
                            <?php esc_html_e('فعال‌سازی ورود و ثبت‌نام با شماره موبایل', 'khoshtip-kocholo'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="khoshtip_faraz_api_key">API Key</label></th>
                    <td>
                        <input type="text" id="khoshtip_faraz_api_key" name="khoshtip_faraz_api_key"
                               value="<?php echo esc_attr($faraz_api_key); ?>" class="large-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="khoshtip_faraz_sender_number"><?php esc_html_e('شماره ارسال‌کننده', 'khoshtip-kocholo'); ?></label></th>
                    <td>
                        <input type="text" id="khoshtip_faraz_sender_number" name="khoshtip_faraz_sender_number"
                               value="<?php echo esc_attr($faraz_sender_number); ?>" class="large-text" dir="ltr">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('الزامی بودن شماره', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="khoshtip_require_phone" value="1" <?php checked($require_phone, '1'); ?>>
                            <?php esc_html_e('شماره موبایل برای ثبت‌نام الزامی باشد', 'khoshtip-kocholo'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <div class="settings-section">
            <h2>✅ <?php esc_html_e('وضعیت تنظیمات', 'khoshtip-kocholo'); ?></h2>
            <table class="form-table">
                <tr>
                    <th>Google OAuth</th>
                    <td>
                        <?php if (!empty($google_client_id) && !empty($google_client_secret)) : ?>
                            <span class="status-active">✓ <?php esc_html_e('فعال', 'khoshtip-kocholo'); ?></span>
                        <?php else : ?>
                            <span class="status-inactive">✗ <?php esc_html_e('غیرفعال', 'khoshtip-kocholo'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('فراز اس‌ام‌اس', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <?php if ($sms_enabled === '1' && !empty($faraz_api_key) && !empty($faraz_sender_number)) : ?>
                            <span class="status-active">✓ <?php esc_html_e('فعال', 'khoshtip-kocholo'); ?></span>
                        <?php elseif ($sms_enabled === '1') : ?>
                            <span class="status-warning">⚠ <?php esc_html_e('اطلاعات ناقص', 'khoshtip-kocholo'); ?></span>
                        <?php else : ?>
                            <span class="status-inactive">✗ <?php esc_html_e('غیرفعال', 'khoshtip-kocholo'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    private function save_auth_tab($data) {
        update_option('khoshtip_google_client_id', sanitize_text_field($data['khoshtip_google_client_id'] ?? ''));
        update_option('khoshtip_google_client_secret', sanitize_text_field($data['khoshtip_google_client_secret'] ?? ''));
        update_option('khoshtip_faraz_api_key', sanitize_text_field($data['khoshtip_faraz_api_key'] ?? ''));
        update_option('khoshtip_faraz_sender_number', sanitize_text_field($data['khoshtip_faraz_sender_number'] ?? ''));
        update_option('khoshtip_sms_enabled', isset($data['khoshtip_sms_enabled']) ? '1' : '0');
        update_option('khoshtip_require_phone', isset($data['khoshtip_require_phone']) ? '1' : '0');

        flush_rewrite_rules();
    }

    // ==========================================
    // SIZE SEARCH TAB
    // ==========================================

    private function render_size_search_tab() {
        $selected_sizes = get_option('k_search_sizes', []);
        if (!is_array($selected_sizes)) {
            $selected_sizes = [];
        }

        // Get all terms from pa_size attribute
        $size_terms = get_terms([
            'taxonomy' => 'pa_size',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        ?>
        <div class="settings-section">
            <h2>جستجو بر اساس سایز</h2>
            <p class="description">سایزهایی که می‌خواهید در مودال جستجو به کاربران نمایش داده شود را انتخاب کنید.</p>

            <?php if (is_wp_error($size_terms) || empty($size_terms)) : ?>
                <div class="k-notice k-notice-warning">
                    <strong>توجه:</strong> هیچ سایزی در ویژگی pa_size یافت نشد. ابتدا ویژگی سایز را در ووکامرس ایجاد کنید.
                </div>
            <?php else : ?>
                <!-- نمایش تعداد سایزهای انتخاب شده برای debug -->
                <?php if (!empty($selected_sizes)) : ?>
                    <div class="k-notice k-notice-success" style="margin-bottom: 15px;">
                        <strong><?php echo count($selected_sizes); ?></strong> سایز انتخاب شده:
                        <code><?php echo esc_html(implode(', ', $selected_sizes)); ?></code>
                    </div>
                <?php endif; ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">سایزهای قابل نمایش</th>
                        <td>
                            <div class="k-size-checkboxes" style="display: flex; flex-wrap: wrap; gap: 15px; max-height: 300px; overflow-y: auto; padding: 15px; background: #f9f9f9; border-radius: 8px; border: 1px solid #e5e5e5;">
                                <?php foreach ($size_terms as $term) :
                                    $is_checked = in_array($term->slug, $selected_sizes, true);
                                ?>
                                    <label class="k-size-checkbox-label" style="display: flex; align-items: center; gap: 8px; padding: 10px 15px; background: <?php echo $is_checked ? '#e8f4f8' : '#fff'; ?>; border-radius: 6px; border: 2px solid <?php echo $is_checked ? '#0073aa' : '#e5e5e5'; ?>; cursor: pointer; transition: all 0.2s ease; min-width: 80px; justify-content: center;">
                                        <input type="checkbox"
                                               name="k_search_sizes[]"
                                               value="<?php echo esc_attr($term->slug); ?>"
                                               <?php checked($is_checked); ?>
                                               style="margin: 0;">
                                        <span style="font-weight: 600; font-size: 14px;"><?php echo esc_html($term->name); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="description" style="margin-top: 10px;">سایزهای انتخاب شده در مودال جستجوی سایت به کاربران نمایش داده می‌شوند.</p>
                        </td>
                    </tr>
                </table>

                <div class="k-notice k-notice-success" style="margin-top: 20px;">
                    <strong>راهنما:</strong> این سیستم محصولاتی را نمایش می‌دهد که واریانت موجود با سایز انتخابی دارند. یعنی:
                    <ul style="margin: 10px 0 0 20px; list-style: disc;">
                        <li>واریانت باید attribute سایز مطابق با انتخاب کاربر داشته باشد</li>
                        <li>واریانت باید in_stock باشد</li>
                        <li>موجودی واریانت باید بیشتر از 0 باشد</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private function save_size_search_tab($data) {
        $sizes = [];
        if (isset($data['k_search_sizes']) && is_array($data['k_search_sizes'])) {
            $sizes = array_map('sanitize_text_field', $data['k_search_sizes']);
            $sizes = array_filter($sizes); // حذف مقادیر خالی
            $sizes = array_values($sizes); // ریست کردن کلیدها
        }
        update_option('k_search_sizes', $sizes, false); // false = no autoload برای بهینه‌سازی
    }

    // ==========================================
    // HEADER TAB
    // ==========================================

    private function render_header_tab() {
        $site_title = get_option('k_site_title', 'خوشتیپ کوچولو');
        $site_tagline = get_option('k_site_tagline', '');
        $site_title_color = get_option('k_site_title_color', '#ffffff');
        $site_title_size = get_option('k_site_title_size', '24');
        $site_tagline_color = get_option('k_site_tagline_color', 'rgba(255,255,255,0.9)');
        $site_tagline_size = get_option('k_site_tagline_size', '12');
        $nav_links = get_option('k_nav_links', []);
        $mobile_menu_links = get_option('k_mobile_menu_links', []);
        $header_gradient_start = get_option('k_header_gradient_start', '#ff6b9d');
        $header_gradient_end = get_option('k_header_gradient_end', '#ffc3d7');
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('عنوان سایت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_site_title" value="<?php echo esc_attr($site_title); ?>" class="regular-text"></td>
            </tr>
            <!-- تنظیمات رنگ و سایز عنوان سایت -->
            <tr>
                <th scope="row"><?php esc_html_e('رنگ عنوان سایت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_site_title_color" value="<?php echo esc_attr($site_title_color); ?>" class="k-color-picker"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('سایز عنوان سایت (px)', 'khoshtip-kocholo'); ?></th>
                <td><input type="number" name="k_site_title_size" value="<?php echo esc_attr($site_title_size); ?>" min="12" max="48" style="width: 80px;"> px</td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('شعار سایت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_site_tagline" value="<?php echo esc_attr($site_tagline); ?>" class="regular-text"></td>
            </tr>
            <!-- تنظیمات رنگ و سایز شعار سایت -->
            <tr>
                <th scope="row"><?php esc_html_e('رنگ شعار سایت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_site_tagline_color" value="<?php echo esc_attr($site_tagline_color); ?>" class="k-color-picker"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('سایز شعار سایت (px)', 'khoshtip-kocholo'); ?></th>
                <td><input type="number" name="k_site_tagline_size" value="<?php echo esc_attr($site_tagline_size); ?>" min="8" max="24" style="width: 80px;"> px</td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('رنگ شروع گرادینت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_header_gradient_start" value="<?php echo esc_attr($header_gradient_start); ?>" class="k-color-picker"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('رنگ پایان گرادینت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_header_gradient_end" value="<?php echo esc_attr($header_gradient_end); ?>" class="k-color-picker"></td>
            </tr>
        </table>

        <h3><?php esc_html_e('لینک‌های منوی دسکتاپ', 'khoshtip-kocholo'); ?></h3>
        <div id="nav-links-repeater" class="k-repeater">
            <?php foreach ($nav_links as $index => $link) : ?>
                <div class="k-repeater-item">
                    <input type="text" name="k_nav_links[<?php echo $index; ?>][text]"
                           value="<?php echo esc_attr($link['text'] ?? ''); ?>" placeholder="متن لینک" style="width: 30%;">
                    <!-- Use esc_url to preserve encoded characters in URLs -->
                    <input type="text" name="k_nav_links[<?php echo $index; ?>][url]"
                           value="<?php echo esc_url_raw($link['url'] ?? ''); ?>" placeholder="آدرس URL" style="width: 50%;">
                    <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()">حذف</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary" id="add-nav-link"><?php esc_html_e('افزودن لینک', 'khoshtip-kocholo'); ?></button>

        <h3><?php esc_html_e('لینک‌های منوی موبایل', 'khoshtip-kocholo'); ?></h3>
        <div id="mobile-menu-links-repeater" class="k-repeater">
            <?php foreach ($mobile_menu_links as $index => $link) : ?>
                <div class="k-repeater-item">
                    <input type="text" name="k_mobile_menu_links[<?php echo $index; ?>][text]"
                           value="<?php echo esc_attr($link['text'] ?? ''); ?>" placeholder="متن لینک" style="width: 30%;">
                    <!-- Use esc_attr to preserve encoded characters in URLs -->
                    <input type="text" name="k_mobile_menu_links[<?php echo $index; ?>][url]"
                           value="<?php echo esc_attr($link['url'] ?? ''); ?>" placeholder="آدرس URL" style="width: 50%;">
                    <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()">حذف</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary" id="add-mobile-menu-link"><?php esc_html_e('افزودن لینک', 'khoshtip-kocholo'); ?></button>
        <?php
    }

    private function save_header_tab($data) {
        update_option('k_site_title', sanitize_text_field($data['k_site_title'] ?? ''));
        update_option('k_site_tagline', sanitize_text_field($data['k_site_tagline'] ?? ''));
        // ذخیره رنگ و سایز فونت عنوان و شعار
        update_option('k_site_title_color', sanitize_hex_color($data['k_site_title_color'] ?? '#ffffff'));
        update_option('k_site_title_size', absint($data['k_site_title_size'] ?? 24));
        update_option('k_site_tagline_color', sanitize_hex_color($data['k_site_tagline_color'] ?? 'rgba(255,255,255,0.9)'));
        update_option('k_site_tagline_size', absint($data['k_site_tagline_size'] ?? 12));
        update_option('k_header_gradient_start', sanitize_hex_color($data['k_header_gradient_start'] ?? '#ff6b9d'));
        update_option('k_header_gradient_end', sanitize_hex_color($data['k_header_gradient_end'] ?? '#ffc3d7'));
        update_option('k_nav_links', KK_Helper::sanitize_repeater_array($data['k_nav_links'] ?? []));
        update_option('k_mobile_menu_links', KK_Helper::sanitize_repeater_array($data['k_mobile_menu_links'] ?? []));
    }

    // ==========================================
    // STORIES TAB
    // ==========================================

    private function render_stories_tab() {
        $stories = get_option('k_stories', []);
        ?>
        <h3><?php esc_html_e('مدیریت استوری‌ها', 'khoshtip-kocholo'); ?></h3>
        <div id="stories-repeater" class="k-repeater">
            <?php foreach ($stories as $index => $story) : ?>
                <?php $this->render_story_item($index, $story); ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary" id="add-story"><?php esc_html_e('افزودن استوری', 'khoshtip-kocholo'); ?></button>
        <?php
    }

    private function render_story_item($index, $story) {
        $media_items = $story['media'] ?? [];
        ?>
        <div class="k-repeater-item story-item">
            <h4><?php printf(__('استوری %d', 'khoshtip-kocholo'), $index + 1); ?></h4>

            <label><?php esc_html_e('نام کاربری:', 'khoshtip-kocholo'); ?></label>
            <input type="text" name="k_stories[<?php echo $index; ?>][username]"
                   value="<?php echo esc_attr($story['username'] ?? ''); ?>" placeholder="نام کاربری">

            <label><?php esc_html_e('آواتار:', 'khoshtip-kocholo'); ?></label>
            <div class="avatar-upload">
                <input type="text" name="k_stories[<?php echo $index; ?>][avatar]"
                       value="<?php echo esc_url($story['avatar'] ?? ''); ?>" class="story-avatar-input" placeholder="آدرس آواتار">
                <button type="button" class="button upload-btn upload-avatar"><?php esc_html_e('انتخاب', 'khoshtip-kocholo'); ?></button>
                <?php if (!empty($story['avatar'])) : ?>
                    <img src="<?php echo esc_url($story['avatar']); ?>" class="avatar-preview" style="max-width: 60px; border-radius: 50%;">
                <?php endif; ?>
            </div>

            <label><?php esc_html_e('رسانه‌ها:', 'khoshtip-kocholo'); ?></label>
            <div class="story-media-list" id="story-media-list-<?php echo $index; ?>">
                <?php foreach ($media_items as $media_index => $media) : ?>
                    <div class="story-media-item">
                        <select name="k_stories[<?php echo $index; ?>][media][<?php echo $media_index; ?>][type]">
                            <option value="image" <?php selected($media['type'] ?? '', 'image'); ?>>تصویر</option>
                            <option value="video" <?php selected($media['type'] ?? '', 'video'); ?>>ویدیو</option>
                        </select>
                        <input type="text" name="k_stories[<?php echo $index; ?>][media][<?php echo $media_index; ?>][url]"
                               value="<?php echo esc_url($media['url'] ?? ''); ?>" placeholder="آدرس رسانه">
                        <input type="text" name="k_stories[<?php echo $index; ?>][media][<?php echo $media_index; ?>][link]"
                               value="<?php echo esc_url($media['link'] ?? ''); ?>" placeholder="لینک (اختیاری)">
                        <button type="button" class="button k-remove-btn"><?php esc_html_e('حذف', 'khoshtip-kocholo'); ?></button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button add-story-media" data-story="<?php echo $index; ?>"><?php esc_html_e('افزودن رسانه', 'khoshtip-kocholo'); ?></button>

            <label><?php esc_html_e('لینک دکمه:', 'khoshtip-kocholo'); ?></label>

            <input type="text" name="k_stories[<?php echo $index; ?>][button_url]"
                   value="<?php echo esc_attr($story['button_url'] ?? ''); ?>" placeholder="لینک دکمه">

            <!-- اضافه کردن فیلد متن دکمه -->
            <label><?php esc_html_e('متن دکمه:', 'khoshtip-kocholo'); ?></label>
            <input type="text" name="k_stories[<?php echo $index; ?>][button_text]"
                   value="<?php echo esc_attr($story['button_text'] ?? 'مشاهده کالکشن'); ?>" placeholder="متن دکمه">

            <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()"><?php esc_html_e('حذف استوری', 'khoshtip-kocholo'); ?></button>
        </div>
        <?php
    }

    private function save_stories_tab($data) {
        update_option('k_stories', KK_Helper::sanitize_repeater_array($data['k_stories'] ?? []));
    }

    // ==========================================
    // HERO TAB
    // ==========================================

    private function render_hero_tab() {
        $slides = get_option('k_hero_slides', []);
        ?>
        <h3><?php esc_html_e('اسلایدهای بنر ا��لی', 'khoshtip-kocholo'); ?></h3>
        <div id="hero-repeater" class="k-repeater">
            <?php foreach ($slides as $index => $slide) : ?>
                <?php $this->render_hero_item($index, $slide); ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary" id="add-hero-slide"><?php esc_html_e('افزودن اسلاید', 'khoshtip-kocholo'); ?></button>
        <?php
    }

    private function render_hero_item($index, $slide) {
        ?>
        <div class="k-repeater-item hero-item">
            <h4><?php printf(__('اسلاید %d', 'khoshtip-kocholo'), $index + 1); ?></h4>

            <div class="image-upload-group">
                <label><?php esc_html_e('تصویر دسکتاپ:', 'khoshtip-kocholo'); ?></label>
                <input type="hidden" name="k_hero_slides[<?php echo $index; ?>][bg_image]"
                       value="<?php echo esc_url($slide['bg_image'] ?? ''); ?>" class="hero-bg-input">
                <button type="button" class="button upload-hero-bg"><?php esc_html_e('انتخاب تصویر', 'khoshtip-kocholo'); ?></button>
                <?php if (!empty($slide['bg_image'])) : ?>
                    <img src="<?php echo esc_url($slide['bg_image']); ?>" class="k-image-preview">
                <?php endif; ?>
            </div>

            <div class="image-upload-group">
                <label><?php esc_html_e('تصویر موبایل:', 'khoshtip-kocholo'); ?></label>
                <input type="hidden" name="k_hero_slides[<?php echo $index; ?>][mobile_image]"
                       value="<?php echo esc_url($slide['mobile_image'] ?? ''); ?>" class="hero-mobile-input">
                <button type="button" class="button upload-hero-mobile"><?php esc_html_e('انتخاب تصویر', 'khoshtip-kocholo'); ?></button>
                <?php if (!empty($slide['mobile_image'])) : ?>
                    <img src="<?php echo esc_url($slide['mobile_image']); ?>" class="k-image-preview">
                <?php endif; ?>
            </div>

            <input type="text" name="k_hero_slides[<?php echo $index; ?>][title]"
                   value="<?php echo esc_attr($slide['title'] ?? ''); ?>" placeholder="عنوان">
            <textarea name="k_hero_slides[<?php echo $index; ?>][description]"
                      placeholder="توضیحات" rows="3"><?php echo esc_textarea($slide['description'] ?? ''); ?></textarea>
            <input type="text" name="k_hero_slides[<?php echo $index; ?>][button_text]"
                   value="<?php echo esc_attr($slide['button_text'] ?? ''); ?>" placeholder="متن دکمه">
            <input type="text" name="k_hero_slides[<?php echo $index; ?>][button_url]"
                   value="<?php echo esc_url($slide['button_url'] ?? ''); ?>" placeholder="لینک دکمه">

            <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()"><?php esc_html_e('حذف اسلاید', 'khoshtip-kocholo'); ?></button>
        </div>
        <?php
    }

    private function save_hero_tab($data) {
        update_option('k_hero_slides', KK_Helper::sanitize_repeater_array($data['k_hero_slides'] ?? []));
    }

    // ==========================================
    // SALES TAB
    // ==========================================

    private function render_sales_tab() {
        $sale_hours = get_option('k_sale_hours', 0);
        $sale_minutes = get_option('k_sale_minutes', 0);
        $sale_end_timestamp = get_option('k_sale_end_timestamp', 0);
        $time_remaining = $sale_end_timestamp > 0 ? $sale_end_timestamp - time() : 0;

        $product_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));
        ?>
        <div class="k-sales-tab">
            <h3>مدیریت حراج</h3>

            <div class="k-sale-config-section" style="background: #f0f0f1; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h4 style="margin-top: 0;">⏰ تنظیمات حراجی</h4>
                <table class="form-table">
                    <tr>
                        <th scope="row">مدت زمان حراج</th>
                        <td>
                            <div style="display: flex; gap: 15px; align-items: center;">
                                <div>
                                    <label>ساعت:</label>
                                    <input type="number" id="k-sale-hours" value="<?php echo esc_attr($sale_hours); ?>" min="0" max="720" style="width: 80px;" />
                                </div>
                                <div>
                                    <label>دقیقه:</label>
                                    <input type="number" id="k-sale-minutes" value="<?php echo esc_attr($sale_minutes); ?>" min="0" max="59" style="width: 80px;" />
                                </div>
                            </div>
                            <?php if ($time_remaining > 0) : ?>
                                <p class="description" style="color: #0073aa; font-weight: 600; margin-top: 10px;">
                                    ⚡ تایمر فعال - زمان باقی‌مانده: <?php echo human_time_diff(time(), $sale_end_timestamp); ?>
                                </p>
                            <?php else: ?>
                                <p class="description" style="color: #666; margin-top: 10px;">
                                    تایمر فعال نیست.
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">فیلتر دسته‌بندی</th>
                        <td>
                            <select id="k-category-filter" style="width: 300px;">
                                <option value="0">همه محصولات</option>
                                <?php foreach ($product_categories as $category) : ?>
                                    <option value="<?php echo esc_attr($category->term_id); ?>">
                                        <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">انتخاب محصولات</th>
                        <td>
                            <div id="k-products-list" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f0f0f1; border-radius: 8px;">
                                <p style="color: #666;">در حال بارگذاری محصولات...</p>
                            </div>
                            <div style="margin-top: 10px;">
                                <label>
                                    <input type="checkbox" id="k-select-all-products" />
                                    انتخاب همه محصولات
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">درصد تخفیف</th>
                        <td>
                            <input type="number" id="k-discount-percent" min="1" max="99" value="10" style="width: 100px;" /> %
                            <p class="description">درصد تخفیف از قیمت اصلی محصولات (1 تا 99 درصد)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"></th>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <button type="button" class="button button-primary button-large" id="k-start-sale">
                                    🚀 شروع حراجی (اعمال تایمر + تخفیف‌ها)
                                </button>
                                <?php if ($time_remaining > 0) : ?>
                                <button type="button" class="button button-secondary" id="k-cancel-sale" style="background: #d63638; color: #fff; border-color: #d63638;">
                                    ❌ لغو حراجی و حذف تمام تخفیف‌ها
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="k-active-discounts" style="background: #fff; padding: 20px; border: 1px solid #c3c4c7; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="margin: 0;">📋 لیست حراجی‌های فعال</h4>
                    <button type="button" class="button button-secondary" id="k-remove-all-discounts" style="background: #d63638; color: #fff; border-color: #d63638;">
                        🗑️ حذف همه تخفیف‌ها
                    </button>
                </div>
                <div id="k-discounted-products-list">
                    <?php $this->render_discounted_products_table(); ?>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            loadProductsByCategory(0);

            $('#k-category-filter').on('change', function() {
                const categoryId = $(this).val();
                loadProductsByCategory(categoryId);
            });

            function loadProductsByCategory(categoryId) {
                const $productsList = $('#k-products-list');

                $productsList.html('<p style="color: #666;">در حال بارگذاری محصولات...</p>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_get_products_by_category',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>',
                        category_id: categoryId
                    },
                    success: function(response) {
                        if (response.success) {
                            let html = '';

                            if (response.data.products.length === 0) {
                                html = '<p style="color: #666;">هیچ محصولی یافت نشد.</p>';
                            } else {
                                response.data.products.forEach(function(product) {
                                    const discountBadge = product.has_discount ? '<span style="color: #d63638; font-weight: 600;"> (در حال حاضر تخفیف دارد)</span>' : '';
                                    const productTypeInfo = product.type === 'variable' ? ' (متغیر)' : '';
                                    html += `
                                        <div style="padding: 8px; border-bottom: 1px solid #ddd;">
                                            <label style="display: flex; align-items: center; cursor: pointer;">
                                                <input type="checkbox" class="k-product-checkbox" value="${product.id}" style="margin-left: 8px;" />
                                                <span>${product.name}${productTypeInfo} - ${product.regular_price}${discountBadge}</span>
                                            </label>
                                        </div>
                                    `;
                                });
                            }

                            $productsList.html(html);
                        }
                    },
                    error: function() {
                        $productsList.html('<p style="color: #d63638;">خطا در بارگذاری محصولات.</p>');
                    }
                });
            }

            $('#k-select-all-products').on('change', function() {
                $('.k-product-checkbox').prop('checked', $(this).is(':checked'));
            });

            $('#k-start-sale').on('click', function() {
                const selectedProducts = [];
                $('.k-product-checkbox:checked').each(function() {
                    selectedProducts.push($(this).val());
                });

                const discountPercent = $('#k-discount-percent').val();
                const saleHours = $('#k-sale-hours').val();
                const saleMinutes = $('#k-sale-minutes').val();

                if (selectedProducts.length === 0) {
                    alert('لطفاً حداقل یک محصول انتخاب کنید.');
                    return;
                }

                if (!discountPercent || discountPercent < 1 || discountPercent > 99) {
                    alert('لطفاً درصد تخفیف معتبر (1 تا 99) و���رد کنید.');
                    return;
                }

                if (!saleHours && !saleMinutes) {
                    alert('لطفاً مدت زمان حراج را وارد کنید.');
                    return;
                }

                if (!confirm('آیا از شروع حراجی اطمینان دارید؟ تایمر و تخفیف‌ها همزمان اعمال می‌شوند.')) {
                    return;
                }

                const $button = $(this);
                $button.prop('disabled', true).text('در حال اعمال حراجی...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_start_sale',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>',
                        product_ids: selectedProducts,
                        discount_percent: discountPercent,
                        sale_hours: saleHours,
                        sale_minutes: saleMinutes
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert(response.data.message || 'خطایی رخ داد.');
                        }
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('🚀 شروع حراجی (اعمال تایمر + تخفیف‌ها)');
                    }
                });
            });

            $('#k-cancel-sale').on('click', function() {
                if (!confirm('آیا از لغو حراجی و حذف تمام تخفیف‌ها اطمینان دارید؟')) {
                    return;
                }

                const $button = $(this);
                $button.prop('disabled', true).text('در حال لغو...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_cancel_sale',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert(response.data.message || 'خطایی رخ داد.');
                        }
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('❌ لغو حراجی و حذف تمام تخفیف‌ها');
                    }
                });
            });

            $('#k-remove-all-discounts').on('click', function() {
                if (!confirm('آیا از حذف تمام تخفیف‌ها اطمینان دارید؟ (تایمر همچنان فعال می‌ماند)')) {
                    return;
                }

                const $button = $(this);
                $button.prop('disabled', true).text('در حال حذف...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_remove_all_discounts',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            updateDiscountedProductsList(response.data.discounted_products);
                            loadProductsByCategory($('#k-category-filter').val());
                        } else {
                            alert(response.data.message || 'خطایی رخ داد.');
                        }
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('🗑️ حذف همه تخفیف‌ها');
                    }
                });
            });

            $(document).on('click', '.k-remove-discount', function() {
                if (!confirm('آیا از حذف این تخفیف اطمینان دارید؟')) {
                    return;
                }

                const productId = $(this).data('product-id');
                const $button = $(this);

                $button.prop('disabled', true).text('در حال حذف...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_remove_discount',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>',
                        product_id: productId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            updateDiscountedProductsList(response.data.discounted_products);
                            loadProductsByCategory($('#k-category-filter').val());
                        } else {
                            alert(response.data.message || 'خطایی رخ داد.');
                        }
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('حذف');
                    }
                });
            });

            function updateDiscountedProductsList(products) {
                let html = '';

                if (products.length === 0) {
                    html = '<p style="color: #666; text-align: center; padding: 20px;">هیچ محصولی با تخفیف وجود ندارد.</p>';
                } else {
                    html = `
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>نام محصول</th>
                                    <th>قیمت اصلی</th>
                                    <th>قیمت با تخفیف</th>
                                    <th>درصد تخفیف</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    products.forEach(function(product) {
                        html += `
                            <tr>
                                <td>${product.name}</td>
                                <td>${product.regular_price}</td>
                                <td style="color: #d63638; font-weight: 600;">${product.sale_price}</td>
                                <td><span style="background: #d63638; color: white; padding: 3px 8px; border-radius: 3px;">${product.discount_percent}%</span></td>
                                <td>
                                    <button type="button" class="button button-small k-remove-discount" data-product-id="${product.id}">
                                        حذف
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    html += `
                            </tbody>
                        </table>
                    `;
                }

                $('#k-discounted-products-list').html(html);
            }
        });
        </script>
        <?php
    }

    private function render_discounted_products_table() {
        $discounted_products = $this->get_discounted_products_list();

        if (empty($discounted_products)) {
            echo '<p style="color: #666; text-align: center; padding: 20px;">هیچ محصولی با تخفیف وجود ندارد.</p>';
            return;
        }
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>نام محصول</th>
                    <th>قیمت اصلی</th>
                    <th>قیمت با تخفیف</th>
                    <th>درصد تخفیف</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($discounted_products as $product) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($product['name']); ?></strong></td>
                        <td><?php echo $product['regular_price']; ?></td>
                        <td><span style="color: #d63638; font-weight: 600;"><?php echo $product['sale_price']; ?></span></td>
                        <td><span style="background: #d63638; color: white; padding: 3px 8px; border-radius: 3px;"><?php echo $product['discount_percent']; ?>%</span></td>
                        <td>
                            <button type="button" class="button button-small k-remove-discount" data-product-id="<?php echo esc_attr($product['id']); ?>">
                                حذف
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function get_discounted_products_list() {
        // Cache 30 دقیقه‌ای
        $cache_key = 'k_admin_discounted_products_list';
        $cached = KK_Cache::get($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $discounted_products_ids = get_option('k_discounted_products', array());

        if (empty($discounted_products_ids)) {
            KK_Cache::set($cache_key, [], 30 * MINUTE_IN_SECONDS);
            return [];
        }

        // Taking all products at once with include
        $products = wc_get_products([
            'include' => $discounted_products_ids,
            'limit' => -1,
            'status' => 'publish'
        ]);

        // Building a map for quick access
        $products_map = [];
        foreach ($products as $product) {
            $products_map[$product->get_id()] = $product;
        }

        $product_list = array();

        foreach ($discounted_products_ids as $product_id) {
            if (!isset($products_map[$product_id])) {
                continue;
            }

            $product = $products_map[$product_id];
            $product_type = $product->get_type();
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            $discount_percent = 0;
            $display_regular_price = wc_price($regular_price);
            $display_sale_price = '';

            if ($product_type === 'variable') {
                // Use true for cache for WooCommerce's internal cache
                $min_reg_price = $product->get_variation_regular_price('min');
                $max_reg_price = $product->get_variation_regular_price('max');

                $display_regular_price = ($min_reg_price == $max_reg_price) ? wc_price($min_reg_price) : wc_format_price_range($min_reg_price, $max_reg_price);

                $lowest_sale_price = PHP_INT_MAX;
                // Use get_post_meta instead of fully loading variations
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

            $product_list[] = array(
                'id' => $product_id,
                'name' => $product->get_name(),
                'regular_price' => $display_regular_price,
                'sale_price' => $display_sale_price,
                'discount_percent' => $discount_percent
            );
        }

        // Save to cache for 30 minutes
        KK_Cache::set($cache_key, $product_list, 30 * MINUTE_IN_SECONDS);

        return $product_list;
    }

    private function save_sales_tab($data) {
        // Sales are managed via AJAX actions
    }

    // ==========================================
    // LATEST PRODUCTS TAB
    // ==========================================

    private function render_latest_tab() {
        $products = get_option('k_latest_products', []);
        ?>
        <h3><?php esc_html_e('جدیدترین محصولات', 'khoshtip-kocholo'); ?></h3>
        <p class="description"><?php esc_html_e('به طور پیش‌فرض، 20 محصول آخر نمایش داده می‌شود.', 'khoshtip-kocholo'); ?></p>

        <table class="form-table">
            <tr>
                <th><?php esc_html_e('محصولات', 'khoshtip-kocholo'); ?></th>
                <td>
                    <?php $this->render_product_selector('k_latest_products', $products); ?>
                </td>
            </tr>
        </table>
        <?php
    }

    private function render_product_selector($name, $selected_ids) {
        $all_products = wc_get_products(['limit' => -1, 'status' => 'publish']);
        ?>
        <select name="<?php echo esc_attr($name); ?>[]" multiple class="k-product-selector" style="width: 100%;">
            <?php foreach ($all_products as $product) : ?>
                <option value="<?php echo $product->get_id(); ?>" <?php echo in_array($product->get_id(), $selected_ids) ? 'selected' : ''; ?>>
                    <?php echo esc_html($product->get_name()); ?> (#<?php echo $product->get_id(); ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    private function save_latest_tab($data) {
        update_option('k_latest_products', KK_Helper::sanitize_int_array($data['k_latest_products'] ?? []));
    }

    // ==========================================
    // CATEGORIES TAB
    // ==========================================

    private function render_categories_tab() {
        $categories = get_option('k_categories', []);
        ?>
        <h3><?php esc_html_e('مدیریت دسته‌بندی‌ها', 'khoshtip-kocholo'); ?></h3>

        <div id="categories-repeater" class="k-repeater">
            <?php foreach ($categories as $index => $category) : ?>
                <?php $this->render_category_item($index, $category); ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary" id="add-category"><?php esc_html_e('افزودن دسته‌بندی', 'khoshtip-kocholo'); ?></button>
        <?php
    }

    private function render_category_item($index, $category) {
        ?>
        <div class="k-repeater-item category-item">
            <h4><?php printf(__('دسته‌بندی %d', 'khoshtip-kocholo'), $index + 1); ?></h4>

            <input type="text" name="k_categories[<?php echo $index; ?>][name]"
                   value="<?php echo esc_attr($category['name'] ?? ''); ?>" placeholder="نام دسته‌بندی">
            <input type="text" name="k_categories[<?php echo $index; ?>][link]"
                   value="<?php echo esc_attr($category['link'] ?? ''); ?>" placeholder="لینک">

            <div class="image-upload-group">
                <label><?php esc_html_e('تصویر دکمه:', 'khoshtip-kocholo'); ?></label>
                <input type="hidden" name="k_categories[<?php echo $index; ?>][image]"
                       value="<?php echo esc_url($category['image'] ?? ''); ?>" class="category-image-input">
                <button type="button" class="button upload-category-image"><?php esc_html_e('انتخاب', 'khoshtip-kocholo'); ?></button>
                <?php if (!empty($category['image'])) : ?>
                    <img src="<?php echo esc_url($category['image']); ?>" class="k-image-preview">
                <?php endif; ?>
            </div>

            <div class="image-upload-group">
                <label><?php esc_html_e('تصویر مودال:', 'khoshtip-kocholo'); ?></label>
                <input type="hidden" name="k_categories[<?php echo $index; ?>][modal_image]"
                       value="<?php echo esc_url($category['modal_image'] ?? ''); ?>" class="category-modal-image-input">
                <button type="button" class="button upload-category-modal-image"><?php esc_html_e('انتخاب', 'khoshtip-kocholo'); ?></button>
                <?php if (!empty($category['modal_image'])) : ?>
                    <img src="<?php echo esc_url($category['modal_image']); ?>" class="k-image-preview">
                <?php endif; ?>
            </div>

            <label><?php esc_html_e('توضیحات:', 'khoshtip-kocholo'); ?></label>
            <textarea name="k_categories[<?php echo $index; ?>][description]" rows="3"><?php echo esc_textarea($category['description'] ?? ''); ?></textarea>

            <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()"><?php esc_html_e('حذف', 'khoshtip-kocholo'); ?></button>
        </div>
        <?php
    }

    private function save_categories_tab($data) {
        update_option('k_categories', KK_Helper::sanitize_repeater_array($data['k_categories'] ?? []));
    }

    // ==========================================
    // ACCORDION TAB
    // ==========================================

    private function render_accordion_tab() {
        $sections = [
            'girls' => [
                'title' => get_option('k_accordion_girls_title', 'محصولات دخترانه'),
                'category' => KK_Helper::ensure_array(get_option('k_girls_category', [])),
                'products' => get_option('k_girls_products', []),
                'gradient_start' => get_option('k_girls_gradient_start', '#ff6b9d'),
                'gradient_end' => get_option('k_girls_gradient_end', '#ffc3d7'),
                'button_bg' => get_option('k_girls_button_bg', '#ff6b9d'),
                'button_text' => get_option('k_girls_button_text', '#ffffff'),
                'icon' => get_option('k_girls_icon', ''),
                'layout' => get_option('k_girls_layout', 'scroll'),
                'default_open' => get_option('k_girls_default_open', '0'),
                'scroll_limit' => get_option('k_girls_scroll_limit', 20),
                'grid_limit' => get_option('k_girls_grid_limit', 10),
                'view_all_text' => get_option('k_girls_view_all_text', 'نمایش همه ←'),
                'order' => get_option('k_girls_order', 1),
            ],
            'boys' => [
                'title' => get_option('k_accordion_boys_title', 'محصولات پسرانه'),
                'category' => KK_Helper::ensure_array(get_option('k_boys_category', [])),
                'products' => get_option('k_boys_products', []),
                'gradient_start' => get_option('k_boys_gradient_start', '#4dabf7'),
                'gradient_end' => get_option('k_boys_gradient_end', '#d0ebff'),
                'button_bg' => get_option('k_boys_button_bg', '#4dabf7'),
                'button_text' => get_option('k_boys_button_text', '#ffffff'),
                'icon' => get_option('k_boys_icon', ''),
                'layout' => get_option('k_boys_layout', 'scroll'),
                'default_open' => get_option('k_boys_default_open', '0'),
                'scroll_limit' => get_option('k_boys_scroll_limit', 20),
                'grid_limit' => get_option('k_boys_grid_limit', 10),
                'view_all_text' => get_option('k_boys_view_all_text', 'نمایش همه ←'),
                'order' => get_option('k_boys_order', 2),
            ],
            'sport' => [
                'title' => get_option('k_accordion_sport_title', 'محصولات ورزشی'),
                'category' => KK_Helper::ensure_array(get_option('k_sport_category', [])),
                'products' => get_option('k_sport_products', []),
                'gradient_start' => get_option('k_sport_gradient_start', '#51cf66'),
                'gradient_end' => get_option('k_sport_gradient_end', '#d3f9d8'),
                'button_bg' => get_option('k_sport_button_bg', '#51cf66'),
                'button_text' => get_option('k_sport_button_text', '#ffffff'),
                'icon' => get_option('k_sport_icon', ''),
                'layout' => get_option('k_sport_layout', 'scroll'),
                'default_open' => get_option('k_sport_default_open', '0'),
                'scroll_limit' => get_option('k_sport_scroll_limit', 20),
                'grid_limit' => get_option('k_sport_grid_limit', 10),
                'view_all_text' => get_option('k_sport_view_all_text', 'نمایش همه ←'),
                'order' => get_option('k_sport_order', 3),
            ],
        ];

        $latest_section = [
            'title' => get_option('k_latest_title', 'همه محصولات'),
            'view_all_text' => get_option('k_latest_view_all_text', 'نمایش همه ←'),
            'gradient_start' => get_option('k_latest_gradient_start', '#f093fb'),
            'gradient_end' => get_option('k_latest_gradient_end', '#f5576c'),
            'button_bg' => get_option('k_latest_button_bg', 'rgba(255, 255, 255, 0.3)'),
            'button_text' => get_option('k_latest_button_text', '#ffffff'),
            'icon' => get_option('k_latest_icon', '✨'),
            'layout' => get_option('k_latest_layout', 'scroll'),
            'default_open' => get_option('k_latest_default_open', '1'),
            'scroll_limit' => get_option('k_latest_scroll_limit', 20),
            'grid_limit' => get_option('k_latest_grid_limit', 10),
            'order' => get_option('k_latest_order', 0),
            'categories' => get_option('k_latest_category', []),
        ];

        $extra_accordions = get_option('k_extra_accordions', []);
        $wc_categories = KK_Helper::get_product_categories();
        $all_products = wc_get_products(['limit' => -1, 'status' => 'publish']);

        ?>
        <h3><?php esc_html_e('محصولات آکاردئونی', 'khoshtip-kocholo'); ?></h3>
        <p style="background: #fff3cd; border: 2px solid #ffc107; padding: 12px; border-radius: 8px; color: #856404;">
            <strong>📌 نکته مهم:</strong> برای تغییر ترتیب نمایش آکاردئون‌ها، از فیلد "ترتیب نمایش" استفاده کنید. عدد کوچکتر = بال��تر در صفحه
        </p>

        <!-- Added complete settings section for "All Products" fixed bar -->
        <div class="settings-section" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 3px solid #f093fb;">
            <h4 style="color: #fff; margin: 0 0 15px 0; display: flex; justify-content: space-between; align-items: center;">
                <span><?php esc_html_e('نوار همه محصولات', 'khoshtip-kocholo'); ?></span>
                <span style="background: rgba(255,255,255,0.3); padding: 5px 12px; border-radius: 6px; font-size: 14px;">
                    ترتیب: <?php echo esc_html($latest_section['order']); ?>
                </span>
            </h4>
            <table class="form-table" style="background: rgba(255,255,255,0.95); border-radius: 8px;">
                <tr>
                    <th><?php esc_html_e('عنوان', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_latest_title" value="<?php echo esc_attr($latest_section['title']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('دسته‌بندی', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <select name="k_latest_category[]" multiple size="5">
                            <?php foreach ($wc_categories as $cat) : ?>
                                <option value="<?php echo $cat->term_id; ?>" <?php echo in_array($cat->term_id, $latest_section['categories']) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($cat->name); ?> (<?php echo $cat->count; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('آیکون', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="text" name="k_latest_icon" value="<?php echo esc_attr($latest_section['icon']); ?>" class="regular-text" placeholder="ایموجی یا URL تصویر">
                        <button type="button" class="button upload-icon"><?php esc_html_e('انتخاب تصویر', 'khoshtip-kocholo'); ?></button>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('رنگ شروع گرادینت نوار', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_latest_gradient_start" value="<?php echo esc_attr($latest_section['gradient_start']); ?>" class="k-color-picker"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('رنگ پایان گرادینت نوار', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_latest_gradient_end" value="<?php echo esc_attr($latest_section['gradient_end']); ?>" class="k-color-picker"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('رنگ پس‌زمینه دکمه "نمایش همه"', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_latest_button_bg" value="<?php echo esc_attr($latest_section['button_bg']); ?>" class="k-color-picker"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('رنگ متن دکمه "نمایش همه"', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_latest_button_text" value="<?php echo esc_attr($latest_section['button_text']); ?>" class="k-color-picker"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('نحوه نمایش', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <select name="k_latest_layout">
                            <option value="scroll" <?php selected($latest_section['layout'], 'scroll'); ?>><?php esc_html_e('اسکرول افقی', 'khoshtip-kocholo'); ?></option>
                            <option value="grid" <?php selected($latest_section['layout'], 'grid'); ?>><?php esc_html_e('گرید عمودی', 'khoshtip-kocholo'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('باز بودن پیش‌فرض', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="k_latest_default_open" value="1" <?php checked($latest_section['default_open'], '1'); ?>>
                            <?php esc_html_e('این بخش هنگام ورود باز باشد', 'khoshtip-kocholo'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('محدودیت تعداد نمایش', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <label><?php esc_html_e('حالت اسکرول:', 'khoshtip-kocholo'); ?> <input type="number" name="k_latest_scroll_limit" value="<?php echo esc_attr($latest_section['scroll_limit']); ?>" min="1" max="100" style="width: 60px;"></label>
                        <label style="margin-right: 15px;"><?php esc_html_e('حالت گرید:', 'khoshtip-kocholo'); ?> <input type="number" name="k_latest_grid_limit" value="<?php echo esc_attr($latest_section['grid_limit']); ?>" min="1" max="50" style="width: 60px;"></label>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('متن دکمه نمایش همه', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_latest_view_all_text" value="<?php echo esc_attr($latest_section['view_all_text']); ?>" class="regular-text"></td>
                </tr>
                <!-- اضافه کردن فیلد انتخاب دسته‌بندی -->
                <tr>
                    <th><?php esc_html_e('دسته‌بندی‌های نمایش داده شوند', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <select name="k_latest_category[]" multiple="multiple" style="width: 100%; min-height: 120px;">
                            <?php
                            $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
                            $selected = KK_Helper::ensure_array($latest_section['categories']);
                            foreach ($categories as $cat) :
                                $is_selected = in_array($cat->term_id, $selected, true);
                            ?>
                                <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($is_selected); ?>>
                                    <?php echo esc_html($cat->name); ?> (<?php echo intval($cat->count); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">برای نمایش محصولات از دسته‌بندی‌های مختلف، چند دسته را انتخاب کنید. اگر خالی گذاشتید، تمام محصولات نمایش داده خواهند شد.</p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('ترتیب نمایش', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="number" name="k_latest_order" value="<?php echo esc_attr($latest_section['order']); ?>" min="0" max="999" style="width: 80px;">
                        <p class="description">عدد کوچکتر = بالاتر در صفحه (0 = اول)</p>
                    </td>
                </tr>
            </table>
        </div>

        <?php foreach ($sections as $key => $section) : ?>
            <?php $this->render_accordion_section($key, $section, $wc_categories, $all_products); ?>
        <?php endforeach; ?>

        <h3><?php esc_html_e('بخش‌های اضافی', 'khoshtip-kocholo'); ?></h3>
        <div id="extra-accordions-container">
            <?php foreach ($extra_accordions as $id => $accordion) : ?>
                <?php $this->render_extra_accordion_item($id, $accordion, $wc_categories); ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary" id="add-extra-accordion"><?php esc_html_e('افزودن بخش جدید', 'khoshtip-kocholo'); ?></button>
        <?php
    }

    private function render_accordion_section($key, $section, $categories, $products) {
        $labels = [
            'girls' => __('بخش اول (دخترانه)', 'khoshtip-kocholo'),
            'boys' => __('بخش دوم (پسرانه)', 'khoshtip-kocholo'),
            'sport' => __('بخش سوم (ورزشی)', 'khoshtip-kocholo'),
        ];
        ?>
        <div class="settings-section accordion-section" data-section="<?php echo esc_attr($key); ?>">
            <h4 style="background: linear-gradient(135deg, <?php echo esc_attr($section['gradient_start']); ?>, <?php echo esc_attr($section['gradient_end']); ?>); color: #fff; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                <span><?php echo esc_html($labels[$key]); ?></span>
                <span style="background: rgba(255,255,255,0.3); padding: 5px 12px; border-radius: 6px; font-size: 14px;">
                    ترتیب: <?php echo esc_html($section['order']); ?>
                </span>
            </h4>

            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('عنوان', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_accordion_<?php echo $key; ?>_title" value="<?php echo esc_attr($section['title']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('دسته‌بندی', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <select name="k_<?php echo $key; ?>_category[]" multiple size="5">
                            <?php foreach ($categories as $cat) : ?>
                                <option value="<?php echo $cat->term_id; ?>" <?php echo in_array($cat->term_id, $section['category']) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($cat->name); ?> (<?php echo $cat->count; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('رنگ شروع گرادینت', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_<?php echo $key; ?>_gradient_start" value="<?php echo esc_attr($section['gradient_start']); ?>" class="k-color-picker"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('رنگ پایان گرادینت', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_<?php echo $key; ?>_gradient_end" value="<?php echo esc_attr($section['gradient_end']); ?>" class="k-color-picker"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('رنگ دکمه', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_<?php echo $key; ?>_button_bg" value="<?php echo esc_attr($section['button_bg']); ?>" class="k-color-picker"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('رنگ متن دکمه', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_<?php echo $key; ?>_button_text" value="<?php echo esc_attr($section['button_text']); ?>" class="k-color-picker"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('آیکون', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="text" name="k_<?php echo $key; ?>_icon" value="<?php echo esc_attr($section['icon']); ?>" class="regular-text" placeholder="ایموجی یا URL تصویر">
                        <button type="button" class="button upload-icon"><?php esc_html_e('انتخاب تصویر', 'khoshtip-kocholo'); ?></button>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('نحوه نمایش', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <select name="k_<?php echo $key; ?>_layout">
                            <option value="scroll" <?php selected($section['layout'], 'scroll'); ?>><?php esc_html_e('اسکرول افقی', 'khoshtip-kocholo'); ?></option>
                            <option value="grid" <?php selected($section['layout'], 'grid'); ?>><?php esc_html_e('گرید', 'khoshtip-kocholo'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('باز بودن پیش‌فرض', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="k_<?php echo $key; ?>_default_open" value="1" <?php checked($section['default_open'], '1'); ?>>
                            <?php esc_html_e('این بخش هنگام ورود باز باشد', 'khoshtip-kocholo'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('محدودیت تعداد', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <label><?php esc_html_e('اسکرول:', 'khoshtip-kocholo'); ?> <input type="number" name="k_<?php echo $key; ?>_scroll_limit" value="<?php echo esc_attr($section['scroll_limit']); ?>" min="1" max="100" style="width: 60px;"></label>
                        <label><?php esc_html_e('گرید:', 'khoshtip-kocholo'); ?> <input type="number" name="k_<?php echo $key; ?>_grid_limit" value="<?php echo esc_attr($section['grid_limit']); ?>" min="1" max="50" style="width: 60px;"></label>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('متن دکمه نمایش همه', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_<?php echo $key; ?>_view_all_text" value="<?php echo esc_attr($section['view_all_text']); ?>" class="regular-text"></td>
                </tr>
                <!-- Added order field for repositioning accordions -->
                <tr>
                    <th><?php esc_html_e('ترتیب نمایش', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="number" name="k_<?php echo $key; ?>_order" value="<?php echo esc_attr($section['order']); ?>" min="0" max="999" style="width: 80px;">
                        <p class="description">عدد کوچکتر = بالاتر در صفحه</p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    private function render_extra_accordion_item($id, $accordion, $categories) {
        ?>
        <div class="k-repeater-item extra-accordion-item" data-id="<?php echo esc_attr($id); ?>">
            <div class="extra-accordion-header">
                <h3><?php echo esc_html($accordion['title'] ?: __('بخش جدید', 'khoshtip-kocholo')); ?></h3>
                <span style="background: rgba(156,39,176,0.2); padding: 5px 12px; border-radius: 6px; font-size: 14px; color: #9c27b0; font-weight: 700;">
                    ترتیب: <?php echo esc_html($accordion['order'] ?? 999); ?>
                </span>
            </div>

            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('عنوان', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_extra_accordions[<?php echo $id; ?>][title]" value="<?php echo esc_attr($accordion['title'] ?? ''); ?>" class="regular-text" placeholder="عنوان بخش"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('دسته‌بندی', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <select name="k_extra_accordions[<?php echo $id; ?>][category][]" multiple size="5" style="min-width:300px;">
                            <?php
                            $selected_cats = KK_Helper::ensure_array($accordion['category'] ?? []);
                            foreach ($categories as $cat) : ?>
                                <option value="<?php echo $cat->term_id; ?>" <?php echo in_array($cat->term_id, $selected_cats) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($cat->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('رنگ شروع گرادینت', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_extra_accordions[<?php echo $id; ?>][gradient_start]" value="<?php echo esc_attr($accordion['gradient_start'] ?? '#9c27b0'); ?>" class="k-color-picker"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('رنگ پایان گرادینت', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_extra_accordions[<?php echo $id; ?>][gradient_end]" value="<?php echo esc_attr($accordion['gradient_end'] ?? '#e1bee7'); ?>" class="k-color-picker"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('رنگ دکمه', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_extra_accordions[<?php echo $id; ?>][button_bg]" value="<?php echo esc_attr($accordion['button_bg'] ?? '#9c27b0'); ?>" class="k-color-picker"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('رنگ متن دکمه', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_extra_accordions[<?php echo $id; ?>][button_text]" value="<?php echo esc_attr($accordion['button_text'] ?? '#ffffff'); ?>" class="k-color-picker"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('آیکون', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="text" name="k_extra_accordions[<?php echo $id; ?>][icon]" value="<?php echo esc_attr($accordion['icon'] ?? ''); ?>" class="regular-text" placeholder="ایموجی یا URL تصویر">
                        <button type="button" class="button upload-icon"><?php esc_html_e('انتخاب تصویر', 'khoshtip-kocholo'); ?></button>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('نحوه نمایش', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <select name="k_extra_accordions[<?php echo $id; ?>][layout]">
                            <option value="scroll" <?php selected($accordion['layout'] ?? 'scroll', 'scroll'); ?>><?php esc_html_e('اسکرول افقی', 'khoshtip-kocholo'); ?></option>
                            <option value="grid" <?php selected($accordion['layout'] ?? 'scroll', 'grid'); ?>><?php esc_html_e('گرید', 'khoshtip-kocholo'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('باز بودن پیش‌فرض', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="k_extra_accordions[<?php echo $id; ?>][default_open]" value="1" <?php checked($accordion['default_open'] ?? '0', '1'); ?>>
                            <?php esc_html_e('این بخش هنگام ورود باز باشد', 'khoshtip-kocholo'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('محدودیت تعداد', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <label><?php esc_html_e('اسکرول:', 'khoshtip-kocholo'); ?> <input type="number" name="k_extra_accordions[<?php echo $id; ?>][scroll_limit]" value="<?php echo esc_attr($accordion['scroll_limit'] ?? 20); ?>" min="1" max="100" style="width: 60px;"></label>
                        <label style="margin-right: 10px;"><?php esc_html_e('گرید:', 'khoshtip-kocholo'); ?> <input type="number" name="k_extra_accordions[<?php echo $id; ?>][grid_limit]" value="<?php echo esc_attr($accordion['grid_limit'] ?? 10); ?>" min="1" max="50" style="width: 60px;"></label>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('متن دکمه نمایش همه', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_extra_accordions[<?php echo $id; ?>][view_all_text]" value="<?php echo esc_attr($accordion['view_all_text'] ?? 'نمایش همه ←'); ?>" class="regular-text"></td>
                </tr>
                <!-- Added order field for extra accordions -->
                <tr>
                    <th><?php esc_html_e('ترتیب نمایش', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="number" name="k_extra_accordions[<?php echo $id; ?>][order]" value="<?php echo esc_attr($accordion['order'] ?? 999); ?>" min="0" max="999" style="width: 80px;">
                        <p class="description">عدد کوچکتر = بالاتر در صفحه</p>
                    </td>
                </tr>
            </table>

            <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()"><?php esc_html_e('حذف این بخش', 'khoshtip-kocholo'); ?></button>
        </div>
        <?php
    }

    private function save_accordion_tab($data) {
        $sections = ['girls', 'boys', 'sport'];

        // Save "View All" text for the main (all products) accordion
        update_option('k_latest_view_all_text', sanitize_text_field($data['k_latest_view_all_text'] ?? 'نمایش همه ←'));

        foreach ($sections as $key) {
            update_option("k_accordion_{$key}_title", sanitize_text_field($data["k_accordion_{$key}_title"] ?? ''));
            // Save "View All" text for each specific section
            update_option("k_{$key}_view_all_text", sanitize_text_field($data["k_{$key}_view_all_text"] ?? 'نمایش همه ←'));
            update_option("k_{$key}_category", KK_Helper::sanitize_int_array($data["k_{$key}_category"] ?? []));
            update_option("k_{$key}_products", KK_Helper::sanitize_int_array($data["k_{$key}_products"] ?? []));
            update_option("k_{$key}_gradient_start", sanitize_hex_color($data["k_{$key}_gradient_start"] ?? ''));
            update_option("k_{$key}_gradient_end", sanitize_hex_color($data["k_{$key}_gradient_end"] ?? ''));
            update_option("k_{$key}_button_bg", sanitize_hex_color($data["k_{$key}_button_bg"] ?? ''));
            update_option("k_{$key}_button_text", sanitize_hex_color($data["k_{$key}_button_text"] ?? ''));
            update_option("k_{$key}_icon", sanitize_text_field($data["k_{$key}_icon"] ?? ''));
            update_option("k_{$key}_layout", sanitize_text_field($data["k_{$key}_layout"] ?? 'scroll'));
            update_option("k_{$key}_default_open", isset($data["k_{$key}_default_open"]) ? '1' : '0');
            update_option("k_{$key}_scroll_limit", absint($data["k_{$key}_scroll_limit"] ?? 20));
            update_option("k_{$key}_grid_limit", absint($data["k_{$key}_grid_limit"] ?? 10));
            // Save order for existing sections
            update_option("k_{$key}_order", absint($data["k_{$key}_order"] ?? 1));
        }

        // Save order for the latest section
        update_option('k_latest_order', absint($data['k_latest_order'] ?? 0));
        update_option('k_latest_title', sanitize_text_field($data['k_latest_title'] ?? ''));
        update_option('k_latest_gradient_start', sanitize_hex_color($data['k_latest_gradient_start'] ?? '#f093fb'));
        update_option('k_latest_gradient_end', sanitize_hex_color($data['k_latest_gradient_end'] ?? '#f5576c'));
        update_option('k_latest_button_bg', sanitize_hex_color($data['k_latest_button_bg'] ?? 'rgba(255, 255, 255, 0.3)'));
        update_option('k_latest_button_text', sanitize_hex_color($data['k_latest_button_text'] ?? '#ffffff'));
        update_option('k_latest_icon', sanitize_text_field($data['k_latest_icon'] ?? '✨'));
        update_option('k_latest_layout', sanitize_text_field($data['k_latest_layout'] ?? 'scroll'));
        update_option('k_latest_default_open', isset($data['k_latest_default_open']) ? '1' : '0');
        update_option('k_latest_scroll_limit', absint($data['k_latest_scroll_limit'] ?? 20));
        update_option('k_latest_grid_limit', absint($data['k_latest_grid_limit'] ?? 10));
        // Save selected categories for the latest section
        update_option('k_latest_category', KK_Helper::sanitize_int_array($data['k_latest_category'] ?? []));

        // Save extra accordions
        $extra = [];
        if (isset($data['k_extra_accordions']) && is_array($data['k_extra_accordions'])) {
            foreach ($data['k_extra_accordions'] as $id => $accordion) {
                $extra[$id] = [
                    'id' => $id,
                    'title' => sanitize_text_field($accordion['title'] ?? ''),
                    'category' => KK_Helper::sanitize_int_array((array)($accordion['category'] ?? [])),
                    'gradient_start' => sanitize_hex_color($accordion['gradient_start'] ?? '#9c27b0'),
                    'gradient_end' => sanitize_hex_color($accordion['gradient_end'] ?? '#e1bee7'),
                    'button_bg' => sanitize_hex_color($accordion['button_bg'] ?? '#9c27b0'),
                    'button_text' => sanitize_hex_color($accordion['button_text'] ?? '#ffffff'),
                    'icon' => sanitize_text_field($accordion['icon'] ?? ''),
                    'layout' => sanitize_text_field($accordion['layout'] ?? 'scroll'),
                    'default_open' => isset($accordion['default_open']) ? '1' : '0',
                    'scroll_limit' => absint($accordion['scroll_limit'] ?? 20),
                    'grid_limit' => absint($accordion['grid_limit'] ?? 10),
                    'view_all_text' => sanitize_text_field($accordion['view_all_text'] ?? 'نمایش همه ←'),
                    'order' => absint($accordion['order'] ?? 999), // Save order for extra accordions
                ];
            }
        }
        update_option('k_extra_accordions', $extra);
    }

    // ==========================================
    // BLOG TAB
    // ==========================================

    private function render_blog_tab() {
        $posts = get_option('k_blog_posts', []);
        ?>
        <h3><?php esc_html_e('مدیریت بلاگ', 'khoshtip-kocholo'); ?></h3>
        <p class="description"><?php esc_html_e('به طور پیش‌فرض، آخرین پست‌ها نمایش داده می‌شوند.', 'khoshtip-kocholo'); ?></p>

        <table class="form-table">
            <tr>
                <th><?php esc_html_e('پست‌ها', 'khoshtip-kocholo'); ?></th>
                <td><?php $this->render_post_selector('k_blog_posts', $posts); ?></td>
            </tr>
        </table>
        <?php
    }

    private function render_post_selector($name, $selected_ids) {
        $posts = get_posts(['numberposts' => -1, 'post_status' => 'publish']);
        ?>
        <select name="<?php echo esc_attr($name); ?>[]" multiple style="width: 100%; min-height: 200px;">
            <?php foreach ($posts as $post) : ?>
                <option value="<?php echo $post->ID; ?>" <?php echo in_array($post->ID, $selected_ids) ? 'selected' : ''; ?>>
                    <?php echo esc_html($post->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    private function save_blog_tab($data) {
        update_option('k_blog_posts', KK_Helper::sanitize_int_array($data['k_blog_posts'] ?? []));
    }

    // ==========================================
    // FOOTER TAB
    // ==========================================

    private function render_footer_tab() {
        $site_title = get_option('k_footer_site_title', 'دنیای رنگارنگ کودکان');
        $site_description = get_option('k_footer_site_description', '');
        $useful_links = get_option('k_footer_useful_links', []);
        $customer_service_links = get_option('k_footer_customer_service_links', []);
        $contact_address = get_option('k_footer_contact_address', '');
        $contact_phone = get_option('k_footer_contact_phone', '');
        $copyright_text = get_option('k_footer_copyright', '');

        // تنظیمات رنگ فوتر
        $footer_bg_start = get_option('k_footer_bg_start', '#667eea');
        $footer_bg_end = get_option('k_footer_bg_end', '#764ba2');
        $footer_text_color = get_option('k_footer_text_color', '#ffffff');

        $bottom_bar_show_home = get_option('k_bottom_bar_show_home', '1');
        $bottom_bar_show_products = get_option('k_bottom_bar_show_products', '1');
        $bottom_bar_show_all_products = get_option('k_bottom_bar_show_all_products', '1');
        $bottom_bar_show_profile = get_option('k_bottom_bar_show_profile', '1');
        $bottom_bar_show_cart = get_option('k_bottom_bar_show_cart', '1'); // Added from updates
        $bottom_bar_show_sendcode = get_option('k_bottom_bar_show_sendcode', '1'); // Added from updates
        $bottom_bar_show_sales = get_option('k_bottom_bar_show_sales', '1'); // Added from updates
        $bottom_bar_buttons = get_option('k_bottom_bar_buttons', []);
        $bottom_bar_bg_start = get_option('k_bottom_bar_bg_start', '#ffffff');
        $bottom_bar_bg_end = get_option('k_bottom_bar_bg_end', '#f9fafb');
        $bottom_bar_text_color = get_option('k_bottom_bar_text_color', '#374151');
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('عنوان سایت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_footer_site_title" value="<?php echo esc_attr($site_title); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('توضیحات سایت', 'khoshtip-kocholo'); ?></th>
                <td><textarea name="k_footer_site_description" rows="3" class="large-text"><?php echo esc_textarea($site_description); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('لینک‌های مفید', 'khoshtip-kocholo'); ?></th>
                <td>
                    <div id="useful-links-repeater" class="k-repeater">
                        <?php foreach ($useful_links as $index => $link) : ?>
                            <div class="k-repeater-item">
                                <input type="text" name="k_footer_useful_links[<?php echo $index; ?>][text]" value="<?php echo esc_attr($link['text'] ?? ''); ?>" placeholder="متن لینک" style="width:30%;">
                                <!-- CHANGE: Changed from esc_url to esc_attr to preserve encoded Persian links -->
                                <input type="text" name="k_footer_useful_links[<?php echo $index; ?>][url]" value="<?php echo esc_attr($link['url'] ?? ''); ?>" placeholder="آدرس URL" style="width:50%;">
                                <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()">حذف</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button button-primary" onclick="addUsefulLink()"><?php esc_html_e('افزودن لینک مفید', 'khoshtip-kocholo'); ?></button>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('خدمات مشتریان', 'khoshtip-kocholo'); ?></th>
                <td>
                    <div id="customer-service-links-repeater" class="k-repeater">
                        <?php foreach ($customer_service_links as $index => $link) : ?>
                            <div class="k-repeater-item">
                                <input type="text" name="k_footer_customer_service_links[<?php echo $index; ?>][text]" value="<?php echo esc_attr($link['text'] ?? ''); ?>" placeholder="متن لینک" style="width:30%;">
                                <!-- CHANGE: Changed from esc_url to esc_attr to preserve encoded Persian links -->
                                <input type="text" name="k_footer_customer_service_links[<?php echo $index; ?>][url]" value="<?php echo esc_attr($link['url'] ?? ''); ?>" placeholder="آدرس URL" style="width:50%;">
                                <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()">حذف</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button button-primary" onclick="addCustomerServiceLink()"><?php esc_html_e('افزودن لینک خدمات', 'khoshtip-kocholo'); ?></button>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('آدرس تماس', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_footer_contact_address" value="<?php echo esc_attr($contact_address); ?>" class="large-text"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('شماره تماس', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_footer_contact_phone" value="<?php echo esc_attr($contact_phone); ?>" class="regular-text" dir="ltr"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('متن کپی‌رایت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_footer_copyright" value="<?php echo esc_attr($copyright_text); ?>" class="large-text"></td>
            </tr>
        </table>

        <h3><?php esc_html_e('رنگ‌های فوتر', 'khoshtip-kocholo'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('رنگ‌های پس‌زمینه', 'khoshtip-kocholo'); ?></th>
                <td>
                    <label style="display:block;margin-bottom:10px;">
                        <span style="display:inline-block;width:120px;"><?php esc_html_e('رنگ شروع:', 'khoshtip-kocholo'); ?></span>
                        <input type="text" name="k_footer_bg_start" value="<?php echo esc_attr($footer_bg_start); ?>" class="k-color-picker">
                    </label>
                    <label style="display:block;">
                        <span style="display:inline-block;width:120px;"><?php esc_html_e('رنگ پایان:', 'khoshtip-kocholo'); ?></span>
                        <input type="text" name="k_footer_bg_end" value="<?php echo esc_attr($footer_bg_end); ?>" class="k-color-picker">
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('رنگ متن و لینک‌ها', 'khoshtip-kocholo'); ?></th>
                <td>
                    <input type="text" name="k_footer_text_color" value="<?php echo esc_attr($footer_text_color); ?>" class="k-color-picker">
                    <p class="description"><?php esc_html_e('رنگ متن و لینک‌های فوتر', 'khoshtip-kocholo'); ?></p>
                </td>
            </tr>
        </table>

        <h3><?php esc_html_e('نوار پایین موبایل', 'khoshtip-kocholo'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('رنگ‌های پس‌زمینه', 'khoshtip-kocholo'); ?></th>
                <td>
                    <label style="display:block;margin-bottom:10px;">
                        <span style="display:inline-block;width:120px;"><?php esc_html_e('رنگ شروع:', 'khoshtip-kocholo'); ?></span>
                        <input type="text" name="k_bottom_bar_bg_start" value="<?php echo esc_attr($bottom_bar_bg_start); ?>" class="k-color-picker">
                    </label>
                    <label style="display:block;">
                        <span style="display:inline-block;width:120px;"><?php esc_html_e('رنگ پایان:', 'khoshtip-kocholo'); ?></span>
                        <input type="text" name="k_bottom_bar_bg_end" value="<?php echo esc_attr($bottom_bar_bg_end); ?>" class="k-color-picker">
                    </label>
                </td>
            </tr>
            <!-- اضافه کردن تنظیم رنگ فونت -->
            <tr>
                <th scope="row"><?php esc_html_e('رنگ فونت', 'khoshtip-kocholo'); ?></th>
                <td>
                    <input type="text" name="k_bottom_bar_text_color" value="<?php echo esc_attr($bottom_bar_text_color); ?>" class="k-color-picker">
                    <p class="description"><?php esc_html_e('رنگ متن و آیکون‌های نوار پایین', 'khoshtip-kocholo'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('نمایش در صفحات', 'khoshtip-kocholo'); ?></th>
                <td>
                    <label style="display:block;margin-bottom:5px;">
                        <input type="checkbox" name="k_bottom_bar_show_home" value="1" <?php checked($bottom_bar_show_home, '1'); ?>>
                        <?php esc_html_e('صفحه اصلی', 'khoshtip-kocholo'); ?>
                    </label>
                    <label style="display:block;margin-bottom:5px;">
                        <input type="checkbox" name="k_bottom_bar_show_products" value="1" <?php checked($bottom_bar_show_products, '1'); ?>>
                        <?php esc_html_e('صفحات محصول', 'khoshtip-kocholo'); ?>
                    </label>
                    <label style="display:block;margin-bottom:5px;">
                        <input type="checkbox" name="k_bottom_bar_show_all_products" value="1" <?php checked($bottom_bar_show_all_products, '1'); ?>>
                        <?php esc_html_e('صفحه همه محصولات', 'khoshtip-kocholo'); ?>
                    </label>
                    <label style="display:block;margin-bottom:5px;">
                        <input type="checkbox" name="k_bottom_bar_show_profile" value="1" <?php checked($bottom_bar_show_profile, '1'); ?>>
                        <?php esc_html_e('صفحه پروفایل', 'khoshtip-kocholo'); ?>
                    </label>
                    <!-- اضافه کردن گزینه نمایش در سبد خرید -->
                    <label style="display:block;margin-bottom:5px;">
                        <input type="checkbox" name="k_bottom_bar_show_cart" value="1" <?php checked(get_option('k_bottom_bar_show_cart', '1'), '1'); ?>>
                        <?php esc_html_e('صفحه سبد خرید', 'khoshtip-kocholo'); ?>
                    </label>
                    <!-- CHANGE: Adding sendcode page visibility option -->
                    <label style="display:block;margin-bottom:5px;">
                        <input type="checkbox" name="k_bottom_bar_show_sendcode" value="1" <?php checked($bottom_bar_show_sendcode, '1'); ?>>
                        <?php esc_html_e('صفحه کد مرسوله', 'khoshtip-kocholo'); ?>
                    </label>
                    <!-- Adding sales page visibility option -->
                    <label style="display:block;margin-bottom:5px;">
                        <input type="checkbox" name="k_bottom_bar_show_sales" value="1" <?php checked(get_option('k_bottom_bar_show_sales', '1'), '1'); ?>>
                        <?php esc_html_e('صفحه حراجی', 'khoshtip-kocholo'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('دکمه‌های سفارشی (حداکثر 6)', 'khoshtip-kocholo'); ?></th>
                <td>
                    <div id="bottom-bar-buttons-repeater" class="k-repeater">
                        <?php foreach ($bottom_bar_buttons as $index => $button) : ?>
                            <div class="k-repeater-item" style="display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-bottom:10px; padding:10px; background:#f9f9f9; border-radius:5px;">
                                <input type="text" name="k_bottom_bar_buttons[<?php echo $index; ?>][label]" value="<?php echo esc_attr($button['label'] ?? ''); ?>" placeholder="عنوان دکمه" style="width:120px;">
                                <input type="text" name="k_bottom_bar_buttons[<?php echo $index; ?>][url]" value="<?php echo esc_attr($button['url'] ?? ''); ?>" placeholder="آدرس URL" style="width:40%;">
                                <input type="hidden" name="k_bottom_bar_buttons[<?php echo $index; ?>][image]" value="<?php echo esc_url($button['image'] ?? ''); ?>" class="bottom-button-image-input">
                                <button type="button" class="button upload-bottom-button-image"><?php esc_html_e('انتخاب تصویر', 'khoshtip-kocholo'); ?></button>
                                <?php if (!empty($button['image'])) : ?>
                                    <img src="<?php echo esc_url($button['image']); ?>" class="k-image-preview" style="max-width:40px;height:auto;">
                                <?php endif; ?>
                                <!--اضافه کردن چک‌باکس سبد خرید -->
                                <label style="display:inline-flex;align-items:center;gap:4px;"><input type="checkbox" name="k_bottom_bar_buttons[<?php echo $index; ?>][is_cart]" value="1" <?php checked(isset($button['is_cart']) && $button['is_cart'] === '1'); ?>> سبد خرید</label>
                                <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()">حذف</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button button-primary" onclick="addBottomBarButton()"><?php esc_html_e('افزودن دکمه', 'khoshtip-kocholo'); ?></button>
                </td>
            </tr>
        </table>
        <?php
    }

    private function save_footer_tab($data) {
        update_option('k_footer_site_title', sanitize_text_field($data['k_footer_site_title'] ?? ''));
        update_option('k_footer_site_description', sanitize_textarea_field($data['k_footer_site_description'] ?? ''));
        update_option('k_footer_useful_links', KK_Helper::sanitize_repeater_array($data['k_footer_useful_links'] ?? []));
        update_option('k_footer_customer_service_links', KK_Helper::sanitize_repeater_array($data['k_footer_customer_service_links'] ?? []));
        update_option('k_footer_contact_address', sanitize_text_field($data['k_footer_contact_address'] ?? ''));
        update_option('k_footer_contact_phone', sanitize_text_field($data['k_footer_contact_phone'] ?? ''));
        update_option('k_footer_copyright', sanitize_text_field($data['k_footer_copyright'] ?? ''));

        // ذخیره رنگ‌های فوتر
        update_option('k_footer_bg_start', sanitize_hex_color($data['k_footer_bg_start'] ?? '#667eea'));
        update_option('k_footer_bg_end', sanitize_hex_color($data['k_footer_bg_end'] ?? '#764ba2'));
        update_option('k_footer_text_color', sanitize_hex_color($data['k_footer_text_color'] ?? '#ffffff'));

        update_option('k_bottom_bar_bg_start', sanitize_hex_color($data['k_bottom_bar_bg_start'] ?? '#ffffff'));
        update_option('k_bottom_bar_bg_end', sanitize_hex_color($data['k_bottom_bar_bg_end'] ?? '#f9fafb'));
        update_option('k_bottom_bar_text_color', sanitize_hex_color($data['k_bottom_bar_text_color'] ?? '#374151'));
        update_option('k_bottom_bar_show_home', isset($data['k_bottom_bar_show_home']) ? '1' : '0');
        update_option('k_bottom_bar_show_products', isset($data['k_bottom_bar_show_products']) ? '1' : '0');
        update_option('k_bottom_bar_show_all_products', isset($data['k_bottom_bar_show_all_products']) ? '1' : '0');
        update_option('k_bottom_bar_show_profile', isset($data['k_bottom_bar_show_profile']) ? '1' : '0');
        update_option('k_bottom_bar_show_cart', isset($data['k_bottom_bar_show_cart']) ? '1' : '0'); // Save cart visibility
        // CHANGE: Saving sendcode page visibility option
        update_option('k_bottom_bar_show_sendcode', isset($data['k_bottom_bar_show_sendcode']) ? '1' : '0');
        // CHANGE: Saving sales page visibility option
        update_option('k_bottom_bar_show_sales', isset($data['k_bottom_bar_show_sales']) ? '1' : '0');

        $buttons = [];
        if (isset($data['k_bottom_bar_buttons']) && is_array($data['k_bottom_bar_buttons'])) {
            foreach ($data['k_bottom_bar_buttons'] as $index => $button) {
                if ($index >= 6) break; // Maximum 6 buttons
                if (!empty($button['label'])) { // Only buttons with a label
                    $buttons[] = [
                        'label' => sanitize_text_field($button['label']),
                        'url' => esc_url_raw($button['url'] ?? ''),
                        'image' => esc_url_raw($button['image'] ?? ''),
                        'is_cart' => isset($button['is_cart']) ? '1' : '0', // Save cart flag
                    ];
                }
            }
        }
        update_option('k_bottom_bar_buttons', $buttons);

        update_option('k_categories_panel_enabled', isset($data['k_categories_panel_enabled']) ? '1' : '0');
        update_option('k_categories_panel_button_label', sanitize_text_field($data['k_categories_panel_button_label'] ?? 'دسته بندی'));
        update_option('k_categories_panel_bg_color', sanitize_hex_color($data['k_categories_panel_bg_color'] ?? '#ffffff'));
        update_option('k_categories_panel_icon', esc_url_raw($data['k_categories_panel_icon'] ?? ''));

        $panel_cats = [];
        if (isset($data['k_categories_panel_categories']) && is_array($data['k_categories_panel_categories'])) {
            $panel_cats = array_map('absint', $data['k_categories_panel_categories']);
        }
        update_option('k_categories_panel_categories', $panel_cats);
    }

    // ==========================================
    // BACKGROUND COLORS TAB (تنظیمات رنگ پس‌زمینه)
    // ==========================================

    private function render_background_colors_tab() {
        $main_bg_color = get_option('k_main_background_color', '#ffffff');
        $accordion_bg_color = get_option('k_accordion_background_color', '#f5f5f5');
        $blog_bg_color = get_option('k_blog_background_color', '#ffffff');
        $banner_bg_color = get_option('k_banner_background_color', '#f9fafb');
        ?>
        <div class="settings-section">
            <h2>🎨 <?php esc_html_e('تنظیمات رنگ پس‌زمینه سایت', 'khoshtip-kocholo'); ?></h2>
            <p class="description"><?php esc_html_e('رنگ‌های پس‌زمینه قسمت‌های مختلف سایت را تنظیم کنید.', 'khoshtip-kocholo'); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="k_main_background_color">
                            <?php esc_html_e('رنگ پس‌زمینه اصلی سایت', 'khoshtip-kocholo'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="k_main_background_color" 
                               name="k_main_background_color" 
                               value="<?php echo esc_attr($main_bg_color); ?>" 
                               class="k-color-picker"
                               data-default="#ffffff">
                        <p class="description">
                            <?php esc_html_e('رنگ پس‌زمینه اصلی صفحه که اکاردئون‌ها، بلاگ‌ها و بنر روی آن قرار دارند.', 'khoshtip-kocholo'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="k_accordion_background_color">
                            <?php esc_html_e('رنگ پس‌زمینه نوار اکاردئون', 'khoshtip-kocholo'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="k_accordion_background_color" 
                               name="k_accordion_background_color" 
                               value="<?php echo esc_attr($accordion_bg_color); ?>" 
                               class="k-color-picker"
                               data-default="#f5f5f5">
                        <p class="description">
                            <?php esc_html_e('رنگ پس‌زمینه نوار‌های اکاردئونی در سایت.', 'khoshtip-kocholo'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="k_blog_background_color">
                            <?php esc_html_e('رنگ پس‌زمینه بلاگ', 'khoshtip-kocholo'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="k_blog_background_color" 
                               name="k_blog_background_color" 
                               value="<?php echo esc_attr($blog_bg_color); ?>" 
                               class="k-color-picker"
                               data-default="#ffffff">
                        <p class="description">
                            <?php esc_html_e('رنگ پس‌زمینه بخش بلاگ و مقالات.', 'khoshtip-kocholo'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="k_banner_background_color">
                            <?php esc_html_e('رنگ پس‌زمینه بنر', 'khoshtip-kocholo'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="k_banner_background_color" 
                               name="k_banner_background_color" 
                               value="<?php echo esc_attr($banner_bg_color); ?>" 
                               class="k-color-picker"
                               data-default="#f9fafb">
                        <p class="description">
                            <?php esc_html_e('رنگ پس‌زمینه بنر و اسلایدها.', 'khoshtip-kocholo'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="k-notice k-notice-warning" style="margin-top: 20px;">
            <strong><?php esc_html_e('نکات مهم:', 'khoshtip-kocholo'); ?></strong>
            <ul style="margin: 10px 0 0 20px; list-style: disc;">
                <li><?php esc_html_e('رنگ‌های انتخاب شده در کل سایت اعمال می‌شوند.', 'khoshtip-kocholo'); ?></li>
                <li><?php esc_html_e('برای دیدن تغییرات، صفحه را بازخانی کنید.', 'khoshtip-kocholo'); ?></li>
                <li><?php esc_html_e('رنگ‌های روشن برای سایت‌های سبک و رنگ‌های تیره برای سایت‌های تاریک توصیه می‌شود.', 'khoshtip-kocholo'); ?></li>
            </ul>
        </div>
        <?php
    }

    private function save_background_colors_tab($data) {
        update_option('k_main_background_color', sanitize_hex_color($data['k_main_background_color'] ?? '#ffffff'));
        update_option('k_accordion_background_color', sanitize_hex_color($data['k_accordion_background_color'] ?? '#f5f5f5'));
        update_option('k_blog_background_color', sanitize_hex_color($data['k_blog_background_color'] ?? '#ffffff'));
        update_option('k_banner_background_color', sanitize_hex_color($data['k_banner_background_color'] ?? '#f9fafb'));
    }

    private function render_categories_panel_tab() {
        $enabled = get_option('k_categories_panel_enabled', '0');
        $button_label = get_option('k_categories_panel_button_label', 'دسته بندی');
        $bg_color = get_option('k_categories_panel_bg_color', '#ffffff');
        $icon_url = get_option('k_categories_panel_icon', '');
        $selected_categories = get_option('k_categories_panel_categories', []);

        // Get all product categories
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        ?>
        <div class="settings-section">
            <h2>پنل سریع دسته‌بندی</h2>
            <p class="description">یک پنل زیبا و کودکانه برای دسترسی سریع به دسته‌بندی‌های محصولات</p>

            <table class="form-table">
                <tr>
                    <th scope="row">فعال‌سازی پنل</th>
                    <td>
                        <label>
                            <input type="checkbox" name="k_categories_panel_enabled" value="1" <?php checked($enabled, '1'); ?>>
                            فعال‌سازی پنل دسته���بندی در منوی پایین
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">باز شدن خودکار هنگام ورود</th>
                    <td>
                        <label>
                            <input type="checkbox" name="k_categories_panel_auto_open" value="1" <?php checked(get_option('k_categories_panel_auto_open', '0'), '1'); ?>>
                            وقتی کاربر وارد سایت می‌شود، پنل دسته‌بندی به صورت خودکار باز شود
                        </label>
                        <p class="description">اگر فعال باشد، کاربران هنگام ورود به سایت ابتدا پنل دسته‌بندی را می‌بینند</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">دسته‌بندی پیش‌فرض هنگام باز شدن خودکار</th>
                    <td>
                        <?php 
                        $auto_open_category = get_option('k_categories_panel_auto_open_category', '');
                        if (is_wp_error($categories) || empty($categories)) : ?>
                            <p>هیچ دسته‌بندی یافت نشد</p>
                        <?php else : ?>
                            <select name="k_categories_panel_auto_open_category" style="width: 100%; max-width: 400px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="">-- همه محصولات --</option>
                                <?php foreach ($categories as $cat) : ?>
                                    <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($auto_open_category, $cat->term_id); ?>>
                                        <?php echo esc_html($cat->name); ?> (<?php echo $cat->count; ?> محصول)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">این دسته‌بندی وقتی پنل به صورت خودکار باز می‌شود، به طور پیش‌فرض نمایش داده می‌شود. اگر "همه محصولات" انتخاب شود، تمام محصولات نمایش داده می‌شود</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">عنوان دکمه</th>
                    <td>
                        <input type="text" name="k_categories_panel_button_label" value="<?php echo esc_attr($button_label); ?>" class="regular-text" placeholder="دسته بندی">
                        <p class="description">عنوانی که روی دکمه منو نمایش داده می‌شود</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">رنگ پس‌زمینه</th>
                    <td>
                        <input type="text" name="k_categories_panel_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="k-color-picker">
                        <p class="description">رنگ پس‌زمینه پنل دسته‌بندی</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">آیکون دکمه</th>
                    <td>
                        <input type="hidden" name="k_categories_panel_icon" value="<?php echo esc_url($icon_url); ?>" id="k-categories-panel-icon-input">
                        <button type="button" class="button upload-categories-panel-icon">انتخاب آیکون</button>
                        <?php if (!empty($icon_url)) : ?>
                            <img src="<?php echo esc_url($icon_url); ?>" id="k-categories-panel-icon-preview" style="max-width: 50px; height: auto; margin-right: 10px; vertical-align: middle;">
                        <?php else : ?>
                            <img src="/placeholder.svg" id="k-categories-panel-icon-preview" style="display: none; max-width: 50px; height: auto; margin-right: 10px; vertical-align: middle;">
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">دسته‌بندی‌های قابل نمایش</th>
                    <td>
                        <?php if (is_wp_error($categories) || empty($categories)) : ?>
                            <p>هیچ دسته‌بندی یافت نشد</p>
                        <?php else : ?>
                            <!-- Added category image upload for each category -->
                            <div style="max-height: 400px; overflow-y: auto; border: 1px solid #e5e5e5; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                                <?php foreach ($categories as $cat) :
                                    $cat_image = get_term_meta($cat->term_id, 'thumbnail_id', true);
                                    $cat_image_url = $cat_image ? wp_get_attachment_url($cat_image) : '';
                                ?>
                                    <div style="display: flex; align-items: center; margin-bottom: 12px; padding: 10px; background: #fff; border-radius: 4px;">
                                        <label style="flex: 1; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                                            <input type="checkbox" name="k_categories_panel_categories[]" value="<?php echo esc_attr($cat->term_id); ?>" <?php checked(in_array($cat->term_id, $selected_categories)); ?>>
                                            <?php if ($cat_image_url) : ?>
                                                <img src="<?php echo esc_url($cat_image_url); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                            <?php else : ?>
                                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                                    <span style="font-size: 20px;">📦</span>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo esc_html($cat->name); ?></strong>
                                                <span style="color: #666; font-size: 12px; display: block;">(<?php echo $cat->count; ?> محصول)</span>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <p class="description" style="margin-top: 10px;">دسته‌بندی‌ها از عکس شاخص تنظیمات خودشان در ووکامرس استفاده می‌کنند. برای تغییر عکس، به <a href="<?php echo admin_url('edit-tags.php?taxonomy=product_cat&post_type=product'); ?>" target="_blank">دسته‌بندی محصولات</a> بروید.</p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.upload-categories-panel-icon').on('click', function(e) {
                e.preventDefault();
                var button = $(this);
                var imageFrame = wp.media({
                    title: 'انتخاب آیکون',
                    multiple: false,
                    library: { type: 'image' }
                });

                imageFrame.on('select', function() {
                    var attachment = imageFrame.state().get('selection').first().toJSON();
                    $('#k-categories-panel-icon-input').val(attachment.url);
                    $('#k-categories-panel-icon-preview').attr('src', attachment.url).show();
                });

                imageFrame.open();
            });
        });
        </script>
        <?php
    }

    private function save_categories_panel_tab($data) {
        update_option('k_categories_panel_enabled', isset($data['k_categories_panel_enabled']) ? '1' : '0');
        update_option('k_categories_panel_auto_open', isset($data['k_categories_panel_auto_open']) ? '1' : '0');
        update_option('k_categories_panel_auto_open_category', absint($data['k_categories_panel_auto_open_category'] ?? 0));
        update_option('k_categories_panel_button_label', sanitize_text_field($data['k_categories_panel_button_label'] ?? 'دسته بندی'));
        update_option('k_categories_panel_bg_color', sanitize_hex_color($data['k_categories_panel_bg_color'] ?? '#ffffff'));
        update_option('k_categories_panel_icon', esc_url_raw($data['k_categories_panel_icon'] ?? ''));

        $panel_cats = [];
        if (isset($data['k_categories_panel_categories']) && is_array($data['k_categories_panel_categories'])) {
            $panel_cats = array_map('absint', $data['k_categories_panel_categories']);
        }
        update_option('k_categories_panel_categories', $panel_cats);
    }

    // ==========================================
    // PRICE SETTINGS TAB
    // ==========================================

    private function render_price_settings_tab() {
        $product_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));
        ?>
        <div class="k-price-settings-tab">
            <h3>تنظیمات قیمت‌گذاری</h3>
            <p style="color: #666; font-size: 14px; line-height: 1.6;">
                با استفاده از این بخش می‌توانید قیمت تمام محصولات یا محصولات یک دسته‌بندی خاص را به صورت درصدی افزایش دهید.
                <br />
                <strong>نکات مهم:</strong> قیمت‌های قبلی خودکار ذخیره می‌شوند و می‌توانید آنها را بازگردانید. عملیات روی قیمت اصلی محصول اعمال می‌شود.
            </p>

            <div class="k-price-increase-section" style="background: #f0f0f1; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h4 style="margin-top: 0;">افزایش قیمت محصولات</h4>
                <table class="form-table">
                    <tr>
                        <th scope="row">انتخاب دسته‌بندی</th>
                        <td>
                            <select id="k-price-category-filter" style="width: 300px;">
                                <option value="0">همه محصولات</option>
                                <?php foreach ($product_categories as $category) : ?>
                                    <option value="<?php echo esc_attr($category->term_id); ?>">
                                        <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">دسته‌بندی را انتخاب کنید.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">انتخاب محصولات</th>
                        <td>
                            <div id="k-price-products-list" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f0f0f1; border-radius: 8px;">
                                <p style="color: #666;">در حال بارگذاری محصولات...</p>
                            </div>
                            <div style="margin-top: 10px;">
                                <label>
                                    <input type="checkbox" id="k-price-select-all-products" />
                                    افزایش قیمت همه محصولات نمایش‌داده‌شده
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">درصد افزایش قیمت</th>
                        <td>
                            <input type="number" id="k-price-increase-percent" min="1" max="500" value="25" step="0.1" style="width: 100px;" /> %
                            <p class="description">درصد افزایش قیمت (1 تا 500 درصد)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"></th>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <button type="button" class="button button-primary button-large" id="k-apply-price-increase">
                                    اعمال افزایش قیمت
                                </button>
                                <button type="button" class="button button-secondary" id="k-revert-prices" style="background: #d63638; color: #fff; border-color: #d63638;">
                                    بازگشت به قیمت‌های قبلی
                                </button>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="k-price-history" style="background: #fff; padding: 20px; border: 1px solid #c3c4c7; border-radius: 8px;">
                <h4>تاریخچه تغییرات</h4>
                <div id="k-price-history-list">
                    <p style="color: #666; text-align: center; padding: 20px;">تغییری ثبت نشده است.</p>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            loadProductsByCategory(0);

            $('#k-price-category-filter').on('change', function() {
                const categoryId = $(this).val();
                loadProductsByCategory(categoryId);
            });

            function loadProductsByCategory(categoryId) {
                const $productsList = $('#k-price-products-list');

                $productsList.html('<p style="color: #666;">در حال بارگذاری محصولات...</p>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_get_products_by_category',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>',
                        category_id: categoryId
                    },
                    success: function(response) {
                        if (response.success) {
                            let html = '';

                            if (response.data.products.length === 0) {
                                html = '<p style="color: #666;">هیچ محصولی یافت نشد.</p>';
                            } else {
                                response.data.products.forEach(function(product) {
                                    const productTypeInfo = product.type === 'variable' ? ' (متغیر)' : '';
                                    html += `
                                        <div style="padding: 8px; border-bottom: 1px solid #ddd;">
                                            <label style="display: flex; align-items: center; cursor: pointer;">
                                                <input type="checkbox" class="k-price-product-checkbox" value="${product.id}" style="margin-left: 8px;" />
                                                <span>${product.name}${productTypeInfo} - ${product.regular_price}</span>
                                            </label>
                                        </div>
                                    `;
                                });
                            }

                            $productsList.html(html);
                        }
                    },
                    error: function() {
                        $productsList.html('<p style="color: #d63638;">خطا در بارگذاری محصولات.</p>');
                    }
                });
            }

            $('#k-price-select-all-products').on('change', function() {
                $('.k-price-product-checkbox').prop('checked', $(this).is(':checked'));
            });

            $('#k-apply-price-increase').on('click', function() {
                const selectedProducts = [];
                $('.k-price-product-checkbox:checked').each(function() {
                    selectedProducts.push($(this).val());
                });

                const increasePercent = $('#k-price-increase-percent').val();

                if (selectedProducts.length === 0) {
                    alert('لطفاً حداقل یک محصول انتخاب کنید.');
                    return;
                }

                if (!increasePercent || increasePercent < 1 || increasePercent > 500) {
                    alert('لطفاً درصد معتبر (1 تا 500) وارد کنید.');
                    return;
                }

                if (!confirm('آیا از افزایش ' + increasePercent + '% قیمت ' + selectedProducts.length + ' محصول اطمینان دارید؟')) {
                    return;
                }

                const $button = $(this);
                $button.prop('disabled', true).text('در حال اعمال تغییرات...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_apply_price_increase',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>',
                        product_ids: selectedProducts,
                        increase_percent: increasePercent
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            const categoryId = $('#k-price-category-filter').val();
                            loadProductsByCategory(categoryId);
                            loadPriceHistory();
                        } else {
                            alert('خطا: ' + (response.data.message || 'خطایی نامعلوم رخ داد'));
                        }
                    },
                    error: function() {
                        alert('خطا در برقراری ارتباط.');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('اعمال افزایش قیمت');
                    }
                });
            });

            $('#k-revert-prices').on('click', function() {
                if (!confirm('آیا از بازگشت به قیمت‌های قبلی اطمینان دارید؟')) {
                    return;
                }

                const $button = $(this);
                $button.prop('disabled', true).text('در حال برگرداندن...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_revert_prices',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            const categoryId = $('#k-price-category-filter').val();
                            loadProductsByCategory(categoryId);
                            loadPriceHistory();
                        } else {
                            alert('خطا: ' + (response.data.message || 'خطایی نامعلوم رخ داد'));
                        }
                    },
                    error: function() {
                        alert('خطا در برقراری ارتباط.');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('بازگشت به قیمت‌های قبلی');
                    }
                });
            });

            function loadPriceHistory() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_get_price_history',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data.history.length > 0) {
                            let html = '<table class="wp-list-table widefat fixed striped">';
                            html += '<thead><tr>';
                            html += '<th>تاریخ</th>';
                            html += '<th>نوع عملیات</th>';
                            html += '<th>تعداد محصولات</th>';
                            html += '<th>درصد تغییر</th>';
                            html += '</tr></thead>';
                            html += '<tbody>';

                            response.data.history.forEach(function(record) {
                                const date = new Date(record.timestamp * 1000);
                                const dateStr = date.toLocaleString('fa-IR');
                                html += '<tr>';
                                html += '<td>' + dateStr + '</td>';
                                html += '<td>' + record.action + '</td>';
                                html += '<td>' + record.product_count + '</td>';
                                html += '<td>' + (record.action === 'افزایش قیمت' ? '+' : '') + record.percent + '%</td>';
                                html += '</tr>';
                            });

                            html += '</tbody></table>';
                            $('#k-price-history-list').html(html);
                        }
                    }
                });
            }

            loadPriceHistory();
        });
        </script>
        <?php
    }

    private function save_price_settings_tab($data) {
        // This tab doesn't save form data directly
    }
}
?>
