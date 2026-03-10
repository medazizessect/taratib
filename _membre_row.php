<div class="membre-item <?= $m['actif'] ? '' : 'inactif' ?>">

    <div class="num"><?= $m['ordre'] ?></div>

    <!-- Nom affiché -->
    <span class="nom-display" id="disp-<?= $m['id'] ?>">
        <?= htmlspecialchars($m['nom']) ?>
    </span>

    <!-- Input édition -->
    <input type="text"
           class="nom-edit"
           id="edit-<?= $m['id'] ?>"
           value="<?= htmlspecialchars($m['nom']) ?>">

    <!-- Statut -->
    <span class="status-badge <?= $m['actif'] ? 's-actif' : 's-inactif' ?>">
        <?= $m['actif'] ? 'نشط' : 'معطل' ?>
    </span>

    <!-- Btn Éditer -->
    <button class="btn btn-edit"
            id="bedit-<?= $m['id'] ?>"
            type="button"
            onclick="startEdit(<?= $m['id'] ?>)"
            title="تعديل">✏️</button>

    <!-- Form Enregistrer -->
    <form method="POST" id="fsave-<?= $m['id'] ?>" style="display:contents">
        <input type="hidden" name="action" value="modifier">
        <input type="hidden" name="id"     value="<?= $m['id'] ?>">
        <input type="hidden" name="nom"    id="snomhid-<?= $m['id'] ?>">
        <button type="submit"
                class="btn btn-save"
                id="bsave-<?= $m['id'] ?>">💾 حفظ</button>
    </form>

    <!-- Btn Annuler édition -->
    <button class="btn btn-cancel"
            id="bcancel-<?= $m['id'] ?>"
            type="button"
            onclick="cancelEdit(<?= $m['id'] ?>, '<?= addslashes(htmlspecialchars($m['nom'])) ?>')"
            >✖</button>

    <!-- Toggle actif -->
    <form method="POST" style="display:contents">
        <input type="hidden" name="action" value="toggle">
        <input type="hidden" name="id"     value="<?= $m['id'] ?>">
        <button type="submit" class="btn btn-toggle"
                title="<?= $m['actif'] ? 'تعطيل' : 'تفعيل' ?>">
            <?= $m['actif'] ? '🔕' : '🔔' ?>
        </button>
    </form>

    <!-- Supprimer -->
    <form method="POST" style="display:contents"
          onsubmit="return confirm('هل تريد حذف هذا العضو نهائياً؟')">
        <input type="hidden" name="action" value="supprimer">
        <input type="hidden" name="id"     value="<?= $m['id'] ?>">
        <button type="submit" class="btn btn-del" title="حذف">🗑️</button>
    </form>

</div>