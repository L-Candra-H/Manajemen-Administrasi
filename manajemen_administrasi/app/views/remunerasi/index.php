<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/AccessControl.php';

$canView = AccessControl::can('remunerasi.index','view');
if (!$canView) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat hasil remunerasi.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Hasil Remunerasi</h5>

      <!-- Form pencarian di pojok kanan -->
      <form method="GET" action="index.php" class="d-flex ms-auto">
        <input type="hidden" name="url" value="remunerasi/index">
        <input type="hidden" name="tiket_id" value="<?= htmlspecialchars($selectedTiket) ?>">

        <input type="text" name="q"
               value="<?= htmlspecialchars($pagination['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               class="form-control form-control-sm me-2" placeholder="Cari nama / NIP">
        <button type="submit" class="btn btn-sm btn-primary">Cari</button>
      </form>
    </div>

    <div style="overflow-x:auto;">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light text-center">
          <tr>
            <th>NIP</th>
            <th>Nama</th>
            <th>Tahun</th>
            <th>Mode</th>
            <th>Periode Nilai</th>
            <th>Poin Awal</th>
            <th>Cuti</th>
            <th>Real Kerja</th>
            <th>Point Akhir</th>
            <th>Net</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($hasil)): ?>
            <?php foreach ($hasil as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['nip'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['nama'] ?? '') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['tahun'] ?? '') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['mode'] ?? '') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['periode_nilai'] ?? '') ?></td>
                <td class="text-center"><?= htmlspecialchars($row['poin_awal'] ?? 0) ?></td>
                <td class="text-center"><?= htmlspecialchars($row['cuti'] ?? 0) ?></td>
                <td class="text-center"><?= htmlspecialchars($row['real_kerja'] ?? 0) ?></td>
                <td class="text-center"><?= number_format($row['point_akhir'] ?? 0,2,',','.') ?></td>
                <td class="text-end"><?= number_format($row['remunerasi_net'] ?? 0,0,',','.') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="12" class="text-center text-muted">
                Belum ada data remunerasi untuk tiket ini
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- pagination -->
<?php if (!empty($pagination)): ?>
  <nav aria-label="Page navigation" class="mt-3">
    <ul class="pagination justify-content-center">
      <?php if ($pagination['page'] > 1): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=remunerasi/index&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>&tiket_id=<?= urlencode($selectedTiket) ?>">
            « Prev
          </a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link"
             href="?url=remunerasi/index&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>&tiket_id=<?= urlencode($selectedTiket) ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>

      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=remunerasi/index&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>&tiket_id=<?= urlencode($selectedTiket) ?>">
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
