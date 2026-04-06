<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

$nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? '';
$isOwner    = ($nipSession === ($pegawai['nomor_induk_pegawai'] ?? ''));

// cek izin akses edit data saya
if (!AccessControl::can('profile.edit_data_saya','update-own') || !$isOwner) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit data ini.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white">
      <h5>Edit Data Saya</h5>
    </div>
    <div class="card-body">
      <form method="post" action="<?= BASE_URL ?>/index.php?url=kepegawaian/update_data_saya" enctype="multipart/form-data">
        <input type="hidden" name="nomor_induk_pegawai" value="<?= htmlspecialchars($pegawai['nomor_induk_pegawai'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <!-- NIK & NIP -->
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Nomor Induk Kependudukan</label>
              <input type="text" name="nomor_induk_kependudukan" maxlength="16" class="form-control"
                     value="<?= htmlspecialchars($pegawai['nomor_induk_kependudukan'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Nomor Induk Pegawai</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($pegawai['nomor_induk_pegawai'] ?? '', ENT_QUOTES, 'UTF-8') ?>" disabled>
            </div>
          </div>
        </div>

        <!-- Nama Lengkap & Tempat Lahir -->
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Nama Lengkap</label>
              <input type="text" name="nama_lengkap" class="form-control"
                     value="<?= htmlspecialchars($pegawai['nama_lengkap'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Tempat Lahir</label>
              <input type="text" name="tempat_lahir" class="form-control"
                     value="<?= htmlspecialchars($pegawai['tempat_lahir'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
        </div>

        <!-- Tanggal Lahir & Jenis Kelamin -->
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Tanggal Lahir</label>
              <input type="date" name="tanggal_lahir" class="form-control"
                     value="<?= !empty($pegawai['tanggal_lahir']) ? date('Y-m-d', strtotime($pegawai['tanggal_lahir'])) : '' ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Jenis Kelamin</label>
              <select name="jenis_kelamin_id" class="form-control">
                <option value="">-- Pilih --</option>
                <?php foreach ($jenis_kelamin as $jk): ?>
                  <option value="<?= htmlspecialchars($jk['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pegawai['jenis_kelamin_id'] ?? '') == $jk['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($jk['jenis'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- Alamat & Email -->
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Alamat Tempat Tinggal</label>
              <textarea name="alamat" class="form-control"><?= htmlspecialchars($pegawai['alamat_tempat_tinggal'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" class="form-control"
                     value="<?= htmlspecialchars($pegawai['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
        </div>

        <!-- Telepon & Status Pernikahan -->
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Nomor Telepon</label>
              <input type="text" name="telepon" class="form-control"
                     value="<?= htmlspecialchars($pegawai['nomor_telepon'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Status Pernikahan</label>
              <select name="status_pernikahan_id" class="form-control">
                <option value="">-- Pilih --</option>
                <?php foreach ($status_pernikahan as $sp): ?>
                  <option value="<?= htmlspecialchars($sp['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pegawai['status_pernikahan_id'] ?? '') == $sp['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sp['status'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- Jumlah Anak & Nama Bank -->
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Jumlah Anak</label>
              <input type="number" name="jumlah_anak" class="form-control"
                     value="<?= htmlspecialchars($pegawai['jumlah_anak'] ?? 0, ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Nama Bank</label>
              <select name="nama_bank" class="form-control" required>
                <option value="">-- Pilih Bank --</option>
                <option value="Bank Mandiri" <?= ($pegawai['nama_bank'] ?? '')=='Bank Mandiri'?'selected':'' ?>>Bank Mandiri</option>
                <option value="BRI" <?= ($pegawai['nama_bank'] ?? '')=='BRI'?'selected':'' ?>>Bank Rakyat Indonesia (BRI)</option>
                <option value="BNI" <?= ($pegawai['nama_bank'] ?? '')=='BNI'?'selected':'' ?>>Bank Negara Indonesia (BNI)</option>
                <option value="BTN" <?= ($pegawai['nama_bank'] ?? '')=='BTN'?'selected':'' ?>>Bank Tabungan Negara (BTN)</option>
                <option value="BCA" <?= ($pegawai['nama_bank'] ?? '')=='BCA'?'selected':'' ?>>Bank Central Asia (BCA)</option>
                <option value="CIMB Niaga" <?= ($pegawai['nama_bank'] ?? '')=='CIMB Niaga'?'selected':'' ?>>CIMB Niaga</option>
                <option value="Danamon" <?= ($pegawai['nama_bank'] ?? '')=='Danamon'?'selected':'' ?>>Bank Danamon</option>
                <option value="Permata" <?= ($pegawai['nama_bank'] ?? '')=='Permata'?'selected':'' ?>>Bank Permata</option>
                <option value="Mega" <?= ($pegawai['nama_bank'] ?? '')=='Mega'?'selected':'' ?>>Bank Mega</option>
                <option value="Panin" <?= ($pegawai['nama_bank'] ?? '')=='Panin'?'selected':'' ?>>Bank Panin</option>
                <option value="Bukopin" <?= ($pegawai['nama_bank'] ?? '')=='Bukopin'?'selected':'' ?>>Bank Bukopin</option>
                <option value="Maybank" <?= ($pegawai['nama_bank'] ?? '')=='Maybank'?'selected':'' ?>>Maybank Indonesia</option>
                <option value="OCBC NISP" <?= ($pegawai['nama_bank'] ?? '')=='OCBC NISP'?'selected':'' ?>>Bank OCBC NISP</option>
                <option value="Sinarmas" <?= ($pegawai['nama_bank'] ?? '')=='Sinarmas'?'selected':'' ?>>Bank Sinarmas</option>
                <option value="UOB" <?= ($pegawai['nama_bank'] ?? '')=='UOB'?'selected':'' ?>>Bank UOB Indonesia</option>
                <option value="Commonwealth" <?= ($pegawai['nama_bank'] ?? '')=='Commonwealth'?'selected':'' ?>>Bank Commonwealth</option>
                <option value="BSI" <?= ($pegawai['nama_bank'] ?? '')=='BSI'?'selected':'' ?>>Bank Syariah Indonesia (BSI)</option>
                <option value="BCA Syariah" <?= ($pegawai['nama_bank'] ?? '')=='BCA Syariah'?'selected':'' ?>>BCA Syariah</option>
                <option value="Muamalat" <?= ($pegawai['nama_bank'] ?? '')=='Muamalat'?'selected':'' ?>>Bank Muamalat Indonesia</option>
                <option value="Mega Syariah" <?= ($pegawai['nama_bank'] ?? '')=='Mega Syariah'?'selected':'' ?>>Bank Mega Syariah</option>
                <option value="Panin Dubai Syariah" <?= ($pegawai['nama_bank'] ?? '')=='Panin Dubai Syariah'?'selected':'' ?>>Panin Dubai Syariah</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Nomor Rekening & Unit Kerja -->
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Nomor Rekening</label>
              <input type="text" name="nomor_rekening" class="form-control"
                     value="<?= htmlspecialchars($pegawai['nomor_rekening'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Unit Kerja</label>
              <select name="unit_id" class="form-control">
                <option value="">-- Pilih Unit Kerja --</option>
                <?php foreach ($unit_kerja as $uk): ?>
                  <?php if (strtolower($uk['nama_unit']) !== 'it departemen'): ?>
                    <option value="<?= htmlspecialchars($uk['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pegawai['unit_id'] ?? '') == $uk['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($uk['nama_unit'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- Pendidikan & Pendidikan Terakhir -->
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Pendidikan</label>
              <select name="pendidikan_id" class="form-control">
                <option value="">-- Pilih Pendidikan --</option>
                <?php foreach ($pendidikan as $pd): ?>
                  <option value="<?= htmlspecialchars($pd['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pegawai['pendidikan_id'] ?? '') == $pd['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($pd['jenjang'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Institusi Pendidikan</label>
              <input type="text" name="pendidikan_terakhir" class="form-control"
                     value="<?= htmlspecialchars($pegawai['pendidikan_terakhir'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
        </div>

        <!-- Tanggal Masuk -->
        <div class="form-group">
          <label>Tanggal Masuk</label>
          <input type="date" name="tanggal_masuk" class="form-control"
                 value="<?= !empty($pegawai['tanggal_masuk']) ? date('Y-m-d', strtotime($pegawai['tanggal_masuk'])) : '' ?>">
        </div>

        <!-- Foto Pegawai -->
        <div class="form-group">
          <?= renderPreviewLink('Foto Pegawai', $pegawai['photo'] ?? '') ?>
          <input type="hidden" name="photo" value="<?= htmlspecialchars($pegawai['photo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <input type="file" name="photo" accept=".jpg,.jpeg,.png" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary mt-3">Simpan Perubahan</button>
      </form>
    </div>
  </div>
</div>

<script>
function toggleUnitKerjaInput(val) {
  const inputDiv = document.getElementById('unitKerjaInput');
  if (inputDiv) {
    inputDiv.style.display = (val === 'new') ? '' : 'none';
  }
}
document.addEventListener('DOMContentLoaded', function() {
  const select = document.querySelector('[name="unit_id"]');
  if (select) toggleUnitKerjaInput(select.value);
});
</script>

