# 🚀 AI Tutor - Quick Start Guide

## ✅ Setup Hoàn Tất!

Database đã được tạo và seed thành công. Server đang chạy!

---

## 🔐 Tài Khoản Test

| Role | Email | Password |
|------|-------|----------|
| 👑 Admin | admin@aitutor.com | password |
| 🎓 Teacher | teacher@aitutor.com | password |
| 📚 Student | student@aitutor.com | password |

---

## 🌐 Truy Cập Ứng Dụng

### Web Interface
```
http://localhost:8000
```

### Quick Links
- **Login:** http://localhost:8000/login
- **Lessons:** http://localhost:8000/lessons
- **Dashboard:** http://localhost:8000/dashboard

---

## 🛠️ Helper Script

Tôi đã tạo script `ai-tutor.sh` để dễ dàng chạy các lệnh Laravel:

### Cách sử dụng:

```bash
# Start server
./ai-tutor.sh serve

# Run migrations
./ai-tutor.sh migrate

# Reset database (drop all + seed)
./ai-tutor.sh migrate:fresh

# Clear cache
./ai-tutor.sh cache:clear

# Open Tinker
./ai-tutor.sh tinker

# Run tests
./ai-tutor.sh test

# View logs
./ai-tutor.sh logs

# Run any artisan command
./ai-tutor.sh artisan route:list
./ai-tutor.sh artisan queue:work

# Run composer
./ai-tutor.sh composer require package/name

# Open bash in container
./ai-tutor.sh bash
```

---

## 📋 Quy Trình Học Tập

### 1. Đăng nhập
```
http://localhost:8000/login
```
- Click **"Login as Student"** (nút màu xanh)

### 2. Xem danh sách bài học
```
http://localhost:8000/lessons
```

### 3. Bắt đầu học
Có 2 cách:
- **🖥️ Học trên Web:** Click nút "Học trên Web" (giao diện đẹp, đầy đủ tính năng)
- **📱 Telegram:** Click nút "Telegram" (học trên mobile)

### 4. Giao diện học tập Web
```
http://localhost:8000/lessons/1/learn
```

**Tính năng:**
- ✅ Sidebar: Danh sách segments
- ✅ Main content: Nội dung bài học
- ✅ Audio player: Nghe giảng bài (nếu có)
- ✅ Quiz: Làm bài tập, nhận feedback AI
- ✅ AI Chat: Hỏi đáp với AI Trợ Giảng

---

## 🗄️ Database Info

**Connection:**
```
Host: host.docker.internal
Port: 33066
Database: ai_tutor
Username: root
Password: secret
```

**Tables Created:**
- ✅ users
- ✅ lessons
- ✅ lesson_segments
- ✅ lesson_questions
- ✅ learning_sessions
- ✅ student_answers
- ✅ learning_analytics
- ✅ lesson_assignments
- ✅ personal_access_tokens (Sanctum)
- ✅ cache, jobs, failed_jobs

**Sample Data:**
- 1 Admin
- 2 Teachers
- 2 Students
- 3 Lessons (English, Math, Logic)
- 3 Segments per lesson
- 3 Questions per lesson
- 3 Assignments
- 1 Completed session

---

## 🔧 Troubleshooting

### Server không chạy?
```bash
# Kiểm tra container
docker ps | grep php82

# Restart server
docker exec -d php82 php /var/www/html/ai_tutor/artisan serve --host=0.0.0.0 --port=8000
```

### Database lỗi?
```bash
# Reset database
./ai-tutor.sh migrate:fresh
```

### Cache issues?
```bash
# Clear all cache
./ai-tutor.sh cache:clear
```

### View logs
```bash
# Laravel logs
./ai-tutor.sh logs

# Or manually
docker exec php82 tail -f /var/www/html/ai_tutor/storage/logs/laravel.log
```

---

## 📊 API Endpoints

### Authentication
```
POST /api/login
POST /api/register
POST /api/logout
```

### Student APIs
```
GET  /api/student/lessons/assigned
GET  /api/student/segments/{id}
POST /api/student/chat
POST /api/student/sessions/start
POST /api/student/sessions/{id}/answer
GET  /api/student/progress
```

### Teacher APIs
```
POST /api/teacher/lessons
GET  /api/teacher/lessons
GET  /api/teacher/dashboard
POST /api/teacher/lessons/{id}/assign
```

---

## 🎯 Next Steps

### 1. Test Quick Login
- Vào `/login`
- Click "Login as Student"
- Kiểm tra redirect đến dashboard

### 2. Test Web Learning
- Vào `/lessons`
- Click "Học trên Web"
- Kiểm tra giao diện học tập

### 3. Test AI Chat
- Trong giao diện học tập
- Mở AI Chat panel
- Hỏi: "Giải thích thêm về phần này"

### 4. Test Quiz
- Trả lời câu hỏi
- Kiểm tra AI feedback

---

## 🔑 Environment Variables

Đảm bảo `.env` có các biến sau:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=host.docker.internal
DB_PORT=33066
DB_DATABASE=ai_tutor
DB_USERNAME=root
DB_PASSWORD=secret

# OpenAI (for AI features)
OPENAI_API_KEY=your_key_here

# Google TTS (optional)
GOOGLE_APPLICATION_CREDENTIALS=/path/to/credentials.json

# Telegram (optional)
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_BOT_USERNAME=your_bot_username
```

---

## 📞 Support

Nếu gặp vấn đề:
1. Check logs: `./ai-tutor.sh logs`
2. Clear cache: `./ai-tutor.sh cache:clear`
3. Reset DB: `./ai-tutor.sh migrate:fresh`
4. Restart server: `./ai-tutor.sh serve`

---

**🎉 Happy Learning with AI Tutor!**
