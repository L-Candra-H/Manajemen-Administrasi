<?php
class SuratKeluarModel extends Model
{
    protected $table = 'surat_keluar';
    protected $counterTable = 'nomor_agenda_counter';

    // Ambil semua data surat keluar
    public function getAll()
    {
        $sql = "SELECT sk.*,
                       js.nama_jenis, js.kode AS jenis_kode,
                       st.nama_status
                FROM {$this->table} sk
                JOIN jenis_surat_keluar js ON sk.jenis_surat_id = js.id
                JOIN status_surat st ON sk.status_surat_id = st.id
                ORDER BY sk.tanggal_surat DESC, sk.id DESC";
        $this->query($sql);
        return $this->resultSet();
    }

    // Ambil surat keluar berdasarkan ID
    public function getById($id)
    {
        $sql = "SELECT sk.*,
                       js.nama_jenis, js.kode AS jenis_kode,
                       st.nama_status
                FROM {$this->table} sk
                LEFT JOIN jenis_surat_keluar js ON sk.jenis_surat_id = js.id
                LEFT JOIN status_surat st ON sk.status_surat_id = st.id
                WHERE sk.id = ?";
        $stmt = $this->run($sql, [$id]);   // ✅ gunakan run(), bukan query()
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM surat_keluar WHERE id = :id LIMIT 1";
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

    // Generate nomor agenda baru (SK-YYYY-MM-XXX)
    public function generateNomorAgenda($tanggalSurat)
    {
        $tahun = date('Y', strtotime($tanggalSurat));
        $urut  = $this->getNextUrut($tahun);
        return $this->buildNomorAgenda($tanggalSurat, $urut);
    }

    // Counter: ambil & naikkan urut per tahun untuk jenis SK
    public function getNextUrut($tahun)
    {
        // Ambil current urut
        $sqlSel = "SELECT urut FROM {$this->counterTable} WHERE jenis = 'SK' AND tahun = ? LIMIT 1";
        $stmt   = $this->db->prepare($sqlSel);
        $stmt->execute([$tahun]);
        $row    = $stmt->fetch();

        if (!$row) {
            // Buat row baru
            $sqlIns = "INSERT INTO {$this->counterTable} (jenis, tahun, urut) VALUES ('SK', ?, 0)";
            $stmtIns = $this->db->prepare($sqlIns);
            $stmtIns->execute([$tahun]);
            $urut = 0;
        } else {
            $urut = $row['urut'];
        }

        // Cari nomor agenda unik
        do {
            $urut++;
            $nomorAgenda = $this->buildNomorAgenda(date("$tahun-01-01"), $urut);
            $sqlCheck = "SELECT COUNT(*) as jml FROM {$this->table} WHERE nomor_agenda = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$nomorAgenda]);
            $used = $stmtCheck->fetch()['jml'];
        } while ($used > 0);

        // Update counter
        $sqlUpd = "UPDATE {$this->counterTable} SET urut = ? WHERE jenis = 'SK' AND tahun = ?";
        $stmtUpd = $this->db->prepare($sqlUpd);
        $stmtUpd->execute([$urut, $tahun]);

        return $urut;
    }

    // Format nomor agenda SK-YYYY-MM-XXX
    public function buildNomorAgenda($tanggalSurat, $urut)
    {
        $tahun = date('Y', strtotime($tanggalSurat));
        $bulan = date('m', strtotime($tanggalSurat));
        return "SK-{$tahun}-{$bulan}-" . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }

    public function saveWithCounter(array $data)
    {
        $this->db->beginTransaction();
        try {
            $tahun = date('Y', strtotime($data['tanggal_surat']));

            // Lock counter row
            $stmt = $this->db->prepare("SELECT urut FROM {$this->counterTable} 
                                        WHERE jenis = 'SK' AND tahun = ? 
                                        FOR UPDATE");
            $stmt->execute([$tahun]);
            $row = $stmt->fetch();
            $urut = $row ? $row['urut'] : 0;

            // Cari nomor agenda unik
            do {
                $urut++;
                $nomorAgenda = $this->buildNomorAgenda($data['tanggal_surat'], $urut);
                $sqlCheck = "SELECT COUNT(*) as jml FROM {$this->table} WHERE nomor_agenda = ?";
                $stmtCheck = $this->db->prepare($sqlCheck);
                $stmtCheck->execute([$nomorAgenda]);
                $used = $stmtCheck->fetch()['jml'];
            } while ($used > 0);

            $data['nomor_agenda'] = $nomorAgenda;

            // Insert surat keluar
            $id = $this->save($data);

            // Update counter hanya kalau insert sukses
            if ($id) {
                $sqlUpd = "UPDATE {$this->counterTable} SET urut = ? WHERE jenis = 'SK' AND tahun = ?";
                $stmtUpd = $this->db->prepare($sqlUpd);
                $stmtUpd->execute([$urut, $tahun]);
            }

            $this->db->commit();
            return $id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Format nama file sk_<kode jenis>_<urut>.pdf
    public function buildNamaFile($jenisKode, $urut)
    {
        return "sk_{$jenisKode}_" . str_pad($urut, 3, '0', STR_PAD_LEFT) . ".pdf";
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

    // Update status surat keluar
    public function updateStatus($id, $statusId)
    {
        $sql = "UPDATE surat_keluar 
                SET status_surat_id = :statusId,
                    tanggal_diarsipkan = :tanggal_diarsipkan
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':statusId', $statusId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':tanggal_diarsipkan',
            $statusId == 4 ? date('Y-m-d') : null);

        return $stmt->execute();
    }

    // Ambil urut dari nomor agenda
    public function parseUrutFromNomorAgenda($nomorAgenda)
    {
        $parts = explode('-', $nomorAgenda);
        return intval(end($parts));
    }

    // === UPDATE FILE LAMPIRAN SURAT KELUAR ===
    public function updateFile($id, $path)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET berkas_surat = :path
            WHERE id = :id
        ");
        return $stmt->execute([
            ':path' => $path,
            ':id'   => $id
        ]);
    }

    public function countAll(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) FROM surat_keluar WHERE 1=1";

        if (!empty($search)) {
            $sql .= " AND (tujuan_surat LIKE :search 
                       OR nomor_surat LIKE :search 
                       OR perihal LIKE :search)";
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
        $sql = "SELECT sk.*,
                       js.nama_jenis, js.kode AS jenis_kode,
                       st.nama_status
                FROM {$this->table} sk
                LEFT JOIN jenis_surat_keluar js ON sk.jenis_surat_id = js.id
                LEFT JOIN status_surat st ON sk.status_surat_id = st.id
                WHERE 1=1";

        if (!empty($search)) {
            $sql .= " AND (sk.tujuan_surat LIKE :search 
                       OR sk.nomor_surat LIKE :search 
                       OR sk.perihal LIKE :search)";
        }

        $sql .= " ORDER BY sk.tanggal_surat DESC, sk.id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}