<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

// Guard akses view
if (!AccessControl::can('master.jabatan','view')) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data Jabatan.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2">
      <h5 class="mb-0">Master Data: Jabatan</h5>
    </div>
    <div class="card-body p-0">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width: 50px;">No</th>
            <th>Nama Jabatan</th>
            <th>Keterangan</th>
            <th>Nilai</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; foreach ($data as $row): ?>
            <?php if (strtolower($row['nama_jabatan']) === 'administrator') continue; ?>
            <tr>
              <td><?= $no++ ?></td>
              <td
                <?= AccessControl::can('master.jabatan','update') ? 'contenteditable="true"' : '' ?>
                data-id="<?= $row['id'] ?>"
                data-field="nama_jabatan"
                class="<?= AccessControl::can('master.jabatan','update') ? 'editable' : '' ?>"
              >
                <?= htmlspecialchars($row['nama_jabatan']) ?>
              </td>
              <td
                <?= AccessControl::can('master.jabatan','update') ? 'contenteditable="true"' : '' ?>
                data-id="<?= $row['id'] ?>"
                data-field="keterangan"
                class="<?= AccessControl::can('master.jabatan','update') ? 'editable' : '' ?>"
              >
                <?= isset($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-' ?>
              </td>
              <td
                <?= AccessControl::can('master.jabatan','update') ? 'contenteditable="true"' : '' ?>
                data-id="<?= $row['id'] ?>"
                data-field="nilai"
                class="<?= AccessControl::can('master.jabatan','update') ? 'editable' : '' ?>"
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

<?php if (AccessControl::can('master.jabatan','update')): ?>
<script>
document.querySelectorAll('.editable').forEach(cell => {
  cell.addEventListener('blur', function () {
    const id = this.dataset.id;
    const field = this.dataset.field;
    const value = this.textContent.trim();

    fetch(`index.php?url=master/update_inline&table=jabatan`, {
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
