<?php
require_once __DIR__ . '/../helpers/AccessControl.php';

class SuratmasukController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?url=auth/login');
            exit;
        }
    }

    // Tampilkan daftar surat masuk + pencarian
    public function index()
    {
        if (!AccessControl::can('surat.masuk','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $model = $this->model('SuratMasukModel');
        $q = $_GET['q'] ?? '';
        $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5;
        $offset  = ($page - 1) * $perPage;

        $userRole    = strtolower(trim($_SESSION['user']['hak_akses'] ?? ''));
        $userJabatan = strtolower(trim($_SESSION['user']['nama_jabatan'] ?? ''));
        $unitId      = $_SESSION['user']['unit_id'] ?? null;

        // Role/jabatan bebas akses: Administrator, Admin2, Admin3, Direktur
        if (in_array($userRole, ['administrator','admin2','admin3']) || $userJabatan === 'direktur') {
            $totalData = $model->countAll($q);
            $surat     = $model->getPaginated($perPage, $offset, $q);

        } elseif (in_array($userJabatan, ['kabid','karu','kaunit','koord','ka.bid','ka.ru','ka.unit','koordinator']) && $unitId) {
            $totalData = $model->countAllByUnit($unitId, $q);
            $surat     = $model->getPaginatedByUnit($unitId, $perPage, $offset, $q);

        } else {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $totalPage = ceil($totalData / $perPage);

        $statusMap = [
            1 => 'Diteruskan',
            2 => 'Diproseskan',
            3 => 'Dikirimkan',
            4 => 'Diarsipkan'
        ];
        $statusBadge = [
            1 => 'info',
            2 => 'warning',
            3 => 'primary',
            4 => 'danger'
        ];

        $pagination = [
            'page'      => $page,
            'totalPage' => max($totalPage, 1),
            'search'    => $q
        ];

        $this->view('surat_masuk/daftar_surat_masuk',
            compact('surat','statusMap','statusBadge','q','pagination'));
    }

    // Form tambah surat masuk
    public function create()
    {
        if (!AccessControl::can('surat.masuk','create')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $sifatModel   = $this->model('SifatSuratMasukModel');
        $statusModel  = $this->model('StatusSuratModel');
        $userModel    = $this->model('UserModel');

        $sifat_surat  = $sifatModel->getAll();
        $status_surat = $statusModel->getAll();
        $jabatan_unit = $userModel->getAllWithJabatanUnit();

        $this->view('surat_masuk/input_surat_masuk', compact('sifat_surat','status_surat','jabatan_unit'));
    }

    private function getDirekturJabatanId()
    {
        $userModel = $this->model('UserModel');
        $direktur  = $userModel->findByJabatan('Direktur');
        return $direktur['id'] ?? null;
    }

    // Simpan surat masuk baru
    public function store()
    {
        if (!AccessControl::can('surat.masuk','create')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        // 🚫 Direktur tidak boleh create surat masuk baru
        if ($_SESSION['user']['hak_akses'] === 'direktur') {
            echo "<div class='alert alert-danger'>Direktur tidak boleh membuat surat masuk baru.</div>";
            $this->redirect('suratmasuk/index');
            return;
       }

        if (empty($_POST)) {
            $this->redirect('suratmasuk/index');
            return;
        }

        $model        = $this->model('SuratMasukModel');
        $tanggalSurat = $_POST['tanggal_surat'] ?? date('Y-m-d');

        // ambil gabungan disposisi (jabatan:unit)
        $gabungan   = $_POST['disposisi_gabungan'] ?? [];
        $jabatanIds = [];
        $unitIds    = [];

        foreach ($gabungan as $item) {
            [$jabatanId, $unitId] = explode(':', $item);
            $jabatanIds[] = $jabatanId;
            $unitIds[]    = $unitId;
        }

        // siapkan data tanpa nomor_agenda (biar model yang generate)
        $data = [
            'nomor_surat'                  => $_POST['nomor_surat'] ?? '',
            'tanggal_surat'                => $tanggalSurat,
            'tanggal_terima'               => $_POST['tanggal_terima'] ?? date('Y-m-d'),
            'asal_surat'                   => $_POST['asal_surat'] ?? '',
            'perihal_surat'                => $_POST['perihal_surat'] ?? '',
            'sifat_surat_id'               => $_POST['sifat_surat_id'] ?? null,
            'jumlah_halaman'               => $_POST['jumlah_halaman'] ?? null,
            'diteruskan_kepada_jabatan_id' => $_POST['diteruskan_kepada_jabatan_id'] ?? $this->getDirekturJabatanId(),
            'isi_disposisi'                => $_POST['isi_disposisi'] ?? null,
            'disposisi_jabatan_id'         => !empty($jabatanIds) ? implode(',', $jabatanIds) : null,
            'disposisi_unit_id'            => !empty($unitIds) ? implode(',', $unitIds) : null,
            'berkas_surat'                 => null,
            'status_surat_id'              => $_POST['status_surat_id'] ?? null,
            'tanggal_diarsipkan'           => null
        ];

        if (in_array($_SESSION['user']['hak_akses'], ['admin2','admin3'])) {
            $data['diteruskan_kepada_jabatan_id'] = $this->getDirekturJabatanId();
            $data['isi_disposisi'] = null; // boleh kosong
        }

        // simpan dengan transaksi counter
        $id = $model->saveWithCounter($data);

        // ambil nomor agenda & urut dari record baru
        $suratBaru = $model->findById($id);
        $urut      = $model->parseUrutFromNomorAgenda($suratBaru['nomor_agenda']);

        // upload file dengan nama sesuai urut
        if (!empty($_FILES['berkas_surat']['tmp_name'])) {
            $namaFile = $model->buildNamaFile($urut);
            $tahun    = date('Y', strtotime($tanggalSurat));
            $uploadDir = __DIR__ . "/../../public/uploads/dokumen/surat_masuk/{$tahun}/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            move_uploaded_file($_FILES['berkas_surat']['tmp_name'], $uploadDir . $namaFile);

            // update record dengan nama file
            $model->updateFile($id, $namaFile);

        }

        $this->redirect('suratmasuk/index');
    }

    public function edit($id)
    {
        if (!AccessControl::can('surat.masuk','update')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit Surat Masuk.</div>";
            return;
        }

        $model        = $this->model('SuratMasukModel');
        $sifatModel   = $this->model('SifatSuratMasukModel');
        $statusModel  = $this->model('StatusSuratModel');
        $userModel    = $this->model('UserModel');

        $surat        = $model->findById($id);
        if (!$surat) {
            echo "<div class='alert alert-danger'>Data surat tidak ditemukan.</div>";
            return;
        }

        if (!empty($surat['tanggal_diarsipkan'])) {
            echo "<div class='alert alert-danger'>Surat sudah diarsipkan dan tidak dapat diedit.</div>";
            return;
        }

        $sifat_surat  = $sifatModel->getAll();
        $status_surat = $statusModel->getAll();
        $jabatan_unit = $userModel->getAllWithJabatanUnit();

        $this->view('surat_masuk/input_surat_masuk', compact('surat','sifat_surat','status_surat','jabatan_unit'));
    }

    // Update surat masuk
    public function update()
    {
        if (!AccessControl::can('surat.masuk','update')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        if (empty($_POST['id'])) {
            echo "<div class='alert alert-danger'>ID surat tidak diberikan.</div>";
            return;
        }

        $model         = $this->model('SuratMasukModel');
        $id            = $_POST['id'];
        $nomorAgenda   = $_POST['nomor_agenda'] ?? null; // dari hidden field
        $urut          = $nomorAgenda ? $model->parseUrutFromNomorAgenda($nomorAgenda) : null;

        // Pakai tanggal surat dari form untuk folder
        $tanggalSurat  = $_POST['tanggal_surat'] ?? date('Y-m-d');
        $tahun         = date('Y', strtotime($tanggalSurat));
        $uploadDir     = __DIR__ . "/../../public/uploads/dokumen/surat_masuk/{$tahun}/";
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

        // Default: pakai file lama
        $namaFile = $_POST['berkas_surat_lama'] ?? null;

        // Jika ada file baru, overwrite file lama dengan nama berdasarkan urut lama (tidak naik counter)
        if (!empty($_FILES['berkas_surat']['tmp_name']) && $urut !== null) {
            $namaBaru = $model->buildNamaFile($urut);
            $target   = $uploadDir . $namaBaru;

            // Hapus file lama jika berbeda nama dan ada fisiknya
            if (!empty($namaFile) && $namaFile !== $namaBaru && file_exists($uploadDir . $namaFile)) {
                @unlink($uploadDir . $namaFile);
            }

            if (!move_uploaded_file($_FILES['berkas_surat']['tmp_name'], $target)) {
                echo "<div class='alert alert-danger'>Upload berkas gagal.</div>";
                return;
            }
            $namaFile = $namaBaru;

            // ✅ update kolom berkas_surat di DB
            $model->updateFile($id, $namaFile);
        }

        // ambil gabungan disposisi (jabatan:unit)
        $gabungan   = $_POST['disposisi_gabungan'] ?? [];
        $jabatanIds = [];
        $unitIds    = [];

        foreach ($gabungan as $item) {
            [$jabatanId, $unitId] = explode(':', $item);
            $jabatanIds[] = $jabatanId;
            $unitIds[]    = $unitId;
        }

        $data = [
            // nomor_agenda tidak diubah saat update
            'nomor_surat'                  => $_POST['nomor_surat'] ?? '',
            'tanggal_surat'                => $tanggalSurat,
            'tanggal_terima'               => $_POST['tanggal_terima'] ?? date('Y-m-d'),
            'asal_surat'                   => $_POST['asal_surat'] ?? '',
            'perihal_surat'                => $_POST['perihal_surat'] ?? '',
            'sifat_surat_id'               => $_POST['sifat_surat_id'] ?? null,
            'jumlah_halaman'               => $_POST['jumlah_halaman'] ?? null,
            'diteruskan_kepada_jabatan_id' => $_POST['diteruskan_kepada_jabatan_id'] ?? null,
            'isi_disposisi'                => $_POST['isi_disposisi'] ?? null,
            'disposisi_jabatan_id'         => !empty($jabatanIds) ? implode(',', $jabatanIds) : null,
            'disposisi_unit_id'            => !empty($unitIds) ? implode(',', $unitIds) : null,
            'berkas_surat'                 => $namaFile,
            'status_surat_id'              => $_POST['status_surat_id'] ?? null,
            'tanggal_diarsipkan'           => null
        ];

        // enforce aturan untuk admin2/admin3
        if (in_array($_SESSION['user']['hak_akses'], ['admin2','admin3'])) {
            $data['diteruskan_kepada_jabatan_id'] = $this->getDirekturJabatanId();
            $data['isi_disposisi'] = null;
        }

        // enforce aturan untuk Direktur
        if ($_SESSION['user']['hak_akses'] === 'direktur') {
            // Direktur hanya boleh isi disposisi
            $data = [
                'isi_disposisi'        => $_POST['isi_disposisi'] ?? null,
                'disposisi_jabatan_id' => !empty($jabatanIds) ? implode(',', $jabatanIds) : null,
                'disposisi_unit_id'    => !empty($unitIds) ? implode(',', $unitIds) : null,
                'berkas_surat'         => $_POST['berkas_surat_lama'] ?? null, // readonly
                'diteruskan_kepada_jabatan_id' => $this->getDirekturJabatanId(),
                'status_surat_id'      => $_POST['status_surat_id'] ?? null,
                'tanggal_diarsipkan'   => null
            ];

        }

        $userRole    = strtolower($_SESSION['user']['hak_akses'] ?? '');
        $userJabatan = strtolower($_SESSION['user']['nama_jabatan'] ?? '');
        $isAdmin2or3 = in_array($userRole, ['admin2','admin3']);
        $isDirektur  = ($userJabatan === 'direktur');

        if ($hasJabatanUnit) {
            $unitId = $_SESSION['user']['unit_id'] ?? null;
            $surat = $model->findByIdForUnit($id, $unitId);
            if (!$surat) {
                echo "<div class='alert alert-danger'>Akses ditolak. Surat ini bukan milik unit Anda.</div>";
                return;
            }
        } else {
            $surat = $model->findById($id);
        }

        $isDiarsipkan = ($surat['status_surat_id'] == 4);

        if ($isAdmin2or3 && !$isDiarsipkan) {
            // Admin2/3 → update penuh
            $model->update($id, $data);
        } elseif ($isDirektur && !$isDiarsipkan) {
            // Direktur → hanya update disposisi
            $model->updateDisposisi($id, [
                'isi_disposisi'        => $_POST['isi_disposisi'] ?? null,
                'disposisi_jabatan_id' => !empty($jabatanIds) ? implode(',', $jabatanIds) : null,
                'disposisi_unit_id'    => !empty($unitIds) ? implode(',', $unitIds) : null
            ]);

        } else {
            // User biasa atau surat diarsipkan → tidak boleh update
            $this->redirect("suratmasuk/detail/$id");
            return;
        }

        $this->redirect('suratmasuk/index');

    }

    // Update status surat masuk via modal
    public function updateStatus()
    {
        if (!AccessControl::can('surat.masuk','update')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $model    = $this->model('SuratMasukModel');
        $id       = $_POST['id'] ?? null;
        $statusId = $_POST['status_surat_id'] ?? null;

        if (!$id || !$statusId) {
            echo "<div class='alert alert-danger'>Data status tidak lengkap.</div>";
            exit;
        }

        $model->updateStatus($id, $statusId);
        $this->redirect('suratmasuk/index');
    }

    // Hapus surat masuk
    public function delete($id)
    {
        if (!AccessControl::can('surat.masuk','delete')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $model = $this->model('SuratMasukModel');
        $model->delete($id);
        $this->redirect('suratmasuk/index');
    }

    // Lihat detail surat masuk (read-only)
    public function detail($id = null)
    {
        if (!AccessControl::can('surat.masuk','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        if ($id === null) {
            $this->redirect('suratmasuk/index');
            return;
        }

        $model        = $this->model('SuratMasukModel');
        $sifatModel   = $this->model('SifatSuratMasukModel');
        $statusModel  = $this->model('StatusSuratModel');
        $userModel    = $this->model('UserModel');

        $userRole    = strtolower(trim($_SESSION['user']['hak_akses'] ?? ''));
        $userJabatanRaw = strtolower(trim($_SESSION['user']['nama_jabatan'] ?? ''));
        $unitId      = $_SESSION['user']['unit_id'] ?? null;

        // mapping variasi penulisan jabatan
        $map = [
            'ka.bid'       => 'ka.bid',
            'kabid'        => 'ka.bid',
            'ka.ru'        => 'ka.ru',
            'karu'         => 'ka.ru',
            'ka.unit'      => 'ka.unit',
            'kaunit'       => 'ka.unit',
            'koord'        => 'koord',
            'koordinator'  => 'koord',
            'direktur'     => 'direktur',
            'administrator'=> 'administrator',
            'kary'         => 'karyawan',
            'karyawan'     => 'karyawan'
        ];
        $userJabatan = $map[$userJabatanRaw] ?? $userJabatanRaw;

        $jabatanBolehAkses = ['ka.bid','ka.ru','ka.unit','koord'];
        $hasJabatanUnit    = in_array($userJabatan, $jabatanBolehAkses);

        $isAdmin2or3 = in_array($userRole, ['admin2','admin3']);
        $isDirektur  = ($userJabatan === 'direktur');

        // 🔑 Ambil surat sesuai role/jabatan
        if ($isAdmin2or3 || $isDirektur || $userRole === 'administrator') {
            $surat = $model->findById($id);
        } elseif ($hasJabatanUnit && $unitId) {
            $surat = $model->findByIdForUnit($id, $unitId);
        } else {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak pernah mengakses Surat Masuk.</div>";
            return;
        }

        $sifat_surat  = $sifatModel->getAll();
        $status_surat = $statusModel->getAll();
        $jabatan_unit = $userModel->getAllWithJabatanUnit();

        $isDiarsipkan = ($surat['status_surat_id'] == 4);

        $isReadonly = false;
        $editableDisposisiOnly = false;

        if ($isDiarsipkan) {
            $isReadonly = true;
        } elseif ($isDirektur && !$isDiarsipkan) {
            $editableDisposisiOnly = true;
        } elseif ($isDirektur && $isDiarsipkan) {
            $isReadonly = true;
        } elseif ($isAdmin2or3) {
            $isReadonly = false;
        } elseif ($hasJabatanUnit) {
            $isReadonly = true; // Ka.Bid, Ka.Ru, Ka.Unit, Koord → readonly
        } else {
            $isReadonly = true; // default: readonly
        }

        $this->view('surat_masuk/input_surat_masuk',
            compact('surat','sifat_surat','status_surat','jabatan_unit','isReadonly','editableDisposisiOnly'));

    }
}
