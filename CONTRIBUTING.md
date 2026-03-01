# 🤝 Contributing Guide — Sports Borrowing System

## 1. กฎการตั้งชื่อตัวแปร (Naming Conventions)

### PHP

| ประเภท | รูปแบบ | ตัวอย่าง |
|--------|--------|---------|
| ตัวแปรทั่วไป | `$camelCase` | `$borrowDate`, `$totalQty` |
| ตัวแปร Array/Object | `$camelCase` | `$equipmentList`, `$userData` |
| ค่าคงที่ | `UPPER_SNAKE_CASE` | `BASE_URL`, `MAX_BORROW_DAYS` |
| ฟังก์ชัน | `camelCase()` | `getUserById()`, `formatDate()` |
| Class | `PascalCase` | `Database`, `BorrowRecord` |
| ตัวแปร PDO Statement | `$stmt` | `$stmt = $pdo->prepare(...)` |
| ตัวแปร PDO Connection | `$pdo` | `$pdo = Database::getInstance()` |

```php
// ✅ ถูกต้อง
$borrowDate   = date('Y-m-d');
$equipmentList = [];
define('MAX_BORROW_DAYS', 7);

function getBorrowById(int $id): array { ... }

// ❌ ผิด
$BorrowDate   = date('Y-m-d');   // PascalCase สำหรับตัวแปรธรรมดา
$equipment_list = [];              // snake_case ใน PHP
```

### JavaScript / jQuery

| ประเภท | รูปแบบ | ตัวอย่าง |
|--------|--------|---------|
| ตัวแปร | `camelCase` | `borrowId`, `equipmentData` |
| ฟังก์ชัน | `camelCase` | `loadEquipmentTable()` |
| ค่าคงที่ | `UPPER_SNAKE_CASE` | `API_BASE_URL` |
| jQuery Object | ขึ้นต้นด้วย `$` | `$table`, `$btnSubmit` |

```javascript
// ✅ ถูกต้อง
const borrowId     = $('#borrow-id').val();
const $btnSubmit   = $('#btn-submit');
const API_BASE_URL = '/api';

function loadEquipmentTable() { ... }

// ❌ ผิด
const BorrowId    = ...;   // PascalCase สำหรับตัวแปรธรรมดา
const btn_submit  = ...;   // snake_case ใน JS
```

### MySQL (ชื่อตาราง/คอลัมน์)

| ประเภท | รูปแบบ | ตัวอย่าง |
|--------|--------|---------|
| ตาราง | `snake_case` พหูพจน์ | `borrow_records`, `equipment_categories` |
| คอลัมน์ | `snake_case` | `created_at`, `available_qty` |
| Primary Key | `id` เสมอ | `id INT UNSIGNED AUTO_INCREMENT` |
| Foreign Key | `{table_singular}_id` | `equipment_id`, `borrower_id` |
| Index | `idx_{table}_{column}` | `idx_equipment_code` |

---

## 2. รูปแบบการเขียน PHP API Endpoint

ทุก file ใน `src/api/` ต้องมีโครงสร้างนี้:

```php
<?php
// 1. Auth check ก่อนเสมอ
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

// 2. ตรวจ HTTP Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// 3. รับและ Validate input
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$name = trim($data['name'] ?? '');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่ออุปกรณ์']);
    exit;
}

// 4. PDO Prepared Statement เท่านั้น
try {
    $pdo  = Database::getInstance();
    $stmt = $pdo->prepare("INSERT INTO equipment (name) VALUES (:name)");
    $stmt->execute([':name' => $name]);

    echo json_encode(['success' => true, 'message' => 'บันทึกสำเร็จ']);
} catch (PDOException $e) {
    // Log error แต่อย่า expose ให้ user เห็น
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในระบบ']);
}
```

---

## 3. รูปแบบ AJAX + jQuery Confirm

```javascript
// Pattern มาตรฐาน: Confirm → AJAX → Feedback
$.confirm({
    title: 'ยืนยันการลบ',
    content: 'ต้องการลบอุปกรณ์นี้ใช่หรือไม่?',
    type: 'red',
    buttons: {
        confirm: {
            text: 'ลบ',
            btnClass: 'btn-danger',
            action: function () {
                $.ajax({
                    url: '/api/equipment/delete.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: equipmentId }),
                    success: function (res) {
                        if (res.success) {
                            $.alert({ title: '✅ สำเร็จ', content: res.message, type: 'green' });
                            loadEquipmentTable(); // reload
                        } else {
                            $.alert({ title: '❌ ผิดพลาด', content: res.message, type: 'red' });
                        }
                    },
                    error: function () {
                        $.alert({ title: 'Error', content: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', type: 'orange' });
                    }
                });
            }
        },
        cancel: { text: 'ยกเลิก' }
    }
});
```

---

## 4. Commit Message Format

ใช้รูปแบบ **Conventional Commits**:

```
<type>(<scope>): <short description ภาษาไทยหรืออังกฤษก็ได้>
```

### ประเภท (type)

| Type | ใช้เมื่อ |
|------|---------|
| `feat` | เพิ่มฟีเจอร์ใหม่ |
| `fix` | แก้ไข bug |
| `docs` | แก้ไขเอกสาร |
| `style` | แก้ไข CSS/UI (ไม่กระทบ logic) |
| `refactor` | ปรับโครงสร้างโค้ด |
| `chore` | งานทั่วไป เช่น config, dependency |
| `test` | เพิ่ม/แก้ไข test |

### ตัวอย่าง Commit Messages

```bash
# ✅ ถูกต้อง
feat(auth): เพิ่มระบบ login ด้วย session
fix(borrow): แก้ bug available_qty ไม่อัปเดตเมื่อคืนอุปกรณ์
docs(readme): อัปเดตวิธีรันด้วย Docker
style(equipment): ปรับ layout ตารางให้ responsive
refactor(api): แยก validation ออกเป็น helper function
chore: เพิ่ม .gitignore สำหรับ .env

# ❌ ผิด
git commit -m "fix bug"          # ไม่บอกว่า fix อะไร
git commit -m "update"           # คลุมเครือเกินไป
git commit -m "WIP"              # ไม่ควร push ขึ้น main
```

---

## 5. Git Branching Strategy

```
main          ← Production-ready เท่านั้น
  └── dev     ← Integration branch
        ├── feat/login
        ├── feat/equipment-crud
        ├── feat/borrow-return
        └── fix/available-qty-bug
```

**กฎ:** ห้าม commit ตรง `main` เด็ดขาด ต้องผ่าน Pull Request เสมอ

---

## 6. Code Style Summary

- **PHP:** ใช้ spaces 4 ช่อง, ต้องมี `<?php` เสมอ, ห้ามใช้ short echo `<?=` ใน logic file
- **SQL:** keyword เป็น UPPERCASE เสมอ (`SELECT`, `INSERT`, `WHERE`)
- **JS:** ใช้ `const`/`let` ไม่ใช้ `var`, จบทุก statement ด้วย `;`
- **HTML:** Tailwind class ในลำดับ: Layout → Spacing → Typography → Color → State
