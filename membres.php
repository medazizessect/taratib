<?php
error_reporting(0);
ini_set('display_errors', 0);
require 'config.php';
requireLogin();
require 'db.php';
if (!userCan('can_manage_members')) {
    header('Location: index.php');
    exit;
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter') {
        $nom = trim($_POST['nom'] ?? '');
        $grade = trim($_POST['grade'] ?? '');
        if ($nom !== '') {
            $max = $pdo->query("SELECT COALESCE(MAX(ordre),0) FROM membres")->fetchColumn();
            $pdo->prepare("INSERT INTO membres (nom, grade, ordre) VALUES (?, ?, ?)")
                ->execute([$nom, ($grade !== '' ? $grade : null), intval($max) + 1]);
            $msg = 'added';
        }
    }
    elseif ($action === 'supprimer') {
        $id_m = intval($_POST['id'] ?? 0);
        if ($id_m) {
            $pdo->prepare("DELETE FROM membres WHERE id = ?")->execute([$id_m]);
            $msg = 'deleted';
        }
    }
    elseif ($action === 'toggle') {
        $id_m = intval($_POST['id'] ?? 0);
        if ($id_m) {
            $pdo->prepare("UPDATE membres SET actif = 1 - actif WHERE id = ?")
                ->execute([$id_m]);
            $msg = 'updated';
        }
    }
    elseif ($action === 'modifier') {
        // ✅ Bug corrigé : récupérer correctement nom et id
        $id_m = intval($_POST['id']  ?? 0);
        $nom  = trim($_POST['nom_edit'] ?? '');
        $grade = trim($_POST['grade_edit'] ?? '');
        if ($id_m && $nom !== '') {
            $pdo->prepare("UPDATE membres SET nom = ?, grade = ? WHERE id = ?")
                ->execute([$nom, ($grade !== '' ? $grade : null), $id_m]);
            $msg = 'updated';
        }
    }
}

