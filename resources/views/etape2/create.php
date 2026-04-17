<div class="card shadow-sm">
  <div class="card-body">
    <h2 class="h5">Formulaire محضر</h2>
    <div class="mb-3">
      <img src="/public/images/Logo_commune_Sousse.svg" class="logo-header" alt="Logo commune">
    </div>
    <form>
      <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Numéro</label><input class="form-control" value="1"></div>
        <div class="col-md-3"><label class="form-label">Année</label><input class="form-control" value="26"></div>
        <div class="col-md-3"><label class="form-label">Date PV</label><input type="date" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Bureau Ordre</label><input class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Propriétaire</label><input class="form-control"></div>
        <div class="col-md-6"><label class="form-label">CIN</label><input class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Est exploité</label>
          <select id="est_exploite" class="form-select"><option value="0">Non</option><option value="1">Oui</option></select>
        </div>
        <div class="col-md-8" id="exploitant_row"><label class="form-label">Exploitant</label><input class="form-control"></div>
        <div class="col-md-12"><label class="form-label">Lieu</label>
          <select class="form-select">
            <?php foreach (($lieux ?? []) as $lieu): ?>
              <option value="<?= (int)$lieu['id'] ?>"><?= htmlspecialchars($lieu['adresse_libelle']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-12"><label class="form-label">Description situation</label><textarea class="form-control" rows="3"></textarea></div>
        <div class="col-md-6"><label class="form-label">Degré confirmation</label><select class="form-select"><option>1</option><option>2</option></select></div>
        <div class="col-md-6"><label class="form-label">Directive ministère</label><input class="form-control"></div>
        <div class="col-md-12">
          <label class="form-label">Membres comité</label>
          <div id="committee-members">
            <div class="input-group mb-2"><input type="text" class="form-control" name="membres_comite[]" placeholder="اسم العضو"></div>
          </div>
          <button id="add-member-btn" type="button" class="btn btn-outline-primary btn-sm">+ إضافة عضو</button>
        </div>
      </div>
    </form>
  </div>
</div>
