<?php
/**
 * Faraz SMS Authentication Handler
 * سیستم احراز هویت با شماره موبایل و ارسال کد تایید
 */

class Khoshtip_SMS_Auth {
    
    private $api_key;
    private $sender_number;
    private $enabled;
    
    public function __construct() {
        $this->api_key = get_option('khoshtip_faraz_api_key', '');
        $this->sender_number = get_option('khoshtip_faraz_sender_number', '');
        $this->enabled = get_option('khoshtip_sms_enabled', '0') === '1';
        
        if ($this->enabled) {
            add_action('wp_ajax_nopriv_send_sms_code', array($this, 'send_sms_code'));
            add_action('wp_ajax_send_sms_code', array($this, 'send_sms_code'));
            
            add_action('wp_ajax_nopriv_verify_sms_code', array($this, 'verify_sms_code'));
            add_action('wp_ajax_verify_sms_code', array($this, 'verify_sms_code'));
            
            add_action('woocommerce_register_form', array($this, 'add_phone_field_to_registration'));
            add_action('woocommerce_created_customer', array($this, 'save_phone_number'));
        }
    }
    
    /**
     * اضافه کردن فیلد شماره موبایل به فرم ثبت‌نام
     */
    public function add_phone_field_to_registration() {
        $required = get_option('khoshtip_require_phone', '0') === '1';
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="reg_phone">
                شماره موبایل&nbsp;<?php if ($required) echo '<span class="required">*</span>'; ?>
            </label>
            <input type="tel" 
                   class="woocommerce-Input woocommerce-Input--text input-text" 
                   name="phone" 
                   id="reg_phone" 
                   value="<?php echo (!empty($_POST['phone'])) ? esc_attr(wp_unslash($_POST['phone'])) : ''; ?>" 
                   placeholder="09123456789"
                   pattern="09[0-9]{9}"
                   <?php if ($required) echo 'required'; ?> />
            <small class="form-text">شماره موبایل خود را با فرمت 09xxxxxxxxx وارد کنید</small>
        </p>
        <?php
    }
    
    /**
     * ذخیره شماره موبایل هنگام ثبت‌نام
     */
    public function save_phone_number($customer_id) {
        if (isset($_POST['phone'])) {
            $phone = sanitize_text_field($_POST['phone']);
            update_user_meta($customer_id, 'billing_phone', $phone);
            update_user_meta($customer_id, 'phone_number', $phone);
        }
    }
    
    /**
     * ارسال کد تایید به شماره موبایل
     */
    public function send_sms_code() {
        check_ajax_referer('sms_auth_nonce', 'nonce');
        
        if (!isset($_POST['phone'])) {
            wp_send_json_error(array('message' => 'شماره موبایل وارد نشده است'));
            return;
        }
        
        $phone = sanitize_text_field($_POST['phone']);
        
        // اعتبارسنجی شماره موبایل
        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            wp_send_json_error(array('message' => 'شماره موبایل نامعتبر است'));
            return;
        }
        
        // تولید کد تایید 6 رقمی
        $code = sprintf('%06d', mt_rand(100000, 999999));
        
        // ذخیره کد در transient برای 5 دقیقه
        set_transient('sms_code_' . $phone, $code, 300);
        
