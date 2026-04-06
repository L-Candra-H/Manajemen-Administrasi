<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

if (!AccessControl::can('master.lama_kerja','view')) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data Lama Kerja.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2">
      <h5 class="mb-0">Master Data: Lama Kerja</h5>
    </div>
    <div class="card-body p-0">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width: 50px;">No</th>
            <th>Lama Kerja</th>
            <th>Nilai</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data as $index => $row): ?>
          <tr>
            <td><?= ($index + 1) + (($page - 1) * 6) ?></td>
            <td
              <?= AccessControl::can('master.lama_kerja','update') ? 'contenteditable="true"' : '' ?>
              data-id="<?= $row['id'] ?>"
              data-field="rentang"
              class="<?= AccessControl::can('master.lama_kerja','update') ? 'editable' : '' ?>"
            >
              <?= isset($row['rentang']) ? htmlspecialchars($row['rentang']) : '-' ?>
            </td>
            <td
              <?= AccessControl::can('master.lama_kerja','update') ? 'contenteditable="true"' : '' ?>
              data-id="<?= $row['id'] ?>"
              data-field="nilai"
              class="<?= AccessControl::can('master.lama_kerja','update') ? 'editable' : '' ?>"
            >
              <?= isset($row['nilai']) ? htmlspecialchars($row['nilai']) : '-' ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      <nav>
        <ul class="pagination justify-content-center mb-0">
          <!-- Tombol Prev -->
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="index.php?url=master/lama_kerja&page=<?= $page - 1 ?>">Prev</a>
            </li>
          <?php endif; ?>

          <!-- Nomor halaman -->
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="index.php?url=master/lama_kerja&page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <!-- Tombol Next -->
          <?php if ($page < $totalPages): ?>
            <li class="page-item">
              <a class="page-link" href="index.php?url=master/lama_kerja&page=<?= $page + 1 ?>">Next</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>

  </div>
</div>

<?php if (AccessControl::can('master.lama_kerja','update')): ?>
<script>
document.querySelectorAll('.editable').forEach(cell => {
  cell.addEventListener('blur', function () {
    const id = this.dataset.id;
    const field = this.dataset.field;
    const value = this.textContent.trim();

    fetch(`index.php?url=master/update_inline&table=lama_kerja`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${id}&field=${field}&value=${encodeURIComponent(value)}`
    })
    .then(res => res.text())
    .then(msg => console.log('Update:', msg))
    .catch(err => console.error('Gagal update:', err));
  });
});
</script>
<?php endif; ?>
