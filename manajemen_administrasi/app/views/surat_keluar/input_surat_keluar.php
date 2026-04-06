<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

// Guard akses create / update
$canCreate = AccessControl::can('surat.keluar','create');
$canUpdate = AccessControl::can('surat.keluar','update');

$isEdit = isset($surat);
if ($isEdit && !$canUpdate) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit Surat Keluar.</div>";
  exit;
}
if (!$isEdit && !$canCreate) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak menambah Surat Keluar.</div>";
  exit;
}

$role    = strtolower($_SESSION['user']['hak_akses'] ?? '');
$jabatan = strtolower($_SESSION['user']['nama_jabatan'] ?? '');

$isAdminRole      = in_array($role, ['administrator','admin2','admin3']);
$isDirekturOrOther = (!$isAdminRole && $jabatan === 'direktur');

$actionUrl = $isEdit 
    ? "index.php?url=suratkeluar/update" 
    : "index.php?url=suratkeluar/store";
$title = $isEdit ? "✍️ Edit Surat Keluar" : "✍️ Tambah Surat Keluar";
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2">
      <h5 class="mb-0"><?= $title ?></h5>
    </div>
    <form method="POST" action="<?= $actionUrl ?>" enctype="multipart/form-data">
      <?php if ($isEdit): ?>
        <!-- Hidden field untuk edit -->
        <input type="hidden" name="id" value="<?= $surat['id'] ?>">
        <input type="hidden" name="nomor_agenda" value="<?= $surat['nomor_agenda'] ?>">
        <input type="hidden" name="berkas_surat_lama" value="<?= $surat['berkas_surat'] ?>">
      <?php endif; ?>

      <div class="card-body">
        <div class="row g-3">
          <!-- Kolom Kiri -->
          <div class="col-md-6">
            <!-- Jenis Surat -->
            <div class="mb-3">
              <label class="form-label">Jenis Surat</label>
              <select name="jenis_surat_id" class="form-control" <?= $isDirekturOrOther ? 'disabled' : '' ?>>
                <option value="">-- Pilih --</option>
                <?php foreach ($jenis_surat as $js): ?>
                  <option value="<?= $js['id'] ?>" data-kode="<?= $js['kode'] ?>"
                    <?= $isEdit && $js['id']==$surat['jenis_surat_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($js['nama_jenis']) ?> (<?= htmlspecialchars($js['kode']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <!-- hidden field untuk kode jenis -->
              <input type="hidden" name="jenis_surat_kode" id="jenisKode"  
                     value="<?= $isEdit && isset($surat['jenis_kode']) ? $surat['jenis_kode'] : 'GEN' ?>">
            </div>

            <!-- Nomor Surat -->
            <div class="mb-3">
              <label class="form-label">Nomor Surat</label>
              <input type="text" name="nomor_surat" class="form-control" required
                     value="<?= $isEdit ? htmlspecialchars($surat['nomor_surat']) : '' ?>"
                     <?= $isDirekturOrOther ? 'readonly' : '' ?>>
            </div>

            <!-- Tujuan Surat -->
            <div class="mb-3">
              <label class="form-label">Tujuan Surat</label>
              <input type="text" name="tujuan_surat" class="form-control" required
                     value="<?= $isEdit ? htmlspecialchars($surat['tujuan_surat']) : '' ?>"
                     <?= $isDirekturOrOther ? 'readonly' : '' ?>>
            </div>
          </div>

          <!-- Kolom Kanan -->
          <div class="col-md-6">
            <!-- Tanggal Surat -->
            <div class="mb-3">
              <label class="form-label">Tanggal Surat</label>
              <input type="date" name="tanggal_surat" class="form-control"
                     value="<?= ($isEdit && !empty($surat['tanggal_surat'])) 
                                 ? date('Y-m-d', strtotime($surat['tanggal_surat'])) 
                                 : '' ?>"
                     <?= $isDirekturOrOther ? 'readonly' : '' ?>>
            </div>

            <!-- Status Surat -->
            <div class="mb-3">
              <label class="form-label">Status Surat</label>
              <select name="status_surat_id" class="form-control" <?= $isDirekturOrOther ? 'disabled' : '' ?>>
                <option value="">-- Pilih --</option>
                <?php foreach ($status_surat as $st): ?>
                  <?php if (strtolower($st['nama_status']) !== 'diarsipkan'): ?>
                    <option value="<?= $st['id'] ?>"
                      <?= $isEdit && $st['id']==$surat['status_surat_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($st['nama_status']) ?>
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Berkas Surat -->
            <div class="mb-3">
              <label class="form-label">Berkas Surat (PDF)</label>
              <input type="file" name="berkas_surat" class="form-control" accept=".pdf" 
                     <?= !$isEdit ? 'required' : '' ?> <?= $isDirekturOrOther ? 'disabled' : '' ?>>

              <?php if ($isEdit && !empty($surat['berkas_surat'])): ?>
                <small class="text-muted">
                    File lama:
                    <a href="public/uploads/dokumen/surat_keluar/<?= date('Y', strtotime($surat['tanggal_surat'])) ?>/<?= urlencode($surat['berkas_surat']) ?>" target="_blank">
                        <?= htmlspecialchars($surat['berkas_surat']) ?>
                    </a>
                </small><br>

                <small class="text-muted">Jika tidak upload file baru, sistem akan tetap memakai file lama.</small>
              <?php endif; ?>

              <small class="text-muted">
                Hanya file PDF yang diterima. Contoh penamaan otomatis: sk_[kode jenis]_[nomor urut].pdf
              </small>
            </div>
          </div>
        </div>
      </div>

      <div class="card-footer text-end">
        <a href="index.php?url=suratkeluar/index" class="btn btn-secondary">Kembali</a>
        <?php if ($isAdminRole): ?>
          <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Simpan' ?></button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<script>
// Ambil kode jenis surat saat dipilih
document.querySelector('select[name="jenis_surat_id"]').addEventListener('change', function() {
  const selected = this.options[this.selectedIndex];
  const kode = selected.getAttribute('data-kode') || 'GEN';
  document.getElementById('jenisKode').value = kode;
});
</script>