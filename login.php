<?php
require 'config.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $users = USERS;
    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        $_SESSION['user'] = [
            'username' => $username,
            'nom'      => $users[$username]['nom'],
            'role'     => $users[$username]['role'],
        ];
        header("Location: index.php");
        exit;
    } else {
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    }
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
            background:linear-gradient(135deg,#1a3c5e 0%,#2e6da4 50%,#1a3c5e 100%);
            min-height:100vh;display:flex;align-items:center;
            justify-content:center;direction:rtl;
        }
        .login-wrap{
            background:white;border-radius:18px;padding:44px 40px;
            width:100%;max-width:420px;
            box-shadow:0 20px 60px rgba(0,0,0,.3);
        }
        .login-logo{text-align:center;margin-bottom:28px}
        .login-logo .icon{
            width:80px;height:80px;margin:0 auto 12px;
            display:flex;align-items:center;justify-content:center;
        }
        .login-logo .icon img{max-width:100%;max-height:100%}
        .login-logo h1{font-size:20px;color:#1a3c5e;font-weight:700}
        .login-logo p{font-size:13px;color:#888;margin-top:4px}

        .fg{margin-bottom:18px}
        .fg label{
            display:block;font-size:13px;font-weight:600;
            color:#555;margin-bottom:7px;
        }
        .input-wrap{position:relative}
        .input-wrap .icon-field{
            position:absolute;right:12px;top:50%;
            transform:translateY(-50%);font-size:16px;color:#aaa;
        }
        .fg input{
            width:100%;padding:12px 40px 12px 14px;
            border:2px solid #e9ecef;border-radius:9px;
            font-size:15px;font-family:inherit;
            transition:border .2s,box-shadow .2s;
            background:#fafafa;
        }
        .fg input:focus{
            outline:none;border-color:#2e6da4;
            box-shadow:0 0 0 3px rgba(46,109,164,.15);
            background:white;
        }

        .error-msg{
            background:#f8d7da;color:#721c24;
            padding:11px 14px;border-radius:8px;
            margin-bottom:18px;font-size:13px;
            border:1px solid #f5c6cb;
            display:flex;align-items:center;gap:8px;
        }

        .btn-login{
            width:100%;padding:13px;
            background:linear-gradient(135deg,#1a3c5e,#2e6da4);
            color:white;border:none;border-radius:9px;
            font-size:16px;font-family:inherit;font-weight:700;
            cursor:pointer;transition:opacity .2s,transform .1s;
            letter-spacing:.3px;
        }
        .btn-login:hover{opacity:.9;transform:translateY(-1px)}
        .btn-login:active{transform:translateY(0)}
        .footer-txt{text-align:center;font-size:11px;color:#bbb;margin-top:20px}
    </style>
</head>
<body>

<div class="login-wrap">
    <div class="login-logo">
        <div class="icon">
            <img src="Logo_commune_Sousse.svg" alt="Logo">
        </div>
        <h1>بلدية سوسة</h1>
        <p>نظام إدارة البنايات المتداعية للسقوط</p>
    </div>

    <?php if ($error): ?>
        <div class="error-msg">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="fg">
            <label>اسم المستخدم</label>
            <div class="input-wrap">
                <span class="icon-field">👤</span>
                <input type="text" name="username"
                       placeholder="أدخل اسم المستخدم"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       autocomplete="username" autofocus>
            </div>
        </div>
        <div class="fg">
            <label>كلمة المرور</label>
            <div class="input-wrap">
                <span class="icon-field">🔒</span>
                <input type="password" name="password"
                       placeholder="أدخل كلمة المرور"
                       autocomplete="current-password">
            </div>
        </div>
        <button type="submit" class="btn-login">🚀 تسجيل الدخول</button>
    </form>

    <div class="footer-txt">بلدية سوسة &copy; <?= date('Y') ?></div>
</div>

</body>
</html>
