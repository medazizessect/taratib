<?php $route = $_GET['route'] ?? 'dashboard'; ?>
<aside class="col-md-2 d-none d-md-block bg-light border-start sidebar p-3">
    <div class="list-group list-group-flush">
        <a class="list-group-item list-group-item-action <?= $route === 'dashboard' ? 'active' : '' ?>" href="/index.php?route=dashboard">Dashboard</a>
        <a class="list-group-item list-group-item-action <?= str_starts_with($route, 'etape1') ? 'active' : '' ?>" href="/index.php?route=etape1/list">Étape 1 - شكاوي</a>
        <a class="list-group-item list-group-item-action <?= str_starts_with($route, 'etape2') ? 'active' : '' ?>" href="/index.php?route=etape2/list">Étape 2 - محضر</a>
        <a class="list-group-item list-group-item-action <?= str_starts_with($route, 'etape3') ? 'active' : '' ?>" href="/index.php?route=etape3/list">Étape 3 - توجيه</a>
        <a class="list-group-item list-group-item-action <?= str_starts_with($route, 'etape4') ? 'active' : '' ?>" href="/index.php?route=etape4/list">Étape 4 - تقرير</a>
        <a class="list-group-item list-group-item-action <?= str_starts_with($route, 'etape5') ? 'active' : '' ?>" href="/index.php?route=etape5/list">Étape 5 - قرار</a>
    </div>
</aside>
