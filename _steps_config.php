<?php
/**
 * Configuration centralisée des étapes
 * NE PAS utiliser $b ici — ce fichier est inclus globalement
 */

define('STEPS', [
    'turat' => [
        'icon'     => '1️⃣',
        'label'    => 'Réclamation',
        'color'    => '#17a2b8',
        'requires' => null,
        'optional' => false,
        'step_num' => 1,
    ],
    'izn_tribunal' => [
        'icon'     => '2️⃣',
        'label'    => 'محضر',
        'color'    => '#6f42c1',
        'requires' => 'turat',
        'optional' => false,
        'step_num' => 2,
    ],
    'courrier_expert' => [
        'icon'     => '3️⃣',
        'label'    => 'Court',
        'color'    => '#2e6da4',
        'requires' => 'izn_tribunal',
        'optional' => false,
        'step_num' => 3,
    ],
    'evacuation' => [
        'icon'     => '4️⃣',
        'label'    => 'Expert',
        'color'    => '#c0392b',
        'requires' => 'courrier_expert',
        'optional' => false,
        'step_num' => 4,
    ],
    'demolition' => [
        'icon'     => '5️⃣',
        'label'    => 'Decision',
        'color'    => '#e67e22',
        'requires' => 'evacuation',
        'optional' => false,
        'step_num' => 5,
    ],
]);

function getStepClass($type, $docs) {
    if (!defined('STEPS') || !isset(STEPS[$type])) return 'step-todo';
    $cfg     = STEPS[$type];
    $statut  = isset($docs[$type]) ? $docs[$type] : null;
    $exists  = ($statut !== null);
    $isFinal = ($statut === 'finalise');
    $req     = $cfg['requires'];
    $locked  = ($req !== null && !isset($docs[$req]));
    $num     = $cfg['step_num'];

    if ($locked)           return 'step-btn step-locked';
    if ($exists && $isFinal) return "step-btn step-done s{$num} step-final";
    if ($exists)             return "step-btn step-done s{$num} step-draft";
    if ($cfg['optional'])    return 'step-btn step-optional';
    return 'step-btn step-todo';
}

function getStepTooltip($type, $docs) {
    if (!defined('STEPS') || !isset(STEPS[$type])) return '';
    $cfg    = STEPS[$type];
    $statut = isset($docs[$type]) ? $docs[$type] : null;
    $req    = $cfg['requires'];
    $locked = ($req !== null && !isset($docs[$req]));

    if ($locked)                return "🔒 يجب إتمام «" . (STEPS[$req]['label'] ?? '') . "» أولاً";
    if ($statut === 'finalise') return "✅ {$cfg['label']} — نهائي";
    if ($statut === 'brouillon')return "✏️ {$cfg['label']} — مسودة";
    if ($cfg['optional'])       return "🏺 {$cfg['label']} (اختياري)";
    return "➕ إنشاء {$cfg['label']}";
}
?>
