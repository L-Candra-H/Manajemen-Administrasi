<?php

class CutiModel extends Model
{
    protected $table = 'cuti_pegawai';

    // Ambil semua data cuti
    public function getAll()
    {
        $stmt = $this->db->prepare("
            SELECT c.id_cuti, c.pegawai_id, p.nomor_induk_pegawai, p.nama_lengkap,
                   c.tanggal_mulai, c.tanggal_selesai,
                   c.lama_cuti_hari, c.lama_cuti_bulan,
                   c.jenis_cuti, c.alasan,
                   c.status_pengajuan, c.disetujui_oleh, c.tanggal_persetujuan
            FROM {$this->table} c
            LEFT JOIN pegawai p ON c.pegawai_id = p.id
            ORDER BY c.id_cuti DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cari cuti berdasarkan ID
    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT c.id_cuti, c.pegawai_id, p.nomor_induk_pegawai, p.nama_lengkap,
                   c.tanggal_mulai, c.tanggal_selesai,
                   c.lama_cuti_hari, c.lama_cuti_bulan,
                   c.jenis_cuti, c.alasan,
                   c.status_pengajuan, c.disetujui_oleh, c.tanggal_persetujuan
            FROM {$this->table} c
            LEFT JOIN pegawai p ON c.pegawai_id = p.id
            WHERE c.id_cuti = :id
            LIMIT 1
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Cari cuti berdasarkan pegawai_id
    public function findByPegawai($pegawaiId)
    {
        $stmt = $this->db->prepare("
            SELECT c.id_cuti, c.pegawai_id, p.nomor_induk_pegawai, p.nama_lengkap,
                   c.tanggal_mulai, c.tanggal_selesai,
                   c.lama_cuti_hari, c.lama_cuti_bulan,
                   c.jenis_cuti, c.alasan,
                   c.status_pengajuan, c.disetujui_oleh, c.tanggal_persetujuan
            FROM {$this->table} c
            LEFT JOIN pegawai p ON c.pegawai_id = p.id
            WHERE c.pegawai_id = :pegawaiId
            ORDER BY c.tanggal_mulai DESC
        ");
        $stmt->execute([':pegawaiId' => $pegawaiId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil data cuti dengan pagination + optional search
    public function getPaginated($limit, $offset, $search = '')
    {
        $sql = "
            SELECT c.id_cuti, c.pegawai_id, p.nomor_induk_pegawai, p.nama_lengkap,
                   c.tanggal_mulai, c.tanggal_selesai,
                   c.lama_cuti_hari, c.lama_cuti_bulan,
                   c.jenis_cuti, c.alasan,
                   c.status_pengajuan, c.disetujui_oleh, c.tanggal_persetujuan
            FROM {$this->table} c
            LEFT JOIN pegawai p ON c.pegawai_id = p.id
            WHERE (c.jenis_cuti LIKE :search
               OR c.alasan LIKE :search
               OR p.nama_lengkap LIKE :search
               OR p.nomor_induk_pegawai LIKE :search)
            ORDER BY c.id_cuti DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hitung total record untuk pagination
    public function countAll($search = '')
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM {$this->table} c
            LEFT JOIN pegawai p ON c.pegawai_id = p.id
            WHERE (c.jenis_cuti LIKE :search
               OR c.alasan LIKE :search
               OR p.nama_lengkap LIKE :search
               OR p.nomor_induk_pegawai LIKE :search)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    // Update data cuti (edit pengajuan)
    public function update($data)
    {
        $sql = "UPDATE {$this->table} SET
                    pegawai_id      = :pegawai_id,
                    tanggal_mulai   = :tanggal_mulai,
                    tanggal_selesai = :tanggal_selesai,
                    jenis_cuti      = :jenis_cuti,
                    alasan          = :alasan
                WHERE id_cuti = :id_cuti";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    // Simpan data cuti baru
    public function insert($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    pegawai_id,
                    tanggal_mulai,
                    tanggal_selesai,
                    jenis_cuti,
                    alasan,
                    status_pengajuan
                ) VALUES (
                    :pegawai_id,
                    :tanggal_mulai,
                    :tanggal_selesai,
                    :jenis_cuti,
                    :alasan,
                    'Diajukan'
                )";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    // Update status pengajuan (approve/reject)
    public function updateStatus($data)
    {
        $sql = "UPDATE {$this->table} SET
                    status_pengajuan    = :status_pengajuan,
                    disetujui_oleh      = :disetujui_oleh,
                    tanggal_persetujuan = :tanggal_persetujuan
                WHERE id_cuti = :id_cuti";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
}
