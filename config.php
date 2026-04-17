<?php
define('USERS', [
    'admin'   => ['password' => 'admin123',   'role' => 'admin',   'nom' => 'المدير العام'],
    'haifa'   => ['password' => 'haifa123',   'role' => 'haifa',   'nom' => 'HAIFA'],
    'khaoula' => ['password' => 'khaoula123', 'role' => 'khaoula', 'nom' => 'KHAOULA'],
    'mohamed' => ['password' => 'mohamed123', 'role' => 'mohamed', 'nom' => 'MOHAMED'],
    'viewer'  => ['password' => 'viewer123',  'role' => 'viewer',  'nom' => 'Viewer'],
]);

// Set TARATIB_OUTPUT_CHARSET=Windows-1256 on servers that require Windows-1256 output.
// Keep default UTF-8 for compatibility with standard web deployments.
define('APP_OUTPUT_CHARSET', getenv('TARATIB_OUTPUT_CHARSET') ?: 'UTF-8');
ini_set('default_charset', APP_OUTPUT_CHARSET);
if (!headers_sent()) {
    header('Content-Type: text/html; charset=' . APP_OUTPUT_CHARSET);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

function hasRole($role) {
    if (!isLoggedIn()) return false;
    $roles = [
        'viewer'  => 1,
        'agent'   => 2,
        'haifa'   => 2,
        'khaoula' => 2,
        'mohamed' => 2,
        'admin'   => 3,
    ];
    $userRole = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : 'viewer';
    return ($roles[$userRole] ?? 0) >= ($roles[$role] ?? 0);
}

function roleLabel($role) {
    if ($role === 'admin') return 'مدير';
    if ($role === 'haifa') return 'HAIFA';
    if ($role === 'khaoula') return 'KHAOULA';
    if ($role === 'mohamed') return 'MOHAMED';
    return 'قارئ';
}

function roleBadgeColor($role) {
    if ($role === 'admin') return 'role-admin';
    if (in_array($role, ['haifa', 'khaoula', 'mohamed', 'agent'], true)) return 'role-agent';
    return 'role-viewer';
}

function canEditStep($stepType) {
    if (!isLoggedIn()) return false;
    $role = $_SESSION['user']['role'] ?? 'viewer';

    if ($role === 'admin') return true;

    $permissions = [
        'haifa'   => ['reclamation', 'proces_verbal'],
        'khaoula' => ['izn_khabir', 'retour_rapport'],
        'mohamed' => ['decision_finale'],
    ];

    return in_array($stepType, $permissions[$role] ?? [], true);
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
