<?php
define('USERS', [
    'admin' => ['password' => 'admin123', 'role' => 'admin',  'nom' => 'المدير العام'],
    'sonia' => ['password' => 'sonia123', 'role' => 'agent',  'nom' => 'سنية'],
    'haifa' => ['password' => 'haifa123', 'role' => 'agent',  'nom' => 'هيفاء'],
    'karim' => ['password' => 'karim123', 'role' => 'viewer', 'nom' => 'محمد كاري'],
]);

define('USER_PERMISSIONS_FILE', __DIR__ . '/user_permissions.json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

function hasRole($role) {
    if (!isLoggedIn()) return false;
    $roles = ['viewer' => 1, 'agent' => 2, 'admin' => 3];
    $userRole = getUserRole($_SESSION['user']['username'] ?? '', $_SESSION['user']['role'] ?? 'viewer');
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

function loadUserPermissions() {
    if (!file_exists(USER_PERMISSIONS_FILE)) return [];
    $raw = @file_get_contents(USER_PERMISSIONS_FILE);
    if ($raw === false || trim($raw) === '') return [];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function saveUserPermissions(array $permissions) {
    $json = json_encode($permissions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return @file_put_contents(USER_PERMISSIONS_FILE, $json, LOCK_EX) !== false;
}

function getDefaultCapabilitiesByRole($role) {
    $isAdmin = ($role === 'admin');
    return [
        'can_add'            => $isAdmin || $role === 'agent',
        'can_manage_members' => $isAdmin || $role === 'agent',
        'can_export'         => true,
        'can_manage_models'  => $isAdmin,
        'step_turat'         => true,
        'step_izn_tribunal'  => true,
        'step_courrier_expert'=> true,
        'step_evacuation'    => true,
        'step_demolition'    => true,
    ];
}

function getUserSettings($username) {
    if (!isset(USERS[$username])) return null;
    $base = USERS[$username];
    $all  = loadUserPermissions();
    $user = $all[$username] ?? [];
    $role = $user['role'] ?? $base['role'];
    $caps = array_merge(getDefaultCapabilitiesByRole($role), $user['capabilities'] ?? []);
    return [
        'username'     => $username,
        'nom'          => $base['nom'],
        'password'     => $base['password'],
        'role'         => $role,
        'capabilities' => $caps,
    ];
}

function getUserRole($username, $fallback = 'viewer') {
    $settings = $username ? getUserSettings($username) : null;
    return $settings['role'] ?? $fallback;
}

function userCan($capability) {
    if (!isLoggedIn()) return false;
    $username = $_SESSION['user']['username'] ?? '';
    $settings = $username ? getUserSettings($username) : null;
    if (!$settings) return false;
    return !empty($settings['capabilities'][$capability]);
}

function canAccessStep($stepType) {
    return userCan('step_' . $stepType);
}
?>
