<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host     = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS batiments_ruine
                CHARACTER SET utf8 COLLATE utf8_unicode_ci");
    $pdo->exec("USE batiments_ruine");

    $pdo->exec("DROP TABLE IF EXISTS documents_officiels");
    $pdo->exec("DROP TABLE IF EXISTS batiments");
    $pdo->exec("DROP TABLE IF EXISTS membres");
    $pdo->exec("DROP TABLE IF EXISTS grades");
    $pdo->exec("DROP TABLE IF EXISTS addresses");
    $pdo->exec("DROP TABLE IF EXISTS modeles_documents");

    // ── Table batiments ──
    $pdo->exec("
        CREATE TABLE batiments (
            id                    INT AUTO_INCREMENT PRIMARY KEY,
            numero_rapport        VARCHAR(20),
            lieu                  TEXT,
            proprietaire          TEXT,
            mise_a_jour           VARCHAR(100),
            notification          VARCHAR(100),
            date_rapport          DATE NULL,
            numero_bureau_ordre   VARCHAR(100),
            date_bureau_ordre     DATE NULL,
            notification_region   TEXT,
            heure_constat         VARCHAR(20),
            exploite_oui          TINYINT(1) DEFAULT 0,
            exploite_non          TINYINT(1) DEFAULT 0,
            cin                   VARCHAR(50),
            occupe_par            TEXT,
            degre_confirmation    VARCHAR(100),
            commission            TEXT,
            commission_json       LONGTEXT,
            description_detaillee LONGTEXT,
            mesures_urgentes      LONGTEXT,
            date_envoi_tratiib    DATE NULL,
            date_envoi_wiz        DATE NULL,
            date_envoi_turat      DATE NULL,
            date_envoi_juridique  DATE NULL,
            date_expert           DATE NULL,
            decision_evacuation   TEXT,
            decision_demolition   TEXT,
            observations          TEXT,
            created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE grades (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            label      VARCHAR(150) NOT NULL UNIQUE,
            actif      TINYINT(1) DEFAULT 1,
            ordre      INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
    ");
    $pdo->exec("
        INSERT INTO grades (label, ordre) VALUES
        ('المهندس المدني المعمارية',1),
        ('ممثل عن بلدية سوسة',2),
        ('ممثل عن الإدارة الجهوية للتجهيز والإسكان',3),
        ('ممثل عن المعهد الوطني للتراث بالساحل',4)
    ");

    $pdo->exec("
        CREATE TABLE addresses (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            libelle    VARCHAR(255) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
    ");
    if (class_exists('ZipArchive') && file_exists(__DIR__ . '/VOIE_Nom_Rues_Arabe_2026.xlsx')) {
        $zip = new ZipArchive();
        if ($zip->open(__DIR__ . '/VOIE_Nom_Rues_Arabe_2026.xlsx') === true) {
            $sheetXml = $zip->getFromName('xl/worksheets/sheet.xml');
            if ($sheetXml) {
                $xml = @simplexml_load_string($sheetXml);
                if ($xml !== false) {
                    $xml->registerXPathNamespace('x','http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                    $insAddr = $pdo->prepare("INSERT IGNORE INTO addresses (libelle) VALUES (?)");
                    $seen = [];
                    foreach ($xml->xpath('//x:sheetData/x:row') ?: [] as $row) {
                        foreach ($row->c as $cell) {
                            $ref = (string)$cell['r'];
                            if (!preg_match('/^E\\d+$/', $ref)) continue;
                            $lib = trim((string)$cell->is->t);
                            if ($lib !== '' && $lib !== 'NomAr' && !isset($seen[$lib])) {
                                $seen[$lib] = true;
                                $insAddr->execute([$lib]);
                            }
                            break;
                        }
                    }
                }
            }
            $zip->close();
        }
    }

    // ── Table documents_officiels ──
    // Ordre: turat(fac) → izn → courrier → evacuation → demolition
    $pdo->exec("
        CREATE TABLE documents_officiels (
            id                    INT AUTO_INCREMENT PRIMARY KEY,
            batiment_id           INT NOT NULL,
            type                  ENUM(
                                    'turat',
                                    'izn_tribunal',
                                    'courrier_expert',
                                    'evacuation',
                                    'demolition'
                                  ) NOT NULL,
            numero_doc            VARCHAR(50),
            date_doc              DATE NULL,
            lieu                  TEXT,
            proprietaire          TEXT,
            numero_rapport        VARCHAR(20),
            date_rapport          DATE NULL,
            nom_expert            VARCHAR(150),
            date_expert           DATE NULL,
            nom_juge              VARCHAR(150),
            date_izn_tribunal     DATE NULL,
            description_batiment  TEXT,
            contenu_specifique    TEXT,
            observations          TEXT,
            statut                ENUM('brouillon','finalise') DEFAULT 'brouillon',
            created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                                  ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_doc (batiment_id, type),
            FOREIGN KEY (batiment_id)
                REFERENCES batiments(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
    ");

    // ── Table membres ──
    $pdo->exec("
        CREATE TABLE membres (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            nom        VARCHAR(150) NOT NULL,
            actif      TINYINT(1) DEFAULT 1,
            ordre      INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
    ");
    $pdo->exec("
        INSERT INTO membres (nom, ordre) VALUES
        ('سنية',1),('هيفاء',2),('محمد كاري',3),
        ('محمد إسماعيل',4),('رضا مصباح',5),('غازي عبودة',6)
    ");

    // ── Table modeles_documents ──
    $pdo->exec("
        CREATE TABLE modeles_documents (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            type       VARCHAR(50) NOT NULL UNIQUE,
            intro      LONGTEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                       ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
    ");
    $pdo->prepare("
        INSERT INTO modeles_documents (type, intro) VALUES
        ('turat',          :t),
        ('izn_tribunal',   :i),
        ('courrier_expert',:c),
        ('evacuation',     :e),
        ('demolition',     :d)
    ")->execute([
        ':t' => 'بعد الاطلاع على ملف البناية المتداعية للسقوط المتواجدة بالعنوان المذكور أدناه، يتشرف المعهد الوطني للتراث بتقديم إجابته حول الطابع المعماري والتراثي للبناية المذكورة.',
        ':i' => 'الحمد لله وحده. بعد اطلاعنا على المطلب محوله وعلى المؤيدات المرافقة له. وعلى أحكام الفصل 213 من م م م ت. نأذن للخبير العدلي بالقيام بالأعمال المشار اليها بالعريضة، وذلك بعد استدعاء الطرفين كما يجب قانونا وعدم التوقف على من بلغه الاستدعاء ولم يحضر، ووفقا للإجراءات القانونية وتحرير تقرير مفصل في الغرض وتسبق له العارضة في شأن مائتي دينارا (200 د) من أجرته.',
        ':c' => 'وبعد، نفيد الجناب أنه تنفيذا لمقتضيات القانون عدد 33 لسنة 2024 المؤرخ في 28 جوان 2024 المتعلق بالبنايات المتداعية للسقوط، أجرت اللجنة الفنية المنصوص عليها صلب الفصل 6 من القانون المذكور معاينة ميدانية للبناء المتواجد بالعنوان المذكور أدناه. لـذا، المرجو من الجناب التفضّل بالإذن بتكليف مهندس خبير اختصاص الخرسانة المسلحة والهياكل الحاملة ليتولى في أجل أقصاه 10 أيام إعداد تقرير أولي وتقريره النهائي في أجل أقصاه شهر.',
        ':e' => 'عملا بالمرسوم عدد 9 لسنة 2023 المؤرخ في 08 مارس 2023 والمتعلق بحل المجالس البلدية وخاصة الفصل الثاني منه، ومكتوب السيد وزير الداخلية المؤرخ في 14 مارس 2023، أن الكاتب العام المكلف بمهمة تسيير الشؤون العادية للبلدية، بعد إطلاعه على مجلة الجماعات المحلية الصادرة بالقانون الأساسي عدد 29 لسنة 2018 وخاصة الفصلين 266 و267 منه، وعلى القانون عدد 33 لسنة 2024 المتعلق بالبنايات المتداعية للسقوط، ولدرء خطر محقق وشيك ومؤكد قَرَّر ما يلي:',
        ':d' => 'عملا بالمرسوم عدد 9 لسنة 2023 المؤرخ في 08 مارس 2023 والمتعلق بحل المجالس البلدية وخاصة الفصل الثاني منه، إن الكاتب العام المكلف بمهمة تسيير الشؤون العادية للبلدية، بعد إطلاعه على مجلة الجماعات المحلية وخاصة الفصلين 266 و267، وعلى القانون عدد 33 لسنة 2024 المتعلق بالبنايات المتداعية للسقوط، وعلى تقرير الاختبار النهائي، ولدرء خطر وشيك ومؤكد قرّر ما يلي:',
    ]);

    // Données initiales
    $ins = $pdo->prepare("
        INSERT INTO batiments
            (numero_rapport,lieu,proprietaire,date_rapport,
             exploite_oui,exploite_non,commission,
             date_envoi_juridique,date_expert,
             decision_demolition,observations)
        VALUES(:nr,:lieu,:prop,:dr,:eoui,:enon,:com,:dej,:dex,:ddem,:obs)
    ");
    $data = [
        ['nr'=>'24/1','lieu'=>'نهج أرناست كونساي- حيّ قابادجي',
         'prop'=>'ورثة الصكلي','dr'=>'2024-10-29','eoui'=>1,'enon'=>0,
         'com'=>'هيفاء / محمد كاري / محمد إسماعيل / رضا مصباح / غازي عبودة',
         'dej'=>null,'dex'=>'2025-01-06','ddem'=>null,
         'obs'=>'تقرير اختبار الخبير: هدم كامل البناية'],
        ['nr'=>'24/2','lieu'=>'نهج سيدي محفوظ عدد 27 المدينة العتيقة',
         'prop'=>'أحمد بن محمد','dr'=>'2024-11-15','eoui'=>1,'enon'=>0,
         'com'=>'سنية / هيفاء / محمد كاري / محمد إسماعيل / رضا مصباح / غازي عبودة',
         'dej'=>null,'dex'=>null,'ddem'=>'13251/615','obs'=>'هدم البناية في أقرب الآجال'],
        ['nr'=>'24/3','lieu'=>'المركب التجاري سوسة بلاص بشارع الحبيب بورقيبة',
         'prop'=>null,'dr'=>'2024-11-15','eoui'=>1,'enon'=>0,
         'com'=>'سنية / هيفاء / محمد كاري / محمد إسماعيل / رضا مصباح / غازي عبودة',
         'dej'=>null,'dex'=>null,'ddem'=>'14258/615','obs'=>null],
        ['nr'=>'24/4','lieu'=>'مطعم السلفدار شارع الحبيب بورقيبة',
         'prop'=>'فتحي بوهلال','dr'=>'2024-11-15','eoui'=>1,'enon'=>0,
         'com'=>'سنية / هيفاء / محمد كاري / محمد إسماعيل / رضا مصباح / غازي عبودة',
         'dej'=>null,'dex'=>null,'ddem'=>null,'obs'=>null],
        ['nr'=>'24/5','lieu'=>'بناية متداعية للسقوط بحي الصفايا',
         'prop'=>'رمزي باصو / سنية البقلوطي','dr'=>'2024-11-15','eoui'=>1,'enon'=>0,
         'com'=>'سنية / محمد كاري / محمد إسماعيل / رضا مصباح / غازي عبودة',
         'dej'=>null,'dex'=>'2025-03-27','ddem'=>null,'obs'=>'هدم كامل البناية'],
        ['nr'=>'24/6','lieu'=>'بناية متداعية للسقوط في نهج حفوز تروكاديرو',
         'prop'=>'منية بن يوسف / سلمى الخشين','dr'=>'2024-11-15','eoui'=>1,'enon'=>0,
         'com'=>'سنية / هيفاء / محمد كاري / محمد إسماعيل / رضا مصباح / غازي عبودة',
         'dej'=>null,'dex'=>'2025-03-27','ddem'=>null,'obs'=>'الترميم الثقيل أو الهدم الكلي'],
    ];
    foreach ($data as $row) $ins->execute($row);

    echo "<!DOCTYPE html><html lang='ar' dir='rtl'>
    <head><meta charset='UTF-8'><title>تهيئة</title>
    <style>body{font-family:Arial;background:#f0f2f5;display:flex;
    justify-content:center;align-items:center;height:100vh;margin:0}
    .box{background:white;padding:40px 50px;border-radius:14px;text-align:center;
    box-shadow:0 4px 20px rgba(0,0,0,.12)}h2{color:#28a745;margin-bottom:10px}
    p{color:#666;font-size:14px;margin:5px 0}
    a{display:inline-block;margin-top:20px;background:#1a3c5e;color:white;
    padding:12px 28px;border-radius:8px;text-decoration:none;font-size:16px}
    </style></head><body><div class='box'>
    <h2>✅ تم إنشاء قاعدة البيانات بنجاح!</h2>
    <p>✔ جدول البنايات: 6 سجلات</p>
    <p>✔ جدول الأعضاء: 6 أعضاء</p>
    <p>✔ نماذج الوثائق: 5 نماذج</p>
    <p style='color:#17a2b8'>✔ ترتيب المراحل:</p>
    <p>🏺 التراث (اختياري) ← ⚖️ إذن ← 📨 خبير ← 📋 إخلاء ← 🏚️ هدم</p>
    <a href='index.php'>🚀 الذهاب إلى التطبيق</a>
    </div></body></html>";

} catch (PDOException $e) {
    die("<div style='font-family:Arial;color:red;padding:20px'>❌ خطأ: ".$e->getMessage()."</div>");
}
?>
