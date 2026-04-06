<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

// Hak akses: create
if (!(AccessControl::can('pegawai.cuti','create') || AccessControl::can('pegawai.cuti','create-own'))) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengajukan cuti.</div>";
  exit;
}

// Ambil role & user login
$currentUser      = $_SESSION['user'] ?? [];
$currentRole      = $currentUser['hak_akses'] ?? ''; 
$currentPegawaiId = $currentUser['pegawai_id'] ?? null;
$currentNama      = $currentUser['nama_lengkap'] ?? '-';

$isAdminRole      = in_array($currentRole, ['administrator','admin','admin3']);
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-3">
      <h5 class="mb-0">Form Pengajuan Cuti Pegawai</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="index.php?url=cuti/simpan">
        <div class="row">
          <!-- Kolom kiri -->
          <div class="col-md-6">
            <div class="mb-3">
              <label for="pegawai_id" class="form-label">Pegawai</label>
              <?php if ($isAdminRole): ?>
                <!-- ADMIN = dropdown -->
                <select name="pegawai_id" id="pegawai_id" class="form-control" required>
                  <option value="">-- Pilih Pegawai --</option>
                  <?php foreach ($data['pegawai'] as $p): ?>
                    <?php
                      $id = htmlspecialchars($p['id'] ?? '', ENT_QUOTES, 'UTF-8');
                      $nama = trim($p['nama_lengkap'] ?? '');
                      if (strtolower($nama) === 'administrator') continue;
                    ?>
                    <option value="<?= $id ?>"><?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?></option>
                  <?php endforeach; ?>
                </select>

              <?php else: ?>
                <!-- USER biasa = readonly -->
                <input type="hidden" name="pegawai_id" value="<?= htmlspecialchars($currentPegawaiId ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="text" class="form-control" value="<?= htmlspecialchars($currentNama, ENT_QUOTES, 'UTF-8') ?>" readonly>
              <?php endif; ?>
            </div>

            <div class="mb-3">
              <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
              <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
              <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" required>
            </div>
          </div>

          <!-- Kolom kanan -->
          <div class="col-md-6">
            <div class="mb-3">
              <label for="jenis_cuti" class="form-label">Jenis Cuti</label>
              <select name="jenis_cuti" id="jenis_cuti" class="form-control" required>
                <option value="">-- Pilih Jenis Cuti --</option>
                <option value="Tahunan">Tahunan</option>
                <option value="Sakit">Sakit</option>
                <option value="Melahirkan">Melahirkan</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="alasan" class="form-label">Alasan</label>
              <textarea name="alasan" id="alasan" class="form-control" rows="6" required></textarea>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between">
          <a href="index.php?url=cuti/index" class="btn btn-secondary">Kembali</a>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
