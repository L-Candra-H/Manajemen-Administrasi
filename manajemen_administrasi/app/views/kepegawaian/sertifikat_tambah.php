<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

$nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? '';
$isOwner    = ($nipSession === ($sertifikat['nomor_induk_pegawai'] ?? null));
$mode       = $mode ?? 'tambah';

// Guard akses
if ($mode === 'edit') {
  if (!AccessControl::can('pegawai.sertifikat','update') &&
      !(AccessControl::can('pegawai.sertifikat','update-own') && $isOwner)) {
    echo "<div class='alert alert-danger'>Akses ditolak. Anda hanya dapat mengedit Sertifikat milik Anda sendiri.</div>";
    exit;
  }
} else { // tambah
  if (!AccessControl::can('pegawai.sertifikat','create')) {
    echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat menambah Sertifikat baru.</div>";
    exit;
  }
}
?>

<form method="POST" enctype="multipart/form-data" 
      action="<?= BASE_URL ?>/index.php?url=kepegawaian/<?= isset($sertifikat) ? 'updateSertifikat' : 'saveSertifikat' ?>">
  <div class="card border-primary mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">
        <?= ($mode ?? 'tambah') === 'edit' 
            ? 'Edit Sertifikat Pegawai' 
            : 'Form Sertifikat Pegawai' ?>
      </h5>
    </div>
    <div class="card-body">

    <div class="row">
      <!-- NIP dari tabel pegawai -->
      <div class="col-md-6 mb-3">
        <label for="nip" class="form-label">Nomor Induk Pegawai</label>
        <?php if (isset($sertifikat)): ?>
          <input type="text" class="form-control" 
                 value="<?= htmlspecialchars($sertifikat['nomor_induk_pegawai'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
        <?php else: ?>
          <select name="nip" id="nip" class="form-control" required>
            <option value="">-- Pilih Pegawai --</option>
            <?php foreach ($pegawaiList as $p): ?>
              <?php if (strtoupper($p['nama_lengkap']) !== 'ADMINISTRATOR'): ?>
                <option value="<?= htmlspecialchars($p['nomor_induk_pegawai'], ENT_QUOTES, 'UTF-8') ?>"
                  <?= ($sertifikat['nomor_induk_pegawai'] ?? '') == $p['nomor_induk_pegawai'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($p['nomor_induk_pegawai'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($p['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>

      <!-- Jenis Sertifikat -->
      <div class="col-md-6 mb-3">
        <label for="jenis" class="form-label">Jenis Sertifikat</label>
        <select name="jenis" id="jenis" class="form-control" required>
          <option value="">-- Pilih --</option>
          <?php 
            $jenisOptions = ['workshop','seminar','pendidikan dan pelatihan'];
            foreach ($jenisOptions as $opt): 
          ?>
            <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>"
              <?= ($sertifikat['jenis'] ?? '') == $opt ? 'selected' : '' ?>>
              <?= ucfirst($opt) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Mode -->
      <div class="col-md-6 mb-3">
        <label for="mode" class="form-label">Mode</label>
        <select name="mode" id="mode" class="form-control" required>
          <option value="">-- Pilih --</option>
          <option value="Daring" <?= ($sertifikat['mode'] ?? '') == 'Daring' ? 'selected' : '' ?>>Daring</option>
          <option value="Luring" <?= ($sertifikat['mode'] ?? '') == 'Luring' ? 'selected' : '' ?>>Luring</option>
        </select>
      </div>

      <!-- Pemberi Sertifikat -->
      <div class="col-md-6 mb-3">
        <label for="pemberi_sertifikat" class="form-label">Pemberi Sertifikat</label>
        <input type="text" name="pemberi_sertifikat" id="pemberi_sertifikat" class="form-control"
               value="<?= htmlspecialchars($sertifikat['pemberi_sertifikat'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
      </div>

      <!-- Nomor Sertifikat -->
      <div class="col-md-6 mb-3">
        <label for="nomor_sertifikat" class="form-label">Nomor Sertifikat</label>
        <input type="text" name="nomor_sertifikat" id="nomor_sertifikat" class="form-control"
               value="<?= htmlspecialchars($sertifikat['nomor_sertifikat'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
      </div>

      <!-- Tanggal Mulai & Selesai -->
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="mulai" class="form-label">Tanggal Mulai</label>
          <input type="date" name="mulai" id="mulai" class="form-control"
                 value="<?= htmlspecialchars($sertifikat['tanggal_mulai_pelaksanaan'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="col-md-6 mb-3">
          <label for="selesai" class="form-label">Tanggal Selesai</label>
          <input type="date" name="selesai" id="selesai" class="form-control"
                 value="<?= htmlspecialchars($sertifikat['tanggal_akhir_pelaksanaan'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
      </div>

      <!-- Upload Berkas -->
      <div class="col-md-6 mb-3">
        <label for="berkas" class="form-label">Berkas Sertifikat (PDF)</label>
        <input type="file" name="berkas" id="berkas" class="form-control" accept="application/pdf">

        <?php if (isset($sertifikat) && !empty($sertifikat['berkas'])): ?>
          <?php $tahun = date('Y', strtotime($sertifikat['tanggal_mulai_pelaksanaan'] ?? 'now')); ?>
          <p class="mt-2 mb-0">
            File saat ini: 
            <a href="<?= BASE_URL ?>/public/uploads/pegawai/sertifikat/<?= $tahun ?>/<?= htmlspecialchars($sertifikat['berkas'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">
              <?= htmlspecialchars($sertifikat['berkas'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </p>
        <?php endif; ?>
      </div>
    </div>

    <div class="text-right">
      <button type="submit" class="btn btn-success">
        <?= isset($sertifikat) ? 'Update' : 'Simpan' ?>
      </button>
      <a href="index.php?url=kepegawaian/sertifikat" class="btn btn-secondary">Batal</a>
    </div>

  </div>
</form>
