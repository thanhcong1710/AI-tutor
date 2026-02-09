# 🚀 HƯỚNG DẪN SETUP GITHUB

## 📋 BƯỚC 1: TẠO REPOSITORY TRÊN GITHUB

### Option 1: Qua GitHub Website (Dễ nhất)

1. **Truy cập:** https://github.com/new

2. **Điền thông tin:**
   - **Repository name:** `ai_tutor`
   - **Description:** `AI Tutor - Multi-platform AI Learning Assistant`
   - **Visibility:** 
     - ✅ **Private** (nếu muốn giữ kín)
     - ⬜ Public (nếu muốn mọi người xem)
   - **Initialize:**
     - ⬜ **KHÔNG** tick "Add a README file" (vì đã có sẵn)
     - ⬜ **KHÔNG** tick ".gitignore" (vì đã có sẵn)
     - ⬜ **KHÔNG** tick "Choose a license" (sẽ thêm sau)

3. **Click:** "Create repository"

---

### Option 2: Qua GitHub CLI (Nhanh hơn)

```bash
# Cài GitHub CLI (nếu chưa có)
brew install gh

# Login
gh auth login

# Tạo repo
gh repo create ai_tutor --private --description "AI Tutor - Multi-platform AI Learning Assistant"
```

---

## 📋 BƯỚC 2: INIT GIT VÀ PUSH CODE

### 1. Init Git repository

```bash
cd /Users/mac24h/Documents/docker-work/src/ai_tutor

# Init git
git init

# Add all files
git add .

# First commit
git commit -m "Initial commit: Laravel 11 + AI Tutor setup"
```

### 2. Connect to GitHub

```bash
# Thay YOUR_USERNAME bằng username GitHub của bạn
git remote add origin https://github.com/YOUR_USERNAME/ai_tutor.git

# Hoặc dùng SSH (khuyến nghị)
git remote add origin git@github.com:YOUR_USERNAME/ai_tutor.git
```

### 3. Push code lên GitHub

```bash
# Push to main branch
git branch -M main
git push -u origin main
```

---

## 📋 BƯỚC 3: SETUP GITHUB SECRETS (Cho CI/CD)

Nếu bạn muốn setup CI/CD sau này, cần thêm secrets:

1. **Vào:** https://github.com/YOUR_USERNAME/ai_tutor/settings/secrets/actions

2. **Thêm secrets:**
   - `OPENAI_API_KEY`
   - `TELEGRAM_BOT_TOKEN`
   - `DISCORD_BOT_TOKEN`
   - `AWS_ACCESS_KEY_ID`
   - `AWS_SECRET_ACCESS_KEY`

---

## 📋 BƯỚC 4: TẠO BRANCHES

### Tạo development branch

```bash
# Tạo dev branch
git checkout -b develop
git push -u origin develop

# Tạo feature branch
git checkout -b feature/telegram-bot
git push -u origin feature/telegram-bot
```

### Branch strategy

```
main (production)
  ↓
develop (staging)
  ↓
feature/telegram-bot
feature/discord-bot
feature/web-app
```

---

## 📋 BƯỚC 5: SETUP BRANCH PROTECTION (Optional)

1. **Vào:** https://github.com/YOUR_USERNAME/ai_tutor/settings/branches

2. **Add rule:**
   - Branch name pattern: `main`
   - ✅ Require pull request reviews before merging
   - ✅ Require status checks to pass before merging

---

## 📋 BƯỚC 6: TẠO LICENSE

```bash
# Tạo MIT License
cat > LICENSE << 'EOF'
MIT License

Copyright (c) 2026 [Your Name]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
EOF

# Commit
git add LICENSE
git commit -m "Add MIT License"
git push
```

---

## 📋 BƯỚC 7: TẠO .env.example

```bash
# Copy .env sang .env.example (remove sensitive data)
cp .env .env.example

# Edit .env.example - xóa các giá trị nhạy cảm
# Chỉ giữ lại keys, không giữ values
```

**Ví dụ .env.example:**

```env
APP_NAME="AI Tutor"
APP_ENV=local
APP_KEY=
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_tutor
DB_USERNAME=root
DB_PASSWORD=

OPENAI_API_KEY=
TELEGRAM_BOT_TOKEN=
DISCORD_BOT_TOKEN=
```

```bash
# Commit
git add .env.example
git commit -m "Add .env.example"
git push
```

---

## 📋 BƯỚC 8: VERIFY

Kiểm tra repository trên GitHub:

1. **Truy cập:** https://github.com/YOUR_USERNAME/ai_tutor

2. **Kiểm tra:**
   - ✅ Code đã được push
   - ✅ README.md hiển thị đẹp
   - ✅ .gitignore hoạt động (không có .env, vendor/)
   - ✅ LICENSE có sẵn

---

## 🎯 QUICK COMMANDS

### Làm việc hàng ngày

```bash
# Pull latest code
git pull origin main

# Tạo feature branch mới
git checkout -b feature/new-feature

# Add & commit changes
git add .
git commit -m "Add new feature"

# Push to GitHub
git push origin feature/new-feature

# Merge vào develop (sau khi review)
git checkout develop
git merge feature/new-feature
git push origin develop
```

---

## 📞 TROUBLESHOOTING

### Lỗi: Permission denied (publickey)

**Giải pháp:** Setup SSH key

```bash
# Generate SSH key
ssh-keygen -t ed25519 -C "your.email@example.com"

# Copy public key
cat ~/.ssh/id_ed25519.pub

# Add to GitHub: https://github.com/settings/keys
```

### Lỗi: Remote origin already exists

```bash
# Remove old remote
git remote remove origin

# Add new remote
git remote add origin git@github.com:YOUR_USERNAME/ai_tutor.git
```

---

## ✅ DONE!

Repository đã sẵn sàng! 🎉

**Next steps:**
1. Invite collaborators (nếu có)
2. Setup CI/CD (GitHub Actions)
3. Setup issue templates
4. Setup pull request templates

---

**Happy coding! 🚀**
