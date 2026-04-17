<h1 class="h4 mb-3">Détail محضر</h1>
<?php if (!$item): ?>
<div class="alert alert-warning">Introuvable.</div>
<?php else: ?>
<div class="card card-body">
    <p><strong>Numéro:</strong> <?= (int)$item['numero_pv'] ?>/<?= substr((string)$item['annee'], -2) ?></p>
    <p><strong>Propriétaire:</strong> <?= htmlspecialchars($item['proprietaire_nom']) ?></p>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($item['description_situation'])) ?></p>
    <a class="btn btn-outline-dark" href="/index.php?route=etape2/pdf&id=<?= (int)$item['id'] ?>">Voir template PDF</a>
</div>
<?php endif; ?>
