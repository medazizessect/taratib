<?php
$numDoc = $v['numero_doc'] ?? '';
$dateDoc = !empty($v['date_doc']) ? date('d/m/Y', strtotime($v['date_doc'])) : date('d/m/Y');
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($label) ?></title>
<style>
*{box-sizing:border-box} body{font-family:'Times New Roman',serif;direction:rtl;padding:16mm}
.top{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #1a3c5e;padding-bottom:5mm}
.logo img{width:70px;height:70px;object-fit:contain}
.blk{font-size:11pt;line-height:1.9}
.title{text-align:center;margin:8mm 0 5mm} .title h1{font-size:17pt;text-decoration:underline}
.meta{display:flex;justify-content:space-between;margin-bottom:5mm}
.card{border:1px solid #ddd;background:#fafafa;border-radius:6px;padding:5mm}
.r{margin:2mm 0}
@media print{.noprint{display:none}}
</style>
</head>
<body>
<div class="noprint" style="position:fixed;top:10px;left:10px"><button onclick="window.print()">🖨️ طباعة</button></div>
<div class="top">
  <div class="blk">الجمهورية التونسية<br>وزارة الداخلية<br>بلدية سوسة</div>
  <div class="logo"><img src="Logo_commune_Sousse.svg" alt="logo"></div>
  <div class="blk" style="direction:ltr;text-align:left">République Tunisienne<br>Ministère de l'Intérieur<br>Municipalité de Sousse</div>
</div>
<div class="title"><h1><?= htmlspecialchars($label) ?></h1></div>
<div class="meta"><div><b>عدد:</b> <?= htmlspecialchars($numDoc ?: '...') ?></div><div><b>في:</b> <?= $dateDoc ?></div></div>
<div class="card">
  <div class="r"><b>ID bureau d'ordre:</b> <?= htmlspecialchars($case['bureau_ordre_id'] ?? '') ?></div>
  <div class="r"><b>المالك:</b> <?= htmlspecialchars($case['proprietaire'] ?? '') ?></div>
  <?php if (!empty($v['cin'])): ?><div class="r"><b>CIN:</b> <?= htmlspecialchars($v['cin']) ?></div><?php endif; ?>
  <?php if (!empty($v['owner_name'])): ?><div class="r"><b>مالك:</b> <?= htmlspecialchars($v['owner_name']) ?></div><?php endif; ?>
  <?php if (!empty($v['occupied_by'])): ?><div class="r"><b>المشغول:</b> <?= htmlspecialchars($v['occupied_by']) ?></div><?php endif; ?>
  <?php if (!empty($v['subject'])): ?><div class="r"><b>الموضوع:</b> <?= htmlspecialchars($v['subject']) ?></div><?php endif; ?>
  <?php if (!empty($v['expert_name'])): ?><div class="r"><b>الخبير:</b> <?= htmlspecialchars($v['expert_name']) ?></div><?php endif; ?>
  <?php if (!empty($v['report_type'])): ?><div class="r"><b>نوع التقرير:</b> <?= $v['report_type'] === 'initial' ? 'أولي' : 'نهائي' ?></div><?php endif; ?>
  <?php if (!empty($v['decision_type'])): ?><div class="r"><b>القرار:</b> <?= $v['decision_type'] === 'evacuation' ? 'إخلاء' : 'هدم' ?></div><?php endif; ?>
  <?php if (!empty($v['observations'])): ?><div class="r"><b>ملاحظات:</b><br><?= nl2br(htmlspecialchars($v['observations'])) ?></div><?php endif; ?>
</div>
</body>
</html>
