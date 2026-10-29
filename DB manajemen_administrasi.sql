CREATE DATABASE manajemen_administrasi;
USE manajemen_administrasi;

-- ===========================
-- MASTER DATA (MODUL 1)
-- ===========================

CREATE TABLE institusi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_institusi VARCHAR(100),
  sub_institusi VARCHAR(100),
  alamat TEXT,
  telepon VARCHAR(20),
  logo VARCHAR(255)
);

CREATE TABLE jenis_kelamin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  jenis VARCHAR(10)
);
INSERT INTO jenis_kelamin (jenis) VALUES ('Pria'), ('Wanita');

CREATE TABLE status_pernikahan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  status VARCHAR(20),
  nilai INT
);

INSERT INTO status_pernikahan (status, nilai) VALUES
('Menikah', 8),
('Belum Menikah', 4),
('Duda', 6),
('Janda', 6);

CREATE TABLE status_kepegawaian (
  id INT AUTO_INCREMENT PRIMARY KEY,
  status VARCHAR(15),
  nilai INT
);

-- isi data baru langsung dengan nilai
INSERT INTO status_kepegawaian (status, nilai) VALUES 
('Kontrak', 3),
('Tetap', 6),
('Non Pegawai', 0);

CREATE TABLE golongan_pegawai (
  id INT AUTO_INCREMENT PRIMARY KEY,
  golongan VARCHAR(20),
  nilai INT
);

INSERT INTO golongan_pegawai (golongan, nilai) VALUES
('Honorer', 1),
('I A', 2), ('I B', 2), ('I C', 2), ('I D', 2),
('II A', 3), ('II B', 3), ('II C', 3), ('II D', 3),
('III A', 4), ('III B', 4), ('III C', 4), ('III D', 4),
('IV A', 5), ('IV B', 5), ('IV C', 5), ('IV D', 5);

CREATE TABLE kategori_kepegawaian (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kategori VARCHAR(30),
  keterangan TEXT,
  nilai INT
);

INSERT INTO kategori_kepegawaian (kategori, keterangan, nilai) VALUES
('Medis', 'Dokter', 9),
('Paramedis', 'Bidan, Perawat', 7),
('Penunjang Medis', 'Apotik, Asisten Apotik, Laboratorium, Radiologi, Rekam Medis, Gizi', 7),
('Non Medis', 'Administrasi, Keuangan', 5),
('Rumah Tangga', 'Satpam, Logistik, Pelaksana Gizi, Laundry, Sarana Prasarana', 3);

CREATE TABLE jabatan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_jabatan VARCHAR(50),
  keterangan VARCHAR(100),
  nilai INT
);

INSERT INTO jabatan (nama_jabatan, keterangan, nilai) VALUES 
('Administrator', 'Administrator',0), ('Direktur', 'Direktur',9),
('Ka.Bid', 'Kepala Bidang',7), ('Ka.Ru', 'Kepala Ruang',5),
('Ka.Unit', 'Kepala Unit',5), ('Koord', 'Koordinator',3),
('Kary', 'Karyawan',1);

CREATE TABLE unit_kerja (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_unit VARCHAR(100) NOT NULL
);
INSERT INTO unit_kerja (nama_unit) VALUES ('-'),('IT Departemen');

CREATE TABLE agama (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_agama VARCHAR(50)
);
INSERT INTO agama (nama_agama) VALUES
('Islam'),
('Kristen Protestan'),
('Kristen Katolik'),
('Hindu'),
('Budha'),
('Kepercayaan');

CREATE TABLE pendidikan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  jenjang VARCHAR(50),
  keterangan VARCHAR(100),
  nilai INT
);

INSERT INTO pendidikan (jenjang, keterangan, nilai) VALUES
('SD', 'Setingkat SD', 1),
('SMP', 'Setingkat SMP', 2),
('SMA / SMK', 'Setingkat SMA / SMK', 3),
('D3', 'Setingkat D3', 4),
('D4', 'Setingkat D4', 5),
('S1', 'Setingkat S1', 6),
('S2', 'Setingkat S2', 7),
('S3', 'Setingkat S3', 8);

-- ===========================
-- MASTER DATA (MODUL 2)
-- ===========================

