<?php
error_reporting(0);
ini_set('display_errors', 0);

$host     = 'localhost';
$dbname   = 'batiments_ruine';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    function ensureColumn(PDO $pdo, $table, $column, $definition) {
        $q = $pdo->prepare("
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ");
        $q->execute([$table, $column]);
        if ((int)$q->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE `$table` ADD `$column` $definition");
        }
    }

    function loadStreetNamesFromXlsx($xlsxPath) {
        if (!is_file($xlsxPath)) return [];
        $zip = new ZipArchive();
        if ($zip->open($xlsxPath) !== true) return [];
        $sheetXml = $zip->getFromName('xl/worksheets/sheet.xml');
        $shared   = $zip->getFromName('xl/sharedStrings.xml');
        if (!$sheetXml) {
            $zip->close();
            return [];
        }

        $sharedStrings = [];
        if ($shared) {
            $sx = @simplexml_load_string($shared);
            if ($sx !== false) {
                $sx->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                foreach ($sx->xpath('//x:si') ?: [] as $si) {
                    $txt = '';
                    foreach ($si->xpath('.//x:t') ?: [] as $t) $txt .= (string)$t;
                    $sharedStrings[] = $txt;
                }
            }
        }

        $xml = @simplexml_load_string($sheetXml);
        $zip->close();
        if ($xml === false) return [];
        $xml->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $streets = [];
        foreach ($xml->xpath('//x:sheetData/x:row') ?: [] as $row) {
            $nomAr = '';
            foreach ($row->c as $cell) {
                $ref = (string)$cell['r'];
                if (!preg_match('/^E\d+$/', $ref)) continue; // NomAr
                $type = (string)$cell['t'];
                if ($type === 'inlineStr') {
                    $nomAr = trim((string)$cell->is->t);
                } else {
                    $val = trim((string)$cell->v);
                    if ($type === 's' && ctype_digit($val)) {
                        $idx = (int)$val;
                        $nomAr = trim($sharedStrings[$idx] ?? '');
                    } else {
                        $nomAr = $val;
                    }
                }
                break;
            }
            if ($nomAr !== '' && mb_strlen($nomAr) >= 2 && $nomAr !== 'NomAr') {
                $streets[$nomAr] = true;
            }
        }
        return array_keys($streets);
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS grades (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            label      VARCHAR(150) NOT NULL UNIQUE,
            actif      TINYINT(1) DEFAULT 1,
            ordre      INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS addresses (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            libelle    VARCHAR(255) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
    ");

    ensureColumn($pdo, 'batiments', 'cin', 'VARCHAR(50) NULL');
    ensureColumn($pdo, 'batiments', 'occupe_par', 'TEXT NULL');
    ensureColumn($pdo, 'batiments', 'degre_confirmation', 'VARCHAR(100) NULL');
    ensureColumn($pdo, 'batiments', 'numero_bureau_ordre', 'VARCHAR(100) NULL');
    ensureColumn($pdo, 'batiments', 'date_bureau_ordre', 'DATE NULL');
    ensureColumn($pdo, 'batiments', 'notification_region', 'TEXT NULL');
    ensureColumn($pdo, 'batiments', 'commission_json', 'LONGTEXT NULL');
    ensureColumn($pdo, 'batiments', 'heure_constat', 'VARCHAR(20) NULL');
    ensureColumn($pdo, 'batiments', 'description_detaillee', 'LONGTEXT NULL');
    ensureColumn($pdo, 'batiments', 'mesures_urgentes', 'LONGTEXT NULL');

    $gCount = (int)$pdo->query("SELECT COUNT(*) FROM grades")->fetchColumn();
    if ($gCount === 0) {
        $pdo->exec("
            INSERT INTO grades (label, ordre) VALUES
            ('المهندس المدني المعمارية',1),
            ('ممثل عن بلدية سوسة',2),
            ('ممثل عن الإدارة الجهوية للتجهيز والإسكان',3),
            ('ممثل عن المعهد الوطني للتراث بالساحل',4)
        ");
    }

    $aCount = (int)$pdo->query("SELECT COUNT(*) FROM addresses")->fetchColumn();
    if ($aCount === 0) {
        $streets = loadStreetNamesFromXlsx(__DIR__ . '/VOIE_Nom_Rues_Arabe_2026.xlsx');
        if (!empty($streets)) {
            $ins = $pdo->prepare("INSERT IGNORE INTO addresses (libelle) VALUES (?)");
            foreach ($streets as $s) $ins->execute([$s]);
        }
    }
} catch (PDOException $e) {
    die("
    <div style='font-family:Arial;padding:30px;text-align:center'>
        <h2 style='color:red'>❌ خطأ في الاتصال بقاعدة البيانات</h2>
        <p style='color:#666;margin:10px 0'>" . $e->getMessage() . "</p>
        <a href='init_db.php'
           style='background:#1a3c5e;color:white;padding:10px 20px;
                  border-radius:6px;text-decoration:none'>
            🔧 إنشاء قاعدة البيانات
        </a>
    </div>");
}
?>
