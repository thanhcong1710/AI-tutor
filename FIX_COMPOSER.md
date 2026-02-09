# 🔧 FIX COMPOSER LOCK FILE

## ⚠️ VẤN ĐỀ

Composer.lock chưa được cập nhật với các packages mới trong composer.json

## ✅ GIẢI PHÁP

Chạy lệnh sau để cập nhật composer.lock và cài đặt packages:

```bash
cd /Users/mac24h/Documents/docker-work/src/ai_tutor
composer update
```

**Hoặc nếu muốn cài từng package:**

```bash
composer require laravel/sanctum
composer require predis/predis
composer require telegram-bot-sdk/telegram-bot-sdk
composer require openai-php/laravel
composer require google/cloud-text-to-speech
composer require aws/aws-sdk-php
composer require --dev spatie/laravel-ignition
```

## 📝 KHUYẾN NGHỊ

**Dùng `composer update`** - Nhanh hơn, cài tất cả cùng lúc!

```bash
composer update
```

**Thời gian:** ~3-5 phút (tùy tốc độ mạng)

---

## 🎯 SAU KHI CHẠY XONG

Kiểm tra xem packages đã được cài chưa:

```bash
composer show | grep -E "telegram|openai|google|aws|sanctum|predis"
```

**Kết quả mong đợi:**
```
aws/aws-sdk-php
google/cloud-text-to-speech
laravel/sanctum
openai-php/laravel
predis/predis
telegram-bot-sdk/telegram-bot-sdk
```

---

Hãy chạy `composer update` và cho tôi biết kết quả! ✅
