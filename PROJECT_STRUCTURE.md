# 📁 AI TUTOR - PROJECT STRUCTURE

Cấu trúc thư mục được thiết kế theo nguyên tắc **Platform-Agnostic Architecture**

```
ai_tutor/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── TelegramSetWebhookCommand.php
│   │       └── DiscordSetWebhookCommand.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── Telegram/
│   │   │   │   │   ├── WebhookController.php
│   │   │   │   │   ├── LessonController.php
│   │   │   │   │   └── UserController.php
│   │   │   │   │
│   │   │   │   ├── Discord/
│   │   │   │   │   ├── WebhookController.php
│   │   │   │   │   ├── LessonController.php
│   │   │   │   │   └── UserController.php
│   │   │   │   │
│   │   │   │   ├── Web/
│   │   │   │   │   ├── LessonController.php
│   │   │   │   │   └── UserController.php
│   │   │   │   │
│   │   │   │   └── Mobile/
│   │   │   │       ├── LessonController.php
│   │   │   │       └── UserController.php
│   │   │   │
│   │   │   └── Controller.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── VerifyTelegramWebhook.php
│   │   │   └── VerifyDiscordWebhook.php
│   │   │
│   │   └── Requests/
│   │       ├── Telegram/
│   │       └── Discord/
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Lesson.php
│   │   ├── LessonSegment.php
│   │   ├── LessonQuestion.php
│   │   ├── LearningSession.php
│   │   ├── StudentAnswer.php
│   │   └── LearningAnalytics.php
│   │
│   ├── Services/                    # ⭐ CORE BUSINESS LOGIC (Platform-agnostic)
│   │   ├── AI/
│   │   │   ├── LLMService.php       # GPT-4 integration
│   │   │   ├── TTSService.php       # Text-to-Speech
│   │   │   └── STTService.php       # Speech-to-Text
│   │   │
│   │   ├── Lesson/
│   │   │   ├── LessonService.php
│   │   │   ├── ContentProcessor.php
│   │   │   └── QuestionGenerator.php
│   │   │
│   │   ├── Learning/
│   │   │   ├── SessionService.php
│   │   │   ├── ProgressTracker.php
│   │   │   └── AnalyticsService.php
│   │   │
│   │   ├── User/
│   │   │   └── UserService.php
│   │   │
│   │   └── Platform/                # Platform adapters
│   │       ├── TelegramAdapter.php
│   │       ├── DiscordAdapter.php
│   │       ├── WebAdapter.php
│   │       └── MobileAdapter.php
│   │
│   ├── Jobs/
│   │   ├── ProcessLessonContent.php
│   │   ├── GenerateAudio.php
│   │   └── EvaluateAnswer.php
│   │
│   ├── Events/
│   │   ├── LessonCreated.php
│   │   ├── SessionStarted.php
│   │   └── AnswerEvaluated.php
│   │
│   └── Listeners/
│       └── SendNotification.php
│
├── database/
│   ├── migrations/
│   │   ├── 2026_02_09_000001_create_users_table.php
│   │   ├── 2026_02_09_000002_create_lessons_table.php
│   │   ├── 2026_02_09_000003_create_lesson_segments_table.php
│   │   ├── 2026_02_09_000004_create_lesson_questions_table.php
│   │   ├── 2026_02_09_000005_create_learning_sessions_table.php
│   │   ├── 2026_02_09_000006_create_student_answers_table.php
│   │   └── 2026_02_09_000007_create_learning_analytics_table.php
│   │
│   ├── factories/
│   └── seeders/
│
├── routes/
│   ├── api.php                      # Main API routes
│   ├── telegram.php                 # Telegram webhook routes
│   ├── discord.php                  # Discord webhook routes
│   └── web.php                      # Web routes
│
├── config/
│   ├── telegram.php                 # Telegram config
│   ├── discord.php                  # Discord config
│   ├── openai.php                   # OpenAI config
│   └── services.php                 # External services config
│
└── storage/
    ├── app/
    │   ├── audio/                   # Temporary audio files
    │   └── lessons/                 # Uploaded lesson files
    └── logs/
```

## 🎯 NGUYÊN TẮC THIẾT KẾ

### 1. **Platform-Agnostic Services**
- Tất cả business logic trong `app/Services/`
- Không phụ thuộc vào platform cụ thể
- Telegram, Discord, Web, Mobile đều dùng chung

### 2. **Thin Controllers**
- Controllers chỉ làm:
  - Validate input
  - Call Services
  - Format output cho platform

### 3. **Adapter Pattern**
- Mỗi platform có adapter riêng
- Convert platform-specific format ↔ unified format

### 4. **Queue Jobs**
- AI processing (GPT-4, TTS) chạy background
- Không block user request

### 5. **Event-Driven**
- Events cho các hành động quan trọng
- Dễ mở rộng (thêm listeners mới)

## 📝 NEXT STEPS

1. ✅ Cấu trúc thư mục
2. ⏳ Tạo Migrations
3. ⏳ Tạo Models
4. ⏳ Tạo Services
5. ⏳ Tạo Controllers
6. ⏳ Setup Routes
7. ⏳ Tích hợp Telegram Bot
8. ⏳ Tích hợp OpenAI

Bạn đã chạy `composer install` xong chưa?
