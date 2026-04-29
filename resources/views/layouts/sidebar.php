<?php $role = $_SESSION['user']['role'] ?? 'viewer'; ?>
<div class="list-group mb-3">
  <a class="list-group-item list-group-item-action" href="/public/index.php?route=dashboard">لوحة المتابعة</a>
  <a class="list-group-item list-group-item-action" href="/public/index.php?route=etape1.list">1) شكاوي</a>
  <a class="list-group-item list-group-item-action" href="/public/index.php?route=etape2.list">2) محضر</a>
  <a class="list-group-item list-group-item-action" href="/public/index.php?route=etape3.list">3) توجيه</a>
  <a class="list-group-item list-group-item-action" href="/public/index.php?route=etape4.list">4) تقرير</a>
  <a class="list-group-item list-group-item-action" href="/public/index.php?route=etape5.list">5) قرار</a>
  <div class="list-group-item small text-muted">الدور: <?= htmlspecialchars($role) ?></div>
</div>
