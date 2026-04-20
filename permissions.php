<?php
require 'config.php';
requireLogin();
if (!hasRole('admin') || !userCan('manage_permissions')) requireRole('admin');

$labels = authorizationLabels();
$msg = '';
$authz = loadUserAuthorizations();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (USERS as $username => $u) {
        foreach ($labels as $k => $_) {
            $authz[$username][$k] = isset($_POST['a'][$username][$k]);
        }
    }
    saveUserAuthorizations($authz);
    $msg = 'saved';
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><title>صلاحيات المستخدمين</title>
<style>body{font-family:Arial;background:#f5f5f5;padding:20px}table{width:100%;background:#fff;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px;text-align:center}th{background:#1a3c5e;color:#fff}.msg{padding:8px;background:#d4edda;margin-bottom:8px}</style>
</head>
<body>
<a href="index.php">↩️ رجوع</a>
<h2>تعديل صلاحيات المستخدمين</h2>
<?php if ($msg): ?><div class="msg">✅ تم حفظ الصلاحيات</div><?php endif; ?>
<form method="post">
    <table>
        <thead>
            <tr><th>المستخدم</th><?php foreach ($labels as $l): ?><th><?= htmlspecialchars($l) ?></th><?php endforeach; ?></tr>
        </thead>
        <tbody>
            <?php foreach (USERS as $username => $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['nom']) ?> (<?= htmlspecialchars($username) ?>)</td>
                    <?php foreach ($labels as $k => $l): ?>
                        <td><input type="checkbox" name="a[<?= htmlspecialchars($username) ?>][<?= htmlspecialchars($k) ?>]" <?= !empty($authz[$username][$k]) ? 'checked' : '' ?>></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p style="margin-top:10px"><button type="submit">💾 حفظ</button></p>
</form>
</body></html>
