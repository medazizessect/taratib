<?php
define('DEFAULT_USERS', [
    'admin'   => ['password' => 'admin123',   'role' => 'admin',   'nom' => 'المدير'],
    'haifa'   => ['password' => 'haifa123',   'role' => 'haifa',   'nom' => 'HAIFA'],
    'khaoula' => ['password' => 'khaoula123', 'role' => 'khaoula', 'nom' => 'KHAOULA'],
    'mohamed' => ['password' => 'mohamed123', 'role' => 'mohamed', 'nom' => 'MOHAMED'],
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return !empty($_SESSION['user']);
}

function currentRole() {
    return $_SESSION['user']['role'] ?? '';
}

function hasAnyRole($roles) {
    if (!isLoggedIn()) return false;
    return in_array(currentRole(), $roles, true);
}

function hasRole($role) {
    if (!isLoggedIn()) return false;

    $current = currentRole();
    $legacyRanks = [
        'viewer'  => 1,
        'mohamed' => 2,
        'khaoula' => 3,
        'haifa'   => 4,
        'admin'   => 5,
    ];

    if ($role === 'agent') {
        return in_array($current, ['admin','haifa','khaoula','mohamed'], true);
    }
    if ($role === 'viewer') {
        return true;
    }

    return ($legacyRanks[$current] ?? 0) >= ($legacyRanks[$role] ?? 0);
}

function hasStepAccess($stepType) {
    if (!isLoggedIn()) return false;
    if (currentRole() === 'admin') return true;

    $map = [
        'step1_reclamation'   => ['haifa'],
        'step2_pv'            => ['haifa'],
        'step3_expert_request'=> ['khaoula'],
        'step4_expert_report' => ['khaoula'],
        'step5_decision'      => ['mohamed'],
    ];
    return in_array(currentRole(), $map[$stepType] ?? [], true);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function denyAccess() {
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

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) denyAccess();
}

function requireStepAccess($stepType) {
    requireLogin();
    if (!hasStepAccess($stepType)) denyAccess();
}

function roleLabel($role) {
    return [
        'admin' => 'مدير',
        'haifa' => 'HAIFA',
        'khaoula' => 'KHAOULA',
        'mohamed' => 'MOHAMED',
    ][$role] ?? $role;
}
?>