CREATE TABLE jenis_surat_keluar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_jenis VARCHAR(100) NOT NULL,
  kode VARCHAR(10) NOT NULL UNIQUE
);
INSERT INTO jenis_surat_keluar (nama_jenis, kode) VALUES
('Peraturan Direktur', 'PD'),
('Surat Keputusan', 'SK'),
('Surat Edaran', 'SE'),
('Surat Undangan', 'UND'),
('Surat Teguran', 'ST');

CREATE TABLE status_surat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_status VARCHAR(50) NOT NULL UNIQUE
);
INSERT INTO status_surat (nama_status) VALUES
('Diteruskan'),
('Diproseskan'),
('Dikirimkan'),
('Diarsipkan');

CREATE TABLE sifat_surat_masuk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_sifat VARCHAR(50) NOT NULL UNIQUE
);
INSERT INTO sifat_surat_masuk (nama_sifat) VALUES
('Biasa'),
('Rahasia'),
('Penting'),
('Segera');

-- ===========================
-- MASTER DATA (MODUL 3)
-- ===========================

CREATE TABLE lama_kerja (
  id INT AUTO_INCREMENT PRIMARY KEY,
  mulai INT,       -- tahun/bulan awal
  akhir INT,       -- tahun/bulan akhir
  rentang VARCHAR(30),
  nilai INT
);

INSERT INTO lama_kerja (mulai, akhir, rentang, nilai) VALUES
(0, 6,   '0 bulan - 6 bulan', 1),
(7, 12,  '6 bulan - 1 tahun', 2),
(12, 60, '1 tahun - 5 tahun', 3),
(61,120, '5 tahun - 10 tahun', 4),
(121,180,'10 tahun - 15 tahun', 5),
(181,240,'15 tahun - 20 tahun', 6),
(241,300,'20 tahun - 25 tahun', 7),
(301,360,'25 tahun - 30 tahun', 8),
(361,999,'> 30 tahun', 9);

-- ===========================
-- DATA PEGAWAI (MODUL 1)
-- ===========================

