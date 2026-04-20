<?php
require 'config.php';
requireLogin();
requireRole('admin');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $lib = trim($_POST['libelle'] ?? '');
        if ($lib !== '') $pdo->prepare("INSERT IGNORE INTO adresses (libelle) VALUES (?)")->execute([$lib]);
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) $pdo->prepare("DELETE FROM adresses WHERE id=?")->execute([$id]);
    }
    header('Location: adresses.php');
    exit;
}

$rows = $pdo->query("SELECT * FROM adresses ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head><meta charset="UTF-8"><title>العناوين</title></head>
<body style="font-family:Arial;background:#f0f2f5;direction:rtl">
<?php include '_menu.php'; ?>
<div style="max-width:900px;margin:20px auto;background:#fff;padding:20px;border-radius:12px">
    <h2>📍 إدارة العناوين</h2>
    <form method="POST" style="display:flex;gap:8px;margin-bottom:14px">
        <input type="hidden" name="action" value="add">
        <input type="text" name="libelle" placeholder="أضف عنوانا جديدا" style="flex:1;padding:8px">
        <button type="submit">➕ إضافة</button>
    </form>
    <table style="width:100%;border-collapse:collapse">
        <tr><th style="border:1px solid #ddd;padding:6px">#</th><th style="border:1px solid #ddd;padding:6px">العنوان</th><th style="border:1px solid #ddd;padding:6px">حذف</th></tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td style="border:1px solid #ddd;padding:6px;text-align:center"><?= (int)$r['id'] ?></td>
                <td style="border:1px solid #ddd;padding:6px"><?= htmlspecialchars($r['libelle']) ?></td>
                <td style="border:1px solid #ddd;padding:6px;text-align:center">
                    <form method="POST" onsubmit="return confirm('حذف العنوان؟')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button type="submit">🗑️</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
