<h1 class="h4 mb-3">Modifier محضر</h1>
<?php $members = json_decode((string)($item['membres_comite'] ?? '[]'), true) ?: []; ?>
<form method="post" class="card card-body">
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Date PV</label><input type="date" class="form-control" name="date_pv" value="<?= htmlspecialchars((string)($item['date_pv'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">CIN</label><input class="form-control" name="cin_proprietaire" value="<?= htmlspecialchars((string)($item['cin_proprietaire'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Propriétaire</label><input class="form-control" name="proprietaire_nom" value="<?= htmlspecialchars((string)($item['proprietaire_nom'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Lieu</label><select class="form-select" name="lieu_id"><?php foreach ($lieux as $l): ?><option value="<?= (int)$l['id'] ?>" <?= (int)$l['id'] === (int)$item['lieu_id'] ? 'selected' : '' ?>><?= htmlspecialchars($l['adresse_libelle']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" id="est_exploite" name="est_exploite" value="1" <?= !empty($item['est_exploite']) ? 'checked' : '' ?>><label class="form-check-label" for="est_exploite">Exploité</label></div></div>
        <div class="col-md-4" id="exploitant_block"><label class="form-label">Exploitant</label><input class="form-control" name="exploitant_nom" value="<?= htmlspecialchars((string)($item['exploitant_nom'] ?? '')) ?>"></div>
        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="4" name="description_situation"><?= htmlspecialchars((string)($item['description_situation'] ?? '')) ?></textarea></div>
        <div class="col-md-6"><label class="form-label">Degré</label><select class="form-select" name="degre_confirmation"><option value="1" <?= (int)$item['degre_confirmation'] === 1 ? 'selected' : '' ?>>1</option><option value="2" <?= (int)$item['degre_confirmation'] === 2 ? 'selected' : '' ?>>2</option></select></div>
        <div class="col-md-6"><label class="form-label">Date réunion</label><input type="datetime-local" class="form-control" name="date_reunion" value="<?= htmlspecialchars(str_replace(' ', 'T', (string)($item['date_reunion'] ?? ''))) ?>"></div>
        <div class="col-12"><label class="form-label">Directive ministère</label><textarea class="form-control" name="directive_ministere"><?= htmlspecialchars((string)($item['directive_ministere'] ?? '')) ?></textarea></div>
        <div class="col-12"><label class="form-label">Membres</label><div id="members-wrap"><?php foreach ($members as $m): ?><input class="form-control mb-2" name="membres_comite[]" value="<?= htmlspecialchars($m) ?>"><?php endforeach; ?></div><button type="button" id="add-member" class="btn btn-sm btn-outline-secondary">Ajouter membre</button></div>
        <div class="col-md-4"><label class="form-label">Statut</label><select class="form-select" name="statut"><option value="brouillon" <?= $item['statut']==='brouillon'?'selected':'' ?>>brouillon</option><option value="finalisé" <?= $item['statut']==='finalisé'?'selected':'' ?>>finalisé</option><option value="imprimé" <?= $item['statut']==='imprimé'?'selected':'' ?>>imprimé</option></select></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="btn btn-primary">Mettre à jour</button><a href="/index.php?route=etape2/list" class="btn btn-light">Annuler</a></div>
</form>
