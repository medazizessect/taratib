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

    // Migration légère pour compatibilité avec anciennes bases
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('batiments', $tables, true)) {
        $cols = $pdo->query("SHOW COLUMNS FROM batiments")->fetchAll(PDO::FETCH_COLUMN);
        $missing = [
            'bureau_ordre_num'   => "ALTER TABLE batiments ADD COLUMN bureau_ordre_num VARCHAR(50) NULL",
            'bureau_ordre_date'  => "ALTER TABLE batiments ADD COLUMN bureau_ordre_date DATE NULL",
            'heure_rapport'      => "ALTER TABLE batiments ADD COLUMN heure_rapport VARCHAR(10) NULL",
            'cin'                => "ALTER TABLE batiments ADD COLUMN cin VARCHAR(30) NULL",
            'occupant'           => "ALTER TABLE batiments ADD COLUMN occupant TEXT NULL",
            'degre_confirmation' => "ALTER TABLE batiments ADD COLUMN degre_confirmation VARCHAR(100) NULL",
            'constat_details'    => "ALTER TABLE batiments ADD COLUMN constat_details LONGTEXT NULL",
            'mesures_urgentes'   => "ALTER TABLE batiments ADD COLUMN mesures_urgentes LONGTEXT NULL",
            'adresse_id'         => "ALTER TABLE batiments ADD COLUMN adresse_id INT NULL",
        ];
        foreach ($missing as $name => $sql) if (!in_array($name, $cols, true)) $pdo->exec($sql);
    }

    if (in_array('membres', $tables, true)) {
        $cols = $pdo->query("SHOW COLUMNS FROM membres")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('grade', $cols, true)) {
            $pdo->exec("ALTER TABLE membres ADD COLUMN grade VARCHAR(150) NULL AFTER nom");
        }
    }

    if (!in_array('adresses', $tables, true)) {
        $pdo->exec("
            CREATE TABLE adresses (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                libelle    VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ");
    }

    $hasAddresses = (int)$pdo->query("SELECT COUNT(*) FROM adresses")->fetchColumn();
    if ($hasAddresses === 0 && file_exists(__DIR__ . '/VOIE_Nom_Rues_Arabe_2026.xlsx') && class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open(__DIR__ . '/VOIE_Nom_Rues_Arabe_2026.xlsx') === true) {
            $shared = [];
            $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedXml) {
                $sd = new DOMDocument();
                if (@$sd->loadXML($sharedXml, LIBXML_PARSEHUGE | LIBXML_NOERROR | LIBXML_NOWARNING)) {
                    $sx = new DOMXPath($sd);
                    $sx->registerNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                    foreach ($sx->query('//a:si') as $si) {
                        $v = '';
                        foreach ($sx->query('.//a:t', $si) as $t) $v .= $t->nodeValue;
                        $shared[] = trim($v);
                    }
                }
            }
            $sheetXml = $zip->getFromName('xl/worksheets/sheet.xml');
            if ($sheetXml) {
                $wd = new DOMDocument();
                if (@$wd->loadXML($sheetXml, LIBXML_PARSEHUGE | LIBXML_NOERROR | LIBXML_NOWARNING)) {
                    $ws = new DOMXPath($wd);
                    $ws->registerNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                    $insAdr = $pdo->prepare("INSERT IGNORE INTO adresses (libelle) VALUES (?)");
                    foreach ($ws->query('//a:sheetData/a:row/a:c[starts-with(@r,"E")]') as $c) {
                            $vNode = $ws->query('./a:v', $c)->item(0);
                            if (!$vNode) continue;
                            $raw = trim($vNode->nodeValue);
                            if ($raw === '') continue;
                            $t = $c->attributes->getNamedItem('t');
                            $val = (($t && $t->nodeValue === 's') && ctype_digit($raw))
                                ? ($shared[(int)$raw] ?? '')
                                : $raw;
                            $val = trim($val);
                            if ($val !== '') $insAdr->execute([$val]);
                    }
                }
            }
            $zip->close();
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
