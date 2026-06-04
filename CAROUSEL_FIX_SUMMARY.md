# خلاصه تغییرات حل مشکل اسکرول افقی حراج

## مشکل اصلی
- اسکرول حراجی به صورت عمودی نمایش داده می‌شد نه افقی
- Swiper به CDN خارجی متصل بود و در شرایط بدون اینترنت جهانی کار نمی‌کرد

## حل‌های پیاده‌شده

### 1. **دانلود محلی Swiper** ✅
- فایل: `/Astra-child3/assets/libs/swiper-bundle.min.js` (151KB)
- فایل: `/Astra-child3/assets/libs/swiper-bundle.min.css` (19KB)
- Swiper 11.2.10 - آخرین نسخه پایدار

### 2. **ایجاد فایل CSS سفارشی** ✅
- فایل: `/Astra-child3/assets/css/swiper-override.css`
- قطعات قوی برای اجبار به جهت افقی
- پشتیبانی RTL برای فارسی
- تنظیمات responsive

### 3. **بروزرسانی فایل‌های PHP**

#### `/Astra-child3/functions.php`
- اضافه شدن لود CSS Swiper Override
- تأیید لود Swiper محلی

#### `/Astra-child3/template-parts/section-sales.php`
- اضافه شدن شرط برای نمایش/مخفی کردن اسکرول
- درست کردن ساختار HTML wrapper

### 4. **بروزرسانی JavaScript** ✅
- فایل: `/Astra-child3/assets/js/main.js`
- اضافه شدن `direction: "horizontal"` به تنظیمات Swiper
- اضافه شدن `resistance` و `touchRatio` برای بهتر کار
- اضافه شدن `console.log("[v0] Initializing Sales Swiper...")`

### 5. **اضافه شدن اختیار ادمین** ✅

#### فایل: `/Ko-kocholo/includes/admin/class-admin-tabs.php`
- متغیر: `$sale_carousel_enabled`
- تب: "نمایش اسکرول حراجی"
- Checkbox: فعال/غیرفعال کردن اسکرول در صفحه اصلی
- تابع save: بروزرسانی `k_sale_carousel_enabled`

#### صفحه اصلی
- بررسی خودکار اختیار قبل از نمایش اسکرول

### 6. **CSS بهبودی شامل:**
- `flex-direction: row !important` - اجبار به جهت افقی
- `flex-shrink: 0` - جلوگیری از کوچک شدن اسلاید‌ها
- `width: auto` - اسلاید‌ها اندازه خود را حفظ می‌کنند
- RTL Support - پشتیبانی از زبان‌های راست به چپ

## فایل‌های تغییر یافته

1. `/Astra-child3/functions.php`
2. `/Astra-child3/assets/css/components.css`
3. `/Astra-child3/assets/js/main.js`
4. `/Astra-child3/template-parts/section-sales.php`
5. `/Ko-kocholo/includes/admin/class-admin-tabs.php`

## فایل‌های ایجاد شده

1. `/Astra-child3/assets/css/swiper-override.css` (جدید)
2. `/Astra-child3/assets/libs/swiper-bundle.min.js` (دانلود شده)
3. `/Astra-child3/assets/libs/swiper-bundle.min.css` (دانلود شده)

## تنظیمات Swiper

```javascript
new Swiper(".sales-swiper", {
  direction: "horizontal",           // جهت افقی
  slidesPerView: 2,                 // 2 محصول در موبایل
  spaceBetween: 16,                 // فاصله 16px
  loop: true,                       // حلقه بی‌پایان
  speed: 3000,                      // سرعت حرکت
  autoplay: {
    delay: 0,                       // بدون تاخیر
    disableOnInteraction: false     // ادامه بعد از تعامل
  },
  freeMode: true,                   // حرکت آزاد
  freeModeMomentum: false,          // بدون لنگری
  breakpoints: {                    // تنظیمات responsive
    640: { slidesPerView: 2.5 },
    768: { slidesPerView: 3 },
    1024: { slidesPerView: 4 },
    1280: { slidesPerView: 5 }
  }
})
```

## اختیار ادمین

محل: **پلاگین Ko-kocholo** → **تب حراج**

- ☑️ نمایش دکمه حراجی (قبلی)
- 🎨 رنگ دکمه حراجی (قبلی)
- ☑️ **نمایش اسکرول حراجی** (جدید)

## تست کردن

1. به ادمین پلاگین بروید
2. تب "حراج" را باز کنید
3. "نمایش اسکرول حراجی" را فعال/غیرفعال کنید
4. تغییرات را ذخیره کنید
5. صفحه اصلی را رفرش کنید
6. اسکرول افقی باید نمایش داده شود (اگر فعال باشد)

## نکات مهم

- ✅ همه CDN‌های خارجی حذف شده‌اند
- ✅ Swiper محلی است
- ✅ کاملاً آفلاین کار می‌کند
- ✅ پشتیبانی فارسی (RTL)
- ✅ اختیار ادمین برای فعال/غیرفعال
