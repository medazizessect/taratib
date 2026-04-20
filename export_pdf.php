<?php
require 'config.php';
requireLogin();
require 'db.php';
if (!userCan('export_tables')) die('غير مصرح');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM batiments
        WHERE numero_rapport LIKE :s OR lieu LIKE :s
           OR proprietaire LIKE :s OR observations LIKE :s
        ORDER BY id ASC
    ");
    $stmt->execute([':s' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM batiments ORDER BY id ASC");
}
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>تصدير PDF</title>
<style>
body{font-family:Arial;padding:16px}.no-print{position:fixed;left:12px;top:12px}
table{width:100%;border-collapse:collapse;font-size:12px}th,td{border:1px solid #ccc;padding:6px;text-align:center}th{background:#1a3c5e;color:#fff}
@media print{.no-print{display:none}}
</style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print()">🖨️ طباعة / PDF</button>
    <a href="index.php">↩️ رجوع</a>
</div>
<h3>جدول البنايات المتداعية للسقوط</h3>
<table>
    <thead><tr><th>#</th><th>عدد المحضر</th><th>المكان</th><th>المالك / المشغول</th><th>تاريخ المعاينة</th><th>اللجنة</th><th>ملاحظات</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($r['numero_rapport'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['lieu'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['proprietaire'] ?? '') ?></td>
            <td><?= !empty($r['date_rapport']) ? date('d/m/Y', strtotime($r['date_rapport'])) : '' ?></td>
            <td><?= htmlspecialchars($r['commission'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['observations'] ?? '') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
