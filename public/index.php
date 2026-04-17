<?php
session_start();

require_once __DIR__ . '/../app/config/Config.php';
Config::setEncoding();

foreach (glob(__DIR__ . '/../app/controllers/*.php') as $file) {
    require_once $file;
}
foreach (glob(__DIR__ . '/../app/models/*.php') as $file) {
    require_once $file;
}

$route = $_GET['route'] ?? (isset($_SESSION['user']) ? 'dashboard' : 'login');

$routes = [
    'login' => [AuthController::class, 'login'],
    'logout' => [AuthController::class, 'logout'],
    'dashboard' => [DashboardController::class, 'index'],

    'etape1.list' => [Etape1Controller::class, 'list'],
    'etape1.create' => [Etape1Controller::class, 'create'],
    'etape1.show' => [Etape1Controller::class, 'show'],

    'etape2.list' => [Etape2Controller::class, 'list'],
    'etape2.create' => [Etape2Controller::class, 'create'],
    'etape2.edit' => [Etape2Controller::class, 'edit'],
    'etape2.show' => [Etape2Controller::class, 'show'],

    'etape3.list' => [Etape3Controller::class, 'list'],
    'etape3.create' => [Etape3Controller::class, 'create'],
    'etape3.show' => [Etape3Controller::class, 'show'],

    'etape4.list' => [Etape4Controller::class, 'list'],
    'etape4.create' => [Etape4Controller::class, 'create'],
    'etape4.show' => [Etape4Controller::class, 'show'],

    'etape5.list' => [Etape5Controller::class, 'list'],
    'etape5.create' => [Etape5Controller::class, 'create'],
    'etape5.show' => [Etape5Controller::class, 'show'],
];

if (!isset($routes[$route])) {
    http_response_code(404);
    echo '404';
    exit;
}

[$class, $method] = $routes[$route];
(new $class())->$method();
