<?php
require 'config.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$users = DEFAULT_USERS;
try {
    $pdoTmp = new PDO("mysql:host=localhost;dbname=batiments_ruine;charset=utf8", 'root', '');
    $pdoTmp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $hasTable = $pdoTmp->query("SHOW TABLES LIKE 'membres'")->fetchColumn();
    if ($hasTable) {
        $rows = $pdoTmp->query("SELECT username, nom, role, password FROM membres WHERE actif=1")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            $users = [];
            foreach ($rows as $r) {
                $users[$r['username']] = [
                    'password' => $r['password'],
                    'role' => $r['role'],
                    'nom' => $r['nom'],
                ];
            }
        }
    }
} catch (Throwable $e) {
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        $_SESSION['user'] = [
            'username' => $username,
            'nom'      => $users[$username]['nom'],
            'role'     => $users[$username]['role'],
        ];
        header("Location: index.php");
        exit;
    }

    $error = 'بيانات الدخول غير صحيحة';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول — بلدية سوسة</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{
            font-family:'Segoe UI',Arial,sans-serif;
            background:linear-gradient(135deg,#1a3c5e,#2e6da4);
            min-height:100vh;display:flex;align-items:center;justify-content:center;direction:rtl;
        }
        .login-wrap{
            background:white;border-radius:16px;padding:34px 30px;
            width:100%;max-width:430px;box-shadow:0 16px 50px rgba(0,0,0,.25);
        }
        .login-logo{text-align:center;margin-bottom:18px}
        .login-logo img{width:96px;height:96px;object-fit:contain}
        .login-logo h1{font-size:20px;color:#1a3c5e}
        .login-logo p{font-size:13px;color:#888;margin-top:5px}
        .error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;padding:10px 12px;border-radius:8px;margin-bottom:12px;font-size:13px}
        .fg{margin-bottom:12px}
        .fg label{display:block;margin-bottom:6px;font-size:13px;color:#666;font-weight:600}
        input{
            width:100%;padding:11px 12px;border:2px solid #e9ecef;border-radius:8px;
            font-family:inherit;font-size:14px;
        }
        input:focus{outline:none;border-color:#2e6da4}
        .users{
            margin:12px 0;padding:11px;border:1px solid #e9ecef;border-radius:9px;background:#f8f9fb;
            display:grid;grid-template-columns:1fr;gap:6px;
        }
        .u{
            border:1px solid #dbe3ee;background:white;border-radius:8px;padding:7px 10px;
            cursor:pointer;display:flex;justify-content:space-between;align-items:center;
        }
        .u.active{border-color:#2e6da4;background:#e8f0fb}
        .u small{font-size:11px;color:#888}
        .btn{
            width:100%;padding:12px;border:none;border-radius:8px;cursor:pointer;
            background:linear-gradient(135deg,#1a3c5e,#2e6da4);color:white;font-weight:700;font-family:inherit;
        }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-logo">
        <img src="Logo_commune_Sousse.svg" alt="Logo commune de Sousse">
        <h1>بلدية سوسة</h1>
        <p>نظام متابعة مسار البنايات المتداعية</p>
    </div>

    <?php if ($error): ?><div class="error">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST">
        <input type="hidden" name="username" id="username-field" value="<?= htmlspecialchars($_POST['username'] ?? 'haifa') ?>">

        <div class="users">
            <?php foreach ($users as $uname => $info): ?>
                <button type="button" class="u<?= (($uname === ($_POST['username'] ?? 'haifa')) ? ' active' : '') ?>" data-u="<?= htmlspecialchars($uname) ?>">
                    <span><?= htmlspecialchars($info['nom']) ?></span>
                    <small><?= roleLabel($info['role']) ?></small>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="fg">
            <label>كلمة المرور</label>
            <input type="password" name="password" autocomplete="current-password" required>
        </div>
        <button class="btn" type="submit">🚀 تسجيل الدخول</button>
    </form>
</div>

<script>
document.querySelectorAll('.u').forEach(function(btn){
    btn.addEventListener('click', function(){
        document.querySelectorAll('.u').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        document.getElementById('username-field').value = btn.getAttribute('data-u');
    });
});
</script>
</body>
</html>
