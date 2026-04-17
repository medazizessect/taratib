<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="h5 mb-0">Étape 2 - محضر</h2>
  <a class="btn btn-warning" href="/public/index.php?route=etape2.create">إضافة</a>
</div>
<table class="table table-bordered bg-white">
  <thead><tr><th>ID</th><th>N°</th><th>Date</th><th>Propriétaire</th><th></th></tr></thead>
  <tbody>
    <?php foreach (($items ?? []) as $row): ?>
      <tr class="step-color-orange"><td><?= (int)$row['id'] ?></td><td><?= (int)($row['numero_pv'] ?? 0) ?>/<?= htmlspecialchars(substr((string)($row['annee'] ?? '2026'), -2)) ?></td><td><?= htmlspecialchars($row['date_pv'] ?? '') ?></td><td><?= htmlspecialchars($row['proprietaire_nom'] ?? '') ?></td><td><a href="/public/index.php?route=etape2.show&id=<?= (int)$row['id'] ?>">عرض</a></td></tr>
    <?php endforeach; ?>
  </tbody>
</table>
