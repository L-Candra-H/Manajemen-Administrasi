<?php
$url = $_GET['url'] ?? 'dashboard';
$parts = explode('/', $url);
$currentKey   = strtolower($parts[0]);
$currentSub   = strtolower($parts[1] ?? '');

// Mapping menu → label rapi
$menuMap = [
    'dashboard'                     => 'Dashboard',

    'admin/hak_akses'               => 'Pengaturan Sistem - Hak Akses',
    'institusi/index'               => 'Pengaturan Sistem - Institusi',
    'master/institusi'              => 'Pengaturan Sistem - Edit Institusi',

    'master/jenis_kelamin'          => 'Master Data - Jenis Kelamin',
    'master/agama'                  => 'Master Data - Agama',
    'master/status_pernikahan'      => 'Master Data - Status Pernikahan',
    'master/status_kepegawaian'     => 'Master Data - Status Kepegawaian',
    'master/golongan_pegawai'       => 'Master Data - Golongan Pegawai',
    'master/kategori_kepegawaian'   => 'Master Data - Kategori Kepegawaian',
    'master/jabatan'                => 'Master Data - Jabatan',
    'master/unit_kerja'             => 'Master Data - Unit Kerja',
    'master/lama_kerja'             => 'Master Data - Lama Kerja',
    'master/pendidikan'             => 'Master Data - Pendidikan',
    'master/sifat_surat_masuk'      => 'Master Data - Sifat Surat Masuk',
    'master/status_surat'           => 'Master Data - Status Surat',
    'master/jenis_surat_keluar'     => 'Master Data - Jenis Surat Keluar',

    'kepegawaian/daftar'            => 'Manajemen Kepegawaian - Daftar Pegawai Aktif',
    'kepegawaian/input'             => 'Manajemen Kepegawaian - Tambah dan Edit Pegawai',
    'kepegawaian/nonaktif'          => 'Manajemen Kepegawaian - Daftar Pegawai Non Aktif',
    'kepegawaian/daftar_qr_pegawai' => 'Manajemen Kepegawaian - Daftar QR Pegawai',
    'kepegawaian/str'               => 'Manajemen Kepegawaian - Daftar Surat Tanda Registrasi',
    'kepegawaian/sip'               => 'Manajemen Kepegawaian - Daftar Surat Ijin Pegawai',
    'kepegawaian/sip_tambah'        => 'Manajemen Kepegawaian - Tambah Surat Ijin Pegawai',
    'kepegawaian/sip_edit'          => 'Manajemen Kepegawaian - Edit Surat Ijin Pegawai',
    'kepegawaian/sk'                => 'Manajemen Kepegawaian - Daftar Surat Keputusan',
    'kepegawaian/sk_tambah'         => 'Manajemen Kepegawaian - Tambah Surat Keputusan',
    'kepegawaian/sk_edit'           => 'Manajemen Kepegawaian - Edit Surat Keputusan',
    'kepegawaian/sertifikat'        => 'Manajemen Kepegawaian - Daftar Sertifikat Pegawai',
    'kepegawaian/sertifikat_tambah' => 'Manajemen Kepegawaian - Tambah Sertifikat Pegawai',
    'kepegawaian/sertifikat_edit'   => 'Manajemen Kepegawaian - Edit Sertifikat Pegawai',
    'kepegawaian/bpjs'              => 'Manajemen Kepegawaian - Daftar Kepesertaan BPJS',
    'cuti/index'                    => 'Manajemen Kepegawaian - Daftar Cuti Pegawai',
    'cuti/create'                   => 'Manajemen Kepegawaian - Tambah Cuti Pegawai',
    'cuti/edit'                     => 'Manajemen Kepegawaian - Edit Cuti Pegawai',
    'kepegawaian/grafik'            => 'Manajemen Kepegawaian - Grafik Kepegawaian',

    'suratkeluar'                   => 'Manajemen Surat - Surat Keluar',
    'suratkeluar/index'             => 'Manajemen Surat - Surat Keluar',
    'suratkeluar/detail'            => 'Manajemen Surat - Lihat Surat Keluar',
    'suratkeluar/create'            => 'Manajemen Surat - Tambah Surat Keluar',
    'suratkeluar/edit'              => 'Manajemen Surat - Edit Surat Keluar',
    'suratmasuk'                    => 'Manajemen Surat - Surat Masuk',
    'suratmasuk/index'              => 'Manajemen Surat - Surat Masuk',
    'suratmasuk/detail'             => 'Manajemen Surat - Lihat Surat Masuk',
    'suratmasuk/create'             => 'Manajemen Surat - Tambah Surat Masuk',
    'suratmasuk/edit'               => 'Manajemen Surat - Edit Surat Masuk',
    'suratinternal'                 => 'Manajemen Surat - Surat Internal',
    'suratinternal/index'           => 'Manajemen Surat - Surat Internal',
    'suratinternal/detail'          => 'Manajemen Surat - Lihat Surat Internal',
    'suratinternal/create'          => 'Manajemen Surat - Tambah Surat Internal',
    'suratinternal/edit'            => 'Manajemen Surat - Edit Surat Internal',

    'remunerasi/master_index'       => 'Manajemen Remunerasi - Index Remunerasi',
    'remunerasi/tiket_list'         => 'Manajemen Remunerasi - Tiket Remunerasi',
    'remunerasi/generate'           => 'Manajemen Remunerasi - Hitung Remunerasi',
    'remunerasi/index'              => 'Manajemen Remunerasi - Data Remunerasi',
    'remunerasi/riwayat'            => 'Manajemen Remunerasi - Riwayat Remunerasi',
    'remunerasi/cetak'              => 'Manajemen Remunerasi - Cetak Data Remunerasi',

    'kepegawaian/edit_data_saya'    => 'Profil Saya',

    'about'                         => 'Tentang Aplikasi',
];

// Gabungkan key utama + sub
$key = $currentKey . ($currentSub ? '/' . $currentSub : '');

// Tentukan judul
$currentPage = $menuMap[$key] ?? ucfirst($currentKey);
?>

<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <span class="nav-link" style="font-weight: bold; color: #007bff; font-size: 1.1rem;">
        <?= $currentPage ?>
      </span>
    </li>
  </ul>

  <!-- Right navbar -->
  <ul class="navbar-nav ml-auto">
    <!-- Nama petugas aktif -->
    <li class="nav-item d-flex align-items-center">
      <span class="nav-link">
        <i class="fas fa-user"></i>
        <?= $_SESSION['user']['nama_lengkap'] ?? 'Petugas' ?>
      </span>
    </li>
    <!-- Tombol Logout -->
    <li class="nav-item">
      <a href="<?= BASE_URL ?>/index.php?url=auth/logout" class="nav-link">Logout</a>
    </li>
  </ul>
</nav>