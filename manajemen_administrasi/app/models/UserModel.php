<?php

class UserModel extends Model
{
    protected $table = 'user';

    public function findByUsername($username)
    {
        $this->query("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
        $this->bind('username', $username);
        return $this->single();
    }

    public function findFullByUsername($username)
    {
        $this->query("
            SELECT 
                u.id,
                u.username,
                u.password,
                u.nomor_induk_pegawai,
                u.jabatan_id,
                j.nama_jabatan,
                h.hak_akses,
                p.nama_lengkap AS nama_pegawai,
                s.status_keaktifan
            FROM {$this->table} u
            LEFT JOIN hak_akses_user h ON u.username = h.username
            LEFT JOIN pegawai p ON u.nomor_induk_pegawai = p.nomor_induk_pegawai
            LEFT JOIN jabatan j ON u.jabatan_id = j.id
            LEFT JOIN status_pegawai s ON u.nomor_induk_pegawai = s.nomor_induk_pegawai
            WHERE u.username = :username
            LIMIT 1
        ");
        $this->bind('username', $username);
        $user = $this->single();

        if (!$user) {
            return null;
        }

        // ✅ Pengecualian untuk administrator
        if (strtolower($user['username']) === 'admin') {
            // kalau admin tidak punya hak akses, beri default 'Administrator'
            if (empty($user['hak_akses'])) {
                $user['hak_akses'] = 'Administrator';
            }
            return $user;
        }

        // ✅ Tolak login jika status pegawai Non Aktif
        if (!empty($user['status_keaktifan']) && strtolower($user['status_keaktifan']) === 'non aktif') {
            return null;
        }

        return $user;
    }

    public function create($data)
    {
        $this->query("
            INSERT INTO {$this->table} 
            (username, password, nomor_induk_pegawai, jabatan_id) 
            VALUES (:username, :password, :nip, :jabatan_id)
        ");
        $this->bind('username', $data['username']);
        $this->bind('password', $data['password']);
        $this->bind('nip', $data['nomor_induk_pegawai']);
        $this->bind('jabatan_id', $data['jabatan_id']);
        return $this->execute() ? $this->lastInsertId() : false;
    }

    public function findByNIP($nip)
    {
        $this->query("SELECT * FROM {$this->table} WHERE nomor_induk_pegawai = :nip LIMIT 1");
        $this->bind('nip', $nip);
        return $this->single();
    }

    public function findByJabatan($namaJabatan)
    {
        $sql = "SELECT * FROM jabatan WHERE LOWER(nama_jabatan) = LOWER(:nama_jabatan) LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nama_jabatan' => $namaJabatan]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $hashedPassword)
    {
        $this->query("UPDATE {$this->table} SET password = :password WHERE id = :id");
        $this->bind('password', $hashedPassword);
        $this->bind('id', $id);
        return $this->execute();
    }

    public function existsByUsername($username)
    {
        $this->query("SELECT COUNT(*) FROM {$this->table} WHERE username = :username");
        $this->bind('username', $username);
        return $this->singleColumn() > 0;
    }

    public function deleteById($id)
    {
        $this->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->bind('id', $id);
        return $this->execute();
    }

    public function updateByNIP($nip, $data)
    {
        $set = [];
        foreach ($data as $key => $val) {
            $set[] = "$key = :$key";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE nomor_induk_pegawai = :nip";
        $this->query($sql);

        foreach ($data as $key => $val) {
            $this->bind($key, $val);
        }
        $this->bind('nip', $nip);

        return $this->execute();
    }

    public function updateStatusByNIP($nip, $status)
    {
        $this->query("UPDATE status_pegawai 
                      SET status_keaktifan = :status, updated_at = :updated_at 
                      WHERE nomor_induk_pegawai = :nip");
        $this->bind('status', $status);
        $this->bind('updated_at', date('Y-m-d H:i:s'));
        $this->bind('nip', $nip);
        return $this->execute();
    }

    public function findActiveByUsername($username)
    {
        $this->query("
            SELECT u.*, s.status_keaktifan
            FROM {$this->table} u
            LEFT JOIN status_pegawai s 
              ON u.nomor_induk_pegawai = s.nomor_induk_pegawai
            WHERE u.username = :username
            LIMIT 1
        ");
        $this->bind('username', $username);
        $user = $this->single();

        // kalau status Non Aktif → tolak login
        if ($user && strtolower($user['status_keaktifan']) === 'non aktif') {
            return null;
        }
        return $user;
    }

    public function getAllWithJabatanUnit()
    {
        $sql = "
            SELECT 
                u.id,
                j.keterangan AS jabatan_keterangan,
                uk.id AS unit_id,
                uk.nama_unit
            FROM {$this->table} u
            LEFT JOIN jabatan j ON u.jabatan_id = j.id
            LEFT JOIN pegawai p ON u.nomor_induk_pegawai = p.nomor_induk_pegawai
            LEFT JOIN unit_kerja uk ON p.unit_id = uk.id
            ORDER BY j.keterangan, uk.nama_unit
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEligibleNIPForUsername()
    {
        $sql = "
            SELECT p.nomor_induk_pegawai, p.nama_lengkap
            FROM pegawai p
            LEFT JOIN status_pegawai s ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
            WHERE p.status_kepegawaian != 'Non Pegawai'
              AND s.status_keaktifan = 'Aktif'
              AND p.nomor_induk_pegawai NOT IN (
                  SELECT nomor_induk_pegawai FROM {$this->table}
              )
            ORDER BY p.nomor_induk_pegawai ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}