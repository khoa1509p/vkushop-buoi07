<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\UserRepository;

final class AuthController
{
    public function __construct(private UserRepository $users) {}

    public function register(): void
    {
        $pageTitle = 'Đăng ký';
        $name = $email = '';
        $errors = [];
        
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            \verify_csrf();
            $name = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
            $email = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
            $confirm = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
            
            // TODO B7-C1: kiểm tra name, email, password, confirm.
            if ($name === '') {
                $errors['name'] = 'Vui lòng nhập họ tên.';
            } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
                $errors['name'] = 'Họ tên từ 2 đến 100 ký tự.';
            }

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email không hợp lệ.';
            }

            if (strlen($password) < 8 || strlen($password) > 72) {
                $errors['password'] = 'Mật khẩu phải từ 8 đến 72 byte.';
            } elseif ($password !== $confirm) {
                $errors['confirm_password'] = 'Mật khẩu nhập lại không khớp.';
            }

            if (!$errors) {
                try {
                    // TODO B7-C2: password_hash() rồi UserRepository::create().
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $this->users->create($name, $email, $hash);

                    // TODO B7-C3: xử lý email trùng (PDOException 1062); redirect tới login.php.
                    redirect('login.php?registered=1');
                } catch (\PDOException $exception) {
                    if (\is_duplicate_key($exception)) {
                        $errors['email'] = 'Email này đã được đăng ký.';
                    } else {
                        throw $exception;
                    }
                }
            }
        }
        require dirname(__DIR__, 2) . '/views/auth/register.php';
    }

    public function login(): void
    {
        $pageTitle = 'Đăng nhập';
        $email = $error = '';
        
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            \verify_csrf();
            $email = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
            
            // TODO B7-C4: findByEmail() và password_verify().
            $user = $this->users->findByEmail($email);
            
            // Nếu có user và mật khẩu xác thực thành công
            if ($user && password_verify($password, (string)$user['password_hash'])) {
                
                // TODO B7-C5: nếu đúng -> session_regenerate_id(true), lưu user_id/role, redirect.
                session_regenerate_id(true); // Reset Session ID để chống hacker cướp phiên
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['role'] = $user['role'];
                
                redirect('profile.php');
            } else {
                // Sai email hoặc sai mật khẩu đều báo chung 1 câu để bảo mật
                $error = 'Email hoặc mật khẩu không chính xác.';
            }
        }
        require dirname(__DIR__, 2) . '/views/auth/login.php';
    }

    public function profile(): void
    {
        \requireLogin();
        
        // TODO B7-C6: dùng UserRepository::findById() lấy $user.
        $user = $this->users->findById((int) $_SESSION['user_id']);
        
        // Giải quyết Challenge Ngôi sao (Slide 17): Xử lý Session cũ khi User bị xóa trong DB
        if (!$user) {
            $_SESSION = [];
            session_destroy();
            redirect('login.php');
        }

        $pageTitle = 'Hồ sơ tài khoản';
        require dirname(__DIR__, 2) . '/views/auth/profile.php';
    }

    public function logout(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Chỉ chấp nhận POST.');
        }
        \verify_csrf();
        
        // TODO B7-C7: xóa Session, cookie Session; redirect login.php.
        $_SESSION = []; // Xóa toàn bộ dữ liệu trong mảng Session
        
        // Xóa cookie phiên làm việc trên trình duyệt
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy(); // Hủy session trên server
        
        redirect('login.php?logged_out=1');
    }
}
