#!/bin/bash
# اسکریپت دانلود منابع محلی برای محیط اینترانت

set -e

# رنگ‌ها برای خروجی
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}دانلود منابع محلی برای اینترانت${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"

# مسیر قالب
THEME_PATH="danikidsweb/Astra-child3"
FONTS_DIR="$THEME_PATH/assets/fonts"
LIBS_DIR="$THEME_PATH/assets/libs"

# ایجاد دایرکتوری‌ها
mkdir -p "$FONTS_DIR"
mkdir -p "$LIBS_DIR"

echo -e "${GREEN}✓ دایرکتوری‌ها ایجاد شدند${NC}"

# دانلود فونت Vazirmatn
echo -e "\n${YELLOW}[1/2] دانلود فونت Vazirmatn...${NC}"

FONT_VERSION="5.0.0"
FONT_BASE_URL="https://github.com/rastikerdar/vazirmatn/raw/main/fonts/webfonts"

FONT_FILES=(
    "Vazirmatn-Thin.woff2"
    "Vazirmatn-ExtraLight.woff2"
    "Vazirmatn-Light.woff2"
    "Vazirmatn-Regular.woff2"
    "Vazirmatn-Medium.woff2"
    "Vazirmatn-SemiBold.woff2"
    "Vazirmatn-Bold.woff2"
    "Vazirmatn-ExtraBold.woff2"
    "Vazirmatn-Black.woff2"
)

for font in "${FONT_FILES[@]}"; do
    echo -n "  دانلود $font... "
    if wget -q "$FONT_BASE_URL/$font" -O "$FONTS_DIR/$font"; then
        echo -e "${GREEN}✓${NC}"
    else
        echo -e "${RED}✗${NC}"
    fi
done

# دانلود Swiper
echo -e "\n${YELLOW}[2/2] دانلود Swiper...${NC}"

SWIPER_VERSION="11.0.0"
SWIPER_BASE_URL="https://cdn.jsdelivr.net/npm/swiper@${SWIPER_VERSION}/swiper-bundle"

echo -n "  دانلود swiper-bundle.min.css... "
if wget -q "$SWIPER_BASE_URL.min.css" -O "$LIBS_DIR/swiper-bundle.min.css"; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC}"
fi

echo -n "  دانلود swiper-bundle.min.js... "
if wget -q "$SWIPER_BASE_URL.min.js" -O "$LIBS_DIR/swiper-bundle.min.js"; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC}"
fi

# تأیید
echo -e "\n${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✓ تمام منابع دانلود شدند!${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"

# بررسی فایل‌های دانلود شده
echo -e "${YELLOW}بررسی فایل‌های دانلود شده:${NC}"
echo -e "  فونت‌ها (${FONTS_DIR}):"
ls -lh "$FONTS_DIR"/*.woff2 2>/dev/null | awk '{print "    - " $9 " (" $5 ")"}'

echo -e "\n  کتابخانه‌ها (${LIBS_DIR}):"
ls -lh "$LIBS_DIR"/*.{css,js} 2>/dev/null | awk '{print "    - " $9 " (" $5 ")"}'

echo -e "\n${GREEN}✓ تمام فایل‌ها آماده هستند!${NC}"
echo -e "${GREEN}✓ اکنون سایت خود را بارگذاری کنید و منابع محلی بارگذاری شوند.${NC}"
