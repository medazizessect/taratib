<?php
// Inclus depuis document.php quand ?print=1
// Variables disponibles: $v, $batiment, $id, $type, $label, $color, $intro
$num_doc  = $v['numero_doc']  ?: '............';
$date_doc = $v['date_doc']    ? date('d/m/Y', strtotime($v['date_doc'])) : date('d/m/Y');
$dr_fmt   = $v['date_rapport']      ? date('d/m/Y', strtotime($v['date_rapport']))      : '...';
$dex_fmt  = $v['date_expert']       ? date('d/m/Y', strtotime($v['date_expert']))       : '...';
$dizn_fmt = $v['date_izn_tribunal'] ? date('d/m/Y', strtotime($v['date_izn_tribunal'])) : '...';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($label) ?> — <?= htmlspecialchars($v['numero_rapport']) ?></title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{
            font-family:'Times New Roman',Times,serif;
            direction:rtl;background:white;color:#000;
            font-size:13pt;padding:18mm 22mm;
        }
        .no-print{
            position:fixed;top:10px;left:10px;
            display:flex;gap:8px;z-index:9999;
        }
        .np-btn{
            padding:8px 16px;border:none;border-radius:6px;
            cursor:pointer;font-size:14px;font-family:Arial;
            font-weight:600;
        }
        .entete{
            display:flex;justify-content:space-between;
            align-items:flex-start;margin-bottom:10mm;
            padding-bottom:5mm;border-bottom:2px solid #1a3c5e;
        }
        .entete-txt{font-size:11pt;line-height:2}
        .logo-box{text-align:center}
        .logo-circle{
            width:65px;height:65px;border-radius:50%;
            border:2px solid #1a3c5e;
            display:flex;align-items:center;justify-content:center;
            font-size:28px;margin:0 auto 4px;
        }
        .ref-line{
            display:flex;justify-content:space-between;
            margin-bottom:7mm;font-size:11pt;
        }
        .titre{text-align:center;margin:5mm 0 7mm}
        .titre h1{font-size:17pt;font-weight:bold;text-decoration:underline}
        .titre h2{font-size:12pt;font-weight:normal;margin-top:3mm}
        .intro{line-height:2;font-size:12pt;text-align:justify;margin-bottom:5mm}
        .data-block{
            border:1px solid #ddd;border-radius:5px;
            padding:6mm;margin:4mm 0;background:#fafafa;
        }
        .data-row{
            display:flex;gap:8mm;margin:2mm 0;font-size:11pt;
            border-bottom:1px solid #eee;padding-bottom:2mm;
        }
        .data-row:last-child{border-bottom:none}
        .dl{font-weight:bold;min-width:130px;flex-shrink:0}
        .dv{flex:1}
        .fasl{margin:4mm 0}
        .fasl-t{font-weight:bold;text-decoration:underline;font-size:12pt}
        .fasl-b{line-height:2;font-size:12pt;text-align:justify;margin-top:2mm}
        .signature{
            margin-top:15mm;display:flex;
            justify-content:flex-left;
        }
        .sig-block{text-align:center;min-width:180px}
        .sig-title{font-weight:bold;font-size:11pt;line-height:2}
        .sig-stamp{
            width:75px;height:75px;border-radius:50%;
            border:2px solid #1a3c5e;margin:6mm auto 3mm;
            display:flex;align-items:center;justify-content:center;
            font-size:9px;color:#1a3c5e;text-align:center;
        }
        .pied{
            position:fixed;bottom:5mm;left:0;right:0;
            text-align:center;font-size:9pt;color:#666;
            border-top:1px solid #ccc;padding-top:2mm;
        }
        @media print{
            .no-print{display:none!important}
            body{padding:10mm 15mm}
            @page{size:A4 portrait;margin:0}
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="np-btn"
            style="background:#28a745;color:white"
            onclick="window.print()">🖨️ طباعة</button>
    <a href="document.php?id=<?= $id ?>&type=<?= $type ?>"
       style="background:#6c757d;color:white;text-decoration:none"
       class="np-btn">↩️ رجوع</a>
</div>

<!-- En-tête -->
<div class="entete">
    <div class="entete-txt">
        الجمهورية التونسية<br>
        وزارة الداخلية<br>
        ولاية سوسة<br>
        <strong>بلدية سوسة</strong>
    </div>
    <div class="logo-box">
        <div class="logo-circle">🏛️</div>
        <div style="font-size:11pt">بلدية سوسة</div>
    </div>
    <div class="entete-txt" style="text-align:left;direction:ltr">
        République Tunisienne<br>
        Ministère de l'Intérieur<br>
        Gouvernorat de Sousse<br>
        <strong>Municipalité de Sousse</strong>
    </div>
</div>

<!-- Référence -->
<div class="ref-line">
    <div><strong>عدد:</strong> <?= htmlspecialchars($num_doc) ?></div>
    <div><strong>في:</strong> <?= $date_doc ?></div>
</div>

<!-- Titre -->
<div class="titre">
    <h1><?= htmlspecialchars($label) ?></h1>
    <h2>(بناء متداعي للسقوط)</h2>
</div>

<!-- Données communes -->
<div class="data-block">
    <div class="data-row">
        <span class="dl">عدد المحضر:</span>
        <span class="dv"><?= htmlspecialchars($v['numero_rapport']) ?></span>
    </div>
    <div class="data-row">
        <span class="dl">تاريخ المعاينة:</span>
        <span class="dv"><?= $dr_fmt ?></span>
    </div>
    <div class="data-row">
        <span class="dl">المكان:</span>
        <span class="dv"><?= htmlspecialchars($v['lieu']) ?></span>
    </div>
    <div class="data-row">
        <span class="dl">المالك / المشغول:</span>
        <span class="dv"><?= htmlspecialchars($v['proprietaire']) ?></span>
    </div>
    <?php if (!empty($v['nom_expert'])): ?>
    <div class="data-row">
        <span class="dl">الخبير العدلي:</span>
        <span class="dv"><?= htmlspecialchars($v['nom_expert']) ?> — <?= $dex_fmt ?></span>
    </div>
    <?php endif; ?>
    <?php if ($type === 'izn_tribunal' && !empty($v['nom_juge'])): ?>
    <div class="data-row">
        <span class="dl">القاضي / الوكيل:</span>
        <span class="dv"><?= htmlspecialchars($v['nom_juge']) ?></span>
    </div>
    <div class="data-row">
        <span class="dl">تاريخ الإذن:</span>
        <span class="dv"><?= $dizn_fmt ?></span>
    </div>
    <?php endif; ?>
</div>

<!-- Intro légale -->
<div class="intro"><?= nl2br(htmlspecialchars($intro)) ?></div>

<!-- Description -->
<?php if (!empty($v['description_batiment'])): ?>
<div class="fasl">
    <div class="fasl-t">وصف البناية:</div>
    <div class="fasl-b"><?= nl2br(htmlspecialchars($v['description_batiment'])) ?></div>
</div>
<?php endif; ?>

<!-- Contenu spécifique -->
<?php if (!empty($v['contenu_specifique'])): ?>
<div class="fasl">
    <?php if (in_array($type, ['evacuation','demolition'])): ?>
    <div class="fasl-t">الفصل الأول:</div>
    <?php endif; ?>
    <div class="fasl-b"><?= nl2br(htmlspecialchars($v['contenu_specifique'])) ?></div>
</div>
<?php endif; ?>

<!-- Fassls standards -->
<?php if ($type === 'evacuation'): ?>
<div class="fasl">
    <div class="fasl-t">الفصل الثاني:</div>
    <div class="fasl-b">الكاتب العام لبلدية سوسة ورئيس مركز الشرطة البلدية مكلفان كل فيما يخصه بتنفيذ هذا القرار.</div>
</div>
<?php elseif ($type === 'demolition'): ?>
<div class="fasl">
    <div class="fasl-t">الفصل الثاني:</div>
    <div class="fasl-b">يأخذ المقاول جميع الاحتياطات اللازمة عند التنفيذ مع وجوبية إبرام عقد تأمين.</div>
</div>
<div class="fasl">
    <div class="fasl-t">الفصل الثالث:</div>
    <div class="fasl-b">الكاتب العام لبلدية سوسة مكلف بتنفيذ هذا القرار.</div>
</div>
<?php endif; ?>

<!-- Observations -->
<?php if (!empty($v['observations'])): ?>
<div class="fasl">
    <div class="fasl-t">ملاحظات:</div>
    <div class="fasl-b"><?= nl2br(htmlspecialchars($v['observations'])) ?></div>
</div>
<?php endif; ?>

<!-- Signature -->
<?php if (in_array($type, ['evacuation','demolition','courrier_expert','turat'])): ?>
<div class="signature">
    <div class="sig-block">
        <div class="sig-title">
            الكاتب العام<br>
            المكلف بتسيير الشؤون البلدية
        </div>
        <div class="sig-stamp">الختم<br>الرسمي</div>
    </div>
</div>
<?php elseif ($type === 'izn_tribunal'): ?>
<div style="text-align:center;margin-top:15mm;font-size:12pt;line-height:2">
    سوسة في <?= $dizn_fmt ?><br>
    الوكيل الأول لرئيس المحكمة الابتدائية بسوسة 1<br>
    <strong><?= htmlspecialchars($v['nom_juge'] ?: '...........') ?></strong>
</div>
<?php endif; ?>

<div class="pied">
    بلدية سوسة — شارع محمد الخامس 4000 سوسة
</div>
</body>
</html>