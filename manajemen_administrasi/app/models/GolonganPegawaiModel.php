<?php

class GolonganPegawaiModel extends Model
{
    protected $table = 'golongan_pegawai';

    // Ambil semua data golongan pegawai
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil satu data golongan pegawai berdasarkan ID
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Ambil data dengan pagination + search
    public function getPaginatedAll(int $limit, int $offset, string $search = ''): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";

        if (!empty($search)) {
            $sql .= " AND (golongan LIKE :search OR keterangan LIKE :search OR nilai LIKE :search)";
        }

        $sql .= " ORDER BY id ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hitung total data untuk pagination
    public function countAll(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";

        if (!empty($search)) {
            $sql .= " AND (golongan LIKE :search OR keterangan LIKE :search OR nilai LIKE :search)";
        }

        $stmt = $this->db->prepare($sql);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }
}
