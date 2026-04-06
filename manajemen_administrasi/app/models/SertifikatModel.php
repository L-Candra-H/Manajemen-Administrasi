<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

class SertifikatModel extends Model
{
    protected $table = 'sertifikat_pegawai';

    public function __construct()
    {
        $this->db = new Database();
    }

    // === GET ALL ===
    public function getAll()
    {
        $query = "
           SELECT 
                s.id,
                s.nomor_induk_pegawai,
                p.nama_lengkap,
                s.nomor_sertifikat,
                s.pemberi_sertifikat,
                s.jenis,
                s.mode,
                s.tanggal_mulai_pelaksanaan,
                s.tanggal_akhir_pelaksanaan,
                s.berkas
            FROM {$this->table} s
            JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
            JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
            WHERE sp.status_keaktifan = 'Aktif'
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === GET BY NIP ===
    public function getByNIP($nip)
    {
        $query = "
            SELECT 
                s.id,
                s.nomor_induk_pegawai,
                p.nama_lengkap,
                s.nomor_sertifikat,
                s.pemberi_sertifikat,
                s.jenis,
                s.mode,
                s.tanggal_mulai_pelaksanaan,
                s.tanggal_akhir_pelaksanaan,
                s.berkas
            FROM sertifikat_pegawai s
            JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
            JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
            WHERE s.nomor_induk_pegawai = :nip
              AND sp.status_keaktifan = 'Aktif'
            ORDER BY s.tanggal_mulai_pelaksanaan DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':nip', $nip, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === SAVE (INSERT) ===
    public function save($data)
    {
        $query = "
            INSERT INTO {$this->table} 
                (nomor_induk_pegawai, jenis, mode, pemberi_sertifikat, nomor_sertifikat, tanggal_mulai_pelaksanaan, tanggal_akhir_pelaksanaan, berkas)
            VALUES 
                (:nip, :jenis, :mode, :pemberi, :nomor, :mulai, :selesai, :berkas)
        ";
        return $this->executeQuery($query, [
            ':nip'     => $data['nomor_induk_pegawai'],
            ':jenis'   => $data['jenis'],
            ':mode'    => $data['mode'],
            ':pemberi' => $data['pemberi_sertifikat'],
            ':nomor'   => $data['nomor_sertifikat'],
            ':mulai'   => $data['tanggal_mulai_pelaksanaan'],
            ':selesai' => $data['tanggal_akhir_pelaksanaan'],
            ':berkas'  => $data['berkas']
        ]);
    }

    // === UPDATE ===
    public function update($data)
    {
        $query = "
            UPDATE {$this->table}
            SET jenis = :jenis,
                mode = :mode,
                pemberi_sertifikat = :pemberi,
                nomor_sertifikat = :nomor,
                tanggal_mulai_pelaksanaan = :mulai,
                tanggal_akhir_pelaksanaan = :selesai,
                berkas = :berkas
            WHERE id = :id
        ";
        return $this->executeQuery($query, [
            ':id'      => $data['id'],
            ':jenis'   => $data['jenis'],
            ':mode'    => $data['mode'],
            ':pemberi' => $data['pemberi_sertifikat'],
            ':nomor'   => $data['nomor_sertifikat'],
            ':mulai'   => $data['tanggal_mulai_pelaksanaan'],
            ':selesai' => $data['tanggal_akhir_pelaksanaan'],
            ':berkas'  => $data['berkas']
        ]);
    }

    // === SAVE OR UPDATE ===
    public function saveOrUpdate($data)
    {
        if ($this->existsById($data['id'])) {
            return $this->update($data);
        } else {
            return $this->save($data);
        }
    }

    // === EXISTS ===
    public function existsById($id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    // === DELETE ===
    public function deleteById($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // === UPDATE FILE BERKAS ===
    public function updateFile($id, $path)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET berkas = :path
            WHERE id = :id
        ");
        return $stmt->execute([
            ':path' => $path,
            ':id'   => $id
        ]);
    }

    // === Helper eksekusi query ===
    private function executeQuery($query, $params)
    {
        $stmt = $this->db->prepare($query);
        $success = $stmt->execute($params);
        if (!$success) {
            error_log("❌ Query gagal di {$this->table}: " . implode(' | ', $stmt->errorInfo()));
        }
        return $success;
    }

    // SertifikatModel.php
    public function getNextUrutByNIP($nip)
    {
        // Ambil semua berkas milik NIP ini
        $sql = "SELECT berkas FROM {$this->table} WHERE nomor_induk_pegawai = :nip AND berkas IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nip' => $nip]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $max = 0;
        foreach ($rows as $r) {
            $berkas = $r['berkas'] ?? '';
            // Cocokkan pola: sertifikat_{nip}_{NNN}.pdf
            if (preg_match("/^sertifikat_{$nip}_(\d+)\.pdf$/", $berkas, $m)) {
                $num = (int)$m[1];
                if ($num > $max) $max = $num;
            }
        }
        return $max + 1;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getNextUrutByNIPAndTahun($nip, $tahun)
    {
        $sql = "SELECT berkas FROM {$this->table} 
                WHERE nomor_induk_pegawai = :nip 
                  AND berkas LIKE :pattern";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nip' => $nip,
            ':pattern' => "sertifikat/{$tahun}/sertifikat_{$nip}_%.pdf"
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $max = 0;
        foreach ($rows as $r) {
            $berkas = basename($r['berkas']);
            if (preg_match("/^sertifikat_{$nip}_(\d+)\.pdf$/", $berkas, $m)) {
                $num = (int)$m[1];
                if ($num > $max) $max = $num;
            }
        }
        return $max + 1;
    }

    // === Hitung total Sertifikat aktif (dengan pencarian opsional) ===
    public function countAll(string $search = ''): int
    {
        $sql = "
            SELECT COUNT(*) 
            FROM {$this->table} s
            JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
            JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
            WHERE sp.status_keaktifan = 'Aktif'
        ";

        if (!empty($search)) {
            $sql .= " AND (p.nama_lengkap LIKE :search 
                       OR s.nomor_sertifikat LIKE :search 
                       OR s.pemberi_sertifikat LIKE :search)";
        }

        $stmt = $this->db->prepare($sql);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    // === Ambil daftar Sertifikat dengan pagination ===
    public function getPaginated(int $limit, int $offset, string $search = ''): array
    {
        $sql = "
            SELECT 
                s.id,
                s.nomor_induk_pegawai,
                p.nama_lengkap,
                s.nomor_sertifikat,
                s.pemberi_sertifikat,
                s.jenis,
                s.mode,
                s.tanggal_mulai_pelaksanaan,
                s.tanggal_akhir_pelaksanaan,
                s.berkas
            FROM {$this->table} s
            JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
            JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
            WHERE sp.status_keaktifan = 'Aktif'
        ";

        if (!empty($search)) {
            $sql .= " AND (p.nama_lengkap LIKE :search 
                       OR s.nomor_sertifikat LIKE :search 
                       OR s.pemberi_sertifikat LIKE :search)";
        }

        $sql .= " ORDER BY s.tanggal_akhir_pelaksanaan DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByNIP($nip, string $search = ''): int
    {
        $sql = "SELECT COUNT(*) FROM sertifikat_pegawai 
                WHERE nomor_induk_pegawai = :nip";
        if (!empty($search)) {
            $sql .= " AND (nomor_sertifikat LIKE :search OR pemberi_sertifikat LIKE :search)";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nip', $nip, PDO::PARAM_STR);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getPaginatedByNIP($nip, int $limit, int $offset, string $search = ''): array
    {
        $sql = "
            SELECT 
                s.id,
                s.nomor_induk_pegawai,
                p.nama_lengkap,
                s.nomor_sertifikat,
                s.pemberi_sertifikat,
                s.jenis,
                s.mode,
                s.tanggal_mulai_pelaksanaan,
                s.tanggal_akhir_pelaksanaan,
                s.berkas
            FROM {$this->table} s
            JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
            JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
            WHERE s.nomor_induk_pegawai = :nip
              AND UPPER(sp.status_keaktifan) = 'AKTIF'
        ";

        if (!empty($search)) {
            $sql .= " AND (p.nama_lengkap LIKE :search 
                       OR s.nomor_sertifikat LIKE :search 
                       OR s.pemberi_sertifikat LIKE :search)";
        }

        $sql .= " ORDER BY s.tanggal_mulai_pelaksanaan DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nip', $nip, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}