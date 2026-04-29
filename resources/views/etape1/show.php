<h1 class="h4 mb-3">Détail Réclamation</h1>
<?php if (!$item): ?>
<div class="alert alert-warning">Réclamation introuvable.</div>
<?php else: ?>
<div class="card card-body">
    <p><strong>ID:</strong> <?= (int) $item['id'] ?></p>
    <p><strong>Bureau ordre:</strong> <?= htmlspecialchars($item['bureau_ordre_id']) ?></p>
    <p><strong>Propriétaire:</strong> <?= htmlspecialchars($item['proprietaire_nom']) ?></p>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($item['description'])) ?></p>
</div>
<?php endif; ?>
