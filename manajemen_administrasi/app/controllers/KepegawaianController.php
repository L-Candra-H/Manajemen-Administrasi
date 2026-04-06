<?php
require_once __DIR__ . '/../models/PegawaiModel.php';
require_once __DIR__ . '/../models/StrModel.php';
require_once __DIR__ . '/../models/SipModel.php';
require_once __DIR__ . '/../models/SuratKeputusanModel.php';
require_once __DIR__ . '/../models/BpjsModel.php';
require_once __DIR__ . '/../models/BpjsKeluargaModel.php';
require_once __DIR__ . '/../models/BpjsTambahanModel.php';
require_once __DIR__ . '/../models/StatusPegawaiModel.php';
require_once __DIR__ . '/../helpers/AccessControl.php';

class KepegawaianController extends Controller
{
    // === DAFTAR PEGAWAI ===
    public function daftar()
    {
        $nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? null;

        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5;
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        if (AccessControl::can('pegawai.daftar_aktif','view-own')) {
            $pegawai = [];
            if ($nipSession) {
                $pegawaiData = $this->model('PegawaiModel')->findByNIP($nipSession);
                if ($pegawaiData) {
                    $pegawai[] = $pegawaiData;
                }
            }
            $pagination = null;
        }
        elseif (AccessControl::can('pegawai.daftar_aktif','view')) {
            $totalData = $this->model('PegawaiModel')->countAll($search);
            $totalPage = max(ceil($totalData / $perPage), 1);

            $pegawai = $this->model('PegawaiModel')->getPaginated($perPage, $offset, $search);

            $pagination = [
                'page'      => $page,
                'totalPage' => $totalPage,
                'search'    => $search
            ];
        }
        else {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        foreach ($pegawai as &$p) {
            if (!empty($p['tanggal_lahir']) && strtotime($p['tanggal_lahir'])) {
                $lahir = new DateTime($p['tanggal_lahir']);
                $today = new DateTime('today');
                $p['umur'] = $lahir->diff($today)->y;
            } else {
                $p['umur'] = '-';
            }
        }
        unset($p);

        $data = [
            'daftar_pegawai' => $pegawai,
            'pagination'     => $pagination,
            'loadFormLogic'  => true,
            'pegawaiList'    => $this->model('PegawaiModel')->getAllAktif()
        ];

        $this->view('kepegawaian/daftar_pegawai', $data);
    }

    // === DAFTAR STR ===
    public function str()
    {
        $nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? null;

        // setup pagination
        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 6;
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        // USER → hanya boleh view-own STR
        if (AccessControl::can('pegawai.str','view-own')) {
            $str = [];
            if ($nipSession) {
                $dataStr = $this->model('StrModel')->getByNIP($nipSession);
                if ($dataStr) {
                    $str[] = $dataStr;
                }
            }
            $pagination = null; // tidak perlu pagination untuk user
        }
        // ADMIN / ADMIN3 / ADMINISTRATOR → boleh view semua STR
        elseif (AccessControl::can('pegawai.str','view')) {
            $totalData = $this->model('StrModel')->countAll($search);
            $totalPage = ceil($totalData / $perPage);

            $str = $this->model('StrModel')->getPaginated($perPage, $offset, $search);

            $pagination = [
                'page'      => $page,
                'totalPage' => max($totalPage, 1), // minimal 1 halaman
                'search'    => $search
            ];
        }
        else {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data STR.</div>";
            exit;
        }

        $data['daftar_str']    = $str;
        $data['pagination']    = $pagination;
        $data['loadFormLogic'] = true;
        $data['pegawaiList']   = $this->model('PegawaiModel')->getAllAktif();

        $this->view('kepegawaian/daftar_str', $data);
    }

    // === DAFTAR SIP ===
    public function sip()
    {
        $nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? null;

        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5; // ✅ konsisten
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        if (AccessControl::can('pegawai.sip','view-own')) {
            $sip = [];
            if ($nipSession) {
                $dataSip = $this->model('SipModel')->getByNIP($nipSession);
                if ($dataSip) {
                    $sip[] = $dataSip;
                }
            }
            $pagination = null;
        }
        elseif (AccessControl::can('pegawai.sip','view')) {
            $totalData = $this->model('SipModel')->countAll($search);
            $totalPage = max(ceil($totalData / $perPage), 1);

            $sip = $this->model('SipModel')->getPaginated($perPage, $offset, $search);

            $pagination = [
                'page'      => $page,
                'totalPage' => $totalPage,
                'search'    => $search
            ];
        }
        else {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data = [
            'daftar_sip'    => $sip,
            'pagination'    => $pagination,
            'loadFormLogic' => true,
            'pegawaiList'   => $this->model('PegawaiModel')->getAllAktif()
        ];

        $this->view('kepegawaian/daftar_sip', $data);
    }

    // === DAFTAR SK ===
    public function sk()
    {
        $nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? null;

        // setup pagination
        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5; // bisa diubah sesuai kebutuhan
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        // USER → hanya boleh view-own SK
        if (AccessControl::can('pegawai.sk','view-own')) {
            $sk = [];
            if ($nipSession) {
                $dataSk = $this->model('SuratKeputusanModel')->getByNIP($nipSession);
                if ($dataSk) {
                    $sk[] = $dataSk;
                }
            }
            $pagination = null; // tidak perlu pagination untuk user
        }
        // ADMIN / ADMIN3 / ADMINISTRATOR → boleh view semua SK
        elseif (AccessControl::can('pegawai.sk','view')) {
            $totalData = $this->model('SuratKeputusanModel')->countAll($search);
            $totalPage = ceil($totalData / $perPage);

            $sk = $this->model('SuratKeputusanModel')->getPaginated($perPage, $offset, $search);

            $pagination = [
                'page'      => $page,
                'totalPage' => max($totalPage, 1),
                'search'    => $search
            ];
        }
        else {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data SK.</div>";
            exit;
        }

        $data['daftar_sk']     = $sk;
        $data['pagination']    = $pagination;
        $data['loadFormLogic'] = true;
        $data['pegawaiList']   = $this->model('PegawaiModel')->getAllAktif();

        $this->view('kepegawaian/daftar_sk', $data);
    }

    // === DAFTAR Sertifikat ===  
    public function sertifikat()
    {
        $nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? null;

        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5;
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        if (AccessControl::can('pegawai.sertifikat','view-own')) {
            $sertifikat = [];
            if ($nipSession) {
                $sertifikat = $this->model('SertifikatModel')->getPaginatedByNIP($nipSession, $perPage, $offset, $search);
            }
            $pagination = null;
        }
        elseif (AccessControl::can('pegawai.sertifikat','view')) {
            $totalData = $this->model('SertifikatModel')->countAll($search);
            $totalPage = max(ceil($totalData / $perPage), 1);

            $sertifikat = $this->model('SertifikatModel')->getPaginated($perPage, $offset, $search);

            $pagination = [
                'page'      => $page,
                'totalPage' => $totalPage,
                'search'    => $search
            ];
        }
        else {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data = [
            'sertifikat'    => $sertifikat,
            'pagination'    => $pagination,
            'loadFormLogic' => true,
            'pegawaiList'   => $this->model('PegawaiModel')->getAllAktif()
        ];

        $this->view('kepegawaian/daftar_sertifikat', $data);
    }

    // === FORM TAMBAH Sertifikat ===
    public function sertifikat_tambah()
    {
        if (!AccessControl::can('pegawai.sertifikat','create')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat menambah Sertifikat baru.</div>";
            exit;
        }

        $data['mode']        = 'tambah';
        $data['pegawaiList'] = $this->model('PegawaiModel')->getAllAktif();

        $this->view('kepegawaian/sertifikat_tambah', $data);
    }

    // === SIMPAN Sertifikat BARU ===
    public function saveSertifikat()
    {
        $this->authorizeSession();

        if (!AccessControl::can('pegawai.sertifikat','create')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        // Validasi minimal input
        if (empty($_POST['nip']) || empty($_POST['nomor_sertifikat'])) {
            echo "<div class='alert alert-danger'>NIP dan Nomor Sertifikat wajib diisi.</div>";
            exit;
        }

        // Upload berkas (wajib PDF, dan wajib berhasil)
        $berkasPath = null;
        if (!empty($_FILES['berkas']['name'])) {
            $berkasPath = $this->uploadSertifikat(
                $_FILES['berkas'],
                $_POST['nip'],
                null,
                $_POST['mulai']   // gunakan tanggal mulai pelaksanaan
            );
            if (!$berkasPath) {
                echo "<div class='alert alert-warning'>Upload berkas gagal. Pastikan file PDF valid dan folder bisa ditulis.</div>";
                exit;
            }
        }

        $data = [
            'nomor_induk_pegawai' => $_POST['nip'],
            'jenis'                    => $_POST['jenis'],
            'mode'                     => $_POST['mode'],
            'pemberi_sertifikat'       => $_POST['pemberi_sertifikat'],
            'nomor_sertifikat'         => $_POST['nomor_sertifikat'],
            'tanggal_mulai_pelaksanaan'=> $_POST['mulai'],
            'tanggal_akhir_pelaksanaan'=> $_POST['selesai'],
            'berkas'                   => $berkasPath // pastikan tidak NULL jika file wajib
        ];

        $this->model('SertifikatModel')->save($data);
        header("Location: index.php?url=kepegawaian/sertifikat");
    }

    // === FORM EDIT Sertifikat ===
    public function sertifikat_edit()
    {
        if (!AccessControl::can('pegawai.sertifikat','update') &&
            !AccessControl::can('pegawai.sertifikat','update-own')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $id = $_GET['id'] ?? null;
        $sertifikat = $this->model('SertifikatModel')->getById($id);

        $data['mode']        = 'edit';
        $data['sertifikat']  = $sertifikat;
        $data['pegawaiList'] = $this->model('PegawaiModel')->getAllAktif();

        $this->view('kepegawaian/sertifikat_tambah', $data);
    }

    // === UPDATE Sertifikat ===
    public function updateSertifikat()
    {
        $this->authorizeSession();

        if (!AccessControl::can('pegawai.sertifikat','update') &&
            !AccessControl::can('pegawai.sertifikat','update-own')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $sertifikatLama = $this->model('SertifikatModel')->getById($_POST['id']);
        $berkasPath = $sertifikatLama['berkas'] ?? null;

        if (!empty($_FILES['berkas']['name'])) {
            if (!empty($sertifikatLama['berkas'])) {
                $oldPath = __DIR__ . "/../" . $sertifikatLama['berkas'];
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }
            $berkasPath = $this->uploadSertifikat(
                $_FILES['berkas'],
                $_POST['nip'],
                $_POST['id'],
                $_POST['mulai']
            );
            if (!$berkasPath) {
                echo "<div class='alert alert-warning'>Upload berkas gagal.</div>";
                exit;
            }
        }

        $data = [
            'id'                       => $_POST['id'],
            'jenis'                    => $_POST['jenis'],
            'mode'                     => $_POST['mode'],
            'pemberi_sertifikat'       => $_POST['pemberi_sertifikat'],
            'nomor_sertifikat'         => $_POST['nomor_sertifikat'],
            'tanggal_mulai_pelaksanaan'=> $_POST['mulai'],
            'tanggal_akhir_pelaksanaan'=> $_POST['selesai'],
            'berkas'                   => $berkasPath
        ];

        $this->model('SertifikatModel')->update($data);
        header("Location: index.php?url=kepegawaian/sertifikat");
    }

    // === DELETE Sertifikat ===
    public function deleteSertifikat()
    {
        $this->authorizeSession();

        if (!AccessControl::can('pegawai.sertifikat','delete')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $id = $_GET['id'] ?? null;
        $this->model('SertifikatModel')->deleteById($id);

        header("Location: index.php?url=kepegawaian/sertifikat");
    }

    // === PEGAWAI NONAKTIF ===
    public function nonaktif()
    {
        $this->authorizeSession();

        // 🔒 Guard akses sesuai AccessControl
        if (!AccessControl::can('pegawai.daftar_nonaktif','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Hanya administrator yang dapat melihat daftar pegawai non aktif.</div>";
            exit;
        }

        $pegawaiModel = $this->model('PegawaiModel');

        // setup pagination
        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5;
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        // hitung total data nonaktif
        $totalData = $pegawaiModel->countNonAktif($search);
        $totalPage = ceil($totalData / $perPage);

        // ambil data sesuai halaman
        $pegawaiList = $pegawaiModel->getNonAktifPaginated($perPage, $offset, $search);

        $pagination = [
            'page'      => $page,
            'totalPage' => max($totalPage, 1),
            'search'    => $search
        ];

        $this->view('kepegawaian/nonaktif', [
            'layout'           => 'dashboard',
            'title'            => 'Daftar Pegawai Non Aktif',
            'pegawai_nonaktif' => $pegawaiList,
            'pagination'       => $pagination
        ]);
    }

    public function aktifkan()
    {
        $this->authorizeSession();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nomor_induk_pegawai'])) {
            $nip = $_POST['nomor_induk_pegawai'];

            // 🔒 Guard akses sesuai AccessControl
            if (!AccessControl::can('pegawai.daftar_nonaktif','update')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Hanya administrator yang dapat mengaktifkan kembali pegawai nonaktif.</div>";
                exit;
            }

            $statusModel = $this->model('StatusPegawaiModel');
            $statusModel->update([
                'nomor_induk_pegawai' => $nip,
                'status_keaktifan'    => 'Aktif',
                'alasan_non_aktif'    => null,
                'updated_at'          => date('Y-m-d H:i:s')
            ]);
        }

        header('Location: ' . BASE_URL . '/index.php?url=kepegawaian/nonaktif');
        exit;
    }

    public function storeUnitKerja()
    {
        header('Content-Type: application/json');

        $nama_unit = trim($_POST['nama_unit'] ?? '');
        if ($nama_unit === '') {
            echo json_encode(['success' => false, 'message' => 'Nama unit kerja wajib diisi']);
            return;
        }

        $unitKerjaModel = $this->model('UnitKerjaModel');

        if (!$unitKerjaModel->exists($nama_unit)) {
            $newId = $unitKerjaModel->save($nama_unit);
        } else {
            $newId = $unitKerjaModel->getIdByName($nama_unit);
        }

        echo json_encode(['success' => true, 'id' => $newId]);
    }

    // === DAFTAR QR PEGAWAI ===
    public function daftar_qr_pegawai()
    {
        $nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? null;

        // USER → hanya boleh view-own QR
        if (AccessControl::can('pegawai.qrcode','view-own')) {
            if (!$nipSession) {
                echo "<div class='alert alert-warning'>NIP tidak ditemukan di session. QR tidak bisa ditampilkan.</div>";
                exit;
            }
            $qrData = PegawaiQrModel::getQrByNip($nipSession);
            $qr = $qrData ? [$qrData] : [];
        }

        // ADMIN / ADMINISTRATOR → boleh view semua QR
        elseif (AccessControl::can('pegawai.qrcode','view')) {
            $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 4;
            $offset  = ($page - 1) * $perPage;
            $search  = $_GET['q'] ?? '';

            $totalData = PegawaiQrModel::countQr($search);
            $totalPage = ceil($totalData / $perPage);

            $qr = PegawaiQrModel::getDaftarQr($perPage, $offset, $search);

            $data['pagination'] = [
                'page'      => $page,
                'totalPage' => $totalPage,
                'search'    => $search
            ];
        }
        else {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat QR Pegawai.</div>";
            exit;
        }

        // siapkan data untuk view
        $data['daftar_qr']     = $qr;
        $data['loadFormLogic'] = true;
        $data['pegawaiList']   = $this->model('PegawaiModel')->getAllAktif();

        // panggil view dengan layout wrapper
        $this->view('kepegawaian/daftar_qr_pegawai', $data);
    }

    // === DAFTAR BPJS ===
    public function bpjs()
    {
        // cek izin akses BPJS
        if (!AccessControl::can('pegawai.bpjs','view') && !AccessControl::can('pegawai.bpjs','view-own')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat data BPJS.</div>";
            exit;
        }

        $bpjsModel = $this->model('BpjsModel');

        // setup pagination
        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 6;
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        // hitung total data
        $totalData = $bpjsModel->countAll($search);
        $totalPage = ceil($totalData / $perPage);

        // ambil data sesuai halaman
        $bpjs = $bpjsModel->getPaginated($perPage, $offset, $search);

        $pagination = [
            'page'      => $page,
            'totalPage' => max($totalPage, 1),
            'search'    => $search
        ];

        $data['daftar_bpjs']   = $bpjs;
        $data['pagination']    = $pagination;
        $data['loadFormLogic'] = true;
        $data['pegawaiList']   = $this->model('PegawaiModel')->getAllAktif();

        $this->view('kepegawaian/daftar_bpjs', $data);
    }

    // === ALIAS UNTUK ROUTE LAMA ===
    public function input()       { $this->formPegawai(); }
    public function sip_tambah()  { $this->formSIP(); }
    public function sk_tambah()   { $this->formSK(); }

    // === ALIAS PENCARIAN ===
    public function daftar_pegawai() { $this->daftar(); }
    public function daftar_str()     { $this->str(); }
    public function daftar_sip()     { $this->sip(); }
    public function daftar_sk()      { $this->sk(); }
    public function daftar_bpjs()    { $this->bpjs(); }

    // === FORM PEGAWAI ===
    public function formPegawai()
    {
    $nipSession = $_SESSION['user']['nomor_induk_pegawai'] ?? null;
    $nipTarget  = $_GET['nip'] ?? null;

    if ($nipTarget) {
        $pegawai = $this->model('PegawaiModel')->findByNIP($nipTarget);
            if (!$pegawai || !is_array($pegawai)) {
                echo "<div class='alert alert-danger'>Data pegawai tidak ditemukan.</div>";
                exit;
            }

            $isOwner = $nipSession === $pegawai['nomor_induk_pegawai'];
            $role = strtolower($_SESSION['user']['hak_akses'] ?? 'user');

            if ($isOwner && in_array($role, ['admin','administrator','admin3'])) {
                // admin yang mengedit dirinya sendiri → tetap pakai update
                if (!AccessControl::can('pegawai.daftar_aktif','update')) {
                    echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit data pegawai.</div>";
                    exit;
                }
            } elseif ($isOwner) {
                if (!AccessControl::can('pegawai.daftar_aktif','update-own')) {
                    echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit data Anda sendiri.</div>";
                    exit;
                }
            } else {
                if (!AccessControl::can('pegawai.daftar_aktif','update')) {
                    echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit data pegawai lain.</div>";
                    exit;
                }
            }

            $isEdit = true;
            $title = 'Edit Data Pegawai';
        } else {
            // mode tambah pegawai baru
            if (!AccessControl::can('pegawai.daftar_aktif','create')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat menambah data pegawai baru.</div>";
                exit;
            }

            $pegawai = null;
            $isEdit = false;
            $title   = 'Tambah Pegawai';
        }

        $pegawaiList = $this->model('PegawaiModel')->getAllAktif();

        if ($pegawai) {
            $nip = $pegawai['nomor_induk_pegawai'];

        $pegawai['pendidikan_id']       = $pegawai['pendidikan_id'] ?? '';
        $pegawai['pendidikan_terakhir'] = $pegawai['pendidikan_terakhir'] ?? '';
        $pegawai['tanggal_masuk']       = $pegawai['tanggal_masuk'] ?? '';

        $str      = $this->model('StrModel')->getByNIP($nip);
        $sip      = $this->model('SipModel')->getByNIP($nip);
        $sk       = $this->model('SuratKeputusanModel')->getByNIP($nip);
        $bpjs     = $this->model('BpjsModel')->getByNIP($nip);
        $keluarga = $this->model('BpjsKeluargaModel')->getByNIP($nip);
        $tambahan = $this->model('BpjsTambahanModel')->getByNIP($nip);

        // STR
        $pegawai['nomor_STR']  = $str['nomor_STR'] ?? '';
        $pegawai['berkas_STR'] = $str['berkas_STR'] ?? '';

        // SIP
        $pegawai['nomor_SIP']  = $sip[0]['nomor_SIP'] ?? '';
        $pegawai['berkas_SIP'] = $sip[0]['berkas_SIP'] ?? '';

        // SK
        $pegawai['nomor_surat_keputusan']  = $sk['nomor_surat_keputusan'] ?? '';
        $pegawai['berkas_surat_keputusan'] = $sk['berkas_surat_keputusan'] ?? '';

        // BPJS Utama
        $bpjs = $this->model('BpjsModel')->getByNIP($nip);
        $pegawai['nomor_kartu_BPJS_ketenagakerjaan'] = $bpjs['nomor_kartu_BPJS_ketenagakerjaan'] ?? '';
        $pegawai['nomor_kartu_BPJS_kesehatan']       = $bpjs['nomor_kartu_BPJS_kesehatan'] ?? '';

        // Tentukan status BPJS dari tabel pegawai
        $statusBPJS = $pegawai['status_BPJS_kesehatan'] ?? 'Sendiri';

        // BPJS Keluarga
        if ($statusBPJS === 'Keluarga') {
            $pegawai['nomor_kartu_BPJS_kesehatan_suami_istri']      = $keluarga['nomor_kartu_BPJS_kesehatan_suami_istri'] ?? '';
            $pegawai['nama_lengkap_suami_istri']                    = $keluarga['nama_lengkap_suami_istri'] ?? '';
            $pegawai['status_keaktifan_BPJS_kesehatan_suami_istri'] = $keluarga['status_keaktifan_BPJS_kesehatan_suami_istri'] ?? '';

            $pegawai['nomor_kartu_BPJS_kesehatan_anak_I']      = $keluarga['nomor_kartu_BPJS_kesehatan_anak_I'] ?? '';
            $pegawai['nama_lengkap_anak_I']                    = $keluarga['nama_lengkap_anak_I'] ?? '';
            $pegawai['status_keaktifan_BPJS_kesehatan_anak_I'] = $keluarga['status_keaktifan_BPJS_kesehatan_anak_I'] ?? '';

            $pegawai['nomor_kartu_BPJS_kesehatan_anak_II']      = $keluarga['nomor_kartu_BPJS_kesehatan_anak_II'] ?? '';
            $pegawai['nama_lengkap_anak_II']                    = $keluarga['nama_lengkap_anak_II'] ?? '';
            $pegawai['status_keaktifan_BPJS_kesehatan_anak_II'] = $keluarga['status_keaktifan_BPJS_kesehatan_anak_II'] ?? '';

            $pegawai['nomor_kartu_BPJS_kesehatan_anak_III']      = $keluarga['nomor_kartu_BPJS_kesehatan_anak_III'] ?? '';
            $pegawai['nama_lengkap_anak_III']                    = $keluarga['nama_lengkap_anak_III'] ?? '';
            $pegawai['status_keaktifan_BPJS_kesehatan_anak_III'] = $keluarga['status_keaktifan_BPJS_kesehatan_anak_III'] ?? '';

        // BPJS Tambahan
        } elseif ($statusBPJS === 'Keluarga dan Tambahan') {
            $pegawai['nomor_kartu_BPJS_kesehatan_tambahan_I']      = $tambahan['nomor_kartu_BPJS_kesehatan_tambahan_I'] ?? '';
            $pegawai['nama_lengkap_tambahan_I']                    = $tambahan['nama_lengkap_tambahan_I'] ?? '';
            $pegawai['status_keaktifan_BPJS_kesehatan_tambahan_I'] = $tambahan['status_keaktifan_BPJS_kesehatan_tambahan_I'] ?? '';

            $pegawai['nomor_kartu_BPJS_kesehatan_tambahan_II']      = $tambahan['nomor_kartu_BPJS_kesehatan_tambahan_II'] ?? '';
            $pegawai['nama_lengkap_tambahan_II']                    = $tambahan['nama_lengkap_tambahan_II'] ?? '';
            $pegawai['status_keaktifan_BPJS_kesehatan_tambahan_II'] = $tambahan['status_keaktifan_BPJS_kesehatan_tambahan_II'] ?? '';

            $pegawai['nomor_kartu_BPJS_kesehatan_tambahan_III']      = $tambahan['nomor_kartu_BPJS_kesehatan_tambahan_III'] ?? '';
            $pegawai['nama_lengkap_tambahan_III']                    = $tambahan['nama_lengkap_tambahan_III'] ?? '';
            $pegawai['status_keaktifan_BPJS_kesehatan_tambahan_III'] = $tambahan['status_keaktifan_BPJS_kesehatan_tambahan_III'] ?? '';

            $pegawai['nomor_kartu_BPJS_kesehatan_tambahan_IV']      = $tambahan['nomor_kartu_BPJS_kesehatan_tambahan_IV'] ?? '';
            $pegawai['nama_lengkap_tambahan_IV']                    = $tambahan['nama_lengkap_tambahan_IV'] ?? '';
            $pegawai['status_keaktifan_BPJS_kesehatan_tambahan_IV'] = $tambahan['status_keaktifan_BPJS_kesehatan_tambahan_IV'] ?? '';
        }
    }  

    // Jika mode tambah (pegawai = null), siapkan array kosong agar tidak undefined
    if (!$pegawai) {
        // ✅ tambahkan default kosong untuk field baru
        $pegawai = [
            'pendidikan_id'       => '',
            'pendidikan_terakhir' => '',
            'tanggal_masuk'       => ''
        ];

        $keluarga = [
            'nomor_kartu_BPJS_kesehatan_suami_istri'      => '',
            'nama_lengkap_suami_istri'                    => '',
            'status_keaktifan_BPJS_kesehatan_suami_istri' => '',
            'nomor_kartu_BPJS_kesehatan_anak_I'           => '',
            'nama_lengkap_anak_I'                         => '',
            'status_keaktifan_BPJS_kesehatan_anak_I'      => '',
            'nomor_kartu_BPJS_kesehatan_anak_II'          => '',
            'nama_lengkap_anak_II'                        => '',
            'status_keaktifan_BPJS_kesehatan_anak_II'     => '',
            'nomor_kartu_BPJS_kesehatan_anak_III'         => '',
            'nama_lengkap_anak_III'                       => '',
            'status_keaktifan_BPJS_kesehatan_anak_III'    => ''
        ];

        $tambahan = [
            'nomor_kartu_BPJS_kesehatan_tambahan_I'       => '',
            'nama_lengkap_tambahan_I'                     => '',
            'status_keaktifan_BPJS_kesehatan_tambahan_I'  => '',
            'nomor_kartu_BPJS_kesehatan_tambahan_II'      => '',
            'nama_lengkap_tambahan_II'                    => '',
            'status_keaktifan_BPJS_kesehatan_tambahan_II' => '',
            'nomor_kartu_BPJS_kesehatan_tambahan_III'     => '',
            'nama_lengkap_tambahan_III'                   => '',
            'status_keaktifan_BPJS_kesehatan_tambahan_III'=> '',
            'nomor_kartu_BPJS_kesehatan_tambahan_IV'      => '',
            'nama_lengkap_tambahan_IV'                    => '',
            'status_keaktifan_BPJS_kesehatan_tambahan_IV' => ''
        ];
    }

        $this->view('kepegawaian/input', [
            'pegawai'             => $pegawai,
            'bpjsKeluarga'        => $keluarga,
            'bpjsTambahan'        => $tambahan,
            'jenis_kelamin'       => $this->model('JenisKelaminModel')->getAll(),
            'agama'               => $this->model('AgamaModel')->getAll(),
            'status_pernikahan'   => $this->model('StatusPernikahanModel')->getAll(),
            'status_kepegawaian'  => $this->model('StatusKepegawaianModel')->getAll(),
            'golongan_pegawai'    => $this->model('GolonganPegawaiModel')->getAll(),
            'kategori_kepegawaian'=> $this->model('KategoriKepegawaianModel')->getAll(),
            'unit_kerja'          => $this->model('UnitKerjaModel')->getAll(),   // ✅ tambahkan ini
            'pendidikan'          => $this->model('PendidikanModel')->getAllNoLimit(),
            'title'               => $title,
            'loadFormLogic'       => true,
            'pegawaiList'         => $pegawaiList,
            'isEdit'              => $isEdit
        ]);
    }

    // === SIMPAN PEGAWAI ===
    public function savePegawai()
    {
        $this->authorizeSession();
        $data  = $_POST;
        $files = $_FILES;

        $nip    = $data['nomor_induk_pegawai'];
        $isEdit = isset($data['mode']) && $data['mode'] === 'edit';

        // 🔒 Guard akses sesuai AccessControl
        if ($isEdit) {
            // edit data pegawai
            $pegawaiLama = $this->model('PegawaiModel')->findByNip($nip);
            if (!$pegawaiLama) {
                echo "<div class='alert alert-danger'>Data pegawai tidak ditemukan.</div>";
                exit;
            }

            $isOwner = ($_SESSION['user']['nomor_induk_pegawai'] ?? null) === $nip;

            if ($isOwner) {
            // kalau role admin/administrator/admin3 → tetap lolos pakai update
            if (in_array(strtolower($_SESSION['user']['hak_akses']), ['admin','administrator','admin3'])) {
                if (!AccessControl::can('pegawai.daftar_aktif','update')) {
                    echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit data pegawai.</div>";
                    exit;
                }
            } else {
                if (!AccessControl::can('pegawai.daftar_aktif','update-own')) {
                    echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit data Anda sendiri.</div>";
                    exit;
                }
            }
        } else {
            if (!AccessControl::can('pegawai.daftar_aktif','update')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit data pegawai lain.</div>";
                exit;
            }
        }
        } else {
            // tambah pegawai baru
            if (!AccessControl::can('pegawai.daftar_aktif','create')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat menambah data pegawai baru.</div>";
                exit;
            }
        }

        // ✅ Tambahkan validasi unit kerja di sini
        $role = strtolower($_SESSION['user']['hak_akses'] ?? '');
        $isDirektur = ($role === 'direktur');

        if (!$isDirektur && empty($data['unit_id'])) {
            echo "<div class='alert alert-danger'>Unit kerja wajib dipilih.</div>";
            exit;
        }

        if ($isDirektur) {
            $data['unit_id'] = null; // Direktur tanpa unit kerja
        }

        if (($data['unit_id'] ?? '') === 'new') {
            $unitKerjaBaru = trim($data['unit_kerja_baru'] ?? '');
            if ($unitKerjaBaru !== '') {
                $unitKerjaModel = $this->model('UnitKerjaModel');

                if (!$unitKerjaModel->exists($unitKerjaBaru)) {
                    $newUnitId = $unitKerjaModel->save($unitKerjaBaru); // ✅ model return ID baru
                } else {
                    $newUnitId = $unitKerjaModel->getIdByName($unitKerjaBaru);
                }

                $data['unit_id'] = $newUnitId;
            } else {
                echo "<div class='alert alert-danger'>Nama unit kerja baru wajib diisi.</div>";
                exit;
            }
        }

        $pegawaiModel = $this->model('PegawaiModel');
        $strModel     = $this->model('StrModel');

        // ✅ Tambahkan normalisasi di sini
        $jumlah_anak = isset($data['jumlah_anak']) && $data['jumlah_anak'] !== ''
            ? (int)$data['jumlah_anak']
            : 0; // default 0 kalau belum menikah

        $kategori_kepegawaian_id = !empty($data['kategori_kepegawaian_id'])
            ? (int)$data['kategori_kepegawaian_id']
            : null;

        // Ambil data lama kalau mode edit
        if ($isEdit) {
            $pegawaiLama = $pegawaiModel->findByNip($nip);
            $strLama     = $strModel->findByNip($nip);
        }

        // Upload File dengan fallback
        $ijazahPath = !empty($files['berkas_ijazah_terakhir']['name'])
            ? $this->uploadPDF($files['berkas_ijazah_terakhir'], 'ijazah', $nip)
            : ($isEdit ? $pegawaiLama['berkas_ijazah_terakhir'] : null);

        $photoPath = !empty($files['photo']['name'])
            ? $this->uploadImage($files['photo'], 'foto', $nip)
            : ($isEdit ? $pegawaiLama['photo'] : null);

        $strPath = !empty($files['berkas_STR']['name'])
            ? $this->uploadPDF($files['berkas_STR'], 'str', $nip)
            : ($isEdit ? $strLama['berkas_STR'] : null);

        // >>> Tambahkan di sini sebelum menyusun $pegawaiData dan $bpjsData
        $kepesertaan = $data['kepesertaan_BPJS_kesehatan'] ?? 'Belum';

        if ($kepesertaan === 'Ya') {
            $statusBpjs = $data['status_BPJS_kesehatan'] ?? 'Sendiri';

            // Pegawai sendiri
            $data['nomor_kartu_BPJS_kesehatan'] = $data['nomor_kartu_BPJS_kesehatan'] ?? null;

            if ($statusBpjs === 'Keluarga' || $statusBpjs === 'Keluarga dan Tambahan') {
                // Suami/Istri
                $data['nomor_kartu_BPJS_kesehatan_suami_istri']     = $data['nomor_kartu_BPJS_kesehatan_suami_istri'] ?? null;
                $data['nama_lengkap_suami_istri']                   = $data['nama_lengkap_suami_istri'] ?? null;
                $data['status_keaktifan_BPJS_kesehatan_suami_istri'] = $data['status_keaktifan_BPJS_kesehatan_suami_istri'] ?? 'Non Aktif';

                // Anak I
                $data['nomor_kartu_BPJS_kesehatan_anak_I']          = $data['nomor_kartu_BPJS_kesehatan_anak_I'] ?? null;
                $data['nama_lengkap_anak_I']                        = $data['nama_lengkap_anak_I'] ?? null;
                $data['status_keaktifan_BPJS_kesehatan_anak_I']     = $data['status_keaktifan_BPJS_kesehatan_anak_I'] ?? 'Non Aktif';

                // Anak II
                $data['nomor_kartu_BPJS_kesehatan_anak_II']         = $data['nomor_kartu_BPJS_kesehatan_anak_II'] ?? null;
                $data['nama_lengkap_anak_II']                       = $data['nama_lengkap_anak_II'] ?? null;
                $data['status_keaktifan_BPJS_kesehatan_anak_II']    = $data['status_keaktifan_BPJS_kesehatan_anak_II'] ?? 'Non Aktif';

                // Anak III
                $data['nomor_kartu_BPJS_kesehatan_anak_III']        = $data['nomor_kartu_BPJS_kesehatan_anak_III'] ?? null;
                $data['nama_lengkap_anak_III']                      = $data['nama_lengkap_anak_III'] ?? null;
                $data['status_keaktifan_BPJS_kesehatan_anak_III']   = $data['status_keaktifan_BPJS_kesehatan_anak_III'] ?? 'Non Aktif';
            }

            if ($statusBpjs === 'Keluarga dan Tambahan') {
                // Tambahan I
                $data['nomor_kartu_BPJS_kesehatan_tambahan_I']      = $data['nomor_kartu_BPJS_kesehatan_tambahan_I'] ?? null;
                $data['nama_lengkap_tambahan_I']                    = $data['nama_lengkap_tambahan_I'] ?? null;
                $data['status_keaktifan_BPJS_kesehatan_tambahan_I'] = $data['status_keaktifan_BPJS_kesehatan_tambahan_I'] ?? 'Non Aktif';

                // Tambahan II
                $data['nomor_kartu_BPJS_kesehatan_tambahan_II']     = $data['nomor_kartu_BPJS_kesehatan_tambahan_II'] ?? null;
                $data['nama_lengkap_tambahan_II']                   = $data['nama_lengkap_tambahan_II'] ?? null;
                $data['status_keaktifan_BPJS_kesehatan_tambahan_II']= $data['status_keaktifan_BPJS_kesehatan_tambahan_II'] ?? 'Non Aktif';

                // Tambahan III
                $data['nomor_kartu_BPJS_kesehatan_tambahan_III']    = $data['nomor_kartu_BPJS_kesehatan_tambahan_III'] ?? null;
                $data['nama_lengkap_tambahan_III']                  = $data['nama_lengkap_tambahan_III'] ?? null;
                $data['status_keaktifan_BPJS_kesehatan_tambahan_III']= $data['status_keaktifan_BPJS_kesehatan_tambahan_III'] ?? 'Non Aktif';

                // Tambahan IV
                $data['nomor_kartu_BPJS_kesehatan_tambahan_IV']     = $data['nomor_kartu_BPJS_kesehatan_tambahan_IV'] ?? null;
                $data['nama_lengkap_tambahan_IV']                   = $data['nama_lengkap_tambahan_IV'] ?? null;
                $data['status_keaktifan_BPJS_kesehatan_tambahan_IV']= $data['status_keaktifan_BPJS_kesehatan_tambahan_IV'] ?? 'Non Aktif';
            }
        } else {
            // Kalau Belum → kosongkan semua field supaya tidak ada warning
            $data['status_BPJS_kesehatan'] = null;
            $data['nomor_kartu_BPJS_kesehatan'] = null;

            // Suami/Istri
            $data['nomor_kartu_BPJS_kesehatan_suami_istri'] = null;
            $data['nama_lengkap_suami_istri'] = null;
            $data['status_keaktifan_BPJS_kesehatan_suami_istri'] = null;

            // Anak I–III
            $data['nomor_kartu_BPJS_kesehatan_anak_I'] = null;
            $data['nama_lengkap_anak_I'] = null;
            $data['status_keaktifan_BPJS_kesehatan_anak_I'] = null;

            $data['nomor_kartu_BPJS_kesehatan_anak_II'] = null;
            $data['nama_lengkap_anak_II'] = null;
            $data['status_keaktifan_BPJS_kesehatan_anak_II'] = null;

            $data['nomor_kartu_BPJS_kesehatan_anak_III'] = null;
            $data['nama_lengkap_anak_III'] = null;
            $data['status_keaktifan_BPJS_kesehatan_anak_III'] = null;

            // Tambahan I–IV
            $data['nomor_kartu_BPJS_kesehatan_tambahan_I'] = null;
            $data['nama_lengkap_tambahan_I'] = null;
            $data['status_keaktifan_BPJS_kesehatan_tambahan_I'] = null;

            $data['nomor_kartu_BPJS_kesehatan_tambahan_II'] = null;
            $data['nama_lengkap_tambahan_II'] = null;
            $data['status_keaktifan_BPJS_kesehatan_tambahan_II'] = null;

            $data['nomor_kartu_BPJS_kesehatan_tambahan_III'] = null;
            $data['nama_lengkap_tambahan_III'] = null;
            $data['status_keaktifan_BPJS_kesehatan_tambahan_III'] = null;

            $data['nomor_kartu_BPJS_kesehatan_tambahan_IV'] = null;
            $data['nama_lengkap_tambahan_IV'] = null;
            $data['status_keaktifan_BPJS_kesehatan_tambahan_IV'] = null;
        }

        // >>> Tambahkan validasi khusus Non Pegawai
        if (!empty($data['status_kepegawaian_id'])) {
            // misalnya ID 3 adalah Non Pegawai
            if ((int)$data['status_kepegawaian_id'] === 3) {
                // kosongkan semua field BPJS
                $data['kepesertaan_BPJS_ketenagakerjaan'] = null;
                $data['kepesertaan_BPJS_kesehatan']       = null;
                $data['status_BPJS_kesehatan']            = null;
                $data['nomor_kartu_BPJS_ketenagakerjaan'] = null;
                $data['nomor_kartu_BPJS_kesehatan']       = null;

                // keluarga & tambahan juga dikosongkan
                $data['nomor_kartu_BPJS_kesehatan_suami_istri'] = null;
                $data['nama_lengkap_suami_istri'] = null;
                $data['status_keaktifan_BPJS_kesehatan_suami_istri'] = null;

                $data['nomor_kartu_BPJS_kesehatan_anak_I'] = null;
                $data['nama_lengkap_anak_I'] = null;
                $data['status_keaktifan_BPJS_kesehatan_anak_I'] = null;

                $data['nomor_kartu_BPJS_kesehatan_anak_II'] = null;
                $data['nama_lengkap_anak_II'] = null;
                $data['status_keaktifan_BPJS_kesehatan_anak_II'] = null;

                $data['nomor_kartu_BPJS_kesehatan_anak_III'] = null;
                $data['nama_lengkap_anak_III'] = null;
                $data['status_keaktifan_BPJS_kesehatan_anak_III'] = null;

                $data['nomor_kartu_BPJS_kesehatan_tambahan_I'] = null;
                $data['nama_lengkap_tambahan_I'] = null;
                $data['status_keaktifan_BPJS_kesehatan_tambahan_I'] = null;

                $data['nomor_kartu_BPJS_kesehatan_tambahan_II'] = null;
                $data['nama_lengkap_tambahan_II'] = null;
                $data['status_keaktifan_BPJS_kesehatan_tambahan_II'] = null;

                $data['nomor_kartu_BPJS_kesehatan_tambahan_III'] = null;
                $data['nama_lengkap_tambahan_III'] = null;
                $data['status_keaktifan_BPJS_kesehatan_tambahan_III'] = null;

                $data['nomor_kartu_BPJS_kesehatan_tambahan_IV'] = null;
                $data['nama_lengkap_tambahan_IV'] = null;
                $data['status_keaktifan_BPJS_kesehatan_tambahan_IV'] = null;
            }
        }

        // ✅ Validasi tanggal masuk sebelum menyusun $pegawaiData
        if (!empty($data['tanggal_masuk'])) {
            $tanggalMasuk = DateTime::createFromFormat('Y-m-d', $data['tanggal_masuk']);
            if (!$tanggalMasuk) {
                echo "<div class='alert alert-danger'>Format tanggal masuk tidak valid (gunakan YYYY-MM-DD).</div>";
                exit;
            }
            // jika valid, simpan hasil format
            $data['tanggal_masuk'] = $tanggalMasuk->format('Y-m-d');
        } else {
            // jika kosong, default ke tanggal hari ini
            $data['tanggal_masuk'] = date('Y-m-d');
        }

        // ✅ Tambahkan di sini (sesudah validasi tanggal masuk, sebelum $pegawaiData)
        if (!empty($data['status_kepegawaian_id'])) {
            $statusId = (int)$data['status_kepegawaian_id'];
            // misalnya 1 = Kontrak, 3 = Non Pegawai
            if ($statusId === 1 || $statusId === 3) {
                $data['golongan_pegawai_id'] = 1; // isi dengan ID Honorer di tabel
            }
        }

        // Data Pegawai 
        $pegawaiData = [
            'nomor_induk_kependudukan' => $data['nomor_induk_kependudukan'],
            'nomor_induk_pegawai'      => $nip,
            'nama_lengkap'             => $data['nama_lengkap'],
            'tempat_lahir'             => $data['tempat_lahir'],
            'tanggal_lahir'            => $data['tanggal_lahir'],
            'jenis_kelamin_id'         => $data['jenis_kelamin_id'],
            'agama_id'                 => $data['agama_id'],
            'alamat_tempat_tinggal'    => $data['alamat_tempat_tinggal'],
            'email'                    => $data['email'],
            'nomor_telepon'            => $data['nomor_telepon'],
            'status_pernikahan_id'     => $data['status_pernikahan_id'],
            'jumlah_anak'              => $jumlah_anak,
            'pendidikan_id'            => $data['pendidikan_id'],
            'pendidikan_terakhir'      => $data['pendidikan_terakhir'],
            'tanggal_masuk'            => $data['tanggal_masuk'],
            'berkas_ijazah_terakhir'   => $ijazahPath,
            'nama_bank'                => $data['nama_bank'],
            'nomor_rekening'           => $data['nomor_rekening'],
            'status_kepegawaian_id'    => $data['status_kepegawaian_id'],
            'golongan_pegawai_id'      => $data['golongan_pegawai_id'],
            'kategori_kepegawaian_id'  => $data['kategori_kepegawaian_id'],
            'kepesertaan_BPJS_ketenagakerjaan' => $data['kepesertaan_BPJS_ketenagakerjaan'],
            'kepesertaan_BPJS_kesehatan'       => $data['kepesertaan_BPJS_kesehatan'],
            'status_BPJS_kesehatan'            => $data['status_BPJS_kesehatan'],
            'unit_id'                 => $data['unit_id'],    // ✅ tambahkan ini
            'photo'                    => $photoPath
        ];
      
        // BPJS Data
        $bpjsData = [
            'nomor_induk_pegawai'              => $nip,
            'nomor_kartu_BPJS_ketenagakerjaan' => $data['nomor_kartu_BPJS_ketenagakerjaan'] ?? null,
            'nomor_kartu_BPJS_kesehatan'       => $data['nomor_kartu_BPJS_kesehatan'] ?? null
        ];

        $bpjsKeluargaData = [
            'nomor_induk_pegawai'                        => $nip,
            'nomor_kartu_BPJS_kesehatan_suami_istri'      => $data['nomor_kartu_BPJS_kesehatan_suami_istri'] ?? null,
            'nama_lengkap_suami_istri'                    => $data['nama_lengkap_suami_istri'] ?? null,
            'status_keaktifan_BPJS_kesehatan_suami_istri' => $data['status_keaktifan_BPJS_kesehatan_suami_istri'] ?? 'Non Aktif',
            'nomor_kartu_BPJS_kesehatan_anak_I'           => $data['nomor_kartu_BPJS_kesehatan_anak_I'] ?? null,
            'nama_lengkap_anak_I'                         => $data['nama_lengkap_anak_I'] ?? null,
            'status_keaktifan_BPJS_kesehatan_anak_I'      => $data['status_keaktifan_BPJS_kesehatan_anak_I'] ?? 'Non Aktif',
            'nomor_kartu_BPJS_kesehatan_anak_II'          => $data['nomor_kartu_BPJS_kesehatan_anak_II'] ?? null,
            'nama_lengkap_anak_II'                        => $data['nama_lengkap_anak_II'] ?? null,
            'status_keaktifan_BPJS_kesehatan_anak_II'     => $data['status_keaktifan_BPJS_kesehatan_anak_II'] ?? 'Non Aktif',
            'nomor_kartu_BPJS_kesehatan_anak_III'         => $data['nomor_kartu_BPJS_kesehatan_anak_III'] ?? null,
            'nama_lengkap_anak_III'                       => $data['nama_lengkap_anak_III'] ?? null,
            'status_keaktifan_BPJS_kesehatan_anak_III'    => $data['status_keaktifan_BPJS_kesehatan_anak_III'] ?? 'Non Aktif',
        ];

        $bpjsTambahanData = [
            'nomor_induk_pegawai'                         => $nip,
            'nomor_kartu_BPJS_kesehatan_tambahan_I'       => $data['nomor_kartu_BPJS_kesehatan_tambahan_I'] ?? null,
            'nama_lengkap_tambahan_I'                     => $data['nama_lengkap_tambahan_I'] ?? null,
            'status_keaktifan_BPJS_kesehatan_tambahan_I'  => $data['status_keaktifan_BPJS_kesehatan_tambahan_I'] ?? 'Non Aktif',
            'nomor_kartu_BPJS_kesehatan_tambahan_II'      => $data['nomor_kartu_BPJS_kesehatan_tambahan_II'] ?? null,
            'nama_lengkap_tambahan_II'                    => $data['nama_lengkap_tambahan_II'] ?? null,
            'status_keaktifan_BPJS_kesehatan_tambahan_II' => $data['status_keaktifan_BPJS_kesehatan_tambahan_II'] ?? 'Non Aktif',
            'nomor_kartu_BPJS_kesehatan_tambahan_III'     => $data['nomor_kartu_BPJS_kesehatan_tambahan_III'] ?? null,
            'nama_lengkap_tambahan_III'                   => $data['nama_lengkap_tambahan_III'] ?? null,
            'status_keaktifan_BPJS_kesehatan_tambahan_III'=> $data['status_keaktifan_BPJS_kesehatan_tambahan_III'] ?? 'Non Aktif',
            'nomor_kartu_BPJS_kesehatan_tambahan_IV'      => $data['nomor_kartu_BPJS_kesehatan_tambahan_IV'] ?? null,
            'nama_lengkap_tambahan_IV'                    => $data['nama_lengkap_tambahan_IV'] ?? null,
            'status_keaktifan_BPJS_kesehatan_tambahan_IV' => $data['status_keaktifan_BPJS_kesehatan_tambahan_IV'] ?? 'Non Aktif',
        ];

        // ✅ Pastikan unit kerja tersimpan di master
        $unitKerjaModel = $this->model('UnitKerjaModel');
        if (!empty($data['unit_id'])) {
                if ($data['unit_id'] === 'new') {
                    $unitKerjaBaru = trim($data['unit_kerja_baru'] ?? '');
                    if ($unitKerjaBaru !== '') {
                        if (!$unitKerjaModel->exists($unitKerjaBaru)) {
                            $data['unit_id'] = $unitKerjaModel->save($unitKerjaBaru); // ✅ model return ID baru
                        } else {
                            $data['unit_id'] = $unitKerjaModel->getIdByName($unitKerjaBaru);
                        }
                    } else {
                        echo "<div class='alert alert-danger'>Nama unit kerja baru wajib diisi.</div>";
                        exit;
                }
            }
        }

        if ($isEdit) {
            $pegawaiModel->update($pegawaiData);
            $this->model('BpjsModel')->update($bpjsData);
            $this->model('BpjsKeluargaModel')->update($bpjsKeluargaData);
            $this->model('BpjsTambahanModel')->update($bpjsTambahanData);

            // Status Pegawai update juga, bukan save
            $statusModel = $this->model('StatusPegawaiModel');
            $statusModel->update([
                    'nomor_induk_pegawai' => $nip,
                    'status_keaktifan'    => 'Aktif',
                    'updated_at'          => date('Y-m-d H:i:s')
            ]);

            // STR pakai update, bukan save
            $kategoriMedis = ['1','2','3'];
            if (in_array($data['kategori_kepegawaian_id'], $kategoriMedis)) {
                  $strModel->update([
                    'nomor_induk_pegawai' => $nip,
                    'nomor_STR'           => $data['nomor_STR'] ?? $strLama['nomor_STR'],
                    'berkas_STR'          => $strPath
                ]);
            }
        } else {
            $result = $pegawaiModel->save($pegawaiData);
            if (!$result) {
                error_log("❌ Gagal menyimpan data pegawai untuk NIP $nip");
                die("Gagal menyimpan data pegawai.");
            }
            $this->model('BpjsModel')->save($bpjsData);
            $this->model('BpjsKeluargaModel')->save($bpjsKeluargaData);
            $this->model('BpjsTambahanModel')->save($bpjsTambahanData);

            // Status Pegawai baru
            $statusModel = $this->model('StatusPegawaiModel');
            $statusModel->save([
                'nomor_induk_pegawai' => $nip,
                'status_keaktifan'    => 'Aktif',
                'updated_at'          => date('Y-m-d H:i:s')
        ]);

            // STR baru
            $kategoriMedis = ['1','2','3'];
            if (in_array($data['kategori_kepegawaian_id'], $kategoriMedis)) {
                $strModel->save([
                    'nomor_induk_pegawai' => $nip,
                    'nomor_STR'           => $data['nomor_STR'] ?? '',
                    'berkas_STR'          => $strPath
                ]);
            }
        }

        // === Generate QR otomatis setelah simpan ===
        require_once __DIR__ . '/../helpers/QrHelper.php';

        $dataQr = BASE_URL . "/kepegawaian/detail_pegawai?nip=" . urlencode($nip);
        $filenameQr = generatePegawaiQr($nip, $dataQr);
        PegawaiQrModel::insertOrUpdateQr($nip, $filenameQr);

        // Redirect ke daftar pegawai
        header('Location: index.php?url=kepegawaian/daftar');
        exit;
        }

    // === FORM SIP ===
    public function formSIP()
    {
        // 🔒 Guard akses sesuai AccessControl
        if (!AccessControl::can('pegawai.sip','create')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat menambah data SIP.</div>";
            exit;
        }

        $pegawaiList = array_filter(
        $this->model('PegawaiModel')->getAllWithStatus(),
        fn($p) =>
            strtolower(trim($p['nama_lengkap'] ?? '')) !== 'administrator' &&
            in_array((int)($p['kategori_kepegawaian_id'] ?? 0), [1, 2, 3])
        );

        $this->view('kepegawaian/sip_tambah', [
            'title'        => 'Input Surat Ijin Praktik',
            'pegawaiList'  => $pegawaiList,
            'sip'          => [],
            'loadFormLogic'=> true
        ]);
    }

    public function tambah_sip()
    {
        $this->formSIP();
    }

    // === FORM SK ===
    public function formSK()
    {
        // 🔒 Guard akses sesuai AccessControl
        if (!AccessControl::can('pegawai.sk','create')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat menambah data SK.</div>";
            exit;
        }

        $pegawaiList = $this->model('PegawaiModel')->getAllAktif();

        $sk = null;
        if (isset($_GET['nip'])) {
            $sk = $this->model('SuratKeputusanModel')->getByNIP($_GET['nip']);
        }

        $this->view('kepegawaian/sk_tambah', [
            'title'        => 'Input Surat Keputusan Pegawai',
            'pegawaiList'  => $pegawaiList,
            'sk'           => $sk,
            'loadFormLogic'=> true
        ]);
    }

    public function tambah_sk()
    {
        $this->formSK();
    }

    // === SIMPAN STR ===
    public function saveSTR()
    {
        $this->authorizeSession();
        $data  = $_POST;
        $files = $_FILES;

        $nip = $data['nip'] ?? null;
        if (!$nip) {
            echo "<div class='alert alert-danger'>Data NIP tidak ditemukan.</div>";
            exit;
        }

        // 🔒 Guard akses sesuai AccessControl
        $isOwner = ($_SESSION['user']['nomor_induk_pegawai'] ?? null) === $nip;

        if ($isOwner) {
            if (!AccessControl::can('pegawai.str','update-own')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit STR Anda sendiri.</div>";
                exit;
            }
        } else {
            if (!AccessControl::can('pegawai.str','update')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit STR pegawai lain.</div>";
                exit;
            }
        }

        $strPath = $this->uploadPDF($files['berkas_STR'], 'str', $data['nip']);

        $this->model('StrModel')->save([
            'nomor_induk_pegawai' => $data['nip'],
            'nomor_STR'           => $data['nomor_STR'],
            'berkas_STR'          => $strPath
        ]);

        header('Location: index.php?url=kepegawaian/str');
    }

    // === SIMPAN / UPDATE SIP ===
    public function saveSIP()
    {
        $this->authorizeSession();
        $data  = $_POST;
        $files = $_FILES;

        // Ambil input wajib
        $nip     = $data['nomor_induk_pegawai'];
        $mulai   = $data['mulai_berlaku_SIP'];
        $berakhir= $data['berakhir_SIP'];

        if (!$nip) {
            echo "<div class='alert alert-danger'>NIP tidak ditemukan.</div>";
            exit;
        }

        // Hitung masa aktif SIP
        $masaAktif = '';
        if ($berakhir) {
            $today = new DateTime('today');   // tanggal sekarang
            $end   = new DateTime($berakhir); // tanggal berakhir SIP
            $diff  = $today->diff($end);

            // kalau sudah lewat, sisa = 0
            $masaAktif = $diff->invert ? 0 : $diff->days;
        }

        // Tentukan mode dari hidden input
        $isEdit = isset($data['mode']) && $data['mode'] === 'edit';
        $namaFileLama = $data['berkas_SIP'] ?? null;

        // 🔒 Guard akses sesuai AccessControl
        $isOwner = ($_SESSION['user']['nomor_induk_pegawai'] ?? null) === $nip;
        $isAdminRole = in_array(strtolower($_SESSION['user']['hak_akses'] ?? ''), ['administrator','admin3']);

        if ($isEdit) {
            if ($isOwner) {
                // Jika owner adalah admin, izinkan langsung
                if (!$isAdminRole && !AccessControl::can('pegawai.sip','update-own')) {
                    echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit SIP Anda sendiri.</div>";
                    exit;
                }
            } else {
                if (!AccessControl::can('pegawai.sip','update')) {
                    echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit SIP pegawai lain.</div>";
                    exit;
                }
            }
        } else {
            if (!AccessControl::can('pegawai.sip','create')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat menambah data SIP.</div>";
                exit;
            }
        }

        $sipPath = null;
        if ($isEdit) {
            // Mode edit
            if ($namaFileLama && isset($files['berkas_SIP']) && $files['berkas_SIP']['error'] === UPLOAD_ERR_OK) {
                // ✅ Overwrite file lama, tidak bikin nama baru
                $sipPath = $this->uploadPDFOverwrite($files['berkas_SIP'], 'sip', $namaFileLama);
            } else {
                // Tidak upload baru → tetap pakai file lama
                $sipPath = $namaFileLama;
            }
        } else {
            // Mode tambah → penomoran otomatis
            if (isset($files['berkas_SIP']) && $files['berkas_SIP']['error'] === UPLOAD_ERR_OK) {
                $sipPath = $this->uploadPDFIncrement($files['berkas_SIP'], 'sip', $nip);
            }
        }

        // Susun data untuk simpan/update
        $dataSIP = [
            'id'                  => $data['id'] ?? null, // penting untuk UPDATE by id
            'nomor_induk_pegawai' => $nip,
            'nomor_SIP'           => $data['nomor_SIP'] ?? '',
            'mulai_berlaku_SIP'   => $mulai,
            'berakhir_SIP'        => $berakhir,
            // Jika tidak upload baru, pakai file lama dari hidden input
            'berkas_SIP'          => $sipPath ?? ($data['berkas_SIP'] ?? null),
            'masa_aktif_SIP'      => $masaAktif
        ];

        // Tentukan mode dari hidden input
        $isEdit = isset($data['mode']) && $data['mode'] === 'edit';

        if ($isEdit) {
            // UPDATE by id — wajib ada id
            if (empty($dataSIP['id'])) {
                echo "<div class='alert alert-danger'>ID SIP untuk update tidak ditemukan.</div>";
                return;
            }
            $this->model('SipModel')->update($dataSIP);
        } else {
            // INSERT
            $this->model('SipModel')->save($dataSIP);
        }

        header('Location: index.php?url=kepegawaian/sip');
    }

    // === SIMPAN / UPDATE SK ===
    public function saveSK()
    {
        $this->authorizeSession();
        $data  = $_POST;
        $files = $_FILES;

        $nip       = $data['nomor_induk_pegawai'] ?? '';
        $mulai     = $data['mulai_berlaku_SK'] ?? '';
        $berakhir  = $data['berakhir_SK'] ?? '';
        $nomorSK   = $data['nomor_SK'] ?? '';
        $namaLama  = $data['berkas_SK'] ?? null;
        $isEdit    = isset($data['mode']) && $data['mode'] === 'edit';

        if (empty($nip)) {
            echo "<div class='alert alert-danger'>NIP tidak ditemukan.</div>";
            exit;
        }

        // 🔒 Guard akses sesuai AccessControl
        $isOwner = ($_SESSION['user']['nomor_induk_pegawai'] ?? null) === $nip;
        $isAdminRole = in_array(strtolower($_SESSION['user']['hak_akses'] ?? ''), ['administrator','admin3']);

        if ($isEdit) {
                if ($isOwner) {
                if (!$isAdminRole && !AccessControl::can('pegawai.sk','update-own')) {
                    echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit SK Anda sendiri.</div>";
                    exit;
                }
            } else {
                if (!AccessControl::can('pegawai.sk','update')) {
                    echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit SK pegawai lain.</div>";
                    exit;
                }
            }
        } else {
            if (!AccessControl::can('pegawai.sk','create')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat menambah data SK.</div>";
                exit;
            }
        }

        // Hitung masa aktif (jumlah hari)
        $masaAktif = 0;
        if (!empty($berakhir)) {
            $today = new DateTime('today');   // tanggal sekarang
            $end   = new DateTime($berakhir); // tanggal berakhir SK
            $diff  = $today->diff($end);

            // kalau sudah lewat, sisa = 0
                $masaAktif = $diff->invert ? 0 : (int)$diff->days;
                }

        // Upload file
        $skPath = null;
        if ($isEdit) {
            if ($namaLama && isset($files['berkas_SK']) && $files['berkas_SK']['error'] === UPLOAD_ERR_OK) {
            // tambahkan tahun dari tanggal SK
            $tahun  = !empty($mulai) ? date('Y', strtotime($mulai)) : date('Y');
            $skPath = $this->uploadPDFOverwrite($files['berkas_SK'], 'surat_keputusan/'.$tahun, $namaLama);
            } else {
                $skPath = $namaLama;
            }
        } else {
            if (isset($files['berkas_SK']) && $files['berkas_SK']['error'] === UPLOAD_ERR_OK) {
                // tambahkan tahun dari tanggal SK
                $tahun  = !empty($mulai) ? date('Y', strtotime($mulai)) : date('Y');
                $skPath = $this->uploadPDFIncrement($files['berkas_SK'], 'surat_keputusan/'.$tahun, $nip);
            }
        }

        // Susun data sesuai struktur DB
        $skData = [
            'id'                              => $data['id'] ?? null,
            'nomor_induk_pegawai'            => $nip,
            'nomor_surat_keputusan'          => $nomorSK,
            'mulai_berlaku_surat_keputusan'  => $mulai,
            'berakhir_surat_keputusan'       => $berakhir,
            'berkas_surat_keputusan'         => $skPath ?? ($data['berkas_SK'] ?? null),
            'masa_aktif_surat_keputusan'     => $masaAktif
            ];

        $model = $this->model('SuratKeputusanModel');
        if ($isEdit) {
            $model->update($skData);
        } else {
            $model->save($skData);
        }

        header('Location: index.php?url=kepegawaian/sk');
    }

    // === EDIT SIP ===
    public function sip_edit()
    {
        $this->authorizeSession(); // atau authorizeAdmin() sesuai aturan akses

        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "<div class='alert alert-danger'>ID SIP tidak ditemukan.</div>";
            return;
        }

        $sip = $this->model('SipModel')->getById($id);
        if (!$sip) {
            echo "<div class='alert alert-danger'>Data SIP tidak ditemukan.</div>";
            return;
        }

        $nip     = $sip['nomor_induk_pegawai'] ?? null;
        $userNip = $_SESSION['user']['nomor_induk_pegawai'] ?? null;
        $role    = strtolower($_SESSION['user']['hak_akses'] ?? 'user');

        // Admin/administrator tidak dianggap owner (selalu boleh update)
        $isAdminRole = in_array($role, ['administrator','admin3','admin']); 
        $isOwner     = ($userNip === $nip);

        // 🔒 Guard akses sesuai AccessControl
        if ($isOwner) {
            if (!$isAdminRole && !AccessControl::can('pegawai.sip','update-own')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit SIP Anda sendiri.</div>";
                exit;
            }
        }  else {
            if (!AccessControl::can('pegawai.sip','update')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit SIP pegawai lain.</div>";
                exit;
            }
        }

        $pegawaiList = $this->model('PegawaiModel')->getAll();

        $this->view('kepegawaian/sip_tambah', [
            'title'        => 'Edit Surat Ijin Praktik',
            'sip'          => $sip,
            'pegawaiList'  => $pegawaiList,
            'mode'         => 'edit',
            'loadFormLogic'=> true
        ]);
    }

    // === EDIT SK ===
    public function sk_edit()
    {
        $this->authorizeSession(); // atau authorizeAdmin() sesuai aturan akses

        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "<div class='alert alert-danger'>ID SK tidak ditemukan.</div>";
            return;
        }

        $sk = $this->model('SuratKeputusanModel')->getById($id);
        if (!$sk) {
            echo "<div class='alert alert-danger'>Data SK tidak ditemukan.</div>";
            return;
        }

        // Tentukan apakah user adalah pemilik SK
        $nip     = $sk['nomor_induk_pegawai'] ?? null;
        $userNip = $_SESSION['user']['nomor_induk_pegawai'] ?? null;
        $role    = strtolower($_SESSION['user']['hak_akses'] ?? 'user');

        // Admin, admin3, administrator tidak dianggap owner
        $isAdminRole = in_array($role, ['administrator','admin3','admin']); 
        $isOwner     = ($userNip === $nip);

        if ($isOwner) {
            if (!$isAdminRole && !AccessControl::can('pegawai.sk','update-own')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit SK Anda sendiri.</div>";
                exit;
            }
      }  else {
            if (!AccessControl::can('pegawai.sk','update')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit SK pegawai lain.</div>";
                exit;
            }
        }

        $pegawaiList = $this->model('PegawaiModel')->getAll();

        $this->view('kepegawaian/sk_tambah', [
            'title'        => 'Edit Surat Keputusan',
            'sk'           => $sk,
            'pegawaiList'  => $pegawaiList,
            'mode'         => 'edit',
            'loadFormLogic'=> true
        ]);
    }

    // === UTILS: Upload PDF ===
    private function uploadPDF($file, $folder, $nip)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($ext !== 'pdf' || $mime !== 'application/pdf') return null;

        $targetDir = __DIR__ . "/../../public/uploads/pegawai/$folder/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        if (in_array($folder, ['ijazah', 'str'])) {
            $fileName   = "{$folder}_{$nip}.pdf";
            $targetPath = $targetDir . $fileName;
            if (is_file($targetPath)) unlink($targetPath);
        } else {
            $fileName   = "{$folder}_{$nip}_" . uniqid() . ".pdf";
            $targetPath = $targetDir . $fileName;
        }

        return move_uploaded_file($file['tmp_name'], $targetPath) ? $fileName : null;
    }

    private function uploadImage($file, $folder, $nip)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($ext, $allowed) || !in_array($mime, ['image/jpeg','image/png'])) return null;

        $targetDir = __DIR__ . "/../../public/uploads/pegawai/$folder/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        $fileName   = "foto_{$nip}.{$ext}";
        $targetPath = $targetDir . $fileName;

        if (is_file($targetPath)) unlink($targetPath);

        return move_uploaded_file($file['tmp_name'], $targetPath) ? $fileName : null;
    }

    private function uploadPDFIncrement($file, $folder, $nip)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') return null;

        $targetDir = __DIR__ . "/../../public/uploads/pegawai/$folder/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        // Cari semua file yang sudah ada untuk NIP ini
        $files = glob($targetDir . "{$folder}_{$nip}_*.pdf");

        // Tentukan nomor urut terakhir
        $lastNumber = 0;
        foreach ($files as $f) {
            if (preg_match("/{$folder}_{$nip}_(\d+)\.pdf$/", basename($f), $matches)) {
                $num = (int)$matches[1];
                if ($num > $lastNumber) {
                    $lastNumber = $num;
                }
            }
        }

        // Nomor berikutnya
        $nextNumber = $lastNumber + 1;
        $fileName   = "{$folder}_{$nip}_" . str_pad($nextNumber, 2, '0', STR_PAD_LEFT) . ".pdf";
        $targetPath = $targetDir . $fileName;

        return move_uploaded_file($file['tmp_name'], $targetPath) ? $fileName : null;
    }

