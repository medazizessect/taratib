<!doctype html>
<html lang="fr" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Taratib</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh;">
<div class="card shadow-sm" style="max-width:420px;width:100%;">
    <div class="card-body p-4">
        <div class="text-center mb-3">
            <img src="/images/Logo_commune_Sousse.svg" alt="Logo" class="logo-header mb-2">
            <h1 class="h5 mb-0">Connexion</h1>
        </div>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" action="/index.php?route=login">
            <div class="mb-3">
                <label class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Se connecter</button>
        </form>
    </div>
</div>
</body>
</html>
