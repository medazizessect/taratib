<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="/public/index.php?route=dashboard">
      <img src="/public/images/Logo_commune_Sousse.svg" alt="Logo" class="logo-header">
      <span>بلدية سوسة - Taratib</span>
    </a>
    <div class="d-flex gap-2">
      <?php if (!empty($_SESSION['user'])): ?>
        <span class="badge text-bg-light"><?= htmlspecialchars($_SESSION['user']['full_name'] ?? $_SESSION['user']['username']) ?></span>
        <a class="btn btn-sm btn-outline-light" href="/public/index.php?route=logout">خروج</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
