<?php
require_once __DIR__ . '/../../helpers/AccessControl.php';

$canViewAll = AccessControl::can('pegawai.bpjs','view');
$canViewOwn = AccessControl::can('pegawai.bpjs','view-own');

if (!$canViewAll && !$canViewOwn) {
    echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat daftar BPJS.</div>";
    exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-3">
      <div class="row align-items-center">
        <div class="col-md-6 d-flex align-items-center gap-2">
          <h5 class="mb-0">Daftar Kepesertaan BPJS</h5>
        </div>
        <div class="col-md-6">
          <form method="GET" action="index.php" class="d-flex justify-content-end">
            <input type="hidden" name="url" value="kepegawaian/daftar_bpjs">
            <input type="text" id="searchBox" name="q"
                   value="<?= htmlspecialchars($data['pagination']['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   class="form-control form-control-sm w-50 me-2" placeholder="Cari nama atau Nomor BPJS">
          </form>
        </div>
      </div>
    </div>

    <div style="overflow-x:auto;">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light text-center">
          <tr>
            <th>Nomor Induk Pegawai</th>
            <th>Nama Pegawai</th>
            <th>BPJS Ketenagakerjaan</th>
            <th>BPJS Kesehatan</th>
            <th>BPJS Kesehatan Keluarga</th>
            <th>BPJS Kesehatan Tambahan</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data['daftar_bpjs'])): ?>
            <?php foreach ($data['daftar_bpjs'] as $row): ?>
              <?php
                $isOwner = ($_SESSION['user']['nomor_induk_pegawai'] ?? null) === ($row['nomor_induk_pegawai'] ?? null);
                if ($canViewAll || ($canViewOwn && $isOwner)):
              ?>
              <tr>
                <td class="text-center"><?= htmlspecialchars($row['nomor_induk_pegawai'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['nama_lengkap'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center">
                  <?= !empty($row['nomor_kartu_BPJS_ketenagakerjaan'])
                        ? htmlspecialchars($row['nomor_kartu_BPJS_ketenagakerjaan'], ENT_QUOTES, 'UTF-8')
                        : '<span class="text-muted">Belum terdaftar</span>' ?>
                </td>
                <td class="text-center">
                  <?= !empty($row['nomor_kartu_BPJS_kesehatan'])
                        ? htmlspecialchars($row['nomor_kartu_BPJS_kesehatan'], ENT_QUOTES, 'UTF-8')
                        : '<span class="text-muted">Belum terdaftar</span>' ?>
                </td>
                <td class="text-center">
                  <?= !empty($row['keluarga'])
                        ? htmlspecialchars($row['keluarga'], ENT_QUOTES, 'UTF-8')
                        : '<span class="text-muted">Belum terdaftar</span>' ?>
                </td>
                <td class="text-center">
                  <?= !empty($row['tambahan'])
                        ? htmlspecialchars($row['tambahan'], ENT_QUOTES, 'UTF-8')
                        : '<span class="text-muted">Belum terdaftar</span>' ?>
                </td>
              </tr>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted">Belum ada data BPJS</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Navigasi Pagination -->
<?php $pagination = $data['pagination'] ?? null; ?>
<?php if ($pagination): ?>
  <nav aria-label="Page navigation" class="mt-3">
    <ul class="pagination justify-content-center">
      <!-- Tombol Prev -->
      <?php if ($pagination['page'] > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?url=kepegawaian/bpjs&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>">« Prev</a>
        </li>
      <?php endif; ?>

      <!-- Nomor halaman -->
      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link" href="?url=kepegawaian/bpjs&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <!-- Tombol Next -->
      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link" href="?url=kepegawaian/bpjs&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>">Next »</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>

<style>
  .table td, .table th {
    white-space: nowrap;
  }
</style>