$membres  = $pdo->query("SELECT * FROM membres ORDER BY ordre ASC")->fetchAll(PDO::FETCH_ASSOC);
$actifs   = array_filter($membres, fn($m) =>  $m['actif']);
$inactifs = array_filter($membres, fn($m) => !$m['actif']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة أعضاء اللجنة</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f2f5;direction:rtl}
        header{background:linear-gradient(135deg,#6f42c1,#9b59b6);
               color:white;padding:20px 30px;text-align:center}
        header h1{font-size:22px}
        header p{font-size:13px;margin-top:4px;opacity:.85}
        .wrap{max-width:720px;margin:28px auto;padding:0 15px}
        .back{display:inline-flex;align-items:center;gap:6px;
              margin-bottom:16px;color:#6f42c1;text-decoration:none;
              font-size:14px;font-weight:600}
        .back:hover{text-decoration:underline}
        .card{background:white;border-radius:12px;padding:22px;
              box-shadow:0 3px 12px rgba(0,0,0,.09);margin-bottom:18px}
        .card-title{font-size:16px;font-weight:bold;color:#1a3c5e;
                    margin-bottom:14px;padding-bottom:10px;
                    border-bottom:2px solid #e8f0fb;
                    display:flex;align-items:center;gap:8px}
        .add-row{display:flex;gap:10px}
        .add-row input{flex:1;padding:10px 14px;border:1px solid #ccc;
                       border-radius:8px;font-size:14px;font-family:inherit;
                       transition:border .2s}
        .add-row input:focus{outline:none;border-color:#6f42c1;
                              box-shadow:0 0 0 3px rgba(111,66,193,.15)}
        .membre-list{display:flex;flex-direction:column;gap:8px}
        .membre-item{display:flex;align-items:center;gap:8px;
                     border:1px solid #e9ecef;border-radius:10px;
                     padding:10px 14px;background:#fafafa;
                     transition:background .2s}
        .membre-item:hover{background:#f0e8ff}
        .membre-item.inactif{background:#fff8f8;border-color:#f5c6cb;opacity:.7}
        .num{width:28px;height:28px;border-radius:50%;
             background:#6f42c1;color:white;display:flex;
             align-items:center;justify-content:center;
             font-size:12px;font-weight:bold;flex-shrink:0}
        .inactif .num{background:#aaa}
        .nom-display{flex:1;font-size:14px;color:#333}

        /* ✅ Input d'édition inline */
        .nom-edit-input{
            flex:1;padding:7px 11px;border:2px solid #6f42c1;
            border-radius:7px;font-size:14px;font-family:inherit;
            display:none;transition:box-shadow .2s;
        }
        .nom-edit-input:focus{
            outline:none;
            box-shadow:0 0 0 3px rgba(111,66,193,.2);
        }

        .status-badge{font-size:11px;padding:3px 9px;border-radius:12px;
                      font-weight:bold;flex-shrink:0}
        .s-actif  {background:#d4edda;color:#155724}
        .s-inactif{background:#f8d7da;color:#721c24}
        .btn{padding:6px 11px;border:none;border-radius:7px;cursor:pointer;
             font-size:12px;font-family:inherit;transition:opacity .2s;
             flex-shrink:0;display:inline-flex;align-items:center;gap:3px;
             font-weight:600}
        .btn:hover{opacity:.82}
        .btn-add   {background:#28a745;color:white;padding:10px 18px;
                    font-size:14px;border-radius:8px}
        .btn-edit  {background:#ffc107;color:#333}
        .btn-save  {background:#28a745;color:white;display:none}
        .btn-cancel{background:#6c757d;color:white;display:none}
        .btn-toggle{background:#17a2b8;color:white}
        .btn-del   {background:#dc3545;color:white}
        .alert{padding:11px 16px;border-radius:7px;margin-bottom:14px;font-size:14px}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
        .alert-danger {background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
        .hint{font-size:12px;color:#888;margin-top:8px}
        .empty{text-align:center;color:#bbb;padding:16px;font-size:14px}
        .section-label{font-size:11px;font-weight:bold;color:#999;
                        letter-spacing:.5px;margin:14px 0 8px}
        .count-badge{background:#6f42c1;color:white;border-radius:12px;
                     padding:2px 10px;font-size:12px;margin-right:6px}
    </style>
</head>
<body>
<header>
    <h1>👥 إدارة أعضاء اللجنة</h1>
    <p><?= count($actifs) ?> عضو نشط · <?= count($inactifs) ?> معطل</p>
</header>

<div class="wrap">
    <a href="index.php" class="back">↩️ رجوع للقائمة الرئيسية</a>

    <?php if ($msg === 'added'): ?>
        <div class="alert alert-success">✅ تمت إضافة العضو بنجاح!</div>
    <?php elseif ($msg === 'deleted'): ?>
        <div class="alert alert-danger">🗑️ تم حذف العضو!</div>
    <?php elseif ($msg === 'updated'): ?>
        <div class="alert alert-success">✅ تم التحديث بنجاح!</div>
    <?php endif; ?>

    <!-- Ajout -->
    <div class="card">
        <div class="card-title">➕ إضافة عضو جديد</div>
        <form method="POST" class="add-row" style="flex-wrap:wrap">
            <input type="hidden" name="action" value="ajouter">
            <input type="text" name="nom"
                    placeholder="اكتب اسم العضو الجديد..."
                    autocomplete="off" required>
            <input type="text" name="grade"
                   placeholder="الرتبة / الصفة (اختياري)"
                   autocomplete="off">
            <button type="submit" class="btn btn-add">➕ إضافة</button>
        </form>
        <p class="hint">💡 الأعضاء النشطون يظهرون تلقائياً في نماذج إضافة وتعديل المحاضر</p>
    </div>

    <!-- Liste -->
    <div class="card">
        <div class="card-title">
            📋 قائمة الأعضاء
            <span class="count-badge"><?= count($membres) ?></span>
        </div>

        <?php if (empty($membres)): ?>
            <div class="empty">لا يوجد أعضاء بعد</div>
        <?php else: ?>

            <?php if (!empty($actifs)): ?>
            <div class="section-label">✅ النشطون (<?= count($actifs) ?>)</div>
            <div class="membre-list">
                <?php foreach ($actifs as $i => $m): ?>
                <div class="membre-item" id="item-<?= $m['id'] ?>">
                    <div class="num"><?= $m['ordre'] ?></div>

                    <!-- Nom affiché -->
                    <span class="nom-display"
                          id="disp-<?= $m['id'] ?>">
                        <?= htmlspecialchars($m['nom']) ?>
                        <?php if (!empty($m['grade'])): ?>
                            — <?= htmlspecialchars($m['grade']) ?>
                        <?php endif; ?>
                    </span>

                    <!-- ✅ Input édition avec nom distinct -->
                    <input type="text"
                           class="nom-edit-input"
                           id="editinput-<?= $m['id'] ?>"
                           value="<?= htmlspecialchars($m['nom']) ?>"
                           placeholder="الاسم الجديد...">
                    <input type="text"
                           class="nom-edit-input"
                           id="editgrade-<?= $m['id'] ?>"
                           value="<?= htmlspecialchars($m['grade'] ?? '') ?>"
                           placeholder="الرتبة / الصفة...">

                    <span class="status-badge s-actif">نشط</span>

                    <!-- Btn Éditer -->
                    <button class="btn btn-edit"
                            id="bedit-<?= $m['id'] ?>"
                            type="button"
                            onclick="startEdit(<?= $m['id'] ?>)">✏️ تعديل</button>

                    <!-- ✅ Form Enregistrer corrigé -->
                    <form method="POST" id="fsave-<?= $m['id'] ?>"
                          style="display:contents">
                        <input type="hidden" name="action"   value="modifier">
                        <input type="hidden" name="id"       id="fid-<?= $m['id'] ?>"   value="<?= $m['id'] ?>">
                        <input type="hidden" name="nom_edit" id="fnomhid-<?= $m['id'] ?>" value="">
                        <input type="hidden" name="grade_edit" id="fgradehid-<?= $m['id'] ?>" value="">
                        <button type="submit"
                                class="btn btn-save"
                                id="bsave-<?= $m['id'] ?>"
                                onclick="prepareSave(<?= $m['id'] ?>)">
                            💾 حفظ
                        </button>
                    </form>

                    <!-- Btn Annuler -->
                    <button class="btn btn-cancel"
                            id="bcancel-<?= $m['id'] ?>"
                            type="button"
                            onclick="cancelEdit(<?= $m['id'] ?>, '<?= addslashes(htmlspecialchars($m['nom'])) ?>', '<?= addslashes(htmlspecialchars($m['grade'] ?? '')) ?>')">
                        ✖
                    </button>

                    <!-- Toggle -->
                    <form method="POST" style="display:contents">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="id"     value="<?= $m['id'] ?>">
                        <button type="submit" class="btn btn-toggle"
                                title="تعطيل">🔕</button>
                    </form>

                    <!-- Supprimer -->
                    <form method="POST" style="display:contents"
                          onsubmit="return confirm('حذف هذا العضو نهائياً؟')">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id"     value="<?= $m['id'] ?>">
                        <button type="submit" class="btn btn-del">🗑️</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($inactifs)): ?>
            <div class="section-label" style="margin-top:16px">
                🔕 المعطلون (<?= count($inactifs) ?>)
            </div>
            <div class="membre-list">
                <?php foreach ($inactifs as $m): ?>
                <div class="membre-item inactif">
                    <div class="num"><?= $m['ordre'] ?></div>
                    <span class="nom-display"><?= htmlspecialchars($m['nom']) ?></span>
                    <?php if (!empty($m['grade'])): ?>
                    <span class="status-badge s-actif"><?= htmlspecialchars($m['grade']) ?></span>
                    <?php endif; ?>
                    <span class="status-badge s-inactif">معطل</span>
                    <!-- Toggle réactiver -->
                    <form method="POST" style="display:contents">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="id"     value="<?= $m['id'] ?>">
                        <button type="submit" class="btn btn-toggle"
                                title="تفعيل">🔔 تفعيل</button>
                    </form>
                    <!-- Supprimer -->
                    <form method="POST" style="display:contents"
                          onsubmit="return confirm('حذف هذا العضو نهائياً؟')">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id"     value="<?= $m['id'] ?>">
                        <button type="submit" class="btn btn-del">🗑️</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<script>
// ✅ Démarrer l'édition
function startEdit(id) {
    document.getElementById('disp-'    + id).style.display = 'none';
    document.getElementById('editinput-' + id).style.display = 'block';
    document.getElementById('editgrade-' + id).style.display = 'block';
    document.getElementById('bedit-'   + id).style.display = 'none';
    document.getElementById('bsave-'   + id).style.display = 'inline-flex';
    document.getElementById('bcancel-' + id).style.display = 'inline-flex';
    document.getElementById('editinput-' + id).focus();
    document.getElementById('editinput-' + id).select();
}

// ✅ Préparer la sauvegarde : copier valeur dans le hidden
function prepareSave(id) {
    var val = document.getElementById('editinput-' + id).value.trim();
    var grade = document.getElementById('editgrade-' + id).value.trim();
    if (!val) {
        alert('الاسم لا يمكن أن يكون فارغاً');
        return false;
    }
    document.getElementById('fnomhid-' + id).value = val;
    document.getElementById('fgradehid-' + id).value = grade;
    return true;
}

// ✅ Annuler l'édition
function cancelEdit(id, original, originalGrade) {
    document.getElementById('disp-'      + id).style.display = '';
    document.getElementById('editinput-' + id).style.display = 'none';
    document.getElementById('editgrade-' + id).style.display = 'none';
    document.getElementById('editinput-' + id).value = original;
    document.getElementById('editgrade-' + id).value = originalGrade || '';
    document.getElementById('bedit-'     + id).style.display = 'inline-flex';
    document.getElementById('bsave-'     + id).style.display = 'none';
    document.getElementById('bcancel-'   + id).style.display = 'none';
}

// Soumettre avec Enter dans l'input d'édition
document.querySelectorAll('.nom-edit-input').forEach(function(inp) {
    inp.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var id = this.id.replace('editinput-', '');
            if (prepareSave(id)) {
                document.getElementById('fsave-' + id).submit();
            }
        }
        if (e.key === 'Escape') {
            var id = this.id.replace('editinput-', '');
            var gradeEl = document.getElementById('editgrade-' + id);
            cancelEdit(id, this.defaultValue, gradeEl ? gradeEl.defaultValue : '');
        }
    });
});
</script>
</body>
</html>
