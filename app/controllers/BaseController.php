<?php
require_once __DIR__ . '/../config/Config.php';

abstract class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = __DIR__ . '/../../resources/views/' . $view . '.php';
        require __DIR__ . '/../../resources/views/layouts/app.php';
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: /public/index.php?route=login');
            exit;
        }
    }

    protected function requireRoles(array $roles): void
    {
        $this->requireAuth();
        $role = $_SESSION['user']['role'] ?? 'viewer';
        if (!in_array($role, $roles, true)) {
            http_response_code(403);
            echo '403 Forbidden';
            exit;
        }
    }
}
