<?php
require 'config.php';
requireLogin();
require 'db.php';
require '_steps_config.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM batiments
        WHERE numero_rapport LIKE :s OR lieu LIKE :s
           OR proprietaire   LIKE :s OR observations LIKE :s
        ORDER BY id DESC
    ");
    $stmt->execute([':s' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM batiments ORDER BY id DESC");
}
$batiments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total     = $pdo->query("SELECT COUNT(*) FROM batiments")->fetchColumn();
$exploites = $pdo->query("SELECT COUNT(*) FROM batiments WHERE exploite_oui=1")->fetchColumn();
$avecHdm   = $pdo->query("SELECT COUNT(*) FROM batiments WHERE decision_demolition IS NOT NULL AND decision_demolition != ''")->fetchColumn();
$avecExp   = $pdo->query("SELECT COUNT(*) FROM batiments WHERE date_expert IS NOT NULL")->fetchColumn();

// ── Charger tous les documents ──
$allDocs = [];
if (!empty($batiments)) {
    $ids = array_column($batiments, 'id');
    $ph  = implode(',', array_fill(0, count($ids), '?'));
    $ds  = $pdo->prepare("
        SELECT batiment_id, type, statut
        FROM documents_officiels
        WHERE batiment_id IN ($ph)
    ");
    $ds->execute($ids);
    foreach ($ds->fetchAll(PDO::FETCH_ASSOC) as $d) {
        $allDocs[$d['batiment_id']][$d['type']] = $d['statut'];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جدول بياني للبنايات المتداعية للسقوط</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{
            font-family:'Segoe UI',Tahoma,Arial,sans-serif;
            background:#f0f2f5;color:#333;direction:rtl;min-height:100vh;
        }

        header{
            background:linear-gradient(135deg,#1a3c5e,#2e6da4);
            color:white;padding:18px 25px;
            display:flex;align-items:center;justify-content:space-between;
            box-shadow:0 3px 10px rgba(0,0,0,.25);
            position:sticky;top:0;z-index:100;
        }
        .header-title h1{font-size:20px;font-weight:700}
        .header-title p{font-size:12px;margin-top:3px;opacity:.8}
        .user-badge{
            background:rgba(255,255,255,.15);padding:6px 14px;
            border-radius:20px;display:flex;align-items:center;
            gap:6px;font-size:13px;
        }

        .container{padding:20px 22px}

        /* Stats */
        .stats{display:flex;gap:14px;margin-bottom:20px;flex-wrap:wrap}
        .stat-card{
            background:white;border-radius:12px;padding:16px 20px;
            text-align:center;box-shadow:0 2px 10px rgba(0,0,0,.07);
            flex:1;min-width:130px;border-top:4px solid transparent;
            transition:transform .2s;
        }
        .stat-card:hover{transform:translateY(-2px)}
        .stat-card.blue  {border-top-color:#2e6da4}
        .stat-card.green {border-top-color:#28a745}
        .stat-card.red   {border-top-color:#dc3545}
        .stat-card.orange{border-top-color:#e67e22}
        .stat-card .number{font-size:34px;font-weight:bold}
        .stat-card.blue   .number{color:#2e6da4}
        .stat-card.green  .number{color:#28a745}
        .stat-card.red    .number{color:#dc3545}
        .stat-card.orange .number{color:#e67e22}
        .stat-card .label{font-size:12px;color:#888;margin-top:4px}

        /* Toolbar */
        .toolbar{
            display:flex;justify-content:space-between;align-items:center;
            margin-bottom:16px;flex-wrap:wrap;gap:10px;
            background:white;padding:14px 16px;
            border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.06);
        }
        .toolbar-left {display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .toolbar-right{display:flex;gap:8px;flex-wrap:wrap;align-items:center}

        .search-box{display:flex;border-radius:8px;overflow:hidden;
                    box-shadow:0 1px 4px rgba(0,0,0,.1)}
        .search-box input{
            padding:9px 14px;border:1px solid #ddd;border-left:none;
            font-size:14px;font-family:inherit;width:250px;
            border-radius:0 8px 8px 0;
        }
        .search-box input:focus{outline:none;border-color:#2e6da4}
        .search-box button{
            padding:9px 14px;background:#2e6da4;color:white;
            border:none;cursor:pointer;font-size:14px;
            border-radius:8px 0 0 8px;
        }
        .search-box button:hover{background:#1a3c5e}

        .btn{
            padding:8px 15px;border:none;border-radius:7px;cursor:pointer;
            font-size:13px;font-family:inherit;text-decoration:none;
            display:inline-flex;align-items:center;gap:5px;font-weight:600;
            transition:opacity .2s,transform .15s;
        }
        .btn:hover {opacity:.88;transform:translateY(-1px)}
        .btn:active{transform:translateY(0)}
        .btn-success{background:#28a745;color:white}
        .btn-excel  {background:#1D6F42;color:white}
        .btn-pdf    {background:#c0392b;color:white}
        .btn-cancel {background:#6c757d;color:white}

        /* Alerts */
        .alert{padding:11px 16px;border-radius:8px;margin-bottom:14px;font-size:14px}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
        .alert-danger {background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}

        /* Table */
        .table-wrap{
            overflow-x:auto;border-radius:12px;
            box-shadow:0 3px 15px rgba(0,0,0,.09);
        }
        table{
            width:100%;border-collapse:collapse;background:white;
            font-size:13px;min-width:1400px;
        }
        thead{
            background:linear-gradient(135deg,#1a3c5e,#2e6da4);
            color:white;position:sticky;top:62px;z-index:10;
        }
        thead th{
            padding:12px 8px;text-align:center;font-size:12px;
            font-weight:600;border:1px solid rgba(255,255,255,.15);
            white-space:nowrap;
        }
        tbody tr{transition:background .15s}
        tbody tr:nth-child(even){background:#f8f9fa}
        tbody tr:hover{background:#e8f0fb}
        tbody td{
            padding:9px 8px;border:1px solid #e9ecef;
            text-align:center;vertical-align:middle;
        }

        .badge{padding:3px 10px;border-radius:20px;font-size:11px;
               font-weight:bold;display:inline-block}
        .badge-yes {background:#d4edda;color:#155724}
        .badge-no  {background:#f8d7da;color:#721c24}
        .badge-num {background:#cce5ff;color:#004085}
        .badge-hdm {background:#f8d7da;color:#721c24}
        .obs-cell{
            max-width:150px;white-space:nowrap;
            overflow:hidden;text-overflow:ellipsis;text-align:right;
        }
        .tr{text-align:right!important}

        /* Action cell */
        .action-wrap{display:flex;flex-direction:column;
                     gap:5px;align-items:center;padding:5px 3px}
        .action-top{display:flex;gap:4px;justify-content:center}
        .ab{
            width:30px;height:30px;border-radius:7px;border:none;
            cursor:pointer;font-size:14px;display:inline-flex;
            align-items:center;justify-content:center;
            transition:opacity .2s,transform .15s;text-decoration:none;
        }
        .ab:hover{opacity:.85;transform:scale(1.1)}
        .ab-edit{background:#ffc107;color:#333}
        .ab-del {background:#dc3545;color:white}

        /* ══ STEPPER ══ */
        .stepper{display:flex;align-items:center;gap:1px;flex-wrap:wrap;
                 justify-content:center}
        .step-arrow{color:#ddd;font-size:11px;flex-shrink:0;padding:0 1px}

        .step-btn{
            display:inline-flex;align-items:center;gap:2px;
            padding:4px 7px;border-radius:6px;font-size:11px;
            font-weight:700;text-decoration:none;white-space:nowrap;
            position:relative;transition:all .2s;
            border:2px solid transparent;cursor:pointer;
        }
        .step-btn:hover:not(.step-locked){
            opacity:.85;transform:translateY(-1px);
            box-shadow:0 2px 6px rgba(0,0,0,.2);
        }

        /* Couleurs par étape */
        .step-done.s1{background:#17a2b8;color:white}
        .step-done.s2{background:#6f42c1;color:white}
        .step-done.s3{background:#2e6da4;color:white}
        .step-done.s4{background:#c0392b;color:white}
        .step-done.s5{background:#e67e22;color:white}

        /* ✓ Finalise */
        .step-final::after{
            content:'✓';position:absolute;top:-6px;left:-5px;
            background:#28a745;color:white;border-radius:50%;
            width:13px;height:13px;font-size:8px;font-weight:bold;
            display:flex;align-items:center;justify-content:center;
            line-height:13px;text-align:center;
        }
        /* ✎ Brouillon */
        .step-draft{border-color:#ffc107 !important}
        .step-draft::after{
            content:'✎';position:absolute;top:-6px;left:-5px;
            background:#ffc107;color:#333;border-radius:50%;
            width:13px;height:13px;font-size:8px;
            display:flex;align-items:center;justify-content:center;
            line-height:13px;text-align:center;
        }

        /* En attente */
        .step-todo{background:#f0f0f0;color:#aaa;border-color:#e0e0e0}
        .step-todo:hover{background:#e8f0fb;color:#2e6da4;border-color:#2e6da4}

        /* Bloqué */
        .step-locked{
            background:#f5f5f5;color:#ccc;border-color:#eee;
            cursor:not-allowed;pointer-events:none;
        }

        /* Facultatif التراث */
        .step-optional{
            background:#e8f8fb;color:#17a2b8;
            border:2px dashed #17a2b8;
        }
        .step-optional:hover{background:#d1ecf1}

        footer{text-align:center;padding:14px;font-size:12px;
               color:#aaa;margin-top:18px}

        @media(max-width:768px){
            .toolbar{flex-direction:column}
            .search-box input{width:180px}
        }
    </style>
</head>
<body>

<?php include '_menu.php'; ?>

<header>
    <div class="header-title">
        <h1>🏚️ جدول بياني للبنايات المتداعية للسقوط</h1>
        <p>إدارة الشؤون التقنية — بلدية سوسة</p>
    </div>
    <div class="user-badge">
        👤 <?= htmlspecialchars($_SESSION['user']['nom']) ?>
        <span style="opacity:.6;font-size:11px">
            (<?= $_SESSION['user']['role']==='admin'
                ? 'مدير'
                : ($_SESSION['user']['role']==='agent' ? 'عون' : 'قارئ') ?>)
        </span>
    </div>
</header>

<div class="container">

    <?php
    $msgs = [
        'added'   => ['success','✅ تمت إضافة المحضر بنجاح!'],
        'updated' => ['success','✅ تم تحديث المحضر بنجاح!'],
        'deleted' => ['danger', '🗑️ تم حذف المحضر بنجاح!'],
    ];
    if (isset($_GET['msg'], $msgs[$_GET['msg']])):
        $m = $msgs[$_GET['msg']];
    ?>
        <div class="alert alert-<?= $m[0] ?>"><?= $m[1] ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-card blue">
            <div class="number"><?= $total ?></div>
            <div class="label">إجمالي المحاضر</div>
        </div>
        <div class="stat-card green">
            <div class="number"><?= $exploites ?></div>
            <div class="label">مستغلة</div>
        </div>
        <div class="stat-card red">
            <div class="number"><?= $avecHdm ?></div>
            <div class="label">قرار هدم</div>
        </div>
        <div class="stat-card orange">
            <div class="number"><?= $avecExp ?></div>
            <div class="label">محضر خبير</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-left">
            <?php if (userCan('can_add')): ?>
                <a href="ajouter.php" class="btn btn-success">➕ إضافة محضر</a>
            <?php endif; ?>
            <?php if (userCan('can_export')): ?>
                <a href="export_excel.php<?= $search!=='' ? '?search='.urlencode($search) : '' ?>"
                   class="btn btn-excel">📊 Excel</a>
                <a href="export_pdf.php<?= $search!=='' ? '?search='.urlencode($search) : '' ?>"
                   class="btn btn-pdf" target="_blank">📄 PDF</a>
            <?php endif; ?>
        </div>
        <div class="toolbar-right">
            <form method="GET" action="index.php">
                <div class="search-box">
                    <button type="submit">🔍</button>
                    <input type="text" name="search"
                           placeholder="بحث عن مكان، مالك، ملاحظات..."
                           value="<?= htmlspecialchars($search) ?>">
                </div>
            </form>
            <?php if ($search !== ''): ?>
                <a href="index.php" class="btn btn-cancel">✖</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>عدد المحضر</th>
                    <th>المكان</th>
                    <th>المالك / المشغول</th>
                    <th>تاريخ المعاينة</th>
                    <th>مستغلة</th>
                    <th>اللجنة</th>
                    <th>توجيه التراتيب</th>
                    <th>توجيه الوزارة</th>
                    <th>توجيه التراث</th>
                    <th>توجيه القانونية</th>
                    <th>تاريخ الخبير</th>
                    <th>قرار إخلاء</th>
                    <th>قرار هدم</th>
                    <th>ملاحظات</th>
                    <th>الإجراءات والمراحل</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($batiments)): ?>
                <tr>
                    <td colspan="16"
                        style="padding:40px;color:#999;font-size:15px;text-align:center">
                        <?= $search !== ''
                            ? "🔍 لا توجد نتائج لـ: <strong>".htmlspecialchars($search)."</strong>"
                            : '📭 لا توجد بيانات — <a href="ajouter.php" style="color:#2e6da4">أضف محضراً</a>' ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($batiments as $i => $row):

                    // ✅ FIX : utiliser $row et non $b pour éviter le conflit
                    $docs = $allDocs[$row['id']] ?? [];

                    // Calculer les classes pour chaque étape
                    $stepClasses   = [];
                    $stepTooltips  = [];
                    $stepLocked    = [];

                    foreach (STEPS as $type => $cfg) {
                        $stepClasses[$type]  = getStepClass($type, $docs);
                        $stepTooltips[$type] = getStepTooltip($type, $docs);
                        $req = $cfg['requires'];
                        $stepLocked[$type]   = $req && !isset($docs[$req]);
                    }

                    $stepKeys = array_keys(STEPS);
                    $lastKey  = end($stepKeys);
                ?>
                <tr>
                    <td style="font-weight:700;color:#888;font-size:12px">
                        <?= $i + 1 ?>
                    </td>

                    <td>
                        <span class="badge badge-num">
                            <?= htmlspecialchars($row['numero_rapport']) ?>
                        </span>
                    </td>

                    <td class="tr" style="max-width:150px;font-size:12px">
                        <?= htmlspecialchars($row['lieu']) ?>
                    </td>

                    <td class="tr" style="font-size:12px">
                        <?= htmlspecialchars($row['proprietaire'] ?? '—') ?>
                    </td>

                    <td style="font-size:12px">
                        <?= $row['date_rapport']
                            ? date('d/m/Y', strtotime($row['date_rapport'])) : '—' ?>
                    </td>

                    <td>
                        <?php if ($row['exploite_oui']): ?>
                            <span class="badge badge-yes">نعم</span>
                        <?php elseif ($row['exploite_non']): ?>
                            <span class="badge badge-no">لا</span>
                        <?php else: ?>—<?php endif; ?>
                    </td>

                    <td class="tr" style="max-width:140px;font-size:11px">
                        <?= htmlspecialchars($row['commission'] ?? '—') ?>
                    </td>

                    <td style="font-size:12px">
                        <?= $row['date_envoi_tratiib']
                            ? date('d/m/Y', strtotime($row['date_envoi_tratiib'])) : '—' ?>
                    </td>

                    <td style="font-size:12px">
                        <?= $row['date_envoi_wiz']
                            ? date('d/m/Y', strtotime($row['date_envoi_wiz'])) : '—' ?>
                    </td>

                    <td style="font-size:12px">
                        <?= $row['date_envoi_turat']
                            ? date('d/m/Y', strtotime($row['date_envoi_turat'])) : '—' ?>
                    </td>

                    <td style="font-size:12px">
                        <?= $row['date_envoi_juridique']
                            ? date('d/m/Y', strtotime($row['date_envoi_juridique'])) : '—' ?>
                    </td>

                    <td style="font-size:12px">
                        <?= $row['date_expert']
                            ? date('d/m/Y', strtotime($row['date_expert'])) : '—' ?>
                    </td>

                    <td>
                        <?php if (!empty($row['decision_evacuation'])): ?>
                            <span class="badge badge-yes" style="font-size:10px">
                                <?= htmlspecialchars($row['decision_evacuation']) ?>
                            </span>
                        <?php else: ?>—<?php endif; ?>
                    </td>

                    <td>
                        <?php if (!empty($row['decision_demolition'])): ?>
                            <span class="badge badge-hdm" style="font-size:10px">
                                <?= htmlspecialchars($row['decision_demolition']) ?>
                            </span>
                        <?php else: ?>—<?php endif; ?>
                    </td>

                    <td class="obs-cell"
                        title="<?= htmlspecialchars($row['observations'] ?? '') ?>">
                        <?= htmlspecialchars($row['observations'] ?? '—') ?>
                    </td>

                    <!-- ══ Procédures ══ -->
                    <td style="min-width:230px;padding:7px 5px">
                        <div class="action-wrap">

                            <!-- Modifier / Supprimer -->
                            <div class="action-top">
                                <a href="pv_export.php?id=<?= $row['id'] ?>"
                                   class="ab" style="background:#6f42c1;color:#fff"
                                   title="تحميل محضر Word">🧾</a>
                                <?php if (userCan('can_add')): ?>
                                <a href="modifier.php?id=<?= $row['id'] ?>"
                                   class="ab ab-edit" title="تعديل">✏️</a>
                                <?php endif; ?>
                                <?php if (hasRole('admin')): ?>
                                <a href="supprimer.php?id=<?= $row['id'] ?>"
                                   class="ab ab-del" title="حذف"
                                   onclick="return confirm('هل أنت متأكد؟')">🗑️</a>
                                <?php endif; ?>
                            </div>

                            <!-- ══ Stepper 5 étapes ══ -->
                            <div class="stepper">
                            <?php foreach (STEPS as $type => $cfg):
                                $cls    = $stepClasses[$type];
                                $tip    = $stepTooltips[$type];
                                $locked = $stepLocked[$type] || !canAccessStep($type);
                                $isLast = ($type === $lastKey);
                            ?>

                                <?php if ($locked): ?>
                                    <span class="step-btn step-locked"
                                          title="<?= htmlspecialchars($tip) ?>">
                                        🔒 <?= mb_substr($cfg['label'], 0, 4) ?>
                                    </span>
                                <?php else: ?>
                                    <a href="document.php?id=<?= $row['id'] ?>&type=<?= $type ?>"
                                       class="step-btn <?= $cls ?>"
                                       title="<?= htmlspecialchars($tip) ?>">
                                        <?= $cfg['icon'] ?>
                                        <?= mb_substr($cfg['label'], 0, 4) ?>
                                        <?php if ($cfg['optional'] && !isset($docs[$type])): ?>
                                            <span style="font-size:9px;opacity:.7">(ف)</span>
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>

                                <?php if (!$isLast): ?>
                                    <?php if ($type === 'turat'): ?>
                                        <span class="step-arrow"
                                              style="color:#17a2b8">·</span>
                                    <?php else: ?>
                                        <span class="step-arrow">←</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                            <?php endforeach; ?>
                            </div>

                            <!-- Légende -->
                            <div style="font-size:9px;color:#bbb;
                                        display:flex;gap:5px;margin-top:2px;
                                        flex-wrap:wrap;justify-content:center">
                                <span style="color:#17a2b8">· (ف) اختياري</span>
                                <span style="color:#ffc107">● مسودة</span>
                                <span style="color:#28a745">● نهائي</span>
                                <span>🔒 مقفل</span>
                            </div>

                        </div>
                    </td>

                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<footer>
    جدول بياني للبنايات المتداعية للسقوط
    &copy; <?= date('Y') ?> — بلدية سوسة
</footer>

</body>
</html>
