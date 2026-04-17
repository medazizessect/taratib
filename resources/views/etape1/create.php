<h1 class="h4 mb-3">Créer une réclamation</h1>
<form method="post" class="card card-body">
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label required">Bureau ordre</label><input class="form-control" name="bureau_ordre_id" required></div>
        <div class="col-md-4"><label class="form-label">Date réclamation</label><input type="date" class="form-control" name="date_reclamation"></div>
        <div class="col-md-4"><label class="form-label required">Propriétaire</label><input class="form-control" name="proprietaire_nom" required></div>
        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="4" name="description"></textarea></div>
        <div class="col-12"><label class="form-label">Document URL</label><input class="form-control" name="document_url"></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="btn btn-danger">Enregistrer</button><a href="/index.php?route=etape1/list" class="btn btn-light">Annuler</a></div>
</form>
