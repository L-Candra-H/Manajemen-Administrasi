<?php
require_once __DIR__ . '/../helpers/AccessControl.php';

class SuratinternalController extends Controller
{
    private $model;

    public function __construct()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?url=auth/login');
            exit;
        }
        $this->model = $this->model('SuratInternalModel');
    }

    // === INDEX: daftar surat internal ===
    public function index()
    {
        if (!AccessControl::can('surat.internal','view') 
            && !AccessControl::can('surat.internal','view-own')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data Surat Internal.</div>";
            exit;
        }

        $unitId = $_SESSION['user']['unit_id'] ?? null;
        $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5;
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        if (AccessControl::can('surat.internal','view')) {
            $totalData = $this->model->countAll($search);
            $surat     = $this->model->getPaginated($perPage, $offset, $search);
        } else {
            $totalData = $this->model->countAllByUnit($unitId, $search);
            $surat     = $this->model->getPaginatedByUnit($unitId, $perPage, $offset, $search);
        }

        $totalPage = ceil($totalData / $perPage);

        $pagination = [
            'page'      => $page,
            'totalPage' => max($totalPage, 1),
            'search'    => $search
        ];

        $this->view('surat_internal/daftar_surat_internal', [
            'surat'      => $surat,
            'pagination' => $pagination
        ]);
    }

    // === CREATE: tampilkan form input ===
    public function create()
    {
        if (!AccessControl::can('surat.internal','create') 
            && !AccessControl::can('surat.internal','create-own')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $unitId  = $_SESSION['user']['unit_id'] ?? null;
        $unitName = '-';
        if ($unitId) {
            $unitModel = $this->model('UnitKerjaModel');
            $unitData  = $unitModel->getById($unitId);
            $unitName  = $unitData['nama_unit'] ?? '-';
        }

        $this->view('surat_internal/input_surat_internal', [
            'unitId'   => $unitId,
            'unitName' => $unitName
        ]);
    }

    // === STORE: simpan data baru ===
    public function store()
    {
        if (!AccessControl::can('surat.internal','create') 
            && !AccessControl::can('surat.internal','create-own')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data  = $_POST;
        $files = $_FILES;

        $tanggalSurat = !empty($data['tanggal_surat']) ? $data['tanggal_surat'] : date('Y-m-d');
        $tahun        = date('Y', strtotime($tanggalSurat));

        $unitId   = $_SESSION['user']['unit_id'] ?? null;
        $unitName = '-';
        if ($unitId) {
            $unitModel = $this->model('UnitKerjaModel');
            $unitData  = $unitModel->getById($unitId);
            $unitName  = $unitData['nama_unit'] ?? '-';
        }

        $defaultPerihal = "Dengan hormat, mohon perhatian Bapak/Ibu Direktur untuk membaca Surat Internal yang telah kami sampaikan serta memberikan disposisi sesuai arahan";

        $saveData = [
            'nomor_surat'        => $data['nomor_surat'] ?? null,
            'tanggal_surat'      => $tanggalSurat,
            'asal_surat'         => $unitId,        // unit pembuat
            'perihal'            => $defaultPerihal,
            'ditujukan_kepada'   => 1,              // Direktur
            'jawaban'            => null,
            'berkas'             => null,
            'status'             => 'belum dibaca', // default
            'tanggal_diarsipkan' => date('Y-m-d')
        ];

        $id = $this->model->save($saveData);

        if (!empty($files['berkas']['tmp_name'])) {
            $idFormatted = str_pad($id, 3, '0', STR_PAD_LEFT);
            $fileName = "surat_internal_{$unitName}_{$idFormatted}.pdf";

            $uploadDir = dirname(__DIR__, 2) . "/public/uploads/dokumen/surat_internal/$tahun/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $target = $uploadDir . $fileName;
            if (move_uploaded_file($files['berkas']['tmp_name'], $target)) {
                $this->model->updateFile($id, $fileName);
            } else {
                echo "<div class='alert alert-danger'>Upload berkas gagal.</div>";
                return;
            }
        }

        $this->redirect('suratinternal/index');
    }

    // === EDIT: tampilkan form edit ===
    public function edit($id = null)
    {
        if (!AccessControl::can('surat.internal','update') 
            && !AccessControl::can('surat.internal','update-own')) {
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

        $surat = $this->model->findById($id);
        if (!$surat) {
            echo "<div class='alert alert-danger'>Data surat tidak ditemukan.</div>";
            return;
        }

        // Ambil unit dari data surat
        $unitId   = $surat['asal_surat'] ?? null;
        $unitName = '-';
        if ($unitId) {
            $unitModel = $this->model('UnitKerjaModel');
            $unitData  = $unitModel->getById($unitId);
            $unitName  = $unitData['nama_unit'] ?? '-';
        }

        $isReadOnly = (strtolower($_SESSION['user']['nama_jabatan'] ?? '') === 'direktur');

        $this->view('surat_internal/input_surat_internal', [
            'unitId'     => $unitId,
            'unitName'   => $unitName,
            'surat'      => $surat,
            'isReadOnly' => $isReadOnly
        ]);
    }

    // === UPDATE: simpan perubahan ===
    public function update()
    {
        if (!AccessControl::can('surat.internal','update') 
            && !AccessControl::can('surat.internal','update-own')) {
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

        // Ambil surat lama untuk asal_surat
        $surat = $this->model->findById($id);
        if (!$surat) {
            echo "<div class='alert alert-danger'>Data surat tidak ditemukan.</div>";
            return;
        }
        $asalSurat = $surat['asal_surat'] ?? null;

        $tanggalSurat = !empty($data['tanggal_surat']) ? $data['tanggal_surat'] : date('Y-m-d');
        $tahun        = date('Y', strtotime($tanggalSurat));

        $unitId  = $_SESSION['user']['unit_id'] ?? null;
        $unitName = '-';
        if ($unitId) {
            $unitModel = $this->model('UnitKerjaModel');
            $unitData  = $unitModel->getById($unitId);
            $unitName  = $unitData['nama_unit'] ?? '-';
        }

        $pathRelatif = $data['berkas_lama'] ?? null;
        if (!empty($files['berkas']['tmp_name'])) {
            $idFormatted = str_pad($id, 3, '0', STR_PAD_LEFT);
            $fileName = "surat_internal_{$unitName}_{$idFormatted}.pdf";

            $uploadDir = dirname(__DIR__, 2) . "/public/uploads/dokumen/surat_internal/$tahun/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $target = $uploadDir . $fileName;
            if (move_uploaded_file($files['berkas']['tmp_name'], $target)) {
                $pathRelatif = $fileName;
            } else {
                echo "<div class='alert alert-danger'>Upload berkas gagal.</div>";
                return;
            }
        }

        $jabatan = strtolower($_SESSION['user']['nama_jabatan'] ?? '');

        if ($jabatan === 'direktur') {
            // Direktur hanya isi jawaban dan ubah status
            $updateData = [
                'jawaban' => $data['jawaban'] ?? null,
                'status'  => 'sudah dibaca'
            ];
        } else {
            // Role lain bisa update semua, asal_surat tetap dari data surat lama
            $updateData = [
                'nomor_surat'        => $data['nomor_surat'] ?? null,
                'tanggal_surat'      => $tanggalSurat,
                'asal_surat'         => $asalSurat,   // tetap dari data surat
                'perihal'            => $data['perihal'] ?? null,
                'berkas'             => $pathRelatif,
                'status'             => 'belum dibaca',
            'tanggal_diarsipkan' => date('Y-m-d')
            ];
            // catatan: kolom jawaban untuk role lain sebaiknya readonly di view,
            // jadi tidak perlu disimpan di sini
        }

        $this->model->update($id, $updateData);
        $this->redirect('suratinternal/index');
    }


}