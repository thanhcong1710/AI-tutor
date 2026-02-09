# 🔧 FIX MYSQL DOCKER CONNECTION

## ⚠️ VẤN ĐỀ

MySQL Docker container chỉ expose port **33060** (MySQL X Protocol), không expose port **3306** (classic protocol).

Navicat cần port **3306** để connect.

---

## ✅ GIẢI PHÁP

### **Option 1: Thêm port mapping 3306 (Khuyến nghị)**

```bash
# Stop container hiện tại
docker stop mysql

# Remove container (data vẫn giữ nguyên nếu dùng volume)
docker rm mysql

# Chạy lại với port 3306
docker run -d \
  --name mysql \
  -e MYSQL_ROOT_PASSWORD=secret \
  -e MYSQL_DATABASE=homestead \
  -e MYSQL_USER=homestead \
  -e MYSQL_PASSWORD=secret \
  -p 3306:3306 \
  -p 33060:33060 \
  --restart unless-stopped \
  -v mysql_data:/var/lib/mysql \
  mariadb:10.5.8
```

**Sau đó connect Navicat:**
- Host: `127.0.0.1`
- Port: `3306`
- Username: `root`
- Password: `secret`

---

### **Option 2: Dùng docker-compose (Tốt nhất)**

Tạo file `docker-compose.yml`:

```yaml
version: '3.8'

services:
  mysql:
    image: mariadb:10.5.8
    container_name: mysql
    restart: unless-stopped
    ports:
      - "3306:3306"
      - "33060:33060"
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: homestead
      MYSQL_USER: homestead
      MYSQL_PASSWORD: secret
    volumes:
      - mysql_data:/var/lib/mysql

  redis:
    image: redis:7-alpine
    container_name: redis
    restart: unless-stopped
    ports:
      - "6379:6379"

volumes:
  mysql_data:
```

**Chạy:**
```bash
docker-compose up -d
```

---

### **Option 3: Connect qua port 33060 (Tạm thời)**

Nếu không muốn restart container, dùng MySQL Shell hoặc MySQL Workbench (hỗ trợ X Protocol):

**Hoặc exec vào container:**
```bash
docker exec -it mysql mysql -uroot -psecret
```

**Tạo database:**
```sql
CREATE DATABASE ai_tutor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON ai_tutor.* TO 'homestead'@'%';
FLUSH PRIVILEGES;
```

---

## 🎯 KHUYẾN NGHỊ

**Dùng Option 1 hoặc 2** để có port 3306 chuẩn.

**Navicat settings:**
```
Connection Name: AI Tutor MySQL
Host: 127.0.0.1
Port: 3306
Username: root
Password: secret
Database: ai_tutor
```

---

## 📝 UPDATE .ENV

Sau khi fix, update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_tutor
DB_USERNAME=root
DB_PASSWORD=secret
```

---

Bạn muốn tôi giúp restart container với port 3306 không? 🚀
