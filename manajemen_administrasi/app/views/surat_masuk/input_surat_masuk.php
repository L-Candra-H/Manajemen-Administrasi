<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../helpers/AccessControl.php';

// --- izin dasar ---
$canCreate = AccessControl::can('surat.masuk','create');
$canUpdate = AccessControl::can('surat.masuk','update');

// --- mode edit / tambah ---
$isEdit = isset($surat) && !empty($surat);
$isInputMode = !$isEdit;

// --- role & jabatan user ---
$userRole    = strtolower($_SESSION['user']['hak_akses'] ?? '');
$userJabatan = strtolower($_SESSION['user']['nama_jabatan'] ?? '');

$isAdminRole = ($userRole === 'administrator');
$isAdmin2or3 = in_array($userRole, ['admin2','admin3']);
$isDirektur  = ($userJabatan === 'direktur');

// --- status surat ---
$isDiarsipkan = ($isEdit && !empty($surat['status_surat_id']) && $surat['status_surat_id'] == 4);

// --- flag akses ---
$canEditAll           = false;
$canEditGeneralOnly   = false;
$canEditDisposisiOnly = false;
$viewOnly             = false;
$showSimpan           = false;
$showKirim            = false;

// logika akses
if ($isDiarsipkan) {
    $viewOnly = true;
} else {
    if ($isAdminRole) {
        $canEditAll = true;
        $showSimpan = true;
    } elseif ($isAdmin2or3) {
        $canEditGeneralOnly = true;
        $showKirim = true;
    } elseif ($isDirektur && $isEdit) {
        $canEditDisposisiOnly = true;
        $showKirim = true;
    } else {
        // jabatan unit (kabid, karu, kaunit, koord) → readonly
        $viewOnly = true;
    }
}

// --- helper atribut ---
$readonlyGeneral = ($viewOnly || $canEditDisposisiOnly);
$disabledSelects = $readonlyGeneral;

