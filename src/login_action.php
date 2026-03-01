<?php
// ============================================================
// api/login_action.php
// รับค่า AJAX จาก index.php → ตรวจ Login ด้วย PDO
// ============================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

// ถ้า Login อยู่แล้ว ไม่ต้องทำอะไร
if (isset($_SESSION['user_id'])) {
    echo json_encode(['success' => true, 'message' => 'Already logged in', 'redirect' => '../dashboard.php']);
    exit;
}

// รับเฉพาะ POST เท่านั้น
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// ----------------------------
// โหลด PDO จาก db.php
// (api/ อยู่ใน subfolder จึง require ขึ้นไป 1 ระดับ)
// ----------------------------
require_once '../db.php'; // ได้ $conn จาก db.php

// ----------------------------
// รับค่าจาก AJAX
// ----------------------------
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอก Username และ Password']);
    exit;
}

// ----------------------------
// Query ด้วย Prepared Statement
// ----------------------------
try {
    $stmt = $conn->prepare("
        SELECT id, username, password, full_name, role
        FROM users
        WHERE username = :username
          AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // ตรวจ user + password
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Username หรือ Password ไม่ถูกต้อง']);
        exit;
    }

    // ----------------------------
    // Login สำเร็จ → เก็บ Session
    // ----------------------------
    session_regenerate_id(true); // ป้องกัน Session Fixation

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];

    echo json_encode([
        'success'  => true,
        'message'  => 'ยินดีต้อนรับ ' . $user['full_name'],
        'redirect' => 'dashboard.php'
    ]);

} catch (PDOException $e) {
    error_log('Login Query Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่']);
}
