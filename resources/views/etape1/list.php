<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Étape 1 - شكاوي</h1>
    <a href="/index.php?route=etape1/create" class="btn btn-danger">Nouvelle réclamation</a>
</div>
<table class="table table-bordered bg-white">
    <thead><tr><th>ID</th><th>Bureau ordre</th><th>Propriétaire</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($items as $i): ?>
        <tr>
            <td><?= (int) $i['id'] ?></td>
            <td><?= htmlspecialchars($i['bureau_ordre_id']) ?></td>
            <td><?= htmlspecialchars($i['proprietaire_nom']) ?></td>
            <td><span class="badge badge-etape-rouge">ROUGE</span></td>
            <td><a href="/index.php?route=etape1/show&id=<?= (int) $i['id'] ?>" class="btn btn-sm btn-outline-secondary">Voir</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
