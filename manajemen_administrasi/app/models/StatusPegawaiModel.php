<?php
require_once __DIR__ . '/../core/Model.php';

class StatusPegawaiModel extends Model
{
  protected $table = 'status_pegawai';

  // === GET STATUS BY NIP ===
  public function getStatusByNIP($nip)
  {
    $stmt = $this->db->prepare("
      SELECT status_keaktifan, alasan_non_aktif
      FROM {$this->table}
      WHERE nomor_induk_pegawai = :nip
      LIMIT 1
    ");
    $stmt->execute([':nip' => $nip]);
    return $stmt->fetch(PDO::FETCH_ASSOC); 
    // contoh hasil: ['status_keaktifan' => 'Aktif', 'alasan_non_aktif' => '']
  }

  // === EXISTS ===
  public function existsByNIP($nip)
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE nomor_induk_pegawai = :nip");
    $stmt->execute([':nip' => $nip]);
    return $stmt->fetchColumn() > 0;
  }

  // === SAVE OR UPDATE ===
  public function save($data)
  {
    return $this->existsByNIP($data['nomor_induk_pegawai'])
      ? $this->update($data)
      : $this->insert($data);
  }

  // === INSERT ===
  public function insert($data)
  {
    $query = "
      INSERT INTO {$this->table} (
        nomor_induk_pegawai, status_keaktifan, alasan_non_aktif
      ) VALUES (
        :nomor_induk_pegawai, :status_keaktifan, :alasan_non_aktif
      )
    ";
    return $this->executeQuery($query, [
      ':nomor_induk_pegawai' => $data['nomor_induk_pegawai'],
      ':status_keaktifan'    => $data['status_keaktifan'],
      ':alasan_non_aktif'    => $data['alasan_non_aktif'] ?? null
    ]);
  }

  // === UPDATE ===
  public function update($data)
  {
    $query = "
      UPDATE {$this->table}
      SET status_keaktifan = :status_keaktifan,
          alasan_non_aktif = :alasan_non_aktif
      WHERE nomor_induk_pegawai = :nomor_induk_pegawai
    ";
    return $this->executeQuery($query, [
      ':nomor_induk_pegawai' => $data['nomor_induk_pegawai'],
      ':status_keaktifan'    => $data['status_keaktifan'],
      ':alasan_non_aktif'    => $data['alasan_non_aktif'] ?? null
    ]);
  }

  // === DELETE ===
  public function deleteByNIP($nip)
  {
    $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE nomor_induk_pegawai = :nip");
    return $stmt->execute([':nip' => $nip]);
  }

  // === Helper eksekusi query ===
  private function executeQuery($query, $params)
  {
    $stmt = $this->db->prepare($query);
    $success = $stmt->execute($params);
    if (!$success) {
      error_log("❌ Query gagal di {$this->table}: " . implode(' | ', $stmt->errorInfo()));
    }
    return $success;
  }
}