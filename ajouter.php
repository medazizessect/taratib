<?php
error_reporting(0);
ini_set('display_errors', 0);
require 'db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty(trim($_POST['numero_rapport'] ?? ''))) $errors[] = 'عدد المحضر مطلوب';
    if (empty(trim($_POST['lieu']           ?? ''))) $errors[] = 'المكان مطلوب';

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO batiments
                (numero_rapport,lieu,proprietaire,mise_a_jour,notification,
                 date_rapport,exploite_oui,exploite_non,commission,
                 date_envoi_tratiib,date_envoi_wiz,date_envoi_turat,
                 date_envoi_juridique,date_expert,
                 decision_evacuation,decision_demolition,observations)
            VALUES(:nr,:lieu,:prop,:maj,:notif,:dr,:eoui,:enon,:com,
                   :det,:dew,:detr,:dej,:dex,:deva,:ddem,:obs)
        ");
        $stmt->execute([
            ':nr'   => trim($_POST['numero_rapport']),
            ':lieu' => trim($_POST['lieu']),
            ':prop' => trim($_POST['proprietaire']        ?? '') ?: null,
            ':maj'  => trim($_POST['mise_a_jour']         ?? '') ?: null,
            ':notif'=> trim($_POST['notification']        ?? '') ?: null,
            ':dr'   => ($_POST['date_rapport']            ?? '') ?: null,
            ':eoui' => ($_POST['exploite'] ?? '') === 'oui' ? 1 : 0,
            ':enon' => ($_POST['exploite'] ?? '') === 'non' ? 1 : 0,
            ':com'  => trim($_POST['commission']          ?? '') ?: null,
            ':det'  => ($_POST['date_envoi_tratiib']      ?? '') ?: null,
            ':dew'  => ($_POST['date_envoi_wiz']          ?? '') ?: null,
            ':detr' => ($_POST['date_envoi_turat']        ?? '') ?: null,
            ':dej'  => ($_POST['date_envoi_juridique']    ?? '') ?: null,
            ':dex'  => ($_POST['date_expert']             ?? '') ?: null,
            ':deva' => trim($_POST['decision_evacuation'] ?? '') ?: null,
            ':ddem' => trim($_POST['decision_demolition'] ?? '') ?: null,
            ':obs'  => trim($_POST['observations']        ?? '') ?: null,
        ]);
        header("Location: index.php?msg=added"); exit;
    }
}

// Valeur exploite
$exploiteVal = '';
if (isset($_POST['exploite'])) $exploiteVal = $_POST['exploite'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة محضر جديد</title>
    <?php include '_styles_form.php'; ?>
</head>
<body>
<header style="background:linear-gradient(135deg,#1a3c5e,#2e6da4)">
    <h1>➕ إضافة محضر جديد</h1>
</header>
<div class="wrap">
    <h2>📋 بيانات المحضر الجديد</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-box">⚠️ يرجى تصحيح الأخطاء:
            <ul><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="grid">

            <!-- ── معلومات أساسية ── -->
            <div class="sec">📄 المعلومات الأساسية</div>

            <div class="fg">
                <label><span class="req">*</span> عدد المحضر</label>
                <input type="text" name="numero_rapport"
                       placeholder="مثال: 24/7"
                       value="<?= htmlspecialchars($_POST['numero_rapport'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>تاريخ محضر المعاينة</label>
                <input type="date" name="date_rapport"
                       value="<?= $_POST['date_rapport'] ?? '' ?>">
            </div>
            <div class="fg full">
                <label><span class="req">*</span> المكان</label>
                <input type="text" name="lieu"
                       placeholder="العنوان التفصيلي للبناية"
                       value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>المالك / المشغول</label>
                <input type="text" name="proprietaire"
                       value="<?= htmlspecialchars($_POST['proprietaire'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>تحيين</label>
                <input type="text" name="mise_a_jour"
                       value="<?= htmlspecialchars($_POST['mise_a_jour'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>الإشعار</label>
                <input type="text" name="notification"
                       value="<?= htmlspecialchars($_POST['notification'] ?? '') ?>">
            </div>

            <!-- ── مستغلة Toggle ── -->
            <div class="fg">
                <label>مستغلة من طرف أشخاص</label>
                <div class="toggle-group">
                    <div class="toggle-option opt-oui">
                        <input type="radio" name="exploite" value="oui"
                               id="exp_oui_a"
                               <?= $exploiteVal === 'oui' ? 'checked' : '' ?>>
                        <label for="exp_oui_a">✅ نعم</label>
                    </div>
                    <div class="toggle-sep"></div>
                    <div class="toggle-option opt-non">
                        <input type="radio" name="exploite" value="non"
                               id="exp_non_a"
                               <?= $exploiteVal === 'non' ? 'checked' : '' ?>>
                        <label for="exp_non_a">❌ لا</label>
                    </div>
                </div>
            </div>

            <!-- ── اللجنة ── -->
            <div class="sec">👥 اللجنة</div>
            <div class="fg full">
                <label>أعضاء اللجنة</label>
                <?php $commissionValue = $_POST['commission'] ?? ''; include '_commission.php'; ?>
            </div>

            <!-- ── توجيه الأوراق ── -->
            <div class="sec">📅 تواريخ توجيه جداول الأوراق</div>

            <div class="fg">
                <label>توجيه التراتيب</label>
                <input type="date" name="date_envoi_tratiib"
                       value="<?= $_POST['date_envoi_tratiib'] ?? '' ?>">
            </div>
            <div class="fg">
                <label>توجيه وزارة التجهيز</label>
                <input type="date" name="date_envoi_wiz"
                       value="<?= $_POST['date_envoi_wiz'] ?? '' ?>">
            </div>
            <div class="fg">
                <label>توجيه المعهد الوطني للتراث</label>
                <input type="date" name="date_envoi_turat"
                       value="<?= $_POST['date_envoi_turat'] ?? '' ?>">
            </div>
            <div class="fg">
                <label>توجيه إدارة الشؤون القانونية</label>
                <input type="date" name="date_envoi_juridique"
                       value="<?= $_POST['date_envoi_juridique'] ?? '' ?>">
            </div>

            <!-- ── القرارات ── -->
            <div class="sec">⚖️ القرارات والنتائج</div>

            <div class="fg">
                <label>تاريخ محضر الخبير</label>
                <input type="date" name="date_expert"
                       value="<?= $_POST['date_expert'] ?? '' ?>">
            </div>
            <div class="fg">
                <label>قرار إخلاء</label>
                <input type="text" name="decision_evacuation"
                       value="<?= htmlspecialchars($_POST['decision_evacuation'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>قرار هدم</label>
                <input type="text" name="decision_demolition"
                       value="<?= htmlspecialchars($_POST['decision_demolition'] ?? '') ?>">
            </div>
            <div class="fg full">
                <label>ملاحظات</label>
                <textarea name="observations"><?= htmlspecialchars($_POST['observations'] ?? '') ?></textarea>
            </div>

        </div>

        <div class="btn-row">
            <button type="submit" class="btn btn-success">💾 حفظ المحضر</button>
            <a href="index.php" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>
</body>
</html>