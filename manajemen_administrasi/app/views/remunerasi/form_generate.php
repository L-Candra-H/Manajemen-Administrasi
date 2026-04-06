<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/AccessControl.php';

// Guard akses view
$canGenerate = AccessControl::can('remunerasi.generate','create');
$canUpdate   = AccessControl::can('remunerasi.tiket','update');

if (!$canGenerate && !$canUpdate) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengakses form tiket remunerasi.</div>";
  exit;
}

// cek apakah ini edit (ada data tiket)
$isEdit = isset($tiket['id']);
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2">
      <h5 class="mb-0"><?= $isEdit ? 'Edit Tiket Remunerasi' : 'Generate Remunerasi Baru' ?></h5>
    </div>
    <div class="card-body">
      <form method="post" action="index.php?url=remunerasi/<?= $isEdit ? 'update_tiket' : 'generate' ?>">
        <?php if ($isEdit): ?>
          <input type="hidden" name="id" value="<?= htmlspecialchars($tiket['id']) ?>">
        <?php endif; ?>

        <div class="row">
          <!-- Tahun -->
          <div class="col-md-6 mb-3">
            <label for="tahun" class="form-label">Tahun</label>
            <input type="number" name="tahun" id="tahun"
                   class="form-control"
                   value="<?= htmlspecialchars($tiket['tahun'] ?? '') ?>"
                   placeholder="-- Isi Tahun --" required>
          </div>

          <!-- Mode Durasi -->
          <div class="col-md-6 mb-3">
            <label for="mode" class="form-label">Periode Hitung</label>
            <select name="mode" id="mode" class="form-control" required>
              <?php $mode = $tiket['mode'] ?? ''; ?>
              <option value="">-- Pilih Periode Hitung --</option>
              <option value="bulanan" <?= $mode=='bulanan'?'selected':'' ?>>Bulanan (1 bulan)</option>
              <option value="triwulan" <?= $mode=='triwulan'?'selected':'' ?>>Triwulan (3 bulan)</option>
              <option value="semester" <?= $mode=='semester'?'selected':'' ?>>Semester (6 bulan)</option>
              <option value="tahunan" <?= $mode=='tahunan'?'selected':'' ?>>Tahunan (12 bulan)</option>
            </select>
          </div>
        </div>

        <div class="row">
          <!-- Periode -->
          <div class="col-md-6 mb-3">
            <label for="periode" class="form-label">Periode (YYYY-MM)</label>
            <input type="month" name="periode" id="periode"
                   class="form-control"
                   value="<?= htmlspecialchars($tiket['periode'] ?? '') ?>"
                   required>
          </div>

          <!-- Nominal -->
          <div class="col-md-6 mb-3">
            <label for="totalNominal" class="form-label">Total Nominal Remunerasi</label>
            <input type="number" name="totalNominal" id="totalNominal"
                   class="form-control"
                   value="<?= htmlspecialchars($tiket['total_nominal'] ?? '') ?>"
                   placeholder="Masukkan total nominal" required>
          </div>
        </div>

        <?php if ($isEdit): ?>
        <div class="mb-3">
          <label for="status" class="form-label">Status</label>
          <select name="status" id="status" class="form-control">
            <option value="aktif" <?= $tiket['status']=='aktif'?'selected':'' ?>>Aktif</option>
            <option value="batal" <?= $tiket['status']=='batal'?'selected':'' ?>>Batal</option>
          </select>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Generate' ?></button>
        <a href="index.php?url=remunerasi/tiket_list" class="btn btn-secondary">Kembali</a>
      </form>
    </div>
  </div>
</div>
