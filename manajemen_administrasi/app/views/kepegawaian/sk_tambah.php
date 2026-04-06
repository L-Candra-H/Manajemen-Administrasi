<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

$nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? '';
$isOwner    = ($nipSession === ($sk['nomor_induk_pegawai'] ?? null));
$mode       = $mode ?? 'tambah';

// Guard akses
if ($mode === 'edit') {
  if (!AccessControl::can('pegawai.sk','update') &&
      !(AccessControl::can('pegawai.sk','update-own') && $isOwner)) {
    echo "<div class='alert alert-danger'>Akses ditolak. Anda hanya dapat mengedit SK milik Anda sendiri.</div>";
    exit;
  }
} else { // tambah
  if (!AccessControl::can('pegawai.sk','create')) {
    echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat menambah SK baru.</div>";
    exit;
  }
}
?>

<?php if (!isset($pegawaiList) || !is_array($pegawaiList)): ?>
  <div class="alert alert-danger">Data pegawai tidak tersedia.</div>
  <?php exit; ?>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" action="<?= BASE_URL ?>/index.php?url=kepegawaian/saveSK">
  <div class="card border-primary mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0"><?= ($mode ?? 'tambah') === 'edit' ? 'Edit Surat Keputusan (SK)' : 'Form Surat Keputusan (SK)' ?></h5>
    </div>

    <!-- Hidden mode & id -->
    <input type="hidden" name="mode" value="<?= $mode ?? 'tambah' ?>">
    <?php if (($mode ?? '') === 'edit'): ?>
      <input type="hidden" name="id" value="<?= htmlspecialchars($sk['id'] ?? '') ?>">
      <input type="hidden" name="nomor_induk_pegawai" value="<?= htmlspecialchars($sk['nomor_induk_pegawai'] ?? '') ?>">
    <?php endif; ?>

    <div class="card-body">

      <!-- Nomor Induk Pegawai -->
      <div class="form-group">
        <label>Nomor Induk Pegawai</label>
        <select name="nomor_induk_pegawai" id="nipSK" class="form-control" <?= ($mode ?? '') === 'edit' ? 'disabled' : 'required' ?>>
          <option value="">-- Pilih --</option>
          <?php foreach ($pegawaiList as $p): ?>
            <?php if (strtoupper($p['nama_lengkap']) !== 'ADMINISTRATOR'): ?>
              <option value="<?= $p['nomor_induk_pegawai'] ?>" <?= ($sk['nomor_induk_pegawai'] ?? '') == $p['nomor_induk_pegawai'] ? 'selected' : '' ?>>
                <?= $p['nomor_induk_pegawai'] ?> - <?= $p['nama_lengkap'] ?>
              </option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Nomor SK -->
      <div class="form-group">
        <label>Nomor SK</label>
        <input type="text" name="nomor_SK" id="nomorSK" class="form-control"
               value="<?= htmlspecialchars($sk['nomor_surat_keputusan'] ?? '') ?>" required>
      </div>

      <!-- Tanggal Berlaku SK -->
      <div class="form-row">
        <div class="col">
          <label>Mulai Berlaku SK</label>
          <input type="date" name="mulai_berlaku_SK" id="mulaiSK" class="form-control"
                 value="<?= htmlspecialchars($sk['mulai_berlaku_surat_keputusan'] ?? '') ?>" required>
        </div>
        <div class="col">
          <label>Berakhir SK</label>
          <input type="date" name="berakhir_SK" id="berakhirSK" class="form-control"
                 value="<?= htmlspecialchars($sk['berakhir_surat_keputusan'] ?? '') ?>" required>
        </div>
      </div>

      <!-- Berkas SK -->
      <div class="form-group mt-3">
        <label>Berkas SK (PDF)</label>
        <?php if (!empty($sk['berkas_surat_keputusan'])): ?>
          <?php $tahun = date('Y', strtotime($sk['mulai_berlaku_surat_keputusan'] ?? 'now')); ?>
          <p class="mb-2">
            File saat ini:
            <a href="<?= BASE_URL ?>/uploads/pegawai/surat_keputusan/<?= $tahun ?>/<?= htmlspecialchars($sk['berkas_surat_keputusan']) ?>" target="_blank">
              <?= htmlspecialchars($sk['berkas_surat_keputusan']) ?>
            </a>
          </p>
          <!-- Hidden berkas lama -->
          <input type="hidden" name="berkas_SK" value="<?= htmlspecialchars($sk['berkas_surat_keputusan']) ?>">
        <?php endif; ?>

        <input type="file" name="berkas_SK" id="berkasSK" accept=".pdf" class="form-control" <?= empty($sk) ? 'required' : '' ?>>
      </div>

      <!-- Masa Aktif SK -->
      <div class="form-group">
        <label>Status Masa Aktif SK</label>
        <span class="badge badge-secondary p-2">
          <?php
            if (!empty($sk['mulai_berlaku_surat_keputusan']) && !empty($sk['berakhir_surat_keputusan'])) {
              $start = strtotime($sk['mulai_berlaku_surat_keputusan']);
              $end   = strtotime($sk['berakhir_surat_keputusan']);
              $days  = round(($end - $start) / (60*60*24));
              $status = ($end >= time()) ? 'Aktif' : 'Kadaluarsa';
              echo $days . ' hari (' . $status . ')';
            } else {
              echo 'Belum dihitung';
            }
          ?>
        </span>
      </div>

      <div class="text-right">
        <button type="submit" class="btn btn-primary">
          <?= ($mode ?? 'tambah') === 'edit' ? 'Update SK' : 'Simpan SK' ?>
        </button>
                <a href="index.php?url=kepegawaian/sk" class="btn btn-secondary">
          Batal
        </a>
    </div>
  </div>
</form>