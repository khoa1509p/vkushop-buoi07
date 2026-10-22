<?php

declare(strict_types=1);

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0;
}

function isAdmin(): bool
{
    // TODO B7-A1: xác định user đã login và role trong Session là admin.
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function requireLogin(string $loginUrl = 'login.php'): void
{
    if (!isLoggedIn()) redirect($loginUrl);
}

function requireAdmin(string $loginUrl = 'login.php'): void
{
    requireLogin($loginUrl);
    if (!isAdmin()) {
        http_response_code(403); // Vẫn trả mã lỗi 403 thật
        // Giao diện thân thiện:
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <title>403 - Forbidden</title>
            <style>
                body { font-family: system-ui, sans-serif; background: #f5f8fc; text-align: center; padding-top: 100px; color: #213047; }
                h1 { color: #b02a37; font-size: 40px; margin-bottom: 10px; }
                a { display: inline-block; padding: 10px 20px; background: #17699e; color: #fff; text-decoration: none; border-radius: 7px; margin-top: 20px; font-weight: 600; }
                a:hover { background: #11527d; }
            </style>
        </head>
        <body>
            <h1>403 - KHÔNG CÓ QUYỀN TRUY CẬP</h1>
            <p>Xin lỗi, bạn cần có quyền Quản trị viên (Admin) để thực hiện thao tác này.</p>
            <a href="products.php">Về trang chủ</a>
        </body>
        </html>
        HTML;

        exit($html);
    }
}

// Helper CSRF có sẵn để code mới không sử dụng POST thiếu token.
// Phần giải thích chuyên sâu về CSRF dành cho Buổi 12.
function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void
{
    $value = $_POST['csrf_token'] ?? '';
    if (!is_string($value) || !hash_equals(csrf_token(), $value)) {
        http_response_code(403);
        exit('Yêu cầu không hợp lệ.');
    }
}
