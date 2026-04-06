<?php
require_once __DIR__ . '/../helpers/AccessControl.php';
require_once __DIR__ . '/../models/RemunerasiModel.php';
require_once __DIR__ . '/../helpers/DateHelper.php';

class RemunerasiController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?url=auth/login');
            exit;
        }
    }

    // === MASTER INDEX REMUNERASI ===
    public function master_index()
    {
        if (!AccessControl::can('remunerasi.master_index','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        // Ambil parameter halaman & pencarian
        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 7; // limit per halaman
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';
        $success = $_GET['success'] ?? null;

        $model = $this->model('RemunerasiModel');

        // Hitung total data sesuai pencarian
        $totalData = $model->count_master_index($search);
        $totalPage = max(ceil($totalData / $perPage), 1);

        // Ambil data dengan LIMIT & OFFSET
        $dataRows = $model->get_master_index_paginated($search, $perPage, $offset);

        // Siapkan info pagination untuk view
        $pagination = [
            'page'      => $page,
            'totalPage' => $totalPage,
            'search'    => $search
        ];

        // Kirim ke view
        $this->view('remunerasi/master_index', [
            'data'       => $dataRows,
            'pagination' => $pagination,
            'layout'     => 'main',
            'title'      => 'Master Index Remunerasi',
            'success'    => $success
        ]);
    }

    // === HASIL REMUNERASI ===
    public function index()
    {
        if (!AccessControl::can('remunerasi.index','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $tahun   = $_GET['tahun'] ?? date('Y');
        $mode    = $_GET['mode'] ?? 'bulanan';
        $page    = $_GET['page'] ?? 1;
        $search  = $_GET['q'] ?? '';
        $limit   = 7;
        $offset  = ($page - 1) * $limit;
        $tiketId = $_GET['tiket_id'] ?? null;

        $model = $this->model('RemunerasiModel');

        // ambil daftar tiket untuk dropdown
        $tiketList = $model->get_tiket_list();

        $hasil     = [];
        $totalData = 0;
        $totalPage = 1;

        if ($tiketId) {
            $tiket = $model->get_tiket_by_id($tiketId);
            if (!$tiket) {
                echo "<div class='alert alert-warning'>Tiket tidak ditemukan.</div>";
                return;
            }

            // jika tiket sudah finalized → ambil hasil dari DB
            if ($tiket['lifecycle'] === 'finalized') {

                $hasil = $model->get_hasil_by_tiket($tiketId);

                $totalData = count($hasil);
                $totalPage = ceil($totalData / $limit);
                $hasil     = array_slice($hasil, $offset, $limit);
            } else {
                // === HITUNG ULANG HASIL ===
                list($periodeMulai, $periodeSelesai, $jumlahBulanPeriode) = getPeriodeRange($tiket['periode'], $tiket['mode']);
                $masterIndex = $model->get_master_index($tiket['periode']);

                foreach ($masterIndex as $pegawai) {
                    $idPegawai  = $pegawai['pegawai_id'];
                    $totalIndex = $pegawai['total_index'];

                    $jumlahCutiHari = $model->get_cuti_by_periode(
                        $idPegawai,
                        $periodeMulai->format('Y-m-d'),
                        $periodeSelesai->format('Y-m-d')
                    );

                    if ($jumlahCutiHari > 70) {
                        $bulanPotong = 3;
                    } elseif ($jumlahCutiHari > 50) {
                        $bulanPotong = 2;
                    } elseif ($jumlahCutiHari > 20) {
                        $bulanPotong = 1;
                    } else {
                        $bulanPotong = 0;
                    }

                    $realKerja = $jumlahBulanPeriode - $bulanPotong;
                    if ($realKerja < 0) $realKerja = 0;

                    $pointAkhir = ($totalIndex / $jumlahBulanPeriode) * $realKerja;

                    $hasil[] = [
                        'pegawai_id'       => $idPegawai,
                        'nip'              => $pegawai['nip'] ?? '-',
                        'nama'             => $pegawai['nama'] ?? '-',
                        'tahun'            => $tiket['tahun'],
                        'mode'             => $tiket['mode'],
                        'periode_nilai'    => $tiket['periode'],
                        'poin_awal'        => $totalIndex,
                        'cuti'             => $bulanPotong,
                        'real_kerja'       => $realKerja,
                        'point_akhir'      => $pointAkhir,
                        'remunerasi_gross' => 0,
                        'remunerasi_net'   => 0,
                        'created_at'       => date('Y-m-d H:i:s')
                    ];
                }

                $totalPointAkhir = array_sum(array_column($hasil, 'point_akhir'));

                foreach ($hasil as &$row) {
                    $gross    = ($tiket['total_nominal'] / $totalPointAkhir) * $row['point_akhir'];
                    $net      = $gross;

                    $row['remunerasi_gross'] = $gross;
                    $row['remunerasi_net']   = $net;

                    // simpan ke tabel hasil
                    $model->save_hasil(
                        $tiket['id'],
                        $row['pegawai_id'],
                        $row['poin_awal'],
                        $row['cuti'],
                        $row['real_kerja'],
                        $row['point_akhir'],
                        $tiket['tahun'],
                        $tiket['mode'],
                        $tiket['periode'],
                        $gross,
                        $net
                    );
                }

                $totalData = count($hasil);
                $totalPage = ceil($totalData / $limit);
                $hasil     = array_slice($hasil, $offset, $limit);
            }
        }

        $pagination = [
            'page'      => (int)$page,
            'totalPage' => (int)$totalPage,
            'search'    => $search
        ];

        $this->view('remunerasi/index', [
            'hasil'         => $hasil,
            'tiketList'     => $tiketList,
            'selectedTiket' => $tiketId,
            'pagination'    => $pagination,
            'layout'        => 'main',
            'title'         => 'Data Remunerasi'
        ]);
    }

    // === GENERATE REMUNERASI ===
    public function generate()
    {
        if (!AccessControl::can('remunerasi.generate','create')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tahun        = (int)($_POST['tahun'] ?? date('Y'));
            $periode      = $_POST['periode'] ?? date('Y-m');
            $totalNominal = (int)($_POST['totalNominal'] ?? 0);
            $mode         = $_POST['mode'] ?? 'bulanan';

            $model = $this->model('RemunerasiModel');

            // === INSERT TIKET REMUNERASI ===
            $tiketId = $model->insert_tiket([
                'tahun'        => $tahun,
                'mode'         => $mode,
                'periode'      => $periode,
                'total_nominal'=> $totalNominal,
                'created_by'   => $_SESSION['user']['id']
            ]);

            // ambil semua pegawai dari master index
            $masterData     = $model->get_master_index_paginated('', PHP_INT_MAX, 0);
            $totalIndexAll  = $model->get_total_index_all($periode);

            if ($totalIndexAll <= 0) {
                return $this->redirect("remunerasi/tiket_list&error=Total index = 0, tidak bisa hitung remunerasi");
            }

            $durasiMap = [
                'bulanan'  => 1,
                'triwulan' => 3,
                'semester' => 6,
                'tahunan'  => 12
            ];
            $periode_nilai = $durasiMap[$mode] ?? 1;

            $totalPointAkhirAll = 0;
            $hasilPegawai = [];

            foreach ($masterData as $row) {
                // skip kalau status kepegawaian = Non Pegawai
                if (strtolower($row['status_kepegawaian'] ?? '') === 'non pegawai') {
                    continue;
                }

                $pegawaiId = $row['pegawai_id'];
                $poinAwal  = $row['total_index'];

                $cuti = $model->get_potongan_cuti($pegawaiId, $periode);
                $realKerja = $periode_nilai - $cuti;
                if ($realKerja < 0) $realKerja = 0;

                $pointAkhir = ($poinAwal / $periode_nilai) * $realKerja;
                $totalPointAkhirAll += $pointAkhir;

                $hasilPegawai[] = [
                    'pegawai_id'    => $pegawaiId,
                    'tahun'         => $tahun,
                    'mode'          => $mode,
                    'periode_nilai' => $periode_nilai,
                    'poin_awal'     => $poinAwal,
                    'cuti'          => $cuti,
                    'real_kerja'    => $realKerja,
                    'point_akhir'   => $pointAkhir
                ];
            }

            foreach ($hasilPegawai as $h) {
                $gross    = $totalNominal;
                $diterima = ($gross / $totalPointAkhirAll) * $h['point_akhir'];

                $model->insert_hasil($tiketId, [
                    'pegawai_id'       => $h['pegawai_id'],
                    'tahun'            => $h['tahun'],
                    'mode'             => $h['mode'],
                    'periode_nilai'    => $h['periode_nilai'],
                    'poin_awal'        => $h['poin_awal'],
                    'cuti'             => $h['cuti'],
                    'real_kerja'       => $h['real_kerja'],
                    'point_akhir'      => $h['point_akhir'],
                    'remunerasi_gross' => $gross,
                    'remunerasi_net'   => $diterima
                ]);
            }

            return $this->redirect("remunerasi/tiket_list&success=Remunerasi periode $periode berhasil digenerate");

        }

        $this->view('remunerasi/form_generate', [
            'layout' => 'main',
            'title'  => 'Generate Remunerasi'
        ]);
    }

    // === GENERATE MASTER INDEX ===
    public function generate_master_index()
    {
        if (!AccessControl::can('remunerasi.master_index','create')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $periode = $_POST['periode'] ?? date('Y-m');
        $pegawaiModel = $this->model('PegawaiModel');
        $remModel     = $this->model('RemunerasiModel');

        // 1. Kosongkan tabel master index lewat model
        $remModel->truncate_master_index();

        // 2. Ambil pegawai aktif
        $pegawaiList = $pegawaiModel->getAll();

        // 3. Loop isi ulang master index
        foreach ($pegawaiList as $p) {
            // skip kalau status kepegawaian = Non Pegawai
            if (strtolower($p['status_kepegawaian'] ?? '') === 'non pegawai') {
                continue;
            }

            $nilaiGolongan   = $remModel->getNilaiGolongan($p['golongan_pegawai_id']);
            $nilaiJabatan    = $remModel->getNilaiJabatan($p['jabatan_id']);
            $nilaiKategori   = $remModel->getNilaiKategori($p['kategori_kepegawaian_id']);
            $nilaiPendidikan = $remModel->getNilaiPendidikan($p['pendidikan_id']);
            $nilaiStatusKepeg= $remModel->getNilaiStatusKepegawaian($p['status_kepegawaian_id']);
            $nilaiStatusNikah= $remModel->getNilaiStatusPernikahan($p['status_pernikahan_id']);

            $lamaKerjaBulan  = $p['lama_kerja_bulan'];
            $lamaKerjaId     = $remModel->getLamaKerjaId($lamaKerjaBulan);
            $nilaiLamaKerja  = $remModel->getNilaiLamaKerja($lamaKerjaBulan);

            $totalIndex = $nilaiGolongan + $nilaiJabatan + $nilaiKategori +
                          $nilaiPendidikan + $nilaiStatusKepeg +
                          $nilaiStatusNikah + $nilaiLamaKerja;

            $remModel->update_master_index($p['pegawai_id'], [
                'golongan_pegawai_id'     => $p['golongan_pegawai_id'],
                'jabatan_id'              => $p['jabatan_id'] ?? 7,
                'kategori_kepegawaian_id' => $p['kategori_kepegawaian_id'],
                'pendidikan_id'           => $p['pendidikan_id'],
                'status_kepegawaian_id'   => $p['status_kepegawaian_id'],
                'status_pernikahan_id'    => $p['status_pernikahan_id'],
                'lama_kerja_id'           => $lamaKerjaId ?? 0,
                'nilai_golongan'          => $nilaiGolongan,
                'nilai_jabatan'           => $nilaiJabatan,
                'nilai_kategori'          => $nilaiKategori,
                'nilai_pendidikan'        => $nilaiPendidikan,
                'nilai_status_kepegawaian'=> $nilaiStatusKepeg,
                'nilai_status_pernikahan' => $nilaiStatusNikah,
                'nilai_lama_kerja'        => $nilaiLamaKerja,
                'total_index'             => $totalIndex,
                'periode'                 => $periode
            ]);
        }

        return $this->redirect("remunerasi/master_index&success=Master index berhasil digenerate");
    }

    // === TIKET REMUNERASI ===
    public function tiket_list()
    {
        if (!AccessControl::can('remunerasi.tiket','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 7; // sesuai permintaan: 1 halaman 7 data
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        $model     = $this->model('RemunerasiModel');
        $totalData = $model->count_tiket($search); // buat fungsi count di model
        $totalPage = max(ceil($totalData / $perPage), 1);

        $tiket     = $model->get_tiket_paginated($search, $perPage, $offset); // buat fungsi paginated di model

        $pagination = [
            'page'      => $page,
            'totalPage' => $totalPage,
            'search'    => $search
        ];

        $this->view('remunerasi/tiket_list', [
            'tiket'      => $tiket,
            'pagination' => $pagination,
            'layout'     => 'main',
            'title'      => 'Daftar Tiket Remunerasi'
        ]);
    }

    public function tiket_batal()
    {
        if (!AccessControl::can('remunerasi.tiket','update')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "<div class='alert alert-danger'>ID tiket tidak ditemukan.</div>";
            exit;
        }

        $model = $this->model('RemunerasiModel');

        // update status tiket jadi batal
        $model->update_tiket_status($id, 'batal');

        // hapus semua hasil remunerasi terkait tiket ini
        $model->delete_hasil_by_tiket($id);

        header("Location: index.php?url=remunerasi/tiket_list&success=Tiket berhasil dibatalkan dan hasil dihapus");
        exit;
    }

    public function edit_tiket()
    {
        if (!AccessControl::can('remunerasi.tiket','update')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $id = $_GET['id'] ?? null;
        if ($id === null) {
            echo "<div class='alert alert-warning'>ID tiket tidak diberikan.</div>";
            return;
        }

        $model = $this->model('RemunerasiModel');
        $tiket = $model->get_tiket_by_id($id);

        if (!$tiket) {
            echo "<div class='alert alert-warning'>Tiket tidak ditemukan.</div>";
            return;
        }

        // gunakan form_generate.php agar create & edit konsisten
        $this->view('remunerasi/form_generate', [
            'tiket'  => $tiket,
            'layout' => 'main',
            'title'  => 'Edit Tiket Remunerasi'
        ]);
    }

    public function update_tiket()
    {
        if (!AccessControl::can('remunerasi.tiket','update')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data = [
            'id'           => $_POST['id'],
            'tahun'        => $_POST['tahun'],
            'mode'         => $_POST['mode'],
            'periode'      => $_POST['periode'],
            'totalNominal' => $_POST['totalNominal'],
            'status'       => $_POST['status'] ?? 'aktif'
        ];

        $model = $this->model('RemunerasiModel');
        $model->update_tiket($data);

        header("Location: index.php?url=remunerasi/tiket_list");
        exit;
    }

    public function finalize() {
        $tiketId = $_POST['tiket_id'] ?? null;
        $model   = $this->model('RemunerasiModel');

        $tiket = $model->get_tiket_by_id($tiketId);
        if (!$tiket) {
            $_SESSION['flash'] = "Tiket tidak ditemukan.";
            header("Location: index.php?url=remunerasi/tiket_list");
            exit;
        }

        // kalau sudah finalized, jangan diubah lagi
        if ($tiket['lifecycle'] === 'finalized') {
            $_SESSION['flash'] = "Tiket ini sudah difinalisasi.";
            header("Location: index.php?url=remunerasi/tiket_list");
            exit;
        }

        $model->finalize_tiket($tiketId);

        $_SESSION['flash'] = "Tiket berhasil difinalisasi.";
        header("Location: index.php?url=remunerasi/tiket_list");
        exit;
    }

    public function unfinalize()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tiketId = $_POST['tiket_id'] ?? null;

            if (!$tiketId) {
                echo "<div class='alert alert-danger'>Tiket ID tidak ditemukan.</div>";
                return;
            }

            // Ambil tiket
            $model = new RemunerasiModel();
            $tiket = $model->get_tiket_by_id($tiketId);

            if (!$tiket) {
                echo "<div class='alert alert-danger'>Tiket tidak ditemukan.</div>";
                return;
            }

            if ($tiket['lifecycle'] !== 'finalized') {
                echo "<div class='alert alert-warning'>Tiket ini belum difinalisasi, tidak bisa dibatalkan.</div>";
                return;
            }

            // Update status kembali ke draft
            $model->unfinalize_tiket($tiketId);

            // Flash message
            $_SESSION['flash'] = "Finalisasi tiket berhasil dibatalkan.";
            header("Location: index.php?url=remunerasi/tiket_list");
            exit;
        }
        
    }

    public function riwayat() {
        $model = $this->model('RemunerasiModel');
        $tiketList = $model->get_tiket_finalized(); // ambil hanya tiket finalized

        $this->view('remunerasi/riwayat', [
            'tiketList' => $tiketList,
            'layout'    => 'main',
            'title'     => 'Riwayat Remunerasi'
        ]);
    }

    public function cetak()
    {
        if (!AccessControl::can('remunerasi.cetak','print')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $id = $_GET['tiket_id'] ?? null;
        if (!$id) {
            echo "<div class='alert alert-danger'>ID tiket tidak ditemukan.</div>";
            exit;
        }

        $model = $this->model('RemunerasiModel');
        $hasil     = $model->getCetakData($id);
        $tiket     = $model->getCetakTiket($id);
        $institusi = $model->getInstitusi();
        $qrcode    = $model->getPetugasQRCode($tiket['created_by']);

        $data = compact('hasil','tiket','institusi','qrcode');
        $this->view('remunerasi/cetak_remunerasi', $data);
    }

}
