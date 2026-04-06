<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../helpers/AccessControl.php';
require_once __DIR__ . '/../helpers/version_helper.php';

class AboutController extends Controller
{
    public function index()
    {
        // Semua user boleh lihat halaman About
        $versions = getAllVersions();

        $this->view('about', [
            'title'    => 'Tentang Aplikasi',
            'versions' => $versions
        ]);
    }

    public function add_version()
    {
        $this->authorizeSession();

        // Hanya Administrator yang boleh menambah versi
        if (!AccessControl::isAdministrator()) {
            echo "<div class='alert alert-danger'>Akses ditolak. Hanya Administrator yang dapat menambah versi aplikasi.</div>";
            exit;
        }

        $version      = trim($_POST['version'] ?? '');
        $release_date = trim($_POST['release_date'] ?? '');
        $description  = trim($_POST['description'] ?? '');

        // Validasi sederhana
        if ($version === '' || $release_date === '' || $description === '') {
            echo "<div class='alert alert-danger'>Semua field wajib diisi.</div>";
            exit;
        }

        // Simpan versi baru
        addVersion($version, $release_date, $description);

        return $this->redirect('about');
    }
}