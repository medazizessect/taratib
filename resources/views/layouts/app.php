<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=windows-1256');
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="windows-1256">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Taratib</title>
  <link rel="stylesheet" href="/public/css/bootstrap.min.css">
  <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<div class="container-fluid py-3">
  <div class="row g-3">
    <?php if (!empty($_SESSION['user'])): ?>
      <aside class="col-lg-3 col-xl-2">
        <?php include __DIR__ . '/sidebar.php'; ?>
      </aside>
      <main class="col-lg-9 col-xl-10">
        <?php include $viewFile; ?>
      </main>
    <?php else: ?>
      <main class="col-12">
        <?php include $viewFile; ?>
      </main>
    <?php endif; ?>
  </div>
</div>
<script src="/public/js/script.js"></script>
</body>
</html>
