<?php
require '_steps_config.php';

// Charger les documents existants
$existingDocs = [];
foreach (STEPS as $dtype => $cfg) {
    $d = $pdo->prepare("
        SELECT statut FROM documents_officiels
        WHERE batiment_id=? AND type=?
    ");
    $d->execute([$id, $dtype]);
    $res = $d->fetchColumn();
    if ($res !== false) $existingDocs[$dtype] = $res;
}
?>

<!-- Section stepper dans le formulaire -->
<div class="sec sec-docs">📜 مسار الإجراءات الرسمية</div>
<div class="fg full">

    <div class="doc-stepper">
    <?php
    $stepNum  = 1;
    $stepKeys = array_keys(STEPS);
    $lastKey  = end($stepKeys);

    foreach (STEPS as $dtype => $cfg):
        $icon      = $cfg['icon'];
        $label     = $cfg['label'];
        $color     = $cfg['color'];
        $req       = $cfg['requires'];
        $optional  = $cfg['optional'];

        $statut    = $existingDocs[$dtype] ?? false;
        $exists    = $statut !== false;
        $isFinal   = $statut === 'finalise';
        $isDraft   = $statut === 'brouillon';
        $isLocked  = $req && !isset($existingDocs[$req]);
        $isLast    = ($dtype === $lastKey);

        // Couleurs
        if ($isFinal) {
            $bg='#1a3c5e'; // override per color
            $bg=$color; $tc='white';
            $numBg='rgba(255,255,255,.25)';$numC='white';$numB='rgba(255,255,255,.5)';
            $stTxt='✅ نهائي';$stBg='rgba(255,255,255,.2)';$stC='white';
            $prTxt='🖨️ جاهز';$prStyle='background:rgba(255,255,255,.15);color:white';
        } elseif ($isDraft) {
            $bg=$color.'15'; $tc=$color;
            $numBg='white';$numC=$color;$numB=$color;
            $stTxt='✏️ مسودة';$stBg=$color.'20';$stC=$color;
            $prTxt='🔒 غير جاهز';$prStyle="background:{$color}10;color:{$color}";
        } elseif ($isLocked) {
            $bg='#f7f7f7'; $tc='#ccc';
            $numBg='#eee';$numC='#bbb';$numB='#ddd';
            $stTxt='🔒 مقفل';$stBg='#e8e8e8';$stC='#aaa';
            $prTxt='';$prStyle='';
        } elseif ($optional) {
            $bg='#e8f8fb'; $tc='#17a2b8';
            $numBg='#17a2b8';$numC='white';$numB='#17a2b8';
            $stTxt='⭕ اختياري';$stBg='#d1ecf1';$stC='#0c5460';
            $prTxt='';$prStyle='';
        } else {
            $bg='white'; $tc=$color;
            $numBg=$color;$numC='white';$numB=$color;
            $stTxt='➕ جديد';$stBg=$color.'15';$stC=$color;
            $prTxt='';$prStyle='';
        }
    ?>

    <div class="doc-step"
         style="<?= !$isLast ? 'border-left:2px solid #e8e8e8' : '' ?>
                <?= $optional ? 'border-top:3px dashed #17a2b8' : "border-top:3px solid {$color}" ?>">

        <?php if ($isLocked): ?>
        <div class="doc-step-inner"
             style="background:<?= $bg ?>;cursor:not-allowed">
        <?php else: ?>
        <a href="document.php?id=<?= $id ?>&type=<?= $dtype ?>"
           class="doc-step-inner"
           style="background:<?= $bg ?>">
        <?php endif; ?>

            <!-- Badge facultatif -->
            <?php if ($optional): ?>
            <div style="font-size:9px;background:#17a2b8;color:white;
                        padding:1px 7px;border-radius:10px;margin-bottom:6px;
                        font-weight:700">اختياري</div>
            <?php endif; ?>

            <!-- Numéro -->
            <div class="doc-step-num"
                 style="background:<?= $numBg ?>;color:<?= $numC ?>;
                        border-color:<?= $numB ?>">
                <?= $isFinal ? '✓' : ($isLocked ? '🔒' : $stepNum) ?>
            </div>

            <!-- Icône -->
            <div class="doc-step-icon"><?= $icon ?></div>

            <!-- Label -->
            <div class="doc-step-label" style="color:<?= $tc ?>">
                <?= $label ?>
            </div>

            <!-- Statut -->
            <div class="doc-step-status"
                 style="background:<?= $stBg ?>;color:<?= $stC ?>">
                <?= $stTxt ?>
            </div>

            <!-- Impression -->
            <?php if ($prTxt): ?>
            <div class="doc-step-print" style="<?= $prStyle ?>">
                <?= $prTxt ?>
            </div>
            <?php endif; ?>

        <?php if ($isLocked): ?>
        </div>
        <?php else: ?>
        </a>
        <?php endif; ?>

    </div>

    <?php $stepNum++; endforeach; ?>
    </div>

    <!-- Légende -->
    <div style="display:flex;gap:14px;margin-top:10px;
                flex-wrap:wrap;font-size:11px;color:#888;
                justify-content:center">
        <span style="color:#17a2b8">⭕ اختياري — يمكن تجاوزه</span>
        <span>➕ جديد</span>
        <span style="color:#e67e22">✏️ مسودة — لا يطبع</span>
        <span style="color:#28a745">✅ نهائي — 🖨️ قابل للطباعة</span>
        <span>🔒 مقفل — أتمم السابق</span>
    </div>
</div>