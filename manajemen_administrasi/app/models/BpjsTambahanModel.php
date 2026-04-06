<?php
require_once __DIR__ . '/../core/Model.php';

class BpjsTambahanModel extends Model
{
  protected $table = 'kepesertaan_BPJS_tambahan';

  // === GET ALL ===
  public function getAll()
  {
      $sql = "
          SELECT 
            t.*,
            p.nama_lengkap,
            sp.status_keaktifan
          FROM {$this->table} t
          JOIN pegawai p ON p.nomor_induk_pegawai = t.nomor_induk_pegawai
          JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
          WHERE sp.status_keaktifan = 'Aktif'
      ";
      $stmt = $this->db->query($sql);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // === GET BY NIP ===
  public function getByNIP($nip)
  {
    $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE nomor_induk_pegawai = :nip");
    $stmt->bindValue(':nip', $nip);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
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
        nomor_induk_pegawai,
        nama_lengkap_tambahan_I,
        nomor_kartu_BPJS_kesehatan_tambahan_I,
        status_keaktifan_BPJS_kesehatan_tambahan_I,
        nama_lengkap_tambahan_II,
        nomor_kartu_BPJS_kesehatan_tambahan_II,
        status_keaktifan_BPJS_kesehatan_tambahan_II,
        nama_lengkap_tambahan_III,
        nomor_kartu_BPJS_kesehatan_tambahan_III,
        status_keaktifan_BPJS_kesehatan_tambahan_III,
        nama_lengkap_tambahan_IV,
        nomor_kartu_BPJS_kesehatan_tambahan_IV,
        status_keaktifan_BPJS_kesehatan_tambahan_IV
      ) VALUES (
        :nomor_induk_pegawai,
        :nama_lengkap_tambahan_I, :nomor_kartu_BPJS_kesehatan_tambahan_I, :status_keaktifan_BPJS_kesehatan_tambahan_I,
        :nama_lengkap_tambahan_II, :nomor_kartu_BPJS_kesehatan_tambahan_II, :status_keaktifan_BPJS_kesehatan_tambahan_II,
        :nama_lengkap_tambahan_III, :nomor_kartu_BPJS_kesehatan_tambahan_III, :status_keaktifan_BPJS_kesehatan_tambahan_III,
        :nama_lengkap_tambahan_IV, :nomor_kartu_BPJS_kesehatan_tambahan_IV, :status_keaktifan_BPJS_kesehatan_tambahan_IV
      )
    ";
    return $this->executeQuery($query, $this->bindParams($data));
  }

  // === UPDATE ===
  public function update($data)
  {
    $query = "
      UPDATE {$this->table}
      SET
        nama_lengkap_tambahan_I = :nama_lengkap_tambahan_I,
        nomor_kartu_BPJS_kesehatan_tambahan_I = :nomor_kartu_BPJS_kesehatan_tambahan_I,
        status_keaktifan_BPJS_kesehatan_tambahan_I = :status_keaktifan_BPJS_kesehatan_tambahan_I,
        nama_lengkap_tambahan_II = :nama_lengkap_tambahan_II,
        nomor_kartu_BPJS_kesehatan_tambahan_II = :nomor_kartu_BPJS_kesehatan_tambahan_II,
        status_keaktifan_BPJS_kesehatan_tambahan_II = :status_keaktifan_BPJS_kesehatan_tambahan_II,
        nama_lengkap_tambahan_III = :nama_lengkap_tambahan_III,
        nomor_kartu_BPJS_kesehatan_tambahan_III = :nomor_kartu_BPJS_kesehatan_tambahan_III,
        status_keaktifan_BPJS_kesehatan_tambahan_III = :status_keaktifan_BPJS_kesehatan_tambahan_III,
        nama_lengkap_tambahan_IV = :nama_lengkap_tambahan_IV,
        nomor_kartu_BPJS_kesehatan_tambahan_IV = :nomor_kartu_BPJS_kesehatan_tambahan_IV,
        status_keaktifan_BPJS_kesehatan_tambahan_IV = :status_keaktifan_BPJS_kesehatan_tambahan_IV
      WHERE nomor_induk_pegawai = :nomor_induk_pegawai
    ";
    return $this->executeQuery($query, $this->bindParams($data));
  }

