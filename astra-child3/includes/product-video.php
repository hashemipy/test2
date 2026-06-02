<?php
/**
 * Product Video Feature for WooCommerce - Astra Child Theme
 * اضافه کردن قابلیت ویدیو به محصولات ووکامرس
 * تطبیق شده برای قالب Astra
 *
 * @package Khoshtip_Kocholo
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add video meta box to product edit page
 * اضافه کردن فیلد ویدیو به صفحه ویرایش محصول
 */
function astra_add_product_video_meta_box() {
    add_meta_box(
        'astra_product_video',
        '🎬 ویدیو محصول',
        'astra_product_video_meta_box_callback',
        'product',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'astra_add_product_video_meta_box');

/**
 * Meta box callback - Display video upload fields
 */
function astra_product_video_meta_box_callback($post) {
    wp_nonce_field('astra_product_video_nonce', 'astra_video_nonce');
    
    $video_type = get_post_meta($post->ID, '_astra_video_type', true);
    $video_url = get_post_meta($post->ID, '_astra_video_url', true);
    $video_media_id = get_post_meta($post->ID, '_astra_video_media_id', true);
    $video_thumbnail = get_post_meta($post->ID, '_astra_video_thumbnail', true);
    
    ?>
    <div class="astra-video-meta-box">
        <style>
            .astra-video-meta-box { padding: 10px 0; }
            .astra-video-meta-box label { display: block; margin-bottom: 5px; font-weight: 600; }
            .astra-video-meta-box input[type="text"],
            .astra-video-meta-box input[type="url"] { width: 100%; margin-bottom: 10px; }
            .astra-video-meta-box .video-type-selector { margin-bottom: 15px; }
            .astra-video-meta-box .video-type-selector label { display: inline-block; margin-left: 15px; font-weight: normal; }
            .astra-video-meta-box .video-field { display: none; margin-bottom: 15px; }
            .astra-video-meta-box .video-field.active { display: block; }
            .astra-video-meta-box .button { margin-top: 5px; }
            .astra-video-meta-box .video-preview { margin-top: 10px; padding: 10px; background: #f5f5f5; border-radius: 5px; }
            .astra-video-meta-box .video-preview video { max-width: 100%; height: auto; }
            .astra-video-meta-box .remove-video { color: #d63638; cursor: pointer; margin-top: 10px; display: inline-block; }
            .astra-video-meta-box .thumbnail-preview { margin-top: 10px; }
            .astra-video-meta-box .thumbnail-preview img { max-width: 100%; height: auto; border-radius: 5px; }
        </style>
        
        <div class="video-type-selector">
            <label>
                <input type="radio" name="astra_video_type" value="media" <?php checked($video_type, 'media'); ?> <?php checked($video_type, ''); ?>>
                از رسانه وردپرس
            </label>
            <label>
                <input type="radio" name="astra_video_type" value="external" <?php checked($video_type, 'external'); ?>>
                لینک خارجی
            </label>
        </div>
        
        <!-- Media Library Video -->
        <div class="video-field media-field <?php echo ($video_type === 'media' || $video_type === '') ? 'active' : ''; ?>">
            <label>انتخاب ویدیو از رسانه:</label>
            <input type="hidden" name="astra_video_media_id" id="astra_video_media_id" value="<?php echo esc_attr($video_media_id); ?>">
            <button type="button" class="button astra-upload-video-btn">انتخاب ویدیو</button>
            
            <?php if ($video_media_id && $video_type === 'media') : 
                $video_src = wp_get_attachment_url($video_media_id);
            ?>
                <div class="video-preview" id="astra-video-preview">
                    <video controls>
                        <source src="<?php echo esc_url($video_src); ?>" type="video/mp4">
                    </video>
                    <span class="remove-video" onclick="astraRemoveVideo()">✕ حذف ویدیو</span>
                </div>
            <?php else : ?>
                <div class="video-preview" id="astra-video-preview" style="display: none;">
                    <video controls>
                        <source src="" type="video/mp4">
                    </video>
                    <span class="remove-video" onclick="astraRemoveVideo()">✕ حذف ویدیو</span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- External URL Video -->
        <div class="video-field external-field <?php echo ($video_type === 'external') ? 'active' : ''; ?>">
            <label>لینک ویدیو (YouTube, Vimeo, یا مستقیم):</label>
            <input type="url" name="astra_video_url" id="astra_video_url" value="<?php echo esc_url($video_url); ?>" placeholder="https://...">
            <p class="description">پشتیبانی از YouTube, Vimeo و لینک مستقیم mp4</p>
        </div>
        
        <!-- Video Thumbnail -->
        <div class="thumbnail-field" style="margin-top: 15px; border-top: 1px solid #ddd; padding-top: 15px;">
            <label>تصویر پیش‌نمایش ویدیو (اختیاری):</label>
            <input type="hidden" name="astra_video_thumbnail" id="astra_video_thumbnail" value="<?php echo esc_attr($video_thumbnail); ?>">
            <button type="button" class="button astra-upload-thumbnail-btn">انتخاب تصویر</button>
            
            <?php if ($video_thumbnail) : ?>
                <div class="thumbnail-preview" id="astra-thumbnail-preview">
                    <img src="<?php echo esc_url(wp_get_attachment_url($video_thumbnail)); ?>" alt="Video Thumbnail">
                    <br>
                    <span class="remove-video" onclick="astraRemoveThumbnail()">✕ حذف تصویر</span>
                </div>
            <?php else : ?>
                <div class="thumbnail-preview" id="astra-thumbnail-preview" style="display: none;">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/placeholder.svg" alt="Video Thumbnail">
                    <br>
                    <span class="remove-video" onclick="astraRemoveThumbnail()">✕ حذف تصویر</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Toggle video type fields
        $('input[name="astra_video_type"]').on('change', function() {
            $('.video-field').removeClass('active');
            if ($(this).val() === 'media') {
                $('.media-field').addClass('active');
            } else {
                $('.external-field').addClass('active');
            }
        });
        
        // Media Library for Video
        var videoFrame;
        $('.astra-upload-video-btn').on('click', function(e) {
            e.preventDefault();
            
            if (videoFrame) {
                videoFrame.open();
                return;
            }
            
            videoFrame = wp.media({
                title: 'انتخاب ویدیو',
                button: { text: 'استفاده از این ویدیو' },
                library: { type: 'video' },
                multiple: false
            });
            
            videoFrame.on('select', function() {
                var attachment = videoFrame.state().get('selection').first().toJSON();
                $('#astra_video_media_id').val(attachment.id);
                $('#astra-video-preview video source').attr('src', attachment.url);
                $('#astra-video-preview video')[0].load();
                $('#astra-video-preview').show();
            });
            
            videoFrame.open();
        });
        
        // Media Library for Thumbnail
        var thumbFrame;
        $('.astra-upload-thumbnail-btn').on('click', function(e) {
            e.preventDefault();
            
            if (thumbFrame) {
                thumbFrame.open();
                return;
            }
            
            thumbFrame = wp.media({
                title: 'انتخاب تصویر پیش‌نمایش',
                button: { text: 'استفاده از این تصویر' },
                library: { type: 'image' },
                multiple: false
            });
            
            thumbFrame.on('select', function() {
                var attachment = thumbFrame.state().get('selection').first().toJSON();
                $('#astra_video_thumbnail').val(attachment.id);
                $('#astra-thumbnail-preview img').attr('src', attachment.url);
                $('#astra-thumbnail-preview').show();
            });
            
            thumbFrame.open();
        });
    });
    
    function astraRemoveVideo() {
        jQuery('#astra_video_media_id').val('');
        jQuery('#astra-video-preview').hide();
    }
    
    function astraRemoveThumbnail() {
        jQuery('#astra_video_thumbnail').val('');
        jQuery('#astra-thumbnail-preview').hide();
    }
    </script>
    <?php
}

/**
 * Save product video meta data
 */
function astra_save_product_video_meta($post_id) {
    if (!isset($_POST['astra_video_nonce']) || !wp_verify_nonce($_POST['astra_video_nonce'], 'astra_product_video_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save video type
    if (isset($_POST['astra_video_type'])) {
        update_post_meta($post_id, '_astra_video_type', sanitize_text_field($_POST['astra_video_type']));
    }
    
    // Save video URL
    if (isset($_POST['astra_video_url'])) {
        update_post_meta($post_id, '_astra_video_url', esc_url_raw($_POST['astra_video_url']));
    }
    
    // Save media ID
    if (isset($_POST['astra_video_media_id'])) {
        update_post_meta($post_id, '_astra_video_media_id', absint($_POST['astra_video_media_id']));
    }
    
    // Save thumbnail
    if (isset($_POST['astra_video_thumbnail'])) {
        update_post_meta($post_id, '_astra_video_thumbnail', absint($_POST['astra_video_thumbnail']));
    }
}
add_action('save_post_product', 'astra_save_product_video_meta');

/**
 * Enqueue media scripts for product edit page
 */
function astra_enqueue_product_video_scripts($hook) {
    global $post_type;
    
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        if ($post_type === 'product') {
            wp_enqueue_media();
        }
    }
}
add_action('admin_enqueue_scripts', 'astra_enqueue_product_video_scripts');

/**
 * Get product video data
 */
function astra_get_product_video($product_id = null) {
    if (!$product_id) {
        global $product;
        $product_id = $product->get_id();
    }
    
    $video_type = get_post_meta($product_id, '_astra_video_type', true);
    $video_url = get_post_meta($product_id, '_astra_video_url', true);
    $video_media_id = get_post_meta($product_id, '_astra_video_media_id', true);
    $video_thumbnail = get_post_meta($product_id, '_astra_video_thumbnail', true);
    
    if (empty($video_type) && empty($video_url) && empty($video_media_id)) {
        return false;
    }
    
    $video_data = array(
        'type' => $video_type,
        'url' => '',
        'thumbnail' => '',
        'embed_type' => 'direct'
    );
    
    // Get video URL
    if ($video_type === 'media' && $video_media_id) {
        $video_data['url'] = wp_get_attachment_url($video_media_id);
    } elseif ($video_type === 'external' && $video_url) {
        $video_data['url'] = $video_url;
        
        // Detect embed type
        if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
            $video_data['embed_type'] = 'youtube';
            $video_data['url'] = astra_get_youtube_embed_url($video_url);
        } elseif (strpos($video_url, 'vimeo.com') !== false) {
            $video_data['embed_type'] = 'vimeo';
            $video_data['url'] = astra_get_vimeo_embed_url($video_url);
        } elseif (strpos($video_url, 'aparat.com') !== false) {
            $video_data['embed_type'] = 'aparat';
            $video_data['url'] = astra_get_aparat_embed_url($video_url);
        }
    }
    
    // Get thumbnail
    if ($video_thumbnail) {
        $video_data['thumbnail'] = wp_get_attachment_url($video_thumbnail);
    } elseif ($video_data['embed_type'] === 'youtube') {
        // Get YouTube thumbnail
        $video_id = astra_get_youtube_id($video_url);
        if ($video_id) {
            $video_data['thumbnail'] = 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg';
        }
    }
    
    // Fallback thumbnail
    if (empty($video_data['thumbnail'])) {
        $video_data['thumbnail'] = get_the_post_thumbnail_url($product_id, 'medium');
    }
    
    return $video_data;
}

/**
 * Convert YouTube URL to embed URL
 */
function astra_get_youtube_embed_url($url) {
    $video_id = astra_get_youtube_id($url);
    if ($video_id) {
        return 'https://www.youtube.com/embed/' . $video_id . '?autoplay=1&rel=0';
    }
    return $url;
}

/**
 * Get YouTube video ID from URL
 */
function astra_get_youtube_id($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    return false;
}

/**
 * Convert Vimeo URL to embed URL
 */
function astra_get_vimeo_embed_url($url) {
    $pattern = '/vimeo\.com\/(\d+)/';
    if (preg_match($pattern, $url, $matches)) {
        return 'https://player.vimeo.com/video/' . $matches[1] . '?autoplay=1';
    }
    return $url;
}

/**
 * Get Aparat video ID from URL
 */
function astra_get_aparat_embed_url($url) {
    $pattern = '/aparat\.com\/v\/([a-zA-Z0-9]+)/';
    if (preg_match($pattern, $url, $matches)) {
        return 'https://www.aparat.com/video/video/embed/videohash/' . $matches[1] . '/vt/frame';
    }
    return $url;
}

/**
 * Check if product has video
 */
function astra_product_has_video($product_id = null) {
    $video = astra_get_product_video($product_id);
    return $video && !empty($video['url']);
}
