<?php
require_once __DIR__ . '/../helpers/AccessControl.php';

class SuratkeluarController extends Controller
{
    private $model;

    public function __construct()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?url=auth/login');
            exit;
        }
        $this->model = $this->model('SuratKeluarModel');
    }

    // === INDEX: daftar surat keluar ===
    public function index()
    {
        if (!AccessControl::can('surat.keluar','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5;
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        $totalData = $this->model->countAll($search);
        $totalPage = ceil($totalData / $perPage);

        $surat = $this->model->getPaginated($perPage, $offset, $search);

        $statusList = $this->model('StatusSuratModel')->getAll();
        $statusMap = [];
        $statusBadge = [];

        foreach ($statusList as $s) {
            $statusMap[$s['id']] = $s['nama_status'];
            switch (strtolower($s['nama_status'])) {
                case 'diarsipkan':  $statusBadge[$s['id']] = 'danger'; break;
                case 'dikirimkan':  $statusBadge[$s['id']] = 'primary'; break;
                case 'diproseskan': $statusBadge[$s['id']] = 'warning'; break;
                case 'diteruskan':  $statusBadge[$s['id']] = 'info'; break;
                default:            $statusBadge[$s['id']] = 'secondary';
            }
        }

        $this->view('surat_keluar/daftar_surat_keluar', [
            'surat'        => $surat,
            'statusMap'    => $statusMap,
            'statusBadge'  => $statusBadge,
            'pagination'   => [
                'page'      => $page,
                'totalPage' => max($totalPage, 1),
                'search'    => $search
            ]
        ]);
    }

    // === CREATE: tampilkan form input ===
    public function create()
    {
        if (!AccessControl::can('surat.keluar','create')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $jenisSurat  = $this->model('JenisSuratKeluarModel')->getAll();
        $statusSurat = $this->model('StatusSuratModel')->getAll();

        $this->view('surat_keluar/input_surat_keluar', [
            'jenis_surat'  => $jenisSurat,
            'status_surat' => $statusSurat
        ]);
    }

    // === STORE: simpan data baru ===
    public function store()
    {
        if (!AccessControl::can('surat.keluar','create')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data  = $_POST;
        $files = $_FILES;

        $tanggalSurat = !empty($data['tanggal_surat']) ? $data['tanggal_surat'] : date('Y-m-d');
        $jenisKode    = $data['jenis_surat_kode'] ?? 'GEN';

        // Insert surat keluar dengan counter
        $saveData = [
            'jenis_surat_id'  => $data['jenis_surat_id'] ?? null,
            'nomor_surat'     => $data['nomor_surat'] ?? null,
            'tanggal_surat'   => $tanggalSurat,
            'tujuan_surat'    => $data['tujuan_surat'] ?? null,
            'status_surat_id' => $data['status_surat_id'] ?? null,
            'berkas_surat'    => null
        ];
        $id = $this->model->saveWithCounter($saveData);

        // Ambil kembali record untuk dapat nomor agenda
        $suratBaru = $this->model->getById($id);

        // Upload file setelah record tersimpan
        if (!empty($files['berkas_surat']['tmp_name'])) {
            $urut     = $this->model->parseUrutFromNomorAgenda($suratBaru['nomor_agenda']);
            $fileName = $this->model->buildNamaFile($jenisKode, $urut);
            $tahun    = date('Y', strtotime($tanggalSurat));
            $uploadDir = dirname(__DIR__, 2) . "/public/uploads/dokumen/surat_keluar/$tahun/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $target = $uploadDir . $fileName;
            if (move_uploaded_file($files['berkas_surat']['tmp_name'], $target)) {
                $this->model->updateFile($id, $fileName);
            } else {
                echo "<div class='alert alert-danger'>Upload berkas gagal.</div>";
                return;
            }
        }

        $this->redirect('suratkeluar/index');
    }

    // === UPDATE STATUS: ubah status surat keluar ===
    public function updateStatus()
    {
        if (!AccessControl::can('surat.keluar','update')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $id       = $_POST['id'] ?? null;
        $statusId = $_POST['status_surat_id'] ?? null;

        if ($id && $statusId) {
            $this->model->updateStatus($id, $statusId);
        }

        $this->redirect('suratkeluar/index');
    }

    public function edit($id = null)
    {
        if (!AccessControl::can('surat.keluar','update')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        if ($id === null) {
            $id = $_GET['id'] ?? null;
        }

        if (!$id || !is_numeric($id)) {
            echo "<div class='alert alert-danger'>ID surat tidak valid.</div>";
            return;
        }

        $surat = $this->model->getById($id);
        if (!$surat) {
            echo "<div class='alert alert-danger'>Data surat tidak ditemukan.</div>";
            return;
        }

        if (!empty($surat['tanggal_diarsipkan'])) {
            echo "<div class='alert alert-danger'>Surat sudah diarsipkan dan tidak dapat diedit.</div>";
            return;
        }

        $jenisSurat  = $this->model('JenisSuratKeluarModel')->getAll();
        $statusSurat = $this->model('StatusSuratModel')->getAll();

        $this->view('surat_keluar/input_surat_keluar', [
            'surat'        => $surat,
            'jenis_surat'  => $jenisSurat,
            'status_surat' => $statusSurat
        ]);
    }

    public function update()
    {
        if (!AccessControl::can('surat.keluar','update')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        if (empty($_POST['id'])) {
            echo "<div class='alert alert-danger'>ID surat tidak diberikan.</div>";
            return;
        }

        $id    = $_POST['id'];
        $data  = $_POST;
        $files = $_FILES;

        $tanggalSurat = !empty($data['tanggal_surat']) ? $data['tanggal_surat'] : date('Y-m-d');

        $pathRelatif = $data['berkas_surat_lama'] ?? null;
        if (!empty($files['berkas_surat']['tmp_name'])) {
            $tahun     = date('Y', strtotime($tanggalSurat));
            $jenisKode = $data['jenis_surat_kode'] ?? 'GEN';

            // Ambil nomor agenda untuk konsistensi nama file
            $surat = $this->model->getById($id);
            $urut  = $this->model->parseUrutFromNomorAgenda($surat['nomor_agenda']);
            $fileName  = $this->model->buildNamaFile($jenisKode, $urut);

            $uploadDir = dirname(__DIR__, 2) . "/public/uploads/dokumen/surat_keluar/$tahun/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $target = $uploadDir . $fileName;
            if (move_uploaded_file($files['berkas_surat']['tmp_name'], $target)) {
                $pathRelatif = $fileName;
            } else {
                echo "<div class='alert alert-danger'>Upload berkas gagal.</div>";
                return;
            }
        }

        // Tanggal arsip jika status = Diarsipkan
        $statusDiarsipkanId = $this->model('StatusSuratModel')->getIdByNama('Diarsipkan');
        $tanggalArsip = ($data['status_surat_id'] == $statusDiarsipkanId) ? date('Y-m-d') : null;

        $updateData = [
            'jenis_surat_id'     => $data['jenis_surat_id'] ?? null,
            'nomor_surat'        => $data['nomor_surat'] ?? null,
            'tanggal_surat'      => $tanggalSurat,
            'tujuan_surat'       => $data['tujuan_surat'] ?? null,
            'status_surat_id'    => $data['status_surat_id'] ?? null,
            'berkas_surat'       => $pathRelatif,
            'tanggal_diarsipkan' => $tanggalArsip
        ];

        $this->model->update($id, $updateData);
        $this->redirect('suratkeluar/index');
    }

    public function detail($id = null)
    {
        if (!AccessControl::can('surat.keluar','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat detail surat keluar.</div>";
            exit;
        }

        if ($id === null) {
            $this->redirect('suratkeluar/index');
            return;
        }

        $model        = $this->model('SuratKeluarModel');
        $jenisModel   = $this->model('JenisSuratKeluarModel');
        $statusModel  = $this->model('StatusSuratModel');
        $userModel    = $this->model('UserModel');

        $surat        = $model->findById($id);
        $jenis_surat  = $jenisModel->getAll();
        $status_surat = $statusModel->getAll();
        $jabatan_unit = $userModel->getAllWithJabatanUnit();

        $this->view('surat_keluar/input_surat_keluar', compact('surat','jenis_surat','status_surat','jabatan_unit'));
    }
}