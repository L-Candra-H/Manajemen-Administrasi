<?php
require_once __DIR__ . '/../core/Model.php';

class BpjsModel extends Model
{
  protected $table = 'kepesertaan_BPJS';

  public function getByNIP($nip)
  {
    $query = "SELECT * FROM {$this->table} WHERE nomor_induk_pegawai = :nip";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':nip', $nip);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function save($data)
  {
    return $this->existsByNIP($data['nomor_induk_pegawai'])
      ? $this->update($data)
      : $this->insert($data);
  }

  public function insert($data)
  {
    $query = "
      INSERT INTO {$this->table} (
        nomor_induk_pegawai,
        nomor_kartu_BPJS_ketenagakerjaan,
        nomor_kartu_BPJS_kesehatan
      ) VALUES (
        :nomor_induk_pegawai,
        :no_kerja,
        :no_sehat
      )
    ";
    return $this->executeQuery($query, [
      ':nomor_induk_pegawai' => $data['nomor_induk_pegawai']?? null,
      ':no_kerja'            => $data['nomor_kartu_BPJS_ketenagakerjaan']?? null,
      ':no_sehat'            => $data['nomor_kartu_BPJS_kesehatan']?? null
    ]);
  }

  public function update($data)
  {
    $query = "
      UPDATE {$this->table}
      SET 
        nomor_kartu_BPJS_ketenagakerjaan = :no_kerja,
        nomor_kartu_BPJS_kesehatan       = :no_sehat
      WHERE nomor_induk_pegawai = :nomor_induk_pegawai
    ";
    return $this->executeQuery($query, [
      ':nomor_induk_pegawai' => $data['nomor_induk_pegawai']?? null,
      ':no_kerja'            => $data['nomor_kartu_BPJS_ketenagakerjaan']?? null,
      ':no_sehat'            => $data['nomor_kartu_BPJS_kesehatan']?? null
    ]);
  }

