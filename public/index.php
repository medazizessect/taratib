<?php
header('Content-Type: text/html; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/Etape1Controller.php';
require_once __DIR__ . '/../app/controllers/Etape2Controller.php';
require_once __DIR__ . '/../app/controllers/Etape3Controller.php';
require_once __DIR__ . '/../app/controllers/Etape4Controller.php';
require_once __DIR__ . '/../app/controllers/Etape5Controller.php';

$route = $_GET['route'] ?? (isset($_SESSION['user']) ? 'dashboard' : 'login');

$routes = [
    'login' => [new AuthController(), 'login'],
    'logout' => [new AuthController(), 'logout'],
    'dashboard' => [new DashboardController(), 'index'],

    'etape1/list' => [new Etape1Controller(), 'list'],
    'etape1/create' => [new Etape1Controller(), 'create'],
    'etape1/show' => [new Etape1Controller(), 'show'],

    'etape2/list' => [new Etape2Controller(), 'list'],
    'etape2/create' => [new Etape2Controller(), 'create'],
    'etape2/edit' => [new Etape2Controller(), 'edit'],
    'etape2/show' => [new Etape2Controller(), 'show'],
    'etape2/pdf' => [new Etape2Controller(), 'pdfTemplate'],

    'etape3/list' => [new Etape3Controller(), 'list'],
    'etape3/create' => [new Etape3Controller(), 'create'],
    'etape3/show' => [new Etape3Controller(), 'show'],

    'etape4/list' => [new Etape4Controller(), 'list'],
    'etape4/create' => [new Etape4Controller(), 'create'],
    'etape4/show' => [new Etape4Controller(), 'show'],

    'etape5/list' => [new Etape5Controller(), 'list'],
    'etape5/create' => [new Etape5Controller(), 'create'],
    'etape5/show' => [new Etape5Controller(), 'show'],
];

if (!isset($routes[$route])) {
    http_response_code(404);
    echo '404 - Route introuvable: ' . htmlspecialchars((string) $route, ENT_QUOTES, 'UTF-8');
    exit;
}

call_user_func($routes[$route]);