CREATE TABLE pegawai (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_induk_kependudukan CHAR(16),
  nomor_induk_pegawai CHAR(6) UNIQUE,
  nama_lengkap VARCHAR(100),
  tempat_lahir VARCHAR(50),
  tanggal_lahir DATE,
  jenis_kelamin_id INT,
  agama_id INT,
  alamat_tempat_tinggal TEXT,
  email VARCHAR(100),
  nomor_telepon VARCHAR(20),
  status_pernikahan_id INT,
  jumlah_anak INT,
  pendidikan_id INT, -- relasi ke master pendidikan
  pendidikan_terakhir VARCHAR(150), -- nama sekolah/universitas (manual)
  berkas_ijazah_terakhir VARCHAR(255),
  nama_bank VARCHAR(50),
  nomor_rekening VARCHAR(30),
  status_kepegawaian_id INT,
  golongan_pegawai_id INT,
  kategori_kepegawaian_id INT,
  kepesertaan_BPJS_ketenagakerjaan ENUM('Ya','Belum'),
  kepesertaan_BPJS_kesehatan ENUM('Ya','Belum'),
  status_BPJS_kesehatan ENUM('Sendiri','Keluarga','Keluarga dan Tambahan'),
  unit_id INT NULL,
  tanggal_masuk DATE NOT NULL, -- penentu lama kerja
  tanggal_masuk_awal DATE NOT NULL, -- histori pertama kali masuk
  tanggal_masuk_aktif DATE DEFAULT NULL, -- tanggal masuk terakhir (re-hire)
  photo VARCHAR(255),
  FOREIGN KEY (jenis_kelamin_id) REFERENCES jenis_kelamin(id),
  FOREIGN KEY (agama_id) REFERENCES agama(id),
  FOREIGN KEY (status_pernikahan_id) REFERENCES status_pernikahan(id),
  FOREIGN KEY (status_kepegawaian_id) REFERENCES status_kepegawaian(id),
  FOREIGN KEY (golongan_pegawai_id) REFERENCES golongan_pegawai(id),
  FOREIGN KEY (kategori_kepegawaian_id) REFERENCES kategori_kepegawaian(id),
  FOREIGN KEY (unit_id) REFERENCES unit_kerja(id),
  FOREIGN KEY (pendidikan_id) REFERENCES pendidikan(id)
);
INSERT INTO pegawai (
INSERT INTO pegawai (
  nomor_induk_kependudukan,
  nomor_induk_pegawai,
  nama_lengkap,
  tempat_lahir,
  tanggal_lahir,
  jenis_kelamin_id,
  agama_id,
  alamat_tempat_tinggal,
  email,
  nomor_telepon,
  status_pernikahan_id,
  jumlah_anak,
  pendidikan_id,
  pendidikan_terakhir,
  berkas_ijazah_terakhir,
  nama_bank,
  nomor_rekening,
  status_kepegawaian_id,
  golongan_pegawai_id,
  kategori_kepegawaian_id,
  kepesertaan_BPJS_ketenagakerjaan,
  kepesertaan_BPJS_kesehatan,
  status_BPJS_kesehatan,
  unit_id,
  tanggal_masuk,
  tanggal_masuk_awal,
  tanggal_masuk_aktif,
  photo
) VALUES (
  '0000000000000000', -- dummy NIK
  '000000',           -- NIP admin
  'Administrator',
  'Malang',
  '1970-01-01',
  1,                  -- jenis_kelamin_id
  1,                  -- agama_id
  'Alamat kantor pusat',
  'admin@example.com',
  '081234567890',
  1,                  -- status_pernikahan_id
  0,
  1,                  -- pendidikan_id
  'Universitas Dummy',
  NULL,
  'Bank Mandiri',
  '0000000000',
  1,                  -- status_kepegawaian_id
  1,                  -- golongan_pegawai_id
  1,                  -- kategori_kepegawaian_id
  'Belum',
  'Belum',
  'Sendiri',
  (SELECT id FROM unit_kerja WHERE nama_unit = 'IT Departemen' LIMIT 1),
  '2000-01-01',       -- tanggal_masuk (lama)
  '2000-01-01',       -- tanggal_masuk_awal
  NULL,               -- tanggal_masuk_aktif (belum pernah re-hire)
  NULL
);

-- ===========================
-- BPJS & SURAT IJIN
-- ===========================

-- Kepesertaan BPJS
CREATE TABLE kepesertaan_BPJS (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_induk_pegawai CHAR(6),
  nomor_kartu_BPJS_ketenagakerjaan CHAR(11),
  nomor_kartu_BPJS_kesehatan CHAR(13),
  FOREIGN KEY (nomor_induk_pegawai) REFERENCES pegawai(nomor_induk_pegawai)
);

-- Kepesertaan BPJS Keluarga
CREATE TABLE kepesertaan_BPJS_keluarga (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_induk_pegawai CHAR(6),
  nomor_kartu_BPJS_kesehatan_suami_istri CHAR(13),
  nama_lengkap_suami_istri VARCHAR(100),
  status_keaktifan_BPJS_kesehatan_suami_istri ENUM('Aktif', 'Non Aktif'),
  nomor_kartu_BPJS_kesehatan_anak_I CHAR(13),
  nama_lengkap_anak_I VARCHAR(100),
  status_keaktifan_BPJS_kesehatan_anak_I ENUM('Aktif', 'Non Aktif'),
  nomor_kartu_BPJS_kesehatan_anak_II CHAR(13),
  nama_lengkap_anak_II VARCHAR(100),
  status_keaktifan_BPJS_kesehatan_anak_II ENUM('Aktif', 'Non Aktif'),
  nomor_kartu_BPJS_kesehatan_anak_III CHAR(13),
  nama_lengkap_anak_III VARCHAR(100),
  status_keaktifan_BPJS_kesehatan_anak_III ENUM('Aktif', 'Non Aktif'),
  FOREIGN KEY (nomor_induk_pegawai) REFERENCES pegawai(nomor_induk_pegawai)
);

-- Kepesertaan BPJS Tambahan
CREATE TABLE kepesertaan_BPJS_tambahan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_induk_pegawai CHAR(6),
  nomor_kartu_BPJS_kesehatan_tambahan_I CHAR(13),
  nama_lengkap_tambahan_I VARCHAR(100),
  status_keaktifan_BPJS_kesehatan_tambahan_I ENUM('Aktif', 'Non Aktif'),
  nomor_kartu_BPJS_kesehatan_tambahan_II CHAR(13),
  nama_lengkap_tambahan_II VARCHAR(100),
  status_keaktifan_BPJS_kesehatan_tambahan_II ENUM('Aktif', 'Non Aktif'),
  nomor_kartu_BPJS_kesehatan_tambahan_III CHAR(13),
  nama_lengkap_tambahan_III VARCHAR(100),
  status_keaktifan_BPJS_kesehatan_tambahan_III ENUM('Aktif', 'Non Aktif'),
  nomor_kartu_BPJS_kesehatan_tambahan_IV CHAR(13),
  nama_lengkap_tambahan_IV VARCHAR(100),
  status_keaktifan_BPJS_kesehatan_tambahan_IV ENUM('Aktif', 'Non Aktif'),
  FOREIGN KEY (nomor_induk_pegawai) REFERENCES pegawai(nomor_induk_pegawai)
);

