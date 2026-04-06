<?php
require_once __DIR__ . '/../helpers/AccessControl.php';

class CutiController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?url=auth/login');
            exit;
        }
    }

    public function index()
    {
        if (!(AccessControl::can('pegawai.cuti','view') || AccessControl::can('pegawai.cuti','view-own'))) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data cuti.</div>";
            exit;
        }

        $page    = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
        $perPage = 6;
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        if (AccessControl::can('pegawai.cuti','view-own')) {
            if (empty($_SESSION['user']['pegawai_id'])) {
                echo "<div class='alert alert-danger'>Data pegawai tidak ditemukan di session. Hubungi admin.</div>";
                exit;
            }
            $pegawaiId = $_SESSION['user']['pegawai_id'];
            $cuti = $this->model('CutiModel')->findByPegawai($pegawaiId);
            $totalRows = is_array($cuti) ? count($cuti) : 0;
            $totalPages = 1;
        } else {
            $cuti = $this->model('CutiModel')->getPaginated($perPage, $offset, $search);
            $totalRows  = $this->model('CutiModel')->countAll($search);
            $totalPages = ceil($totalRows / $perPage);
        }

        $this->view('cuti/index', [
            'cuti'       => $cuti,
            'layout'     => 'main',
            'title'      => 'Manajemen Cuti Pegawai',
            'page'       => $page,
            'totalPages' => $totalPages,
            'pagination' => [
                'page'      => $page,
                'totalPage' => $totalPages,
                'search'    => $search
            ]
        ]);
    }

    public function create()
    {
        if (!(AccessControl::can('pegawai.cuti','create') || AccessControl::can('pegawai.cuti','create-own'))) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengajukan cuti.</div>";
            exit;
        }

        $pegawai = $this->model('PegawaiModel')->getAll();

        $this->view('cuti/create', [
            'layout'  => 'main',
            'title'   => 'Ajukan Cuti Pegawai',
            'pegawai' => $pegawai,
            'edit'    => null
        ]);
    }

    public function edit($id = null)
    {
        if (!(AccessControl::can('pegawai.cuti','update') || AccessControl::can('pegawai.cuti','update-own'))) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit data cuti.</div>";
            exit;
        }

        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            echo "<div class='alert alert-danger'>ID cuti tidak valid.</div>";
            exit;
        }

        $cuti = $this->model('CutiModel')->findById($id);
        if (empty($cuti)) {
            echo "<div class='alert alert-danger'>Data cuti tidak ditemukan.</div>";
            exit;
        }

        $sessionPegawaiId = $_SESSION['user']['pegawai_id'] ?? null;

        if (AccessControl::can('pegawai.cuti','update-own')) {
            if (empty($sessionPegawaiId) || $cuti['pegawai_id'] != $sessionPegawaiId) {
                echo "<div class='alert alert-danger'>Akses ditolak. Anda hanya bisa mengedit cuti Anda sendiri.</div>";
                exit;
            }
        }

        $pegawai = $this->model('PegawaiModel')->getAll();

        $this->view('cuti/edit', [
            'layout'  => 'main',
            'title'   => 'Edit Pengajuan Cuti',
            'pegawai' => $pegawai,
            'edit'    => $cuti
        ]);
    }

    public function simpan()
    {
        if (!(AccessControl::can('pegawai.cuti','create') || AccessControl::can('pegawai.cuti','update') ||
              AccessControl::can('pegawai.cuti','create-own') || AccessControl::can('pegawai.cuti','update-own'))) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak menyimpan data cuti.</div>";
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id      = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $pegawai = $_POST['pegawai_id'] ?? null;
            $mulai   = $_POST['tanggal_mulai'] ?? null;
            $selesai = $_POST['tanggal_selesai'] ?? null;
            $jenis   = trim($_POST['jenis_cuti'] ?? '');
            $alasan  = trim($_POST['alasan'] ?? '');

            if (empty($pegawai) || empty($mulai) || empty($selesai)) {
                return $this->redirect('cuti/index&error=Data wajib diisi');
            }

            // ✅ Validasi tanggal: akhir tidak boleh < awal
            if (strtotime($selesai) < strtotime($mulai)) {
                return $this->redirect('cuti/index&error=Tanggal selesai tidak boleh lebih kecil dari tanggal mulai');
            }

            // ✅ Validasi tambahan: tanggal mulai tidak boleh di masa lalu
            if (strtotime($mulai) < strtotime(date('Y-m-d'))) {
                return $this->redirect('cuti/index&error=Tanggal mulai tidak boleh di masa lalu');
            }

            if (AccessControl::can('pegawai.cuti','create') || AccessControl::can('pegawai.cuti','update')) {
                // ADMIN → bebas pilih pegawai
            } elseif (AccessControl::can('pegawai.cuti','create-own') || AccessControl::can('pegawai.cuti','update-own')) {
                // USER → hanya boleh untuk dirinya sendiri
                if ($pegawai != $_SESSION['user']['pegawai_id']) {
                    echo "<div class='alert alert-danger'>Akses ditolak. Anda hanya bisa mengajukan/mengedit cuti Anda sendiri.</div>";
                    exit;
                }
            }

            $data = [
                ':pegawai_id'       => $pegawai,
                ':tanggal_mulai'    => $mulai,
                ':tanggal_selesai'  => $selesai,
                ':jenis_cuti'       => $jenis,
                ':alasan'           => $alasan
            ];

            $ok = false;
            if ($id) {
                $ok = $this->model('CutiModel')->update($data + [':id_cuti' => $id]);
            } else {
                $ok = $this->model('CutiModel')->insert($data);
            }

            if ($ok) {
                return $this->redirect('cuti/index&success=Data berhasil disimpan');
            } else {
                return $this->redirect('cuti/index&error=Gagal menyimpan data');
            }
        }
    }

    public function approve($id)
    {
        if (!AccessControl::can('pegawai.cuti','approve')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak menyetujui cuti.</div>";
            exit;
        }

        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) {
            echo "<div class='alert alert-danger'>ID cuti tidak valid.</div>";
            exit;
        }

        $data = [
            ':status_pengajuan'    => 'Disetujui',
            ':disetujui_oleh'      => $_SESSION['user']['pegawai_id'], // konsisten pakai pegawai_id
            ':tanggal_persetujuan' => date('Y-m-d'),
            ':id_cuti'             => $id
        ];
        $this->model('CutiModel')->updateStatus($data);
        return $this->redirect('cuti/index&success=Cuti disetujui');
    }

    public function reject($id)
    {
        if (!AccessControl::can('pegawai.cuti','approve')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak menolak cuti.</div>";
            exit;
        }

        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) {
            echo "<div class='alert alert-danger'>ID cuti tidak valid.</div>";
            exit;
        }

        $data = [
            ':status_pengajuan'    => 'Ditolak',
            ':disetujui_oleh'      => $_SESSION['user']['pegawai_id'], // konsisten pakai pegawai_id
            ':tanggal_persetujuan' => date('Y-m-d'),
            ':id_cuti'             => $id
        ];
        $this->model('CutiModel')->updateStatus($data);
        return $this->redirect('cuti/index&success=Cuti ditolak');
    }
}
