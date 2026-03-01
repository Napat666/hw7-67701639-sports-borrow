# 🏗️ Architecture — Sports Borrowing System

## 1. โครงสร้างไฟล์ (File Structure)

```
sports-borrowing-system/
├── docker-compose.yml              # Docker services definition
├── .env.example                    # Template สำหรับ environment variables
├── database/
│   ├── schema.sql                  # DDL สร้างตาราง
│   └── seed.sql                    # ข้อมูลตัวอย่าง
├── src/
│   ├── config/
│   │   ├── database.php            # PDO Connection singleton
│   │   └── app.php                 # ค่าคงที่ทั่วไป (BASE_URL, SALT ฯลฯ)
│   ├── includes/
│   │   ├── header.php              # HTML head + Navbar
│   │   ├── footer.php              # JS scripts + Footer
│   │   ├── auth_check.php          # ตรวจสอบ Session ก่อนเข้าหน้า
│   │   └── functions.php           # Helper functions ทั่วไป
│   ├── pages/
│   │   ├── login.php               # หน้า Login
│   │   ├── logout.php              # Destroy session + redirect
│   │   ├── dashboard.php           # หน้าแรกหลัง login
│   │   ├── equipment/
│   │   │   ├── index.php           # รายการอุปกรณ์ทั้งหมด
│   │   │   ├── form.php            # ฟอร์ม Add / Edit (ใช้ร่วมกัน)
│   │   │   └── detail.php          # รายละเอียดอุปกรณ์
│   │   └── borrow/
│   │       ├── index.php           # รายการการยืมทั้งหมด
│   │       ├── form.php            # ฟอร์มยืมอุปกรณ์
│   │       └── return.php          # หน้าบันทึกการคืน
│   ├── api/                        # Endpoints รับ AJAX requests
│   │   ├── equipment/
│   │   │   ├── create.php
│   │   │   ├── read.php
│   │   │   ├── update.php
│   │   │   └── delete.php
│   │   └── borrow/
│   │       ├── create.php
│   │       ├── read.php
│   │       └── return.php
│   └── assets/
│       ├── css/
│       │   └── app.css             # Custom styles (override Tailwind)
│       ├── js/
│       │   └── app.js              # Global JS utilities
│       └── img/
└── docs/
    ├── readme.md
    ├── architecture.md
    ├── task.md
    └── CONTRIBUTING.md
```

---

## 2. Database Design

### ERD (Text)

```
users ──────< borrow_records >────── equipment
                    │
               borrow_items
```

### SQL Schema

```sql
-- ============================
-- DATABASE: sports_db
-- ============================
CREATE DATABASE IF NOT EXISTS sports_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE sports_db;

-- ----------------------------
-- ตาราง: users
-- บุคลากรและนักศึกษาที่ใช้ระบบ
-- ----------------------------
CREATE TABLE users (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)     NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL COMMENT 'Hashed ด้วย password_hash()',
    full_name   VARCHAR(100)    NOT NULL,
    role        ENUM('admin','staff','student') NOT NULL DEFAULT 'student',
    is_active   TINYINT(1)      NOT NULL DEFAULT 1,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- ตาราง: equipment_categories
-- หมวดหมู่อุปกรณ์ เช่น ลูกบอล, แร็กเกต
-- ----------------------------
CREATE TABLE equipment_categories (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)    NOT NULL UNIQUE,
    description TEXT,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- ตาราง: equipment
-- อุปกรณ์กีฬาแต่ละชิ้น
-- ----------------------------
CREATE TABLE equipment (
    id            INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    category_id   INT UNSIGNED    NOT NULL,
    name          VARCHAR(150)    NOT NULL,
    code          VARCHAR(50)     NOT NULL UNIQUE COMMENT 'รหัสอุปกรณ์ เช่น BK-001',
    total_qty     INT UNSIGNED    NOT NULL DEFAULT 1,
    available_qty INT UNSIGNED    NOT NULL DEFAULT 1,
    condition_note TEXT           COMMENT 'หมายเหตุสภาพอุปกรณ์',
    image_path    VARCHAR(255),
    is_active     TINYINT(1)      NOT NULL DEFAULT 1,
    created_at    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_eq_category FOREIGN KEY (category_id)
        REFERENCES equipment_categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ----------------------------
-- ตาราง: borrow_records
-- รายการการยืม 1 ครั้ง = 1 record
-- ----------------------------
CREATE TABLE borrow_records (
    id              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    borrower_id     INT UNSIGNED    NOT NULL COMMENT 'FK → users.id',
    approved_by     INT UNSIGNED    COMMENT 'FK → users.id (admin/staff)',
    borrow_date     DATE            NOT NULL,
    due_date        DATE            NOT NULL,
    return_date     DATE            COMMENT 'NULL = ยังไม่คืน',
    status          ENUM('pending','approved','returned','overdue') NOT NULL DEFAULT 'pending',
    note            TEXT,
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_br_borrower  FOREIGN KEY (borrower_id) REFERENCES users(id),
    CONSTRAINT fk_br_approver  FOREIGN KEY (approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ----------------------------
-- ตาราง: borrow_items
-- รายการอุปกรณ์ในแต่ละ borrow_record
-- ----------------------------
CREATE TABLE borrow_items (
    id               INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    borrow_record_id INT UNSIGNED    NOT NULL,
    equipment_id     INT UNSIGNED    NOT NULL,
    qty              INT UNSIGNED    NOT NULL DEFAULT 1,
    return_condition TEXT            COMMENT 'สภาพอุปกรณ์ตอนคืน',
    CONSTRAINT fk_bi_record    FOREIGN KEY (borrow_record_id) REFERENCES borrow_records(id) ON DELETE CASCADE,
    CONSTRAINT fk_bi_equipment FOREIGN KEY (equipment_id)     REFERENCES equipment(id)
) ENGINE=InnoDB;
```

---

## 3. Docker Compose Services

```yaml
# docker-compose.yml (โครงร่าง)
services:
  app:
    build: .
    ports: ["8080:80"]
    volumes: ["./src:/var/www/html"]
    depends_on: [db]
    env_file: .env

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: sports_db
    ports: ["3306:3306"]
    volumes: ["db_data:/var/lib/mysql"]

  phpmyadmin:
    image: phpmyadmin:latest
    ports: ["8081:80"]
    environment:
      PMA_HOST: db

volumes:
  db_data:
```

---

## 4. Data Flow (AJAX Pattern)

```
Browser (jQuery)
    │
    │  $.ajax({ url: '/api/equipment/create.php', method: 'POST', data: {...} })
    ▼
API Endpoint (PHP)
    │  ① รับ $_POST / php://input
    │  ② Validate input
    │  ③ PDO Prepared Statement → MySQL
    │  ④ ส่ง JSON response กลับ
    ▼
Browser (Callback)
    │  ① รับ JSON { success: true/false, message: '...' }
    │  ② jQuery Confirm แสดงผล Alert
    │  ③ Reload ตาราง / Redirect
```
