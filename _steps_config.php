<?php
define('STEPS', [
    'reclamation' => [
        'icon'     => '📝',
        'label'    => 'شكاوي',
        'color'    => '#dc3545',
        'requires' => null,
        'optional' => false,
        'step_num' => 1,
    ],
    'proces_verbal' => [
        'icon'     => '📋',
        'label'    => 'محضر',
        'color'    => '#f39c12',
        'requires' => 'reclamation',
        'optional' => false,
        'step_num' => 2,
    ],
    'izn_khabir' => [
        'icon'     => '⚖️',
        'label'    => 'اذن خبير',
        'color'    => '#2e6da4',
        'requires' => 'proces_verbal',
        'optional' => false,
        'step_num' => 3,
    ],
    'retour_rapport' => [
        'icon'     => '📨',
        'label'    => 'تقرير خبير',
        'color'    => '#6f42c1',
        'requires' => 'izn_khabir',
        'optional' => false,
        'step_num' => 4,
    ],
    'decision_finale' => [
        'icon'     => '✅',
        'label'    => 'قرار نهائي',
        'color'    => '#28a745',
        'requires' => 'retour_rapport',
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

    if ($locked)              return 'step-btn step-locked';
    if ($exists && $isFinal)  return "step-btn step-done s{$num} step-final";
    if ($exists)              return "step-btn step-done s{$num} step-draft";
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
    return "➕ إنشاء {$cfg['label']}";
}
?>
