<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

// Guard akses view
if (!AccessControl::can('master.golongan_pegawai','view')) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data Golongan Pegawai.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2">
      <h5 class="mb-0">Master Data: Golongan Pegawai</h5>
    </div>
    <div class="card-body p-0">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width: 50px;">No</th>
            <th>Golongan</th>
            <th>Nilai</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data as $index => $row): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td
              <?= AccessControl::can('master.golongan_pegawai','update') ? 'contenteditable="true"' : '' ?>
              data-id="<?= $row['id'] ?>"
              data-field="golongan"
              class="<?= AccessControl::can('master.golongan_pegawai','update') ? 'editable' : '' ?>"
            >
              <?= isset($row['golongan']) ? htmlspecialchars($row['golongan']) : '-' ?>
            </td>
            <td
              <?= AccessControl::can('master.golongan_pegawai','update') ? 'contenteditable="true"' : '' ?>
              data-id="<?= $row['id'] ?>"
              data-field="nilai"
              class="<?= AccessControl::can('master.golongan_pegawai','update') ? 'editable' : '' ?>"
            >
              <?= isset($row['nilai']) ? htmlspecialchars($row['nilai']) : '-' ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if (!empty($pagination)): ?>
  <nav aria-label="Page navigation" class="mt-3">
    <ul class="pagination justify-content-center">
      <?php if ($pagination['page'] > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?url=master/golongan_pegawai&page=<?= $pagination['page']-1 ?>&q=<?= urlencode($pagination['search']) ?>">« Prev</a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link" href="?url=master/golongan_pegawai&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link" href="?url=master/golongan_pegawai&page=<?= $pagination['page']+1 ?>&q=<?= urlencode($pagination['search']) ?>">Next »</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>

<?php if (AccessControl::can('master.golongan_pegawai','update')): ?>
<script>
document.querySelectorAll('.editable').forEach(cell => {
  cell.addEventListener('blur', function () {
    const id = this.dataset.id;
    const field = this.dataset.field;
    const value = this.textContent.trim();

    fetch(`index.php?url=master/update_inline&table=golongan_pegawai`, {
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
