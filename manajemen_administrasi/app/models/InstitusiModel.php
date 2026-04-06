<?php

class InstitusiModel extends Model
{
    // Tambahkan properti table supaya tidak undefined
    protected $table = 'institusi';

    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY nama_institusi ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil institusi berdasarkan ID
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Ambil header institusi (nama, sub, alamat, telepon, logo)
    public function getHeader()
    {
        $sql = "SELECT nama_institusi, sub_institusi, alamat, telepon, logo 
                FROM {$this->table} 
                ORDER BY id ASC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // === UPDATE LOGO INSTITUSI ===
    public function updateLogo($id, $path)
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET logo = :path
            WHERE id = :id
        ");
        return $stmt->execute([
            ':path' => $path,
            ':id'   => $id
        ]);
    }

}