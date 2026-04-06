<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

$nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? '';
$isOwner    = ($nipSession === ($str['nomor_induk_pegawai'] ?? null));
$mode       = isset($str) ? 'edit' : 'tambah';

// Guard akses
if ($mode === 'edit') {
  if (!AccessControl::can('pegawai.str','update') &&
      !(AccessControl::can('pegawai.str','update-own') && $isOwner)) {
    echo "<div class='alert alert-danger'>Akses ditolak. Anda hanya dapat mengedit STR milik Anda sendiri.</div>";
    exit;
  }
} else { // tambah
  if (!AccessControl::can('pegawai.str','create')) {
    echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat menambah STR baru.</div>";
    exit;
  }
}
?>

<?php if (!isset($pegawaiList) || !is_array($pegawaiList)): ?>
  <div class="alert alert-danger">Data pegawai tidak tersedia.</div>
  <?php exit; ?>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" action="<?= BASE_URL ?>/index.php?url=str/save">
  <div class="card border-info mb-4">
    <div class="card-header bg-info text-white">
      <h5 class="mb-0">Form Surat Tanda Registrasi (STR)</h5>
    </div>
    <div class="card-body">

      <!-- Nomor Induk Pegawai -->
      <div class="form-group">
        <label>Nomor Induk Pegawai</label>
        <select name="nomor_induk_pegawai" id="nipSTR" class="form-control" required>
          <option value="">-- Pilih --</option>
          <?php foreach ($pegawaiList as $p): ?>
            <option value="<?= $p['nomor_induk_pegawai'] ?>" <?= ($str['nomor_induk_pegawai'] ?? '') == $p['nomor_induk_pegawai'] ? 'selected' : '' ?>>
              <?= $p['nomor_induk_pegawai'] ?> - <?= $p['nama_lengkap'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Nama Lengkap -->
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" id="namaLengkapSTR" class="form-control" readonly>
      </div>

      <!-- Nomor STR -->
      <div class="form-group">
        <label>Nomor STR</label>
        <input type="text" name="nomor_STR" class="form-control" value="<?= $str['nomor_STR'] ?? '' ?>" required>
      </div>

      <!-- Tanggal Berlaku STR -->
      <div class="form-row">
        <div class="col">
          <label>Mulai Berlaku STR</label>
          <input type="date" name="mulai_berlaku_STR" class="form-control" value="<?= $str['mulai_berlaku_STR'] ?? '' ?>" required>
        </div>
        <div class="col">
          <label>Berakhir STR</label>
          <input type="date" name="berakhir_STR" class="form-control" value="<?= $str['berakhir_STR'] ?? '' ?>" required>
        </div>
      </div>

      <!-- Berkas STR -->
      <div class="form-group mt-3">
        <label>Berkas STR (PDF)</label>
        <?php if (!empty($str['berkas_STR'])): ?>
          <?= renderPreviewLink('File STR', $str['berkas_STR'] ?? '') ?>
        <?php endif; ?>
        <input type="file" name="berkas_STR" accept=".pdf" class="form-control" <?= empty($str) ? 'required' : '' ?>>
      </div>

      <!-- Masa Aktif STR -->
      <div class="form-group">
        <label>Status Masa Aktif STR</label>
        <span id="masaAktifSTR" class="badge badge-secondary p-2">Belum dihitung</span>
      </div>

      <button type="submit" class="btn btn-primary mt-3">
        <?= isset($str) ? 'Update STR' : 'Simpan STR' ?>
      </button>
    </div>
  </div>
</form>