<?php
error_reporting(0);
ini_set('display_errors', 0);
require 'config.php';
requireLogin();
if (!userCan('step2_pv')) requireRole('admin');
require 'db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM batiments WHERE id=?");
$stmt->execute([$id]);
$b = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$b) { header('Location: index.php'); exit; }

$errors = [];
$addresses = $pdo->query("SELECT id, libelle FROM addresses ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty(trim($_POST['numero_rapport'] ?? ''))) $errors[] = 'عدد المحضر مطلوب';
    if (empty(trim($_POST['lieu'] ?? ''))) $errors[] = 'المكان مطلوب';
    if (empty($errors)) {
        $up = $pdo->prepare("
            UPDATE batiments SET
                numero_rapport=:nr, lieu=:lieu, proprietaire=:prop, cin=:cin,
                occupe_par=:occ, degre_confirmation=:dc, date_rapport=:dr,
                numero_bureau_ordre=:nbo, date_bureau_ordre=:dbo,
                notification_region=:nreg, heure_constat=:heure,
                commission=:com, commission_json=:comj,
                description_detaillee=:descd, mesures_urgentes=:mes,
                mise_a_jour=:maj, notification=:notif, exploite_oui=:eoui, exploite_non=:enon,
                date_envoi_tratiib=:det, date_envoi_wiz=:dew, date_envoi_turat=:detr,
                date_envoi_juridique=:dej, date_expert=:dex,
                decision_evacuation=:deva, decision_demolition=:ddem, observations=:obs
            WHERE id=:id
        ");
        $up->execute([
            ':nr' => trim($_POST['numero_rapport']),
            ':lieu' => trim($_POST['lieu']),
            ':prop' => trim($_POST['proprietaire'] ?? '') ?: null,
            ':cin' => trim($_POST['cin'] ?? '') ?: null,
            ':occ' => trim($_POST['occupe_par'] ?? '') ?: null,
            ':dc' => trim($_POST['degre_confirmation'] ?? '') ?: null,
            ':dr' => ($_POST['date_rapport'] ?? '') ?: null,
            ':nbo' => trim($_POST['numero_bureau_ordre'] ?? '') ?: null,
            ':dbo' => ($_POST['date_bureau_ordre'] ?? '') ?: null,
            ':nreg' => trim($_POST['notification_region'] ?? '') ?: null,
            ':heure' => trim($_POST['heure_constat'] ?? '') ?: null,
            ':com' => trim($_POST['commission'] ?? '') ?: null,
            ':comj' => trim($_POST['commission_json'] ?? '') ?: null,
            ':descd' => trim($_POST['description_detaillee'] ?? '') ?: null,
            ':mes' => trim($_POST['mesures_urgentes'] ?? '') ?: null,
            ':maj' => trim($_POST['mise_a_jour'] ?? '') ?: null,
            ':notif' => trim($_POST['notification'] ?? '') ?: null,
            ':eoui' => ($_POST['exploite'] ?? '') === 'oui' ? 1 : 0,
            ':enon' => ($_POST['exploite'] ?? '') === 'non' ? 1 : 0,
            ':det'  => ($_POST['date_envoi_tratiib'] ?? '') ?: null,
            ':dew'  => ($_POST['date_envoi_wiz'] ?? '') ?: null,
            ':detr' => ($_POST['date_envoi_turat'] ?? '') ?: null,
            ':dej'  => ($_POST['date_envoi_juridique'] ?? '') ?: null,
            ':dex'  => ($_POST['date_expert'] ?? '') ?: null,
            ':deva' => trim($_POST['decision_evacuation'] ?? '') ?: null,
            ':ddem' => trim($_POST['decision_demolition'] ?? '') ?: null,
            ':obs'  => trim($_POST['observations'] ?? '') ?: null,
            ':id' => $id,
        ]);
        header("Location: index.php?msg=updated");
        exit;
    }
    $b = array_merge($b, $_POST);
}

$exploiteVal = !empty($b['exploite_oui']) ? 'oui' : (!empty($b['exploite_non']) ? 'non' : '');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل محضر</title>
    <?php include '_styles_form.php'; ?>
</head>
<body>
<header style="background:linear-gradient(135deg,#6f42c1,#2e6da4)">
    <h1>✏️ تعديل محضر</h1>
</header>
<div class="wrap">
    <h2>📋 تعديل بيانات المحضر</h2>
    <?php if (!empty($errors)): ?>
        <div class="error-box">⚠️ يرجى تصحيح الأخطاء:
            <ul><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul>
        </div>
    <?php endif; ?>
    <form method="POST">
        <div class="grid">
            <div class="sec">📄 المعلومات الأساسية</div>
            <div class="fg"><label><span class="req">*</span> عدد المحضر</label><input type="text" name="numero_rapport" value="<?= htmlspecialchars($b['numero_rapport'] ?? '') ?>"></div>
            <div class="fg"><label>تاريخ محضر المعاينة</label><input type="date" name="date_rapport" value="<?= htmlspecialchars($b['date_rapport'] ?? '') ?>"></div>
            <div class="fg"><label>رقم مكتب الضبط</label><input type="text" name="numero_bureau_ordre" value="<?= htmlspecialchars($b['numero_bureau_ordre'] ?? '') ?>"></div>
            <div class="fg"><label>تاريخ مكتب الضبط</label><input type="date" name="date_bureau_ordre" value="<?= htmlspecialchars($b['date_bureau_ordre'] ?? '') ?>"></div>
            <div class="fg full">
                <label><span class="req">*</span> المكان</label>
                <select name="lieu">
                    <option value="">— اختر العنوان —</option>
                    <?php foreach ($addresses as $a): ?>
                        <option value="<?= htmlspecialchars($a['libelle']) ?>" <?= (($b['lieu'] ?? '') === $a['libelle']) ? 'selected' : '' ?>><?= htmlspecialchars($a['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg"><label>المالك / المشغول</label><input type="text" name="proprietaire" value="<?= htmlspecialchars($b['proprietaire'] ?? '') ?>"></div>
            <div class="fg"><label>رقم بطاقة التعريف (اختياري)</label><input type="text" name="cin" value="<?= htmlspecialchars($b['cin'] ?? '') ?>"></div>
            <div class="fg"><label>المشغول من طرف</label><input type="text" name="occupe_par" value="<?= htmlspecialchars($b['occupe_par'] ?? '') ?>"></div>
            <div class="fg"><label>درجة التأكيد</label><input type="text" name="degre_confirmation" value="<?= htmlspecialchars($b['degre_confirmation'] ?? '') ?>"></div>
            <div class="fg"><label>إشعار عمدة المنطقة</label><input type="text" name="notification_region" value="<?= htmlspecialchars($b['notification_region'] ?? '') ?>"></div>
            <div class="fg"><label>الساعة</label><input type="text" name="heure_constat" value="<?= htmlspecialchars($b['heure_constat'] ?? '') ?>"></div>
            <div class="fg"><label>تحيين</label><input type="text" name="mise_a_jour" value="<?= htmlspecialchars($b['mise_a_jour'] ?? '') ?>"></div>
            <div class="fg"><label>الإشعار</label><input type="text" name="notification" value="<?= htmlspecialchars($b['notification'] ?? '') ?>"></div>

            <div class="fg">
                <label>مستغلة من طرف أشخاص</label>
                <div class="toggle-group">
                    <div class="toggle-option opt-oui"><input type="radio" name="exploite" value="oui" id="exp_oui_m" <?= $exploiteVal === 'oui' ? 'checked' : '' ?>><label for="exp_oui_m">✅ نعم</label></div>
                    <div class="toggle-sep"></div>
                    <div class="toggle-option opt-non"><input type="radio" name="exploite" value="non" id="exp_non_m" <?= $exploiteVal === 'non' ? 'checked' : '' ?>><label for="exp_non_m">❌ لا</label></div>
                </div>
            </div>

            <div class="sec">👥 اللجنة</div>
            <div class="fg full">
                <label>أعضاء اللجنة</label>
                <?php
                    $commissionValue = $b['commission'] ?? '';
                    $commissionJsonValue = $b['commission_json'] ?? '';
                    include '_commission.php';
                ?>
            </div>

            <div class="sec">🟨 حقول محضر المعاينة (مطابقة pv.docx)</div>
            <div class="fg full"><label>وصف العقار</label><textarea name="description_detaillee"><?= htmlspecialchars($b['description_detaillee'] ?? '') ?></textarea></div>
            <div class="fg full"><label>الإجراءات الوقائية والإستعجالية</label><textarea name="mesures_urgentes"><?= htmlspecialchars($b['mesures_urgentes'] ?? '') ?></textarea></div>
            <div class="sec">📅 تواريخ توجيه جداول الأوراق</div>
            <div class="fg"><label>توجيه التراتيب</label><input type="date" name="date_envoi_tratiib" value="<?= htmlspecialchars($b['date_envoi_tratiib'] ?? '') ?>"></div>
            <div class="fg"><label>توجيه وزارة التجهيز</label><input type="date" name="date_envoi_wiz" value="<?= htmlspecialchars($b['date_envoi_wiz'] ?? '') ?>"></div>
            <div class="fg"><label>توجيه المعهد الوطني للتراث</label><input type="date" name="date_envoi_turat" value="<?= htmlspecialchars($b['date_envoi_turat'] ?? '') ?>"></div>
            <div class="fg"><label>توجيه إدارة الشؤون القانونية</label><input type="date" name="date_envoi_juridique" value="<?= htmlspecialchars($b['date_envoi_juridique'] ?? '') ?>"></div>
            <div class="sec">⚖️ القرارات والنتائج</div>
            <div class="fg"><label>تاريخ محضر الخبير</label><input type="date" name="date_expert" value="<?= htmlspecialchars($b['date_expert'] ?? '') ?>"></div>
            <div class="fg"><label>قرار إخلاء</label><input type="text" name="decision_evacuation" value="<?= htmlspecialchars($b['decision_evacuation'] ?? '') ?>"></div>
            <div class="fg"><label>قرار هدم</label><input type="text" name="decision_demolition" value="<?= htmlspecialchars($b['decision_demolition'] ?? '') ?>"></div>
            <div class="fg full"><label>ملاحظات</label><textarea name="observations"><?= htmlspecialchars($b['observations'] ?? '') ?></textarea></div>
        </div>
        <div class="btn-row">
            <button type="submit" class="btn btn-success">💾 حفظ التعديلات</button>
            <a href="pv_print.php?id=<?= $id ?>" target="_blank" class="btn btn-primary">🖨️ طباعة PV</a>
            <a href="index.php" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>
</body>
</html>
