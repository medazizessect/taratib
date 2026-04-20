<?php
define('USERS', [
    'admin' => ['password' => 'admin123', 'role' => 'admin',  'nom' => 'المدير العام'],
    'sonia' => ['password' => 'sonia123', 'role' => 'agent',  'nom' => 'سنية'],
    'haifa' => ['password' => 'haifa123', 'role' => 'agent',  'nom' => 'هيفاء'],
    'karim' => ['password' => 'karim123', 'role' => 'viewer', 'nom' => 'محمد كاري'],
]);

define('AUTHZ_FILE', __DIR__ . '/user_authorizations.json');

function authorizationLabels() {
    return [
        'step1_reclamation' => 'المرحلة 1: Réclamation',
        'step2_pv'          => 'المرحلة 2: محضر',
        'step3_court'       => 'المرحلة 3: Court',
        'step4_expert'      => 'المرحلة 4: Expert',
        'step5_decision'    => 'المرحلة 5: Decision',
        'export_tables'     => 'تصدير الجداول (Excel/PDF)',
        'manage_grades'     => 'إدارة الدرجات',
        'manage_permissions'=> 'تعديل الصلاحيات',
    ];
}

function defaultAuthorizationsForRole($role) {
    $all = array_fill_keys(array_keys(authorizationLabels()), true);
    if ($role === 'admin') return $all;
    if ($role === 'agent') {
        return [
            'step1_reclamation' => true,
            'step2_pv'          => true,
            'step3_court'       => true,
            'step4_expert'      => true,
            'step5_decision'    => true,
            'export_tables'     => true,
            'manage_grades'     => false,
            'manage_permissions'=> false,
        ];
    }
    return [
        'step1_reclamation' => true,
        'step2_pv'          => false,
        'step3_court'       => false,
        'step4_expert'      => false,
        'step5_decision'    => false,
        'export_tables'     => true,
        'manage_grades'     => false,
        'manage_permissions'=> false,
    ];
}

function loadUserAuthorizations() {
    $authz = [];
    foreach (USERS as $username => $u) {
        $authz[$username] = defaultAuthorizationsForRole($u['role']);
    }
    if (is_file(AUTHZ_FILE)) {
        $json = file_get_contents(AUTHZ_FILE);
        $saved = json_decode($json, true);
        if (is_array($saved)) {
            foreach ($saved as $username => $perms) {
                if (!isset($authz[$username]) || !is_array($perms)) continue;
                foreach (authorizationLabels() as $k => $_) {
                    if (array_key_exists($k, $perms)) {
                        $authz[$username][$k] = (bool)$perms[$k];
                    }
                }
            }
        }
    }
    return $authz;
}

function saveUserAuthorizations($authz) {
    $payload = [];
    foreach (USERS as $username => $_) {
        if (!isset($authz[$username]) || !is_array($authz[$username])) continue;
        $payload[$username] = [];
        foreach (authorizationLabels() as $k => $_l) {
            $payload[$username][$k] = !empty($authz[$username][$k]);
        }
    }
    file_put_contents(
        AUTHZ_FILE,
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

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

function userCan($authorization) {
    if (!isLoggedIn()) return false;
    $username = $_SESSION['user']['username'] ?? '';
    $role     = $_SESSION['user']['role'] ?? 'viewer';
    $authz    = loadUserAuthorizations();
    if (isset($authz[$username][$authorization])) {
        return (bool)$authz[$username][$authorization];
    }
    $defaults = defaultAuthorizationsForRole($role);
    return !empty($defaults[$authorization]);
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
