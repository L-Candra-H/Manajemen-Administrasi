<?php

class SifatSuratMasukModel extends Model
{
    protected $table = 'sifat_surat_masuk';

    /**
     * Ambil semua data sifat surat
     * @return array
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nama_sifat FROM {$this->table} ORDER BY id ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("DB Error getAll SifatSuratMasuk: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ambil satu data sifat surat berdasarkan ID
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nama_sifat FROM {$this->table} WHERE id = :id LIMIT 1");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log("DB Error find SifatSuratMasuk: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Ambil ID berdasarkan nama sifat
     * @param string $nama
     * @return int|null
     */
    public function getIdByNama(string $nama): ?int
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE nama_sifat = :nama LIMIT 1");
            $stmt->bindValue(':nama', $nama, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['id'] ?? null;
        } catch (PDOException $e) {
            error_log("DB Error getIdByNama SifatSuratMasuk: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tambah data sifat surat baru
     * @param string $nama
     * @return bool
     */
    public function insert(string $nama): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (nama_sifat) VALUES (:nama)");
            $stmt->bindValue(':nama', $nama, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("DB Error insert SifatSuratMasuk: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update data sifat surat
     * @param int $id
     * @param string $nama
     * @return bool
     */
    public function update(int $id, string $nama): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET nama_sifat = :nama WHERE id = :id");
            $stmt->bindValue(':nama', $nama, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("DB Error update SifatSuratMasuk: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus data sifat surat
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("DB Error delete SifatSuratMasuk: " . $e->getMessage());
            return false;
        }
    }
}