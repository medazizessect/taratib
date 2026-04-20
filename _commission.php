<?php
$membresActifs = $pdo->query(
    "SELECT * FROM membres WHERE actif = 1 ORDER BY ordre ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$currentCommission = trim($commissionValue ?? '');
$rows = [];
if ($currentCommission !== '') {
    $parts = preg_split('/\s*\/\s*/u', $currentCommission);
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p === '') continue;
        if (mb_strpos($p, ':') !== false) {
            [$n, $g] = array_pad(array_map('trim', explode(':', $p, 2)), 2, '');
            $rows[] = ['nom' => $n, 'grade' => $g];
        } else {
            $rows[] = ['nom' => $p, 'grade' => ''];
        }
    }
}
?>
<div class="commission-wrap">
    <div class="membres-predefs">
        <?php foreach ($membresActifs as $m):
            $label = trim($m['nom'] . (!empty($m['grade']) ? ' : ' . $m['grade'] : ''));
        ?>
            <button type="button" class="predef-btn"
                    data-nom="<?= htmlspecialchars($m['nom'], ENT_QUOTES) ?>"
                    data-grade="<?= htmlspecialchars($m['grade'] ?? '', ENT_QUOTES) ?>"
                    onclick="addPredefinedMember(this)">
                <?= htmlspecialchars($label) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div id="commission-rows"></div>

    <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
        <button type="button" class="predef-btn" onclick="addCommissionRow('', '')">➕ إضافة عضو</button>
    </div>

    <p class="commission-hint">
        ✍️ أضف أعضاء اللجنة مع الرتبة/الصفة — يمكن التعديل أو الحذف مباشرة.
    </p>
    <input type="hidden" name="commission" id="commission-hidden"
           value="<?= htmlspecialchars($currentCommission, ENT_QUOTES) ?>">
</div>
<script>
(function(){
    var rows = <?= json_encode($rows, JSON_UNESCAPED_UNICODE) ?>;
    var box  = document.getElementById('commission-rows');
    var hidden = document.getElementById('commission-hidden');

    window.addCommissionRow = function(nom, grade) {
        rows.push({nom: nom || '', grade: grade || ''});
        render();
    };

    window.addPredefinedMember = function(btn) {
        var nom = btn.getAttribute('data-nom') || '';
        var grade = btn.getAttribute('data-grade') || '';
        rows.push({nom: nom, grade: grade});
        render();
    };

    window.removeCommissionRow = function(idx) {
        rows.splice(idx, 1);
        render();
    };

    window.updateCommissionRow = function(idx, key, val) {
        if (!rows[idx]) return;
        rows[idx][key] = val;
        sync();
    };

    function sync() {
        hidden.value = rows
            .map(function(r){
                var n = (r.nom || '').trim();
                var g = (r.grade || '').trim();
                if (!n) return '';
                return g ? (n + ' : ' + g) : n;
            })
            .filter(Boolean)
            .join(' / ');
    }

    function render() {
        box.innerHTML = '';
        rows.forEach(function(r, i){
            var row = document.createElement('div');
            row.className = 'commission-row';
            var inNom = document.createElement('input');
            inNom.type = 'text';
            inNom.className = 'commission-input';
            inNom.placeholder = 'اسم العضو';
            inNom.value = r.nom || '';
            inNom.addEventListener('input', function(){ updateCommissionRow(i, 'nom', this.value); });

            var inGrade = document.createElement('input');
            inGrade.type = 'text';
            inGrade.className = 'commission-input';
            inGrade.placeholder = 'الرتبة / الصفة';
            inGrade.value = r.grade || '';
            inGrade.addEventListener('input', function(){ updateCommissionRow(i, 'grade', this.value); });

            var del = document.createElement('button');
            del.type = 'button';
            del.className = 'commission-remove';
            del.textContent = '✖';
            del.addEventListener('click', function(){ removeCommissionRow(i); });

            row.appendChild(inNom);
            row.appendChild(inGrade);
            row.appendChild(del);
            box.appendChild(row);
        });
        sync();
    }

    if (!rows.length) rows.push({nom:'', grade:''});
    render();
})();
</script>