    private function uploadPDFOverwrite($file, $folder, $namaFileLama)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        // Validasi PDF
        if ($ext !== 'pdf' || $mime !== 'application/pdf') return null;

        $targetDir = __DIR__ . "/../../public/uploads/pegawai/{$folder}/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        // Pastikan nama file lama valid
        $fileName   = basename($namaFileLama);
        $targetPath = $targetDir . $fileName;

        // Overwrite file lama
        if (is_file($targetPath)) {
            unlink($targetPath);
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $fileName; // tetap pakai nama lama
        }

        return null;
    }

    private function uploadSertifikat($file, $nip, $id = null, $tanggalMulai = null)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;

        // Validasi PDF
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($ext !== 'pdf' || $mime !== 'application/pdf') return null;

        // Tentukan tahun (pakai tanggal mulai jika ada, fallback ke tahun sekarang)
        $tahun = !empty($tanggalMulai) ? date('Y', strtotime($tanggalMulai)) : date('Y');

        // Folder konsisten per tahun
        $uploadDir = __DIR__ . "/../../public/uploads/pegawai/sertifikat/{$tahun}/";
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) return null;
        }

        // Tentukan suffix urut per NIP + Tahun
        $suffix = null;
        if ($id) {
            // Mode edit → pakai suffix berkas lama agar tidak berubah
            $lama = $this->model('SertifikatModel')->getById($id);
            if (!empty($lama['berkas'])) {
                if (preg_match("/^sertifikat_{$nip}_(\d+)\.pdf$/", $lama['berkas'], $m)) {
                    $suffix = $m[1];
                }
            }
        }

        if (!$suffix) {
            $next = $this->model('SertifikatModel')->getNextUrutByNIPAndTahun($nip, $tahun);
            $suffix = str_pad((string)$next, 3, '0', STR_PAD_LEFT);
        }

        // Nama file final
        $fileName = "sertifikat_{$nip}_{$suffix}.pdf";
        $target   = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $target)) return null;

        // Simpan ke DB hanya nama file
        return $fileName;
    }

    // === UPDATE STATUS PEGAWAI ===
    public function updateStatusPegawai()
    {
        $this->authorizeSession();
        $data = $_POST;

        $nip    = $data['nomor_induk_pegawai'] ?? null;
        $status = $data['status_keaktifan'] ?? null;

        if (!$nip || !$status) {
            echo "<div class='alert alert-danger'>Data NIP atau status tidak lengkap.</div>";
            exit;
        }

        // 🔒 Guard akses sesuai AccessControl
        $isOwner = ($_SESSION['user']['nomor_induk_pegawai'] ?? null) === $nip;

        if ($isOwner) {
            if (!AccessControl::can('pegawai.daftar_aktif','update-own')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengubah status pegawai Anda sendiri.</div>";
                exit;
            }
        } else {
            if (!AccessControl::can('pegawai.daftar_aktif','update')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengubah status pegawai lain.</div>";
                exit;
            }
        }

        // Update status pegawai utama
        $statusModel = $this->model('StatusPegawaiModel');
        $statusModel->update([
            'nomor_induk_pegawai' => $nip,
            'status_keaktifan'    => $status,
            'alasan_non_aktif'    => $data['alasan_non_aktif'] ?? null,
            'updated_at'          => date('Y-m-d H:i:s')
        ]);

        /// Jika Non Aktif, nonaktifkan akun login (misalnya disable login)
        if ($status === 'Non Aktif') {
            // Bisa kosongkan password atau tambahkan flag di hak_akses_user
            // Contoh: update kolom 'aktif' di tabel user kalau memang ada
            // Kalau tidak ada, cukup rely ke status_pegawai untuk filter akses
        }

        header('Location: index.php?url=kepegawaian/daftar');
        exit;
    }

    // === EDIT DATA SAYA ===
    public function edit_data_saya()
    {
            $this->authorizeSession();

            // Ambil NIP dari session user
            $nip     = $_SESSION['user']['nomor_induk_pegawai'] ?? null;
            $role    = strtolower($_SESSION['user']['hak_akses'] ?? 'user');

            if (!$nip) {
                return $this->redirect('unauthorized');
            }

            // 🔒 Guard akses sesuai kebijakan
            if ($role === 'administrator') {
                // Administrator tidak punya menu Akun Saya
                echo "<div class='alert alert-danger'>Akses ditolak. Administrator tidak berhak mengedit data ini.</div>";
                exit;
            }

            // Admin, Admin2, Admin3, User → boleh edit data miliknya sendiri
            if (!AccessControl::can('profile.edit_data_saya','update-own')) {
                echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak mengedit data ini.</div>";
                exit;
            }

            // Ambil data pegawai berdasarkan NIP
            $pegawai = $this->model('PegawaiModel')->findByNip($nip);

            if (!$pegawai) {
                return $this->redirect('notfound');
            }

            $jenisKelamin       = $this->model('JenisKelaminModel')->getAll();
            $statusPernikahan   = $this->model('StatusPernikahanModel')->getAll();
            $unitKerja          = $this->model('UnitKerjaModel')->getAll();   // ✅ tambahkan ini
            $pendidikan         = $this->model('PendidikanModel')->getAllNoLimit();   // untuk dropdown pendidikan

            // Render view edit data saya
            return $this->view('kepegawaian/edit_data_saya', [
                'pegawai'           => $pegawai,
                'jenis_kelamin'     => $jenisKelamin,
                'status_pernikahan' => $statusPernikahan,
                'unit_kerja'        => $unitKerja,
                'pendidikan'        => $pendidikan,
                'title'             => 'Edit Data Saya',
                'layout'            => 'main'
            ]);
        }

    public function update_data_saya()
    {
        $this->authorizeSession();
        $nip = $_SESSION['user']['nomor_induk_pegawai'] ?? null;
        if (!$nip) return $this->redirect('unauthorized');

        $data = $_POST;
        $data['nomor_induk_pegawai'] = $nip;

        // handle unit kerja baru
        if (($data['unit_id'] ?? '') === 'new') {
            $unitKerjaBaru = trim($data['unit_kerja_baru'] ?? '');
            if ($unitKerjaBaru !== '') {
                $unitKerjaModel = $this->model('UnitKerjaModel');
                if (!$unitKerjaModel->exists($unitKerjaBaru)) {
                    $data['unit_id'] = $unitKerjaModel->save($unitKerjaBaru);
                } else {
                    $data['unit_id'] = $unitKerjaModel->getIdByName($unitKerjaBaru);
                }
            }
        }

        // siapkan parameter sesuai query update di PegawaiModel
        $params = [
            'nomor_induk_kependudukan' => $data['nomor_induk_kependudukan'] ?? null,
            'nama_lengkap'             => $data['nama_lengkap'] ?? null,
            'tempat_lahir'             => $data['tempat_lahir'] ?? null,
            'tanggal_lahir'            => $data['tanggal_lahir'] ?? null,
            'jenis_kelamin_id'         => $data['jenis_kelamin_id'] ?? null,
            'alamat_tempat_tinggal'    => $data['alamat'] ?? null,
            'email'                    => $data['email'] ?? null,
            'nomor_telepon'            => $data['telepon'] ?? null,
            'status_pernikahan_id'     => $data['status_pernikahan_id'] ?? null,
            'jumlah_anak'              => $data['jumlah_anak'] ?? 0,
            'nama_bank'                => $data['nama_bank'] ?? null,
            'nomor_rekening'           => $data['nomor_rekening'] ?? null,
            'unit_id'                  => $data['unit_id'] ?? null,
            'pendidikan_id'            => $data['pendidikan_id'] ?? null,       // ✅ baru
            'pendidikan_terakhir'      => $data['pendidikan_terakhir'] ?? null, // ✅ baru
            'tanggal_masuk'            => $data['tanggal_masuk'] ?? null,       // ✅ baru
            'photo'                    => !empty($_FILES['photo']['name'])
                                           ? $this->uploadImage($_FILES['photo'], 'foto', $nip)
                                           : ($data['photo'] ?? null),
            'nomor_induk_pegawai'      => $nip // untuk WHERE
        ];

        $this->model('PegawaiModel')->updatePartial($params);

        return $this->redirect('kepegawaian/edit_data_saya&success=1');

    }

    // === GRAFIK KEPEGAWAIAN ===
    public function grafik()
    {
        $this->authorizeSession();

        // 🔒 Guard akses sesuai AccessControl
        if (!AccessControl::can('pegawai.grafik','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Anda tidak berhak melihat grafik kepegawaian.</div>";
            exit;
        }

        // Ambil data dari model
        $pegawaiModel = $this->model('PegawaiModel');

        $grafikJenisKelamin = $pegawaiModel->countByJenisKelamin();
        $grafikPendidikan   = $pegawaiModel->countByPendidikan();
        $grafikStatus       = $pegawaiModel->countByStatusKepegawaian();
        $grafikKategori     = $pegawaiModel->countByKategoriKepegawaian();
        $grafikLamaKerja = $pegawaiModel->countByLamaKerja(); // misalnya hitung berdasarkan tahun masuk

        // Kirim ke view
        return $this->view('kepegawaian/grafik', [
            'grafikJenisKelamin' => $grafikJenisKelamin,
            'grafikPendidikan'   => $grafikPendidikan,
            'grafikStatus'       => $grafikStatus,
            'grafikKategori'     => $grafikKategori,
            'grafikLamaKerja'    => $grafikLamaKerja,
            'title'              => 'Grafik Kepegawaian',
            'layout'             => 'main'
        ]);
    }

    // === CETAK KEPEGAWAIAN ===
    public function cetak()
    {
        $this->authorizeSession();

        $nip = $_GET['nip'] ?? null;
        if (!$nip) {
            echo "<div class='alert alert-danger'>NIP tidak ditemukan.</div>";
            exit;
        }

        $userNip = $_SESSION['user']['nomor_induk_pegawai'] ?? null;

        // 🔒 Guard akses sesuai AccessControl
        if (AccessControl::can('pegawai.daftar_aktif','print')) {
            // ADMIN/ADMINISTRATOR boleh cetak semua pegawai
        } elseif (AccessControl::can('pegawai.daftar_aktif','print-own')) {
            // USER boleh cetak biodata miliknya sendiri
            if ($userNip !== $nip) {
                echo "<div class='alert alert-danger'>Akses ditolak. Anda hanya dapat mencetak biodata Anda sendiri.</div>";
                exit;
            }
        } else {
            echo "<div class='alert alert-danger'>Akses ditolak. Hanya ADMIN yang dapat mencetak biodata pegawai.</div>";
            exit;
        }

        // Ambil pegawai + unit kerja
        $pegawai = $this->model('PegawaiModel')->getByNIP($nip);

        // Ambil user + jabatan
        $user = $this->model('UserModel')->findByNIP($nip);
        $jbt  = $this->model('JabatanModel')->find($user['jabatan_id'] ?? 0);

        // Ambil semua data di sini
        $institusi = $this->model('InstitusiModel')->getHeader();
        $str       = $this->model('StrModel')->getByNIP($nip);
        if (!$str || !is_array($str)) {
            $str = []; // normalisasi supaya aman di view
        }

        $sip       = $this->model('SipModel')->getByNIP($nip);
        if (!$sip || !is_array($sip)) {
            $sip = [];
        }

        $sk        = $this->model('SuratKeputusanModel')->getByNIP($nip);
        $bpjs      = $this->model('BpjsModel')->getByNIP($nip);
        $keluarga  = $this->model('BpjsKeluargaModel')->getByNIP($nip);
        $tambahan  = $this->model('BpjsTambahanModel')->getByNIP($nip);
        $jk        = $this->model('JenisKelaminModel')->find($pegawai['jenis_kelamin_id']);
        $agama     = $this->model('AgamaModel')->find($pegawai['agama_id']);
        $status    = $this->model('StatusPernikahanModel')->find($pegawai['status_pernikahan_id']);
        $stat_kep  = $this->model('StatusKepegawaianModel')->find($pegawai['status_kepegawaian_id']);
        $gol       = $this->model('GolonganPegawaiModel')->find($pegawai['golongan_pegawai_id']);
        $kat       = $this->model('KategoriKepegawaianModel')->find($pegawai['kategori_kepegawaian_id']);
        $user      = $this->model('UserModel')->findByNIP($nip);
        $pendidikan = $this->model('PendidikanModel')->find($pegawai['pendidikan_id'] ?? 0);
        $jabatan_id = $user['jabatan_id'] ?? 0;

        $jbt = $this->model('JabatanModel')->find($jabatan_id);

        $kategori_id = $pegawai['kategori_kepegawaian_id'] ?? 0;

        // STR dan SIP hanya untuk kategori 1–3
        if (!in_array($kategori_id, [1,2,3])) {
            $str = [];
            $sip = [];
        }

        // fallback kalau tidak ketemu jabatan
        if (!$jbt || empty($jbt['keterangan'])) {
            $jbt = ['nama_jabatan' => 'Karyawan', 'keterangan' => 'Karyawan'];
        }

        // Kirim ke view sebagai array $data
        $this->view('kepegawaian/cetak_data_pegawai', [
            'institusi' => $institusi,
            'pegawai'   => $pegawai,
            'str'       => $str,
            'sip'       => $sip,
            'sk'        => $sk,
            'bpjs'      => $bpjs,
            'keluarga'  => $keluarga,
            'tambahan'  => $tambahan,
            'jk'        => $jk,
            'agama'     => $agama,
            'status'    => $status,
            'stat_kep'  => $stat_kep,
            'gol'       => $gol,
            'kat'       => $kat,
            'user'      => $user,
            'jbt'       => $jbt,
            'pendidikan'=> $pendidikan,
        ], false); // ⬅️ false = tanpa layout
    }

    // === CETAK KARTU PEGAWAI ===
    public function kartu()
    {
        $this->authorizeSession();

        $nip = $_GET['nip'] ?? null;
        if (!$nip) {
            echo "<div class='alert alert-danger'>NIP tidak ditemukan.</div>";
            exit;
        }

        // 🔒 Hanya admin yang boleh cetak kartu
        if (!AccessControl::can('pegawai.daftar_aktif','print')) {
            echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat mencetak kartu pegawai.</div>";
            exit;
        }

        $institusi = $this->model('InstitusiModel')->getHeader();
        $pegawai = $this->model('PegawaiModel')->getByNIP($nip);
        $user      = $this->model('UserModel')->findByNIP($nip);
        $jabatan   = $this->model('JabatanModel')->find($user['jabatan_id'] ?? 0);
        $pendidikan = $this->model('PendidikanModel')->find($pegawai['pendidikan_id'] ?? 0);

        if (!$jabatan || empty($jabatan['keterangan'])) {
            $jabatan = ['keterangan' => 'Karyawan'];
        }

        $this->view('kepegawaian/kartu_pegawai', [
            'institusi' => $institusi,
            'pegawai'   => $pegawai,
            'jbt'       => $jabatan,
            'pendidikan'=> $pendidikan,
        ], false);
    }

}