<?php

class JabatanModel extends Model
{
    protected $table = 'jabatan';
    
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT id, nama_jabatan, keterangan, nilai 
                                    FROM {$this->table} 
                                    WHERE id != 1 
                                    ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT id, nama_jabatan, keterangan, nilai 
                                    FROM {$this->table} 
                                    WHERE id = :id 
                                    LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: []; // kalau tidak ada, return array kosong
    }

    public function find($id)
    {
        return $this->findById($id);
    }

    public function findByName($nama)
    {
        $stmt = $this->db->prepare("SELECT id, nama_jabatan, keterangan, nilai
                                    FROM {$this->table} 
                                    WHERE LOWER(nama_jabatan) = LOWER(:nama) 
                                    LIMIT 1");
        $stmt->execute([':nama' => $nama]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
