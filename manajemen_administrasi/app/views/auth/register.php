<div class="container d-flex justify-content-end align-items-center vh-100">
  <div class="card p-4 shadow" style="width: 400px; backdrop-filter: blur(6px); background-color: rgba(255,255,255,0.2); border-radius: 8px;">
    <h4 class="text-center mb-4">Buat Akun Baru</h4>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/index.php?url=auth/register" autocomplete="off">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control" required autocomplete="off">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required autocomplete="off">
      </div>
      <div class="form-group">
        <label>Nomor Induk Pegawai</label>
        <select name="nomor_induk_pegawai" class="form-control" required>
          <option value="">-- Pilih Pegawai --</option>
          <?php foreach ($pegawaiList as $p): ?>
            <option value="<?= htmlspecialchars($p['nomor_induk_pegawai']) ?>">
              <?= htmlspecialchars($p['nomor_induk_pegawai']) ?> - <?= htmlspecialchars($p['nama_lengkap']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Jabatan</label>
        <select name="jabatan_id" class="form-control" required>
          <option value="">-- Pilih Jabatan --</option>
          <?php foreach ($jabatan as $j): ?>
            <?php if ($j['nama_jabatan'] !== 'Administrator'): ?>
              <option value="<?= htmlspecialchars($j['id']) ?>"><?= htmlspecialchars($j['nama_jabatan']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- ✅ CSRF Token -->
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

      <button type="submit" class="btn btn-success btn-block">Daftar</button>
      <div class="mt-3 text-center">
        <a href="<?= BASE_URL ?>/index.php?url=auth/login">Kembali ke Login</a>
      </div>
    </form>
  </div>
</div>