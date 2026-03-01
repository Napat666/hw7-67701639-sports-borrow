#  Task Checklist — Sports Borrowing System

> **กฎ:** อย่าเริ่ม Phase ถัดไปจนกว่า Phase ปัจจุบันจะครบ

---

## Phase 1: Database & Login ⬅️ เริ่มที่นี่

### 1.1 Project Setup
- [ ] สร้างโครงสร้างโฟลเดอร์ตาม `architecture.md`
- [ ] สร้าง `docker-compose.yml` และ `Dockerfile`
- [ ] สร้าง `.env.example` และ `.env`
- [ ] ทดสอบ `docker compose up -d` สำเร็จ

### 1.2 Database
- [ ] เขียน `database/schema.sql` จาก DDL ใน `architecture.md`
- [ ] Import schema ลง MySQL container สำเร็จ
- [ ] เขียน `database/seed.sql` — ข้อมูล admin + หมวดหมู่ + อุปกรณ์ตัวอย่าง
- [ ] ตรวจสอบ Foreign Keys ถูกต้องใน phpMyAdmin

### 1.3 PDO Connection
- [ ] เขียน `src/config/database.php` — PDO Singleton
- [ ] ทดสอบ connection สำเร็จ ไม่มี error
- [ ] ตั้งค่า error mode เป็น `PDO::ERRMODE_EXCEPTION`
- [ ] เขียน `src/config/app.php` — ค่าคงที่พื้นฐาน

### 1.4 Authentication
- [ ] เขียน `login.php` — UI ด้วย Tailwind CSS
- [ ] เขียน `api/auth/login.php` — รับ AJAX, ตรวจ username/password ด้วย `password_verify()`
- [ ] ตั้ง Session variables: `user_id`, `username`, `role`
- [ ] เขียน `includes/auth_check.php` — redirect ถ้าไม่มี session
- [ ] เขียน `logout.php` — `session_destroy()` + redirect
- [ ] ทดสอบ Login ด้วย admin / student สำเร็จ
- [ ] ทดสอบ Login ผิดรหัส → jQuery Confirm แสดง error

**🏁 Phase 1 Done เมื่อ:** Login/Logout ทำงานได้ และ session ถูกต้อง

---

## Phase 2: Equipment Management (CRUD)

### 2.1 Equipment List
- [ ] เขียน `pages/equipment/index.php` — ตารางแสดงอุปกรณ์ทั้งหมด
- [ ] เขียน `api/equipment/read.php` — ดึงข้อมูลด้วย PDO + ส่ง JSON
- [ ] โหลดตารางผ่าน AJAX ได้สำเร็จ
- [ ] เพิ่มช่องค้นหา (Search) แบบ Real-time

### 2.2 Create Equipment
- [ ] เขียน `pages/equipment/form.php` — ฟอร์มเพิ่มอุปกรณ์
- [ ] เขียน `api/equipment/create.php` — INSERT ด้วย Prepared Statement
- [ ] Validate ฝั่ง Server: ตรวจ required fields
- [ ] jQuery Confirm: ยืนยันก่อน Submit
- [ ] แสดง Success/Error Toast หลัง AJAX

### 2.3 Update Equipment
- [ ] ใช้ `form.php` ร่วมกัน (ตรวจ `$_GET['id']`)
- [ ] เขียน `api/equipment/update.php` — UPDATE ด้วย Prepared Statement
- [ ] โหลดข้อมูลเก่าเข้าฟอร์มผ่าน AJAX
- [ ] jQuery Confirm: ยืนยันก่อนบันทึก

### 2.4 Delete Equipment
- [ ] เขียน `api/equipment/delete.php` — Soft Delete (`is_active = 0`)
- [ ] jQuery Confirm: ยืนยันก่อนลบ
- [ ] ตรวจสอบ: ลบไม่ได้ถ้ามีการยืมค้างอยู่

**🏁 Phase 2 Done เมื่อ:** CRUD อุปกรณ์ทำงานครบ พร้อม Confirm Dialog

---

## Phase 3: Borrow & Return

### 3.1 Create Borrow Record
- [ ] เขียน `pages/borrow/form.php` — ฟอร์มยืมอุปกรณ์
- [ ] ดึงรายการอุปกรณ์ที่ `available_qty > 0` ผ่าน AJAX
- [ ] เขียน `api/borrow/create.php` — INSERT borrow_records + borrow_items
- [ ] อัปเดต `available_qty` ของอุปกรณ์อัตโนมัติ
- [ ] jQuery Confirm: ยืนยันรายการก่อนยืม

### 3.2 Borrow List
- [ ] เขียน `pages/borrow/index.php` — ตารางรายการยืมทั้งหมด
- [ ] กรองโดย status: pending / approved / returned / overdue
- [ ] Admin เห็นทุก record, Student เห็นแค่ของตัวเอง

### 3.3 Return Equipment
- [ ] เขียน `pages/borrow/return.php` — ฟอร์มบันทึกการคืน
- [ ] เขียน `api/borrow/return.php` — UPDATE return_date, status, คืน available_qty
- [ ] jQuery Confirm: ยืนยันก่อนบันทึกการคืน

**🏁 Phase 3 Done เมื่อ:** ยืม-คืนได้ครบวงจร และ available_qty ถูกต้อง

---

## Phase 4: Dashboard & Report

- [ ] เขียน `dashboard.php` — แสดงสถิติ: อุปกรณ์ทั้งหมด, กำลังยืม, เกินกำหนด
- [ ] การ์ดสรุปข้อมูล (Stat Cards) โหลดผ่าน AJAX
- [ ] รายการยืมล่าสุด 5 รายการ
- [ ] ตรวจสอบ overdue อัตโนมัติเมื่อ login

---

## Phase 5: Polish & Security

- [ ] ตรวจสอบทุก API ใส่ `auth_check.php`
- [ ] Role-based Access Control: Admin เท่านั้น CRUD อุปกรณ์ได้
- [ ] ป้องกัน XSS ด้วย `htmlspecialchars()` ทุก output
- [ ] ใส่ CSRF token ในทุก form
- [ ] ทดสอบ SQL Injection ด้วย Prepared Statements
- [ ] ทดสอบ Responsive บนมือถือ
- [ ] เขียน `seed.sql` ให้สมบูรณ์สำหรับ Demo
