<?php
require_once __DIR__ . '/../helpers/AccessControl.php';

class InstitusiController extends Controller
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
        if (!AccessControl::can('pengaturan.institusi','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data institusi.</div>";
            exit;
        }

        // Ambil semua data institusi untuk ditampilkan di tabel
        $institusi = $this->model('MasterModel')->getAll('institusi');

        // Jika ada parameter id, ambil record spesifik untuk edit
        $id   = $_GET['id'] ?? null;
        $edit = null;
        if ($id) {
            $edit = $this->model('MasterModel')->getWhere('institusi', ['id' => $id]);
        }

        $this->view('master/institusi', [
            'institusi' => $institusi,
            'edit'      => $edit,
            'layout'    => 'main',
            'title'     => 'Master Data: Institusi',
            'edit_id'   => $id
        ]);
    }

    public function simpan()
    {
        if (!(AccessControl::can('pengaturan.institusi','create') || AccessControl::can('pengaturan.institusi','update'))) {
            echo "<div class='alert alert-danger'>Akses ditolak. Hanya administrator/admin yang dapat menyimpan data institusi.</div>";
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id      = $_POST['id'] ?? null; // 🔑 ambil id dari hidden input
            $nama    = trim($_POST['nama_institusi'] ?? '');
            $sub     = trim($_POST['sub_institusi'] ?? '');
            $alamat  = trim($_POST['alamat'] ?? '');
            $telepon = trim($_POST['telepon'] ?? '');
            $logo    = null;

            // Validasi sederhana
            if ($nama === '' || $alamat === '') {
                return $this->redirect('institusi/index?error=Data wajib diisi');
            }

            // Proses upload logo jika ada
            if (!empty($_FILES['logo']['name'])) {
                $ext      = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                $filename = 'logo_institusi.' . $ext;
                $target   = __DIR__ . '/../../public/uploads/institusi_logo/' . $filename;

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
                    $logo = $filename;
                } else {
                    return $this->redirect('institusi/index?error=Upload logo gagal');
                }
            }

            $data = [
                'nama_institusi' => $nama,
                'sub_institusi'  => $sub,
                'alamat'         => $alamat,
                'telepon'        => $telepon
            ];
            if ($logo !== null) {
                $data['logo'] = $logo;
            }

            // 🔑 Logika update/insert berdasarkan id
            if (!empty($id)) {
                $this->model('MasterModel')->update('institusi', $data, ['id' => $id]);
            } else {
                $this->model('MasterModel')->insert('institusi', $data);
            }

            return $this->redirect('institusi/index');
        }
    }
}
