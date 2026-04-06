<?php

class KategoriKepegawaianModel extends Model
{
    protected $table = 'kategori_kepegawaian';

    // Ambil semua data kategori kepegawaian
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT id, kategori, keterangan, nilai 
                                    FROM {$this->table} 
                                    ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil satu data kategori kepegawaian berdasarkan ID
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT id, kategori, keterangan, nilai 
                                    FROM {$this->table} 
                                    WHERE id = :id 
                                    LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
