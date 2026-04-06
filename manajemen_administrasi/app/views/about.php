<?php
require_once __DIR__ . '/../helpers/version_helper.php';
$versions = getAllVersions(); // ambil semua versi dari awal sampai akhir
?>

<style>
  .version-description {
    white-space: pre-line;
    line-height: 1.1 !important;
    margin: 0 !important;
    padding: 0 !important;
    font-size: 0.9rem;
  }
  .table td, .table th {
    padding: 6px 10px !important;
    line-height: 1.2 !important;
    vertical-align: top !important;
  }
  .badge-version {
    font-size: 0.85rem;
    padding: 4px 8px;
    border-radius: 6px;
  }
  /* container scroll khusus isi tabel */
  #versionTableContainer {
    max-height: 400px;       /* tinggi area scroll */
    overflow-y: auto;        /* aktifkan scroll vertikal */
    scroll-behavior: smooth; /* scroll halus */
    border: 1px solid #dee2e6;
    border-radius: 0 0 6px 6px;
  }
</style>

<div class="container mt-4">
  <div class="card shadow-lg border-0 rounded-3">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i> Tentang Aplikasi</h5>
      <?php if (strtolower($_SESSION['user']['hak_akses'] ?? '') === 'administrator'): ?>
        <button type="button" class="btn btn-light btn-sm text-primary"
                data-toggle="modal"
                data-target="#modalTambahVersi">
          <i class="fas fa-plus-circle"></i> Tambah Versi
        </button>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <p class="text-muted lh-sm mb-2">
        <strong class="text-primary">Manajemen Administrasi</strong> dikembangkan untuk mendukung pengelolaan data pegawai, surat, dan administrasi institusi secara terintegrasi.
      </p>

      <div class="row mb-2">
        <div class="col-md-4">
          <div class="d-flex align-items-center mb-1">
            <i class="fas fa-user-cog text-primary mr-2"></i>
            <span><strong>Pengembang:</strong> IT Pengembang</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="d-flex align-items-center mb-1">
            <i class="fas fa-tools text-primary mr-2"></i>
            <span><strong>Teknologi:</strong> PHP 8, MySQL, Bootstrap, AdminLTE</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="d-flex align-items-center mb-1">
            <i class="fas fa-check-circle text-success mr-2"></i>
            <span><strong>Status:</strong> Stable Release</span>
          </div>
        </div>
      </div>

      <!-- Riwayat Versi -->
      <h6 class="mt-3 fw-bold text-primary"><i class="fas fa-history mr-2"></i> Riwayat Versi</h6>
      <div class="table-responsive">
        <!-- Header tetap -->
        <table class="table table-hover table-bordered align-middle mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width: 15%">Versi</th>
              <th style="width: 20%">Tanggal Rilis</th>
              <th>Deskripsi</th>
            </tr>
          </thead>
        </table>
        <!-- Isi tabel scroll -->
        <div id="versionTableContainer">
          <table class="table table-hover table-bordered align-middle mb-0">
            <tbody>
              <?php foreach ($versions as $v): ?>
              <tr>
                <td style="width:15%"><span class="badge badge-success badge-version"><?= htmlspecialchars($v['version']) ?></span></td>
                <td style="width:20%"><?= htmlspecialchars($v['release_date']) ?></td>
                <td class="version-description"><?= nl2br(htmlspecialchars($v['description'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-3">
        <a href="<?= BASE_URL ?>/index.php?url=dashboard" class="btn btn-outline-primary btn-sm">
          <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
        </a>
      </div>
    </div>
  </div>
</div>

<?php if (strtolower($_SESSION['user']['hak_akses'] ?? '') === 'administrator'): ?>
<!-- Modal Tambah Versi -->
<div class="modal fade" id="modalTambahVersi" tabindex="-1" role="dialog" aria-labelledby="modalTambahVersiLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <form method="post" action="<?= BASE_URL ?>/index.php?url=about/add_version">
      <div class="modal-content shadow-lg border-0 rounded-3">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalTambahVersiLabel"><i class="fas fa-plus-circle mr-2"></i> Tambah Versi Aplikasi</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-2">
            <label for="version" class="fw-bold">Versi</label>
            <input type="text" id="version" name="version" class="form-control form-control-sm" placeholder="Misal: 1.2.1" required>
          </div>
          <div class="form-group mb-2">
            <label for="release_date" class="fw-bold">Tanggal Rilis</label>
            <input type="date" id="release_date" name="release_date" class="form-control form-control-sm" required>
          </div>
          <div class="form-group mb-2">
            <label for="description" class="fw-bold">Deskripsi</label>
            <textarea id="description" name="description" class="form-control form-control-sm version-description" rows="5"
              placeholder="- Perbaikan fitur A&#10;- Penambahan fitur B&#10>- Perubahan tampilan C" required></textarea>
            <small class="text-muted">Gunakan format poin dengan tanda <code>-</code> di awal baris.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save mr-1"></i> Simpan</button>
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Batal</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Auto scroll loop hanya isi tabel -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  function autoScrollTable() {
    const container = document.getElementById('versionTableContainer');
    const table = container.querySelector('table');

    // Gandakan isi tabel untuk efek loop
    const clone = table.cloneNode(true);
    container.appendChild(clone);

    let scrollSpeed = 0.8;

    setInterval(function() {
      container.scrollTop += scrollSpeed;

      // kalau sudah melewati tinggi tabel pertama, reset ke awal
      if (container.scrollTop >= table.scrollHeight) {
        container.scrollTop = 0;
      }
    }, 30); // interval kecil = lebih halus
  }

  window.addEventListener('load', autoScrollTable);

</script>