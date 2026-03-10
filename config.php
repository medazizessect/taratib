<?php
define('USERS', [
    'admin' => ['password' => 'admin123', 'role' => 'admin',  'nom' => 'المدير العام'],
    'sonia' => ['password' => 'sonia123', 'role' => 'agent',  'nom' => 'سنية'],
    'haifa' => ['password' => 'haifa123', 'role' => 'agent',  'nom' => 'هيفاء'],
    'karim' => ['password' => 'karim123', 'role' => 'viewer', 'nom' => 'محمد كاري'],
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

function hasRole($role) {
    if (!isLoggedIn()) return false;
    $roles = ['viewer' => 1, 'agent' => 2, 'admin' => 3];
    $userRole = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : 'viewer';
    return ($roles[$userRole] ?? 0) >= ($roles[$role] ?? 0);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        die("
        <div style='font-family:Arial;text-align:center;padding:60px;direction:rtl'>
            <div style='font-size:48px;margin-bottom:20px'>🚫</div>
            <h2 style='color:#dc3545'>غير مصرح لك بهذه العملية</h2>
            <a href='index.php'
               style='background:#2e6da4;color:white;padding:10px 24px;
                      border-radius:8px;text-decoration:none;margin-top:20px;
                      display:inline-block'>↩️ رجوع</a>
        </div>");
    }
}
?>