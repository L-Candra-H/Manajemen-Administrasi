<?php
require_once __DIR__ . '/../core/Model.php';

class SipModel extends Model
{
  protected $table = 'surat_ijin_SIP';

  // === GET ALL ===
  public function getAll()
  {
      $query = "
        SELECT 
          s.id,
          s.nomor_induk_pegawai,
          s.nomor_SIP,
          s.mulai_berlaku_SIP,
          s.berakhir_SIP,
          s.berkas_SIP,
          s.masa_aktif_SIP,
          p.nama_lengkap,
          sp.status_keaktifan
        FROM {$this->table} s
        JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
        JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
        WHERE s.nomor_SIP IS NOT NULL
          AND s.berakhir_SIP >= CURDATE()
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
        s.nomor_SIP,
        s.mulai_berlaku_SIP,
        s.berakhir_SIP,
        s.berkas_SIP,
        s.masa_aktif_SIP,
        p.nama_lengkap
      FROM {$this->table} s
      JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
      WHERE s.nomor_SIP IS NOT NULL
        AND s.nomor_induk_pegawai = :nip
        AND s.berakhir_SIP >= CURDATE()
      ORDER BY s.berakhir_SIP DESC
      LIMIT 1
    ";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':nip', $nip);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC); // hanya 1 SIP aktif
  }

  // === GET BY ID ===
  public function getById($id)
  {
      $query = "
        SELECT 
          s.id,
          s.nomor_induk_pegawai,
          s.nomor_SIP,
          s.mulai_berlaku_SIP,
          s.berakhir_SIP,
          s.berkas_SIP,
          s.masa_aktif_SIP,
          p.nama_lengkap,
          sp.status_keaktifan
        FROM {$this->table} s
        JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
        JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
        WHERE s.id = :id
      ";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // === SAVE (INSERT) ===
  public function save($data)
  {
    $query = "
      INSERT INTO {$this->table} (
        nomor_induk_pegawai, nomor_SIP, mulai_berlaku_SIP, berakhir_SIP, berkas_SIP, masa_aktif_SIP
      ) VALUES (
        :nomor_induk_pegawai, :nomor_SIP, :mulai_berlaku_SIP, :berakhir_SIP, :berkas_SIP, :masa_aktif_SIP
      )
    ";
    return $this->executeQuery($query, [
      ':nomor_induk_pegawai' => $data['nomor_induk_pegawai'],
      ':nomor_SIP'           => $data['nomor_SIP'],
      ':mulai_berlaku_SIP'   => $data['mulai_berlaku_SIP'],
      ':berakhir_SIP'        => $data['berakhir_SIP'],
      ':berkas_SIP'          => $data['berkas_SIP'],
      ':masa_aktif_SIP'      => $data['masa_aktif_SIP']

    ]);
  }

  // === UPDATE ===
  public function update($data)
  {
    $query = "
      UPDATE {$this->table}
      SET nomor_SIP = :nomor_SIP,
          mulai_berlaku_SIP = :mulai_berlaku_SIP,
          berakhir_SIP = :berakhir_SIP,
          berkas_SIP = :berkas_SIP,
          masa_aktif_SIP = :masa_aktif_SIP
      WHERE id = :id
    ";
    return $this->executeQuery($query, [
      ':id'                => $data['id'],
      ':nomor_SIP'           => $data['nomor_SIP'],
      ':mulai_berlaku_SIP'   => $data['mulai_berlaku_SIP'],
      ':berakhir_SIP'        => $data['berakhir_SIP'],
      ':berkas_SIP'          => $data['berkas_SIP'],
      ':masa_aktif_SIP'      => $data['masa_aktif_SIP']
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

  // === UPDATE FILE BERKAS SIP ===
  public function updateFile($nip, $path)
  {
      $stmt = $this->db->prepare("
          UPDATE {$this->table}
          SET berkas_SIP = :path
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
          JOIN surat_ijin_SIP sp 
              ON p.nomor_induk_pegawai = sp.nomor_induk_pegawai
          WHERE s.status_keaktifan = 'Aktif'
      ";
      return (int)$this->db->query($sql)->fetchColumn();
  }

  // === PAGINATING ===

  // Hitung total SIP aktif (dengan pencarian opsional)
  public function countAll(string $search = ''): int
  {
      $sql = "
          SELECT COUNT(*) 
          FROM {$this->table} s
          JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
          JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
          WHERE s.nomor_SIP IS NOT NULL
            AND s.berakhir_SIP >= CURDATE()
            AND sp.status_keaktifan = 'Aktif'
      ";

      if (!empty($search)) {
          $sql .= " AND (p.nama_lengkap LIKE :search OR s.nomor_SIP LIKE :search)";
      }

      $stmt = $this->db->prepare($sql);
      if (!empty($search)) {
          $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
      }
      $stmt->execute();
      return (int)$stmt->fetchColumn();
  }

  // Ambil daftar SIP dengan pagination
  public function getPaginated(int $limit, int $offset, string $search = ''): array
  {
      $sql = "
          SELECT 
            s.id,
            s.nomor_induk_pegawai,
            s.nomor_SIP,
            s.mulai_berlaku_SIP,
            s.berakhir_SIP,
            s.berkas_SIP,
            s.masa_aktif_SIP,
            p.nama_lengkap,
            sp.status_keaktifan
          FROM {$this->table} s
          JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
          JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
          WHERE s.nomor_SIP IS NOT NULL
            AND s.berakhir_SIP >= CURDATE()
            AND sp.status_keaktifan = 'Aktif'
      ";

      if (!empty($search)) {
          $sql .= " AND (p.nama_lengkap LIKE :search OR s.nomor_SIP LIKE :search)";
      }

      $sql .= " ORDER BY s.berakhir_SIP DESC LIMIT :limit OFFSET :offset";

      $stmt = $this->db->prepare($sql);
      $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
      $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

      if (!empty($search)) {
          $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
      }

      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Ambil SIP milik user sendiri (untuk view-own)
  public function getOwn(string $nip): ?array
  {
      $sql = "
          SELECT 
            s.id,
            s.nomor_induk_pegawai,
            s.nomor_SIP,
            s.mulai_berlaku_SIP,
            s.berakhir_SIP,
            s.berkas_SIP,
            s.masa_aktif_SIP,
            p.nama_lengkap
          FROM {$this->table} s
          JOIN pegawai p ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
          WHERE s.nomor_induk_pegawai = :nip
            AND s.berakhir_SIP >= CURDATE()
          ORDER BY s.berakhir_SIP DESC
          LIMIT 1
      ";
      $stmt = $this->db->prepare($sql);
      $stmt->bindValue(':nip', $nip);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

}