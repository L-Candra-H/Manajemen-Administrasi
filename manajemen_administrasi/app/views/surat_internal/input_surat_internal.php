<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

// Ambil unit dari session (id dan nama)
$unitId   = $_SESSION['user']['unit_id'] ?? null;

// Helper aman untuk echo string
function esc($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Guard akses create / update
$canCreate = AccessControl::can('surat.internal','create') 
          || AccessControl::can('surat.internal','create-own');
$canUpdate = AccessControl::can('surat.internal','update') 
          || AccessControl::can('surat.internal','update-own');

$isEdit = isset($surat);
if ($isEdit && !$canUpdate) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit Surat Internal.</div>";
  exit;
}
if (!$isEdit && !$canCreate) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak menambah Surat Internal.</div>";
  exit;
}

$role    = strtolower($_SESSION['user']['hak_akses'] ?? '');
$jabatanRaw = $_SESSION['user']['nama_jabatan'] ?? '';
$jabatan = strtolower(str_replace(' ', '_', $jabatanRaw));

$isAdministrator = ($role === 'administrator');
$isDirektur      = ($jabatan === 'direktur');
$isJabatanUnit   = in_array($jabatan, [
  'kepala_bidang','ka.bid',
  'kepala_ruang','ka.ruang',
  'kepala_unit','ka.unit',
  'koordinator','koord'
]);

$actionUrl = $isEdit 
    ? "index.php?url=suratinternal/update" 
    : "index.php?url=suratinternal/store";
$title = $isEdit ? "✍️ Edit Surat Internal" : "✍️ Tambah Surat Internal";
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2">
      <h5 class="mb-0"><?= $title ?></h5>
    </div>
    <form method="POST" action="<?= $actionUrl ?>" enctype="multipart/form-data">
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $surat['id'] ?>">
        <input type="hidden" name="berkas_lama" value="<?= $surat['berkas'] ?>">
      <?php endif; ?>

      <div class="card-body">
        <div class="row g-3">
          <!-- Kolom Kiri -->
          <div class="col-md-6">
            <!-- Nomor Surat -->
            <div class="mb-3">
              <label class="form-label">Nomor Surat</label>
              <input type="text" name="nomor_surat" class="form-control" required
                     value="<?= $isEdit ? esc($surat['nomor_surat']) : '' ?>"
                     <?= $isDirektur ? 'readonly' : '' ?>>
            </div>

            <!-- Tanggal Surat -->
            <div class="mb-3">
              <label class="form-label">Tanggal Surat</label>
              <input type="date" name="tanggal_surat" class="form-control"
                     value="<?= ($isEdit && !empty($surat['tanggal_surat'])) 
                                 ? date('Y-m-d', strtotime($surat['tanggal_surat'])) 
                                 : date('Y-m-d') ?>"
                     <?= $isDirektur ? 'readonly' : '' ?>>
            </div>

            <!-- Asal Surat -->
            <div class="mb-3">
              <label class="form-label">Unit (Asal Surat)</label>
              <input type="text" class="form-control" value="<?= esc($unitName) ?>" readonly>
              <input type="hidden" name="asal_surat" value="<?= esc($isEdit ? $unitId : ($_SESSION['user']['unit_id'] ?? '')) ?>">
            </div>

            <!-- Perihal -->
            <div class="mb-3">
              <label class="form-label">Perihal</label>
              <textarea name="perihal" class="form-control" rows="4" readonly>
Dengan hormat, mohon perhatian Bapak/Ibu Direktur untuk membaca Surat Internal yang telah kami sampaikan serta memberikan disposisi sesuai arahan
              </textarea>
            </div>
          </div>

          <!-- Kolom Kanan -->
          <div class="col-md-6">
            <!-- Ditujukan Kepada -->
            <div class="mb-3">
              <label class="form-label">Ditujukan Kepada</label>
              <input type="text" class="form-control" value="Direktur" readonly>
              <input type="hidden" name="ditujukan_kepada" value="1">
            </div>

            <!-- Jawaban -->
            <div class="mb-3">
              <label class="form-label">Jawaban</label>
              <textarea name="jawaban" class="form-control" <?= !$isDirektur ? 'readonly' : '' ?>>
                <?= $isEdit ? esc($surat['jawaban']) : '' ?>
              </textarea>
            </div>

            <!-- Berkas -->
            <div class="mb-3">
              <label class="form-label">Berkas Surat (PDF)</label>
              <input type="file" name="berkas" class="form-control" accept=".pdf"
                     <?= $isDirektur ? 'disabled' : (!$isEdit ? 'required' : '') ?>>
              <?php if ($isEdit && !empty($surat['berkas'])): ?>
                <?php $tahun = date('Y', strtotime($surat['tanggal_surat'])); ?>
                <small class="text-muted">
                  File lama: <a href="<?= BASE_URL ?>/public/uploads/dokumen/surat_internal/<?= $tahun ?>/<?= esc($surat['berkas']) ?>" target="_blank">
                    <?= esc($surat['berkas']) ?>
                  </a>
                </small>
              <?php endif; ?>
              <small class="text-muted">Hanya file PDF yang diterima. Penamaan otomatis: surat_internal_[unit]_[nomor urut].pdf</small>
            </div>
          </div>
        </div>
      </div>

      <div class="card-footer text-end">
        <a href="index.php?url=suratinternal/index" class="btn btn-secondary">Kembali</a>
        <?php if ($isAdministrator || $isJabatanUnit): ?>
          <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'SIMPAN' ?></button>
        <?php elseif ($isDirektur): ?>
          <button type="submit" class="btn btn-primary">KIRIM</button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>