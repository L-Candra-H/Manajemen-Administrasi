<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

$canViewGrafik = AccessControl::can('pegawai.grafik','view');

if (!$canViewGrafik) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat grafik kepegawaian.</div>";
  exit;
}
?>

<div class="container mt-4">
  <h3>Grafik Kepegawaian</h3>

  <div class="row">
    <!-- Grafik Jenis Kelamin -->
    <div class="col-md-6">
      <div class="card mb-4">
        <div class="card-header"><strong>Pegawai berdasarkan Jenis Kelamin</strong></div>
        <div class="card-body">
          <canvas id="grafikJenisKelamin" height="200"></canvas>
        </div>
      </div>
    </div>

    <!-- Grafik Pendidikan -->
    <div class="col-md-6">
      <div class="card mb-4">
        <div class="card-header"><strong>Pegawai berdasarkan Pendidikan Terakhir</strong></div>
        <div class="card-body">
          <canvas id="grafikPendidikan" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Grafik Status -->
    <div class="col-md-6">
      <div class="card mb-4">
        <div class="card-header"><strong>Pegawai berdasarkan Status Kepegawaian</strong></div>
        <div class="card-body">
          <canvas id="grafikStatus" height="200"></canvas>
        </div>
      </div>
    </div>

    <!-- Grafik Kategori -->
    <div class="col-md-6">
      <div class="card mb-4">
        <div class="card-header"><strong>Pegawai berdasarkan Kategori Kepegawaian</strong></div>
        <div class="card-body">
          <canvas id="grafikKategori" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
  <!-- Grafik Lama Kerja -->
    <div class="col-md-12">
      <div class="card mb-4">
        <div class="card-header"><strong>Pegawai berdasarkan Lama Kerja</strong></div>
        <div class="card-body">
          <canvas id="grafikLamaKerja" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function renderChart(canvasId, labels, data, title, color) {
  new Chart(document.getElementById(canvasId), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: title,
        data: data,
        backgroundColor: color
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });
}

// Warna berbeda tiap grafik
renderChart('grafikJenisKelamin',
  <?= json_encode(array_column($grafikJenisKelamin, 'jenis')) ?>,
  <?= json_encode(array_column($grafikJenisKelamin, 'jumlah')) ?>,
  'Pegawai berdasarkan Jenis Kelamin',
  'rgba(54, 162, 235, 0.7)' // Biru
);

renderChart('grafikPendidikan',
  <?= json_encode(array_column($grafikPendidikan, 'pendidikan')) ?>,
  <?= json_encode(array_column($grafikPendidikan, 'jumlah')) ?>,
  'Pegawai berdasarkan Pendidikan Terakhir',
  'rgba(255, 159, 64, 0.7)' // Oranye
);

renderChart('grafikStatus',
  <?= json_encode(array_column($grafikStatus, 'status')) ?>,
  <?= json_encode(array_column($grafikStatus, 'jumlah')) ?>,
  'Pegawai berdasarkan Status Kepegawaian',
  'rgba(75, 192, 192, 0.7)' // Hijau
);

renderChart('grafikKategori',
  <?= json_encode(array_column($grafikKategori, 'kategori')) ?>,
  <?= json_encode(array_column($grafikKategori, 'jumlah')) ?>,
  'Pegawai berdasarkan Kategori Kepegawaian',
  'rgba(153, 102, 255, 0.7)' // Ungu
);

renderChart('grafikLamaKerja',
  <?= json_encode(array_column($grafikLamaKerja, 'lama_kerja')) ?>,
  <?= json_encode(array_column($grafikLamaKerja, 'jumlah')) ?>,
  'Pegawai berdasarkan Lama Kerja (tahun)',
  'rgba(255, 206, 86, 0.7)' // Kuning
);

</script>