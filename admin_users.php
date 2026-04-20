<?php
require 'config.php';
requireLogin();
requireRole('admin');

$all = loadUserPermissions();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (USERS as $username => $u) {
        $role = $_POST['role'][$username] ?? $u['role'];
        if (!in_array($role, ['viewer','agent','admin'], true)) $role = $u['role'];
        $caps = [
            'can_add'             => isset($_POST['caps']['can_add'][$username]),
            'can_manage_members'  => isset($_POST['caps']['can_manage_members'][$username]),
            'can_export'          => isset($_POST['caps']['can_export'][$username]),
            'can_manage_models'   => isset($_POST['caps']['can_manage_models'][$username]),
            'step_turat'          => isset($_POST['caps']['step_turat'][$username]),
            'step_izn_tribunal'   => isset($_POST['caps']['step_izn_tribunal'][$username]),
            'step_courrier_expert'=> isset($_POST['caps']['step_courrier_expert'][$username]),
            'step_evacuation'     => isset($_POST['caps']['step_evacuation'][$username]),
            'step_demolition'     => isset($_POST['caps']['step_demolition'][$username]),
        ];
        $all[$username] = ['role' => $role, 'capabilities' => $caps];
    }
    if (saveUserPermissions($all)) $msg = 'saved';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صلاحيات المستخدمين</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f0f2f5;direction:rtl}
        .wrap{max-width:1100px;margin:20px auto;background:#fff;padding:20px;border-radius:12px}
        table{width:100%;border-collapse:collapse}
        th,td{border:1px solid #e6e6e6;padding:8px;text-align:center;font-size:13px}
        th{background:#1a3c5e;color:#fff}
        .btn{background:#28a745;color:#fff;border:none;border-radius:8px;padding:10px 20px;cursor:pointer}
        .alert{margin-bottom:10px;padding:10px;border-radius:8px;background:#d4edda;color:#155724}
    </style>
</head>
<body>
<?php include '_menu.php'; ?>
<div class="wrap">
    <h2>🛡️ تعديل صلاحيات المستخدمين</h2>
    <?php if ($msg === 'saved'): ?><div class="alert">✅ تم حفظ الصلاحيات</div><?php endif; ?>
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>المستخدم</th><th>الدور</th><th>إضافة</th><th>أعضاء</th><th>تصدير</th><th>نماذج</th>
                    <th>تراث</th><th>إذن</th><th>خبير</th><th>إخلاء</th><th>هدم</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (USERS as $username => $u):
                $s = getUserSettings($username);
                $c = $s['capabilities'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($s['nom']) ?><br><small><?= htmlspecialchars($username) ?></small></td>
                    <td>
                        <select name="role[<?= htmlspecialchars($username) ?>]">
                            <option value="viewer" <?= $s['role']==='viewer'?'selected':'' ?>>قارئ</option>
                            <option value="agent" <?= $s['role']==='agent'?'selected':'' ?>>عون</option>
                            <option value="admin" <?= $s['role']==='admin'?'selected':'' ?>>مدير</option>
                        </select>
                    </td>
                    <td><input type="checkbox" name="caps[can_add][<?= htmlspecialchars($username) ?>]" <?= !empty($c['can_add'])?'checked':'' ?>></td>
                    <td><input type="checkbox" name="caps[can_manage_members][<?= htmlspecialchars($username) ?>]" <?= !empty($c['can_manage_members'])?'checked':'' ?>></td>
                    <td><input type="checkbox" name="caps[can_export][<?= htmlspecialchars($username) ?>]" <?= !empty($c['can_export'])?'checked':'' ?>></td>
                    <td><input type="checkbox" name="caps[can_manage_models][<?= htmlspecialchars($username) ?>]" <?= !empty($c['can_manage_models'])?'checked':'' ?>></td>
                    <td><input type="checkbox" name="caps[step_turat][<?= htmlspecialchars($username) ?>]" <?= !empty($c['step_turat'])?'checked':'' ?>></td>
                    <td><input type="checkbox" name="caps[step_izn_tribunal][<?= htmlspecialchars($username) ?>]" <?= !empty($c['step_izn_tribunal'])?'checked':'' ?>></td>
                    <td><input type="checkbox" name="caps[step_courrier_expert][<?= htmlspecialchars($username) ?>]" <?= !empty($c['step_courrier_expert'])?'checked':'' ?>></td>
                    <td><input type="checkbox" name="caps[step_evacuation][<?= htmlspecialchars($username) ?>]" <?= !empty($c['step_evacuation'])?'checked':'' ?>></td>
                    <td><input type="checkbox" name="caps[step_demolition][<?= htmlspecialchars($username) ?>]" <?= !empty($c['step_demolition'])?'checked':'' ?>></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p style="margin-top:12px"><button class="btn" type="submit">💾 حفظ</button></p>
    </form>
</div>
</body>
</html>
