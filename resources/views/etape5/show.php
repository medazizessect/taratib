<h1 class="h4 mb-3">Détail قرار</h1>
<?php if (!$item): ?><div class="alert alert-warning">Introuvable.</div><?php else: ?><div class="card card-body"><p><strong>Type:</strong> <?= htmlspecialchars($item['type_decision']) ?></p><p><strong>Détails:</strong> <?= nl2br(htmlspecialchars((string)$item['details'])) ?></p><span class="badge badge-etape-vert">VERT</span></div><?php endif; ?>
