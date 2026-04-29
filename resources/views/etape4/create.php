<h1 class="h4 mb-3">Créer تقرير</h1>
<form method="post" class="card card-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label required">Échange cour</label><select class="form-select" name="echange_cour_id" required><option value="">Choisir</option><?php foreach ($echanges as $e): ?><option value="<?= (int)$e['id'] ?>">#<?= (int)$e['id'] ?> - <?= htmlspecialchars($e['sujet']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Type rapport</label><select class="form-select" name="type_rapport"><option value="تقرير اختيار اولي">تقرير اختيار اولي</option><option value="تقرير اختبار نهائي">تقرير اختبار نهائي</option></select></div>
<div class="col-md-6"><label class="form-label">Date visite</label><input type="date" class="form-control" name="date_visite"></div>
<div class="col-md-6"><label class="form-label">Décision patrimoine</label><input class="form-control" name="decision_patrimoine"></div>
<div class="col-12"><label class="form-label">Échanges patrimoine (une ligne = une entrée)</label><textarea class="form-control" name="echanges_patrimoine" rows="4"></textarea></div>
<div class="col-12"><label class="form-label">Document URL</label><input class="form-control" name="document_url"></div>
</div><div class="mt-3"><button class="btn btn-warning">Enregistrer</button></div></form>
