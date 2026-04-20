<?php
require 'config.php';
requireLogin();
require 'db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$st = $pdo->prepare("SELECT * FROM batiments WHERE id = ?");
$st->execute([$id]);
$b = $st->fetch(PDO::FETCH_ASSOC);
if (!$b) {
    header('Location: index.php');
    exit;
}

$template = __DIR__ . '/pv.docx';
if (!file_exists($template)) {
    die('Template pv.docx not found');
}

function splitToSlots($text, $sizes) {
    $text = trim((string)$text);
    $parts = array_fill(0, count($sizes), '');
    if ($text === '') return $parts;
    $offset = 0;
    $lenTotal = mb_strlen($text, 'UTF-8');
    foreach ($sizes as $i => $size) {
        if ($offset >= $lenTotal) break;
        $take = max(1, (int)$size);
        $parts[$i] = mb_substr($text, $offset, $take, 'UTF-8');
        $offset += mb_strlen($parts[$i], 'UTF-8');
    }
    if ($offset < $lenTotal && count($parts) > 0) {
        $parts[count($parts) - 1] .= mb_substr($text, $offset, null, 'UTF-8');
    }
    return $parts;
}

function dateAr($date) {
    if (!$date) return '';
    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : '';
}

function datePartsAr($date) {
    if (!$date) return ['', '', ''];
    $ts = strtotime($date);
    if (!$ts) return ['', '', ''];
    return [date('d', $ts), date('m', $ts), date('Y', $ts)];
}

$zip = new ZipArchive();
if ($zip->open($template) !== true) die('Cannot open pv.docx');
$xml = $zip->getFromName('word/document.xml');
$zip->close();
if ($xml === false) die('Invalid docx document.xml');

$dom = new DOMDocument();
$dom->preserveWhiteSpace = true;
$dom->formatOutput = false;
$dom->loadXML($xml);
$xp = new DOMXPath($dom);
$xp->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

$runs = [];
foreach ($xp->query('//w:r[w:rPr/w:highlight[@w:val="yellow"]]') as $r) {
    $tNode = $xp->query('.//w:t', $r)->item(0);
    if (!$tNode) continue;
    $runs[] = [
        'node' => $tNode,
        'len'  => max(1, mb_strlen($tNode->nodeValue, 'UTF-8')),
    ];
}

if (count($runs) < 96) die('Template mapping mismatch');

$commission = trim((string)($b['commission'] ?? ''));
$members = $commission !== '' ? preg_split('/\s*\/\s*/u', $commission) : [];
$memberLine = implode(' - ', array_filter(array_map('trim', $members)));

[$dDay, $dMonth, $dYear] = datePartsAr($b['date_rapport'] ?? null);
$timeText = trim((string)($b['heure_rapport'] ?? ''));
$owner = trim((string)($b['proprietaire'] ?? ''));
$occupant = trim((string)($b['occupant'] ?? ''));
$description = trim((string)($b['constat_details'] ?? ''));
if ($description === '') $description = trim((string)($b['observations'] ?? ''));
$actions = trim((string)($b['mesures_urgentes'] ?? ''));
$danger = trim((string)($b['degre_confirmation'] ?? ''));

$ranges = [
    [0, 1, trim((string)($b['notification'] ?? ''))],
    [2, 2, trim((string)($b['bureau_ordre_num'] ?? $b['numero_rapport']))],
    [3, 3, dateAr($b['bureau_ordre_date'] ?? $b['date_rapport'] ?? null)],
    [4, 13, $memberLine],
    [14, 14, $dDay !== '' ? "اليوم $dDay" : ''],
    [15, 15, $dMonth !== '' ? "الشهر $dMonth" : ''],
    [16, 16, $dYear !== '' ? "($dYear)" : ''],
    [17, 17, $timeText !== '' ? "($timeText)" : ''],
    [18, 19, trim((string)($b['lieu'] ?? ''))],
    [20, 22, $owner],
    [23, 27, $occupant],
    [28, 67, $description],
    [68, 80, $actions],
    [81, 87, $danger],
];

foreach ($ranges as $r) {
    [$s, $e, $text] = $r;
    $sizes = [];
    for ($i = $s; $i <= $e; $i++) $sizes[] = $runs[$i]['len'];
    $chunks = splitToSlots($text, $sizes);
    foreach ($chunks as $k => $val) {
        $runs[$s + $k]['node']->nodeValue = $val;
    }
}

$newXml = $dom->saveXML();

$tmpFile = tempnam(sys_get_temp_dir(), 'pv_');
@unlink($tmpFile);
$tmpFile .= '.docx';

$in = new ZipArchive();
$out = new ZipArchive();
if ($in->open($template) !== true || $out->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die('Cannot build output file');
}
for ($i = 0; $i < $in->numFiles; $i++) {
    $name = $in->getNameIndex($i);
    if ($name === 'word/document.xml') {
        $out->addFromString($name, $newXml);
    } else {
        $out->addFromString($name, $in->getFromIndex($i));
    }
}
$in->close();
$out->close();

$filename = 'pv_' . preg_replace('/[^\w\-]+/u', '_', (string)$b['numero_rapport']) . '.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpFile));
readfile($tmpFile);
@unlink($tmpFile);
exit;
?>
