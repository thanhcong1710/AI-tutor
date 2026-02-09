# 🔧 FIX TELEGRAM BOT SDK VERSION

## ⚠️ VẤN ĐỀ

Telegram Bot SDK v3.14 chưa có bản stable, chỉ có dev version.

## ✅ GIẢI PHÁP

### **Option 1: Dùng v3.13 (Đã fix)**

Tôi đã downgrade xuống v3.13 trong composer.json.

**Chạy lại:**
```bash
composer update
```

---

### **Option 2: Nếu vẫn lỗi - Dùng dev version**

Nếu v3.13 cũng không có, dùng dev version:

```bash
# Cho phép dev packages
composer config minimum-stability dev
composer config prefer-stable true

# Cài telegram bot sdk
composer require telegram-bot-sdk/telegram-bot-sdk:dev-master
```

---

### **Option 3: Dùng v2 (Stable nhất)**

Nếu vẫn lỗi, dùng v2 (stable, nhiều người dùng):

```bash
composer require telegram-bot-sdk/telegram-bot-sdk:^2.0
```

**Lưu ý:** v2 có API hơi khác v3, nhưng vẫn hoạt động tốt.

---

## 🎯 KHUYẾN NGHỊ

**Thử theo thứ tự:**

1. ✅ **Chạy `composer update`** (đã fix v3.13)
2. Nếu lỗi → Dùng Option 2 (dev-master)
3. Nếu vẫn lỗi → Dùng Option 3 (v2.0)

---

**Hãy chạy `composer update` và cho tôi biết kết quả!** 🚀