-- Surat Ijin STR
CREATE TABLE surat_ijin_STR (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_induk_pegawai CHAR(6),
  nomor_STR VARCHAR(50),
  berkas_STR VARCHAR(255),
  FOREIGN KEY (nomor_induk_pegawai) REFERENCES pegawai(nomor_induk_pegawai)
);

-- Surat Ijin SIP
CREATE TABLE surat_ijin_SIP (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_induk_pegawai CHAR(6),
  nomor_SIP VARCHAR(50),
  mulai_berlaku_SIP DATE,
  berakhir_SIP DATE,
  berkas_SIP VARCHAR(255),
  masa_aktif_SIP INT,
  FOREIGN KEY (nomor_induk_pegawai) REFERENCES pegawai(nomor_induk_pegawai)
);

-- Surat Keputusan
CREATE TABLE surat_keputusan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_induk_pegawai CHAR(6),
  nomor_surat_keputusan VARCHAR(50),
  mulai_berlaku_surat_keputusan DATE,
  berakhir_surat_keputusan DATE,
  berkas_surat_keputusan VARCHAR(255),
  masa_aktif_surat_keputusan INT,
  FOREIGN KEY (nomor_induk_pegawai) REFERENCES pegawai(nomor_induk_pegawai)
);

-- Status Pegawai
CREATE TABLE status_pegawai (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_induk_pegawai VARCHAR(20) NOT NULL,
  status_keaktifan ENUM('Aktif', 'Non Aktif') NOT NULL DEFAULT 'Aktif',
  alasan_non_aktif TEXT DEFAULT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (nomor_induk_pegawai) REFERENCES pegawai(nomor_induk_pegawai)
);

-- ===========================
-- TABEL SERTIFIKAT
-- ===========================

CREATE TABLE sertifikat_pegawai (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_induk_pegawai VARCHAR(6),
  jenis ENUM('workshop','seminar','pendidikan dan pelatihan'),
  mode ENUM('Daring','Luring'),
  pemberi_sertifikat VARCHAR(100),
  nomor_sertifikat VARCHAR(50),
  tanggal_mulai_pelaksanaan DATE,
  tanggal_akhir_pelaksanaan DATE,
  berkas VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===========================
-- DATA CUTI
-- ===========================

CREATE TABLE cuti_pegawai (
    id_cuti INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    lama_cuti_hari INT GENERATED ALWAYS AS (DATEDIFF(tanggal_selesai, tanggal_mulai) + 1) STORED,
    lama_cuti_bulan DECIMAL(5,2) GENERATED ALWAYS AS (ROUND((DATEDIFF(tanggal_selesai, tanggal_mulai) + 1) / 30, 2)) STORED,
    jenis_cuti VARCHAR(50) NOT NULL,
    alasan TEXT,
    status_pengajuan ENUM('Diajukan','Disetujui','Ditolak') DEFAULT 'Diajukan',
    disetujui_oleh INT NULL,
    tanggal_persetujuan DATE NULL,
    CONSTRAINT fk_cuti_pegawai FOREIGN KEY (pegawai_id) REFERENCES pegawai(id)
);

-- ===========================
-- QRCODE
-- ===========================

CREATE TABLE pegawai_qrcode (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(50) NOT NULL,
    qr_file VARCHAR(255) NOT NULL,
    qr_generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_nip (nip)
);

-- ===========================
-- DATA SURAT (MODUL 2)
-- ===========================

-- Surat Keluar
CREATE TABLE surat_keluar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_agenda VARCHAR(50) NOT NULL UNIQUE,
  jenis_surat_id INT NOT NULL,
  nomor_surat VARCHAR(80) NOT NULL,
  tanggal_surat DATE NOT NULL,
  tujuan_surat VARCHAR(255) NOT NULL,
  status_surat_id INT NOT NULL,
  berkas_surat VARCHAR(255),
  tanggal_diarsipkan DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (jenis_surat_id) REFERENCES jenis_surat_keluar(id),
  FOREIGN KEY (status_surat_id) REFERENCES status_surat(id)
);

