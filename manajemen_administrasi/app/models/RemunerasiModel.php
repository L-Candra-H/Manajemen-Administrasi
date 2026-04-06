<?php
class RemunerasiModel extends Model
{
    private $table_master = 'master_index_remunerasi';
    private $table_tiket  = 'remunerasi_tiket';
    private $table_hasil  = 'remunerasi_hasil';

    // === MASTER INDEX ===
    public function get_master_index_paginated($search, $limit, $offset)
    {
        $sql = "SELECT m.*, p.nomor_induk_pegawai, p.nama_lengkap
                FROM {$this->table_master} m
                LEFT JOIN pegawai p ON m.pegawai_id = p.id
                WHERE p.nama_lengkap LIKE :search
                ORDER BY p.nomor_induk_pegawai ASC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count_master_index($search)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table_master} m
                LEFT JOIN pegawai p ON m.pegawai_id = p.id
                WHERE p.nama_lengkap LIKE :search";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function update_master_index($pegawai_id, $data)
    {
        $sql = "INSERT INTO {$this->table_master}
                (pegawai_id, golongan_pegawai_id, jabatan_id, kategori_kepegawaian_id, pendidikan_id,
                 status_kepegawaian_id, status_pernikahan_id, lama_kerja_id,
                 nilai_golongan, nilai_jabatan, nilai_kategori, nilai_pendidikan,
                 nilai_status_kepegawaian, nilai_status_pernikahan, nilai_lama_kerja,
                 total_index, periode)
                VALUES (:pegawai_id, :golongan_pegawai_id, :jabatan_id, :kategori_kepegawaian_id, :pendidikan_id,
                        :status_kepegawaian_id, :status_pernikahan_id, :lama_kerja_id,
                        :nilai_golongan, :nilai_jabatan, :nilai_kategori, :nilai_pendidikan,
                        :nilai_status_kepegawaian, :nilai_status_pernikahan, :nilai_lama_kerja,
                        :total_index, :periode)
                ON DUPLICATE KEY UPDATE
                    golongan_pegawai_id = VALUES(golongan_pegawai_id),
                    jabatan_id = VALUES(jabatan_id),
                    kategori_kepegawaian_id = VALUES(kategori_kepegawaian_id),
                    pendidikan_id = VALUES(pendidikan_id),
                    status_kepegawaian_id = VALUES(status_kepegawaian_id),
                    status_pernikahan_id = VALUES(status_pernikahan_id),
                    lama_kerja_id = VALUES(lama_kerja_id),
                    nilai_golongan = VALUES(nilai_golongan),
                    nilai_jabatan = VALUES(nilai_jabatan),
                    nilai_kategori = VALUES(nilai_kategori),
                    nilai_pendidikan = VALUES(nilai_pendidikan),
                    nilai_status_kepegawaian = VALUES(nilai_status_kepegawaian),
                    nilai_status_pernikahan = VALUES(nilai_status_pernikahan),
                    nilai_lama_kerja = VALUES(nilai_lama_kerja),
                    total_index = VALUES(total_index),
                    periode = VALUES(periode)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge(['pegawai_id' => $pegawai_id], $data));
        return $stmt->rowCount();
    }

    // === HASIL REMUNERASI ===
    public function get_hasil_paginated($tahun, $mode, $search, $limit, $offset)
    {
        $sql = "SELECT h.*, p.nomor_induk_pegawai, p.nama_lengkap
                FROM {$this->table_hasil} h
                LEFT JOIN pegawai p ON h.pegawai_id = p.id
                WHERE h.tahun = :tahun
                  AND h.mode = :mode
                  AND (p.nama_lengkap LIKE :search OR p.nomor_induk_pegawai LIKE :search)
                ORDER BY p.nomor_induk_pegawai ASC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tahun', (int)$tahun, PDO::PARAM_INT);
        $stmt->bindValue(':mode', $mode, PDO::PARAM_STR);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count_hasil($tahun, $mode, $search)
    {
        $sql = "SELECT COUNT(*) 
                FROM {$this->table_hasil} h
                LEFT JOIN pegawai p ON h.pegawai_id = p.id
                WHERE h.tahun = :tahun
                  AND h.mode = :mode
                  AND (p.nama_lengkap LIKE :search OR p.nomor_induk_pegawai LIKE :search)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tahun', (int)$tahun, PDO::PARAM_INT);
        $stmt->bindValue(':mode', $mode, PDO::PARAM_STR);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function insert_hasil($tiket_id, $data)
    {
        $sql = "INSERT INTO {$this->table_hasil}
                (tiket_id, pegawai_id, tahun, mode, periode_nilai, poin_awal, cuti, real_kerja, point_akhir, remunerasi_gross, remunerasi_net)
                VALUES
                (:tiket_id, :pegawai_id, :tahun, :mode, :periode_nilai, :poin_awal, :cuti, :real_kerja, :point_akhir, :gross, :net)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tiket_id'     => $tiket_id,
            ':pegawai_id'   => $data['pegawai_id'],
            ':tahun'        => $data['tahun'],
            ':mode'         => $data['mode'],
            ':periode_nilai'=> $data['periode_nilai'],
            ':poin_awal'    => $data['poin_awal'],
            ':cuti'         => $data['cuti'],
            ':real_kerja'   => $data['real_kerja'],
            ':point_akhir'  => $data['point_akhir'],
            ':gross'        => $data['remunerasi_gross'],
            ':net'          => $data['remunerasi_net']
        ]);
        return $this->db->lastInsertId();
    }

    public function insert_tiket($data)
    {
        $sql = "INSERT INTO {$this->table_tiket}
                (tahun, mode, periode, total_nominal, created_by)
                VALUES (:tahun, :mode, :periode, :total_nominal, :created_by)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tahun'        => $data['tahun'],
            ':mode'         => $data['mode'],
            ':periode'      => $data['periode'],
            ':total_nominal'=> $data['total_nominal'],
            ':created_by'   => $data['created_by']
        ]);
        return $this->db->lastInsertId();
    }

    public function get_tiket_list()
    {
        $sql = "SELECT t.*, u.username
                FROM {$this->table_tiket} t
                LEFT JOIN user u ON t.created_by = u.id
                ORDER BY t.created_at DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_hasil_by_tiket($tiketId, $search = '', $limit = null, $offset = null) {
        $sql = "SELECT h.id, h.tiket_id, h.pegawai_id,
                       h.tahun, h.mode, h.periode_nilai,
                       h.poin_awal, h.cuti, h.real_kerja, h.point_akhir,
                       h.remunerasi_gross, h.remunerasi_net,
                       p.nomor_induk_pegawai AS nip,
                       p.nama_lengkap AS nama
                FROM remunerasi_hasil h
                JOIN pegawai p ON h.pegawai_id = p.id
                WHERE h.tiket_id = :tiket_id";

        if (!empty($search)) {
            $sql .= " AND (p.nomor_induk_pegawai LIKE :search OR p.nama_lengkap LIKE :search)";
        }

        $sql .= " ORDER BY p.nomor_induk_pegawai ASC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tiket_id', $tiketId, PDO::PARAM_INT);

        if (!empty($search)) {
            $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update_tiket($data) {
        $sql = "UPDATE remunerasi_tiket 
                SET tahun = :tahun,
                    mode = :mode,
                    periode = :periode,
                    total_nominal = :total_nominal,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tahun'        => $data['tahun'],
            ':mode'         => $data['mode'],
            ':periode'      => $data['periode'],
            ':total_nominal'=> $data['totalNominal'],
            ':status'       => $data['status'],
            ':id'           => $data['id']
        ]);
    }

    public function count_hasil_by_tiket($tiketId, $search = '') {
        $sql = "SELECT COUNT(*) 
                FROM remunerasi_hasil h
                JOIN pegawai p ON h.pegawai_id = p.id
                WHERE h.tiket_id = :tiket_id";

        if (!empty($search)) {
            $sql .= " AND (p.nomor_induk_pegawai LIKE :search OR p.nama_lengkap LIKE :search)";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tiket_id', $tiketId, PDO::PARAM_INT);

        if (!empty($search)) {
            $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function update_tiket_status($id, $status) {
        $sql = "UPDATE remunerasi_tiket SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':status' => $status,
            ':id'     => $id
        ]);
    }

    public function get_tiket_by_id($id) {
        $sql = "SELECT * FROM remunerasi_tiket WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // === TAMBAHAN UNTUK RUMUS REMUNERASI ===
    public function get_total_index_all($periode) {
        $sql = "SELECT SUM(total_index) 
                FROM {$this->table_master}
                WHERE periode = :periode";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':periode', $periode, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function get_total_index_pegawai($pegawai_id, $periode)
    {
        $sql = "SELECT total_index
                FROM {$this->table_master}
                WHERE pegawai_id = :pegawai_id AND periode = :periode";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pegawai_id' => $pegawai_id,
            ':periode'    => $periode
        ]);
        return (float)$stmt->fetchColumn();
    }

    public function get_potongan_cuti($pegawai_id, $periode, $mode = 'bulanan')
    {
        // tentukan range periode berdasarkan mode
        list($periodeMulai, $periodeSelesai, $jumlahBulan) = getPeriodeRange($periode, $mode);

        if ($periodeMulai instanceof DateTime) {
            $periodeMulai = $periodeMulai->format('Y-m-d');
        }
        if ($periodeSelesai instanceof DateTime) {
            $periodeSelesai = $periodeSelesai->format('Y-m-d');
        }

        $sql = "SELECT SUM(DATEDIFF(
                        LEAST(tanggal_selesai, :periodeSelesai),
                        GREATEST(tanggal_mulai, :periodeMulai)
                    ) + 1) as total_cuti
                FROM cuti_pegawai
                WHERE pegawai_id = :pegawai_id
                  AND status_pengajuan = 'Disetujui'
                  AND jenis_cuti != 'Tahunan'
                  AND tanggal_mulai <= :periodeSelesai
                  AND tanggal_selesai >= :periodeMulai";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pegawai_id'    => $pegawai_id,
            ':periodeMulai'  => $periodeMulai,
            ':periodeSelesai'=> $periodeSelesai
        ]);

        return (int)$stmt->fetchColumn();
    }

    // === MAPPING NILAI MASTER DATA ===
    public function getNilaiGolongan($golonganId)
    {
        $stmt = $this->db->prepare("SELECT nilai FROM golongan_pegawai WHERE id = :id");
        $stmt->execute([':id' => $golonganId]);
        return (int)$stmt->fetchColumn();
    }

    public function getNilaiJabatan($jabatanId)
    {
        // kalau null atau kosong, langsung default ke Kary = 1
        if (empty($jabatanId)) {
            return 1;
        }

        $stmt = $this->db->prepare("SELECT nilai FROM jabatan WHERE id = :id");
        $stmt->execute([':id' => $jabatanId]);
        $nilai = $stmt->fetchColumn();

        // kalau tidak ada hasil di tabel, cek apakah id = 7 (Kary)
        if ($nilai === false) {
            if ($jabatanId == 7) {
                return 1;
            }
            return 0; // fallback
        }

        return (int)$nilai;
    }

    public function getNilaiKategori($kategoriId)
    {
        $stmt = $this->db->prepare("SELECT nilai FROM kategori_kepegawaian WHERE id = :id");
        $stmt->execute([':id' => $kategoriId]);
        return (int)$stmt->fetchColumn();
    }

    public function getNilaiPendidikan($pendidikanId)
    {
        $stmt = $this->db->prepare("SELECT nilai FROM pendidikan WHERE id = :id");
        $stmt->execute([':id' => $pendidikanId]);
        return (int)$stmt->fetchColumn();
    }

    public function getNilaiStatusKepegawaian($statusId)
    {
        $stmt = $this->db->prepare("SELECT nilai FROM status_kepegawaian WHERE id = :id");
        $stmt->execute([':id' => $statusId]);
        return (int)$stmt->fetchColumn();
    }

    public function getNilaiStatusPernikahan($statusId)
    {
        $stmt = $this->db->prepare("SELECT nilai FROM status_pernikahan WHERE id = :id");
        $stmt->execute([':id' => $statusId]);
        return (int)$stmt->fetchColumn();
    }

    public function getNilaiLamaKerja($lamaKerjaBulan)
    {
        // Ambil nilai dari tabel lama_kerja sesuai rentang bulan kerja
        $sql = "SELECT nilai 
                FROM lama_kerja 
                WHERE :lamaKerjaBulan BETWEEN mulai AND akhir
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':lamaKerjaBulan' => $lamaKerjaBulan]);
        $nilai = $stmt->fetchColumn();

        // Default minimal 1 kalau tidak ketemu
        return $nilai !== false ? (int)$nilai : 1;
    }

    public function getLamaKerjaId($lamaKerjaBulan)
    {
        $sql = "SELECT id 
                FROM lama_kerja 
                WHERE :bulan BETWEEN mulai AND akhir
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':bulan' => $lamaKerjaBulan]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int)$id : null; // null kalau tidak ketemu
    }

    public function truncate_master_index()
    {
        $this->db->query("TRUNCATE TABLE master_index_remunerasi");
    }

    public function get_master_index() {
        $sql = "SELECT mi.pegawai_id,
                       mi.total_index,
                       p.nomor_induk_pegawai AS nip,
                       p.nama_lengkap AS nama
                FROM master_index_remunerasi mi
                JOIN pegawai p ON p.id = mi.pegawai_id";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_cuti_by_periode($pegawai_id, $periodeMulai, $periodeSelesai)
    {
        if ($periodeMulai instanceof DateTime) {
            $periodeMulai = $periodeMulai->format('Y-m-d');
        }
        if ($periodeSelesai instanceof DateTime) {
            $periodeSelesai = $periodeSelesai->format('Y-m-d');
        }

        $sql = "SELECT SUM(DATEDIFF(
                        LEAST(tanggal_selesai, :periodeSelesai),
                        GREATEST(tanggal_mulai, :periodeMulai)
                    ) + 1) as jumlah_cuti
                FROM cuti_pegawai
                WHERE pegawai_id = :pegawai_id
                  AND status_pengajuan = 'Disetujui'
                  AND jenis_cuti != 'Tahunan'
                  AND tanggal_mulai <= :periodeSelesai
                  AND tanggal_selesai >= :periodeMulai";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pegawai_id'    => $pegawai_id,
            ':periodeMulai'  => $periodeMulai,
            ':periodeSelesai'=> $periodeSelesai
        ]);
        return (int)$stmt->fetchColumn();
    }

    public function save_hasil($tiketId, $pegawaiId, $poinAwal, $cuti, $realKerja, $pointAkhir,
                               $tahun, $mode, $periodeNilai, $gross, $net) {
        $sql = "INSERT INTO remunerasi_hasil
            (tiket_id, pegawai_id, poin_awal, cuti, real_kerja, point_akhir,
             tahun, mode, periode_nilai, remunerasi_gross, remunerasi_net, created_at)
            VALUES (:tiket_id, :pegawai_id, :poin_awal, :cuti, :real_kerja, :point_akhir,
                    :tahun, :mode, :periode_nilai, :gross, :net, NOW())
            ON DUPLICATE KEY UPDATE
                poin_awal = VALUES(poin_awal),
                cuti = VALUES(cuti),
                real_kerja = VALUES(real_kerja),
                point_akhir = VALUES(point_akhir),
                remunerasi_gross = VALUES(remunerasi_gross),
                remunerasi_net = VALUES(remunerasi_net),
                created_at = NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tiket_id' => $tiketId,
            ':pegawai_id' => $pegawaiId,
            ':poin_awal' => $poinAwal,
            ':cuti' => $cuti,
            ':real_kerja' => $realKerja,
            ':point_akhir' => $pointAkhir,
            ':tahun' => $tahun,
            ':mode' => $mode,
            ':periode_nilai' => $periodeNilai,
            ':gross' => $gross,
            ':net' => $net
        ]);
    }

    public function count_tiket($search = '')
    {
        $sql = "SELECT COUNT(*) 
                FROM remunerasi_tiket 
                WHERE periode LIKE :search";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function get_tiket_paginated($search, $limit, $offset)
    {
        $limit  = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT t.*, p.nama_lengkap AS dibuat_oleh
                FROM remunerasi_tiket t
                LEFT JOIN pegawai p ON t.created_by = p.id
                WHERE t.periode LIKE :search
                ORDER BY t.created_at DESC
                LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function finalize_tiket($tiketId) {
        $sql = "UPDATE remunerasi_tiket 
                SET lifecycle = 'finalized', finalized_at = NOW() 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $tiketId]);
    }

    public function unfinalize_tiket($tiketId)
    {
        $sql = "UPDATE remunerasi_tiket 
                SET lifecycle = 'draft', finalized_at = NULL 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $tiketId]);
    }

    public function get_tiket_finalized() {
        $sql = "SELECT id, tahun, mode, periode, total_nominal, finalized_at
                FROM remunerasi_tiket
                WHERE lifecycle = 'finalized'
                ORDER BY finalized_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete_hasil_by_tiket($tiketId)
    {
        $stmt = $this->db->prepare("DELETE FROM remunerasi_hasil WHERE tiket_id = ?");
        $stmt->execute([$tiketId]);
    }

    // === CETAK REMUNERASI ===
    public function getCetakData($tiketId)
    {
        $sql = "SELECT h.remunerasi_net,
                       p.nomor_induk_pegawai AS nip,
                       p.nama_lengkap AS nama,
                       p.nama_bank,
                       p.nomor_rekening
                FROM remunerasi_hasil h
                JOIN pegawai p ON h.pegawai_id = p.id
                WHERE h.tiket_id = :tiket_id
                ORDER BY p.nama_bank, p.nomor_induk_pegawai ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tiket_id', $tiketId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCetakTiket($tiketId)
    {
        $sql = "SELECT t.*, p.nama_lengkap AS dibuat_oleh
                FROM remunerasi_tiket t
                LEFT JOIN pegawai p ON t.created_by = p.id
                WHERE t.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $tiketId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getInstitusi()
    {
        $sql = "SELECT * FROM institusi LIMIT 1";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    public function getPetugasQRCode($pegawaiId)
    {
        $sql = "SELECT q.qr_file
                FROM pegawai_qrcode q
                JOIN pegawai p ON q.nip = p.nomor_induk_pegawai
                WHERE p.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $pegawaiId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}