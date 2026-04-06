<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

$nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? '';
$isOwner    = ($nipSession === ($sip['nomor_induk_pegawai'] ?? null));
$mode       = $mode ?? 'tambah';

// Guard akses
if ($mode === 'edit') {
  if (!AccessControl::can('pegawai.sip','update') &&
      !(AccessControl::can('pegawai.sip','update-own') && $isOwner)) {
    echo "<div class='alert alert-danger'>Akses ditolak. Anda hanya dapat mengedit SIP milik Anda sendiri.</div>";
    exit;
  }
} else { // tambah
  if (!AccessControl::can('pegawai.sip','create')) {
    echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat menambah SIP baru.</div>";
    exit;
  }
}
?>

<?php if (!isset($pegawaiList) || !is_array($pegawaiList)): ?>
  <div class="alert alert-danger">Data pegawai tidak tersedia.</div>
  <?php exit; ?>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" action="<?= BASE_URL ?>/index.php?url=kepegawaian/saveSIP">
  <div class="card border-primary mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0"><?= ($mode ?? 'tambah') === 'edit' ? 'Edit Surat Izin Praktik (SIP)' : 'Form Surat Izin Praktik (SIP)' ?></h5>
    </div>

    <!-- Hidden mode & id -->
    <input type="hidden" name="mode" value="<?= $mode ?? 'tambah' ?>">
    <?php if (($mode ?? '') === 'edit'): ?>
      <input type="hidden" name="id" value="<?= htmlspecialchars($sip['id'] ?? '') ?>">
      <input type="hidden" name="nomor_induk_pegawai" value="<?= htmlspecialchars($sip['nomor_induk_pegawai'] ?? '') ?>">
    <?php endif; ?>

    <div class="card-body">

      <!-- Nomor Induk Pegawai -->
      <div class="form-group">
        <label>Nomor Induk Pegawai</label>
        <select name="nomor_induk_pegawai" id="nipSIP" class="form-control" <?= ($mode ?? '') === 'edit' ? 'disabled' : 'required' ?>>
          <option value="">-- Pilih --</option>
          <?php foreach ($pegawaiList as $p): ?>
            <option value="<?= $p['nomor_induk_pegawai'] ?>" <?= ($sip['nomor_induk_pegawai'] ?? '') == $p['nomor_induk_pegawai'] ? 'selected' : '' ?>>
              <?= $p['nomor_induk_pegawai'] ?> - <?= $p['nama_lengkap'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Nomor SIP -->
      <div class="form-group">
        <label>Nomor SIP</label>
        <input type="text" name="nomor_SIP" id="nomorSIP" class="form-control"
               value="<?= htmlspecialchars($sip['nomor_SIP'] ?? '') ?>" required>
      </div>

      <!-- Tanggal Berlaku SIP -->
      <div class="form-row">
        <div class="col">
          <label>Mulai Berlaku SIP</label>
          <input type="date" name="mulai_berlaku_SIP" id="mulaiSIP" class="form-control"
                 value="<?= htmlspecialchars($sip['mulai_berlaku_SIP'] ?? '') ?>" required>
        </div>
        <div class="col">
          <label>Berakhir SIP</label>
          <input type="date" name="berakhir_SIP" id="berakhirSIP" class="form-control"
                 value="<?= htmlspecialchars($sip['berakhir_SIP'] ?? '') ?>" required>
        </div>
      </div>

      <!-- Berkas SIP -->
      <div class="form-group mt-3">
        <label>Berkas SIP (PDF)</label>
        <?php if (!empty($sip['berkas_SIP'])): ?>
          <p class="mb-2">
            File saat ini:
            <a href="<?= BASE_URL ?>/public/uploads/pegawai/sip/<?= urlencode($sip['berkas_SIP']) ?>" target="_blank">
              <?= htmlspecialchars($sip['berkas_SIP']) ?>
            </a>
          </p>
          <!-- Hidden berkas lama: dikirim ke controller -->
          <input type="hidden" name="berkas_SIP" value="<?= htmlspecialchars($sip['berkas_SIP']) ?>">
        <?php endif; ?>

        <!-- Input file baru (opsional saat edit) -->
        <input type="file" name="berkas_SIP" id="berkasSIP" accept=".pdf" class="form-control" <?= empty($sip) ? 'required' : '' ?>>
      </div>

      <!-- Masa Aktif SIP (read-only) -->
      <div class="form-group">
        <label>Status Masa Aktif SIP</label>
        <span class="badge badge-secondary p-2">
          <?php
            if (!empty($sip['mulai_berlaku_SIP']) && !empty($sip['berakhir_SIP'])) {
              $start = strtotime($sip['mulai_berlaku_SIP']);
              $end   = strtotime($sip['berakhir_SIP']);
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
          <?= ($mode ?? 'tambah') === 'edit' ? 'Update SIP' : 'Simpan SIP' ?>
        </button>
        <a href="index.php?url=kepegawaian/sip" class="btn btn-secondary">
          Batal
        </a>
      </div>

    </div>
  </div>
</form>