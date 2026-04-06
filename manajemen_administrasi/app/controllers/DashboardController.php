<?php
require_once __DIR__ . '/../helpers/AccessControl.php';

class DashboardController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/index.php?url=auth/login');
            exit;
        }

        if (!AccessControl::can('dashboard','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengakses Dashboard.</div>";
            exit;
        }

        $user = $_SESSION['user'];
        $jabatanModel = $this->model('JabatanModel');
        $jabatan = $jabatanModel->findById($user['jabatan_id']);

        $pegawaiModel = $this->model('PegawaiModel');
        $strModel     = $this->model('StrModel');
        $sipModel     = $this->model('SipModel');

        // Statistik global (tanpa filter role/unit)
        $totalPegawai = $pegawaiModel->countPegawaiAktifAll();
        $jumlahSTR    = $strModel->countAktifAll();
        $jumlahSIP    = $sipModel->countAktifAll();

        $this->view('dashboard/index', [
            'user'         => $user,
            'nama_jabatan' => $jabatan['nama_jabatan'] ?? '',
            'layout'       => 'dashboard',
            'title'        => 'Dashboard',
            'totalPegawai' => $totalPegawai,
            'jumlahSTR'    => $jumlahSTR,
            'jumlahSIP'    => $jumlahSIP
        ]);
    }
}