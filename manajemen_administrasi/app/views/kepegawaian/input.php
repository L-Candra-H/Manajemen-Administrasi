<?php
require_once __DIR__ . '/../../helpers/PreviewHelper.php';
require_once __DIR__ . '/../../helpers/AccessControl.php';

$nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? '';
$isOwner  = ($nipSession === ($pegawai['nomor_induk_pegawai'] ?? null));

if ($isEdit) {
  if (!AccessControl::can('pegawai.daftar_aktif','update') &&
      !(AccessControl::can('pegawai.daftar_aktif','update-own') && $isOwner)) {
    echo "<div class='alert alert-danger'>Akses ditolak. Anda hanya dapat mengedit data Anda sendiri.</div>";
    exit;
  }
} else {
  if (!AccessControl::can('pegawai.daftar_aktif','create')) {
    echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat menambah data pegawai baru.</div>";
    exit;
  }
}
?>

<form method="POST" enctype="multipart/form-data" action="<?= BASE_URL ?>/index.php?url=kepegawaian/savePegawai">
  <input type="hidden" name="mode" value="<?= $isEdit ? 'edit' : 'add' ?>">

  <!-- ================= DATA INDUK ================= -->
  <div class="card border-info mb-4">
    <div class="card-header bg-info text-white">DATA INDUK</div>
    <div class="card-body">
      <div class="form-group">
        <label>Nomor Induk Kependudukan</label>
        <input type="text" id="nomor_induk_kependudukan" name="nomor_induk_kependudukan" maxlength="16" class="form-control" required value="<?= htmlspecialchars($pegawai['nomor_induk_kependudukan'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="form-group">
        <label>Nomor Induk Pegawai</label>
        <input type="text" id="nomor_induk_pegawai" name="nomor_induk_pegawai" maxlength="6" class="form-control" required value="<?= htmlspecialchars($pegawai['nomor_induk_pegawai'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="nama_lengkap" class="form-control" required value="<?= htmlspecialchars($pegawai['nama_lengkap'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="form-row">
        <div class="col">
          <label>Tempat Lahir</label>
          <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($pegawai['tempat_lahir'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col">
          <label>Tanggal Lahir</label>
          <input type="date" name="tanggal_lahir" class="form-control" value="<?= isset($pegawai['tanggal_lahir']) ? date('Y-m-d', strtotime($pegawai['tanggal_lahir'])) : '' ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="col">
          <label>Jenis Kelamin</label>
          <select name="jenis_kelamin_id" class="form-control">
            <option value="">-- Pilih --</option>
            <?php foreach ($jenis_kelamin as $jk): ?>
              <option value="<?= htmlspecialchars($jk['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pegawai['jenis_kelamin_id'] ?? '') == $jk['id'] ? 'selected' : '' ?>><?= htmlspecialchars($jk['jenis'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col">
          <label>Agama</label>
          <select name="agama_id" class="form-control">
            <option value="">-- Pilih --</option>
            <?php foreach ($agama as $a): ?>
              <option value="<?= htmlspecialchars($a['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pegawai['agama_id'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['nama_agama'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Alamat Tempat Tinggal</label>
        <textarea name="alamat_tempat_tinggal" class="form-control"><?= htmlspecialchars($pegawai['alamat_tempat_tinggal'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div class="form-row">
        <div class="col">
          <label>Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($pegawai['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col">
          <label>Nomor Telepon</label>
          <input type="text" name="nomor_telepon" class="form-control" value="<?= htmlspecialchars($pegawai['nomor_telepon'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="col">
          <label>Status Pernikahan</label>
          <select name="status_pernikahan_id" class="form-control" id="statusPernikahan">
            <option value="">-- Pilih --</option>
            <?php foreach ($status_pernikahan as $sp): ?>
              <option value="<?= htmlspecialchars($sp['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pegawai['status_pernikahan_id'] ?? '') == $sp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sp['status'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col">
          <label>Jumlah Anak</label>
          <input type="number" name="jumlah_anak" class="form-control" id="jumlahAnak" value="<?= htmlspecialchars($pegawai['jumlah_anak'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="col">
          <label>Pendidikan Terakhir</label>
          <select name="pendidikan_id" class="form-control" required>
            <option value="">-- Pilih Pendidikan --</option>
            <?php foreach ($pendidikan as $pd): ?>
              <option value="<?= htmlspecialchars($pd['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pegawai['pendidikan_id'] ?? '') == $pd['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($pd['jenjang'], ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col">
          <label>Institusi Pendidikan</label>
          <input type="text" name="pendidikan_terakhir" class="form-control"
                 value="<?= htmlspecialchars($pegawai['pendidikan_terakhir'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                 placeholder="Isi nama institusi pendidikan">
        </div>
      </div>
      <div class="form-group">
        <label>Berkas Ijazah Terakhir (PDF)</label>
        <?= renderPreviewLink('Ijazah Terakhir', $pegawai['berkas_ijazah_terakhir'] ?? '') ?>
        <input type="file" name="berkas_ijazah_terakhir" accept=".pdf" class="form-control">
      </div>
      <div class="form-row">
        <div class="col">
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

        <div class="col">
          <label>Nomor Rekening Bank</label>
                <input type="text" name="nomor_rekening" class="form-control"
                 value="<?= htmlspecialchars($pegawai['nomor_rekening'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>

    <div class="form-group">
      <label>Unit Kerja</label>
      <div class="input-group">
        <?php
        $role = strtolower($_SESSION['user']['hak_akses'] ?? '');
        $isDirektur = ($role === 'direktur');
        ?>
        <select name="unit_id" id="unitKerjaSelect" class="form-control" required>
          <option value="">-- Pilih Unit Kerja --</option>
          <?php foreach ($unit_kerja as $uk): ?>
              <?php if (strtolower($uk['nama_unit']) === 'it departemen') continue; ?>
              <option value="<?= htmlspecialchars($uk['id'], ENT_QUOTES, 'UTF-8') ?>"
                <?= ($pegawai['unit_id'] ?? '') == $uk['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($uk['nama_unit'], ENT_QUOTES, 'UTF-8') ?>
              </option>
          <?php endforeach; ?>
        </select>
        <div class="input-group-append">
          <button type="button" class="btn btn-outline-primary" onclick="tambahUnitKerjaInline()">
            Tambah
          </button>
        </div>
      </div>

      <!-- field inline muncul hanya saat tombol tambah diklik -->
      <div id="unitKerjaInline" style="margin-top:10px; display:none;">
        <input type="text" id="namaUnitKerjaBaru" class="form-control mb-2" placeholder="Nama Unit Kerja Baru">
        <button type="button" class="btn btn-success btn-sm" onclick="simpanUnitKerjaBaru()">Simpan</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="batalUnitKerjaInline()">Batal</button>
      </div>
    </div>

    <div class="form-group">
      <label>Tanggal Masuk</label>
      <input type="date" name="tanggal_masuk" class="form-control"
             value="<?= !empty($pegawai['tanggal_masuk']) 
                        ? date('Y-m-d', strtotime($pegawai['tanggal_masuk'])) 
                        : '' ?>"
             placeholder="-- Pilih Tanggal --">
    </div>

    <div class="form-group">
      <label>Photo</label>
      <?= renderPreviewLink('Foto Pegawai', $pegawai['photo'] ?? '') ?>
      <input type="file" name="photo" accept=".jpg,.jpeg,.png" class="form-control">
    </div>
  </div>
</div>

<!-- ================= KELENGKAPAN DATA KEPEGAWAIAN ================= -->
<div class="card border-success mb-4">
  <div class="card-header bg-success text-white">KELENGKAPAN DATA KEPEGAWAIAN</div>
  <div class="card-body">
    <div class="form-row">
      <div class="col">
        <label>Status Kepegawaian</label>
        <select name="status_kepegawaian_id" class="form-control" id="statusKepegawaian">
          <option value="">-- Pilih --</option>
          <?php foreach ($status_kepegawaian as $sk): ?>
            <option value="<?= htmlspecialchars($sk['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pegawai['status_kepegawaian_id'] ?? '') == $sk['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sk['status'], ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col">
        <label>Golongan Pegawai</label>
        <select name="golongan_pegawai_id" class="form-control" id="golonganPegawai" disabled>
          <option value="">-- Pilih --</option>
          <?php foreach ($golongan_pegawai as $gp): ?>
            <option value="<?= htmlspecialchars($gp['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pegawai['golongan_pegawai_id'] ?? '') == $gp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($gp['golongan'], ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label>Kategori Kepegawaian</label>
      <select name="kategori_kepegawaian_id" class="form-control" id="kategoriKepegawaian">
        <option value="">-- Pilih --</option>
        <?php foreach ($kategori_kepegawaian as $kk): ?>
          <option value="<?= htmlspecialchars($kk['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($pegawai['kategori_kepegawaian_id'] ?? '') == $kk['id'] ? 'selected' : '' ?>><?= htmlspecialchars($kk['kategori'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-row">
      <div class="col">
        <label>Nomor STR</label>
        <input type="text" name="nomor_STR" class="form-control" id="nomorSTR" value="<?= htmlspecialchars($pegawai['nomor_STR'] ?? '', ENT_QUOTES, 'UTF-8') ?>" disabled>
      </div>
      <div class="col">
        <label>Berkas STR (PDF)</label>
        <?= renderPreviewLink('File STR', $pegawai['berkas_STR'] ?? '') ?>
        <input type="file" name="berkas_STR" accept=".pdf" class="form-control" id="berkasSTR" disabled>
      </div>
    </div>
  </div>
</div>

<!-- ================= KEPESERTAAN BPJS ================= -->
<div class="card border-warning mb-4">
  <div class="card-header bg-warning text-dark">KEPESERTAAN BPJS</div>
  <div class="card-body">
    <?php include 'bpjs_form.php'; ?>
  </div>
</div>

<!-- ================= TOMBOL SIMPAN ================= -->
<div class="text-right">
  <button type="submit" class="btn btn-primary">
    <?= $isEdit ? 'Update Data' : 'Simpan Data' ?>
  </button>
  <a href="index.php?url=kepegawaian/daftar" class="btn btn-secondary">
    Batal
  </a>
</div>

</form>

<script>
function tambahUnitKerjaInline() {
  document.getElementById('unitKerjaInline').style.display = 'block';
}

function batalUnitKerjaInline() {
  document.getElementById('unitKerjaInline').style.display = 'none';
  document.getElementById('namaUnitKerjaBaru').value = '';
}

function simpanUnitKerjaBaru() {
  const nama = document.getElementById('namaUnitKerjaBaru').value.trim();
  if (!nama) return alert('Isi nama unit kerja baru');

  fetch('index.php?url=kepegawaian/storeUnitKerja', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'nama_unit=' + encodeURIComponent(nama)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      const select = document.getElementById('unitKerjaSelect');
      const opt = document.createElement('option');
      opt.value = data.id;
      opt.textContent = nama;
      select.appendChild(opt);
      select.value = data.id;

      batalUnitKerjaInline();
    } else {
      alert(data.message || 'Gagal menambah unit kerja');
    }
  })
  .catch(err => {
    console.error('Respons bukan JSON:', err);
    alert('Server tidak balas JSON, cek controller.');
  });
}
</script>
