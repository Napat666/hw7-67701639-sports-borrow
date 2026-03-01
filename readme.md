# 🏸 Sports Borrowing System
> ระบบยืม-คืนอุปกรณ์กีฬา สำหรับบุคลากรและนักศึกษา

## 📋 ภาพรวมโปรเจกต์

ระบบจัดการการยืม-คืนอุปกรณ์กีฬา พัฒนาด้วย PHP (PDO), MySQL, AJAX, jQuery, jQuery Confirm และ Tailwind CSS เพื่อให้ใช้งานง่าย ลื่นไหล และปลอดภัย

### ฟีเจอร์หลัก
- **Authentication** — Login/Logout พร้อม Session Management
- **Dashboard** — ภาพรวมสถานะอุปกรณ์และการยืม
- **จัดการอุปกรณ์** — CRUD สำหรับ Admin
- **ยืม-คืน** — บันทึกการยืมและการคืนอุปกรณ์
- **รายงาน** — ประวัติการยืม-คืนย้อนหลัง

### Tech Stack

| Layer     | Technology                        |
|-----------|-----------------------------------|
| Frontend  | Tailwind CSS, jQuery, jQuery Confirm |
| Backend   | PHP 8.x, PDO                      |
| Database  | MySQL 8.x                         |
| Container | Docker, Docker Compose            |

---

## 🐳 วิธีรันด้วย Docker

### ข้อกำหนดเบื้องต้น
- Docker >= 24.x
- Docker Compose >= 2.x

### ขั้นตอน

```bash
# 1. Clone โปรเจกต์
git clone https://github.com/your-org/sports-borrowing-system.git
cd sports-borrowing-system

# 2. Copy ไฟล์ environment
cp .env.example .env

# 3. รัน containers
docker compose up -d

# 4. Import โครงสร้างฐานข้อมูล
docker compose exec db mysql -u root -proot sports_db < database/schema.sql

# 5. (Optional) Import ข้อมูลตัวอย่าง
docker compose exec db mysql -u root -proot sports_db < database/seed.sql
```

### เปิดเบราว์เซอร์
```
http://localhost:8080
```

### บัญชีเริ่มต้น (สำหรับทดสอบ)

| Role    | Username  | Password  |
|---------|-----------|-----------|
| Admin   | admin     | admin1234 |
| Student | student01 | 1234      |

### หยุด/ลบ Containers
```bash
docker compose down        # หยุด
docker compose down -v     # หยุด + ลบ volumes
```

---

## 📁 โครงสร้างโปรเจกต์ (สรุป)

```
sports-borrowing-system/
├── docker-compose.yml
├── .env.example
├── database/
│   ├── schema.sql
│   └── seed.sql
├── src/
│   ├── config/
│   ├── pages/
│   ├── api/
│   ├── includes/
│   └── assets/
└── docs/
    ├── readme.md
    ├── architecture.md
    ├── task.md
    └── CONTRIBUTING.md
```

> ดูโครงสร้างแบบละเอียดได้ที่ [`architecture.md`](./architecture.md)

---

## 📄 License
MIT License — © 2025 Your Organization
