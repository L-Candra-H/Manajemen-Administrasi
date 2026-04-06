<?php

class LamakerjaModel extends Model
{
    protected $table = 'lama_kerja';

    // Ambil semua data lama kerja dengan pagination
    public function getAll($limit = 6, $offset = 0)
    {
        $stmt = $this->db->prepare("SELECT id, rentang, nilai 
                                    FROM {$this->table} 
                                    ORDER BY id ASC 
                                    LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hitung total data untuk pagination
    public function countAll()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table}");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Ambil satu data lama kerja berdasarkan ID
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT id, rentang, nilai 
                                    FROM {$this->table} 
                                    WHERE id = :id 
                                    LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Ambil nilai lama kerja berdasarkan tahun kerja pegawai
    public function getNilaiByTahunKerja(int $tahunKerja): ?int
    {
        $stmt = $this->db->prepare("SELECT nilai 
                                    FROM {$this->table} 
                                    WHERE :tahunKerja BETWEEN min_tahun AND max_tahun 
                                    LIMIT 1");
        $stmt->bindParam(':tahunKerja', $tahunKerja, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['nilai'] ?? null;
    }

    // Ambil rentang lama kerja berdasarkan tahun kerja pegawai
    public function getRentangByTahunKerja(int $tahunKerja): ?string
    {
        $stmt = $this->db->prepare("SELECT rentang 
                                    FROM {$this->table} 
                                    WHERE :tahunKerja BETWEEN min_tahun AND max_tahun 
                                    LIMIT 1");
        $stmt->bindParam(':tahunKerja', $tahunKerja, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['rentang'] ?? null;
    }
}
