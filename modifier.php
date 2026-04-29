<?php
require 'config.php';
requireLogin();
require 'db.php';
require '_steps_config.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM batiments WHERE id=?");
$stmt->execute([$id]);
$case = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$case) { header("Location: index.php"); exit; }

$docs = [];
$d = $pdo->prepare("SELECT type, statut FROM documents_officiels WHERE batiment_id=?");
$d->execute([$id]);
foreach ($d->fetchAll(PDO::FETCH_ASSOC) as $r) $docs[$r['type']] = $r['statut'];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasStepAccess('step1_reclamation')) {
    $bureau = trim($_POST['bureau_ordre_id'] ?? '');
    if ($bureau === '') $errors[] = 'ID du bureau d\'ordre مطلوب';
    if (empty($errors)) {
        $pdo->prepare("UPDATE batiments SET bureau_ordre_id=?, date_reclamation=?, proprietaire=?, notification_pending=? WHERE id=?")
            ->execute([
                $bureau,
                ($_POST['date_reclamation'] ?? '') ?: null,
                trim($_POST['proprietaire'] ?? '') ?: null,
                !empty($_POST['notification_pending']) ? 1 : 0,
                $id
            ]);
        header("Location: modifier.php?id=$id&saved=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>متابعة الملف</title>
    <?php include '_styles_form.php'; ?>
</head>
<body>
<?php include '_menu.php'; ?>
<header style="background:linear-gradient(135deg,#1a3c5e,#2e6da4)"><h1>📁 متابعة الملف</h1></header>
<div class="wrap">
    <h2>الملف: <?= htmlspecialchars($case['bureau_ordre_id']) ?></h2>
    <?php if (!empty($_GET['saved'])): ?><div class="alert alert-success">✅ تم التحديث</div><?php endif; ?>
    <?php if ($errors): ?><div class="error-box"><ul><?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul></div><?php endif; ?>

    <div class="sec sec-docs">📜 مسار الإجراءات (5 مراحل)</div>
    <div class="fg full">
        <div class="doc-stepper">
        <?php foreach (STEPS as $dtype => $cfg):
            $cls = getStepClass($dtype, $docs);
            $locked = ($cfg['requires'] && !isset($docs[$cfg['requires']]));
            $canAccess = ($dtype === 'step1_reclamation') ? hasStepAccess('step1_reclamation') : hasStepAccess($dtype);
            ?>
            <div class="doc-step">
                <?php if ($dtype === 'step1_reclamation'): ?>
                    <span class="doc-step-inner" style="background:#ffe9ec">
                        <div class="doc-step-num">1</div><div class="doc-step-icon">🧾</div>
                        <div class="doc-step-label">شكاوي</div><div class="doc-step-status">الحالة الحالية</div>
                    </span>
                <?php elseif ($locked || !$canAccess): ?>
                    <span class="doc-step-inner" style="background:#f5f5f5;color:#aaa">
                        <div class="doc-step-num">🔒</div><div class="doc-step-icon"><?= $cfg['icon'] ?></div>
                        <div class="doc-step-label"><?= htmlspecialchars($cfg['label']) ?></div>
                        <div class="doc-step-status"><?= $locked ? 'أكمل المرحلة السابقة' : 'غير مصرح' ?></div>
                    </span>
                <?php else: ?>
                    <a class="doc-step-inner" href="document.php?id=<?= $id ?>&type=<?= $dtype ?>" style="background:white">
                        <div class="doc-step-num"><?= $cfg['step_num'] ?></div><div class="doc-step-icon"><?= $cfg['icon'] ?></div>
                        <div class="doc-step-label"><?= htmlspecialchars($cfg['label']) ?></div>
                        <div class="doc-step-status"><?= htmlspecialchars($docs[$dtype] ?? 'جديد') ?></div>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <?php if (hasStepAccess('step1_reclamation')): ?>
    <form method="POST">
        <div class="grid">
            <div class="fg"><label>ID bureau d'ordre</label><input type="text" name="bureau_ordre_id" value="<?= htmlspecialchars($case['bureau_ordre_id']) ?>"></div>
            <div class="fg"><label>التاريخ</label><input type="date" name="date_reclamation" value="<?= htmlspecialchars($case['date_reclamation']) ?>"></div>
            <div class="fg full"><label>المالك</label><input type="text" name="proprietaire" value="<?= htmlspecialchars($case['proprietaire']) ?>"></div>
            <div class="fg full"><label><input type="checkbox" name="notification_pending" value="1" <?= !empty($case['notification_pending']) ? 'checked' : '' ?>> إشعار بمعلومات غير معالجة</label></div>
        </div>
        <div class="btn-row">
            <button class="btn btn-success" type="submit">💾 حفظ الشكاية</button>
            <a href="index.php" class="btn btn-secondary">↩️ رجوع</a>
        </div>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