-- Surat Masuk
CREATE TABLE surat_masuk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_agenda VARCHAR(50) NOT NULL UNIQUE,
  nomor_surat VARCHAR(80) NOT NULL,
  tanggal_surat DATE NOT NULL,
  tanggal_terima DATE NOT NULL,
  asal_surat VARCHAR(255) NOT NULL,
  perihal_surat VARCHAR(255) NOT NULL,
  sifat_surat_id INT NOT NULL,
  jumlah_halaman VARCHAR(50),
  diteruskan_kepada_jabatan_id INT NOT NULL,
  isi_disposisi TEXT,
  disposisi_jabatan_id  VARCHAR(255),
  disposisi_unit_id  VARCHAR(255),
  berkas_surat VARCHAR(255),
  status_surat_id INT NOT NULL,
  tanggal_diarsipkan DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (sifat_surat_id) REFERENCES sifat_surat_masuk(id),
  FOREIGN KEY (status_surat_id) REFERENCES status_surat(id),
  FOREIGN KEY (diteruskan_kepada_jabatan_id) REFERENCES jabatan(id)
);

-- Reset Counter per Tahun
CREATE TABLE nomor_agenda_counter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jenis ENUM('SK','SM') NOT NULL,
    tahun INT NOT NULL,
    urut INT NOT NULL DEFAULT 0,
    CONSTRAINT uq_counter UNIQUE (jenis, tahun)
);
INSERT INTO nomor_agenda_counter (jenis, tahun, urut)
VALUES ('SK', YEAR(CURDATE()), 0), ('SM', YEAR(CURDATE()), 0);

-- Indeks tambahan
CREATE INDEX idx_sk_tanggal_surat ON surat_keluar (tanggal_surat);
CREATE INDEX idx_sk_status ON surat_keluar (status_surat_id);
CREATE INDEX idx_sk_jenis ON surat_keluar (jenis_surat_id);

CREATE INDEX idx_sm_tanggal_terima ON surat_masuk (tanggal_terima);
CREATE INDEX idx_sm_status ON surat_masuk (status_surat_id);
CREATE INDEX idx_sm_sifat ON surat_masuk (sifat_surat_id);

-- ===========================
-- SURAT INTERNAL (MODUL 2)
-- ===========================

CREATE TABLE surat_internal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_surat VARCHAR(100) NOT NULL,
  tanggal_surat DATE NOT NULL,
  asal_surat INT NOT NULL, -- FK ke unit_kerja.id
  perihal TEXT NOT NULL DEFAULT 'Dengan hormat, mohon perhatian Bapak/Ibu Direktur untuk membaca Surat Internal yang telah kami sampaikan serta memberikan disposisi sesuai arahan',
  ditujukan_kepada INT NOT NULL, -- FK ke jabatan.id (misalnya Direktur)
  jawaban TEXT,
  berkas VARCHAR(255), -- nama file PDF
  status ENUM('belum dibaca','sudah dibaca') DEFAULT 'belum dibaca',
  tanggal_diarsipkan DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===========================
-- MASTER INDEX REMUNERASI (MODUL 3)
-- ===========================

CREATE TABLE master_index_remunerasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    golongan_pegawai_id INT NOT NULL,
    jabatan_id INT NOT NULL,
    kategori_kepegawaian_id INT NOT NULL,
    pendidikan_id INT NOT NULL,
    status_kepegawaian_id INT NOT NULL,
    status_pernikahan_id INT NOT NULL,
    lama_kerja_id INT NOT NULL,
    nilai_golongan DECIMAL(10,2) DEFAULT 0,
    nilai_jabatan DECIMAL(10,2) DEFAULT 0,
    nilai_kategori DECIMAL(10,2) DEFAULT 0,
    nilai_pendidikan DECIMAL(10,2) DEFAULT 0,
    nilai_status_kepegawaian DECIMAL(10,2) DEFAULT 0,
    nilai_status_pernikahan DECIMAL(10,2) DEFAULT 0,
    nilai_lama_kerja DECIMAL(10,2) DEFAULT 0,
    total_index DECIMAL(10,2) DEFAULT 0,
    periode VARCHAR(7) NOT NULL, -- format YYYY-MM
    UNIQUE KEY (pegawai_id)
);

