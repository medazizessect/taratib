<?php
error_reporting(0);
ini_set('display_errors', 0);
require 'config.php';
requireLogin();
if (!canEditStep('reclamation')) {
    requireRole('admin');
}
require 'db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty(trim($_POST['numero_rapport'] ?? ''))) $errors[] = 'ID Bureau d\'Ordre مطلوب';
    if (empty($_POST['date_rapport'] ?? '')) $errors[] = 'تاريخ الشكاية مطلوب';
    if (empty($_FILES['reclamation_file']['name'] ?? '')) $errors[] = 'ملف الشكاية (PDF/Scan) مطلوب';

    $savedFile = null;
    if (empty($errors)) {
        $uploadDir = __DIR__ . '/uploads/reclamations';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

        $original = $_FILES['reclamation_file']['name'];
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed, true)) {
            $errors[] = 'صيغة الملف غير مدعومة';
        } else {
            $safeName = uniqid('rec_', true) . '.' . $ext;
            $target = $uploadDir . '/' . $safeName;
            if (!move_uploaded_file($_FILES['reclamation_file']['tmp_name'], $target)) {
                $errors[] = 'فشل رفع الملف';
            } else {
                $savedFile = 'uploads/reclamations/' . $safeName;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO batiments
                (numero_rapport,lieu,proprietaire,reclamation_file,extraction_auto,notification,date_rapport)
            VALUES(:nr,:lieu,:prop,:rf,:ea,:notif,:dr)
        ");
        $stmt->execute([
            ':nr'   => trim($_POST['numero_rapport']),
            ':lieu' => trim($_POST['lieu'] ?? '') ?: '—',
            ':prop' => trim($_POST['proprietaire'] ?? '') ?: null,
            ':rf'   => $savedFile,
            ':ea'   => !empty($_POST['extraction_auto']) ? 1 : 0,
            ':notif'=> !empty($_POST['notification']) ? 'غير معالجة' : null,
            ':dr'   => $_POST['date_rapport'],
        ]);
        header("Location: index.php?msg=added"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة شكاية جديدة</title>
    <?php include '_styles_form.php'; ?>
</head>
<body>
<header style="background:linear-gradient(135deg,#dc3545,#c82333)">
    <img src="Logo_commune_Sousse.svg" alt="Logo" style="width:36px;height:36px;position:absolute;left:16px;top:14px;background:white;border-radius:50%;padding:3px">
    <h1>➕ إضافة شكاية (المرحلة 1)</h1>
</header>
<div class="wrap">
    <h2>📝 بيانات الشكاية</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-box">⚠️ يرجى تصحيح الأخطاء:
            <ul><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="grid">
            <div class="fg">
                <label><span class="req">*</span> ID Bureau d'Ordre</label>
                <input type="text" name="numero_rapport"
                       value="<?= htmlspecialchars($_POST['numero_rapport'] ?? '') ?>">
            </div>
            <div class="fg">
                <label><span class="req">*</span> التاريخ</label>
                <input type="date" name="date_rapport"
                       value="<?= $_POST['date_rapport'] ?? '' ?>">
            </div>
            <div class="fg full">
                <label>المالك (اختياري)</label>
                <input type="text" name="proprietaire"
                       value="<?= htmlspecialchars($_POST['proprietaire'] ?? '') ?>">
            </div>
            <div class="fg full">
                <label>العنوان (اختياري)</label>
                <input type="text" name="lieu"
                       value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>">
            </div>
            <div class="fg full">
                <label><span class="req">*</span> الشكاية الممسوحة/PDF</label>
                <input type="file" name="reclamation_file" accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <div class="fg">
                <label><input type="checkbox" name="extraction_auto" value="1" <?= !empty($_POST['extraction_auto']) ? 'checked' : '' ?>> استخراج بيانات آلي من الوثيقة</label>
            </div>
            <div class="fg">
                <label><input type="checkbox" name="notification" value="1" <?= !empty($_POST['notification']) ? 'checked' : '' ?>> تفعيل تنبيه بيانات غير معالجة</label>
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn btn-success">💾 حفظ الشكاية</button>
            <a href="index.php" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>
</body>
</html>
