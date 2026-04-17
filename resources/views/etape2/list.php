<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Étape 2 - محضر</h1>
    <a href="/index.php?route=etape2/create" class="btn btn-warning">Nouveau محضر</a>
</div>
<table class="table table-bordered bg-white">
    <thead><tr><th>ID</th><th>Numéro</th><th>Propriétaire</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($items as $i): ?>
        <tr class="bg-etape-orange">
            <td><?= (int) $i['id'] ?></td>
            <td><?= (int) $i['numero_pv'] ?>/<?= substr((string) $i['annee'], -2) ?></td>
            <td><?= htmlspecialchars($i['proprietaire_nom']) ?></td>
            <td><?= htmlspecialchars($i['statut']) ?></td>
            <td class="d-flex gap-1">
                <a href="/index.php?route=etape2/show&id=<?= (int) $i['id'] ?>" class="btn btn-sm btn-outline-secondary">Voir</a>
                <a href="/index.php?route=etape2/edit&id=<?= (int) $i['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                <a href="/index.php?route=etape2/pdf&id=<?= (int) $i['id'] ?>" class="btn btn-sm btn-outline-dark">PDF</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
