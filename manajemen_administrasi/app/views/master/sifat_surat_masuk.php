<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';

// Guard akses view
if (!AccessControl::can('master.sifat_surat_masuk','view')) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data Sifat Surat Masuk.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-2">
      <h5 class="mb-0">Master Data: Sifat Surat Masuk</h5>
    </div>
    <div class="card-body p-0">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width: 50px;">No</th>
            <th>Sifat Surat</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; foreach ($data as $row): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td
              <?= AccessControl::can('master.sifat_surat_masuk','update') ? 'contenteditable="true"' : '' ?>
              data-id="<?= $row['id'] ?>"
              data-field="nama_sifat"
              class="<?= AccessControl::can('master.sifat_surat_masuk','update') ? 'editable' : '' ?>"
            >
              <?= isset($row['nama_sifat']) ? htmlspecialchars($row['nama_sifat']) : '-' ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if (AccessControl::can('master.sifat_surat_masuk','update')): ?>

<script>
document.querySelectorAll('.editable').forEach(cell => {
  cell.addEventListener('blur', function () {
    const id = this.dataset.id;
    const field = this.dataset.field;
    const value = this.textContent.trim();

    fetch(`index.php?url=master/update_inline&table=sifat_surat_masuk`, {
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