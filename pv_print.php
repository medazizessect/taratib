<?php
require 'config.php';
requireLogin();
require 'db.php';

$id = intval($_GET['id'] ?? 0);
$q = $pdo->prepare("SELECT * FROM batiments WHERE id=?");
$q->execute([$id]);
$b = $q->fetch(PDO::FETCH_ASSOC);
if (!$b) die('PV introuvable');

$commission = [];
if (!empty($b['commission_json'])) {
    $decoded = json_decode($b['commission_json'], true);
    if (is_array($decoded)) {
        $gradeMap = [];
        foreach ($pdo->query("SELECT id, label FROM grades") as $g) {
            $gradeMap[(int)$g['id']] = $g['label'];
        }
        foreach ($decoded as $r) {
            $nom = trim((string)($r['nom'] ?? ''));
            if ($nom === '') continue;
            $gid = (int)($r['grade_id'] ?? 0);
            $commission[] = ['nom' => $nom, 'grade' => $gradeMap[$gid] ?? ''];
        }
    }
}

$dRapport = !empty($b['date_rapport']) ? date('d-m-Y', strtotime($b['date_rapport'])) : '........';
$dBureau  = !empty($b['date_bureau_ordre']) ? date('d-m-Y', strtotime($b['date_bureau_ordre'])) : '........';
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>محضر معاينة</title>
<style>
body{font-family:'Times New Roman',serif;line-height:1.9;padding:20px;font-size:14pt}
.top{display:flex;justify-content:space-between;margin-bottom:20px}
.y{background:#ffeb3b;padding:0 4px}
h2{text-align:center;text-decoration:underline;margin:15px 0}
ul{margin:0 20px}
.no-print{position:fixed;left:12px;top:12px}
@media print{.no-print{display:none}}
</style>
</head>
<body>
<div class="no-print"><button onclick="window.print()">🖨️ طباعة</button></div>
<div class="top">
    <div>الجمهورية التونسية<br>بلدية سوسة</div>
    <div>عدد: <span class="y"><?= htmlspecialchars($b['numero_rapport'] ?: '...') ?></span></div>
</div>
<h2>محضر معاينة لجنة البنايات المتداعية للسقوط</h2>
<p>
وبناء على إشعار السيد(ة) <span class="y"><?= htmlspecialchars($b['notification_region'] ?: '................') ?></span>
والمضمن بالضبط المركزي تحت عدد <span class="y"><?= htmlspecialchars($b['numero_bureau_ordre'] ?: '...') ?></span>
بتاريخ <span class="y"><?= $dBureau ?></span>.
</p>
<p>تكونت اللجنة من السادة:</p>
<ul>
    <?php foreach ($commission as $m): ?>
        <li><span class="y"><?= htmlspecialchars($m['nom']) ?></span> : <span class="y"><?= htmlspecialchars($m['grade']) ?></span></li>
    <?php endforeach; ?>
</ul>
<p>
في يوم <span class="y"><?= htmlspecialchars($dRapport) ?></span>
على الساعة <span class="y"><?= htmlspecialchars($b['heure_constat'] ?: '...') ?></span>
للعقار الكائن بـ <span class="y"><?= htmlspecialchars($b['lieu'] ?: '...') ?></span>.
</p>
<p>
المالك/الملاك: <span class="y"><?= htmlspecialchars($b['proprietaire'] ?: '...') ?></span><br>
المشغول من طرف: <span class="y"><?= htmlspecialchars($b['occupe_par'] ?: '...') ?></span><br>
رقم ب.ت.و: <span class="y"><?= htmlspecialchars($b['cin'] ?: '...') ?></span><br>
درجة التأكيد: <span class="y"><?= htmlspecialchars($b['degre_confirmation'] ?: '...') ?></span>
</p>
<p><strong>وصف العقار:</strong><br><span class="y"><?= nl2br(htmlspecialchars($b['description_detaillee'] ?: '...')) ?></span></p>
<p><strong>الإجراءات الوقائية والاستعجالية المقترحة:</strong><br><span class="y"><?= nl2br(htmlspecialchars($b['mesures_urgentes'] ?: '...')) ?></span></p>
</body>
</html>
