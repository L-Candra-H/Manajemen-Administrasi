<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

// Guard akses view
if (!AccessControl::can('surat.internal','view') 
    && !AccessControl::can('surat.internal','view-own')) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data Surat Internal.</div>";
  exit;
}

$role    = strtolower($_SESSION['user']['hak_akses'] ?? '');
$jabatan = strtolower($_SESSION['user']['nama_jabatan'] ?? '');

$isAdminRole       = in_array($role, ['administrator','admin2','admin3']);
$isDirekturOrOther = (!$isAdminRole && $jabatan !== 'karyawan');
?>
<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-3">
      <div class="row align-items-center">
        <div class="col-md-6 d-flex align-items-center gap-2">
          <h5 class="mb-0">Daftar Surat Internal</h5>
          <?php 
            // Tombol tambah hanya untuk Administrator dan jabatan selain Direktur/Karyawan
            if ($isAdminRole || (!$isAdminRole && $jabatan !== 'direktur' && $jabatan !== 'karyawan')): 
          ?>
            <a href="index.php?url=suratinternal/create" class="btn btn-sm btn-primary ms-2">
              <i class="fas fa-plus"></i> Tambah Surat Internal
            </a>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <form method="GET" action="index.php" class="d-flex justify-content-end">
            <input type="hidden" name="url" value="suratinternal/index">
            <input type="text" name="q" class="form-control form-control-sm w-50 me-2" placeholder="Cari nomor / unit">
          </form>
        </div>
      </div>
    </div>

    <div style="overflow-x:auto;">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light text-center">
          <tr>
            <th>Unit</th>
            <th>Nomor Surat</th>
            <th>Tanggal Surat</th>
            <th>Jawaban</th>
            <th>Berkas</th>
            <th>Status</th>
            <th>Tanggal Arsip</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($surat)): ?>
            <?php foreach ($surat as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['nama_unit']) ?></td>
                <td><?= htmlspecialchars($row['nomor_surat']) ?></td>
                <td><?= !empty($row['tanggal_surat']) ? date('d-m-Y', strtotime($row['tanggal_surat'])) : '-' ?></td>
                <td><?= htmlspecialchars($row['jawaban'] ?? '-') ?></td>
                <td class="text-center">
                  <?php if (!empty($row['berkas'])): ?>
                    <?php $tahun = date('Y', strtotime($row['tanggal_surat'])); ?>
                    <a href="<?= BASE_URL ?>/public/uploads/dokumen/surat_internal/<?= $tahun ?>/<?= $row['berkas'] ?>" target="_blank">
                      Lihat Surat
                    </a>
                  <?php else: ?>
                    <span class="text-muted">Belum Upload</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if ($row['status'] === 'sudah dibaca'): ?>
                    <span class="badge bg-success">Sudah Dibaca</span>
                  <?php else: ?>
                    <span class="badge bg-warning">Belum Dibaca</span>
                  <?php endif; ?>
                </td>
                <td><?= !empty($row['tanggal_diarsipkan']) ? date('d-m-Y', strtotime($row['tanggal_diarsipkan'])) : '-' ?></td>

                <td class="text-center">
                  <?php if (AccessControl::can('surat.internal','update') 
                            || AccessControl::can('surat.internal','update-own')): ?>

                    <?php if ($jabatan === 'direktur'): ?>
                      <a href="index.php?url=suratinternal/edit&id=<?= urlencode($row['id']) ?>" 
                         class="btn btn-sm btn-info">Buka</a>
                    <?php elseif ($row['status'] === 'sudah dibaca'): ?>
                      <button class="btn btn-sm btn-secondary" disabled>Terkunci</button>
                    <?php else: ?>
                      <a href="index.php?url=suratinternal/edit&id=<?= urlencode($row['id']) ?>" 
                         class="btn btn-sm btn-warning">Edit</a>
                    <?php endif; ?>

                  <?php endif; ?>
                </td>

              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center text-muted">Belum ada data surat internal</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Navigasi Pagination -->
<?php $pagination = $pagination ?? null; ?>
<?php if ($pagination): ?>
  <nav aria-label="Page navigation" class="mt-3">
    <ul class="pagination justify-content-center">
      <?php if ($pagination['page'] > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?url=suratinternal/index&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>">« Prev</a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link" href="?url=suratinternal/index&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link" href="?url=suratinternal/index&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>">Next »</a>
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
