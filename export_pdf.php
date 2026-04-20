<?php
require 'config.php';
requireLogin();
require 'db.php';

if (!userCan('can_export')) {
    header('Location: index.php');
    exit;
}

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
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تصدير PDF</title>
    <style>
        body{font-family:Arial,sans-serif;direction:rtl;margin:20px}
        h1{font-size:20px;margin-bottom:8px}
        table{width:100%;border-collapse:collapse;font-size:12px}
        th,td{border:1px solid #999;padding:6px;text-align:center}
        th{background:#1a3c5e;color:#fff}
        .no-print{margin-bottom:10px}
        @media print{.no-print{display:none}}
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ طباعة / PDF</button>
        <a href="index.php">↩️ رجوع</a>
    </div>
    <h1>جدول البنايات المتداعية للسقوط</h1>
    <table>
        <thead><tr><th>#</th><th>عدد المحضر</th><th>المكان</th><th>المالك</th><th>تاريخ المعاينة</th><th>اللجنة</th><th>ملاحظات</th></tr></thead>
        <tbody>
            <?php foreach ($rows as $i => $r): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($r['numero_rapport']) ?></td>
                <td><?= htmlspecialchars($r['lieu']) ?></td>
                <td><?= htmlspecialchars($r['proprietaire'] ?? '') ?></td>
                <td><?= $r['date_rapport'] ? date('d/m/Y', strtotime($r['date_rapport'])) : '' ?></td>
                <td><?= htmlspecialchars($r['commission'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['observations'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
