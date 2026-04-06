<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

// Hak akses: update
if (!(AccessControl::can('pegawai.cuti','update') || AccessControl::can('pegawai.cuti','update-own'))) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit cuti.</div>";
  exit;
}

// Ambil user login
$currentUser      = $_SESSION['user'] ?? [];
$currentPegawaiId = $currentUser['pegawai_id'] ?? null;

$isEditMode       = !empty($data['edit']['id_cuti']);
$selectedId       = $data['edit']['pegawai_id'] ?? $currentPegawaiId;
$selectedNIP      = $data['edit']['nomor_induk_pegawai'] ?? null;
$selectedNama     = $data['edit']['nama_lengkap'] ?? '-';

// fallback: cari nama di list pegawai berdasarkan NIP
if ($selectedNama === '-' && $selectedNIP) {
  foreach ($data['pegawai'] as $p) {
    if (isset($p['nomor_induk_pegawai']) && $p['nomor_induk_pegawai'] === $selectedNIP) {
      $selectedNama = $p['nama_lengkap'] ?? '-';
      break;
    }
  }
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-3">
      <h5 class="mb-0">Edit Pengajuan Cuti Pegawai</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="index.php?url=cuti/simpan">
        <!-- hidden id -->
        <input type="hidden" name="id" value="<?= htmlspecialchars($data['edit']['id_cuti'], ENT_QUOTES, 'UTF-8') ?>">

        <div class="row">
          <!-- Kolom kiri -->
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Pegawai</label>
              <!-- EDIT mode = selalu readonly -->
              <input type="hidden" name="pegawai_id" value="<?= htmlspecialchars($selectedId ?? '', ENT_QUOTES, 'UTF-8') ?>">
              <input type="text" class="form-control" value="<?= htmlspecialchars($selectedNama, ENT_QUOTES, 'UTF-8') ?>" readonly>
            </div>

            <div class="mb-3">
              <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
              <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                     value="<?= htmlspecialchars($data['edit']['tanggal_mulai'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                     class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
              <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                     value="<?= htmlspecialchars($data['edit']['tanggal_selesai'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                     class="form-control" required>
            </div>
          </div>

          <!-- Kolom kanan -->
          <div class="col-md-6">
            <div class="mb-3">
              <label for="jenis_cuti" class="form-label">Jenis Cuti</label>
              <select name="jenis_cuti" id="jenis_cuti" class="form-control" required>
                <option value="">-- Pilih Jenis Cuti --</option>
                <option value="Tahunan" <?= ($data['edit']['jenis_cuti'] ?? '') === 'Tahunan' ? 'selected' : '' ?>>Tahunan</option>
                <option value="Sakit" <?= ($data['edit']['jenis_cuti'] ?? '') === 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                <option value="Melahirkan" <?= ($data['edit']['jenis_cuti'] ?? '') === 'Melahirkan' ? 'selected' : '' ?>>Melahirkan</option>
                <option value="Lainnya" <?= ($data['edit']['jenis_cuti'] ?? '') === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="alasan" class="form-label">Alasan</label>
              <textarea name="alasan" id="alasan" class="form-control" rows="6" required><?= htmlspecialchars($data['edit']['alasan'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between">
          <a href="index.php?url=cuti/index" class="btn btn-secondary">Kembali</a>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
