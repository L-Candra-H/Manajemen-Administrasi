<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

$canViewAll = AccessControl::can('pegawai.cuti','view');
$canViewOwn = AccessControl::can('pegawai.cuti','view-own');
$canCreate  = AccessControl::can('pegawai.cuti','create') || AccessControl::can('pegawai.cuti','create-own');
$canApprove = AccessControl::can('pegawai.cuti','approve');

if (!($canViewAll || $canViewOwn)) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat daftar cuti.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-3">
      <div class="row align-items-center">
        <div class="col-md-6 d-flex align-items-center gap-2">
          <h5 class="mb-0">Daftar Cuti Pegawai</h5>
          <?php if ($canCreate): ?>
            <a href="index.php?url=cuti/create" class="btn btn-sm btn-success ms-2">
              + Ajukan Cuti
            </a>
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <form method="GET" action="index.php" class="d-flex justify-content-end">
            <input type="hidden" name="url" value="cuti/index">
            <input type="text" id="searchBox" name="q"
                   value="<?= htmlspecialchars($data['pagination']['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   class="form-control form-control-sm w-50 me-2" placeholder="Cari nama pegawai atau jenis cuti">
            <button type="submit" class="btn btn-sm btn-primary">Cari</button>
          </form>
        </div>
      </div>
    </div>

    <div style="overflow-x:auto;">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light text-center">
          <tr>
            <th style="width: 120px;">NIP</th>
            <th style="width: 160px;">Nama Pegawai</th>
            <th style="width: 120px;">Tanggal Mulai</th>
            <th style="width: 120px;">Tanggal Selesai</th>
            <th style="width: 100px;">Lama Cuti</th>
            <th style="width: 120px;">Jenis Cuti</th>
            <th>Alasan</th>
            <th style="width: 100px;">Status</th>
            <th style="width: 200px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data['cuti'])): ?>
            <?php foreach ($data['cuti'] as $c): ?>
              <?php
                $mulai   = !empty($c['tanggal_mulai']) ? date('d-m-Y', strtotime($c['tanggal_mulai'])) : '-';
                $selesai = !empty($c['tanggal_selesai']) ? date('d-m-Y', strtotime($c['tanggal_selesai'])) : '-';
                $lama    = $c['lama_cuti_hari'] ?? 0;
                $status  = $c['status_pengajuan'] ?? 'Diajukan';

                if ($status === 'Disetujui') {
                  $badge = '<span class="badge bg-success">Disetujui</span>';
                } elseif ($status === 'Ditolak') {
                  $badge = '<span class="badge bg-danger">Ditolak</span>';
                } else {
                  $badge = '<span class="badge bg-warning text-dark">Diajukan</span>';
                }
              ?>
              <tr>
                <td class="text-center"><?= htmlspecialchars($c['nomor_induk_pegawai'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= $mulai ?></td>
                <td class="text-center"><?= $selesai ?></td>
                <td class="text-center"><?= $lama ?> hari</td>
                <td class="text-center"><?= htmlspecialchars($c['jenis_cuti'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['alasan'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= $badge ?></td>
                <td class="text-center">
                  <?php if (AccessControl::can('pegawai.cuti','update-own') && $c['pegawai_id'] == ($_SESSION['user']['pegawai_id'] ?? null)): ?>
                    <?php if ($status === 'Diajukan'): ?>
                      <a href="index.php?url=cuti/edit/<?= $c['id_cuti'] ?>" class="btn btn-sm btn-primary">Edit</a>
                    <?php else: ?>
                      <button class="btn btn-sm btn-secondary" disabled>Edit</button>
                    <?php endif; ?>
                  <?php endif; ?>

                  <?php if (AccessControl::can('pegawai.cuti','update')): ?>
                    <?php if ($status === 'Diajukan'): ?>
                      <a href="index.php?url=cuti/edit/<?= $c['id_cuti'] ?>" class="btn btn-sm btn-primary">Edit</a>
                      <?php if ($canApprove): ?>
                        <a href="index.php?url=cuti/approve/<?= $c['id_cuti'] ?>" class="btn btn-sm btn-success">Approve</a>
                        <a href="index.php?url=cuti/reject/<?= $c['id_cuti'] ?>" class="btn btn-sm btn-danger">Reject</a>
                      <?php endif; ?>
                    <?php else: ?>
                      <button class="btn btn-sm btn-secondary" disabled>Edit</button>
                    <?php endif; ?>
                  <?php endif; ?>

                  <?php if (!AccessControl::can('pegawai.cuti','update-own') && !AccessControl::can('pegawai.cuti','update')): ?>
                    <button class="btn btn-sm btn-secondary" disabled>Aksi</button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center text-muted">Belum ada data cuti</td>
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
      <?php if ($pagination['page'] > 1): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=cuti/index&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link"
             href="?url=cuti/index&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>

      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=cuti/index&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>">
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