<div class="container d-flex justify-content-end align-items-center vh-100">
  <div class="card p-4 shadow" style="width: 400px;">
    <h4 class="text-center mb-4">Login Sistem</h4>

    <?php if (isset($_GET['timeout'])): ?>
      <div class="alert alert-warning text-center">
        Sesi Anda telah berakhir karena tidak ada aktivitas selama 5 menit.
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/index.php?url=auth/login">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control" required autocomplete="off">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required autocomplete="off">
      </div>

      <!-- CSRF Token -->
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

      <button type="submit" class="btn btn-primary btn-block">Login</button>
      <div class="mt-3 text-center">
        <a href="<?= BASE_URL ?>/index.php?url=auth/register">Buat Username Baru</a> |
        <a href="<?= BASE_URL ?>/index.php?url=auth/forgot_password">Reset Password</a>
      </div>
    </form>
  </div>
</div>