  // === DELETE ===
  public function deleteByNIP($nip)
  {
    $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE nomor_induk_pegawai = :nip");
    return $stmt->execute([':nip' => $nip]);
  }

  // === PARAM BINDING ===
  private function bindParams($data)
  {
    return [
      ':nomor_induk_pegawai'                   => $data['nomor_induk_pegawai'],
      ':nama_lengkap_tambahan_I'               => $data['nama_lengkap_tambahan_I'],
      ':nomor_kartu_BPJS_kesehatan_tambahan_I' => $data['nomor_kartu_BPJS_kesehatan_tambahan_I'],
      ':status_keaktifan_BPJS_kesehatan_tambahan_I' => $data['status_keaktifan_BPJS_kesehatan_tambahan_I'],
      ':nama_lengkap_tambahan_II'              => $data['nama_lengkap_tambahan_II'],
      ':nomor_kartu_BPJS_kesehatan_tambahan_II'=> $data['nomor_kartu_BPJS_kesehatan_tambahan_II'],
      ':status_keaktifan_BPJS_kesehatan_tambahan_II'=> $data['status_keaktifan_BPJS_kesehatan_tambahan_II'],
      ':nama_lengkap_tambahan_III'             => $data['nama_lengkap_tambahan_III'],
      ':nomor_kartu_BPJS_kesehatan_tambahan_III'=> $data['nomor_kartu_BPJS_kesehatan_tambahan_III'],
      ':status_keaktifan_BPJS_kesehatan_tambahan_III'=> $data['status_keaktifan_BPJS_kesehatan_tambahan_III'],
      ':nama_lengkap_tambahan_IV'              => $data['nama_lengkap_tambahan_IV'],
      ':nomor_kartu_BPJS_kesehatan_tambahan_IV'=> $data['nomor_kartu_BPJS_kesehatan_tambahan_IV'],
      ':status_keaktifan_BPJS_kesehatan_tambahan_IV'=> $data['status_keaktifan_BPJS_kesehatan_tambahan_IV']
    ];
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

  public function countAll(string $search = ''): int
  {
      $sql = "SELECT COUNT(*) 
              FROM {$this->table} b
              JOIN pegawai p ON p.nomor_induk_pegawai = b.nomor_induk_pegawai
              JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
              WHERE sp.status_keaktifan = 'Aktif'";

      if (!empty($search)) {
          $sql .= " AND (p.nama_lengkap LIKE :search OR b.nomor_bpjs LIKE :search)";
      }

      $stmt = $this->db->prepare($sql);
      if (!empty($search)) {
          $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
      }
      $stmt->execute();
      return (int)$stmt->fetchColumn();
  }

  public function getPaginated(int $limit, int $offset, string $search = ''): array
  {
      $sql = "SELECT 
                  b.id,
                  b.nomor_induk_pegawai,
                  p.nama_lengkap,
                  b.nomor_bpjs,
                  b.faskes_tingkat,
                  b.kelas_rawat
              FROM {$this->table} b
              JOIN pegawai p ON p.nomor_induk_pegawai = b.nomor_induk_pegawai
              JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
              WHERE sp.status_keaktifan = 'Aktif'";

      if (!empty($search)) {
          $sql .= " AND (p.nama_lengkap LIKE :search OR b.nomor_bpjs LIKE :search)";
      }

      $sql .= " ORDER BY b.nomor_induk_pegawai DESC LIMIT :limit OFFSET :offset";

      $stmt = $this->db->prepare($sql);
      $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
      $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

      if (!empty($search)) {
          $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
      }

      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
}