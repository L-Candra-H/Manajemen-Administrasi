<?php

class PendidikanModel extends Model
{
    protected $table = 'pendidikan';

    // Ambil semua data pendidikan dengan pagination
    public function getAll($limit = 6, $offset = 0)
    {
        $stmt = $this->db->prepare("SELECT id, jenjang, keterangan, nilai 
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

    // Ambil satu data pendidikan berdasarkan ID
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT id, jenjang, keterangan, nilai 
                                    FROM {$this->table} 
                                    WHERE id = :id 
                                    LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllNoLimit()
    {
        $stmt = $this->db->prepare("SELECT id, jenjang, keterangan, nilai 
                                    FROM {$this->table} 
                                    ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
