<?php

require_once __DIR__ . '/../../helpers/AccessControl.php';

$isOwner = $_SESSION['user']['nomor_induk_pegawai'] == ($pegawai['nomor_induk_pegawai'] ?? null);

// cek izin akses cetak biodata
if (AccessControl::can('pegawai.daftar_aktif','print')) {
    // Admin/Administrator boleh cetak semua pegawai
} elseif (AccessControl::can('pegawai.daftar_aktif','print-own') && $isOwner) {
    // User boleh cetak biodata miliknya sendiri
} else {
    echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mencetak biodata pegawai ini.</div>";
    exit;
}

// Ambil data dari controller
$institusi = $data['institusi'];
$pegawai   = $data['pegawai'];
$str       = $data['str'];
$sip       = $data['sip'];
$sk        = $data['sk'];
$bpjs      = $data['bpjs'];
$keluarga  = $data['keluarga'];
$tambahan  = $data['tambahan'];

// Relasi master
$jk     = $data['jk'];
$agama  = $data['agama'];
$status = $data['status'];
$stat_kep = $data['stat_kep'];
$gol    = $data['gol'];
$kat    = $data['kat'];
$jbt    = $data['jbt'];

function purl($path){
  return '/manajemen_administrasi/public/uploads/' . $path;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Data Pegawai</title>

<style>
  @page { margin: 15mm; }
  @media print {
    .no-print { display: none !important; }
  }
  body { font-family: Arial, sans-serif; font-size: 12px; }
  .garis-header { border-bottom: 2px solid #000; margin: 5px 0 15px; }
  .judul-biodata { font-size: 18px; font-weight: bold; text-align: center; margin: 10px 0 15px; }
  .subjudul { font-size: 14px; font-weight: bold; margin: 12px 0 8px; }
  .tabel-bordered { border-collapse: collapse; width: 100%; }
  .tabel-bordered th, .tabel-bordered td { border: 1px solid #000; padding: 5px; }
</style>

</head>
<body>

<!-- HEADER -->
<table style="margin-bottom:5px;">
  <tr>
    <!-- Logo kiri -->
    <td style="width:80px; vertical-align:top;">
      <?php if (!empty($institusi['logo'])): ?>
        <img src="<?= purl('institusi_logo/' . $institusi['logo']) ?>" alt="Logo" style="height:60px;">
      <?php endif; ?>
    </td>
    <!-- Teks institusi kanan -->
    <td style="padding-left:10px;">
      <div style="font-size:18px; font-weight:bold; text-transform:uppercase;">
        <?= $institusi['nama_institusi'] ?? '-' ?>
      </div>
      <div style="font-size:16px; font-weight:bold; text-transform:uppercase;">
        <?= $institusi['sub_institusi'] ?? '-' ?>
      </div>
      <div style="font-size:14px;">
        <?= $institusi['alamat'] ?? '-' ?> - <?= $institusi['telepon'] ?? '-' ?>
      </div>
    </td>
  </tr>
</table>

<!-- Garis bawah sepanjang blok logo + teks -->
<div class="garis-header"></div>

<div class="judul-biodata">BIODATA PEGAWAI</div>

<table width="100%" cellpadding="5" cellspacing="0">
  <!-- isi tabel A -->
</table>

<!-- A. DATA INDUK -->
<div class="subjudul">A. DATA INDUK</div>
<table class="data-induk">
  <!-- Baris 1: NIK + Foto + NIP -->
  <tr>
    <td style="width:25%;"><strong>Nomor Induk Kependudukan </strong></td>
    <td style="width:25%;">: <?= $pegawai['nomor_induk_kependudukan'] ?? '-' ?></td>
    <td style="width:25%;"></td>
    <td style="width:25%; text-align:center;" rowspan="6">
      <?php if (!empty($pegawai['photo'])): ?>
        <img src="<?= purl('pegawai/foto/' . $pegawai['photo']) ?>" alt="Foto Pegawai" style="height:100px;"><br>
      <?php else: ?>
        <div class="foto-placeholder">FOTO PEGAWAI</div>
      <?php endif; ?>
      <strong>NIP </strong>: <?= $pegawai['nomor_induk_pegawai'] ?? '-' ?>
    </td>
  </tr>

  <!-- Identitas Utama -->
  <tr><td><strong>Nama Lengkap </strong></td><td>: <?= $pegawai['nama_lengkap'] ?? '-' ?></td><td></td><td></td></tr>
  <tr>
  <td><strong>Tempat / Tanggal Lahir </strong></td>
      <td>: <?= $pegawai['tempat_lahir'] ?? '-' ?> / 
            <?= !empty($pegawai['tanggal_lahir']) ? date('d-m-Y', strtotime($pegawai['tanggal_lahir'])) : '-' ?>
      </td>
      <td></td>
      <td></td>
    </tr>
  <tr><td><strong>Jenis Kelamin </strong></td><td>: <?= $jk['jenis'] ?? '-' ?></td><td></td><td></td></tr>
  <tr><td><strong>Agama </strong></td> <td>: <?= $agama['nama_agama'] ?? '-' ?></td><td></td><td></td></tr>
  <tr><td><strong>Alamat Lengkap </strong></td> <td>: <?= $pegawai['alamat_tempat_tinggal'] ?? '-' ?></td><td></td><td></td></tr>

  <!-- Data Tambahan: KOLOM A/B vs KOLOM C/D -->
  <tr>
    <td><strong>Email </strong></td>
    <td>: <?= $pegawai['email'] ?? '-' ?></td>
    <td><strong>Telepon </strong></td>
    <td>: <?= $pegawai['nomor_telepon'] ?? '-' ?></td>
  </tr>
  <tr>
    <td><strong>Status Pernikahan </strong></td>
    <td>: <?= $status['status'] ?? '-' ?></td>
    <td><strong>Jumlah Anak </strong></td>
    <td>: <?= $pegawai['jumlah_anak'] ?? '-' ?></td>
  </tr>
  <tr>
    <td><strong>Nama Bank </strong></td>
    <td>: <?= $pegawai['nama_bank'] ?? '-' ?></td>
    <td><strong>Nomor Rekening </strong></td>
    <td>: <?= $pegawai['nomor_rekening'] ?? '-' ?></td>
  </tr>

  <!-- Data Pekerjaan -->
  <tr>
    <td><strong>Status / Golongan </strong></td>
    <td colspan="3">: <?= $stat_kep['status'] ?? '-' ?> / <?= $gol['golongan'] ?? '-' ?></td>
  </tr>
  <tr>
    <td><strong>Kategori Kepegawaian </strong></td>
    <td colspan="3">: <?= $kat['kategori'] ?? '-' ?></td>
  </tr>
  <tr>
    <td><strong>Jabatan</strong></td>
    <td>: <?= $jbt['keterangan'] ?? '-' ?></td>
  </tr>
  <tr>
    <td><strong>Unit Kerja </strong></td>
    <td colspan="3">: <?= !empty($pegawai['unit_nama']) ? $pegawai['unit_nama'] : '-' ?></td>
  </tr>
  <tr>
    <td><strong>Pendidikan</strong></td>
    <td colspan="3">: <?= !empty($pegawai['pendidikan_id']) ? ($pendidikan['jenjang'] ?? '-') : '-' ?></td>
  </tr>
  <tr>
    <td><strong>Institusi Pendidikan</strong></td>
    <td colspan="3">: <?= $pegawai['pendidikan_terakhir'] ?? '-' ?></td>
  </tr>
  <tr>
    <td><strong>Tanggal Masuk</strong></td>
    <td colspan="3">: <?= !empty($pegawai['tanggal_masuk']) ? date('d-m-Y', strtotime($pegawai['tanggal_masuk'])) : '-' ?></td>
  </tr>
</table>

<!-- B. KELENGKAPAN BERKAS DATA -->
<div class="subjudul">B. KELENGKAPAN BERKAS DATA</div>
<table class="tabel-bordered" width="100%" cellpadding="5" cellspacing="0">
  <tr><th>NO</th><th>KETERANGAN</th><th>MASA BERLAKU</th><th>BERKAS</th></tr>
  <tr><td>1</td><td>Pendidikan Terakhir : <?= $pegawai['pendidikan_terakhir'] ?></td><td>-</td><td><?= purl($pegawai['berkas_ijazah_terakhir']) ? 'Ada' : 'Tidak Ada' ?></td></tr>
  <tr>
    <td>2</td>
    <td>STR : <?= !empty($str['nomor_STR']) ? $str['nomor_STR'] : '-' ?></td>
    <td>-</td>
    <td><?= !empty($str['berkas_STR']) ? (purl($str['berkas_STR']) ? 'Ada' : 'Tidak Ada') : 'Tidak Ada' ?></td>
  </tr>
  <tr>
      <td>3</td>
      <td>SIP : <?= $sip['nomor_SIP'] ?? '-' ?></td>
      <td>
        <?= !empty($sip['mulai_berlaku_SIP']) ? date('d-m-Y', strtotime($sip['mulai_berlaku_SIP'])) : '-' ?>
        s/d
        <?= !empty($sip['berakhir_SIP']) ? date('d-m-Y', strtotime($sip['berakhir_SIP'])) : '-' ?>
      </td>
      <td><?= !empty($sip['berkas_SIP']) ? (purl($sip['berkas_SIP']) ? 'Ada' : 'Tidak Ada') : 'Tidak Ada' ?></td>
    </tr>
  <tr>
      <td>4</td>
      <td>SK : <?= $sk['nomor_surat_keputusan'] ?? '-' ?></td>
      <td>
        <?= !empty($sk['mulai_berlaku_surat_keputusan']) ? date('d-m-Y', strtotime($sk['mulai_berlaku_surat_keputusan'])) : '-' ?>
        s/d
        <?= !empty($sk['berakhir_surat_keputusan']) ? date('d-m-Y', strtotime($sk['berakhir_surat_keputusan'])) : '-' ?>
      </td>
      <td><?= !empty($sk['berkas_surat_keputusan']) ? (purl($sk['berkas_surat_keputusan']) ? 'Ada' : 'Tidak Ada') : 'Tidak Ada' ?></td>
    </tr>
</table>

<!-- C. KEPESERTAAN BPJS -->
<div class="subjudul">C. KEPESERTAAN BPJS</div>
<table class="tabel-bordered" width="100%" cellpadding="5" cellspacing="0">
  <tr><th>NO</th><th>KETERANGAN</th><th>NAMA PESERTA</th><th>NOMOR KARTU</th></tr>
  <tr>
  <td>1</td>
    <td>BPJS Ketenagakerjaan</td>
    <td><?= !empty($bpjs['nomor_kartu_BPJS_ketenagakerjaan']) ? $pegawai['nama_lengkap'] : '-' ?></td>
    <td><?= !empty($bpjs['nomor_kartu_BPJS_ketenagakerjaan']) ? $bpjs['nomor_kartu_BPJS_ketenagakerjaan'] : '-' ?></td>
  </tr>
  <tr>
    <td>2</td>
    <td>BPJS Kesehatan</td>
    <td><?= !empty($bpjs['nomor_kartu_BPJS_kesehatan']) ? $pegawai['nama_lengkap'] : '-' ?></td>
    <td><?= !empty($bpjs['nomor_kartu_BPJS_kesehatan']) ? $bpjs['nomor_kartu_BPJS_kesehatan'] : '-' ?></td>
  </tr>
  <tr><td rowspan="4">3</td><td rowspan="4">BPJS Kesehatan Keluarga</td><td><?= $keluarga['nama_lengkap_suami_istri'] ?? '-' ?></td><td><?= $keluarga['nomor_kartu_BPJS_kesehatan_suami_istri'] ?? '-' ?></td></tr>
  <tr><td><?= $keluarga['nama_lengkap_anak_I'] ?? '-' ?></td><td><?= $keluarga['nomor_kartu_BPJS_kesehatan_anak_I'] ?? '-' ?></td></tr>
  <tr><td><?= $keluarga['nama_lengkap_anak_II'] ?? '-' ?></td><td><?= $keluarga['nomor_kartu_BPJS_kesehatan_anak_II'] ?? '-' ?></td></tr>
  <tr><td><?= $keluarga['nama_lengkap_anak_III'] ?? '-' ?></td><td><?= $keluarga['nomor_kartu_BPJS_kesehatan_anak_III'] ?? '-' ?></td></tr>
  <tr><td rowspan="4">4</td><td rowspan="4">BPJS Kesehatan Tambahan</td><td><?= $tambahan['nama_lengkap_tambahan_I'] ?? '-' ?></td><td><?= $tambahan['nomor_kartu_BPJS_kesehatan_tambahan_I'] ?? '-' ?></td></tr>
  <tr><td><?= $tambahan['nama_lengkap_tambahan_II'] ?? '-' ?></td><td><?= $tambahan['nomor_kartu_BPJS_kesehatan_tambahan_II'] ?? '-' ?></td></tr>
  <tr><td><?= $tambahan['nama_lengkap_tambahan_III'] ?? '-' ?></td><td><?= $tambahan['nomor_kartu_BPJS_kesehatan_tambahan_III'] ?? '-' ?></td></tr>
  <tr><td><?= $tambahan['nama_lengkap_tambahan_IV'] ?? '-' ?></td><td><?= $tambahan['nomor_kartu_BPJS_kesehatan_tambahan_IV'] ?? '-' ?></td></tr>
</table>

<!-- FOOTER -->
<div class="footer-info d-flex justify-content-between align-items-center p-2" 
     style="border-top: 2px solid #ccc; font-size: 14px; background-color: #f9f9f9;">
  
  <div class="text-left font-weight-bold text-primary">
    ✦ Data ini telah diisi dengan benar dan diteliti kebenarannya ✦
  </div>
  
  <div class="text-right font-weight-bold text-dark">
    <?php
      $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
      $formatter = new IntlDateFormatter(
        'id_ID',
        IntlDateFormatter::FULL,
        IntlDateFormatter::NONE,
        'Asia/Jakarta',
        IntlDateFormatter::GREGORIAN,
        'EEEE, dd-MM-yyyy'
      );
      $tanggal = $formatter->format($now);
      $jam     = $now->format('H:i');
      echo "Data diperbarui terakhir pada <span style='color:#007bff;'>$tanggal $jam WIB</span>";
    ?>
  </div>
</div>

</body>
</html>
