<div class="row justify-content-center">
  <div class="col-md-5 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="text-center mb-3">
          <img src="/public/images/Logo_commune_Sousse.svg" class="logo-header" alt="Logo">
          <h1 class="h5 mt-2">تسجيل الدخول</h1>
        </div>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/public/index.php?route=login">
          <div class="mb-3">
            <label class="form-label">اسم المستخدم</label>
            <input class="form-control" type="text" name="username" required>
          </div>
          <div class="mb-3">
            <label class="form-label">كلمة المرور</label>
            <input class="form-control" type="password" name="password" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">دخول</button>
        </form>
      </div>
    </div>
  </div>
</div>
