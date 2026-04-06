<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

$nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? '';

$canViewAll = AccessControl::can('pegawai.sk','view');
$canViewOwn = AccessControl::can('pegawai.sk','view-own');

if (!$canViewAll && !$canViewOwn) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat daftar SK.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-3">
      <div class="row align-items-center">
        <div class="col-md-6 d-flex align-items-center gap-2">
          <h5 class="mb-0">Daftar Surat Keputusan (SK)</h5>
          <?php if (AccessControl::can('pegawai.sk','create')): ?>
            <a href="index.php?url=kepegawaian/sk_tambah" class="btn btn-sm btn-success ms-2">
              + Tambah SK Pegawai
            </a>
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <form method="GET" action="index.php" class="d-flex justify-content-end">
            <input type="hidden" name="url" value="kepegawaian/sk">
            <input type="text" id="searchBox" name="q"
                   value="<?= htmlspecialchars($data['pagination']['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   class="form-control form-control-sm w-50 me-2" placeholder="Cari nama atau Nomor SK">
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
            <th>Nomor SK</th>
            <th>Mulai Berlaku SK</th>
            <th>Berakhir SK</th>
            <th>Berkas SK</th>
            <th>Masa Aktif SK</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data['daftar_sk'])): ?>
            <?php foreach ($data['daftar_sk'] as $sk): ?>
              <?php
                $nip = $sk['nomor_induk_pegawai'] ?? '';
                $isOwner = ($nipSession === $nip);
                if (!AccessControl::can('pegawai.sk','view') && !(AccessControl::can('pegawai.sk','view-own') && $isOwner)) {
                    continue;
                }

                $nama     = $sk['nama_lengkap'] ?? '-';
                $nomor    = $sk['nomor_surat_keputusan'] ?? '-';
                $mulai    = !empty($sk['mulai_berlaku_surat_keputusan']) ? date('d-m-Y', strtotime($sk['mulai_berlaku_surat_keputusan'])) : '-';
                $berakhir = !empty($sk['berakhir_surat_keputusan']) ? date('d-m-Y', strtotime($sk['berakhir_surat_keputusan'])) : '-';
                $berkas   = $sk['berkas_surat_keputusan'] ?? '';
                $endDate  = !empty($sk['berakhir_surat_keputusan']) ? new DateTime($sk['berakhir_surat_keputusan']) : null;
                $nowDate  = new DateTime();

                $days = ($endDate) ? round(($endDate->getTimestamp() - $nowDate->getTimestamp()) / (60*60*24)) : 0;

                if ($endDate) {
                    $interval = $nowDate->diff($endDate);
                    $masaAktifText = $interval->y . " tahun " . $interval->m . " bulan " . $interval->d . " hari";
                } else {
                    $masaAktifText = "-";
                }

                if ($days > 180) {
                    $masaAktif = '<span class="badge badge-success p-2 text-center d-inline-block" style="line-height:1.2">
                                    <i class="fas fa-check-circle"></i><br>
                                    <strong>AKTIF</strong><br>
                                    <span style="font-size:0.9rem; font-weight:bold">' . $masaAktifText . '</span>
                                  </span>';
                } elseif ($days > 0) {
                    $masaAktif = '<span class="badge badge-warning text-dark p-2 text-center d-inline-block" style="line-height:1.2">
                                    <i class="fas fa-exclamation-circle"></i><br>
                                    <strong>Waktunya Pembaruan</strong><br>
                                    <span style="font-size:0.9rem; font-weight:bold">' . $masaAktifText . '</span>
                                  </span>';
                } else {
                    $masaAktif = '<span class="badge badge-danger p-2 text-center d-inline-block" style="line-height:1.2">
                                   <i class="fas fa-times-circle"></i><br>
                                   <strong>TIDAK AKTIF</strong><br>
                                   <span style="font-size:0.9rem; font-weight:bold">0 hari</span>
                                 </span>';
                }

                $tahun = !empty($sk['mulai_berlaku_surat_keputusan']) ? date('Y', strtotime($sk['mulai_berlaku_surat_keputusan'])) : date('Y');
              ?>
              <tr>
                <td class="text-center"><?= htmlspecialchars($nip, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= htmlspecialchars($nomor, ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= $mulai ?></td>
                <td class="text-center"><?= $berakhir ?></td>
                <td class="text-center">
                  <?php if (!empty($berkas)): ?>
                    <a href="<?= BASE_URL ?>/public/uploads/pegawai/surat_keputusan/<?= $tahun ?>/<?= htmlspecialchars($berkas, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                      Lihat SK
                    </a>
                  <?php else: ?>
                    <span class="text-muted">Belum Upload</span>
                  <?php endif; ?>
                </td>
                <td class="text-center"><?= $masaAktif ?></td>
                <td class="text-center">
                  <?php if (AccessControl::can('pegawai.sk','update')): ?>
                    <a href="index.php?url=kepegawaian/sk_edit&id=<?= $sk['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                  <?php else: ?>
                    <button class="btn btn-sm btn-secondary" disabled>Edit</button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center text-muted">Belum ada data SK</td>
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
          <a class="page-link" 
             href="?url=kepegawaian/sk&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>
      <?php endif; ?>

      <!-- Nomor halaman -->
      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link" 
             href="?url=kepegawaian/sk&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>

      <!-- Tombol Next -->
      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link" 
             href="?url=kepegawaian/sk&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>">
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
