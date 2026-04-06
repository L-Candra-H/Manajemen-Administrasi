<?php

class StatusSuratModel extends Model
{
    protected $table = 'status_surat';

    // Ambil semua data status surat
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil satu data status surat berdasarkan ID
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getIdByNama(string $nama)
    {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE nama_status = :nama LIMIT 1");
        $stmt->bindValue(':nama', $nama, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['id'] ?? null;
    }

}