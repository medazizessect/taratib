<?php
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/../app/config/Database.php';

$xlsxPath = __DIR__ . '/../VOIE_Nom_Rues_Arabe_2026.xlsx';
if (!file_exists($xlsxPath)) {
    exit("Fichier introuvable: {$xlsxPath}\n");
}

$zip = new ZipArchive();
if ($zip->open($xlsxPath) !== true) {
    exit("Impossible d'ouvrir le fichier XLSX\n");
}

$sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
$sharedXml = $zip->getFromName('xl/sharedStrings.xml');
if ($sheetXml === false) {
    $zip->close();
    exit("Feuille sheet1.xml introuvable\n");
}

$sharedStrings = [];
if ($sharedXml !== false) {
    $sharedDoc = simplexml_load_string($sharedXml);
    if ($sharedDoc && isset($sharedDoc->si)) {
        foreach ($sharedDoc->si as $si) {
            if (isset($si->t)) {
                $sharedStrings[] = (string) $si->t;
            } else {
                $text = '';
                foreach ($si->r as $run) {
                    $text .= (string) $run->t;
                }
                $sharedStrings[] = $text;
            }
        }
    }
}

$sheet = simplexml_load_string($sheetXml);
$zip->close();
if (!$sheet) {
    exit("Contenu feuille invalide\n");
}

$db = Database::connection();
$insert = $db->prepare('INSERT INTO lieux (adresse_libelle, code) VALUES (:adresse_libelle, :code)');

$imported = 0;
foreach ($sheet->sheetData->row as $row) {
    $cells = [];
    foreach ($row->c as $c) {
        $ref = (string) $c['r'];
        $col = preg_replace('/\d+/', '', $ref);
        $type = (string) $c['t'];
        $value = (string) ($c->v ?? '');
        if ($type === 's') {
            $value = $sharedStrings[(int) $value] ?? '';
        }
        $cells[$col] = trim($value);
    }

    $code = $cells['A'] ?? '';
    $label = $cells['B'] ?? '';
    if ($label === '' || mb_strtolower($label) === 'adresse_libelle') {
        continue;
    }

    $insert->execute([
        'adresse_libelle' => $label,
        'code' => $code,
    ]);
    $imported++;
}

echo "Import terminé. {$imported} adresses insérées dans lieux.\n";
