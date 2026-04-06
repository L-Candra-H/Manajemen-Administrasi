<?php
require_once __DIR__ . '/../../helpers/AccessControl.php';
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="#" class="brand-link">
    <span class="brand-text font-weight-light">Manajemen Administrasi</span>
  </a>

  <div class="sidebar">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

        <!-- Dashboard -->
        <?php if (AccessControl::can('dashboard','view')): ?>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?url=dashboard" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <?php endif; ?>

        <!-- Pengaturan Sistem -->
        <?php if (AccessControl::can('pengaturan.institusi','view')): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-cogs"></i>
            <p>Pengaturan Sistem<i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <?php if (AccessControl::can('pengaturan.hak_akses','view')): ?>
            <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=admin/hak_akses" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Hak Akses</p></a></li>
            <?php endif; ?>
            <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=institusi/index" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Institusi</p></a></li>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Master Data -->
        <?php if (
          AccessControl::can('master.jenis_kelamin','view') ||
          AccessControl::can('master.agama','view') ||
          AccessControl::can('master.status_pernikahan','view') ||
          AccessControl::can('master.status_kepegawaian','view') ||
          AccessControl::can('master.golongan_pegawai','view') ||
          AccessControl::can('master.kategori_kepegawaian','view') ||
          AccessControl::can('master.jabatan','view') ||
          AccessControl::can('master.unit_kerja','view') ||
          AccessControl::can('master.lama_kerja','view') ||
          AccessControl::can('master.pendidikan','view') ||
          AccessControl::can('master.sifat_surat_masuk','view') ||
          AccessControl::can('master.status_surat','view') ||
          AccessControl::can('master.jenis_surat_keluar','view')
        ): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-database"></i>
            <p>Master Data<i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">

            <!-- Submenu Kepegawaian -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Kepegawaian<i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                <?php if (AccessControl::can('master.jenis_kelamin','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/jenis_kelamin" class="nav-link"><p>Jenis Kelamin</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('master.agama','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/agama" class="nav-link"><p>Agama</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('master.status_pernikahan','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/status_pernikahan" class="nav-link"><p>Status Pernikahan</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('master.status_kepegawaian','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/status_kepegawaian" class="nav-link"><p>Status Kepegawaian</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('master.golongan_pegawai','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/golongan_pegawai" class="nav-link"><p>Golongan Pegawai</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('master.kategori_kepegawaian','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/kategori_kepegawaian" class="nav-link"><p>Kategori Kepegawaian</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('master.jabatan','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/jabatan" class="nav-link"><p>Jabatan</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('master.unit_kerja','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/unit_kerja" class="nav-link"><p>Unit Kerja</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('master.lama_kerja','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/lama_kerja" class="nav-link"><p>Lama Kerja</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('master.pendidikan','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/pendidikan" class="nav-link"><p>Pendidikan</p></a></li>
                <?php endif; ?>
              </ul>
            </li>

            <!-- Submenu Surat -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Surat<i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                <?php if (AccessControl::can('master.sifat_surat_masuk','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/sifat_surat_masuk" class="nav-link"><p>Sifat Surat Masuk</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('master.status_surat','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/status_surat" class="nav-link"><p>Status Surat</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('master.jenis_surat_keluar','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=master/jenis_surat_keluar" class="nav-link"><p>Jenis Surat Keluar</p></a></li>
                <?php endif; ?>
              </ul>
            </li>

          </ul>
        </li>
        <?php endif; ?>

        <!-- Manajemen Kepegawaian -->
        <?php if (
          AccessControl::can('pegawai.daftar_aktif','view') ||
          AccessControl::can('pegawai.daftar_aktif','view-own') ||
          AccessControl::can('pegawai.daftar_nonaktif','view') ||
          AccessControl::can('pegawai.qrcode','view') ||
          AccessControl::can('pegawai.qrcode','view-own') ||
          AccessControl::can('pegawai.str','view') ||
          AccessControl::can('pegawai.str','view-own') ||
          AccessControl::can('pegawai.sip','view') ||
          AccessControl::can('pegawai.sip','view-own') ||
          AccessControl::can('pegawai.sk','view') ||
          AccessControl::can('pegawai.sk','view-own') ||
          AccessControl::can('pegawai.sertifikat','view') ||
          AccessControl::can('pegawai.sertifikat','view-own') ||
          AccessControl::can('pegawai.bpjs','view') ||
          AccessControl::can('pegawai.bpjs','view-own') ||
          AccessControl::can('pegawai.cuti','view') ||
          AccessControl::can('pegawai.cuti','view-own') ||
          AccessControl::can('pegawai.grafik','view')
        ): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-users"></i>
            <p>Manajemen Kepegawaian<i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">

            <!-- Submenu Daftar Pegawai -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Daftar Pegawai<i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                <?php if (AccessControl::can('pegawai.daftar_aktif','view') || AccessControl::can('pegawai.daftar_aktif','view-own')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=kepegawaian/daftar" class="nav-link"><p>Daftar Pegawai Aktif</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('pegawai.daftar_nonaktif','view')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=kepegawaian/nonaktif" class="nav-link"><p>Daftar Pegawai Non Aktif</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('pegawai.qrcode','view') || AccessControl::can('pegawai.qrcode','view-own')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=kepegawaian/daftar_qr_pegawai" class="nav-link"><p>Daftar QR Pegawai</p></a></li>
                <?php endif; ?>
              </ul>
            </li>

            <!-- Submenu Daftar Kelengkapan Surat -->
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Daftar Kelengkapan Surat<i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                <?php if (AccessControl::can('pegawai.str','view') || AccessControl::can('pegawai.str','view-own')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=kepegawaian/str" class="nav-link"><p>Daftar STR Pegawai</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('pegawai.sip','view') || AccessControl::can('pegawai.sip','view-own')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=kepegawaian/sip" class="nav-link"><p>Daftar SIP Pegawai</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('pegawai.sk','view') || AccessControl::can('pegawai.sk','view-own')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=kepegawaian/sk" class="nav-link"><p>Daftar Surat Keputusan</p></a></li>
                <?php endif; ?>
                <?php if (AccessControl::can('pegawai.sertifikat','view') || AccessControl::can('pegawai.sertifikat','view-own')): ?>
                <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=kepegawaian/sertifikat" class="nav-link"><p>Daftar Sertifikat</p></a></li>
                <?php endif; ?>
              </ul>
            </li>

            <!-- Item lain -->
            <?php if (AccessControl::can('pegawai.bpjs','view') || AccessControl::can('pegawai.bpjs','view-own')): ?>
            <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=kepegawaian/bpjs" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Daftar Kepesertaan BPJS</p></a></li>
            <?php endif; ?>
            <?php if (AccessControl::can('pegawai.cuti','view') || AccessControl::can('pegawai.cuti','view-own')): ?>
            <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=cuti/index" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Daftar Cuti Pegawai</p></a></li>
            <?php endif; ?>
            <?php if (AccessControl::can('pegawai.grafik','view')): ?>
            <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=kepegawaian/grafik" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Grafik Kepegawaian</p></a></li>
            <?php endif; ?>

          </ul>
        </li>
        <?php endif; ?>

        <!-- Manajemen Surat -->
        <?php if (AccessControl::can('surat.keluar','view') || AccessControl::can('surat.masuk','view')): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-envelope"></i>
            <p>Manajemen Surat<i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <?php if (AccessControl::can('surat.keluar','view')): ?>
            <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=suratkeluar" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Surat Keluar</p></a></li>
            <?php endif; ?>
            <?php if (AccessControl::can('surat.masuk','view')): ?>
            <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=suratmasuk" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Surat Masuk</p></a></li>
            <?php endif; ?>
            <?php if (AccessControl::can('surat.internal','view') || AccessControl::can('surat.internal','view-own')): ?>
            <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=suratinternal" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Surat Internal</p></a></li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Manajemen Remunerasi -->
        <?php if (
          AccessControl::can('remunerasi.master_index','view') ||
          AccessControl::can('remunerasi.index','view') ||
          AccessControl::can('remunerasi.generate','create')
        ): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-money-check-alt"></i>
            <p>Manajemen Remunerasi<i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <?php if (AccessControl::can('remunerasi.master_index','view')): ?>
            <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=remunerasi/master_index" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Master Index</p></a></li>
            <?php endif; ?>
            <?php if (AccessControl::can('remunerasi.tiket','view')): ?>
            <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=remunerasi/tiket_list" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Tiket Remunerasi</p></a></li>
            <?php endif; ?>
            <?php if (AccessControl::can('remunerasi.riwayat','view')): ?>
            <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=remunerasi/riwayat" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Riwayat Remunerasi</p></a></li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Akun Saya -->
        <?php if (
          $_SESSION['user']['hak_akses'] !== 'administrator' &&
          (AccessControl::can('profile.edit_data_saya','view') || AccessControl::can('profile.edit_data_saya','view-own'))
        ): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-user-edit"></i>
            <p>Akun Saya<i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item"><a href="<?= BASE_URL ?>/index.php?url=kepegawaian/edit_data_saya" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Edit Data Saya</p></a></li>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Tentang Aplikasi -->
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?url=about" class="nav-link">
            <i class="nav-icon fas fa-info-circle"></i>
            <p>Tentang Aplikasi</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>
