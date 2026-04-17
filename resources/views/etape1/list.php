<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="h5 mb-0">Étape 1 - شكاوي</h2>
  <a class="btn btn-danger" href="/public/index.php?route=etape1.create">إضافة</a>
</div>
<table class="table table-bordered bg-white">
  <thead><tr><th>ID</th><th>Bureau ordre</th><th>Propriétaire</th><th>Statut</th><th></th></tr></thead>
  <tbody>
    <?php foreach (($items ?? []) as $row): ?>
      <tr class="step-color-red"><td><?= (int)$row['id'] ?></td><td><?= htmlspecialchars($row['bureau_ordre_id'] ?? '') ?></td><td><?= htmlspecialchars($row['proprietaire_nom'] ?? '') ?></td><td><?= htmlspecialchars($row['statut'] ?? 'rouge') ?></td><td><a href="/public/index.php?route=etape1.show&id=<?= (int)$row['id'] ?>">عرض</a></td></tr>
    <?php endforeach; ?>
  </tbody>
</table>