        // ارسال پیامک
        $result = $this->send_sms($phone, "کد تایید شما: {$code}\nخوش‌تیپ کوچولو");
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
            return;
        }
        
        wp_send_json_success(array('message' => 'کد تایید به شماره موبایل شما ارسال شد'));
    }
    
    /**
     * تایید کد و ورود/ثبت‌نام کاربر
     */
    public function verify_sms_code() {
        check_ajax_referer('sms_auth_nonce', 'nonce');
        
        if (!isset($_POST['phone']) || !isset($_POST['code'])) {
            wp_send_json_error(array('message' => 'اطلاعات ناقص است'));
            return;
        }
        
        $phone = sanitize_text_field($_POST['phone']);
        $code = sanitize_text_field($_POST['code']);
        
        // بررسی کد تایید
        $saved_code = get_transient('sms_code_' . $phone);
        
        if (!$saved_code) {
            wp_send_json_error(array('message' => 'کد تایید منقضی شده است'));
            return;
        }
        
        if ($saved_code !== $code) {
            wp_send_json_error(array('message' => 'کد تایید نادرست است'));
            return;
        }
        
        // حذف کد استفاده شده
        delete_transient('sms_code_' . $phone);
        
        // جستجوی کاربر با این شماره موبایل به عنوان نام کاربری
        $user = get_user_by('login', $phone);
        
        // اگر با نام کاربری پیدا نشد، با متا جستجو کن
        if (!$user) {
            $users = get_users(array(
                'meta_key' => 'billing_phone',
                'meta_value' => $phone,
                'number' => 1
            ));
            
            if (!empty($users)) {
                $user = $users[0];
            }
        }
        
        if ($user) {
            // کاربر وجود دارد - ورود
            // اطمینان از اینکه username شماره موبایل است
            global $wpdb;
            $wpdb->update(
                $wpdb->users,
                array('user_login' => $phone),
                array('ID' => $user->ID),
                array('%s'),
                array('%d')
            );
        } else {
            // ثبت‌نام کاربر جدید با شماره موبایل به عنوان نام کاربری
            $email = $phone . '@temp.local'; // ایمیل موقت
            
            $user_id = wp_create_user($phone, wp_generate_password(), $email);
            
            if (is_wp_error($user_id)) {
                wp_send_json_error(array('message' => 'خطا در ایجاد حساب کاربری'));
                return;
            }
            
            // ذخیره شماره موبایل
            update_user_meta($user_id, 'billing_phone', $phone);
            
            // تنظیم نقش
            wp_update_user(array(
                'ID' => $user_id,
                'role' => 'customer'
            ));
            
            $user = get_user_by('id', $user_id);
        }
        
        // ورود کاربر
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        wp_send_json_success(array(
            'message' => 'ورود موفقیت‌آمیز بود',
            'redirect' => wc_get_page_permalink('myaccount')
        ));
    }
    
    /**
     * ارسال پیامک از طریق IP Panel (Edge API)
     */
    private function send_sms($phone, $message) {
        if (empty($this->api_key) || empty($this->sender_number)) {
            return new WP_Error('sms_config', 'تنظیمات SMS ناقص است. لطفا API Key و شماره ارسال کننده (مثلا 3000505) را در تنظیمات وارد کنید.');
        }
        
        // تبدیل شماره به فرمت E.164
        $formatted_phone = $this->format_phone_e164($phone);
        $formatted_sender = $this->format_phone_e164($this->sender_number);
        
        // استفاده از IP Panel Edge API
        $response = wp_remote_post('https://edge.ippanel.com/v1/api/send', array(
            'timeout' => 15,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => $this->api_key  // بدون Bearer
            ),
            'body' => json_encode(array(
                'sending_type' => 'webservice',
                'from_number' => $formatted_sender,
                'message' => $message,
                'params' => array(
                    'recipients' => array($formatted_phone)
                )
            ))
        ));
        
        if (is_wp_error($response)) {
            error_log('SMS API Error: ' . $response->get_error_message());
            return new WP_Error('sms_connection', 'خطا در اتصال به سرویس پیامک. لطفا دوباره تلاش کنید.');
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        // بررسی پاسخ موفق
        if ($http_code === 200 || $http_code === 201) {
            if (isset($body['meta']['status']) && $body['meta']['status'] === true) {
                return true;
            }
        }
        
        // لاگ خطا برای دیباگ
        error_log('SMS API Response: ' . wp_remote_retrieve_body($response));
        
        $error_message = isset($body['meta']['message']) ? $body['meta']['message'] : 'خطا در ارسال پیامک';
        return new WP_Error('sms_send_failed', $error_message);
    }
    
    /**
     * تبدیل شماره تلفن ایرانی به فرمت E.164
     * مثال: 09123456789 -> +989123456789
     * مثال: 3000505 -> +983000505
     */
    private function format_phone_e164($phone) {
        // حذف فاصله‌ها و کاراکترهای اضافی
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // اگر با +98 شروع می‌شود، همان را برگردان
        if (strpos($phone, '+98') === 0) {
            return $phone;
        }
        
        // اگر با 0 شروع می‌شود، 0 را حذف کن و +98 اضافه کن
        if (strpos($phone, '0') === 0) {
            return '+98' . substr($phone, 1);
        }
        
        // در غیر این صورت، +98 اضافه کن
        return '+98' . $phone;
    }
}

// راه‌اندازی کلاس
new Khoshtip_SMS_Auth();
