<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends BaseController
{
    public static function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function requireAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /index.php?route=login');
            exit;
        }
    }

    public static function canAccessStep(int $step): bool
    {
        $role = $_SESSION['user']['role'] ?? 'viewer';
        if ($role === 'admin') {
            return true;
        }
        if ($role === 'haifa') {
            return in_array($step, [1, 2], true);
        }
        if ($role === 'khaoula') {
            return in_array($step, [3, 4], true);
        }
        if ($role === 'mohamed') {
            return $step === 5;
        }
        return false;
    }

    public function login(): void
    {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new User();
            $username = trim($_POST['username'] ?? '');
            $password = (string) ($_POST['password'] ?? '');

            $user = $userModel->findByUsername($username);
            if ($user && password_verify($password, (string) $user['password'])) {
                $_SESSION['user'] = [
                    'id' => (int) $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role'],
                ];
                $this->redirect('dashboard');
            }
            $error = 'Identifiants invalides';
        }

        include __DIR__ . '/../../resources/views/login.php';
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_destroy();
        $this->redirect('login');
    }
}
