<?php
require_once __DIR__ . '/../app/config/Database.php';

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=windows-1256');
}

$xlsx = $argv[1] ?? (__DIR__ . '/../VOIE_Nom_Rues_Arabe_2026.xlsx');
if (!is_file($xlsx)) {
    die("Fichier introuvable: {$xlsx}\n");
}

$zip = new ZipArchive();
if ($zip->open($xlsx) !== true) {
    die("Impossible d'ouvrir le fichier XLSX\n");
}

$sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
$sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
if ($sharedStringsXml === false || $sheetXml === false) {
    $zip->close();
    die("Format XLSX inattendu\n");
}

$sharedStrings = [];
$ss = simplexml_load_string($sharedStringsXml);
if ($ss) {
    foreach ($ss->si as $item) {
        $text = '';
        if (isset($item->t)) {
            $text = (string)$item->t;
        } elseif (isset($item->r)) {
            foreach ($item->r as $run) {
                $text .= (string)$run->t;
            }
        }
        $sharedStrings[] = trim($text);
    }
}

$sheet = simplexml_load_string($sheetXml);
$rows = $sheet->sheetData->row ?? [];

$db = Database::getConnection();
$stmt = $db->prepare('INSERT INTO lieux (adresse_libelle, code) VALUES (:adresse, :code)');
$count = 0;

foreach ($rows as $row) {
    $rowNumber = (int)($row['r'] ?? 0);
    if ($rowNumber === 1) {
        continue;
    }

    $cells = [];
    foreach ($row->c as $cell) {
        $v = (string)$cell->v;
        $type = (string)$cell['t'];
        if ($type === 's') {
            $v = $sharedStrings[(int)$v] ?? '';
        }
        $cells[] = trim($v);
    }

    $code = $cells[0] ?? null;
    $adresse = $cells[1] ?? null;
    if ($adresse === null || $adresse === '') {
        continue;
    }

    $stmt->execute([
        'adresse' => $adresse,
        'code' => ($code !== '' ? $code : null),
    ]);
    $count++;
}

$zip->close();
echo "Import terminé: {$count} adresses\n";
