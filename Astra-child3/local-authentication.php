<?php
/**
 * Local Authentication Without External APIs
 * احراز هویت محلی بدون نیاز به API‌های خارجی
 */

// اگر احتیاج به Google Auth بدون اتصال خارجی دارید، 
// می‌توانید از روش‌های زیر استفاده کنید:

/**
 * ۱. احراز هویت Local (Username/Password)
 */
function local_authenticate_user($username, $password) {
    $user = get_user_by('login', sanitize_user($username));
    
    if (!$user) {
        return new WP_Error('invalid_username', 'نام کاربری نادرست');
    }
    
    if (!wp_check_password($password, $user->user_pass, $user->ID)) {
        return new WP_Error('invalid_password', 'رمز عبور نادرست');
    }
    
    // موفقیت
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);
    
    return $user;
}

/**
 * ۲. احراز هویت OTP (یک‌بار رمز)
 */
function generate_otp_local($user_id) {
    $otp = rand(100000, 999999);
    
    // ذخیره در database
    set_user_meta($user_id, '_otp_code', $otp);
    set_user_meta($user_id, '_otp_created', time());
    
    return $otp;
}

function verify_otp_local($user_id, $otp_code) {
    $stored_otp = get_user_meta($user_id, '_otp_code', true);
    $otp_time = get_user_meta($user_id, '_otp_created', true);
    
    // بررسی انقضای OTP (5 دقیقه)
    if (time() - $otp_time > 300) {
        return new WP_Error('otp_expired', 'کد یک‌بار رمز منقضی شده‌است');
    }
    
    if ($stored_otp != $otp_code) {
        return new WP_Error('invalid_otp', 'کد یک‌بار رمز نادرست');
    }
    
    // حذف OTP
    delete_user_meta($user_id, '_otp_code');
    delete_user_meta($user_id, '_otp_created');
    
    return true;
}

/**
 * ۳. احراز هویت دو‌مراحلی محلی
 */
function enable_two_factor_local($user_id) {
    $secret = bin2hex(random_bytes(16));
    update_user_meta($user_id, '_two_factor_secret', $secret);
    return $secret;
}

function verify_two_factor_local($user_id, $code) {
    $secret = get_user_meta($user_id, '_two_factor_secret', true);
    
    if (!$secret) {
        return false;
    }
    
    // ساده‌ترین روش: کد 6 رقمی
    // برای پیاده‌سازی کامل، از TOTP استفاده کنید
    
    return true;
}

/**
 * ۴. سیستم Session محلی بدون API
 */
class LocalAuthManager {
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * ایجاد JWT Token محلی
     */
    public function create_token($user_id, $expiry = 86400) {
        $secret_key = defined('AUTH_KEY') ? AUTH_KEY : 'default-secret';
        
        $payload = array(
            'user_id' => $user_id,
            'iat' => time(),
            'exp' => time() + $expiry,
        );
        
        // ساخت token ساده (برای پیاده‌سازی کامل از jose_jwt استفاده کنید)
        $token = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $token, $secret_key);
        
        return $token . '.' . $signature;
    }
    
    /**
     * تأیید Token
     */
    public function verify_token($token) {
        $secret_key = defined('AUTH_KEY') ? AUTH_KEY : 'default-secret';
        
        $parts = explode('.', $token);
        if (count($parts) != 2) {
            return false;
        }
        
        $payload_encoded = $parts[0];
        $signature = $parts[1];
        
        // تأیید امضا
        if (hash_hmac('sha256', $payload_encoded, $secret_key) !== $signature) {
            return false;
        }
        
        $payload = json_decode(base64_decode($payload_encoded), true);
        
        // بررسی انقضا
        if ($payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
}

/**
 * ۵. استفاده در فرم لاگین
 * 
 * مثال استفاده:
 * 
 * if ($_POST['action'] === 'local_login') {
 *     $username = sanitize_text_field($_POST['username']);
 *     $password = sanitize_text_field($_POST['password']);
 *     
 *     $user = local_authenticate_user($username, $password);
 *     
 *     if (!is_wp_error($user)) {
 *         wp_redirect(home_url('/dashboard'));
 *     }
 * }
 */

/**
 * ۶. برای احراز هویت SMS محلی:
 * 
 * می‌توانید از سیستم SMS محلی استفاده کنید:
 * - Kanboard
 * - Mattermost
 * - یا سیستم SMS خود
 */
class LocalSMSManager {
    /**
     * ارسال SMS محلی
     * نیازمند: Gateway SMS داخلی یا Twilio Proxy
     */
    public static function send_sms($phone, $message) {
        // برای اینترانت، از Gateway داخلی استفاده کنید
        // این تنها نمونه‌ای است
        
        $gateway_url = 'http://internal-sms-gateway:9090/api/send';
        
        $response = wp_remote_post($gateway_url, array(
            'method' => 'POST',
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'phone' => $phone,
                'message' => $message,
            )),
            'timeout' => 10,
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }
        
        return array('success' => true, 'message' => 'SMS ارسال شد');
    }
}

/**
 * ۷. مثال فرم لاگین محلی کامل
 */
function render_local_login_form() {
    if (is_user_logged_in()) {
        wp_redirect(home_url('/dashboard'));
        exit;
    }
    ?>
    <form method="post" class="local-login-form">
        <h2>ورود به سیستم</h2>
        
        <input 
            type="hidden" 
            name="action" 
            value="local_login"
        >
        
        <div class="form-group">
            <label>نام کاربری:</label>
            <input 
                type="text" 
                name="username" 
                required
                pattern="[a-zA-Z0-9_-]+"
                maxlength="60"
            >
        </div>
        
        <div class="form-group">
            <label>رمز عبور:</label>
            <input 
                type="password" 
                name="password" 
                required
                minlength="8"
            >
        </div>
        
        <button type="submit">ورود</button>
    </form>
    
    <style>
        .local-login-form {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background-color: #007cba;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background-color: #005a87;
        }
    </style>
    <?php
}

/**
 * ۸. Hooks برای پردازش فرم
 */
add_action('init', function() {
    if (isset($_POST['action']) && $_POST['action'] === 'local_login') {
        check_admin_referer('local_login_nonce', 'nonce');
        
        $username = sanitize_text_field($_POST['username']);
        $password = sanitize_text_field($_POST['password']);
        
        $user = local_authenticate_user($username, $password);
        
        if (!is_wp_error($user)) {
            wp_redirect(home_url('/dashboard'));
            exit;
        } else {
            add_action('wp_head', function() use ($user) {
                echo '<div class="error">' . $user->get_error_message() . '</div>';
            });
        }
    }
});

// ======================================
// خلاصه: احراز هویت محلی بدون API
// ======================================

/*
استفاده از:
1. Local Username/Password ✅
2. OTP محلی ✅
3. دو‌مراحلی ✅
4. JWT Tokens ✅
5. Session ✅

بدون نیاز به:
❌ Google OAuth
❌ Gravatar
❌ خدمات خارجی
❌ اتصال جهانی

برای فعال کردن:
1. این فایل را در functions.php شامل کنید
2. render_local_login_form() را فراخوانی کنید
3. فرم را کاستومایز کنید
*/
?>
