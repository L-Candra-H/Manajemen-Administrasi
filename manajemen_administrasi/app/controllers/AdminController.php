<?php
require_once __DIR__ . '/../helpers/AccessControl.php';

class AdminController extends Controller
{
    public function updateHakAkses()
    {
        // hanya administrator boleh update hak akses
        if (!AccessControl::can('pengaturan.hak_akses','update')) {
            return $this->redirect('unauthorized');
        }

        $username  = $_POST['username'] ?? '';
        $hak_akses = $_POST['hak_akses'] ?? '';

        // Validasi role
        if (!in_array($hak_akses, ['admin','admin2','admin3','user'])) {
            return $this->redirect('admin/hak_akses');
        }

        $this->model('HakAksesModel')->updateHakAkses($username, $hak_akses);

        // cara lama: langsung redirect ke halaman hak_akses
        return $this->redirect('admin/hak_akses');
    }

    public function updateJabatan()
    {
        $role = $_SESSION['user']['hak_akses'] ?? '';
        if (!in_array($role, ['admin','admin3','administrator'])) {
        return $this->redirect('unauthorized');
        }

        $username  = $_POST['username'] ?? '';
        $jabatanId = (int)($_POST['jabatan_id'] ?? 0);

        if (empty($username) || $jabatanId <= 0) {
            return $this->redirect('admin/hak_akses');
        }

        $this->model('HakAksesModel')->updateJabatan($username, $jabatanId);
        return $this->redirect('admin/hak_akses');
    }

    public function updateUnitKerja()
    {
        $role = $_SESSION['user']['hak_akses'] ?? '';
        if (!in_array($role, ['admin','admin3','administrator'])) {
            return $this->redirect('unauthorized');
        }

        $username    = $_POST['username'] ?? '';
        $unitKerjaId = (int)($_POST['unit_kerja_id'] ?? 0);

        if (empty($username) || $unitKerjaId <= 0) {
            return $this->redirect('admin/hak_akses');
        }

        $this->model('HakAksesModel')->updateUnitKerja($username, $unitKerjaId);
        return $this->redirect('admin/hak_akses');
    }

    public function hak_akses()
    {
        if (!AccessControl::can('pengaturan.hak_akses','view')) {
            return $this->redirect('unauthorized');
        }

        // setup pagination
        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5;
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        $totalData   = $this->model('HakAksesModel')->countAll($search);
        $totalPage   = ceil($totalData / $perPage);
        $hakAkses    = $this->model('HakAksesModel')->getPaginatedAll($perPage, $offset, $search);

        $jabatanList   = $this->model('JabatanModel')->getAll();
        $unitKerjaList = $this->model('UnitKerjaModel')->getAll();

        return $this->view('admin/hak_akses', [
            'hak_akses'      => $hakAkses,
            'jabatan_list'   => $jabatanList,
            'unit_kerja_list'=> $unitKerjaList,
            'pagination'     => [
                'page'      => $page,
                'totalPage' => max($totalPage, 1),
                'search'    => $search
            ],
            'title'          => 'Manajemen Hak Akses',
            'layout'         => 'main',
            'loadFormLogic'  => true
        ]);
    }
}