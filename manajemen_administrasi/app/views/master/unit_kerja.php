<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../helpers/AccessControl.php';

// Guard akses view
if (!AccessControl::can('master.unit_kerja','view')) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data Unit Kerja.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2">
      <h5 class="mb-0">Master Data: Unit Kerja</h5>
    </div>
    <div class="card-body p-0">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width: 50px;">No</th>
            <th>Nama Unit Kerja</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; foreach ($data as $row): ?>
            <?php if ($row['id'] == 2 || $row['nama_unit'] === 'IT Departemen') continue; ?>
            <tr>
              <td><?= ($pagination['page']-1)*10 + $no++ ?></td>
              <td
                <?= AccessControl::can('master.unit_kerja','update') ? 'contenteditable="true"' : '' ?>
                data-id="<?= $row['id'] ?>"
                data-field="nama_unit"
                class="<?= AccessControl::can('master.unit_kerja','update') ? 'editable' : '' ?>"
              >
                <?= isset($row['nama_unit']) ? htmlspecialchars($row['nama_unit']) : '-' ?>
              </td>
            </tr>
          <?php endforeach; ?>

          <!-- ✅ Baris kosong untuk tambah otomatis -->
          <?php if (AccessControl::can('master.unit_kerja','create')): ?>
          <tr>
            <td>+</td>
            <td contenteditable="true"
                data-id="new"
                data-field="nama_unit"
                class="editable-new text-muted">
                (isi nama unit baru...)
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if (AccessControl::can('master.unit_kerja','update')): ?>
<script>
document.querySelectorAll('.editable').forEach(cell => {
  cell.addEventListener('blur', function () {
    const id = this.dataset.id;
    const field = this.dataset.field;
    const value = this.textContent.trim();

    fetch(`index.php?url=master/update_inline&table=unit_kerja`, {
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

<script>
document.querySelectorAll('.editable-new').forEach(cell => {
  cell.addEventListener('blur', function () {
    const value = this.textContent.trim();
    if (!value || value === '(isi nama unit baru...)') return;

    fetch(`index.php?url=master/unit_kerja_tambah`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `nama_unit=${encodeURIComponent(value)}`
    })
    .then(res => res.text())
    .then(msg => {
      console.log('Tambah:', msg);
      location.reload(); // refresh supaya baris baru muncul di tabel
    })
    .catch(err => console.error('Gagal tambah:', err));
  });
});
</script>

<?php if (!empty($pagination)): ?>
<nav aria-label="Page navigation" class="mt-3">
  <ul class="pagination justify-content-center">
    <?php if ($pagination['page'] > 1): ?>
      <li class="page-item">
        <a class="page-link" href="?url=master/unit_kerja&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>">« Prev</a>
      </li>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
      <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
        <a class="page-link" href="?url=master/unit_kerja&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>

    <?php if ($pagination['page'] < $pagination['totalPage']): ?>
      <li class="page-item">
        <a class="page-link" href="?url=master/unit_kerja&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>">Next »</a>
      </li>
    <?php endif; ?>
  </ul>
</nav>
<?php endif; ?>