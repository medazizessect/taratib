<?php
require 'config.php';
requireRole('admin');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_user') {
        $pdo->prepare("INSERT INTO membres (nom, username, role, password, actif) VALUES (?,?,?,?,1)")
            ->execute([
                trim($_POST['nom'] ?? ''),
                trim($_POST['username'] ?? ''),
                in_array($_POST['role'] ?? '', ['admin','haifa','khaoula','mohamed'], true) ? $_POST['role'] : 'haifa',
                password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT),
            ]);
    } elseif ($action === 'toggle_user') {
        $pdo->prepare("UPDATE membres SET actif = 1 - actif WHERE id=?")->execute([intval($_POST['id'] ?? 0)]);
    } elseif ($action === 'update_user') {
        $sql = "UPDATE membres SET nom=?, role=?";
        $params = [trim($_POST['nom'] ?? ''), $_POST['role'] ?? 'haifa'];
        if (trim($_POST['password'] ?? '') !== '') {
            $sql .= ", password=?";
            $params[] = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        }
        $sql .= " WHERE id=?";
        $params[] = intval($_POST['id'] ?? 0);
        $pdo->prepare($sql)->execute($params);
    } elseif ($action === 'add_address') {
        $lib = trim($_POST['libelle'] ?? '');
        if ($lib !== '') $pdo->prepare("INSERT IGNORE INTO adresses (libelle) VALUES (?)")->execute([$lib]);
    } elseif ($action === 'delete_address') {
        $pdo->prepare("DELETE FROM adresses WHERE id=?")->execute([intval($_POST['id'] ?? 0)]);
    }
    header("Location: membres.php?ok=1");
    exit;
}

$users = $pdo->query("SELECT * FROM membres ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$addresses = $pdo->query("SELECT * FROM adresses ORDER BY libelle ASC LIMIT 1000")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>الإدارة</title>
<style>
*{box-sizing:border-box} body{font-family:Segoe UI,Arial;background:#f0f2f5;direction:rtl;margin:0}
header{background:linear-gradient(135deg,#6f42c1,#9b59b6);color:#fff;padding:18px 26px}
.wrap{max-width:1000px;margin:20px auto;padding:0 15px}.card{background:#fff;border-radius:12px;padding:18px;margin-bottom:14px;box-shadow:0 3px 12px rgba(0,0,0,.08)}
table{width:100%;border-collapse:collapse}th,td{border:1px solid #e9ecef;padding:8px;font-size:12px}th{background:#f8f9fa}
input,select{padding:7px 9px;border:1px solid #ddd;border-radius:7px;width:100%;font-family:inherit}
.btn{padding:7px 10px;border:none;border-radius:7px;cursor:pointer}.b1{background:#28a745;color:#fff}.b2{background:#17a2b8;color:#fff}.b3{background:#dc3545;color:#fff}
</style>
</head>
<body>
<?php include '_menu.php'; ?>
<header><h2 style="margin:0">⚙️ إدارة الحسابات والعناوين</h2></header>
<div class="wrap">
<?php if (!empty($_GET['ok'])): ?><div style="background:#d4edda;padding:10px;border:1px solid #c3e6cb;border-radius:8px;margin-bottom:10px">✅ تم الحفظ</div><?php endif; ?>

<div class="card">
    <h3>الحسابات والصلاحيات</h3>
    <table>
        <tr><th>الاسم</th><th>Username</th><th>الدور</th><th>كلمة مرور جديدة</th><th>الحالة</th><th>إجراءات</th></tr>
        <?php foreach($users as $u): ?>
        <tr>
            <form method="POST">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <td><input name="nom" value="<?= htmlspecialchars($u['nom']) ?>"></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><select name="role">
                    <?php foreach(['admin','haifa','khaoula','mohamed'] as $r): ?>
                        <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= roleLabel($r) ?></option>
                    <?php endforeach; ?>
                </select></td>
                <td><input name="password" placeholder="اتركه فارغًا بدون تغيير"></td>
                <td><?= $u['actif'] ? 'نشط' : 'معطل' ?></td>
                <td style="display:flex;gap:6px">
                    <button class="btn b1" type="submit">💾</button>
            </form>
            <form method="POST">
                <input type="hidden" name="action" value="toggle_user">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <button class="btn b2" type="submit"><?= $u['actif'] ? '🔕' : '🔔' ?></button>
            </form>
                </td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                <td><input name="nom" required></td>
                <td><input name="username" required></td>
                <td><select name="role"><option value="haifa">HAIFA</option><option value="khaoula">KHAOULA</option><option value="mohamed">MOHAMED</option><option value="admin">مدير</option></select></td>
                <td><input name="password" required></td>
                <td>جديد</td>
                <td><button class="btn b1">➕</button></td>
            </form>
        </tr>
    </table>
</div>

<div class="card">
    <h3>لائحة العناوين (المكان)</h3>
    <form method="POST" style="display:flex;gap:8px;margin-bottom:10px">
        <input type="hidden" name="action" value="add_address">
        <input name="libelle" placeholder="أضف عنوانًا جديدًا" required>
        <button class="btn b1">➕</button>
    </form>
    <div style="max-height:360px;overflow:auto">
        <table>
            <tr><th>ID</th><th>العنوان</th><th></th></tr>
            <?php foreach($addresses as $a): ?>
            <tr><td><?= $a['id'] ?></td><td><?= htmlspecialchars($a['libelle']) ?></td><td>
                <form method="POST"><input type="hidden" name="action" value="delete_address"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="btn b3" onclick="return confirm('حذف؟')">🗑️</button></form>
            </td></tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</div>
</body>
</html>
