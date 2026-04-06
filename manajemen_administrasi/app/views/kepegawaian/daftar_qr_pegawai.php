<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';
$canViewQr = AccessControl::can('pegawai.qrcode','view') 
           || AccessControl::can('pegawai.qrcode','view-own');

if (!$canViewQr) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat QR Pegawai.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-3">
      <div class="row align-items-center">
        <div class="col-md-6 d-flex align-items-center gap-2">
          <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i> Daftar QR Code Pegawai</h5>
        </div>
        <div class="col-md-6">
          <form method="GET" action="index.php" class="d-flex justify-content-end">
            <input type="hidden" name="url" value="kepegawaian/daftar_qr_pegawai">
            <input type="text" id="searchBox" name="q"
                   value="<?= htmlspecialchars($data['pagination']['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   class="form-control form-control-sm w-50 me-2" placeholder="Cari nama atau NIP">
          </form>
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light text-center">
          <tr>
            <th>NIP</th>
            <th>Nama Pegawai</th>
            <th>QR Code</th>
            <th>Tanggal Generate</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data['daftar_qr'])): ?>
            <?php foreach ($data['daftar_qr'] as $qr): ?>
              <?php
                $nip   = $qr['nip'] ?? '';
                $nama  = $qr['nama_lengkap'] ?? '-';
                $file  = $qr['qr_file'] ?? '';
                $tgl   = $qr['qr_generated_at'] ?? '-';
              ?>
              <tr>
                <!-- NIP -->
                <td class="text-center"><?= htmlspecialchars($nip, ENT_QUOTES, 'UTF-8') ?></td>

                <!-- Nama -->
                <td><?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?></td>

                <!-- QR Code -->
                <td class="text-center">
                  <?php if (!empty($file)): ?>
                    <a href="<?= BASE_URL ?>/public/uploads/qrcode/pegawai/<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                      <img src="<?= BASE_URL ?>/public/uploads/qrcode/pegawai/<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>"
                           alt="QR Pegawai"
                           style="height:55px;border:1px solid #ccc;border-radius:6px;padding:4px;background:#fff;">
                    </a>
                  <?php else: ?>
                    <span class="badge bg-secondary">Belum Digenerate</span>
                  <?php endif; ?>
                </td>

                <!-- Tanggal Generate -->
                <td class="text-center"><?= htmlspecialchars($tgl, ENT_QUOTES, 'UTF-8') ?></td>

                <!-- Aksi -->
                <td class="text-center">
                  <?php if (AccessControl::can('pegawai.qrcode','update')): ?>
                    <a href="index.php?url=kepegawaian/generateQrPegawai&nip=<?= urlencode($nip) ?>"
                       class="btn btn-sm btn-outline-primary">
                       <i class="fas fa-sync-alt"></i> Regenerate
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center text-muted">Belum ada QR Code pegawai</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- navigasi pagination -->
<?php $pagination = $data['pagination'] ?? null; ?>

<?php if ($pagination): ?>
  <nav aria-label="Page navigation" class="mt-3">
    <ul class="pagination justify-content-center">
      <!-- Tombol Prev -->
      <?php if ($pagination['page'] > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?url=kepegawaian/daftar_qr_pegawai&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>">« Prev</a>
        </li>
      <?php endif; ?>

      <!-- Nomor halaman -->
      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link" href="?url=kepegawaian/daftar_qr_pegawai&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <!-- Tombol Next -->
      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link" href="?url=kepegawaian/daftar_qr_pegawai&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>">Next »</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>
