<?php

class PegawaiModel extends Model
{
    protected $table = 'pegawai';

    // === CREATE sederhana (hanya NIP + nama) ===
    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (nomor_induk_pegawai, nama_lengkap)
            VALUES (:nip, :nama)
        ");
        return $stmt->execute([
            ':nip'  => $data['nomor_induk_pegawai'],
            ':nama' => $data['nama_lengkap'],
        ]);
    }

    // === FIND ===
    public function findByNIP($nip)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE nomor_induk_pegawai = :nip LIMIT 1");
        $stmt->execute([':nip' => $nip]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // === GET ALL ===
    public function getAll()
    {
        $stmt = $this->db->prepare("
            SELECT p.id AS pegawai_id,
                   p.nomor_induk_pegawai,
                   p.nama_lengkap,
                   u.nama_unit AS unit_nama,
                   p.photo,
                   p.pendidikan_terakhir,
                   p.tanggal_masuk,
                   p.tanggal_masuk_awal,
                   p.tanggal_masuk_aktif,
                   TIMESTAMPDIFF(
                                       MONTH,
                                       COALESCE(p.tanggal_masuk_aktif, p.tanggal_masuk_awal),
                                       CURDATE()
                   ) AS lama_kerja_bulan,
                   pd.jenjang AS pendidikan_jenjang,
                   p.golongan_pegawai_id,
                   p.kategori_kepegawaian_id,
                   p.pendidikan_id,
                   p.status_kepegawaian_id,
                   sk.status AS status_kepegawaian,
                   p.status_pernikahan_id,
                   usr.jabatan_id,
                   sp.status_keaktifan
            FROM {$this->table} p
            LEFT JOIN unit_kerja u ON p.unit_id = u.id
            LEFT JOIN pendidikan pd ON p.pendidikan_id = pd.id
            LEFT JOIN user usr ON usr.nomor_induk_pegawai = p.nomor_induk_pegawai
            LEFT JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
            LEFT JOIN status_kepegawaian sk ON p.status_kepegawaian_id = sk.id
            WHERE LOWER(TRIM(p.nama_lengkap)) <> 'administrator'
                      AND p.nomor_induk_pegawai <> '000000'
                      AND sp.status_keaktifan = 'Aktif'
            ORDER BY p.nomor_induk_pegawai ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllAktif()
    {
        $sql = "SELECT p.nomor_induk_pegawai,
                       p.nama_lengkap,
                       u.nama_unit AS unit_nama,
                       p.photo,
                       p.pendidikan_terakhir,
                       p.tanggal_masuk,
                       p.tanggal_masuk_awal,
                       p.tanggal_masuk_aktif,
                       TIMESTAMPDIFF(
                           YEAR,
                           COALESCE(p.tanggal_masuk_aktif, p.tanggal_masuk_awal),
                           CURDATE()
                       ) AS lama_kerja,
                       pd.jenjang AS pendidikan_jenjang,
                       -- kolom ID master untuk remunerasi
                       p.golongan_pegawai_id,
                       p.kategori_kepegawaian_id,
                       p.pendidikan_id,
                       p.status_kepegawaian_id,
                       sk.status AS status_kepegawaian,
                       p.status_pernikahan_id,
                       usr.jabatan_id   -- ambil dari tabel user                       
                FROM {$this->table} p
                JOIN status_pegawai sp 
                  ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                LEFT JOIN unit_kerja u 
                  ON p.unit_id = u.id
                LEFT JOIN pendidikan pd 
                  ON p.pendidikan_id = pd.id
                LEFT JOIN user usr ON usr.nomor_induk_pegawai = p.nomor_induk_pegawai
                LEFT JOIN status_kepegawaian sk ON p.status_kepegawaian_id = sk.id
                WHERE sp.status_keaktifan = 'Aktif'
                  AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'
                ORDER BY p.nomor_induk_pegawai ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUnitId($unitId)
    {
        $sql = "SELECT p.nomor_induk_pegawai,
                       p.nama_lengkap,
                       u.nama_unit AS unit_nama,
                       p.photo,
                       p.pendidikan_terakhir,
                       p.tanggal_masuk,
                       p.tanggal_masuk_awal,
                       p.tanggal_masuk_aktif,
                       TIMESTAMPDIFF(
                           YEAR,
                           COALESCE(p.tanggal_masuk_aktif, p.tanggal_masuk_awal),
                           CURDATE()
                       ) AS lama_kerja,
                       pd.jenjang AS pendidikan_jenjang,
                       -- kolom ID master untuk remunerasi
                       p.golongan_pegawai_id,
                       p.kategori_kepegawaian_id,
                       p.pendidikan_id,
                       p.status_kepegawaian_id,
                       sk.status AS status_kepegawaian,
                       p.status_pernikahan_id,
                       usr.jabatan_id   -- ambil dari tabel user
                FROM {$this->table} p
                JOIN status_pegawai sp 
                  ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                LEFT JOIN unit_kerja u 
                  ON p.unit_id = u.id
                LEFT JOIN pendidikan pd 
                  ON p.pendidikan_id = pd.id
                LEFT JOIN user usr ON usr.nomor_induk_pegawai = p.nomor_induk_pegawai
                LEFT JOIN status_kepegawaian sk ON p.status_kepegawaian_id = sk.id
                WHERE sp.status_keaktifan = 'Aktif'
                  AND p.unit_id = :unit_id
                  AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'
                ORDER BY p.nomor_induk_pegawai ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':unit_id', $unitId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllNonAktif()
    {
        $sql = "SELECT p.nomor_induk_pegawai,
                       p.nama_lengkap,
                       u.nama_unit AS unit_nama,
                       sp.alasan_non_aktif,
                       sp.status_keaktifan,
                       p.pendidikan_terakhir,
                       p.tanggal_masuk,
                       p.tanggal_masuk_awal,
                       p.tanggal_masuk_aktif,
                       TIMESTAMPDIFF(
                           YEAR,
                           COALESCE(p.tanggal_masuk_aktif, p.tanggal_masuk_awal),
                           CURDATE()
                       ) AS lama_kerja,
                       pd.jenjang AS pendidikan_jenjang,
                       -- kolom ID master untuk remunerasi
                       p.golongan_pegawai_id,
                       p.kategori_kepegawaian_id,
                       p.pendidikan_id,
                       p.status_kepegawaian_id,
                       sk.status AS status_kepegawaian,
                       p.status_pernikahan_id,
                       usr.jabatan_id   -- ambil dari tabel user
                FROM {$this->table} p
                JOIN status_pegawai sp 
                  ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                LEFT JOIN unit_kerja u 
                  ON p.unit_id = u.id
                LEFT JOIN pendidikan pd 
                  ON p.pendidikan_id = pd.id
                LEFT JOIN user usr ON usr.nomor_induk_pegawai = p.nomor_induk_pegawai
                LEFT JOIN status_kepegawaian sk ON p.status_kepegawaian_id = sk.id
                WHERE sp.status_keaktifan = 'Non Aktif'
                ORDER BY p.nomor_induk_pegawai ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByNIP($nip)
    {
        $sql = "SELECT p.nomor_induk_kependudukan,
                       p.nomor_induk_pegawai,
                       p.nama_lengkap,
                       p.tempat_lahir,
                       p.tanggal_lahir,
                       p.jenis_kelamin_id,
                       p.agama_id,
                       p.alamat_tempat_tinggal,
                       p.email,
                       p.nomor_telepon,
                       p.status_pernikahan_id,
                       p.jumlah_anak,
                       p.pendidikan_id,
                       pd.jenjang AS pendidikan_jenjang,
                       pd.nilai AS pendidikan_nilai,
                       p.pendidikan_terakhir, -- nama sekolah/universitas (manual)
                       p.berkas_ijazah_terakhir,
                       p.nama_bank,
                       p.nomor_rekening,
                       p.status_kepegawaian_id,
                       sk.status AS status_kepegawaian,
                       p.golongan_pegawai_id,
                       p.kategori_kepegawaian_id,
                       p.unit_id,
                       uk.nama_unit AS unit_nama,
                       p.tanggal_masuk,
                       p.tanggal_masuk_awal,
                       p.tanggal_masuk_aktif,
                       TIMESTAMPDIFF(
                           YEAR,
                           COALESCE(p.tanggal_masuk_aktif, p.tanggal_masuk_awal),
                           CURDATE()
                       ) AS lama_kerja,
                       p.photo
                FROM {$this->table} p
                LEFT JOIN unit_kerja uk ON p.unit_id = uk.id
                LEFT JOIN pendidikan pd ON p.pendidikan_id = pd.id
                LEFT JOIN status_kepegawaian sk ON p.status_kepegawaian_id = sk.id
                WHERE p.nomor_induk_pegawai = :nip
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nip' => $nip]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAllWithoutUser()
    {
        $stmt = $this->db->prepare("
            SELECT p.nomor_induk_pegawai,
                   p.nama_lengkap,
                   p.pendidikan_terakhir,
                   p.tanggal_masuk,
                   p.tanggal_masuk_awal,
                   p.tanggal_masuk_aktif,
                   TIMESTAMPDIFF(
                       YEAR,
                       COALESCE(p.tanggal_masuk_aktif, p.tanggal_masuk_awal),
                       CURDATE()
                   ) AS lama_kerja,
                   pd.jenjang AS pendidikan_jenjang,
                   -- kolom ID master untuk remunerasi
                   p.golongan_pegawai_id,
                   p.kategori_kepegawaian_id,
                   p.pendidikan_id,
                   p.status_kepegawaian_id,
                   sk.status AS status_kepegawaian,
                   p.status_pernikahan_id,
                   usr.jabatan_id   -- ambil dari tabel user
            FROM {$this->table} p
            LEFT JOIN user u 
                   ON u.nomor_induk_pegawai = p.nomor_induk_pegawai
            LEFT JOIN pendidikan pd 
                   ON p.pendidikan_id = pd.id
            LEFT JOIN user usr ON usr.nomor_induk_pegawai = p.nomor_induk_pegawai
            LEFT JOIN status_kepegawaian sk ON p.status_kepegawaian_id = sk.id
            LEFT JOIN status_pegawai s ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
            WHERE u.id IS NULL
                AND sk.status != 'Non Pegawai'
                AND s.status_keaktifan = 'Aktif'
            ORDER BY p.nomor_induk_pegawai ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllWithStatus()
    {
        $sql = "SELECT p.*,
                       s.status_keaktifan,
                       pd.jenjang AS pendidikan_jenjang,
                       pd.nilai AS pendidikan_nilai,
                       p.tanggal_masuk_awal,
                       p.tanggal_masuk_aktif,
                       TIMESTAMPDIFF(
                           YEAR,
                           COALESCE(p.tanggal_masuk_aktif, p.tanggal_masuk_awal),
                           CURDATE()
                       ) AS lama_kerja
                FROM pegawai p
                JOIN status_pegawai s 
                  ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
                LEFT JOIN pendidikan pd 
                  ON p.pendidikan_id = pd.id
                WHERE s.status_keaktifan = 'Aktif'
                  AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'
                ORDER BY p.nomor_induk_pegawai ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // === SAVE (INSERT) ===
    public function save($data)
    {
        // Trim NIP untuk menghindari spasi tersembunyi
        $nip = trim($data['nomor_induk_pegawai']);

        // Cek apakah NIP sudah ada
        if ($this->existsByNIP($nip)) {
            error_log("❌ NIP $nip sudah ada, gunakan update.");
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (
                nomor_induk_kependudukan, nomor_induk_pegawai, nama_lengkap, tempat_lahir, tanggal_lahir,
                jenis_kelamin_id, agama_id, alamat_tempat_tinggal, email, nomor_telepon,
                status_pernikahan_id, jumlah_anak, pendidikan_id, pendidikan_terakhir,
                berkas_ijazah_terakhir, nama_bank, nomor_rekening,
                status_kepegawaian_id, golongan_pegawai_id, kategori_kepegawaian_id,
                kepesertaan_BPJS_ketenagakerjaan, kepesertaan_BPJS_kesehatan, status_BPJS_kesehatan,
                unit_id, tanggal_masuk, tanggal_masuk_awal, tanggal_masuk_aktif, photo
            ) VALUES (
                :nomor_induk_kependudukan, :nomor_induk_pegawai, :nama_lengkap, :tempat_lahir, :tanggal_lahir,
                :jenis_kelamin_id, :agama_id, :alamat_tempat_tinggal, :email, :nomor_telepon,
                :status_pernikahan_id, :jumlah_anak, :pendidikan_id, :pendidikan_terakhir,
                :berkas_ijazah_terakhir, :nama_bank, :nomor_rekening,
                :status_kepegawaian_id, :golongan_pegawai_id, :kategori_kepegawaian_id,
                :kepesertaan_BPJS_ketenagakerjaan, :kepesertaan_BPJS_kesehatan, :status_BPJS_kesehatan,
                :unit_id, :tanggal_masuk, :tanggal_masuk_awal, :tanggal_masuk_aktif, :photo
            )
        ");

        // Bind parameter sesuai placeholder di query
        $params = [
            ':nomor_induk_kependudukan' => $data['nomor_induk_kependudukan'] ?? null,
            ':nomor_induk_pegawai'      => $nip,
            ':nama_lengkap'             => $data['nama_lengkap'] ?? null,
            ':tempat_lahir'             => $data['tempat_lahir'] ?? null,
            ':tanggal_lahir'            => $data['tanggal_lahir'] ?? null,
            ':jenis_kelamin_id'         => $data['jenis_kelamin_id'] ?? null,
            ':agama_id'                 => $data['agama_id'] ?? null,
            ':alamat_tempat_tinggal'    => $data['alamat_tempat_tinggal'] ?? null,
            ':email'                    => $data['email'] ?? null,
            ':nomor_telepon'            => $data['nomor_telepon'] ?? null,
            ':status_pernikahan_id'     => $data['status_pernikahan_id'] ?? null,
            ':jumlah_anak'              => $data['jumlah_anak'] ?? null,
            ':pendidikan_id'            => $data['pendidikan_id'] ?? null,
            ':pendidikan_terakhir'      => $data['pendidikan_terakhir'] ?? null,
            ':berkas_ijazah_terakhir'   => $data['berkas_ijazah_terakhir'] ?? null,
            ':nama_bank'                => $data['nama_bank'] ?? null,
            ':nomor_rekening'           => $data['nomor_rekening'] ?? null,
            ':status_kepegawaian_id'    => $data['status_kepegawaian_id'] ?? null,
            ':golongan_pegawai_id'      => $data['golongan_pegawai_id'] ?? null,
            ':kategori_kepegawaian_id'  => $data['kategori_kepegawaian_id'] ?? null,
            ':kepesertaan_BPJS_ketenagakerjaan' => $data['kepesertaan_BPJS_ketenagakerjaan'] ?? null,
            ':kepesertaan_BPJS_kesehatan'       => $data['kepesertaan_BPJS_kesehatan'] ?? null,
            ':status_BPJS_kesehatan'    => $data['status_BPJS_kesehatan'] ?? null,
            ':unit_id'                  => isset($data['unit_id']) ? (int)$data['unit_id'] : null,
            ':tanggal_masuk'      => $data['tanggal_masuk'] ?? date('Y-m-d'),
            ':tanggal_masuk_awal' => $data['tanggal_masuk'] ?? date('Y-m-d'),
            ':tanggal_masuk_aktif'      => null, // default NULL, diisi saat re-hire
            ':photo'                    => $data['photo'] ?? null,
        ];

        $success = $stmt->execute($params);
        if (!$success) {
            error_log("❌ Gagal insert pegawai: " . implode(' | ', $stmt->errorInfo()));
            return false;
        }
        return $this->db->lastInsertId();   // ✅ return ID baru
    }

    // === UPDATE ===
    public function update($data)
    {
        $sql = "
            UPDATE {$this->table} SET
              nomor_induk_kependudukan = :nomor_induk_kependudukan,
              nama_lengkap             = :nama_lengkap,
              tempat_lahir             = :tempat_lahir,
              tanggal_lahir            = :tanggal_lahir,
              jenis_kelamin_id         = :jenis_kelamin_id,
              agama_id                 = :agama_id,
              alamat_tempat_tinggal    = :alamat_tempat_tinggal,
              email                    = :email,
              nomor_telepon            = :nomor_telepon,
              status_pernikahan_id     = :status_pernikahan_id,
              jumlah_anak              = :jumlah_anak,
              pendidikan_id            = :pendidikan_id,
              pendidikan_terakhir      = :pendidikan_terakhir,
              berkas_ijazah_terakhir   = :berkas_ijazah_terakhir,
              nama_bank                = :nama_bank,
              nomor_rekening           = :nomor_rekening,
              status_kepegawaian_id    = :status_kepegawaian_id,
              golongan_pegawai_id      = :golongan_pegawai_id,
              kategori_kepegawaian_id  = :kategori_kepegawaian_id,
              kepesertaan_BPJS_ketenagakerjaan = :kepesertaan_BPJS_ketenagakerjaan,
              kepesertaan_BPJS_kesehatan       = :kepesertaan_BPJS_kesehatan,
              status_BPJS_kesehatan            = :status_BPJS_kesehatan,
              unit_id                = :unit_id,
              tanggal_masuk          = :tanggal_masuk,
              tanggal_masuk_aktif    = :tanggal_masuk_aktif,
              photo                  = :photo
            WHERE nomor_induk_pegawai = :nomor_induk_pegawai
        ";

        $stmt = $this->db->prepare($sql);

        $params = [
            ':nomor_induk_kependudukan' => $data['nomor_induk_kependudukan'] ?? null,
            ':nama_lengkap'             => $data['nama_lengkap'] ?? null,
            ':tempat_lahir'             => $data['tempat_lahir'] ?? null,
            ':tanggal_lahir'            => $data['tanggal_lahir'] ?? null,
            ':jenis_kelamin_id'         => $data['jenis_kelamin_id'] ?? null,
            ':agama_id'                 => $data['agama_id'] ?? null,
            ':alamat_tempat_tinggal'    => $data['alamat_tempat_tinggal'] ?? null,
            ':email'                    => $data['email'] ?? null,
            ':nomor_telepon'            => $data['nomor_telepon'] ?? null,
            ':status_pernikahan_id'     => $data['status_pernikahan_id'] ?? null,
            ':jumlah_anak'              => $data['jumlah_anak'] ?? null,
            ':pendidikan_id'            => $data['pendidikan_id'] ?? null,
            ':pendidikan_terakhir'      => $data['pendidikan_terakhir'] ?? null,
            ':berkas_ijazah_terakhir'   => $data['berkas_ijazah_terakhir'] ?? null,
            ':nama_bank'                => $data['nama_bank'] ?? null,
            ':nomor_rekening'           => $data['nomor_rekening'] ?? null,
            ':status_kepegawaian_id'    => $data['status_kepegawaian_id'] ?? null,
            ':golongan_pegawai_id'      => $data['golongan_pegawai_id'] ?? null,
            ':kategori_kepegawaian_id'  => $data['kategori_kepegawaian_id'] ?? null,
            ':kepesertaan_BPJS_ketenagakerjaan' => $data['kepesertaan_BPJS_ketenagakerjaan'] ?? null,
            ':kepesertaan_BPJS_kesehatan'       => $data['kepesertaan_BPJS_kesehatan'] ?? null,
            ':status_BPJS_kesehatan'            => $data['status_BPJS_kesehatan'] ?? null,
            ':unit_id'                => $data['unit_id'] ?? null,
            ':tanggal_masuk'       => $data['tanggal_masuk'] ?? date('Y-m-d'),
            ':tanggal_masuk_aktif' => $data['tanggal_masuk_aktif'] ?? $data['tanggal_masuk'] ?? date('Y-m-d'),
            ':photo'                  => $data['photo'] ?? null,
            ':nomor_induk_pegawai'    => $data['nomor_induk_pegawai'] ?? null,
        ];

        $success = $stmt->execute($params);
        if (!$success) {
            error_log("❌ Gagal update pegawai: " . implode(' | ', $stmt->errorInfo()));
        }
        return $success;
    }

    public function updatePartial($data)
    {
        $sql = "UPDATE pegawai SET
                  nomor_induk_kependudukan = :nomor_induk_kependudukan,
                  nama_lengkap             = :nama_lengkap,
                  tempat_lahir             = :tempat_lahir,
                  tanggal_lahir            = :tanggal_lahir,
                  jenis_kelamin_id         = :jenis_kelamin_id,
                  alamat_tempat_tinggal    = :alamat_tempat_tinggal,
                  email                    = :email,
                  nomor_telepon            = :nomor_telepon,
                  status_pernikahan_id     = :status_pernikahan_id,
                  jumlah_anak              = :jumlah_anak,
                  pendidikan_id            = :pendidikan_id,
                  pendidikan_terakhir      = :pendidikan_terakhir,
                  nama_bank                = :nama_bank,
                  nomor_rekening           = :nomor_rekening,
                  unit_id                  = :unit_id,
                  tanggal_masuk            = :tanggal_masuk,
                  tanggal_masuk_aktif      = :tanggal_masuk_aktif,
                  photo                    = :photo
                WHERE nomor_induk_pegawai = :nomor_induk_pegawai";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
    }

    // === SAVE OR UPDATE (mode simpan/edit) ===
    public function saveOrUpdate($data)
    {
        // Trim NIP untuk menghindari spasi tersembunyi
        $nip = trim($data['nomor_induk_pegawai']);
        $data['nomor_induk_pegawai'] = $nip;

        if ($this->existsByNIP($nip)) {
            // Update data pegawai lama
            return $this->update($data);
        } else {
            // Insert data pegawai baru
            // otomatis isi tanggal_masuk_awal = tanggal_masuk
            if (!isset($data['tanggal_masuk_awal'])) {
                $data['tanggal_masuk_awal'] = $data['tanggal_masuk'] ?? null;
            }
            // default tanggal_masuk_aktif = NULL
            if (!isset($data['tanggal_masuk_aktif'])) {
                $data['tanggal_masuk_aktif'] = null;
            }
            return $this->save($data);
        }
    }

    // === EXISTS ===
    public function existsByNIP($nip)
    {
        $stmt = $this->db->prepare("
            SELECT 1 
            FROM {$this->table} 
            WHERE nomor_induk_pegawai = :nip 
            LIMIT 1
        ");
        $stmt->execute([':nip' => trim($nip)]);
        return (bool) $stmt->fetchColumn();
    }

    // === STATUS ===
    public function getStatus($nip)
    {
        return $this->db->row("
            SELECT sp.*,
                   p.pendidikan_id,
                   p.pendidikan_terakhir,
                   p.tanggal_masuk,
                   p.tanggal_masuk_awal,
                   p.tanggal_masuk_aktif,
                   TIMESTAMPDIFF(
                       YEAR,
                       COALESCE(p.tanggal_masuk_aktif, p.tanggal_masuk_awal),
                       CURDATE()
                   ) AS lama_kerja,
                   pd.jenjang AS pendidikan_jenjang,
                   pd.nilai   AS pendidikan_nilai
            FROM status_pegawai sp
            LEFT JOIN pegawai p 
                   ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
            LEFT JOIN pendidikan pd 
                   ON p.pendidikan_id = pd.id
            WHERE sp.nomor_induk_pegawai = ?
        ", [$nip]);
    }

    public function getStatusByNIP($nip)
    {
        return $this->db->row("
            SELECT sp.status_keaktifan,
                   sp.alasan_non_aktif,
                   p.pendidikan_terakhir,
                   p.tanggal_masuk,
                   p.tanggal_masuk_awal,
                   p.tanggal_masuk_aktif,
                   TIMESTAMPDIFF(
                       YEAR,
                       COALESCE(p.tanggal_masuk_aktif, p.tanggal_masuk_awal),
                       CURDATE()
                   ) AS lama_kerja,
                   pd.jenjang AS pendidikan_jenjang,
                   pd.nilai   AS pendidikan_nilai
            FROM status_pegawai sp
            LEFT JOIN pegawai p 
                   ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
            LEFT JOIN pendidikan pd 
                   ON p.pendidikan_id = pd.id
            WHERE sp.nomor_induk_pegawai = ?
        ", [$nip]);
    }

    public function countActive()
    {
        $sql = "SELECT COUNT(*) 
                FROM {$this->table} p
                JOIN status_pegawai sp 
                  ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                WHERE sp.status_keaktifan = 'Aktif'
                  AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function countActiveWithLamaKerja()
    {
        $sql = "SELECT TIMESTAMPDIFF(
                        YEAR,
                        COALESCE(p.tanggal_masuk_aktif, p.tanggal_masuk_awal),
                        CURDATE()
                    ) AS lama_kerja,
                       COUNT(p.id) AS jumlah
                FROM {$this->table} p
                JOIN status_pegawai sp 
                  ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                WHERE sp.status_keaktifan = 'Aktif'
                  AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'
                GROUP BY lama_kerja
                ORDER BY lama_kerja ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === GRAFIK ===
    public function countByJenisKelamin() {
        $sql = "SELECT jk.jenis, COUNT(*) AS jumlah
                FROM pegawai p
                JOIN jenis_kelamin jk ON p.jenis_kelamin_id = jk.id
                JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                WHERE p.nomor_induk_pegawai <> '000000'
                  AND sp.status_keaktifan = 'Aktif'
                GROUP BY jk.jenis";
        $this->query($sql);
        return $this->resultSet();
    }

    public function countByJenisKelaminWithLamaKerja() {
        $sql = "SELECT jk.jenis,
                       TIMESTAMPDIFF(
                           YEAR,
                           COALESCE(p.tanggal_masuk_aktif, p.tanggal_masuk_awal),
                           CURDATE()
                       ) AS lama_kerja,
                       COUNT(*) AS jumlah
                FROM pegawai p
                JOIN jenis_kelamin jk ON p.jenis_kelamin_id = jk.id
                JOIN status_pegawai sp ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                WHERE p.nomor_induk_pegawai <> '000000'
                  AND sp.status_keaktifan = 'Aktif'
                GROUP BY jk.jenis, lama_kerja
                ORDER BY jk.jenis, lama_kerja ASC";
        $this->query($sql);
        return $this->resultSet();
    }

    public function countByPendidikan() {
        $sql = "SELECT COALESCE(pd.jenjang, 'Tidak Ada') AS pendidikan,
                       COUNT(p.id) AS jumlah
                FROM pegawai p
                JOIN status_pegawai sp 
                  ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                LEFT JOIN pendidikan pd 
                  ON p.pendidikan_id = pd.id
                WHERE p.nomor_induk_pegawai <> '000000'
                  AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'
                  AND sp.status_keaktifan = 'Aktif'
                GROUP BY pd.jenjang
                ORDER BY pd.jenjang ASC";
        $this->query($sql);
        return $this->resultSet();
    }

    public function countByStatusKepegawaian() {
        $sql = "SELECT sk.status AS status, COUNT(*) AS jumlah
                FROM pegawai p
                JOIN status_kepegawaian sk 
                  ON p.status_kepegawaian_id = sk.id
                JOIN status_pegawai sp 
                  ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                WHERE p.nomor_induk_pegawai <> '000000'
                  AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'
                  AND sp.status_keaktifan = 'Aktif'
                GROUP BY sk.status
                ORDER BY sk.status ASC";
        $this->query($sql);
        return $this->resultSet();
    }

    public function countByKategoriKepegawaian() {
        $sql = "SELECT kk.kategori AS kategori, COUNT(*) AS jumlah
                FROM pegawai p
                JOIN kategori_kepegawaian kk 
                  ON p.kategori_kepegawaian_id = kk.id
                JOIN status_pegawai sp 
                  ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                WHERE p.nomor_induk_pegawai <> '000000'
                  AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'
                  AND sp.status_keaktifan = 'Aktif'
                GROUP BY kk.kategori
                ORDER BY kk.kategori ASC";
        $this->query($sql);
        return $this->resultSet();
    }

    public function countByLamaKerja() {
        $sql = "SELECT TIMESTAMPDIFF(
                           YEAR,
                           COALESCE(p.tanggal_masuk_aktif, p.tanggal_masuk_awal),
                           CURDATE()
                       ) AS lama_kerja,
                       COUNT(p.id) AS jumlah
                FROM pegawai p
                JOIN status_pegawai sp 
                  ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                WHERE p.nomor_induk_pegawai <> '000000'
                  AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'
                  AND sp.status_keaktifan = 'Aktif'
                GROUP BY lama_kerja
                ORDER BY lama_kerja ASC";
        $this->query($sql);
        return $this->resultSet();
    }

    // === UPDATE PHOTO ===
    public function updatePhoto($id, $path)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table} 
                SET photo = :photo 
                WHERE id = :id
            ");
            $success = $stmt->execute([
                ':photo' => $path,
                ':id'    => (int)$id
            ]);

            if (!$success) {
                error_log("❌ Gagal update photo pegawai ID $id: " . implode(' | ', $stmt->errorInfo()));
            }
            return $success;
        } catch (PDOException $e) {
            error_log("❌ Error update photo: " . $e->getMessage());
            return false;
        }
    }

    // === UPDATE IJAZAH ===
    public function updateIjazah($id, $path)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table} 
                SET berkas_ijazah_terakhir = :ijazah 
                WHERE id = :id
            ");
            $success = $stmt->execute([
                ':ijazah' => $path,
                ':id'     => (int)$id
            ]);

            if (!$success) {
                error_log("❌ Gagal update ijazah pegawai ID $id: " . implode(' | ', $stmt->errorInfo()));
            }
            return $success;
        } catch (PDOException $e) {
            error_log("❌ Error update ijazah: " . $e->getMessage());
            return false;
        }
    }

    public function getDb(): ?PDO
    {
        if ($this->db instanceof PDO) {
            return $this->db;
        }
        error_log("❌ getDb dipanggil tapi \$this->db bukan instance PDO");
        return null;
    }
    
    public function existsByNomorInduk($nip)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 1 
                FROM pegawai 
                WHERE nomor_induk_pegawai = :nip 
                LIMIT 1
            ");
            $stmt->execute([':nip' => trim($nip)]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("❌ Error cek Nomor Induk Pegawai: " . $e->getMessage());
            return false;
        }
    }

    public function countPegawaiAktifAll()
    {
        $sql = "
            SELECT COUNT(*) 
            FROM pegawai p
            JOIN status_pegawai s 
              ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
            WHERE s.status_keaktifan = 'Aktif'
              AND p.nomor_induk_pegawai <> '000000'
              AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'
        ";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    public function getUnitByPegawaiId($id)
    {
        try {
            $sql = "SELECT u.nama_unit
                    FROM pegawai p
                    JOIN unit_kerja u ON p.unit_id = u.id
                    WHERE p.id = :id
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => (int)$id]);
            return $stmt->fetchColumn() ?: null; // return null kalau tidak ada
        } catch (PDOException $e) {
            error_log("❌ Error getUnitByPegawaiId($id): " . $e->getMessage());
            return null;
        }
    }

    // Hitung total pegawai aktif (dengan pencarian opsional)
    public static function countAll(string $search = ''): int
    {
        $db = new Database();
        $sql = "SELECT COUNT(*) AS jumlah
                  FROM pegawai p
                  JOIN status_pegawai s 
                    ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
                 WHERE LOWER(s.status_keaktifan) = 'aktif'
                   AND p.nomor_induk_pegawai <> '000000'
                   AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'";

        if (!empty($search)) {
            $sql .= " AND (p.nama_lengkap LIKE :search 
                       OR p.nomor_induk_pegawai LIKE :search)";
        }

        $db->prepareQuery($sql);

        if (!empty($search)) {
            $db->bind(':search', "%$search%");
        }

        $db->execute();
        $row = $db->single();
        return isset($row['jumlah']) ? (int)$row['jumlah'] : 0;
    }

    // Ambil daftar pegawai aktif dengan pagination
    public static function getPaginated(int $limit, int $offset, string $search = ''): array
    {
        $db = new Database();
        $sql = "SELECT p.*, 
                       s.status_keaktifan, 
                       pd.jenjang AS pendidikan_jenjang, 
                       pd.nilai   AS pendidikan_nilai,
                       p.status_kepegawaian_id,
                       sk.status AS status_kepegawaian
                  FROM pegawai p
                  JOIN status_pegawai s 
                    ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
                  LEFT JOIN pendidikan pd 
                    ON p.pendidikan_id = pd.id
                  LEFT JOIN status_kepegawaian sk ON p.status_kepegawaian_id = sk.id
                 WHERE LOWER(s.status_keaktifan) = 'aktif'
                   AND p.nomor_induk_pegawai <> '000000'
                   AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'";

        if (!empty($search)) {
            $sql .= " AND (p.nama_lengkap LIKE :search 
                       OR p.nomor_induk_pegawai LIKE :search)";
        }

        $sql .= " ORDER BY p.nomor_induk_pegawai ASC LIMIT :limit OFFSET :offset";

        $db->prepareQuery($sql);
        $db->bind(':limit', $limit, PDO::PARAM_INT);
        $db->bind(':offset', $offset, PDO::PARAM_INT);

        if (!empty($search)) {
            $db->bind(':search', "%$search%");
        }

        return $db->resultSet();
    }

    // Ambil data pegawai milik user sendiri
    public static function getOwn(int $pegawaiId): ?array
    {
        $db = new Database();
        $sql = "SELECT p.*, 
                       s.status_keaktifan,
                       pd.jenjang AS pendidikan_jenjang,
                       pd.nilai   AS pendidikan_nilai
                  FROM pegawai p
                  JOIN status_pegawai s 
                    ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
                  LEFT JOIN pendidikan pd 
                    ON p.pendidikan_id = pd.id
                 WHERE p.id = :id
                   AND LOWER(s.status_keaktifan) = 'aktif'
                   AND p.nomor_induk_pegawai <> '000000'
                   AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'
                 LIMIT 1";

        $db->prepareQuery($sql);
        $db->bind(':id', $pegawaiId, PDO::PARAM_INT);

        return $db->single() ?: null;
    }

    // Hitung total pegawai nonaktif
    public function countNonAktif(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) 
                FROM pegawai p
                JOIN status_pegawai sp 
                  ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                WHERE LOWER(sp.status_keaktifan) = 'non aktif'
                  AND p.nomor_induk_pegawai <> '000000'
                  AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'";

        if (!empty($search)) {
            $sql .= " AND (p.nama_lengkap LIKE :search 
                       OR p.nomor_induk_pegawai LIKE :search)";
        }

        $stmt = $this->db->prepare($sql);

        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    // Ambil daftar pegawai nonaktif dengan pagination
    public function getNonAktifPaginated(int $limit, int $offset, string $search = ''): array
    {
        $sql = "SELECT p.nomor_induk_pegawai, 
                       p.nama_lengkap, 
                       sp.status_keaktifan, 
                       sp.alasan_non_aktif
                FROM pegawai p
                JOIN status_pegawai sp 
                  ON sp.nomor_induk_pegawai = p.nomor_induk_pegawai
                WHERE LOWER(sp.status_keaktifan) = 'non aktif'
                  AND p.nomor_induk_pegawai <> '000000'
                  AND LOWER(TRIM(p.nama_lengkap)) <> 'administrator'";

        if (!empty($search)) {
            $sql .= " AND (p.nama_lengkap LIKE :search 
                       OR p.nomor_induk_pegawai LIKE :search)";
        }

        $sql .= " ORDER BY p.nomor_induk_pegawai ASC LIMIT :limit OFFSET :offset";

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