<div class="content-header">
  <div class="container-fluid">
    <h1 class="m-0">
      Selamat Datang, <?= !empty($user['nama_lengkap']) ? htmlspecialchars($user['nama_lengkap']) : 'Administrator' ?>
    </h1>
    <p class="text-light">
      Anda login sebagai <strong><?= htmlspecialchars($user['username'] ?? 'admin') ?></strong>
      <?php if (!empty($nama_jabatan)): ?>
        <span class="badge badge-info text-light ml-2">Jabatan: <?= htmlspecialchars($nama_jabatan) ?></span>
      <?php endif; ?>
    </p>
  </div>
</div>

<section class="content">
  <div class="row">
    <!-- Total Pegawai Aktif -->
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3><?= $totalPegawai ?></h3>
          <p>Total Pegawai Aktif</p>
        </div>
        <div class="icon"><i class="fas fa-users"></i></div>
      </div>
    </div>

    <!-- Pegawai Ber-STR -->
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3><?= $jumlahSTR ?></h3>
          <p>Pegawai Ber STR Aktif</p>
        </div>
        <div class="icon"><i class="fas fa-id-card-alt"></i></div>
      </div>
    </div>

    <!-- Pegawai Ber-SIP -->
    <div class="col-lg-3 col-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3><?= $jumlahSIP ?></h3>
          <p>Pegawai Ber SIP Aktif</p>
        </div>
        <div class="icon"><i class="fas fa-file-medical"></i></div>
      </div>
    </div>
  </div>
</section>