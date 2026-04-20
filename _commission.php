<?php
$gradesActifs = $pdo->query(
    "SELECT id, label FROM grades WHERE actif = 1 ORDER BY ordre ASC, id ASC"
)->fetchAll(PDO::FETCH_ASSOC);
$membresActifs = $pdo->query(
    "SELECT nom FROM membres WHERE actif = 1 ORDER BY ordre ASC, id ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$commissionJson = trim((string)($commissionJsonValue ?? ''));
$rows = [];
if ($commissionJson !== '') {
    $decoded = json_decode($commissionJson, true);
    if (is_array($decoded)) {
        foreach ($decoded as $r) {
            $nom = trim((string)($r['nom'] ?? ''));
            $grade_id = (int)($r['grade_id'] ?? 0);
            if ($nom !== '') $rows[] = ['nom' => $nom, 'grade_id' => $grade_id];
        }
    }
}
if (empty($rows)) $rows[] = ['nom' => '', 'grade_id' => 0];
?>
<div class="commission-wrap">
    <div id="commission-rows" style="display:flex;flex-direction:column;gap:8px"></div>
    <button type="button" class="btn btn-secondary" style="margin-top:8px" onclick="addCommissionRow()">
        ➕ إضافة عضو
    </button>
    <input type="hidden" name="commission" id="commission-hidden"
           value="<?= htmlspecialchars($commissionValue ?? '', ENT_QUOTES) ?>">
    <input type="hidden" name="commission_json" id="commission-json"
           value="<?= htmlspecialchars($commissionJson, ENT_QUOTES) ?>">
</div>
<datalist id="membres-list">
    <?php foreach ($membresActifs as $m): ?>
        <option value="<?= htmlspecialchars($m['nom']) ?>"></option>
    <?php endforeach; ?>
</datalist>
<script>
(function(){
    var container = document.getElementById('commission-rows');
    var gradeOptions = <?= json_encode($gradesActifs, JSON_UNESCAPED_UNICODE) ?>;
    var rows = <?= json_encode($rows, JSON_UNESCAPED_UNICODE) ?>;
    function render(){
        container.innerHTML = '';
        rows.forEach(function(r, i){
            var row = document.createElement('div');
            row.style.display = 'grid';
            row.style.gridTemplateColumns = '2fr 2fr auto';
            row.style.gap = '8px';

            var nameInput = document.createElement('input');
            nameInput.type = 'text';
            nameInput.setAttribute('list', 'membres-list');
            nameInput.placeholder = 'اسم العضو';
            nameInput.value = String(r.nom || '');
            nameInput.setAttribute('data-i', String(i));
            nameInput.setAttribute('data-k', 'nom');

            var select = document.createElement('select');
            select.setAttribute('data-i', String(i));
            select.setAttribute('data-k', 'grade_id');
            var firstOption = document.createElement('option');
            firstOption.value = '';
            firstOption.textContent = '— الدرجة —';
            select.appendChild(firstOption);
            gradeOptions.forEach(function(g){
                var op = document.createElement('option');
                op.value = String(g.id);
                op.textContent = String(g.label || '');
                if (Number(r.grade_id) === Number(g.id)) op.selected = true;
                select.appendChild(op);
            });

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-cancel';
            removeBtn.textContent = '✖';
            removeBtn.addEventListener('click', function(){ window.removeCommissionRow(i); });

            row.appendChild(nameInput);
            row.appendChild(select);
            row.appendChild(removeBtn);
            container.appendChild(row);
        });
        container.querySelectorAll('input[data-k],select[data-k]').forEach(function(el){
            el.addEventListener('input', function(){
                var i = Number(this.getAttribute('data-i'));
                var k = this.getAttribute('data-k');
                rows[i][k] = this.value;
                syncHidden();
            });
            el.addEventListener('change', function(){
                var i = Number(this.getAttribute('data-i'));
                var k = this.getAttribute('data-k');
                rows[i][k] = this.value;
                syncHidden();
            });
        });
        syncHidden();
    }
    function syncHidden(){
        var normalized = rows
            .map(function(r){
                return {
                    nom: String(r.nom || '').trim(),
                    grade_id: Number(r.grade_id || 0)
                };
            })
            .filter(function(r){ return r.nom !== ''; });
        var gradeMap = {};
        gradeOptions.forEach(function(g){ gradeMap[Number(g.id)] = g.label; });
        var txt = normalized.map(function(r){
            var gl = gradeMap[r.grade_id] || 'بدون درجة';
            return r.nom + ' : ' + gl;
        }).join(' / ');
        document.getElementById('commission-hidden').value = txt;
        document.getElementById('commission-json').value = JSON.stringify(normalized);
    }
    window.addCommissionRow = function(){
        rows.push({nom:'', grade_id:0});
        render();
    };
    window.removeCommissionRow = function(i){
        rows.splice(i, 1);
        if (rows.length === 0) rows.push({nom:'', grade_id:0});
        render();
    };
    render();
})();
</script>
