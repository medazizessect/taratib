<h1 class="h4 mb-3">Dashboard</h1>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Dossiers</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>#</th><th>Bureau ordre</th><th>Propriétaire</th><th>Date</th><th>État</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <?php $c = $row['couleur']; ?>
                        <tr class="bg-etape-<?= htmlspecialchars($c) ?>">
                            <td><?= (int) $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['bureau_ordre_id']) ?></td>
                            <td><?= htmlspecialchars($row['proprietaire_nom']) ?></td>
                            <td><?= htmlspecialchars((string) $row['date_reclamation']) ?></td>
                            <td><span class="badge badge-etape-<?= htmlspecialchars($c) ?>"><?= strtoupper($c) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Notifications</div>
            <ul class="list-group list-group-flush">
                <?php if (empty($notifications)): ?>
                    <li class="list-group-item text-muted">Aucune notification.</li>
                <?php else: foreach ($notifications as $n): ?>
                    <li class="list-group-item">
                        <div class="fw-semibold"><?= htmlspecialchars($n['type']) ?></div>
                        <div><?= htmlspecialchars($n['message']) ?></div>
                    </li>
                <?php endforeach; endif; ?>
            </ul>
        </div>
    </div>
</div>
