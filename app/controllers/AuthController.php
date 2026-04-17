<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends BaseController
{
    public function login(): void
    {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $user = (new User())->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role'],
                ];
                header('Location: /public/index.php?route=dashboard');
                exit;
            }
            $error = 'بيانات الدخول غير صحيحة';
        }

        $this->render('login', ['error' => $error]);
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /public/index.php?route=login');
        exit;
    }
}
