<?php
require_once __DIR__ . '/../core/Model.php';

class SuratKeputusanModel extends Model
{
  protected $table = 'surat_keputusan';

  // === GET ALL ===
  public function getAll()
  {
      $query = "
          SELECT 
            sk.id,
            sk.nomor_induk_pegawai,
            sk.nomor_surat_keputusan,
            sk.mulai_berlaku_surat_keputusan,
            sk.berakhir_surat_keputusan,
            sk.berkas_surat_keputusan,
            sk.masa_aktif_surat_keputusan,
            p.nama_lengkap,
            sp.status_keaktifan
          FROM {$this->table} sk
          JOIN pegawai p ON p.nomor_induk_pegawai = sk.nomor_induk_pegawai
          JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
          WHERE sk.nomor_surat_keputusan IS NOT NULL
            AND sk.berakhir_surat_keputusan >= CURDATE()
            AND sp.status_keaktifan = 'Aktif'
          ORDER BY sk.id DESC
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
        sk.nomor_induk_pegawai,
        sk.nomor_surat_keputusan,
        sk.mulai_berlaku_surat_keputusan,
        sk.berakhir_surat_keputusan,
        sk.berkas_surat_keputusan,
        sk.masa_aktif_surat_keputusan,
        p.nama_lengkap
      FROM {$this->table} sk
      JOIN pegawai p ON p.nomor_induk_pegawai = sk.nomor_induk_pegawai
      WHERE sk.nomor_surat_keputusan IS NOT NULL
        AND sk.nomor_induk_pegawai = :nip
        AND sk.berakhir_surat_keputusan >= CURDATE()
      ORDER BY sk.berakhir_surat_keputusan DESC
      LIMIT 1
    ";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':nip', $nip);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // === GET BY ID ===
  public function getById($id)
  {
      $query = "
          SELECT 
            sk.id,
            sk.nomor_induk_pegawai,
            sk.nomor_surat_keputusan,
            sk.mulai_berlaku_surat_keputusan,
            sk.berakhir_surat_keputusan,
            sk.berkas_surat_keputusan,
            sk.masa_aktif_surat_keputusan,
            p.nama_lengkap,
            sp.status_keaktifan
          FROM {$this->table} sk
          JOIN pegawai p ON p.nomor_induk_pegawai = sk.nomor_induk_pegawai
          JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
          WHERE sk.id = :id
      ";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
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

  // === SAVE (INSERT) ===
  public function save($data)
  {
    $query = "
      INSERT INTO {$this->table} (
        nomor_induk_pegawai,
        nomor_surat_keputusan,
        mulai_berlaku_surat_keputusan,
        berakhir_surat_keputusan,
        berkas_surat_keputusan,
        masa_aktif_surat_keputusan
      ) VALUES (
        :nomor_induk_pegawai, :nomor_surat_keputusan, :mulai_berlaku_surat_keputusan,
        :berakhir_surat_keputusan, :berkas_surat_keputusan, :masa_aktif_surat_keputusan
      )
    ";
    return $this->executeQuery($query, [
      ':nomor_induk_pegawai'        => $data['nomor_induk_pegawai'],
      ':nomor_surat_keputusan'      => $data['nomor_surat_keputusan'],
      ':mulai_berlaku_surat_keputusan' => $data['mulai_berlaku_surat_keputusan'],
      ':berakhir_surat_keputusan'   => $data['berakhir_surat_keputusan'],
      ':berkas_surat_keputusan'     => $data['berkas_surat_keputusan'],
      ':masa_aktif_surat_keputusan' => $data['masa_aktif_surat_keputusan']
    ]);
  }

  // === UPDATE ===
  public function update($data)
  {
    $query = "
      UPDATE {$this->table}
      SET
        nomor_surat_keputusan       = :nomor_surat_keputusan,
        mulai_berlaku_surat_keputusan = :mulai_berlaku_surat_keputusan,
        berakhir_surat_keputusan    = :berakhir_surat_keputusan,
        berkas_surat_keputusan      = :berkas_surat_keputusan,
        masa_aktif_surat_keputusan  = :masa_aktif_surat_keputusan
      WHERE id = :id
    ";
    return $this->executeQuery($query, [
      ':id'                         => $data['id'],
      ':nomor_surat_keputusan'      => $data['nomor_surat_keputusan'],
      ':mulai_berlaku_surat_keputusan' => $data['mulai_berlaku_surat_keputusan'],
      ':berakhir_surat_keputusan'   => $data['berakhir_surat_keputusan'],
      ':berkas_surat_keputusan'     => $data['berkas_surat_keputusan'],
      ':masa_aktif_surat_keputusan' => $data['masa_aktif_surat_keputusan']
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

  // === UPDATE FILE BERKAS SK ===
  public function updateFile($nip, $path)
  {
      $stmt = $this->db->prepare("
          UPDATE {$this->table}
          SET berkas_surat_keputusan = :path
          WHERE nomor_induk_pegawai = :nip
      ");
      return $stmt->execute([
          ':path' => $path,
          ':nip'  => $nip
      ]);
  }

  // === Hitung total SK aktif (dengan pencarian opsional) ===
  public function countAll(string $search = ''): int
  {
      $sql = "
          SELECT COUNT(*) 
          FROM {$this->table} sk
          JOIN pegawai p ON p.nomor_induk_pegawai = sk.nomor_induk_pegawai
          JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
          WHERE sk.nomor_surat_keputusan IS NOT NULL
            AND sp.status_keaktifan = 'Aktif'
      ";

      if (!empty($search)) {
          $sql .= " AND (sk.nomor_surat_keputusan LIKE :search 
                     OR p.nama_lengkap LIKE :search)";
      }

      $stmt = $this->db->prepare($sql);
      if (!empty($search)) {
          $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
      }
      $stmt->execute();
      return (int)$stmt->fetchColumn();
  }

  // === Ambil daftar SK dengan pagination ===
  public function getPaginated(int $limit, int $offset, string $search = ''): array
  {
      $sql = "
          SELECT 
            sk.id,
            sk.nomor_induk_pegawai,
            sk.nomor_surat_keputusan,
            sk.mulai_berlaku_surat_keputusan,
            sk.berakhir_surat_keputusan,
            sk.berkas_surat_keputusan,
            sk.masa_aktif_surat_keputusan,
            p.nama_lengkap,
            sp.status_keaktifan
          FROM {$this->table} sk
          JOIN pegawai p ON p.nomor_induk_pegawai = sk.nomor_induk_pegawai
          JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
          WHERE sk.nomor_surat_keputusan IS NOT NULL
            AND sp.status_keaktifan = 'Aktif'
      ";

      if (!empty($search)) {
          $sql .= " AND (sk.nomor_surat_keputusan LIKE :search 
                     OR p.nama_lengkap LIKE :search)";
      }

      $sql .= " ORDER BY sk.berakhir_surat_keputusan DESC LIMIT :limit OFFSET :offset";

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