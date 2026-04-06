<?php
class UnitKerjaModel extends Model
{
    protected $table = 'unit_kerja';

    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY nama_unit ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save($namaUnit)
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nama_unit) VALUES (:nama_unit)");
        $stmt->execute([':nama_unit' => $namaUnit]);

        // ✅ Ambil ID baru dari auto_increment
        return $this->db->lastInsertId();
    }

    public function exists($namaUnit)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE nama_unit = :nama_unit");
        $stmt->execute([':nama_unit' => $namaUnit]);
        return $stmt->fetchColumn() > 0;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $namaUnit)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET nama_unit = :nama_unit WHERE id = :id");
        return $stmt->execute([':nama_unit' => $namaUnit, ':id' => $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getPaginatedAll(int $limit, int $offset, string $search = ''): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND nama_unit LIKE :search";
        }
        $sql .= " ORDER BY nama_unit ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND nama_unit LIKE :search";
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