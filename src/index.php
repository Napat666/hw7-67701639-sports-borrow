<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | ระบบยืม-คืนอุปกรณ์กีฬา</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; } </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-lg p-8 border border-slate-700">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-white tracking-wide mt-4">เข้าสู่ระบบ</h1>
            <p class="text-slate-400 mt-2 text-sm">ระบบยืม-คืนอุปกรณ์กีฬา</p>
        </div>

        <form id="loginForm" class="space-y-6">
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2" for="username">รหัสนักศึกษา / Username</label>
                <input type="text" id="username" class="w-full px-4 py-3 rounded-lg bg-slate-900 border border-slate-700 text-white focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none" placeholder="กรอกรหัสนักศึกษา" required>
            </div>
            <div>
                <label class="block text-slate-300 text-sm font-medium mb-2" for="password">รหัสผ่าน / Password</label>
                <input type="password" id="password" class="w-full px-4 py-3 rounded-lg bg-slate-900 border border-slate-700 text-white focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none" placeholder="••••••••" required>
            </div>
            <button type="submit" id="btnSubmit" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-semibold py-3 px-4 rounded-lg transition flex justify-center items-center">
                เข้าสู่ระบบ
            </button>
        </form>
    </div>

    <script>
        $(document).ready(function () {

            $('#loginForm').on('submit', function (e) {
                e.preventDefault();

                const username = $('#username').val().trim();
                const password = $('#password').val().trim();
                const $btn     = $('#btnSubmit');
                const originalContent = $btn.html();

                // Validate ฝั่ง Client
                if (!username || !password) {
                    $.alert({
                        title: '⚠️ แจ้งเตือน',
                        content: 'กรุณากรอก Username และ Password',
                        type: 'orange',
                        theme: 'modern'
                    });
                    return;
                }

                // ปิดปุ่มระหว่าง Loading
                $btn.html('กำลังตรวจสอบ...').prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

                // ยิง AJAX ไปที่ api/login_action.php
                $.ajax({
                    url: 'api/login_action.php',
                    method: 'POST',
                    data: { username: username, password: password },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            $.confirm({
                                title: '🎉 สำเร็จ!',
                                content: res.message,
                                type: 'green',
                                theme: 'modern',
                                buttons: {
                                    ok: {
                                        text: 'ไปที่ Dashboard',
                                        btnClass: 'btn-green',
                                        action: function () {
                                            window.location.href = res.redirect;
                                        }
                                    }
                                }
                            });
                        } else {
                            $.confirm({
                                title: '🚨 เข้าสู่ระบบไม่สำเร็จ',
                                content: res.message,
                                type: 'red',
                                theme: 'modern',
                                buttons: {
                                    tryAgain: {
                                        text: 'ลองอีกครั้ง',
                                        btnClass: 'btn-red'
                                    }
                                }
                            });
                            // คืนค่าปุ่ม
                            $btn.html(originalContent).prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                        }
                    },
                    error: function () {
                        $.alert({
                            title: '❌ Connection Error',
                            content: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่',
                            type: 'red',
                            theme: 'modern'
                        });
                        $btn.html(originalContent).prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    }
                });

            });

        });
    </script>
</body>
</html>
