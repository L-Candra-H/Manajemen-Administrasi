<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

$canViewAll = AccessControl::can('pegawai.str','view');
$canViewOwn = AccessControl::can('pegawai.str','view-own');

if (!$canViewAll && !$canViewOwn) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat daftar STR.</div>";
  exit;
}

$nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? '';
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-3">
      <div class="row align-items-center">
        <div class="col-md-6 d-flex align-items-center gap-2">
          <h5 class="mb-0">Daftar Surat Tanda Registrasi (STR)</h5>
        </div>
        <div class="col-md-6">
          <form method="GET" action="index.php" class="d-flex justify-content-end">
            <input type="hidden" name="url" value="kepegawaian/str">
            <input type="text" id="searchBox" name="q"
                   value="<?= htmlspecialchars($data['pagination']['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   class="form-control form-control-sm w-50 me-2" placeholder="Cari nama atau Nomor STR">
          </form>
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light text-center">
          <tr>
            <th>Nomor Induk Pegawai</th>
            <th>Nama Pegawai</th>
            <th>Nomor STR</th>
            <th>Berkas STR</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data['daftar_str'])): ?>
            <?php foreach ($data['daftar_str'] as $row): ?>
              <?php
                $nip   = $row['nomor_induk_pegawai'] ?? '';
                $nama  = $row['nama_lengkap'] ?? '-';
                $nomor = $row['nomor_STR'] ?? '-';
                $file  = $row['berkas_STR'] ?? '';
              ?>
              <tr>
                <td class="text-center"><?= htmlspecialchars($nip, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center"><?= htmlspecialchars($nomor, ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center">
                  <?php if (!empty($file)): ?>
                    <?php if (AccessControl::can('pegawai.str','view') || (AccessControl::can('pegawai.str','view-own') && $nipSession === $nip)): ?>
                      <a href="<?= BASE_URL ?>/public/uploads/pegawai/str/<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>" target="_blank">Lihat STR</a>
                    <?php else: ?>
                      <span class="text-muted">Terkunci</span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted">Belum diunggah</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center text-muted">Belum ada data STR</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if (!empty($data['pagination'])): ?>
  <nav aria-label="Page navigation" class="mt-3">
    <ul class="pagination justify-content-center">
      <?php if ($data['pagination']['page'] > 1): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=kepegawaian/str&page=<?= $data['pagination']['page']-1 ?>&q=<?= urlencode($data['pagination']['search']) ?>">
            « Prev
          </a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $data['pagination']['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $data['pagination']['page']) ? 'active' : '' ?>">
          <a class="page-link"
             href="?url=kepegawaian/str&page=<?= $i ?>&q=<?= urlencode($data['pagination']['search']) ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>

      <?php if ($data['pagination']['page'] < $data['pagination']['totalPage']): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=kepegawaian/str&page=<?= $data['pagination']['page']+1 ?>&q=<?= urlencode($data['pagination']['search']) ?>">
            Next »
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>
