<?php
require 'config.php';
requireLogin();
if (!hasRole('admin') || !userCan('manage_grades')) requireRole('admin');
require 'db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $label = trim($_POST['label'] ?? '');
        if ($label !== '') {
            $max = (int)$pdo->query("SELECT COALESCE(MAX(ordre),0) FROM grades")->fetchColumn();
            $pdo->prepare("INSERT IGNORE INTO grades (label, ordre) VALUES (?, ?)")->execute([$label, $max + 1]);
            $msg = 'saved';
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        if ($id > 0 && $label !== '') {
            $pdo->prepare("UPDATE grades SET label=? WHERE id=?")->execute([$label, $id]);
            $msg = 'saved';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM grades WHERE id=?")->execute([$id]);
            $msg = 'deleted';
        }
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE grades SET actif = 1 - actif WHERE id=?")->execute([$id]);
            $msg = 'saved';
        }
    }
}
$grades = $pdo->query("SELECT * FROM grades ORDER BY ordre ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><title>إدارة الدرجات</title>
<style>body{font-family:Arial;background:#f5f5f5;padding:20px}.card{background:#fff;padding:16px;border-radius:10px;margin-bottom:14px}.row{display:flex;gap:8px;align-items:center;margin:6px 0}input{padding:8px}button{padding:8px 10px}.msg{padding:8px;background:#d4edda}</style>
</head>
<body>
<a href="index.php">↩️ رجوع</a>
<h2>إدارة درجات أعضاء اللجنة</h2>
<?php if ($msg): ?><div class="msg">✅ تم التحديث</div><?php endif; ?>
<div class="card">
    <form method="post" class="row">
        <input type="hidden" name="action" value="add">
        <input type="text" name="label" placeholder="الدرجة الجديدة" required>
        <button type="submit">➕ إضافة</button>
    </form>
</div>
<div class="card">
<?php foreach ($grades as $g): ?>
    <div class="row">
        <form method="post" class="row" style="flex:1">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
            <input type="text" name="label" value="<?= htmlspecialchars($g['label']) ?>" required style="flex:1">
            <button type="submit">💾</button>
        </form>
        <form method="post"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$g['id'] ?>"><button><?= $g['actif'] ? 'تعطيل' : 'تفعيل' ?></button></form>
        <form method="post" onsubmit="return confirm('حذف الدرجة؟')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$g['id'] ?>"><button>🗑️</button></form>
    </div>
<?php endforeach; ?>
</div>
</body></html>
