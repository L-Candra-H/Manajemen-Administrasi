<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../helpers/AccessControl.php';
$canViewAll = AccessControl::can('pegawai.daftar_aktif','view');
$canViewOwn = AccessControl::can('pegawai.daftar_aktif','view-own');

if (!$canViewAll && !$canViewOwn) {
  echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat daftar pegawai.</div>";
  exit;
}
?>

<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-dark text-white py-3">
      <div class="row align-items-center">
        <div class="col-md-6 d-flex align-items-center gap-2">
          <h5 class="mb-0">Daftar Pegawai</h5>
          <?php if (AccessControl::can('pegawai.daftar_aktif','create')): ?>
            <a href="index.php?url=kepegawaian/input" class="btn btn-sm btn-primary ms-2">
              <i class="fas fa-plus"></i> Tambah Pegawai
            </a>
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <form method="GET" action="index.php" class="d-flex justify-content-end">
            <input type="hidden" name="url" value="kepegawaian/daftar_pegawai">
            <input type="text" id="searchBox" name="q"
                   value="<?= htmlspecialchars($data['pagination']['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   class="form-control form-control-sm w-50 me-2" placeholder="Cari nama atau NIP">
          </form>
        </div>
      </div>
    </div>

    <div style="overflow-x:auto;">
      <table class="table table-bordered table-hover mb-0">
        <thead class="thead-light text-center">
          <tr>
            <th>Nomor Induk Pegawai</th>
            <th>Nama Pegawai</th>
            <th>Status kepegawaian</th>
            <th>Umur</th>
            <th>Lama Kerja</th>
            <th>Foto</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data['daftar_pegawai'])): ?>
            <?php foreach ($data['daftar_pegawai'] as $pegawai): ?>
              <?php
                $statusRaw = $pegawai['status_keaktifan'] ?? 'Aktif';
                $status = strtolower(trim($statusRaw));
                $nip = $pegawai['nomor_induk_pegawai'] ?? '';
                $nama = $pegawai['nama_lengkap'] ?? '';
                $photo = $pegawai['photo'] ?? '';
                $isOwner = ($_SESSION['user']['nomor_induk_pegawai'] ?? null) === ($pegawai['nomor_induk_pegawai'] ?? null);
              ?>
              <tr>
                <!-- NIP -->
                <td class="text-center"><?= htmlspecialchars($nip, ENT_QUOTES, 'UTF-8') ?></td>

                <!-- Nama -->
                <td><?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?></td>

                <!-- Status Kepegawaian -->
                <td><?= htmlspecialchars($pegawai['status_kepegawaian'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>

                <!-- Umur -->
                <td>
                  <?php if (!empty($pegawai['umur'])): ?>
                    <?= htmlspecialchars($pegawai['umur'], ENT_QUOTES, 'UTF-8') ?> tahun
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>

                <!-- Lama Kerja -->
                <td>
                  <?php
                    if (!empty($pegawai['tanggal_masuk'])) {
                      $masuk = new DateTime($pegawai['tanggal_masuk']);
                      $sekarang = new DateTime();
                      $interval = $sekarang->diff($masuk);
                      echo $interval->y . " tahun " . $interval->m . " bulan";
                    } else {
                      echo "-";
                    }
                  ?>
                </td>
              
                <!-- Foto -->
                <td class="text-center">
                  <?php if (!empty($photo)): ?>
                    <img src="<?= BASE_URL ?>/public/uploads/pegawai/foto/<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>"
                         alt="Foto Pegawai" style="height: 40px; border-radius: 4px;">
                  <?php else: ?>
                    <span class="text-muted">Tidak ada foto</span>
                  <?php endif; ?>
                </td>

                <!-- Status -->
                <td class="text-center">
                  <?php if (AccessControl::can('pegawai.daftar_aktif','update')): ?>
                    <?php if ($status === 'non aktif'): ?>
                      <span class="badge bg-danger" style="cursor:pointer"
                            onclick="openStatusModal('<?= htmlspecialchars($nip, ENT_QUOTES, 'UTF-8') ?>','Non Aktif')">
                        Non Aktif
                      </span>
                    <?php else: ?>
                      <span class="badge bg-success" style="cursor:pointer"
                            onclick="openStatusModal('<?= htmlspecialchars($nip, ENT_QUOTES, 'UTF-8') ?>','Aktif')">
                        Aktif
                      </span>
                    <?php endif; ?>
                  <?php else: ?>
                    <?php if ($status === 'non aktif'): ?>
                      <span class="badge bg-danger">Non Aktif</span>
                    <?php else: ?>
                      <span class="badge bg-success">Aktif</span>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>

                <!-- Aksi -->
                <td class="text-center">
                  <?php if (AccessControl::can('pegawai.daftar_aktif','update')): ?>
                    <a href="index.php?url=kepegawaian/input&nip=<?= urlencode($nip) ?>" 
                       class="btn btn-sm btn-warning">Edit</a>
                  <?php endif; ?>

                  <?php if (AccessControl::can('pegawai.daftar_aktif','print') 
                        || (AccessControl::can('pegawai.daftar_aktif','print-own') && $isOwner)): ?>
                      <a href="#" onclick="openPrintPopup('index.php?url=kepegawaian/cetak&nip=<?= urlencode($nip) ?>')" 
                         class="btn btn-sm btn-info">Cetak Biodata</a>
                  <?php endif; ?>

                  <?php if (AccessControl::can('pegawai.daftar_aktif','print')): ?> 
                    <a href="index.php?url=kepegawaian/kartu&nip=<?= urlencode($nip) ?>"  
                       target="_blank" class="btn btn-sm btn-secondary">
                       Cetak Kartu
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center text-muted">Belum ada data pegawai</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- navigasi pagination -->
<?php
$pagination = $data['pagination'] ?? null;
?>

<?php if (!empty($pagination)): ?>
  <nav aria-label="Page navigation" class="mt-3">
    <ul class="pagination justify-content-center">
      <!-- Tombol Prev -->
      <?php if ($pagination['page'] > 1): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=kepegawaian/daftar_pegawai&page=<?= $pagination['page'] - 1 ?>&q=<?= urlencode($pagination['search']) ?>">
            « Prev
          </a>
        </li>
      <?php endif; ?>

      <!-- Nomor halaman -->
      <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
        <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
          <a class="page-link"
             href="?url=kepegawaian/daftar_pegawai&page=<?= $i ?>&q=<?= urlencode($pagination['search']) ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>

      <!-- Tombol Next -->
      <?php if ($pagination['page'] < $pagination['totalPage']): ?>
        <li class="page-item">
          <a class="page-link"
             href="?url=kepegawaian/daftar_pegawai&page=<?= $pagination['page'] + 1 ?>&q=<?= urlencode($pagination['search']) ?>">
            Next »
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>

<script>
function openPrintPopup(url) {
  fetch(url)
    .then(response => response.text())
    .then(html => {
      const w = screen.availWidth;
      const h = screen.availHeight;
      const printWindow = window.open('', '', `width=${w},height=${h},top=0,left=0`);
      printWindow.document.open();
      printWindow.document.write(html);
      printWindow.document.close();
      printWindow.focus();
      printWindow.onload = function() {
        printWindow.print();
      };
    })
    .catch(err => {
      console.error('Gagal membuka popup cetak:', err);
      alert('Terjadi kesalahan saat membuka cetak biodata.');
    });
}
</script>

<?php if (AccessControl::can('pegawai.daftar_aktif','update')): ?>
<!-- Modal Ubah Status Pegawai -->
<div class="modal fade" id="statusModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="index.php?url=kepegawaian/updateStatusPegawai">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Ubah Status Pegawai</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="nomor_induk_pegawai" id="statusNIP">
          <div class="mb-3">
            <label>Status Keaktifan</label>
            <select name="status_keaktifan" class="form-select" onchange="toggleAlasan(this.value)">
              <option value="Aktif">Aktif</option>
              <option value="Non Aktif">Non Aktif</option>
            </select>
          </div>
          <div class="mb-3" id="alasanGroup" style="display:none;">
            <label>Alasan Non Aktif</label>
            <textarea name="alasan_non_aktif" class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
function openStatusModal(nip, status) {
  var modal = new bootstrap.Modal(document.getElementById('statusModal'));
  document.getElementById('statusNIP').value = nip;
  const alasanGroup = document.getElementById('alasanGroup');
  alasanGroup.style.display = (status === 'Non Aktif') ? 'block' : 'none';
  modal.show();
}
function toggleAlasan(val) {
  const alasanGroup = document.getElementById('alasanGroup');
  alasanGroup.style.display = (val === 'Non Aktif') ? 'block' : 'none';
}
</script>

<style>
  .table td, .table th {
    white-space: nowrap;
  }
</style>

<?php endif; ?>
