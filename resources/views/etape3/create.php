<h1 class="h4 mb-3">Créer توجيه</h1>
<form method="post" class="card card-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label required">محضر lié</label><select class="form-select" name="proces_verbal_id" required><option value="">Choisir</option><?php foreach ($procesVerbaux as $pv): ?><option value="<?= (int)$pv['id'] ?>">#<?= (int)$pv['id'] ?> - <?= htmlspecialchars($pv['proprietaire_nom']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label required">Bureau ordre</label><input class="form-control" name="bureau_ordre_id" required></div>
<div class="col-md-6"><label class="form-label required">Sujet</label><input class="form-control" name="sujet" required></div>
<div class="col-md-3"><label class="form-label">Type</label><select class="form-select" name="type"><option value="صادر">صادر</option><option value="وارد">وارد</option></select></div>
<div class="col-md-3"><label class="form-label">Couleur</label><select class="form-select" name="couleur"><option value="orange">orange</option><option value="vert">vert</option></select></div>
<div class="col-md-6"><label class="form-label">Désignation expert</label><input class="form-control" name="designation_expert"></div>
<div class="col-md-6"><label class="form-label">Document URL</label><input class="form-control" name="document_url"></div>
</div><div class="mt-3"><button class="btn btn-warning">Enregistrer</button></div></form>
