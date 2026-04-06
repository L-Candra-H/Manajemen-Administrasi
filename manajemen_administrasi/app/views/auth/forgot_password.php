<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <h1 class="h4"><b>Reset Password</b></h1>
    </div>
    <div class="card-body">

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/index.php?url=auth/forgot_password" autocomplete="off">
        <div class="mb-3">
          <label for="username">Username</label>
          <input type="text" name="username" class="form-control" required autocomplete="off">
        </div>
        <div class="mb-3">
          <label for="new_password">Password Baru</label>
          <input type="password" name="new_password" class="form-control" required autocomplete="off">
        </div>

        <!-- ✅ CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
      </form>

      <div class="mt-3 text-center">
        <a href="<?= BASE_URL ?>/index.php?url=auth/login" class="text-muted">Kembali ke Login</a>
      </div>
    </div>
  </div>
</div>