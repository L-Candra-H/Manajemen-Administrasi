<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

if (!AccessControl::can('surat.masuk','view')) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data Surat Masuk.</div>";
  exit;
}

$role    = strtolower($_SESSION['user']['hak_akses'] ?? '');
$jabatan = strtolower($_SESSION['user']['nama_jabatan'] ?? '');

$isAdminRole      = in_array($role, ['administrator','admin2','admin3']);
$isDirekturOrOther = (!$isAdminRole && $jabatan !== 'karyawan');
?>
<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-3">
      <div class="row align-items-center">
        <div class="col-md-6 d-flex align-items-center gap-2">
          <h5 class="mb-0">Daftar Surat Masuk</h5>
          <?php if ($isAdminRole): ?>
            <a href="index.php?url=suratmasuk/create" class="btn btn-sm btn-primary ms-2">
              <i class="fas fa-plus"></i> Tambah Surat Masuk
            </a>
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <form method="GET" action="index.php" class="d-flex justify-content-end">
            <input type="hidden" name="url" value="suratmasuk/index">
            <input type="text" name="q" class="form-control form-control-sm w-50 me-2" placeholder="Cari nomor / asal / perihal">
          </form>
        </div>
      </div>
    </div>

    <div style="overflow-x:auto;">
      <table class="table table-bordered table-hover mb-0 text-center">
        <thead class="thead-light">
          <tr>
            <th>Nomor Agenda</th>
            <th>Nomor Surat</th>
            <th>Tanggal Surat</th>
            <th>Asal Surat</th>
            <th>Perihal</th>
            <th>Isi Disposisi</th>
            <th>Status</th>
            <th>Berkas</th>
            <th>Tanggal Arsip</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($surat)): ?>
            <?php foreach ($surat as $row): ?>
              <?php
                $id     = $row['id'];
                $status = $statusMap[$row['status_surat_id']] ?? 'Tidak Diketahui';
                $badge  = $statusBadge[$row['status_surat_id']] ?? 'secondary';
              ?>
              <tr>
                <td><?= htmlspecialchars($row['nomor_agenda']) ?></td>
                <td><?= htmlspecialchars($row['nomor_surat']) ?></td>
                <td><?= !empty($row['tanggal_surat']) ? date('d-m-Y', strtotime($row['tanggal_surat'])) : '-' ?></td>
                <td><?= htmlspecialchars($row['asal_surat']) ?></td>
                <td><?= htmlspecialchars($row['perihal_surat']) ?></td>
                <td>
                  <?php if (!empty($row['isi_disposisi'])): ?>
                    <?= nl2br(htmlspecialchars($row['isi_disposisi'])) ?>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if ($isAdminRole): ?>
                    <span class="badge bg-<?= $badge ?>" style="cursor:pointer"
                          onclick="openStatusModal('<?= $id ?>','<?= $status ?>')">
                        <?= $status ?>
                    </span>
                  <?php else: ?>
                    <span class="badge bg-<?= $badge ?>"><?= $status ?></span>
                  <?php endif; ?>
                </td>

                <td>
                  <?php if (!empty($row['berkas_surat'])): ?>
                    <?php $tahun = date('Y', strtotime($row['tanggal_surat'])); ?>
                    <a href="<?= BASE_URL ?>/uploads/dokumen/surat_masuk/<?= $tahun ?>/<?= $row['berkas_surat'] ?>" target="_blank">Lihat Surat</a>
                  <?php else: ?>
                    <span class="text-muted">Tidak ada</span>
                  <?php endif; ?>
                </td>
                <td><?= !empty($row['tanggal_diarsipkan']) ? date('d-m-Y', strtotime($row['tanggal_diarsipkan'])) : '-' ?></td>
                <td>
                  <?php
                    $isDiarsipkan = ($row['status_surat_id'] == 4); // 4 = Diarsipkan
                    $isAdmin2or3  = in_array($role, ['admin2','admin3']);
                    $isDirektur   = ($jabatan === 'direktur');
                  ?>

                  <?php if ($isAdmin2or3): ?>
                    <?php if ($isDiarsipkan): ?>
                      <a href="index.php?url=suratmasuk/detail/<?= urlencode($id) ?>" class="btn btn-sm btn-info">Buka</a>
                    <?php else: ?>
                      <a href="index.php?url=suratmasuk/edit/<?= urlencode($id) ?>" class="btn btn-sm btn-warning">Edit</a>
                      <?php endif; ?>
                  <?php elseif ($isDirektur): ?>
                    <a href="index.php?url=suratmasuk/detail/<?= urlencode($id) ?>" class="btn btn-sm btn-info">Buka</a>
                  <?php else: ?>
                    <a href="index.php?url=suratmasuk/detail/<?= urlencode($id) ?>" class="btn btn-sm btn-info">Buka</a>
                  <?php endif; ?>
                 </td>

              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="9" class="text-center text-muted">Belum ada data surat masuk</td></tr>
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
          <a class="page-link" href="?url=suratmasuk/index&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>">« Prev</a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link" href="?url=suratmasuk/index&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link" href="?url=suratmasuk/index&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>">Next »</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>

<?php if ($isAdminRole): ?>
<!-- Modal Ubah Status Surat Masuk -->
<div class="modal fade" id="statusModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="index.php?url=suratmasuk/updateStatus">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Ubah Status Surat Masuk</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="statusSuratID">
          <div class="mb-3">
            <label>Status Surat</label>
            <select name="status_surat_id" class="form-select">
              <option value="4">Diarsipkan</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
function openStatusModal(id) {
  var modal = new bootstrap.Modal(document.getElementById('statusModal'));
  document.getElementById('statusSuratID').value = id;
  modal.show();
}
</script>

<style>
  .table td, .table th {
    white-space: nowrap;
  }
</style>

<?php endif; ?>