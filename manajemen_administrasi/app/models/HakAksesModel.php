<?php
class HakAksesModel extends Model
{
    protected $table = 'hak_akses_user';

    // Ambil data hak akses + pegawai + jabatan
    public function getAllWithPegawaiAndJabatan()
    {
        $sql = "SELECT u.id, 
                       u.username, 
                       u.nomor_induk_pegawai, 
                       h.hak_akses, 
                       u.jabatan_id, 
                       p.nama_lengkap, 
                       j.keterangan AS nama_jabatan,
                       p.unit_id,
                       uk.nama_unit,
                       s.status_keaktifan
                FROM user u
                LEFT JOIN pegawai p ON u.nomor_induk_pegawai = p.nomor_induk_pegawai
                LEFT JOIN jabatan j ON u.jabatan_id = j.id
                LEFT JOIN hak_akses_user h ON u.username = h.username
                LEFT JOIN unit_kerja uk ON p.unit_id = uk.id
                LEFT JOIN status_pegawai s ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
                WHERE u.username != 'admin'
                    AND s.status_keaktifan = 'Aktif'
                ORDER BY p.nama_lengkap ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cari hak akses berdasarkan username
    public function findByUsername($username)
    {
        $this->query("SELECT * FROM {$this->table} WHERE username = :username");
        $this->bind('username', $username);
        return $this->single();
    }

    // Update jabatan di tabel user
    public function updateJabatan($username, $jabatanId)
    {
        $stmt = $this->db->prepare("UPDATE user SET jabatan_id = :jabatanId WHERE username = :username");
        $stmt->execute(['jabatanId' => $jabatanId, 'username' => $username]);
        return $stmt->rowCount() > 0;
    }

    // Update hak akses
    public function updateHakAkses($username, $hak_akses)
    {
        $this->query("UPDATE {$this->table} SET hak_akses = :hak_akses WHERE username = :username");
        $this->bind('username', $username);
        $this->bind('hak_akses', $hak_akses);
        return $this->execute();
    }

    // Update unit kerja di tabel pegawai
    public function updateUnitKerja($username, $unitKerjaId)
    {
        // ambil NIP dari user
        $stmt = $this->db->prepare("SELECT nomor_induk_pegawai FROM user WHERE username=?");
        $stmt->execute([$username]);
        $nip = $stmt->fetchColumn();

        if ($nip) {
            $update = $this->db->prepare("UPDATE pegawai SET unit_id=? WHERE nomor_induk_pegawai=?");
            $update->execute([$unitKerjaId, $nip]);
        }
    }

    // Insert otomatis hak akses default 'user'
    public function insertAuto($username, $nomor_induk_pegawai)
    {
        $hak_akses = 'user';
        $this->query("INSERT INTO {$this->table} (username, nomor_induk_pegawai, hak_akses)
                      VALUES (:username, :nip, :hak_akses)");
        $this->bind('username', $username);
        $this->bind('nip', $nomor_induk_pegawai);
        $this->bind('hak_akses', $hak_akses);
        return $this->execute();
    }

    // Cek apakah username sudah ada di hak_akses_user
    public function existsByUsername($username)
    {
        $this->query("SELECT COUNT(*) FROM {$this->table} WHERE username = :username");
        $this->bind('username', $username);
        return $this->singleColumn() > 0;
    }

    // Hapus hak akses user
    public function deleteByUsername($username)
    {
        $this->query("DELETE FROM {$this->table} WHERE username = :username");
        $this->bind('username', $username);
        return $this->execute();
    }

    // Ambil data hak akses dengan pagination
    public function getPaginatedAll(int $limit, int $offset, string $search = ''): array
    {
        $sql = "SELECT u.id,
                       u.username,
                       u.nomor_induk_pegawai,
                       h.hak_akses,
                       u.jabatan_id,
                       p.nama_lengkap,
                       j.keterangan AS nama_jabatan,
                       p.unit_id,
                       uk.nama_unit,
                       s.status_keaktifan
                FROM user u
                LEFT JOIN pegawai p ON u.nomor_induk_pegawai = p.nomor_induk_pegawai
                LEFT JOIN jabatan j ON u.jabatan_id = j.id
                LEFT JOIN hak_akses_user h ON u.username = h.username
                LEFT JOIN unit_kerja uk ON p.unit_id = uk.id
                LEFT JOIN status_pegawai s ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
                WHERE u.username != 'admin' AND s.status_keaktifan = 'Aktif'";

        if (!empty($search)) {
            $sql .= " AND (p.nama_lengkap LIKE :search OR u.username LIKE :search)";
        }

        $sql .= " ORDER BY u.nomor_induk_pegawai ASC LIMIT :limit OFFSET :offset";

        $this->query($sql);
        if (!empty($search)) {
            $this->bind('search', "%$search%");
        }
        $this->bind('limit', $limit, PDO::PARAM_INT);
        $this->bind('offset', $offset, PDO::PARAM_INT);

        return $this->resultSet();
    }

    // Hitung total data untuk pagination
    public function countAll(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM user u
                LEFT JOIN pegawai p ON u.nomor_induk_pegawai = p.nomor_induk_pegawai
                LEFT JOIN status_pegawai s ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
                WHERE u.username != 'admin' AND s.status_keaktifan = 'Aktif'";

        if (!empty($search)) {
            $sql .= " AND (p.nama_lengkap LIKE :search OR u.username LIKE :search)";
        }

        $this->query($sql);
        if (!empty($search)) {
            $this->bind('search', "%$search%");
        }

        $row = $this->single();
        return $row ? (int)$row['total'] : 0;
    }

}