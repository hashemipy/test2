<?php
/**
 * SMS Authentication (IP Panel)
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_SMS_Auth {
    
    private static $instance = null;
    private $api_key;
    private $sender_number;
    private $enabled;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->api_key = get_option('khoshtip_faraz_api_key', '');
        $this->sender_number = get_option('khoshtip_faraz_sender_number', '');
        $this->enabled = get_option('khoshtip_sms_enabled', '0') === '1';
        
        if (!$this->enabled) {
            return;
        }
        
        add_action('wp_ajax_nopriv_send_sms_code', [$this, 'send_code']);
        add_action('wp_ajax_send_sms_code', [$this, 'send_code']);
        add_action('wp_ajax_nopriv_verify_sms_code', [$this, 'verify_code']);
        add_action('wp_ajax_verify_sms_code', [$this, 'verify_code']);
        
        add_action('woocommerce_register_form', [$this, 'add_phone_field']);
        add_action('woocommerce_created_customer', [$this, 'save_phone']);
    }
    
    public function add_phone_field() {
        $required = get_option('khoshtip_require_phone', '0') === '1';
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="reg_phone">
                <?php esc_html_e('شماره موبایل', 'khoshtip-kocholo'); ?>
                <?php if ($required) echo '<span class="required">*</span>'; ?>
            </label>
            <input type="tel" class="woocommerce-Input input-text" name="phone" id="reg_phone" 
                   value="<?php echo isset($_POST['phone']) ? esc_attr($_POST['phone']) : ''; ?>" 
                   placeholder="09123456789" pattern="09[0-9]{9}" <?php echo $required ? 'required' : ''; ?>>
        </p>
        <?php
    }
    
    public function save_phone($customer_id) {
        if (isset($_POST['phone'])) {
            $phone = sanitize_text_field($_POST['phone']);
            update_user_meta($customer_id, 'billing_phone', $phone);
            update_user_meta($customer_id, 'phone_number', $phone);
        }
    }
    
    public function send_code() {
        check_ajax_referer('sms_auth_nonce', 'nonce');
        
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        
        if (empty($phone)) {
            wp_send_json_error(['message' => __('شماره موبایل وارد نشده است', 'khoshtip-kocholo')]);
        }
        
        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            wp_send_json_error(['message' => __('شماره موبایل نامعتبر است', 'khoshtip-kocholo')]);
        }
        
        $code = sprintf('%06d', mt_rand(100000, 999999));
        set_transient('sms_code_' . $phone, $code, 300);
        
        $message = sprintf(__('کد تایید شما: %s', 'khoshtip-kocholo'), $code);
        $result = $this->send_sms($phone, $message);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success(['message' => __('کد تایید ارسال شد', 'khoshtip-kocholo')]);
    }
    
    public function verify_code() {
        check_ajax_referer('sms_auth_nonce', 'nonce');
        
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $code = sanitize_text_field($_POST['code'] ?? '');
        
        if (empty($phone) || empty($code)) {
            wp_send_json_error(['message' => __('اطلاعات ناقص است', 'khoshtip-kocholo')]);
        }
        
        $saved_code = get_transient('sms_code_' . $phone);
        
        if (!$saved_code) {
            wp_send_json_error(['message' => __('کد تایید منقضی شده است', 'khoshtip-kocholo')]);
        }
        
        if ($saved_code !== $code) {
            wp_send_json_error(['message' => __('کد تایید نادرست است', 'khoshtip-kocholo')]);
        }
        
        delete_transient('sms_code_' . $phone);
        
        // Find or create user
        $users = get_users([
            'meta_key' => 'phone_number',
            'meta_value' => $phone,
            'number' => 1
        ]);
        
        if (!empty($users)) {
            $user = $users[0];
        } else {
            $username = 'user_' . substr($phone, -8);
            $counter = 1;
            $original_username = $username;
            while (username_exists($username)) {
                $username = $original_username . $counter++;
            }
            
            $user_id = wp_create_user($username, wp_generate_password(), $phone . '@temp.local');
            
            if (is_wp_error($user_id)) {
                wp_send_json_error(['message' => __('خطا در ایجاد حساب', 'khoshtip-kocholo')]);
            }
            
            update_user_meta($user_id, 'phone_number', $phone);
            update_user_meta($user_id, 'billing_phone', $phone);
            wp_update_user(['ID' => $user_id, 'role' => 'customer']);
            
            $user = get_user_by('id', $user_id);
        }
        
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        wp_send_json_success([
            'message' => __('ورود موفق', 'khoshtip-kocholo'),
            'redirect' => wc_get_page_permalink('myaccount')
        ]);
    }
    
    private function send_sms($phone, $message) {
        if (empty($this->api_key) || empty($this->sender_number)) {
            return new WP_Error('sms_config', __('تنظیمات SMS ناقص است. لطفا API Key و شماره ارسال کننده را در تنظیمات وارد کنید.', 'khoshtip-kocholo'));
        }
        
        // تبدیل شماره موبایل ایرانی به فرمت E.164
        $formatted_phone = $this->format_phone_e164($phone);
        $formatted_sender = $this->format_phone_e164($this->sender_number);
        
        // استفاده از IP Panel Edge API
        $response = wp_remote_post('https://edge.ippanel.com/v1/api/send', [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $this->api_key, // بدون Bearer
            ],
            'body' => json_encode([
                'sending_type' => 'webservice',
                'from_number' => $formatted_sender,
                'message' => $message,
                'params' => [
                    'recipients' => [$formatted_phone]
                ]
            ]),
        ]);
        
        if (is_wp_error($response)) {
            error_log('SMS API Error: ' . $response->get_error_message());
            return new WP_Error('sms_connection', __('خطا در اتصال به سرویس پیامک. لطفا دوباره تلاش کنید.', 'khoshtip-kocholo'));
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
        
        $error_message = $body['meta']['message'] ?? __('خطا در ارسال SMS', 'khoshtip-kocholo');
        return new WP_Error('sms_failed', $error_message);
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
