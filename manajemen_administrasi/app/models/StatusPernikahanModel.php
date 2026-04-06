<?php

class StatusPernikahanModel extends Model
{
    protected $table = 'status_pernikahan';

    // Ambil semua data status pernikahan
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT id, status, nilai 
                                    FROM {$this->table} 
                                    ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil satu data status pernikahan berdasarkan ID
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT id, status, nilai 
                                    FROM {$this->table} 
                                    WHERE id = :id 
                                    LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
