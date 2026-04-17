<h1 class="h4 mb-3">Détail تقرير</h1>
<?php if (!$item): ?><div class="alert alert-warning">Introuvable.</div><?php else: ?><div class="card card-body"><p><strong>Type:</strong> <?= htmlspecialchars($item['type_rapport']) ?></p><p><strong>Décision patrimoine:</strong> <?= htmlspecialchars((string)$item['decision_patrimoine']) ?></p></div><?php endif; ?>
