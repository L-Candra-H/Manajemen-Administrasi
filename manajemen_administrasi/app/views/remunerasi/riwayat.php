<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/AccessControl.php';

$canView = AccessControl::can('remunerasi.riwayat','view');
if (!$canView) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat riwayat remunerasi.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Riwayat Remunerasi</h5>

      <!-- Form pencarian di pojok kanan -->
      <form method="GET" action="index.php" class="d-flex ms-auto">
        <input type="hidden" name="url" value="remunerasi/riwayat">

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
            <th>Tahun</th>
            <th>Mode</th>
            <th>Periode</th>
            <th>Nominal</th>
            <th>Finalized At</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($tiketList)): ?>
            <?php foreach ($tiketList as $t): ?>
              <tr>
                <td class="text-center"><?= htmlspecialchars($t['tahun']) ?></td>
                <td class="text-center"><?= htmlspecialchars(ucfirst($t['mode'])) ?></td>
                <td class="text-center"><?= htmlspecialchars($t['periode']) ?></td>
                <td class="text-end"><?= isset($t['total_nominal']) ? number_format($t['total_nominal'],0,',','.') : '-' ?></td>
                <td class="text-center"><?= htmlspecialchars($t['finalized_at']) ?></td>
                <td class="text-center">
                  <a href="index.php?url=remunerasi/index&tiket_id=<?= $t['id'] ?>" class="btn btn-info btn-sm">Lihat Hasil</a>
                  <a href="javascript:void(0);"
                       class="btn btn-primary btn-sm"
                       onclick="window.open(
                           'index.php?url=remunerasi/cetak&tiket_id=<?= $t['id'] ?>',
                           'CetakRemunerasi',
                           'width='+screen.width+',height='+screen.height+',scrollbars=yes,resizable=yes'
                       );">
                       Cetak
                    </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted">Belum ada tiket finalized</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
  .table td, .table th {
    white-space: nowrap;
  }
</style>
