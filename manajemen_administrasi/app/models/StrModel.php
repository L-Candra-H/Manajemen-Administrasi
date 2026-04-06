<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

class StrModel extends Model
{
  protected $table = 'surat_ijin_STR';

  public function __construct()
  {
    $this->db = new Database();
  }

  // === GET ALL ===
  public function getAll()
  {
    $query = "
      SELECT 
        s.nomor_induk_pegawai,
        s.nomor_STR,
        s.berkas_STR,
        p.nama_lengkap
      FROM {$this->table} s
      JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
      JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
      WHERE s.nomor_STR IS NOT NULL
        AND sp.status_keaktifan = 'Aktif'

    ";
    $stmt = $this->db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // === GET BY NIP ===
  public function getByNIP($nip)
  {
    $query = "
      SELECT 
        s.nomor_induk_pegawai,
        s.nomor_STR,
        s.berkas_STR,
        p.nama_lengkap
      FROM {$this->table} s
      JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
      WHERE s.nomor_induk_pegawai = :nip
      LIMIT 1
    ";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':nip', $nip);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // === SAVE (INSERT) ===
  public function save($data)
  {
    $query = "
      INSERT INTO {$this->table} (nomor_induk_pegawai, nomor_STR, berkas_STR)
      VALUES (:nomor_induk_pegawai, :nomor_STR, :berkas_STR)
    ";
    return $this->executeQuery($query, [
      ':nomor_induk_pegawai' => $data['nomor_induk_pegawai'],
      ':nomor_STR'           => $data['nomor_STR'],
      ':berkas_STR'          => $data['berkas_STR']
    ]);
  }

  // === UPDATE ===
  public function update($data)
  {
    $query = "
      UPDATE {$this->table}
      SET nomor_STR = :nomor_STR,
          berkas_STR = :berkas_STR
      WHERE nomor_induk_pegawai = :nomor_induk_pegawai
    ";
    return $this->executeQuery($query, [
      ':nomor_induk_pegawai' => $data['nomor_induk_pegawai'],
      ':nomor_STR'           => $data['nomor_STR'],
      ':berkas_STR'          => $data['berkas_STR']
    ]);
  }

  // === SAVE OR UPDATE (mode simpan/edit) ===
  public function saveOrUpdate($data)
  {
    if ($this->existsByNIP($data['nomor_induk_pegawai'])) {
      return $this->update($data);
    } else {
      return $this->save($data);
    }
  }

  // === EXISTS ===
  public function existsByNIP($nip)
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE nomor_induk_pegawai = :nip");
    $stmt->execute([':nip' => $nip]);
    return $stmt->fetchColumn() > 0;
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

  // === FIND BY NIP ===
  public function findByNip($nip) {
      $sql = "SELECT * FROM surat_ijin_STR WHERE nomor_induk_pegawai = :nip LIMIT 1";
      $this->query($sql);
      $this->bind(':nip', $nip);
      return $this->single();
  }

  // === UPDATE FILE BERKAS STR ===
  public function updateFile($nip, $path)
  {
      $stmt = $this->db->prepare("
          UPDATE {$this->table}
          SET berkas_STR = :path
          WHERE nomor_induk_pegawai = :nip
      ");
      return $stmt->execute([
          ':path' => $path,
          ':nip'  => $nip
      ]);
  }

  public function countAktifAll()
  {
      $sql = "
          SELECT COUNT(DISTINCT p.nomor_induk_pegawai)
          FROM pegawai p
          JOIN status_pegawai s 
              ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
          JOIN surat_ijin_STR st 
              ON p.nomor_induk_pegawai = st.nomor_induk_pegawai
          WHERE s.status_keaktifan = 'Aktif'
      ";
      return (int)$this->db->query($sql)->fetchColumn();
  }

  // === Hitung total STR aktif (dengan pencarian opsional) ===
  public function countAll(string $search = ''): int
  {
      $sql = "
          SELECT COUNT(*) 
          FROM {$this->table} s
          JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
          JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
          WHERE s.nomor_STR IS NOT NULL
            AND sp.status_keaktifan = 'Aktif'
      ";

      if (!empty($search)) {
          $sql .= " AND (p.nama_lengkap LIKE :search 
                     OR s.nomor_induk_pegawai LIKE :search 
                     OR s.nomor_STR LIKE :search)";
      }

      $stmt = $this->db->prepare($sql);

      if (!empty($search)) {
          $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
      }

      $stmt->execute();
      return (int)$stmt->fetchColumn();
  }

  // === Ambil daftar STR dengan pagination ===
  public function getPaginated(int $limit, int $offset, string $search = ''): array
  {
      $sql = "
          SELECT 
              s.nomor_induk_pegawai,
              s.nomor_STR,
              s.berkas_STR,
              p.nama_lengkap
          FROM {$this->table} s
          JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
          JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
          WHERE s.nomor_STR IS NOT NULL
            AND sp.status_keaktifan = 'Aktif'
      ";

      if (!empty($search)) {
          $sql .= " AND (p.nama_lengkap LIKE :search 
                     OR s.nomor_induk_pegawai LIKE :search 
                     OR s.nomor_STR LIKE :search)";
      }

      $sql .= " ORDER BY s.nomor_induk_pegawai ASC LIMIT :limit OFFSET :offset";

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