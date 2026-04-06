<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../helpers/AccessControl.php';

$userRole = $_SESSION['user']['hak_akses'] ?? '';

// flag akses
$canEditHakAkses    = AccessControl::isAdministrator(); // hanya Administrator
$canEditJabatanUnit = in_array($userRole, ['admin','admin3','administrator']); // Admin, Admin3, Administrator

// kalau tidak punya akses apapun → tolak
if (!$canEditHakAkses && !$canEditJabatanUnit) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengelola data ini.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-3">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h5 class="mb-0">Manajemen Hak Akses</h5>
        </div>
        <div class="col-md-6">
          <input type="text" id="searchInput" class="form-control form-control-sm w-50 ms-auto"
                 placeholder="Cari username / NIP / nama...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light text-center">
          <tr>
            <th>Username</th>
            <th>Nomor Induk Pegawai</th>
            <th>Nama Lengkap</th>
            <th>Jabatan</th>
            <th>Unit Kerja</th>
            <?php if ($canEditHakAkses): ?>
              <th>Hak Akses</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['hak_akses'] as $row): ?>
            <?php if ($row['hak_akses'] !== 'administrator'): ?>
            <tr>
              <td class="text-center"><?= htmlspecialchars($row['username']) ?></td>
              <td class="text-center"><?= htmlspecialchars($row['nomor_induk_pegawai']) ?></td>
              <td><?= htmlspecialchars($row['nama_lengkap'] ?? '-') ?></td>

              <!-- Jabatan -->
              <td class="text-center">
                <?php if ($canEditJabatanUnit): ?>
                  <select class="form-control form-control-sm d-inline-block w-auto"
                          onchange="ubahJabatan('<?= $row['username'] ?>', this.value)">
                    <?php foreach ($data['jabatan_list'] as $jbt): ?>
                      <?php if (strtolower($jbt['keterangan']) !== 'administrator'): ?>
                        <option value="<?= $jbt['id'] ?>"
                          <?= (int)$row['jabatan_id'] === (int)$jbt['id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($jbt['keterangan']) ?>
                        </option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </select>
                <?php else: ?>
                  <?= htmlspecialchars($row['nama_jabatan']) ?>
                <?php endif; ?>
              </td>

              <!-- Unit Kerja -->
              <td class="text-center">
                <?php if ($canEditJabatanUnit): ?>
                  <select class="form-control form-control-sm d-inline-block w-auto"
                          onchange="ubahUnitKerja('<?= $row['username'] ?>', this.value)">
                    <?php foreach ($data['unit_kerja_list'] as $uk): ?>
                      <?php if (strtolower($uk['nama_unit']) !== 'it departemen'): ?>
                        <option value="<?= $uk['id'] ?>"
                          <?= (int)$row['unit_id'] === (int)$uk['id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($uk['nama_unit']) ?>
                        </option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </select>
                <?php else: ?>
                  <?= htmlspecialchars($row['nama_unit']) ?>
                <?php endif; ?>
              </td>

              <!-- Hak Akses -->
              <?php if ($canEditHakAkses): ?>
              <td class="text-center">
                <select class="form-control form-control-sm d-inline-block w-auto"
                        onchange="ubahHakAkses('<?= $row['username'] ?>', this.value)">
                  <option value="admin"   <?= $row['hak_akses']==='admin'   ? 'selected' : '' ?>>Admin</option>
                  <option value="admin2"  <?= $row['hak_akses']==='admin2'  ? 'selected' : '' ?>>Admin2</option>
                  <option value="admin3"  <?= $row['hak_akses']==='admin3'  ? 'selected' : '' ?>>Admin3</option>
                  <option value="user"    <?= $row['hak_akses']==='user'    ? 'selected' : '' ?>>User</option>
                </select>
              </td>
              <?php endif; ?>
            </tr>
            <?php endif; ?>
          <?php endforeach; ?>
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
          <a class="page-link" href="?url=admin/hak_akses&page=<?= $data['pagination']['page']-1 ?>&q=<?= urlencode($data['pagination']['search']) ?>">« Prev</a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $data['pagination']['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $data['pagination']['page']) ? 'active' : '' ?>">
          <a class="page-link" href="?url=admin/hak_akses&page=<?= $i ?>&q=<?= urlencode($data['pagination']['search']) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <?php if ($data['pagination']['page'] < $data['pagination']['totalPage']): ?>
        <li class="page-item">
          <a class="page-link" href="?url=admin/hak_akses&page=<?= $data['pagination']['page']+1 ?>&q=<?= urlencode($data['pagination']['search']) ?>">Next »</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>

<script>
// === Ubah Hak Akses ===
function ubahHakAkses(username, newRole) {
  if (!confirm(`Ubah hak akses ${username} menjadi ${newRole}?`)) return;

  fetch('index.php?url=admin/updateHakAkses', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `username=${encodeURIComponent(username)}&hak_akses=${encodeURIComponent(newRole)}`
  })
  .then(res => res.ok ? location.reload() : alert('Gagal mengubah hak akses'))
  .catch(() => alert('Terjadi kesalahan saat mengubah hak akses'));
}

function ubahJabatan(username, jabatanId) {
  if (!confirm(`Ubah jabatan ${username} menjadi ${jabatanId}?`)) return;

  fetch('index.php?url=admin/updateJabatan', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `username=${encodeURIComponent(username)}&jabatan_id=${encodeURIComponent(jabatanId)}`
  })
  .then(res => res.ok ? location.reload() : alert('Gagal mengubah jabatan'))
  .catch(() => alert('Terjadi kesalahan saat mengubah jabatan'));
}

function ubahUnitKerja(username, unitKerjaId) {
  if (!confirm(`Ubah unit kerja ${username} menjadi ${unitKerjaId}?`)) return;

  fetch('index.php?url=admin/updateUnitKerja', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `username=${encodeURIComponent(username)}&unit_kerja_id=${encodeURIComponent(unitKerjaId)}`
  })
  .then(res => res.ok ? location.reload() : alert('Gagal mengubah unit kerja'))
  .catch(() => alert('Terjadi kesalahan saat mengubah unit kerja'));
}

// === Pencarian di tabel Hak Akses ===
document.getElementById('searchInput').addEventListener('keyup', function() {
  const keyword = this.value.toLowerCase();
  const rows = document.querySelectorAll("table tbody tr");

  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(keyword) ? "" : "none";
  });
});
</script>