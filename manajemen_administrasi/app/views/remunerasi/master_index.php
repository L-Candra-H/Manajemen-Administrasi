<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/AccessControl.php';

$canView   = AccessControl::can('remunerasi.master_index','view');
$canUpdate = AccessControl::can('remunerasi.master_index','update');

if (!$canView) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data Index Remunerasi.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2">
      <div class="row align-items-center">
        <div class="col-md-6 d-flex align-items-center gap-2">
          <h5 class="mb-0">Index Remunerasi Pegawai</h5>
          <?php if ($canUpdate): ?>
            <form method="post" action="index.php?url=remunerasi/generate_master_index" class="d-inline">
              <button type="submit" class="btn btn-sm btn-success ms-2">
                Generate Index
              </button>
            </form>
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <form method="GET" action="index.php" class="d-flex justify-content-end">
            <input type="hidden" name="url" value="remunerasi/master_index">
            <input type="text" name="q"
                   value="<?= htmlspecialchars($pagination['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   class="form-control form-control-sm w-50 me-2" placeholder="Cari nama / periode">
            <button type="submit" class="btn btn-sm btn-primary">Cari</button>
          </form>
        </div>
      </div>
    </div>

    <div style="overflow-x:auto;">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light text-center">
          <tr>
            <th style="width: 120px;">Nomor Induk Pegawai</th>
            <th style="width: 200px;">Nama Pegawai</th>
            <th style="width: 80px;">Golongan</th>
            <th style="width: 80px;">Jabatan</th>
            <th style="width: 80px;">Kategori Kepegawaian</th>
            <th style="width: 100px;">Pendidikan</th>
            <th style="width: 120px;">Status Kepegawaian</th>
            <th style="width: 120px;">Status Pernikahan</th>
            <th style="width: 100px;">Lama Kerja</th>
            <th style="width: 100px;">Total Index</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; if (!empty($data)): ?>
            <?php foreach ($data as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['nomor_induk_pegawai']) ?></td>
                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                <td><?= htmlspecialchars($row['nilai_golongan']) ?></td>
                <td><?= htmlspecialchars($row['nilai_jabatan']) ?></td>
                <td><?= htmlspecialchars($row['nilai_kategori']) ?></td>
                <td><?= htmlspecialchars($row['nilai_pendidikan']) ?></td>
                <td><?= htmlspecialchars($row['nilai_status_kepegawaian']) ?></td>
                <td><?= htmlspecialchars($row['nilai_status_pernikahan']) ?></td>
                <td><?= htmlspecialchars($row['nilai_lama_kerja']) ?></td>
                <td><?= htmlspecialchars($row['total_index']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="13" class="text-center text-muted">Belum ada data index remunerasi</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- navigasi pagination -->
<?php if ($pagination): ?>
  <nav aria-label="Page navigation" class="mt-3">
    <ul class="pagination justify-content-center">
      <?php if ($pagination['page'] > 1): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=remunerasi/master_index&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link"
             href="?url=remunerasi/master_index&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>

      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=remunerasi/master_index&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>">
            Next »
          </a>
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