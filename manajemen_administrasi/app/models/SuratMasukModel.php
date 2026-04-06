<?php
class SuratMasukModel extends Model
{
    protected $table = 'surat_masuk';

    // Ambil semua data + dukungan pencarian
    public function getAll($q = null) {
        $sql = "SELECT sm.*, 
                       ss.nama_sifat, 
                       st.nama_status, 
                       dj.nama_jabatan AS disposisi_jabatan, 
                       uk.nama_unit AS unit_nama, 
                       dk.nama_jabatan AS diteruskan_kepada
                FROM {$this->table} sm
                LEFT JOIN sifat_surat_masuk ss ON sm.sifat_surat_id = ss.id
                LEFT JOIN status_surat st ON sm.status_surat_id = st.id
                LEFT JOIN jabatan dj ON sm.disposisi_jabatan_id = dj.id
                LEFT JOIN unit_kerja uk ON sm.disposisi_unit_id = uk.id
                LEFT JOIN jabatan dk ON sm.diteruskan_kepada_jabatan_id = dk.id";

        if ($q) {
            $sql .= " WHERE sm.nomor_surat LIKE :q 
                      OR sm.asal_surat LIKE :q 
                      OR sm.perihal_surat LIKE :q";
        }

        $sql .= " ORDER BY sm.created_at DESC";
        $stmt = $this->db->prepare($sql);

        if ($q) {
            $stmt->bindValue(':q', "%{$q}%");
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Simpan data baru
    public function save(array $data)
    {
        $allowed = [
            'nomor_agenda','nomor_surat','tanggal_surat','tanggal_terima',
            'asal_surat','perihal_surat','sifat_surat_id','jumlah_halaman',
            'diteruskan_kepada_jabatan_id','isi_disposisi',
            'disposisi_jabatan_id','disposisi_unit_id',
            'berkas_surat','status_surat_id','tanggal_diarsipkan'
        ];
        $data = array_intersect_key($data, array_flip($allowed));

        $sql = "INSERT INTO {$this->table}
                (".implode(',', array_keys($data)).")
                VALUES (:".implode(',:', array_keys($data)).")";

        $stmt = $this->db->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        return $stmt->execute() ? $this->db->lastInsertId() : false;
    }

    // Update data
    public function update($id, array $data)
    {
        $allowed = [
            'nomor_surat','tanggal_surat','tanggal_terima','asal_surat',
            'perihal_surat','sifat_surat_id','jumlah_halaman',
            'diteruskan_kepada_jabatan_id','isi_disposisi',
            'disposisi_jabatan_id','disposisi_unit_id',
            'berkas_surat','status_surat_id','tanggal_diarsipkan'
        ];
        $data = array_intersect_key($data, array_flip($allowed));

        // build query dinamis
        $setParts = [];
        foreach ($data as $key => $val) {
            $setParts[] = "$key = :$key";
        }
        $sql = "UPDATE {$this->table} SET ".implode(',', $setParts)." WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    // Update status surat
    public function updateStatus($id, $statusId)
    {
        $tanggalArsip = ($statusId == 4) ? date('Y-m-d') : null;
        $sql = "UPDATE {$this->table} 
                SET status_surat_id = :status, tanggal_diarsipkan = :arsip 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $statusId,
            ':arsip'  => $tanggalArsip,
            ':id'     => $id
        ]);
    }

    public function updateDisposisi($id, array $data)
    {
        // hanya field disposisi yang boleh diupdate oleh Direktur
        $allowed = ['isi_disposisi','disposisi_jabatan_id','disposisi_unit_id'];
        $data = array_intersect_key($data, array_flip($allowed));

        if (empty($data)) {
            return false; // tidak ada field yang valid
        }

        $setParts = [];
        foreach ($data as $key => $val) {
            $setParts[] = "$key = :$key";
        }
        $sql = "UPDATE {$this->table} SET ".implode(',', $setParts)." WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    // Cari detail surat masuk
    public function findById($id)
    {
        $sql = "SELECT sm.*, 
                       ss.nama_sifat, 
                       st.nama_status, 
                       dj.nama_jabatan AS disposisi_jabatan, 
                       uk.nama_unit AS unit_nama, 
                       dk.nama_jabatan AS diteruskan_kepada
                FROM {$this->table} sm
                LEFT JOIN sifat_surat_masuk ss ON sm.sifat_surat_id = ss.id
                LEFT JOIN status_surat st ON sm.status_surat_id = st.id
                LEFT JOIN jabatan dj ON sm.disposisi_jabatan_id = dj.id
                LEFT JOIN unit_kerja uk ON sm.disposisi_unit_id = uk.id
                LEFT JOIN jabatan dk ON sm.diteruskan_kepada_jabatan_id = dk.id
                WHERE sm.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByIdForUnit($id, $unitId)
    {
        $sql = "SELECT sm.*, 
                       ss.nama_sifat, 
                       st.nama_status, 
                       dj.nama_jabatan AS disposisi_jabatan, 
                       uk.nama_unit AS unit_nama, 
                       dk.nama_jabatan AS diteruskan_kepada
                FROM {$this->table} sm
                LEFT JOIN sifat_surat_masuk ss ON sm.sifat_surat_id = ss.id
                LEFT JOIN status_surat st ON sm.status_surat_id = st.id
                LEFT JOIN jabatan dj ON sm.disposisi_jabatan_id = dj.id
                LEFT JOIN unit_kerja uk ON sm.disposisi_unit_id = uk.id
                LEFT JOIN jabatan dk ON sm.diteruskan_kepada_jabatan_id = dk.id
                WHERE sm.id = :id
                    AND (sm.disposisi_unit_id = :unitId 
                        OR FIND_IN_SET(:unitId, sm.disposisi_unit_id) > 0)";
              
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':unitId' => $unitId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllByUnit($unitId, $q = null)
    {
        $sql = "SELECT sm.*, 
                       ss.nama_sifat, 
                       st.nama_status, 
                       dj.nama_jabatan AS disposisi_jabatan, 
                       uk.nama_unit AS unit_nama, 
                       dk.nama_jabatan AS diteruskan_kepada
                FROM {$this->table} sm
                LEFT JOIN sifat_surat_masuk ss ON sm.sifat_surat_id = ss.id
                LEFT JOIN status_surat st ON sm.status_surat_id = st.id
                LEFT JOIN jabatan dj ON sm.disposisi_jabatan_id = dj.id
                LEFT JOIN unit_kerja uk ON sm.disposisi_unit_id = uk.id
                LEFT JOIN jabatan dk ON sm.diteruskan_kepada_jabatan_id = dk.id
                WHERE FIND_IN_SET(:unitId, sm.disposisi_unit_id) > 0";

        if ($q) {
            $sql .= " AND (sm.nomor_surat LIKE :q 
                       OR sm.asal_surat LIKE :q 
                       OR sm.perihal_surat LIKE :q)";
        }

        $sql .= " ORDER BY sm.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':unitId', $unitId);
        if ($q) {
            $stmt->bindValue(':q', "%{$q}%");
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hapus surat masuk
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Generate nomor agenda
    public function generateNomorAgenda($tanggalSurat)
    {
        $tahun = date('Y', strtotime($tanggalSurat));
        $bulan = date('m', strtotime($tanggalSurat));
        $urut  = $this->getNextUrut($tahun);

        return $this->buildNomorAgenda($tanggalSurat, $urut);
    }

    // Ambil urut berikutnya
    public function getNextUrut($tahun)
    {
        $sqlSel = "SELECT urut FROM nomor_agenda_counter WHERE jenis = 'SM' AND tahun = ? LIMIT 1";
        $stmt   = $this->db->prepare($sqlSel);
        $stmt->execute([$tahun]);
        $row    = $stmt->fetch();

        if (!$row) {
            $sqlIns = "INSERT INTO nomor_agenda_counter (jenis, tahun, urut) VALUES ('SM', ?, 0)";
            $stmtIns = $this->db->prepare($sqlIns);
            $stmtIns->execute([$tahun]);
            $urut = 0;
        } else {
            $urut = $row['urut'];
        }

        do {
            $urut++;
            $nomorAgenda = $this->buildNomorAgenda(date("$tahun-01-01"), $urut);
            $sqlCheck = "SELECT COUNT(*) as jml FROM {$this->table} WHERE nomor_agenda = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$nomorAgenda]);
            $used = $stmtCheck->fetch()['jml'];
        } while ($used > 0);

        $sqlUpd = "UPDATE nomor_agenda_counter SET urut = ? WHERE jenis = 'SM' AND tahun = ?";
        $stmtUpd = $this->db->prepare($sqlUpd);
        $stmtUpd->execute([$urut, $tahun]);

        return $urut;
    }

    // Bangun nomor agenda
    public function buildNomorAgenda($tanggalSurat, $urut)
    {
        $tahun = date('Y', strtotime($tanggalSurat));
        $bulan = date('m', strtotime($tanggalSurat));
        return "SM-{$tahun}-{$bulan}-" . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }

    public function saveWithCounter(array $data)
    {
        $this->db->beginTransaction();
        try {
            $tahun = date('Y', strtotime($data['tanggal_surat']));

            // Ambil urut terakhir dengan lock
            $stmt = $this->db->prepare("SELECT urut FROM nomor_agenda_counter 
                                        WHERE jenis = 'SM' AND tahun = ? 
                                        FOR UPDATE");
            $stmt->execute([$tahun]);
            $row = $stmt->fetch();
            $urut = $row ? $row['urut'] : 0;

            $urut++;
            $data['nomor_agenda'] = $this->buildNomorAgenda($data['tanggal_surat'], $urut);

            // Insert surat masuk
            $id = $this->save($data);

            // Update counter hanya kalau insert sukses
            if ($id) {
                $sqlUpd = "UPDATE nomor_agenda_counter SET urut = ? WHERE jenis = 'SM' AND tahun = ?";
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

    // Bangun nama file PDF
    public function buildNamaFile($urut)
    {
        return "sm_" . str_pad($urut, 3, '0', STR_PAD_LEFT) . ".pdf";
    }

    public function parseUrutFromNomorAgenda($nomorAgenda) {
        $parts = explode('-', $nomorAgenda);
        return isset($parts[3]) ? (int)$parts[3] : null;
    }

    public function updateFile($id, $path)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET berkas_surat = :path WHERE id = :id");
        return $stmt->execute([':path' => $path, ':id' => $id]);
    }

    public function countAll(?string $search = null): int
    {
        $sql = "SELECT COUNT(*) FROM surat_masuk WHERE 1=1";

        if (!empty($search)) {
            $sql .= " AND (asal_surat LIKE :search OR perihal_surat LIKE :search OR nomor_surat LIKE :search)";
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
        $sql = "SELECT * FROM surat_masuk WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND (asal_surat LIKE :search OR perihal_surat LIKE :search OR nomor_surat LIKE :search)";
        }
        $sql .= " ORDER BY tanggal_surat DESC LIMIT :limit OFFSET :offset";

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
        $sql = "SELECT COUNT(*) FROM surat_masuk WHERE disposisi_unit_id = :unitId";
        if (!empty($search)) {
            $sql .= " AND (asal_surat LIKE :search OR perihal_surat LIKE :search OR nomor_surat LIKE :search)";
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
        $sql = "SELECT * FROM surat_masuk WHERE disposisi_unit_id = :unitId";
        if (!empty($search)) {
            $sql .= " AND (asal_surat LIKE :search OR perihal_surat LIKE :search OR nomor_surat LIKE :search)";
        }
        $sql .= " ORDER BY tanggal_surat DESC LIMIT :limit OFFSET :offset";

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