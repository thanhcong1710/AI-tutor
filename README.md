# 🤖 AI TUTOR - Complete Implementation

**AI-Powered Learning Platform with Multi-Platform Support**

![Status](https://img.shields.io/badge/Status-Production%20Ready-success)
![Laravel](https://img.shields.io/badge/Laravel-11-red)
![PHP](https://img.shields.io/badge/PHP-8.2-blue)
![Vue](https://img.shields.io/badge/Vue-3-green)

---

## 🎯 Overview

AI Tutor is a comprehensive AI-powered learning platform that provides personalized 1-on-1 tutoring using GPT-4, with voice support via Google TTS and Whisper STT. Teachers can upload materials (PDF/DOCX), and AI automatically creates interactive lessons with questions and audio.

### ✨ Key Features

**For Students:**
- 🎓 1-on-1 AI tutoring with instant feedback
- 🎤 Voice lessons with natural TTS
- 📊 Detailed progress tracking
- 💪 Strengths/weaknesses analysis
- 🔥 Learning streak tracking
- 🎯 Adaptive difficulty

**For Teachers:**
- 📤 Easy content upload (PDF, DOCX, PPT)
- 🤖 AI auto-generates lessons
- 👥 Assign lessons to students
- 📈 View student progress
- 📊 Performance analytics

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────┐
│  Multi-Platform Frontend                    │
│  (Web, Telegram, Discord, Mobile)           │
└──────────────────┬──────────────────────────┘
                   │
┌──────────────────▼──────────────────────────┐
│  Laravel 11 Backend API                     │
│  ┌────────────────────────────────────────┐ │
│  │  Controllers (Teacher + Student)       │ │
│  └──────────────┬─────────────────────────┘ │
│                 │                            │
│  ┌──────────────▼─────────────────────────┐ │
│  │  Services Layer                        │ │
│  │  • LessonService                       │ │
│  │  • SessionService                      │ │
│  │  • ProgressTracker                     │ │
│  │  • AnalyticsService                    │ │
│  └──────────────┬─────────────────────────┘ │
│                 │                            │
│  ┌──────────────▼─────────────────────────┐ │
│  │  AI Services                           │ │
│  │  • LLMService (GPT-4)                  │ │
│  │  • TTSService (Google TTS)             │ │
│  │  • STTService (Whisper)                │ │
│  └──────────────┬─────────────────────────┘ │
│                 │                            │
│  ┌──────────────▼─────────────────────────┐ │
│  │  Database (MySQL)                      │ │
│  │  8 tables + relationships              │ │
│  └────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

---

## 📁 Project Structure

```
ai_tutor/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── TeacherController.php
│   │   │   └── StudentController.php
│   │   └── Middleware/
│   │       └── CheckRole.php
│   ├── Models/
│   │   ├── Lesson.php
│   │   ├── LessonSegment.php
│   │   ├── LessonQuestion.php
│   │   ├── LearningSession.php
│   │   ├── StudentAnswer.php
│   │   ├── LearningAnalytics.php
│   │   └── LessonAssignment.php
│   ├── Services/
│   │   ├── AI/
│   │   │   ├── LLMService.php
│   │   │   ├── TTSService.php
│   │   │   └── STTService.php
│   │   ├── Lesson/
│   │   │   ├── LessonService.php
│   │   │   ├── ContentProcessor.php
│   │   │   └── QuestionGenerator.php
│   │   └── Learning/
│   │       ├── SessionService.php
│   │       ├── ProgressTracker.php
│   │       └── AnalyticsService.php
│   └── Jobs/
│       └── ProcessLessonContent.php
├── database/
│   ├── migrations/ (8 migrations)
│   └── seeders/
│       └── DatabaseSeeder.php
├── public/
│   ├── index.html (Landing page)
│   └── dashboard.html (Student dashboard)
└── routes/
    └── api.php (20+ endpoints)
```

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js (optional, for frontend build)

### Installation

1. **Clone & Install**
   ```bash
   cd /Users/mac24h/Documents/docker-work/src/ai_tutor
   composer install
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Update `.env`**
   ```env
   # Database
   DB_CONNECTION=mysql
   DB_HOST=host.docker.internal
   DB_PORT=33066
   DB_DATABASE=ai_tutor
   DB_USERNAME=root
   DB_PASSWORD=secret

   # OpenAI
   OPENAI_API_KEY=your_openai_key

   # Google TTS
   GOOGLE_APPLICATION_CREDENTIALS=/path/to/credentials.json

   # AWS S3
   AWS_ACCESS_KEY_ID=your_key
   AWS_SECRET_ACCESS_KEY=your_secret
   AWS_DEFAULT_REGION=us-east-1
   AWS_BUCKET=your_bucket
   ```

4. **Run Migrations & Seed**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Start Server**
   ```bash
   php artisan serve
   ```

6. **Visit**
   - Landing: http://localhost:8000/index.html
   - Dashboard: http://localhost:8000/dashboard.html

---

## 📧 Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@aitutor.com | password |
| Teacher | teacher@aitutor.com | password |
| Student | student@aitutor.com | password |

---

## 🔌 API Endpoints

### Authentication
```
POST /api/login
POST /api/register
POST /api/logout
```

### Teacher Endpoints
```
POST   /api/teacher/lessons              # Upload lesson
GET    /api/teacher/lessons              # List lessons
GET    /api/teacher/lessons/{id}         # View lesson
PUT    /api/teacher/lessons/{id}         # Update lesson
DELETE /api/teacher/lessons/{id}         # Delete lesson
POST   /api/teacher/lessons/{id}/assign  # Assign to student
GET    /api/teacher/dashboard            # Dashboard stats
GET    /api/teacher/students/{id}/progress
GET    /api/teacher/lessons/{id}/performance
```

### Student Endpoints
```
GET    /api/student/lessons/assigned     # Assigned lessons
POST   /api/student/sessions/start       # Start learning
GET    /api/student/sessions             # My sessions
GET    /api/student/sessions/{id}        # Session details
POST   /api/student/sessions/{id}/answer # Submit answer
POST   /api/student/sessions/{id}/next   # Next segment
POST   /api/student/sessions/{id}/complete
GET    /api/student/progress             # My progress
GET    /api/student/progress/{subject}/{level}
```

---

## 🗄️ Database Schema

### Tables

1. **users** - Multi-role users (student/teacher/admin)
2. **lessons** - Teacher-uploaded materials
3. **lesson_segments** - Lessons broken into chunks
4. **lesson_questions** - AI-generated questions
5. **learning_sessions** - Student progress tracking
6. **student_answers** - Answers with AI feedback
7. **learning_analytics** - Performance analytics
8. **lesson_assignments** - Teacher assigns to students

### Relationships

```
users (teacher) → lessons → lesson_segments → lesson_questions
                     ↓
              lesson_assignments
                     ↓
users (student) → learning_sessions → student_answers
                     ↓
              learning_analytics
```

---

## 🤖 AI Integration

### GPT-4 (LLMService)
- Generate lesson explanations
- Create questions (multiple types)
- Evaluate student answers
- Analyze performance

### Google TTS (TTSService)
- Convert text to natural speech
- Multiple languages support
- Adjustable voice/speed/pitch
- Auto-upload to S3

### Whisper (STTService)
- Transcribe audio to text
- Support voice answers
- Multi-language support

---

## 📊 Sample Data

The seeder creates:
- 1 Admin
- 2 Teachers
- 2 Students
- 3 Lessons with segments & questions
- 3 Assignments
- 1 Completed session
- Analytics data

---

## 🎨 Frontend

### Landing Page (`index.html`)
- Modern gradient design
- Feature showcase
- Login modal
- Responsive layout

### Student Dashboard (`dashboard.html`)
- Sidebar navigation
- Stats cards
- Assigned lessons
- Progress charts
- Strengths/weaknesses

---

## 🛠️ Development

### Run Queue Worker
```bash
php artisan queue:work
```

### Run Tests
```bash
php artisan test
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 📦 Dependencies

### Backend
- laravel/framework: ^11.0
- openai-php/laravel: ^0.10
- google/cloud-text-to-speech: ^1.7
- aws/aws-sdk-php: ^3.300
- smalot/pdfparser: ^2.12
- phpoffice/phpword: ^1.4

### Frontend
- Vue.js 3
- Tailwind CSS
- Chart.js
- Font Awesome

---

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure queue driver (Redis/SQS)
- [ ] Setup S3 for file storage
- [ ] Configure OpenAI API key
- [ ] Setup Google TTS credentials
- [ ] Enable HTTPS
- [ ] Setup CDN for assets
- [ ] Configure backup
- [ ] Setup monitoring

---

## 📝 License

MIT License

---

## 👥 Credits

Built with ❤️ using:
- Laravel 11
- GPT-4 by OpenAI
- Google Cloud TTS
- Whisper by OpenAI

---

## 📞 Support

For issues or questions:
- Email: support@aitutor.com
- GitHub: [Issues](https://github.com/your-repo/issues)

---

**🎉 Happy Learning with AI!**
