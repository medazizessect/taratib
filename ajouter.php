<?php
error_reporting(0);
ini_set('display_errors', 0);
require 'config.php';
requireLogin();
if (!userCan('step2_pv')) {
    requireRole('admin');
}
require 'db.php';

$errors = [];
$addresses = $pdo->query("SELECT id, libelle FROM addresses ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty(trim($_POST['numero_rapport'] ?? ''))) $errors[] = 'عدد المحضر مطلوب';
    if (empty(trim($_POST['lieu']           ?? ''))) $errors[] = 'المكان مطلوب';

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO batiments
                (numero_rapport,lieu,proprietaire,mise_a_jour,notification,
                 date_rapport,exploite_oui,exploite_non,commission,commission_json,
                 date_envoi_tratiib,date_envoi_wiz,date_envoi_turat,
                 date_envoi_juridique,date_expert,
                 decision_evacuation,decision_demolition,observations,
                 cin,occupe_par,degre_confirmation,numero_bureau_ordre,
                 date_bureau_ordre,notification_region,heure_constat,
                 description_detaillee,mesures_urgentes)
            VALUES(:nr,:lieu,:prop,:maj,:notif,:dr,:eoui,:enon,:com,:comj,
                   :det,:dew,:detr,:dej,:dex,:deva,:ddem,:obs,
                   :cin,:occ,:dc,:nbo,:dbo,:nreg,:heure,:descd,:mes)
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
            ':comj' => trim($_POST['commission_json']     ?? '') ?: null,
            ':det'  => ($_POST['date_envoi_tratiib']      ?? '') ?: null,
            ':dew'  => ($_POST['date_envoi_wiz']          ?? '') ?: null,
            ':detr' => ($_POST['date_envoi_turat']        ?? '') ?: null,
            ':dej'  => ($_POST['date_envoi_juridique']    ?? '') ?: null,
            ':dex'  => ($_POST['date_expert']             ?? '') ?: null,
            ':deva' => trim($_POST['decision_evacuation'] ?? '') ?: null,
            ':ddem' => trim($_POST['decision_demolition'] ?? '') ?: null,
            ':obs'  => trim($_POST['observations']        ?? '') ?: null,
            ':cin'  => trim($_POST['cin']                 ?? '') ?: null,
            ':occ'  => trim($_POST['occupe_par']          ?? '') ?: null,
            ':dc'   => trim($_POST['degre_confirmation']  ?? '') ?: null,
            ':nbo'  => trim($_POST['numero_bureau_ordre'] ?? '') ?: null,
            ':dbo'  => ($_POST['date_bureau_ordre']       ?? '') ?: null,
            ':nreg' => trim($_POST['notification_region']  ?? '') ?: null,
            ':heure'=> trim($_POST['heure_constat']        ?? '') ?: null,
            ':descd'=> trim($_POST['description_detaillee']?? '') ?: null,
            ':mes'  => trim($_POST['mesures_urgentes']     ?? '') ?: null,
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
            <div class="fg">
                <label>رقم مكتب الضبط</label>
                <input type="text" name="numero_bureau_ordre"
                       value="<?= htmlspecialchars($_POST['numero_bureau_ordre'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>تاريخ مكتب الضبط</label>
                <input type="date" name="date_bureau_ordre"
                       value="<?= $_POST['date_bureau_ordre'] ?? '' ?>">
            </div>
            <div class="fg full">
                <label><span class="req">*</span> المكان</label>
                <select name="lieu">
                    <option value="">— اختر العنوان —</option>
                    <?php foreach ($addresses as $a): ?>
                        <option value="<?= htmlspecialchars($a['libelle']) ?>"
                            <?= (($_POST['lieu'] ?? '') === $a['libelle']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label>المالك / المشغول</label>
                <input type="text" name="proprietaire"
                       value="<?= htmlspecialchars($_POST['proprietaire'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>رقم بطاقة التعريف (اختياري)</label>
                <input type="text" name="cin"
                       value="<?= htmlspecialchars($_POST['cin'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>المشغول من طرف</label>
                <input type="text" name="occupe_par"
                       value="<?= htmlspecialchars($_POST['occupe_par'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>درجة التأكيد</label>
                <input type="text" name="degre_confirmation"
                       value="<?= htmlspecialchars($_POST['degre_confirmation'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>إشعار عمدة المنطقة</label>
                <input type="text" name="notification_region"
                       value="<?= htmlspecialchars($_POST['notification_region'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>الساعة</label>
                <input type="text" name="heure_constat" placeholder="مثال: 11:55"
                       value="<?= htmlspecialchars($_POST['heure_constat'] ?? '') ?>">
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
                <?php
                    $commissionValue = $_POST['commission'] ?? '';
                    $commissionJsonValue = $_POST['commission_json'] ?? '';
                    include '_commission.php';
                ?>
            </div>

            <div class="sec">🟨 حقول محضر المعاينة (مطابقة pv.docx)</div>
            <div class="fg full">
                <label>وصف العقار</label>
                <textarea name="description_detaillee"><?= htmlspecialchars($_POST['description_detaillee'] ?? '') ?></textarea>
            </div>
            <div class="fg full">
                <label>الإجراءات الوقائية والإستعجالية</label>
                <textarea name="mesures_urgentes"><?= htmlspecialchars($_POST['mesures_urgentes'] ?? '') ?></textarea>
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