// --- form action & title ---
$actionUrl = $isEdit ? "index.php?url=suratmasuk/update" : "index.php?url=suratmasuk/store";
$title     = $isEdit ? "✍️ Edit Surat Masuk" : "✍️ Tambah Surat Masuk";
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2">
      <h5 class="mb-0"><?= $title ?></h5>
    </div>
    <form method="POST" action="<?= $actionUrl ?>" enctype="multipart/form-data">
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($surat['id']) ?>">
        <input type="hidden" name="nomor_agenda" value="<?= htmlspecialchars($surat['nomor_agenda']) ?>">
        <input type="hidden" name="berkas_surat_lama" value="<?= htmlspecialchars($surat['berkas_surat']) ?>">
      <?php endif; ?>

      <div class="card-body">
        <div class="row g-3">
          <!-- Kolom Kiri -->
          <div class="col-md-6">
            <!-- Nomor Surat -->
            <div class="mb-3">
              <label class="form-label">Nomor Surat</label>
              <input type="text" name="nomor_surat" class="form-control"
                     value="<?= $isEdit ? htmlspecialchars($surat['nomor_surat']) : '' ?>"
                     <?= $readonlyGeneral ? 'readonly' : '' ?>>
            </div>

            <!-- Tanggal Surat -->
            <div class="mb-3">
              <label class="form-label">Tanggal Surat</label>
              <input type="date" name="tanggal_surat" class="form-control" required
                value="<?= ($isEdit && !empty($surat['tanggal_surat'])) ? date('Y-m-d', strtotime($surat['tanggal_surat'])) : '' ?>"
                <?= $readonlyGeneral ? 'readonly' : '' ?>>
            </div>

            <!-- Tanggal Terima -->
            <div class="mb-3">
              <label class="form-label">Tanggal Terima</label>
              <input type="date" name="tanggal_terima" class="form-control" required
                value="<?= ($isEdit && !empty($surat['tanggal_terima'])) ? date('Y-m-d', strtotime($surat['tanggal_terima'])) : '' ?>"
                <?= $readonlyGeneral ? 'readonly' : '' ?>>
            </div>

            <!-- Asal Surat -->
            <div class="mb-3">
              <label class="form-label">Asal Surat</label>
              <input type="text" name="asal_surat" class="form-control" required
                value="<?= $isEdit ? htmlspecialchars($surat['asal_surat']) : '' ?>"
                <?= $readonlyGeneral ? 'readonly' : '' ?>>
            </div>

            <!-- Perihal Surat -->
            <div class="mb-3">
              <label class="form-label">Perihal Surat</label>
              <input type="text" name="perihal_surat" class="form-control" required
                value="<?= $isEdit ? htmlspecialchars($surat['perihal_surat']) : '' ?>"
                <?= $readonlyGeneral ? 'readonly' : '' ?>>
            </div>

            <!-- Sifat Surat -->
            <div class="mb-3">
              <label class="form-label">Sifat Surat</label>
              <select name="sifat_surat_id" class="form-control" required
                <?= $disabledSelects ? 'disabled' : '' ?>>
                <option value="">-- Pilih --</option>
                <?php foreach ($sifat_surat as $s): ?>
                  <option value="<?= $s['id'] ?>"
                    <?= $isEdit && $s['id']==($surat['sifat_surat_id'] ?? null) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nama_sifat']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Jumlah Halaman -->
            <div class="mb-3">
              <label class="form-label">Jumlah Halaman</label>
              <input type="text" name="jumlah_halaman" class="form-control"
                value="<?= $isEdit ? htmlspecialchars($surat['jumlah_halaman']) : '' ?>"
                placeholder="contoh: 3 lembar / 1 bendel"
                <?= $readonlyGeneral ? 'readonly' : '' ?>>
            </div>
          </div>

          <!-- Kolom Kanan -->
          <div class="col-md-6">
            <!-- Diteruskan Kepada -->
            <div class="mb-3">
              <label class="form-label">Diteruskan Kepada</label>
              <?php
              $direkturId = null;
              foreach ($jabatan_unit as $j) {
                if (strtolower($j['jabatan_keterangan']) === 'direktur') {
                  $direkturId = $j['id'];
                  break;
                }
              }
              ?>
              <select name="diteruskan_kepada_jabatan_id" class="form-control" <?= !$isAdminRole ? 'disabled' : '' ?>>
                <option value="<?= $direkturId ?>" selected>Direktur</option>
              </select>
              <?php if (!$isAdminRole): ?>
                <input type="hidden" name="diteruskan_kepada_jabatan_id" value="<?= $direkturId ?>">
              <?php endif; ?>
            </div>

            <!-- Isi Disposisi -->
            <div class="mb-3">
              <label class="form-label">Isi Disposisi</label>
              <textarea name="isi_disposisi" class="form-control" rows="3"
                        <?= $canEditDisposisiOnly ? '' : 'readonly' ?>><?= $isEdit && !empty($surat['isi_disposisi']) ? htmlspecialchars($surat['isi_disposisi']) : '' ?></textarea>
            </div>

            <!-- Disposisi Kepada -->
            <div class="mb-3">
              <label class="form-label">Disposisi Kepada</label>
              <?php
              $excludedJabatan = ['direktur', 'administrator', 'karyawan'];
              $excludedUnit    = ['it departemen', 'tidak ada unit kerja'];
              $jabatanTerpilih = explode(',', $surat['disposisi_jabatan_id'] ?? '');
              foreach ($jabatan_unit as $j):
                $jabatanOk = !in_array(strtolower($j['jabatan_keterangan'] ?? ''), $excludedJabatan);
                $unitOk    = !in_array(strtolower($j['nama_unit'] ?? ''), $excludedUnit);
                $hasUnitId = isset($j['unit_id']) && $j['unit_id'] !== '';
                if ($jabatanOk && $unitOk && $hasUnitId):
                  $jabatanId   = htmlspecialchars($j['id']);
                  $unitId      = htmlspecialchars($j['unit_id']);
                  $isChecked   = in_array($j['id'], $jabatanTerpilih);
                  $jabatanText = htmlspecialchars($j['jabatan_keterangan']);
                  $unitText    = htmlspecialchars($j['nama_unit']);
              ?>
                <div class="form-check mb-1">
                  <input class="form-check-input" type="checkbox"
                         name="disposisi_gabungan[]" value="<?= $jabatanId . ':' . $unitId ?>"
                         id="jabatan<?= $jabatanId ?>"
                         <?= $isChecked ? 'checked' : '' ?>
                         <?= $canEditDisposisiOnly ? '' : 'disabled' ?>>
                  <label class="form-check-label" for="jabatan<?= $jabatanId ?>">
                    <?= $jabatanText ?><?= !empty($unitText) ? ' - ' . $unitText : '' ?>
                  </label>
                </div>
              <?php
                endif;
              endforeach;
              ?>
            </div>

            <!-- Berkas Surat -->
            <div class="mb-3">
              <label class="form-label">Berkas Surat (PDF)</label>
              <?php if ($viewOnly): ?>
                <?php if (!empty($surat['berkas_surat'])): ?>
                  <a href="public/uploads/dokumen/surat_masuk/<?= date('Y', strtotime($surat['tanggal_surat'])) ?>/<?= htmlspecialchars($surat['berkas_surat']) ?>" target="_blank"><?= htmlspecialchars($surat['berkas_surat']) ?></a>
                  <input type="hidden" name="berkas_surat_lama" value="<?= htmlspecialchars($surat['berkas_surat']) ?>">
                <?php else: ?>
                  <span class="text-muted">Tidak ada berkas</span>
                <?php endif; ?>
              <?php else: ?>
                <input type="file" name="berkas_surat" class="form-control" accept=".pdf" <?= !$isEdit ? 'required' : '' ?>>
                <?php if ($isEdit && !empty($surat['berkas_surat'])): ?>
                <small class="text-muted">
                    File lama:
                    <a href="public/uploads/dokumen/surat_masuk/<?= date('Y', strtotime($surat['tanggal_surat'])) ?>/<?= urlencode($surat['berkas_surat']) ?>" target="_blank">
                        <?= htmlspecialchars($surat['berkas_surat']) ?>
                    </a>
                </small><br>
                  <small class="text-muted">Jika tidak upload file baru, sistem akan tetap memakai file lama.</small><br>
                <?php endif; ?>
                <small class="text-muted">Nama file otomatis: sm_[nomor urut].pdf</small>
              <?php endif; ?>
            </div>

            <!-- Status Surat -->
            <div class="mb-3">
              <label class="form-label">Status Surat</label>
              <select name="status_surat_id" id="status_id" class="form-control"
                      <?= $viewOnly ? 'disabled' : '' ?>>
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
          </div> <!-- end col-md-6 -->
        </div> <!-- end row -->
      </div> <!-- end card-body -->

      <!-- Footer tombol -->
      <div class="card-footer text-end">
        <a href="index.php?url=suratmasuk/index" class="btn btn-secondary">Kembali</a>
        <?php
        if ($isAdminRole && !$isDiarsipkan) {
            // Administrator → Simpan / Update jika belum diarsipkan
            echo '<button type="submit" class="btn btn-primary">'.($isEdit ? 'Update' : 'Simpan').'</button>';
        } elseif (($isAdmin2or3 || $isDirektur) && !$isDiarsipkan) {
            // Admin2/Admin3/Direktur → tombol Kirim
            echo '<button type="submit" class="btn btn-primary">Kirim</button>';
        }
        // Jabatan unit / surat diarsipkan → tidak ada tombol simpan
        ?>
      </div>

    </form>
  </div>
</div>