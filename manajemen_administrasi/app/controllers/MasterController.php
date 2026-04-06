<?php
require_once __DIR__ . '/../helpers/AccessControl.php';

class MasterController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?url=auth/login');
            exit;
        }
    }

    public function institusi()
    {
        if (!AccessControl::can('pengaturan.institusi','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $model = $this->model('InstitusiModel');
        $institusiList = $model->getAll();
        $editData = null;

        if (isset($_GET['id'])) {
            $editData = $model->findById($_GET['id']);
        }

        $this->view('master/institusi', [
            'title' => 'Data Institusi',
            'layout' => 'dashboard',
            'institusi' => $institusiList,
            'edit' => $editData
        ]);
    }

    public function jenis_kelamin()
    {
        if (!AccessControl::can('master.jenis_kelamin','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $id = $_GET['id'] ?? null;
        $data = $this->model('MasterModel')->getAll('jenis_kelamin');

        $this->view('master/jenis_kelamin', [
            'data' => $data,
            'layout' => 'main',
            'title' => 'Master Data: Jenis Kelamin',
            'edit_id' => $id
        ]);
    }

    public function status_pernikahan()
    {
        if (!AccessControl::can('master.status_pernikahan','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data = $this->model('MasterModel')->getAll('status_pernikahan');

        $this->view('master/status_pernikahan', [
            'data' => $data,
            'layout' => 'main',
            'title' => 'Master Data: Status Pernikahan'
        ]);
    }

    public function status_kepegawaian()
    {
        if (!AccessControl::can('master.status_kepegawaian','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data = $this->model('MasterModel')->getAll('status_kepegawaian');

        $this->view('master/status_kepegawaian', [
            'data' => $data,
            'layout' => 'main',
            'title' => 'Master Data: Status Kepegawaian'
        ]);
    }

    public function golongan_pegawai()
    {
        if (!AccessControl::can('master.golongan_pegawai','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 6;
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        $model = $this->model('GolonganPegawaiModel');
        $totalData = $model->countAll($search);
        $totalPage = max(ceil($totalData / $perPage), 1);
        $data      = $model->getPaginatedAll($perPage, $offset, $search);

        $this->view('master/golongan_pegawai', [
            'data'       => $data,
            'layout'     => 'main',
            'title'      => 'Master Data: Golongan Pegawai',
            'pagination' => [
                'page'      => $page,
                'totalPage' => $totalPage,
                'search'    => $search
            ]
        ]);
    }

    public function getPaginatedAll(int $limit, int $offset, string $search = ''): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND (golongan LIKE :search OR keterangan LIKE :search OR nilai LIKE :search)";
        }
        $sql .= " ORDER BY id ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND (golongan LIKE :search OR keterangan LIKE :search OR nilai LIKE :search)";
        }

        $stmt = $this->db->prepare($sql);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    public function kategori_kepegawaian()
    {
        if (!AccessControl::can('master.kategori_kepegawaian','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data = $this->model('MasterModel')->getAll('kategori_kepegawaian');

        $this->view('master/kategori_kepegawaian', [
            'data' => $data,
            'layout' => 'main',
            'title' => 'Master Data: Kategori Kepegawaian'
        ]);
    }

    public function jabatan()
    {
        if (!AccessControl::can('master.jabatan','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data = $this->model('MasterModel')->getAll('jabatan');

        $this->view('master/jabatan', [
            'data' => $data,
            'layout' => 'main',
            'title' => 'Master Data: Jabatan'
        ]);
    }

    public function agama()
    {
        if (!AccessControl::can('master.agama','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data = $this->model('MasterModel')->getAll('agama');

        $this->view('master/agama', [
            'data' => $data,
            'layout' => 'main',
            'title' => 'Master Data: Agama'
        ]);
    }

    public function unit_kerja()
    {
        if (!AccessControl::can('master.unit_kerja','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        // setup pagination
        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        $offset  = ($page - 1) * $perPage;
        $search  = $_GET['q'] ?? '';

        $model = $this->model('UnitKerjaModel');

        $totalData = $model->countAll($search);
        $totalPage = ceil($totalData / $perPage);
        $data      = $model->getPaginatedAll($perPage, $offset, $search);

        $this->view('master/unit_kerja', [
            'data'       => $data,
            'layout'     => 'main',
            'title'      => 'Master Data: Unit Kerja',
            'pagination' => [
                'page'      => $page,
                'totalPage' => max($totalPage, 1),
                'search'    => $search
            ]
        ]);
    }

    public function unit_kerja_tambah()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Metode tidak diizinkan';
            return;
        }

        if (!AccessControl::can('master.unit_kerja','create')) {
            http_response_code(403);
            echo 'Akses ditolak';
            return;
        }

        $namaUnit = trim($_POST['nama_unit'] ?? '');
        if ($namaUnit === '') {
            http_response_code(400);
            echo 'Nama Unit Kerja wajib diisi';
            return;
        }

        $model = $this->model('UnitKerjaModel');

        // Cek duplikat
        if ($model->exists($namaUnit)) {
            http_response_code(409);
            echo 'Unit Kerja sudah ada';
            return;
        }

        $id = $model->save($namaUnit);
        echo $id ? 'Berhasil ditambahkan' : 'Gagal menambahkan';
    }

    public function sifat_surat_masuk()
    {
        if (!AccessControl::can('master.sifat_surat_masuk','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data = $this->model('MasterModel')->getAll('sifat_surat_masuk');

        $this->view('master/sifat_surat_masuk', [
            'data' => $data,
            'layout' => 'main',
            'title' => 'Master Data: Sifat Surat Masuk'
        ]);
    }

    public function status_surat()
    {
        if (!AccessControl::can('master.status_surat','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data = $this->model('MasterModel')->getAll('status_surat');

        $this->view('master/status_surat', [
            'data' => $data,
            'layout' => 'main',
            'title' => 'Master Data: Status Surat'
        ]);
    }

    public function jenis_surat_keluar()
    {
        if (!AccessControl::can('master.jenis_surat_keluar','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $data = $this->model('MasterModel')->getAll('jenis_surat_keluar');

        $this->view('master/jenis_surat_keluar', [
            'data' => $data,
            'layout' => 'main',
            'title' => 'Master Data: Jenis Surat Keluar'
        ]);
    }

    public function update_inline()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Metode tidak diizinkan';
            return;
        }

        $table = $_GET['table'] ?? null;
        $id    = intval($_POST['id'] ?? 0);
        $field = $_POST['field'] ?? null;
        $value = trim($_POST['value'] ?? '');

        if (!$table || !$id || !$field) {
            http_response_code(400);
            echo 'Data tidak lengkap';
            return;
        }

        $mapMenu = [
            'jenis_kelamin'      => 'master.jenis_kelamin',
            'agama'              => 'master.agama',
            'status_pernikahan'  => 'master.status_pernikahan',
            'status_kepegawaian' => 'master.status_kepegawaian',
            'golongan_pegawai'   => 'master.golongan_pegawai',
            'kategori_kepegawaian'=> 'master.kategori_kepegawaian',
            'jabatan'            => 'master.jabatan',
            'unit_kerja'         => 'master.unit_kerja',
            'lama_kerja'         => 'master.lama_kerja',
            'pendidikan'         => 'master.pendidikan',
            'sifat_surat_masuk'  => 'master.sifat_surat_masuk',
            'status_surat'       => 'master.status_surat',
            'jenis_surat_keluar' => 'master.jenis_surat_keluar'
        ];

        if (!isset($mapMenu[$table]) || !AccessControl::can($mapMenu[$table],'update')) {
            http_response_code(403);
            echo 'Akses ditolak';
            return;
        }

        $allowedTables = [
            'jenis_kelamin' => ['jenis'],
            'agama' => ['nama_agama'],
            'status_pernikahan' => ['status', 'nilai'],
            'status_kepegawaian' => ['status', 'nilai'],
            'golongan_pegawai' => ['golongan', 'nilai'],
            'kategori_kepegawaian' => ['kategori', 'keterangan', 'nilai'],
            'jabatan' => ['nama_jabatan', 'keterangan', 'nilai'],
            'unit_kerja' => ['nama_unit'],
            'lama_kerja' => ['rentang','nilai'],
            'pendidikan' => ['jenjang','keterangan','nilai'],
            'sifat_surat_masuk' => ['nama_sifat'],
            'status_surat' => ['nama_status'],
            'jenis_surat_keluar' => ['nama_jenis','kode']
        ];

        if (!array_key_exists($table, $allowedTables) || !in_array($field, $allowedTables[$table])) {
            http_response_code(403);
            echo 'Tabel atau kolom tidak diizinkan';
            return;
        }

        if (strlen($value) > 255) {
            http_response_code(400);
            echo 'Nilai terlalu panjang';
            return;
        }

        $result = $this->model('MasterModel')->update($table, [$field => $value], ['id' => $id]);

        if ($result) {
            echo 'Berhasil disimpan';
        } else {
            error_log("Gagal update $table: kolom $field, id $id, nilai '$value'");
            http_response_code(500);
            echo 'Gagal menyimpan data';
        }
    }

    public function lama_kerja()
    {
        if (!AccessControl::can('master.lama_kerja','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 6;
        $offset = ($page - 1) * $limit;

        $model = $this->model('LamakerjaModel');
        $data = $model->getAll($limit, $offset);
        $total = $model->countAll();
        $totalPages = ceil($total / $limit);

        $this->view('master/lama_kerja', [
            'data' => $data,
            'layout' => 'main',
            'title' => 'Master Data: Lama Kerja',
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function pendidikan()
    {
        if (!AccessControl::can('master.pendidikan','view')) {
            echo "<div class='alert alert-danger'>Akses ditolak.</div>";
            exit;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 6;
        $offset = ($page - 1) * $limit;

        $model = $this->model('PendidikanModel');
        $data = $model->getAll($limit, $offset);
        $total = $model->countAll();
        $totalPages = ceil($total / $limit);

        $this->view('master/pendidikan', [
            'data' => $data,
            'layout' => 'main',
            'title' => 'Master Data: Pendidikan',
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

}