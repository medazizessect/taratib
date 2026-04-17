<?php
require_once __DIR__ . '/../config/Config.php';

abstract class BaseController
{
    protected function render(string $view, array $params = []): void
    {
        extract($params);
        $viewPath = __DIR__ . '/../../resources/views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            http_response_code(404);
            echo 'View not found: ' . htmlspecialchars($view);
            return;
        }
        include __DIR__ . '/../../resources/views/layouts/app.php';
    }

    protected function redirect(string $route): void
    {
        header('Location: /index.php?route=' . urlencode($route));
        exit;
    }
}
