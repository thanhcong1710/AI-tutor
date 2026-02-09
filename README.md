# 🤖 AI Tutor - Multi-platform AI Learning Assistant

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

**AI Tutor** là một nền tảng học tập thông minh sử dụng AI (GPT-4) để giảng dạy 1-1 cho học sinh. Hệ thống hỗ trợ đa nền tảng: Telegram Bot, Discord Bot, Web App, và Mobile App.

---

## ✨ Tính năng

### 🎓 Cho Học sinh
- ✅ Học 1-1 với AI Tutor
- ✅ AI giảng bài tự động (text + voice)
- ✅ AI đặt câu hỏi và đánh giá
- ✅ Theo dõi tiến độ học tập
- ✅ Báo cáo chi tiết điểm mạnh/yếu

### 👨‍🏫 Cho Giáo viên
- ✅ Upload tài liệu (PDF, DOCX, PPT)
- ✅ AI tự động tạo bài học
- ✅ Giao bài cho học sinh
- ✅ Xem báo cáo tiến độ

### 🌐 Multi-platform
- ✅ **Telegram Bot** - Học qua Telegram
- ✅ **Discord Bot** - Học qua Discord
- ✅ **Web App** - Học trên trình duyệt
- ✅ **Mobile App** - Học trên điện thoại

---

## 🏗️ Kiến trúc

```
┌─────────────────────────────────────────────────────────┐
│         PLATFORMS (Input Layer)                         │
│   Telegram  │  Discord  │  Web App  │  Mobile App      │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│         CONTROLLERS (Thin Layer)                        │
│   - Validate input                                      │
│   - Call Services                                       │
│   - Format output                                       │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│         SERVICES (Business Logic)                       │
│   - Platform-agnostic                                   │
│   - Reusable across all platforms                      │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│         MODELS & DATABASE                               │
└─────────────────────────────────────────────────────────┘
```

**Nguyên tắc:**
- ✅ Business logic trong `Services/` (platform-agnostic)
- ✅ Controllers chỉ là thin layer
- ✅ Dễ thêm platform mới

---

## 🛠️ Tech Stack

### Backend
- **Framework:** Laravel 11
- **Language:** PHP 8.2+
- **Database:** MySQL 8.0+
- **Cache/Queue:** Redis
- **Storage:** AWS S3 / MinIO

### AI Services
- **LLM:** OpenAI GPT-4o
- **TTS:** Google Cloud Text-to-Speech
- **STT:** OpenAI Whisper

### Frontend
- **Web:** Next.js + Tailwind CSS
- **Mobile:** React Native

### DevOps
- **Hosting:** AWS / DigitalOcean
- **CI/CD:** GitHub Actions
- **Monitoring:** Sentry, DataDog

---

## 📋 Yêu cầu hệ thống

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Redis
- Node.js >= 18 (cho frontend)

---

## 🚀 Cài đặt

### 1. Clone repository

```bash
git clone https://github.com/your-username/ai_tutor.git
cd ai_tutor
```

### 2. Cài đặt dependencies

```bash
composer install
npm install
```

### 3. Cấu hình environment

```bash
cp .env.example .env
php artisan key:generate
```

**Cập nhật .env:**

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_tutor
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# OpenAI
OPENAI_API_KEY=your_openai_key_here

# Telegram Bot
TELEGRAM_BOT_TOKEN=your_telegram_token_here

# Discord Bot
DISCORD_BOT_TOKEN=your_discord_token_here
```

### 4. Tạo database

```bash
mysql -u root -p -e "CREATE DATABASE ai_tutor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Chạy migrations

```bash
php artisan migrate
```

### 6. Chạy development server

```bash
# Backend
php artisan serve

# Queue worker
php artisan queue:work

# Frontend (nếu có)
npm run dev
```

---

## 📚 Documentation

- [Project Structure](PROJECT_STRUCTURE.md)
- [Setup Progress](SETUP_PROGRESS.md)
- [API Documentation](docs/API.md) (Coming soon)
- [Telegram Bot Guide](docs/TELEGRAM.md) (Coming soon)
- [Discord Bot Guide](docs/DISCORD.md) (Coming soon)

---

## 🎯 Roadmap

### Phase 1: MVP (Tháng 1-2) ✅
- [x] Setup project
- [x] Database schema
- [ ] Telegram Bot MVP
- [ ] OpenAI integration
- [ ] Google TTS integration

### Phase 2: Beta (Tháng 3-4)
- [ ] Discord Bot
- [ ] Web App
- [ ] Analytics dashboard
- [ ] Payment integration

### Phase 3: Launch (Tháng 5-6)
- [ ] Mobile App
- [ ] Advanced features
- [ ] Performance optimization
- [ ] Production deployment

---

## 🤝 Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👥 Team

- **Developer:** [Your Name]
- **Email:** your.email@example.com
- **Website:** [https://yourwebsite.com](https://yourwebsite.com)

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com)
- [OpenAI](https://openai.com)
- [Google Cloud](https://cloud.google.com)
- [Telegram Bot API](https://core.telegram.org/bots/api)
- [Discord API](https://discord.com/developers/docs)

---

## 📞 Support

Nếu bạn gặp vấn đề hoặc có câu hỏi, vui lòng:
- Tạo [Issue](https://github.com/your-username/ai_tutor/issues)
- Email: support@example.com

---

**Made with ❤️ in Vietnam 🇻🇳**
