<?php

class MasterModel extends Model
{
    private $allowed = [
        'institusi', 'jenis_kelamin', 'agama', 'status_pernikahan',
        'status_kepegawaian', 'golongan_pegawai',
        'kategori_kepegawaian', 'jabatan', 'unit_kerja', 'lama_kerja', 'pendidikan',
        'sifat_surat_masuk', 'status_surat', 'jenis_surat_keluar'
    ];

    private function validateTable(string $table): void
    {
        if (!in_array($table, $this->allowed)) {
            throw new Exception("Tabel $table tidak diizinkan.");
        }
    }

    public function getAll(string $table, int $limit = 0, int $offset = 0, string $search = ''): array
    {
        $this->validateTable($table);

        $sql = "SELECT * FROM {$table} WHERE 1=1";

        if (!empty($search)) {
            $sql .= " AND (nama LIKE :search OR keterangan LIKE :search)";
        }

        if ($limit > 0) {
            $sql .= " ORDER BY id ASC LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        if ($limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(string $table, array $data): bool
    {
        $this->validateTable($table);

        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        $sql = "INSERT INTO {$table} (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);

        $success = $stmt->execute(array_values($data));
        if (!$success) {
            error_log("❌ Gagal insert ke {$table}: " . implode(' | ', $stmt->errorInfo()));
        }
        return $success;
    }

    public function update(string $table, array $data, array $where): bool
    {
        $this->validateTable($table);

        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
        }

        $conditions = [];
        foreach ($where as $key => $value) {
            $conditions[] = "$key = ?";
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $fields) . " WHERE " . implode(' AND ', $conditions);
        $stmt = $this->db->prepare($sql);

        $values = array_merge(array_values($data), array_values($where));
        $success = $stmt->execute($values);

        if (!$success) {
            error_log("❌ Gagal update {$table}: " . implode(' | ', $stmt->errorInfo()));
        }

        return $success;
    }

    public function updateField(string $table, int $id, string $field, $value): bool
    {
        $this->validateTable($table);

        $sql = "UPDATE {$table} SET {$field} = :value WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':value' => $value,
            ':id'    => $id
        ]);
    }

    public function getWhere(string $table, array $conditions): array
    {
        $this->validateTable($table);

        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "$key = ?";
        }

        $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($conditions));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(string $table, int $id): ?array
    {
        $this->validateTable($table);

        $sql = "SELECT * FROM {$table} WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function delete(string $table, array $conditions): bool
    {
        $this->validateTable($table);

        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "$key = ?";
        }

        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute(array_values($conditions));

        if (!$success) {
            error_log("❌ Gagal delete dari {$table}: " . implode(' | ', $stmt->errorInfo()));
        }

        return $success;
    }
}
