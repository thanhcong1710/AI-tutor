# 🚀 Quick Test Guide - Student Dashboard

## ✅ Đã Fix: Auto Redirect

**Thay đổi:**
- Student truy cập `/lessons` → Auto redirect → `/student/dashboard`
- Admin/Teacher truy cập `/lessons` → Vẫn hiển thị lessons list

---

## 🧪 Test Steps

### 1. Login as Student
```
URL: http://ai-tutor.local/login
Click: "Login as Student" (nút màu xanh)
```

### 2. Auto Redirect
```
Login thành công → Auto redirect to:
http://ai-tutor.local/student/dashboard
```

### 3. Xem Dashboard Mới
Anh sẽ thấy:

#### Top Section:
- ✅ Welcome message: "Xin chào, Alice Student! 👋"
- ✅ Avatar tròn với chữ cái đầu
- ✅ Điểm tích lũy: 0 XP

#### Stats Cards (3 cards):
- 📚 **Khóa học đang học**: Số lượng bài được giao
- ✅ **Hoàn thành**: 0
- 🔥 **Streak**: 0 ngày

#### Course Cards:
Mỗi khóa học có:
- **Gradient header** (màu đẹp)
- **Subject badge** (English, Math, etc.)
- **Level & Duration**
- **Progress bar**
- **2 nút chọn chế độ học:**
  - 🖥️ **Học trên Web** (màu xanh)
  - 📱 **Học trên Telegram** (viền xanh)

#### Bottom:
- 💡 **Learning Tips** banner (màu tím gradient)

---

## 🎯 Test Learning Modes

### Test 1: Web Learning
```
1. Click "Học trên Web" button
2. Should open: /lessons/{id}/learn
3. See full learning interface
```

### Test 2: Telegram Learning
```
1. Click "Học trên Telegram" button
2. Should open: Telegram deep link
3. Opens Telegram app
```

---

## 🔄 Navigation Flow

```
Login (Student)
    ↓
Auto redirect to /student/dashboard
    ↓
See assigned courses
    ↓
Choose learning mode:
    ├─→ Web → Full interface
    └─→ Telegram → Mobile app
```

---

## 📊 Expected Data

### Sample Courses (from seeder):
1. **English Basics**
   - Subject: English
   - Level: beginner
   - 3 segments

2. **Math Fundamentals**
   - Subject: Math
   - Level: intermediate
   - 3 segments

3. **Logic & Reasoning**
   - Subject: Logic
   - Level: advanced
   - 3 segments

---

## 🐛 Troubleshooting

### Issue: Still seeing old interface
**Solution:**
```bash
# Clear browser cache
Ctrl/Cmd + Shift + R (hard refresh)

# Or clear Laravel cache
./ai-tutor.sh cache:clear
```

### Issue: No courses showing
**Reason:** Chưa có assignments
**Solution:**
```bash
# Login as teacher
# Assign lessons to student
# Or run seeder again
./ai-tutor.sh seed
```

### Issue: 404 on /student/dashboard
**Solution:**
```bash
# Clear route cache
./ai-tutor.sh artisan route:clear

# Check routes
./ai-tutor.sh artisan route:list | grep student
```

---

## 📸 Expected UI

### Dashboard Layout:
```
┌─────────────────────────────────────────┐
│  Xin chào, Alice Student! 👋      [A]   │
│  Sẵn sàng học tập hôm nay chưa?   0 XP  │
├─────────────────────────────────────────┤
│  [📚 3]  [✅ 0]  [🔥 0 ngày]           │
├─────────────────────────────────────────┤
│  Khóa học của tôi                3 khóa │
├─────────────────────────────────────────┤
│  ┌───────┐  ┌───────┐  ┌───────┐       │
│  │Course │  │Course │  │Course │       │
│  │  1    │  │  2    │  │  3    │       │
│  │[Web]  │  │[Web]  │  │[Web]  │       │
│  │[Tele] │  │[Tele] │  │[Tele] │       │
│  └───────┘  └───────┘  └───────┘       │
├─────────────────────────────────────────┤
│  💡 Mẹo học tập hiệu quả                │
└─────────────────────────────────────────┘
```

---

## ✅ Checklist

- [ ] Login as student
- [ ] See new dashboard (not lessons list)
- [ ] See 3 stats cards
- [ ] See assigned courses
- [ ] Each course has 2 buttons
- [ ] Click "Học trên Web" → Opens learning interface
- [ ] Click "Học trên Telegram" → Opens Telegram
- [ ] See learning tips at bottom

---

## 🎨 Design Features to Verify

### Colors:
- ✅ Blue gradient for Web button
- ✅ White with blue border for Telegram button
- ✅ Different gradient for each course card
- ✅ Purple gradient for tips banner

### Animations:
- ✅ Hover effect on course cards
- ✅ Shadow transition
- ✅ Scale effect on hover

### Responsive:
- ✅ Mobile: 1 column
- ✅ Tablet: 2 columns
- ✅ Desktop: 3 columns

---

## 🔗 URLs to Test

```
Login:     http://ai-tutor.local/login
Dashboard: http://ai-tutor.local/student/dashboard
Learning:  http://ai-tutor.local/lessons/1/learn
```

---

**Bây giờ refresh trang và test thử nhé!** 🚀
