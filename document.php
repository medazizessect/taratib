<?php
error_reporting(0);
ini_set('display_errors', 0);

require 'config.php';
requireLogin();
require 'db.php';
require '_steps_config.php';

$id   = intval($_GET['id']   ?? 0);
$type = trim($_GET['type']   ?? '');

// Types valides
$types_info = [
    'turat'           => ['🏺 إجابة التراث',       '#17a2b8'],
    'izn_tribunal'    => ['⚖️ إذن خبير المحكمة',  '#6f42c1'],
    'courrier_expert' => ['📨 مراسلة تكليف خبير', '#2e6da4'],
    'evacuation'      => ['📋 قرار إخلاء فوري',   '#c0392b'],
    'demolition'      => ['🏚️ قرار هدم',          '#e67e22'],
];

if (!$id || !array_key_exists($type, $types_info)) {
    header("Location: index.php");
    exit;
}

// Charger le bâtiment
$stmtB = $pdo->prepare("SELECT * FROM batiments WHERE id = ?");
$stmtB->execute([$id]);
$batiment = $stmtB->fetch(PDO::FETCH_ASSOC);
if (!$batiment) {
    header("Location: index.php");
    exit;
}

// Charger le document existant
$stmtD = $pdo->prepare("
    SELECT * FROM documents_officiels
    WHERE batiment_id = ? AND type = ?
");
$stmtD->execute([$id, $type]);
$doc = $stmtD->fetch(PDO::FETCH_ASSOC);

// Charger l'intro du modèle
$stmtM = $pdo->prepare("SELECT intro FROM modeles_documents WHERE type = ?");
$stmtM->execute([$type]);
$intro = $stmtM->fetchColumn() ?: '';

// Charger docs existants pour breadcrumb
$stmtAllDocs = $pdo->prepare("
    SELECT type, statut FROM documents_officiels WHERE batiment_id = ?
");
$stmtAllDocs->execute([$id]);
$allDocsBc = [];
foreach ($stmtAllDocs->fetchAll(PDO::FETCH_ASSOC) as $d) {
    $allDocsBc[$d['type']] = $d['statut'];
}

$msg = '';

// ── Sauvegarde ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasRole('agent')) {
    $statut = in_array($_POST['statut'] ?? '', ['brouillon','finalise'])
              ? $_POST['statut'] : 'brouillon';

    $data = [
        ':bid'    => $id,
        ':type'   => $type,
        ':num'    => trim($_POST['numero_doc']           ?? '') ?: null,
        ':datedoc'=> ($_POST['date_doc']                 ?? '') ?: null,
        ':lieu'   => trim($_POST['lieu']                 ?? '') ?: null,
        ':prop'   => trim($_POST['proprietaire']         ?? '') ?: null,
        ':nr'     => trim($_POST['numero_rapport']       ?? '') ?: null,
        ':dr'     => ($_POST['date_rapport']             ?? '') ?: null,
        ':expert' => trim($_POST['nom_expert']           ?? '') ?: null,
        ':dex'    => ($_POST['date_expert']              ?? '') ?: null,
        ':juge'   => trim($_POST['nom_juge']             ?? '') ?: null,
        ':dizn'   => ($_POST['date_izn_tribunal']        ?? '') ?: null,
        ':desc'   => trim($_POST['description_batiment'] ?? '') ?: null,
        ':contenu'=> trim($_POST['contenu_specifique']   ?? '') ?: null,
        ':obs'    => trim($_POST['observations']         ?? '') ?: null,
        ':statut' => $statut,
    ];

    if ($doc) {
        $pdo->prepare("
            UPDATE documents_officiels SET
                numero_doc=:num, date_doc=:datedoc,
                lieu=:lieu, proprietaire=:prop,
                numero_rapport=:nr, date_rapport=:dr,
                nom_expert=:expert, date_expert=:dex,
                nom_juge=:juge, date_izn_tribunal=:dizn,
                description_batiment=:desc,
                contenu_specifique=:contenu,
                observations=:obs, statut=:statut
            WHERE batiment_id=:bid AND type=:type
        ")->execute($data);
    } else {
        $pdo->prepare("
            INSERT INTO documents_officiels
                (batiment_id,type,numero_doc,date_doc,lieu,proprietaire,
                 numero_rapport,date_rapport,nom_expert,date_expert,
                 nom_juge,date_izn_tribunal,description_batiment,
                 contenu_specifique,observations,statut)
            VALUES
                (:bid,:type,:num,:datedoc,:lieu,:prop,:nr,:dr,
                 :expert,:dex,:juge,:dizn,:desc,:contenu,:obs,:statut)
        ")->execute($data);
    }

    // Recharger
    $stmtD->execute([$id, $type]);
    $doc = $stmtD->fetch(PDO::FETCH_ASSOC);
    $msg = $statut;
}

// Valeurs du formulaire
$v = [
    'numero_doc'           => $doc['numero_doc']           ?? '',
    'date_doc'             => $doc['date_doc']             ?? '',
    'lieu'                 => $doc['lieu']                 ?? $batiment['lieu']           ?? '',
    'proprietaire'         => $doc['proprietaire']         ?? $batiment['proprietaire']   ?? '',
    'numero_rapport'       => $doc['numero_rapport']       ?? $batiment['numero_rapport'] ?? '',
    'date_rapport'         => $doc['date_rapport']         ?? $batiment['date_rapport']   ?? '',
    'nom_expert'           => $doc['nom_expert']           ?? '',
    'date_expert'          => $doc['date_expert']          ?? $batiment['date_expert']    ?? '',
    'nom_juge'             => $doc['nom_juge']             ?? '',
    'date_izn_tribunal'    => $doc['date_izn_tribunal']    ?? '',
    'description_batiment' => $doc['description_batiment'] ?? '',
    'contenu_specifique'   => $doc['contenu_specifique']   ?? '',
    'observations'         => $doc['observations']         ?? '',
    'statut'               => $doc['statut']               ?? 'brouillon',
];

$info  = $types_info[$type];
$color = $info[1];
$label = $info[0];

// Mode impression
if (isset($_GET['print'])) {
    if ($v['statut'] !== 'finalise') {
        header("Location: document.php?id=$id&type=$type&err=notfinal");
        exit;
    }
    include 'document_print.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($label) ?> — <?= htmlspecialchars($batiment['numero_rapport']) ?></title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{
            font-family:'Segoe UI',Arial,sans-serif;
            background:#f0f2f5;direction:rtl;color:#333;
            min-height:100vh;
        }

        header{
            background:linear-gradient(135deg,<?= $color ?>,<?= $color ?>bb);
            color:white;padding:15px 25px;
            display:flex;align-items:center;justify-content:space-between;
            box-shadow:0 3px 10px rgba(0,0,0,.2);
            position:sticky;top:0;z-index:100;
        }
        header h1{font-size:18px;font-weight:700}
        header p{font-size:12px;margin-top:3px;opacity:.85}

        .wrap{max-width:900px;margin:22px auto;padding:0 15px}

        /* Breadcrumb */
        .breadcrumb{
            display:flex;align-items:center;gap:0;
            margin-bottom:18px;background:white;
            border-radius:10px;padding:10px 14px;
            box-shadow:0 2px 8px rgba(0,0,0,.07);
            overflow-x:auto;flex-wrap:nowrap;
        }
        .bc-step{
            display:inline-flex;align-items:center;gap:5px;
            padding:5px 12px;border-radius:7px;
            font-size:12px;font-weight:600;white-space:nowrap;
            text-decoration:none;transition:opacity .2s;
        }
        .bc-step.active{color:white}
        .bc-step.done  {color:white;opacity:.75}
        .bc-step.todo  {color:#aaa;background:#f5f5f5}
        .bc-arrow{color:#ccc;font-size:14px;padding:0 3px;flex-shrink:0}

        /* Back */
        .back{
            display:inline-flex;align-items:center;gap:6px;
            margin-bottom:14px;color:<?= $color ?>;
            text-decoration:none;font-size:14px;font-weight:600;
        }
        .back:hover{text-decoration:underline}

        /* Alert */
        .alert{padding:11px 16px;border-radius:8px;
               margin-bottom:14px;font-size:14px}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
        .alert-warning{background:#fff3cd;color:#856404;border:1px solid #ffeeba}
        .alert-danger {background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}

        /* Cards */
        .card{
            background:white;border-radius:12px;padding:22px;
            box-shadow:0 3px 12px rgba(0,0,0,.08);margin-bottom:16px;
        }
        .card-title{
            font-size:15px;font-weight:bold;color:#1a3c5e;
            margin-bottom:16px;padding-bottom:10px;
            border-bottom:2px solid #e8f0fb;
            display:flex;align-items:center;gap:8px;
        }

        /* Grid */
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .fg{display:flex;flex-direction:column;gap:5px}
        .fg.full{grid-column:1/-1}
        label{font-size:12px;font-weight:700;color:#555}

        input[type=text],input[type=date],textarea,select{
            padding:9px 12px;border:2px solid #e9ecef;
            border-radius:8px;font-size:13px;font-family:inherit;
            width:100%;transition:border .2s,box-shadow .2s;background:#fafafa;
        }
        input:focus,textarea:focus,select:focus{
            outline:none;border-color:<?= $color ?>;
            box-shadow:0 0 0 3px <?= $color ?>22;background:white;
        }
        textarea{resize:vertical;min-height:85px;line-height:1.7}

        /* Auto-filled */
        .field-auto{background:#e8f4fd !important;border-color:#bee5eb !important;color:#0c5460}
        .auto-hint{font-size:10px;color:#2e6da4;margin-top:2px}

        /* Section sep */
        .sec-sep{
            grid-column:1/-1;display:flex;align-items:center;gap:10px;
            margin:6px 0 2px;
        }
        .sec-sep span{
            background:<?= $color ?>;color:white;padding:3px 12px;
            border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;
        }
        .sec-sep hr{flex:1;border:none;border-top:2px solid #f0f0f0}

        /* Statut */
        .statut-wrap{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
        .statut-badge{padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700}
        .s-brouillon{background:#fff3cd;color:#856404}
        .s-finalise {background:#d4edda;color:#155724}

        /* Aperçu */
        .preview-card{
            background:#fffef5;border:2px dashed #ffc107;
            border-radius:10px;padding:18px;display:none;margin-top:10px;
        }
        .preview-card.show{display:block}
        .preview-content{
            font-family:'Times New Roman',serif;
            font-size:12pt;line-height:2;direction:rtl;
            text-align:justify;white-space:pre-wrap;
        }

        /* Boutons */
        .btn-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:6px}
        .btn{
            padding:10px 20px;border:none;border-radius:8px;
            cursor:pointer;font-size:14px;font-family:inherit;
            display:inline-flex;align-items:center;gap:6px;
            font-weight:600;transition:opacity .2s,transform .1s;
            text-decoration:none;
        }
        .btn:hover{opacity:.85;transform:translateY(-1px)}
        .btn-draft {background:#ffc107;color:#333}
        .btn-final {background:#28a745;color:white}
        .btn-print {background:<?= $color ?>;color:white}
        .btn-print-locked{background:#dee2e6;color:#aaa;cursor:not-allowed}
        .btn-back  {background:#6c757d;color:white}

        /* Toast */
        #toast{
            display:none;position:fixed;bottom:24px;right:24px;
            background:#333;color:white;padding:14px 22px;
            border-radius:10px;font-size:14px;z-index:9999;
            box-shadow:0 4px 16px rgba(0,0,0,.3);direction:rtl;
        }

        @media(max-width:600px){.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>

<?php include '_menu.php'; ?>

<header>
    <div>
        <h1><?= htmlspecialchars($label) ?></h1>
        <p>محضر رقم: <?= htmlspecialchars($batiment['numero_rapport']) ?>
           — <?= htmlspecialchars(mb_substr($batiment['lieu'] ?? '', 0, 50)) ?></p>
    </div>
    <div style="font-size:13px;opacity:.85">
        <?php
        $s = $v['statut'];
        echo $s === 'finalise'
            ? '<span style="background:rgba(40,167,69,.3);padding:4px 12px;border-radius:20px">✅ نهائي</span>'
            : '<span style="background:rgba(255,193,7,.3);padding:4px 12px;border-radius:20px">✏️ مسودة</span>';
        ?>
    </div>
</header>

<div class="wrap">

    <a href="modifier.php?id=<?= $id ?>" class="back">↩️ رجوع للمحضر</a>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <?php
        $stepsArr  = array_keys(STEPS);
        $lastStepK = end($stepsArr);
        foreach (STEPS as $st => $scfg):
            $stStatut  = $allDocsBc[$st] ?? null;
            $stExists  = ($stStatut !== null);
            $isActive  = ($st === $type);
            $stColor   = $scfg['color'];

            if ($isActive) {
                $cls   = 'active';
                $style = "background:{$stColor}";
            } elseif ($stExists) {
                $cls   = 'done';
                $style = "background:{$stColor}";
            } else {
                $cls   = 'todo';
                $style = '';
            }
        ?>
            <a href="document.php?id=<?= $id ?>&type=<?= $st ?>"
               class="bc-step <?= $cls ?>"
               style="<?= $style ?>">
                <?= $scfg['icon'] ?> <?= $scfg['label'] ?>
                <?php if ($stExists && !$isActive): ?> ✓<?php endif; ?>
                <?php if ($scfg['optional']): ?>
                    <span style="font-size:10px;opacity:.7">(ف)</span>
                <?php endif; ?>
            </a>
            <?php if ($st !== $lastStepK): ?>
                <span class="bc-arrow">←</span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Messages -->
    <?php if (isset($_GET['err']) && $_GET['err'] === 'notfinal'): ?>
        <div class="alert alert-warning">
            ⚠️ لا يمكن الطباعة — يجب حفظ الوثيقة كـ <strong>نهائية</strong> أولاً
        </div>
    <?php endif; ?>
    <?php if ($msg === 'finalise'): ?>
        <div class="alert alert-success">
            ✅ تم الحفظ كنهائي — يمكنك الآن الطباعة 🖨️
        </div>
    <?php elseif ($msg === 'brouillon'): ?>
        <div class="alert alert-warning">
            ✏️ تم الحفظ كمسودة — الطباعة غير متاحة حتى تحفظ كنهائي
        </div>
    <?php endif; ?>

    <form method="POST" id="docForm">
        <input type="hidden" name="statut" id="statut-hidden"
               value="<?= htmlspecialchars($v['statut']) ?>">

        <!-- ── بيانات الوثيقة ── -->
        <div class="card">
            <div class="card-title">📌 بيانات الوثيقة</div>
            <div class="grid">
                <div class="fg">
                    <label>🔢 رقم الوثيقة / العدد</label>
                    <input type="text" name="numero_doc"
                           placeholder="مثال: 531/615"
                           value="<?= htmlspecialchars($v['numero_doc']) ?>">
                </div>
                <div class="fg">
                    <label>📅 تاريخ الوثيقة</label>
                    <input type="date" name="date_doc"
                           value="<?= htmlspecialchars($v['date_doc']) ?>">
                </div>
                <div class="fg">
                    <label>📋 الحالة</label>
                    <div class="statut-wrap">
                        <select name="statut_select" id="statut-select"
                                onchange="document.getElementById('statut-hidden').value=this.value;
                                          updateBadge(this.value)">
                            <option value="brouillon"
                                <?= $v['statut']==='brouillon' ? 'selected' : '' ?>>
                                ✏️ مسودة
                            </option>
                            <option value="finalise"
                                <?= $v['statut']==='finalise' ? 'selected' : '' ?>>
                                ✅ نهائي
                            </option>
                        </select>
                        <span class="statut-badge <?= $v['statut']==='finalise' ? 's-finalise' : 's-brouillon' ?>"
                              id="statut-badge">
                            <?= $v['statut']==='finalise' ? '✅ نهائي' : '✏️ مسودة' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── بيانات البناية ── -->
        <div class="card">
            <div class="card-title">🏚️ بيانات البناية</div>
            <div class="grid">

                <div class="sec-sep full">
                    <span>📍 تعريف البناية</span><hr>
                </div>

                <div class="fg">
                    <label>🔢 عدد المحضر</label>
                    <input type="text" name="numero_rapport"
                           class="field-auto"
                           value="<?= htmlspecialchars($v['numero_rapport']) ?>">
                    <div class="auto-hint">🔄 مُعبَّأ تلقائياً</div>
                </div>
                <div class="fg">
                    <label>📅 تاريخ المعاينة</label>
                    <input type="date" name="date_rapport"
                           class="field-auto"
                           value="<?= htmlspecialchars($v['date_rapport']) ?>">
                    <div class="auto-hint">🔄 مُعبَّأ تلقائياً</div>
                </div>
                <div class="fg full">
                    <label>📍 مكان البناية</label>
                    <input type="text" name="lieu"
                           class="field-auto"
                           value="<?= htmlspecialchars($v['lieu']) ?>">
                    <div class="auto-hint">🔄 مُعبَّأ تلقائياً — يمكن تعديله</div>
                </div>
                <div class="fg full">
                    <label>👤 المالك / المشغول</label>
                    <input type="text" name="proprietaire"
                           class="field-auto"
                           value="<?= htmlspecialchars($v['proprietaire']) ?>">
                    <div class="auto-hint">🔄 مُعبَّأ تلقائياً — يمكن تعديله</div>
                </div>

                <div class="sec-sep full">
                    <span>🏗️ وصف البناية</span><hr>
                </div>
                <div class="fg full">
                    <label>📝 وصف البناية ومكوناتها</label>
                    <textarea name="description_batiment"
                              placeholder="مثال: تتكوّن البناية من طابق أرضي وطابق علوي..."
                    ><?= htmlspecialchars($v['description_batiment']) ?></textarea>
                </div>

                <?php if (in_array($type, ['demolition','courrier_expert','izn_tribunal'])): ?>
                <div class="sec-sep full">
                    <span>🔬 بيانات الخبير</span><hr>
                </div>
                <div class="fg">
                    <label>👨‍💼 اسم الخبير العدلي</label>
                    <input type="text" name="nom_expert"
                           placeholder="الاسم الكامل للخبير"
                           value="<?= htmlspecialchars($v['nom_expert']) ?>">
                </div>
                <div class="fg">
                    <label>📅 تاريخ تقرير الخبير</label>
                    <input type="date" name="date_expert"
                           value="<?= htmlspecialchars($v['date_expert']) ?>">
                </div>
                <?php endif; ?>

                <?php if ($type === 'izn_tribunal'): ?>
                <div class="sec-sep full">
                    <span>⚖️ بيانات المحكمة</span><hr>
                </div>
                <div class="fg">
                    <label>👨‍⚖️ اسم القاضي / الوكيل الأول</label>
                    <input type="text" name="nom_juge"
                           placeholder="الوكيل الأول لرئيس المحكمة"
                           value="<?= htmlspecialchars($v['nom_juge']) ?>">
                </div>
                <div class="fg">
                    <label>📅 تاريخ الإذن</label>
                    <input type="date" name="date_izn_tribunal"
                           value="<?= htmlspecialchars($v['date_izn_tribunal']) ?>">
                </div>
                <?php endif; ?>

                <div class="sec-sep full">
                    <span>📄 تفاصيل القرار / المراسلة</span><hr>
                </div>
                <div class="fg full">
                    <label>✍️ المحتوى التفصيلي</label>
                    <textarea name="contenu_specifique"
                              style="min-height:110px"
                              placeholder="أدخل تفاصيل القرار أو المراسلة..."
                    ><?= htmlspecialchars($v['contenu_specifique']) ?></textarea>
                </div>
                <div class="fg full">
                    <label>📎 ملاحظات إضافية</label>
                    <textarea name="observations"
                              placeholder="ملاحظات..."
                    ><?= htmlspecialchars($v['observations']) ?></textarea>
                </div>

            </div>
        </div>

        <!-- ── Aperçu ── -->
        <div class="card">
            <div class="card-title">👁️ معاينة الوثيقة</div>
            <button type="button"
                    style="background:#17a2b8;color:white;padding:8px 16px;
                           border:none;border-radius:7px;cursor:pointer;
                           font-size:13px;font-family:inherit;font-weight:600;
                           margin-bottom:10px"
                    onclick="togglePreview()">
                👁️ عرض / إخفاء المعاينة
            </button>
            <div class="preview-card" id="preview-box">
                <div class="preview-content" id="preview-content"></div>
            </div>
        </div>

        <!-- ── Actions ── -->
        <div class="btn-row">
            <!-- Brouillon -->
            <button type="button" class="btn btn-draft"
                    onclick="saveDoc('brouillon')">
                💾 حفظ كمسودة
            </button>

            <!-- Final -->
            <button type="button" class="btn btn-final"
                    onclick="saveDoc('finalise')">
                ✅ حفظ كنهائي
            </button>

            <!-- Impression -->
            <?php if ($v['statut'] === 'finalise'): ?>
                <a href="document.php?id=<?= $id ?>&type=<?= $type ?>&print=1"
                   class="btn btn-print" target="_blank">
                    🖨️ طباعة / PDF
                </a>
            <?php else: ?>
                <button type="button"
                        class="btn btn-print-locked"
                        onclick="showToast()">
                    🔒 طباعة / PDF
                </button>
            <?php endif; ?>

            <a href="modifier.php?id=<?= $id ?>" class="btn btn-back">
                ↩️ رجوع
            </a>
        </div>

    </form>
</div>

<!-- Toast -->
<div id="toast">
    ⚠️ يجب حفظ الوثيقة كـ <strong>نهائية</strong> أولاً للطباعة!
</div>

<script>
var introText = <?= json_encode($intro, JSON_UNESCAPED_UNICODE) ?>;
var docType   = '<?= $type ?>';

function saveDoc(statut) {
    document.getElementById('statut-hidden').value = statut;
    document.getElementById('docForm').submit();
}

function updateBadge(val) {
    var b = document.getElementById('statut-badge');
    if (val === 'finalise') {
        b.textContent = '✅ نهائي';
        b.className   = 'statut-badge s-finalise';
    } else {
        b.textContent = '✏️ مسودة';
        b.className   = 'statut-badge s-brouillon';
    }
    document.getElementById('statut-hidden').value = val;
}

function gv(name) {
    var el = document.querySelector('[name="' + name + '"]');
    return el ? el.value.trim() : '';
}

function buildPreview() {
    var nr   = gv('numero_rapport');
    var dr   = gv('date_rapport');
    var lieu = gv('lieu');
    var prop = gv('proprietaire');
    var exp  = gv('nom_expert');
    var dex  = gv('date_expert');
    var juge = gv('nom_juge');
    var dizn = gv('date_izn_tribunal');
    var desc = gv('description_batiment');
    var cont = gv('contenu_specifique');
    var obs  = gv('observations');
    var t = '';

    if (docType === 'turat') {
        t = introText + '\n\n';
        t += 'البناية الكائنة بـ: ' + lieu + '\n';
        t += 'المالك: ' + prop + '\n';
        if (desc) t += '\nوصف البناية:\n' + desc + '\n';
        if (cont) t += '\n' + cont;
    } else if (docType === 'izn_tribunal') {
        t = 'نحن ' + juge + ' الوكيل الأول لرئيس المحكمة الابتدائية بسوسة.\n\n';
        t += introText + '\n\n';
        t += 'نأذن للخبير العدلي "' + exp + '" بالقيام بالأعمال المشار إليها.\n';
        if (cont) t += '\n' + cont + '\n';
        t += '\nسوسة في ' + dizn + '\nالوكيل الأول: ' + juge;
    } else if (docType === 'courrier_expert') {
        t = 'الموضوع: إذن في تكليف مهندس خبير\n\n';
        t += introText + '\n\n';
        t += 'البناية الكائنة بـ: ' + lieu + ' (ملك ' + prop + ')\n';
        if (desc) t += '\nتتكوّن من:\n' + desc + '\n';
        if (cont) t += '\n' + cont + '\n';
        t += '\nالخبير المقترح: ' + exp;
    } else if (docType === 'evacuation') {
        t = introText + '\n\n';
        t += 'الفصل الأول: يتم الشروع الفوري في الإخلاء\n';
        if (cont) t += cont + '\n';
        t += '\nالكائن بـ: ' + lieu;
        t += '\nعلى حساب: ' + prop;
        t += '\n\nالفصل الثاني: الكاتب العام مكلف بالتنفيذ.';
    } else if (docType === 'demolition') {
        t = introText + '\n';
        t += 'محضر عدد ' + nr + ' بتاريخ ' + dr + '\n';
        t += 'تقرير الخبير ' + exp + ' بتاريخ ' + dex + '\n\n';
        t += 'الفصل الأول: الشروع الفوري في الهدم\n';
        if (desc) t += desc + '\n';
        if (cont) t += cont + '\n';
        t += '\nالعقار الكائن بـ: ' + lieu;
        t += '\nعلى حساب: ' + prop;
    }
    if (obs) t += '\n\nملاحظات: ' + obs;
    return t;
}

function togglePreview() {
    var box = document.getElementById('preview-box');
    if (box.classList.contains('show')) {
        box.classList.remove('show');
    } else {
        document.getElementById('preview-content').textContent = buildPreview();
        box.classList.add('show');
    }
}

function showToast() {
    var t = document.getElementById('toast');
    t.style.display = 'block';
    setTimeout(function() { t.style.display = 'none'; }, 3500);
}
</script>

</body>
</html>