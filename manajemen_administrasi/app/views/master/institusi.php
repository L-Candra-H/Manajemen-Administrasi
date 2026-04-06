<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../helpers/AccessControl.php';

if (!AccessControl::can('pengaturan.institusi','view')) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data Institusi.</div>";
  exit;
}

// Flag: apakah sudah ada data institusi
$hasInstitusi = !empty($institusi);
?>

<div class="container mt-4">
  <div class="card mt-3">
    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Daftar Institusi</h5>
      <?php if (AccessControl::can('pengaturan.institusi','create')): ?>
        <button type="button" class="btn btn-sm btn-success"
                data-toggle="modal"
                data-target="#formInstitusiModal"
                data-id=""
                data-nama=""
                data-sub=""
                data-alamat=""
                data-telepon=""
                data-logo=""
                <?= $hasInstitusi ? 'disabled' : '' ?>>
          Tambah Institusi
        </button>
      <?php endif; ?>
    </div>
    <div class="card-body p-0">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th>Nama</th>
            <th>Sub</th>
            <th>Alamat</th>
            <th>Telepon</th>
            <th>Logo</th>
            <?php if (AccessControl::can('pengaturan.institusi','update')): ?>
              <th>Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($institusi as $i): ?>
          <tr>
            <td><?= htmlspecialchars($i['nama_institusi']) ?></td>
            <td><?= htmlspecialchars($i['sub_institusi']) ?></td>
            <td><?= htmlspecialchars($i['alamat']) ?></td>
            <td><?= htmlspecialchars($i['telepon']) ?></td>
            <td>
              <?php if (!empty($i['logo'])): ?>
                <img src="<?= BASE_URL ?>/public/uploads/institusi_logo/<?= urlencode($i['logo']) ?>" alt="Logo" style="height:40px;">
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <?php if (AccessControl::can('pengaturan.institusi','update')): ?>
            <td>
              <button type="button" class="btn btn-sm btn-warning"
                      data-toggle="modal"
                      data-target="#formInstitusiModal"
                      data-id="<?= $i['id'] ?>"
                      data-nama="<?= htmlspecialchars($i['nama_institusi'], ENT_QUOTES) ?>"
                      data-sub="<?= htmlspecialchars($i['sub_institusi'], ENT_QUOTES) ?>"
                      data-alamat="<?= htmlspecialchars($i['alamat'], ENT_QUOTES) ?>"
                      data-telepon="<?= htmlspecialchars($i['telepon'], ENT_QUOTES) ?>"
                      data-logo="<?= htmlspecialchars($i['logo'], ENT_QUOTES) ?>">
                Edit
              </button>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Form Institusi -->
<div class="modal fade" id="formInstitusiModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Form Institusi</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form method="POST" action="<?= BASE_URL ?>/index.php?url=institusi/simpan" enctype="multipart/form-data">
          <input type="hidden" name="id" id="form-id">
          <div class="row">
            <div class="col-md-6">
              <label>Nama Institusi</label>
              <input type="text" name="nama_institusi" id="form-nama" class="form-control form-control-sm" required>

              <label class="mt-3">Alamat</label>
              <textarea name="alamat" id="form-alamat" class="form-control form-control-sm" rows="2"></textarea>
            </div>
            <div class="col-md-6">
              <label>Sub Institusi</label>
              <input type="text" name="sub_institusi" id="form-sub" class="form-control form-control-sm">

              <label class="mt-3">Telepon</label>
              <input type="text" name="telepon" id="form-telepon" class="form-control form-control-sm">

              <label class="mt-3">Logo (opsional)</label>
              <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
              <div id="logo-preview" class="mt-2"></div>
            </div>
          </div>
          <div class="mt-4">
            <button type="submit" class="btn btn-success btn-sm" id="form-submit-btn">Simpan</button>
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
  $('#formInstitusiModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    if (!button || !button.length) return;

    var id = button.attr('data-id') || '';
    $('#form-id').val(id);
    $('#form-nama').val(button.attr('data-nama') || '');
    $('#form-sub').val(button.attr('data-sub') || '');
    $('#form-alamat').val(button.attr('data-alamat') || '');
    $('#form-telepon').val(button.attr('data-telepon') || '');

    var logo = button.attr('data-logo');
    if (logo && logo !== 'null' && logo !== 'undefined') {
      $('#logo-preview').html(`<img src="<?= BASE_URL ?>/public/uploads/institusi_logo/${logo}" style="height:40px;">`);
    } else {
      $('#logo-preview').html('');
    }

    $('#form-submit-btn').text(id ? 'Update' : 'Simpan');
  });

  $('#formInstitusiModal').on('hidden.bs.modal', function () {
    $('#form-id').val('');
    $('#form-nama').val('');
    $('#form-sub').val('');
    $('#form-alamat').val('');
    $('#form-telepon').val('');
    $('#logo-preview').html('');
    $('#form-submit-btn').text('Simpan');
  });
});
</script>