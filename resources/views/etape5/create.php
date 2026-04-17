<h1 class="h4 mb-3">Créer قرار</h1>
<form method="post" class="card card-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label required">Rapport expert</label><select class="form-select" name="rapport_expert_id" required><option value="">Choisir</option><?php foreach ($rapports as $r): ?><option value="<?= (int)$r['id'] ?>">#<?= (int)$r['id'] ?> - <?= htmlspecialchars($r['type_rapport']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Type décision</label><select class="form-select" name="type_decision"><option value="قرار إخلاء">قرار إخلاء</option><option value="قرار هدم">قرار هدم</option></select></div>
<div class="col-md-6"><label class="form-label">Date décision</label><input type="date" class="form-control" name="date_decision"></div>
<div class="col-md-6"><label class="form-label">Document URL</label><input class="form-control" name="document_url"></div>
<div class="col-12"><label class="form-label">Détails</label><textarea class="form-control" rows="4" name="details"></textarea></div>
</div><div class="mt-3"><button class="btn btn-success">Enregistrer</button></div></form>