-- ===========================
-- TIKET REMUNERASI (MODUL 3)
-- ===========================

CREATE TABLE remunerasi_tiket (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tahun INT NOT NULL,                          
    mode ENUM('bulanan','triwulan','semester','tahunan') NOT NULL,
    periode VARCHAR(7) NOT NULL,                 
    total_nominal DECIMAL(15,2) NOT NULL,        
    status ENUM('aktif','batal') DEFAULT 'aktif',          -- kondisi tiket
    lifecycle ENUM('draft','processed','finalized') DEFAULT 'draft', -- proses tiket
    finalized_at DATETIME NULL DEFAULT NULL,               -- waktu tiket difinalisasi
    created_by INT NOT NULL,                     
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tiket_user FOREIGN KEY (created_by)
        REFERENCES user(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- ===========================
-- REMUNERASI HASIL (MODUL 3)
-- ===========================

CREATE TABLE remunerasi_hasil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,                   -- relasi ke pegawai
    tiket_id INT NOT NULL,                     -- relasi ke tiket remunerasi
    tahun INT NOT NULL,                        -- tahun remunerasi
    mode ENUM('bulanan','triwulan','semester','tahunan') NOT NULL,
    periode_nilai VARCHAR(7) NOT NULL,
    poin_awal DECIMAL(10,2) NOT NULL,          -- total index dari master index
    cuti INT DEFAULT 0,                        -- jumlah bulan cuti
    real_kerja INT NOT NULL,                   -- periode_nilai - cuti
    point_akhir DECIMAL(10,2) NOT NULL,        -- (poin_awal / periode_nilai) * real_kerja
    remunerasi_gross DECIMAL(15,2) NOT NULL,   -- hasil perhitungan sebelum potongan
    remunerasi_net DECIMAL(15,2) NOT NULL,     -- diterima (gross - potongan)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (pegawai_id, tiket_id),         -- unik per pegawai per tiket
    CONSTRAINT fk_remunerasi_pegawai FOREIGN KEY (pegawai_id)
        REFERENCES pegawai(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_remunerasi_tiket FOREIGN KEY (tiket_id)
        REFERENCES remunerasi_tiket(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- ===========================
-- TABEL AKSES
-- ===========================

CREATE TABLE user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE,
  password VARCHAR(255),
  nomor_induk_pegawai VARCHAR(6),
  jabatan_id INT,
  FOREIGN KEY (jabatan_id) REFERENCES jabatan(id)
);

INSERT INTO user (username, password, nomor_induk_pegawai, jabatan_id)
VALUES ('admin',
  '$2y$10$WJ7kJAeYr/aPgAY5VofRFOwxtV9vDvPybuzgERYNVxzUPDNqzfUEu',
  '000000', 1);

CREATE TABLE hak_akses_user (
  username VARCHAR(50) PRIMARY KEY,
  nomor_induk_pegawai CHAR(6),
  hak_akses ENUM('administrator','admin','admin2','admin3','user') DEFAULT 'user',
  FOREIGN KEY (username) REFERENCES user(username),
  FOREIGN KEY (nomor_induk_pegawai) REFERENCES pegawai(nomor_induk_pegawai)
);

INSERT INTO hak_akses_user (username, nomor_induk_pegawai, hak_akses)
VALUES ('admin', '000000', 'administrator');

-- ===========================
-- VERSI APLIKASI
-- ===========================

CREATE TABLE app_version (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(10) NOT NULL,
    release_date DATE NOT NULL,
    description TEXT
);

INSERT INTO app_version (version, release_date, description)
VALUES 
('1.0.0', '2025-11-20', 'Rilis awal aplikasi Modul 1 : Manajemen Kepegawaian'),
('1.1.0', '2025-12-06', '- Penambahan Modul 2: Manajemen Surat
- Perbaikan input tanggal'),('1.2.0', '2025-12-08', '- Perbaikan redirect URL agar tidak terjadi duplikasi `index.php?url`
- Penambahan guard akses di `edit_data_saya` (administrator ditolak, user hanya bisa edit miliknya)
- Perbaikan handling upload foto/dokumen (hidden input + conditional update) supaya file lama tidak hilang
- Penghapusan field foto duplikat di form edit
- Konsistensi form action dengan `input.php`
- Modul 2 masuk tahap pengecekan awal');
