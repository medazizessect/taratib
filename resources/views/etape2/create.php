<h1 class="h4 mb-3">Créer محضر</h1>
<form method="post" class="card card-body">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label required">Réclamation liée</label>
            <select class="form-select" name="reclamation_id" required>
                <option value="">Choisir</option>
                <?php foreach ($reclamations as $r): ?><option value="<?= (int) $r['id'] ?>">#<?= (int) $r['id'] ?> - <?= htmlspecialchars($r['proprietaire_nom']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label required">Numérotation</label>
            <input class="form-control field-highlight" name="numero_pv" value="<?= (int) $nextNumero ?>" required>
            <small class="text-muted">Format affiché: <?= (int) $nextNumero ?>/<?= substr((string)$year, -2) ?></small>
        </div>
        <div class="col-md-4">
            <label class="form-label required">Année</label>
            <input class="form-control field-highlight" name="annee" value="<?= (int) $year ?>" required>
        </div>

        <div class="col-md-4"><label class="form-label">Date PV</label><input type="date" class="form-control field-highlight" name="date_pv"></div>
        <div class="col-md-4"><label class="form-label">CIN propriétaire (optionnel)</label><input class="form-control field-highlight" name="cin_proprietaire"></div>
        <div class="col-md-4"><label class="form-label required">Propriétaire</label><input class="form-control field-highlight" name="proprietaire_nom" required></div>

        <div class="col-md-4">
            <label class="form-label">Lieu</label>
            <select class="form-select field-highlight" name="lieu_id">
                <option value="">Choisir une adresse</option>
                <?php foreach ($lieux as $l): ?><option value="<?= (int) $l['id'] ?>"><?= htmlspecialchars($l['adresse_libelle']) ?></option><?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="est_exploite" name="est_exploite" value="1">
                <label class="form-check-label" for="est_exploite">Le local est exploité</label>
            </div>
        </div>
        <div class="col-md-4" id="exploitant_block" style="display:none;">
            <label class="form-label">Nom exploitant</label>
            <input class="form-control field-highlight" name="exploitant_nom">
        </div>

        <div class="col-md-6"><label class="form-label">Date/Heure réunion</label><input type="datetime-local" class="form-control field-highlight" name="date_reunion"></div>
        <div class="col-md-6"><label class="form-label">Degré confirmation</label><select class="form-select field-highlight" name="degre_confirmation"><option value="1">1</option><option value="2">2</option></select></div>

        <div class="col-12"><label class="form-label">Description situation (WYSIWYG simple)</label><textarea class="form-control field-highlight" rows="5" name="description_situation"></textarea></div>
        <div class="col-12"><label class="form-label">Directive ministère (optionnel)</label><textarea class="form-control field-highlight" rows="2" name="directive_ministere"></textarea></div>

        <div class="col-12">
            <label class="form-label">Membres comité (ajout dynamique)</label>
            <div id="members-wrap">
                <input type="text" class="form-control mb-2" name="membres_comite[]" placeholder="Nom membre 1">
                <input type="text" class="form-control mb-2" name="membres_comite[]" placeholder="Nom membre 2">
            </div>
            <button type="button" id="add-member" class="btn btn-sm btn-outline-secondary">Ajouter membre</button>
        </div>

        <div class="col-12"><label class="form-label">Document URL</label><input class="form-control" name="document_url"></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="btn btn-warning">Enregistrer</button><a href="/index.php?route=etape2/list" class="btn btn-light">Annuler</a></div>
</form>
