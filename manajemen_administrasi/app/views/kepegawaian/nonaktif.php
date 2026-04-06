<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

if (!AccessControl::can('pegawai.daftar_nonaktif','view')) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat daftar pegawai non aktif.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-3">
      <div class="row align-items-center">
        <div class="col-md-6 d-flex align-items-center gap-2">
          <h5 class="mb-0">Daftar Pegawai Non Aktif</h5>
        </div>
        <div class="col-md-6">
          <!-- Search bar -->
          <input type="text" id="searchInput" class="form-control form-control-sm w-50 ms-auto"
                 placeholder="Cari NIP / nama / alasan..."
                 value="<?= htmlspecialchars($data['pagination']['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light text-center">
          <tr>
            <th>Nomor Induk Pegawai</th>
            <th>Nama Pegawai</th>
            <th>Alasan Non Aktif</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data['pegawai_nonaktif'])): ?>
            <?php foreach ($data['pegawai_nonaktif'] as $pegawai): ?>
              <?php
                $nip    = $pegawai['nomor_induk_pegawai'] ?? '';
                $nama   = $pegawai['nama_lengkap'] ?? '';
                $alasan = $pegawai['alasan_non_aktif'] ?? '-';
              ?>
              <tr>
                <td class="text-center"><?= htmlspecialchars($nip, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($alasan, ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-center">
                  <?php if (AccessControl::can('pegawai.daftar_nonaktif','update')): ?>
                    <form method="POST" action="index.php?url=kepegawaian/aktifkan">
                      <input type="hidden" name="nomor_induk_pegawai" value="<?= htmlspecialchars($nip, ENT_QUOTES, 'UTF-8') ?>">
                      <button type="submit" class="btn btn-success btn-sm">Aktifkan</button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted">Tidak berhak mengubah status</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center text-muted">Tidak ada pegawai non aktif</td>
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
          <a class="page-link" href="?url=kepegawaian/nonaktif&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>">« Prev</a>
        </li>
      <?php endif; ?>

      <!-- Nomor halaman -->
      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link" href="?url=kepegawaian/nonaktif&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <!-- Tombol Next -->
      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link" href="?url=kepegawaian/nonaktif&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>">Next »</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>

<script>
// === Pencarian di tabel Pegawai Non Aktif ===
document.getElementById('searchInput').addEventListener('keyup', function() {
  const keyword = this.value.toLowerCase();
  const rows = document.querySelectorAll("table tbody tr");

  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(keyword) ? "" : "none";
  });
});
</script>
