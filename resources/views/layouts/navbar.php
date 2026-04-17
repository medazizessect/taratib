<?php $user = $_SESSION['user'] ?? null; ?>
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/index.php?route=dashboard">
            <img src="/images/Logo_commune_Sousse.svg" alt="Logo commune" class="logo-header">
            <strong>Taratib</strong>
        </a>
        <div class="d-flex align-items-center gap-3">
            <?php if ($user): ?>
                <span class="text-muted small"><?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['role']) ?>)</span>
                <a class="btn btn-outline-secondary btn-sm" href="/index.php?route=logout">Déconnexion</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
