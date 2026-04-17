<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Template PDF - محضر</strong>
        <img src="/images/Logo_commune_Sousse.svg" alt="Logo" style="max-height:48px;">
    </div>
    <div class="card-body" contenteditable="true">
        <p><strong>محضر رقم:</strong> <?= (int)($item['numero_pv'] ?? 0) ?>/<?= substr((string)($item['annee'] ?? date('Y')), -2) ?></p>
        <p><strong>Propriétaire:</strong> <?= htmlspecialchars((string)($item['proprietaire_nom'] ?? '')) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars((string)($item['date_pv'] ?? '')) ?></p>
        <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars((string)($item['description_situation'] ?? ''))) ?></p>
        <hr>
        <p class="text-muted">Zone modifiable avant impression.</p>
    </div>
    <div class="card-footer"><button class="btn btn-dark" onclick="window.print()">Imprimer</button></div>
</div>