  public function existsByNIP($nip)
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE nomor_induk_pegawai = :nip");
    $stmt->execute([':nip' => $nip]);
    return $stmt->fetchColumn() > 0;
  }

  public function deleteByNIP($nip)
  {
    $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE nomor_induk_pegawai = :nip");
    return $stmt->execute([':nip' => $nip]);
  }

  private function executeQuery($query, $params)
  {
    $stmt = $this->db->prepare($query);
    $success = $stmt->execute($params);
    if (!$success) {
      error_log("❌ Query gagal di {$this->table}: " . implode(' | ', $stmt->errorInfo()));
    }
    return $success;
  }

  public function getDaftarBpjs()
  {
      $sql = "
        SELECT 
          p.nomor_induk_pegawai, 
          p.nama_lengkap,
          b.nomor_kartu_BPJS_ketenagakerjaan,
          b.nomor_kartu_BPJS_kesehatan,

          -- BPJS Keluarga
          CONCAT_WS('; ',
              IF(k.status_keaktifan_BPJS_kesehatan_suami_istri = 'AKTIF',
                 CONCAT(k.nomor_kartu_BPJS_kesehatan_suami_istri, ', ', k.nama_lengkap_suami_istri), NULL),
              IF(k.status_keaktifan_BPJS_kesehatan_anak_I = 'AKTIF',
                 CONCAT(k.nomor_kartu_BPJS_kesehatan_anak_I, ', ', k.nama_lengkap_anak_I), NULL),
              IF(k.status_keaktifan_BPJS_kesehatan_anak_II = 'AKTIF',
                 CONCAT(k.nomor_kartu_BPJS_kesehatan_anak_II, ', ', k.nama_lengkap_anak_II), NULL),
              IF(k.status_keaktifan_BPJS_kesehatan_anak_III = 'AKTIF',
                 CONCAT(k.nomor_kartu_BPJS_kesehatan_anak_III, ', ', k.nama_lengkap_anak_III), NULL)
          ) AS keluarga,

          -- BPJS Tambahan
          CONCAT_WS('; ',
              IF(t.status_keaktifan_BPJS_kesehatan_tambahan_I = 'AKTIF',
                 CONCAT(t.nomor_kartu_BPJS_kesehatan_tambahan_I, ', ', t.nama_lengkap_tambahan_I), NULL),
              IF(t.status_keaktifan_BPJS_kesehatan_tambahan_II = 'AKTIF',
                 CONCAT(t.nomor_kartu_BPJS_kesehatan_tambahan_II, ', ', t.nama_lengkap_tambahan_II), NULL),
              IF(t.status_keaktifan_BPJS_kesehatan_tambahan_III = 'AKTIF',
                 CONCAT(t.nomor_kartu_BPJS_kesehatan_tambahan_III, ', ', t.nama_lengkap_tambahan_III), NULL),
              IF(t.status_keaktifan_BPJS_kesehatan_tambahan_IV = 'AKTIF',
                 CONCAT(t.nomor_kartu_BPJS_kesehatan_tambahan_IV, ', ', t.nama_lengkap_tambahan_IV), NULL)
          ) AS tambahan

        FROM pegawai p
        LEFT JOIN kepesertaan_BPJS b 
               ON p.nomor_induk_pegawai = b.nomor_induk_pegawai
        LEFT JOIN kepesertaan_BPJS_keluarga k 
               ON p.nomor_induk_pegawai = k.nomor_induk_pegawai
        LEFT JOIN kepesertaan_BPJS_tambahan t 
               ON p.nomor_induk_pegawai = t.nomor_induk_pegawai
        JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
        WHERE p.nama_lengkap <> 'Administrator'
          AND sp.status_keaktifan = 'Aktif'
      ";

      $stmt = $this->db->query($sql);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function countAll(string $search = ''): int
  {
      $sql = "SELECT COUNT(*) 
              FROM {$this->table} b
              JOIN pegawai p ON p.nomor_induk_pegawai = b.nomor_induk_pegawai
              JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
              LEFT JOIN kepesertaan_BPJS_keluarga k ON p.nomor_induk_pegawai = k.nomor_induk_pegawai
              LEFT JOIN kepesertaan_BPJS_tambahan t ON p.nomor_induk_pegawai = t.nomor_induk_pegawai
              WHERE sp.status_keaktifan = 'Aktif'";

      if (!empty($search)) {
          $sql .= " AND (
              p.nama_lengkap LIKE :search OR 
              b.nomor_kartu_BPJS_ketenagakerjaan LIKE :search OR 
              b.nomor_kartu_BPJS_kesehatan LIKE :search OR
              k.nama_lengkap_suami_istri LIKE :search OR
              t.nama_lengkap_tambahan_I LIKE :search
          )";
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
                  b.nomor_kartu_BPJS_ketenagakerjaan,
                  b.nomor_kartu_BPJS_kesehatan,

                  -- BPJS Keluarga
                  CONCAT_WS('; ',
                      IF(k.status_keaktifan_BPJS_kesehatan_suami_istri = 'AKTIF',
                         CONCAT(k.nomor_kartu_BPJS_kesehatan_suami_istri, ', ', k.nama_lengkap_suami_istri), NULL),
                      IF(k.status_keaktifan_BPJS_kesehatan_anak_I = 'AKTIF',
                         CONCAT(k.nomor_kartu_BPJS_kesehatan_anak_I, ', ', k.nama_lengkap_anak_I), NULL),
                      IF(k.status_keaktifan_BPJS_kesehatan_anak_II = 'AKTIF',
                         CONCAT(k.nomor_kartu_BPJS_kesehatan_anak_II, ', ', k.nama_lengkap_anak_II), NULL),
                      IF(k.status_keaktifan_BPJS_kesehatan_anak_III = 'AKTIF',
                         CONCAT(k.nomor_kartu_BPJS_kesehatan_anak_III, ', ', k.nama_lengkap_anak_III), NULL)
                  ) AS keluarga,

                  -- BPJS Tambahan
                  CONCAT_WS('; ',
                      IF(t.status_keaktifan_BPJS_kesehatan_tambahan_I = 'AKTIF',
                         CONCAT(t.nomor_kartu_BPJS_kesehatan_tambahan_I, ', ', t.nama_lengkap_tambahan_I), NULL),
                      IF(t.status_keaktifan_BPJS_kesehatan_tambahan_II = 'AKTIF',
                         CONCAT(t.nomor_kartu_BPJS_kesehatan_tambahan_II, ', ', t.nama_lengkap_tambahan_II), NULL),
                      IF(t.status_keaktifan_BPJS_kesehatan_tambahan_III = 'AKTIF',
                         CONCAT(t.nomor_kartu_BPJS_kesehatan_tambahan_III, ', ', t.nama_lengkap_tambahan_III), NULL),
                      IF(t.status_keaktifan_BPJS_kesehatan_tambahan_IV = 'AKTIF',
                         CONCAT(t.nomor_kartu_BPJS_kesehatan_tambahan_IV, ', ', t.nama_lengkap_tambahan_IV), NULL)
                  ) AS tambahan

              FROM {$this->table} b
              JOIN pegawai p ON p.nomor_induk_pegawai = b.nomor_induk_pegawai
              JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
              LEFT JOIN kepesertaan_BPJS_keluarga k ON p.nomor_induk_pegawai = k.nomor_induk_pegawai
              LEFT JOIN kepesertaan_BPJS_tambahan t ON p.nomor_induk_pegawai = t.nomor_induk_pegawai
              WHERE sp.status_keaktifan = 'Aktif'";

      if (!empty($search)) {
          $sql .= " AND (
              p.nama_lengkap LIKE :search OR 
              b.nomor_kartu_BPJS_ketenagakerjaan LIKE :search OR 
              b.nomor_kartu_BPJS_kesehatan LIKE :search OR
              k.nama_lengkap_suami_istri LIKE :search OR
              t.nama_lengkap_tambahan_I LIKE :search
          )";
      }

      $sql .= " ORDER BY b.nomor_induk_pegawai ASC LIMIT :limit OFFSET :offset";

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