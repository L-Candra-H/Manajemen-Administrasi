<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../helpers/AccessControl.php';

$canView   = AccessControl::can('remunerasi.tiket','view');
$canCreate = AccessControl::can('remunerasi.generate','create');

if (!$canView) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data Tiket Remunerasi.</div>";
  exit;
}

?>
<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2">
      <div class="row align-items-center">
        <div class="col-md-6 d-flex align-items-center gap-2">
          <h5 class="mb-0">Daftar Tiket Remunerasi</h5>
          <?php if ($canCreate): ?>
            <!-- tombol buka modal generate -->
            <button type="button" class="btn btn-sm btn-success ms-2"
                    data-toggle="modal" data-target="#generateModal">
              + Generate Baru
            </button>
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <form method="GET" action="index.php" class="d-flex justify-content-end">
            <input type="hidden" name="url" value="remunerasi/tiket_list">
            <input type="text" name="q"
                   value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   class="form-control form-control-sm w-50 me-2" placeholder="Cari tahun / periode">
            <button type="submit" class="btn btn-sm btn-primary">Cari</button>
          </form>
        </div>
      </div>
    </div>

    <div style="overflow-x:auto;">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light text-center">
          <tr>
            <th style="width: 60px;">ID</th>
            <th style="width: 80px;">Tahun</th>
            <th style="width: 100px;">Mode</th>
            <th style="width: 100px;">Periode</th>
            <th style="width: 150px;">Total Nominal</th>
            <th style="width: 120px;">Dibuat Oleh</th>
            <th style="width: 150px;">Tanggal Generate</th>
            <th style="width: 100px;">Status</th>
            <th style="width: 100px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($tiket)): ?>
            <?php foreach ($tiket as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['tahun']) ?></td>
                <td><?= ucfirst(htmlspecialchars($row['mode'])) ?></td>
                <td><?= htmlspecialchars($row['periode']) ?></td>
                <td class="text-end"><?= number_format($row['total_nominal'], 0, ',', '.') ?></td>
                <td><?= htmlspecialchars($row['dibuat_oleh']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                  <?php
                    if ($row['status'] === 'batal') {
                        echo "<span class='badge bg-danger'>Batal</span>";
                    } else {
                        if ($row['lifecycle'] === 'finalized') {
                            echo "<span class='badge bg-secondary'>Finalized (Aktif)</span>";
                        } elseif ($row['lifecycle'] === 'processed') {
                            echo "<span class='badge bg-info'>Processed (Aktif)</span>";
                        } else {
                            echo "<span class='badge bg-warning text-dark'>Draft (Aktif)</span>";
                        }
                    }
                  ?>
                </td>
                <?php
                $role    = strtolower($_SESSION['user']['hak_akses'] ?? '');
                $jabatan = strtolower($_SESSION['user']['nama_jabatan'] ?? '');
                ?>

                <td class="text-center">
                  <?php if ($row['status'] === 'batal'): ?>
                    <!-- Semua tombol disabled -->
                    <button class="btn btn-sm btn-secondary" disabled>Lihat</button>
                    <button class="btn btn-sm btn-primary" disabled>Edit</button>
                    <button class="btn btn-sm btn-warning" disabled>Batal</button>
                    <button class="btn btn-sm btn-success" disabled>Finalize</button>
                    <button class="btn btn-sm btn-danger" disabled>Batalkan Finalisasi</button>

                  <?php elseif ($row['lifecycle'] === 'draft'): ?>
                    <?php if ($role === 'administrator' || in_array($role, ['admin','admin3']) || $jabatan === 'direktur'): ?>
                      <!-- Admin/Admin3/Administrator/Direktur bisa lihat draft -->
                      <a href="index.php?url=remunerasi/index&tiket_id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Lihat Draft</a>
                    <?php endif; ?>

                    <?php if ($role === 'administrator'): ?>
                      <!-- Administrator full akses -->
                      <a href="index.php?url=remunerasi/edit_tiket&id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                      <a href="index.php?url=remunerasi/tiket_batal&id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary"
                         onclick="return confirm('Yakin batalkan tiket ini?')">Batal</a>
                      <form method="post" action="index.php?url=remunerasi/finalize" style="display:inline;">
                        <input type="hidden" name="tiket_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-success">Finalize</button>
                      </form>
                    <?php elseif (in_array($role, ['admin','admin3'])): ?>
                      <!-- Admin/Admin3 hanya finalize -->
                      <form method="post" action="index.php?url=remunerasi/finalize" style="display:inline;">
                        <input type="hidden" name="tiket_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-success">Finalize</button>
                      </form>
                    <?php endif; ?>

                  <?php elseif ($row['lifecycle'] === 'finalized'): ?>
                    <!-- Semua role bisa lihat final -->
                    <a href="index.php?url=remunerasi/index&tiket_id=<?= $row['id'] ?>" class="btn btn-sm btn-success">Lihat Final</a>

                    <?php if ($role === 'administrator'): ?>
                      <!-- Administrator full akses -->
                      <form method="post" action="index.php?url=remunerasi/unfinalize" style="display:inline;">
                        <input type="hidden" name="tiket_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Yakin batalkan finalisasi tiket ini?')">
                          Batalkan Finalisasi
                        </button>
                      </form>
                    <?php elseif ($jabatan === 'direktur'): ?>
                      <!-- Direktur khusus batalkan finalisasi -->
                      <form method="post" action="index.php?url=remunerasi/unfinalize" style="display:inline;">
                        <input type="hidden" name="tiket_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Yakin batalkan finalisasi tiket ini?')">
                          Batalkan Finalisasi
                        </button>
                      </form>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center text-muted">Belum ada tiket remunerasi</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- navigasi pagination -->
<?php if (!empty($pagination)): ?>
  <nav aria-label="Page navigation" class="mt-3">
    <ul class="pagination justify-content-center">
      <?php if ($pagination['page'] > 1): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=remunerasi/tiket_list&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link"
             href="?url=remunerasi/tiket_list&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>

      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=remunerasi/tiket_list&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>">
            Next »
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>

<!-- Modal Pop-up: Form Generate Remunerasi -->
<div class="modal fade" id="generateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="index.php?url=remunerasi/generate">
        <div class="modal-header">
          <h5 class="modal-title">Generate Remunerasi Baru</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Tahun</label>
              <input type="number" name="tahun" class="form-control"
                     placeholder="-- pilih tahun --" min="2000" max="2099" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Mode</label>
              <select name="mode" class="form-control" required>
                <option value="">-- Pilih Mode --</option>
                <option value="bulanan">Bulanan</option>
                <option value="triwulan">Triwulan</option>
                <option value="semester">Semester</option>
                <option value="tahunan">Tahunan</option>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Periode (YYYY-MM)</label>
              <input type="month" name="periode" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Total Nominal Remunerasi</label>
              <input type="number" name="totalNominal" class="form-control"
                     placeholder="-- isi nominal --" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Generate</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- pagination -->
<?php if (!empty($pagination) && $pagination['totalPage'] > 1): ?>
  <nav aria-label="Page navigation" class="mt-3">
    <ul class="pagination justify-content-center">
      <?php if ($pagination['page'] > 1): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=remunerasi/tiket_list&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link"
             href="?url=remunerasi/tiket_list&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>

      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=remunerasi/tiket_list&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>">
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
