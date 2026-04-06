<?php
class SuratInternalModel extends Model
{
    protected $table = 'surat_internal';
    protected $counterTable = 'nomor_internal_counter';

    // Ambil semua data surat internal
    public function getAll()
    {
        $sql = "SELECT si.*, uk.nama_unit
                FROM surat_internal si
                JOIN unit_kerja uk ON si.asal_surat = uk.id
                ORDER BY si.tanggal_surat DESC, si.id DESC";
        $this->query($sql);
        return $this->resultSet();
    }

    // Ambil surat internal berdasarkan unit asal
    public function getByUnit($unitId)
    {
        $sql = "SELECT si.*, uk.nama_unit
                FROM surat_internal si
                JOIN unit_kerja uk ON si.asal_surat = uk.id
                WHERE si.asal_surat = :unitId
                ORDER BY si.tanggal_surat DESC, si.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['unitId' => $unitId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil surat internal berdasarkan ID
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Simpan data baru
    public function save($data)
    {
        return $this->insert($this->table, $data);
    }

    // Update data berdasarkan ID
    public function update($id, $data)
    {
        return $this->updateById($this->table, $id, $data);
    }

    // Hapus data berdasarkan ID
    public function delete($id)
    {
        return $this->deleteById($this->table, $id);
    }

    // Counter: ambil & naikkan urut per tahun untuk surat internal
    public function getNextUrut($tahun)
    {
        $sqlSel = "SELECT urut FROM {$this->counterTable} WHERE tahun = ? LIMIT 1";
        $stmt   = $this->db->prepare($sqlSel);
        $stmt->execute([$tahun]);
        $row    = $stmt->fetch();

        if (!$row) {
            $sqlIns = "INSERT INTO {$this->counterTable} (tahun, urut) VALUES (?, 0)";
            $stmtIns = $this->db->prepare($sqlIns);
            $stmtIns->execute([$tahun]);
            $urut = 0;
        } else {
            $urut = $row['urut'];
        }

        $urut++;
        $sqlUpd = "UPDATE {$this->counterTable} SET urut = ? WHERE tahun = ?";
        $stmtUpd = $this->db->prepare($sqlUpd);
        $stmtUpd->execute([$urut, $tahun]);

        return $urut;
    }

    // Format nama file surat_internal_(unit)_(urut).pdf
    public function buildNamaFile($unitKode, $urut)
    {
        return "surat_internal_{$unitKode}_" . str_pad($urut, 3, '0', STR_PAD_LEFT) . ".pdf";
    }

    // Insert data
    public function insert($table, $data)
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ':' . $f, $fields);

        $sql = "INSERT INTO {$table} (" . implode(',', $fields) . ")
                VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $field => $value) {
            $stmt->bindValue(':' . $field, $value);
        }

        $stmt->execute();
        return $this->lastInsertId();
    }

    // Update data by ID
    public function updateById($table, $id, $data)
    {
        $fields = array_keys($data);
        $set = implode(',', array_map(fn($f) => "$f = :$f", $fields));

        $sql = "UPDATE {$table} SET $set WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $field => $value) {
            $stmt->bindValue(':' . $field, $value);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Update file berkas surat internal
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

    public function countAll(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) 
                FROM surat_internal si
                LEFT JOIN unit_kerja u ON si.asal_surat = u.id
                WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND (si.nomor_surat LIKE :search OR u.nama_unit LIKE :search)";
        }
        $stmt = $this->db->prepare($sql);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getPaginated(int $limit, int $offset, string $search = ''): array
    {
        $sql = "SELECT si.*, u.nama_unit
                FROM surat_internal si
                LEFT JOIN unit_kerja u ON si.asal_surat = u.id
                WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND (si.nomor_surat LIKE :search OR u.nama_unit LIKE :search)";
        }
        $sql .= " ORDER BY si.tanggal_surat DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Versi khusus unit
    public function countAllByUnit($unitId, string $search = ''): int
    {
        $sql = "SELECT COUNT(*) 
                FROM surat_internal si
                LEFT JOIN unit_kerja u ON si.asal_surat = u.id
                WHERE si.asal_surat = :unitId";
        if (!empty($search)) {
            $sql .= " AND (si.nomor_surat LIKE :search OR u.nama_unit LIKE :search)";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':unitId', $unitId, PDO::PARAM_INT);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getPaginatedByUnit($unitId, int $limit, int $offset, string $search = ''): array
    {
        $sql = "SELECT si.*, u.nama_unit
                FROM surat_internal si
                LEFT JOIN unit_kerja u ON si.asal_surat = u.id
                WHERE si.asal_surat = :unitId";
        if (!empty($search)) {
            $sql .= " AND (si.nomor_surat LIKE :search OR u.nama_unit LIKE :search)";
        }
        $sql .= " ORDER BY si.tanggal_surat DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':unitId', $unitId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}