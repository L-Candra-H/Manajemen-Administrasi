<?php

class PegawaiQrModel extends Model
{
    protected $table = 'pegawai_qrcode';

    // simpan atau update QR file untuk pegawai
    public static function insertOrUpdateQr(string $nip, string $filename): bool
    {
        $db = new Database();
        $sql = "INSERT INTO pegawai_qrcode (nip, qr_file, qr_generated_at)
                VALUES (:nip, :file, NOW())
                ON DUPLICATE KEY UPDATE qr_file = VALUES(qr_file), qr_generated_at = NOW()";
        $db->prepareQuery($sql);
        $db->bind(':nip', $nip);
        $db->bind(':file', $filename);
        return $db->execute();
    }

    // ambil data QR berdasarkan NIP
    public static function getQrByNip(string $nip): ?array
    {
        $db = new Database();
        $sql = "SELECT q.*, p.nama_lengkap, s.status_keaktifan
                FROM pegawai_qrcode q
                JOIN pegawai p ON q.nip = p.nomor_induk_pegawai
                JOIN status_pegawai s ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
                WHERE q.nip = :nip AND s.status_keaktifan = 'Aktif'";
        $db->prepareQuery($sql);
        $db->bind(':nip', $nip);
        $row = $db->single();
        return $row ?: null;
    }

    // ambil daftar QR dengan pagination
    public static function getDaftarQr($limit, $offset, $search = '')
    {
        $db = new Database();
        $sql = "SELECT q.*, p.nama_lengkap, s.status_keaktifan
                FROM pegawai_qrcode q
                JOIN pegawai p ON q.nip = p.nomor_induk_pegawai
                JOIN status_pegawai s ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
                WHERE s.status_keaktifan = 'Aktif'";

        if (!empty($search)) {
            $sql .= " AND (p.nama_lengkap LIKE :search OR p.nomor_induk_pegawai LIKE :search)";
        }

        $sql .= " ORDER BY p.nomor_induk_pegawai ASC LIMIT :limit OFFSET :offset";
        $db->prepareQuery($sql);

        if (!empty($search)) {
            $db->bind(':search', "%$search%");
        }
        $db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $db->bind(':offset', (int)$offset, PDO::PARAM_INT);

        return $db->resultSet();
    }

    // hitung total QR untuk pagination
    public static function countQr($search = '')
    {
        $db = new Database();
        $sql = "SELECT COUNT(*) as total
                FROM pegawai_qrcode q
                JOIN pegawai p ON q.nip = p.nomor_induk_pegawai
                JOIN status_pegawai s ON p.nomor_induk_pegawai = s.nomor_induk_pegawai
                WHERE s.status_keaktifan = 'Aktif'";

        if (!empty($search)) {
            $sql .= " AND (p.nama_lengkap LIKE :search OR p.nomor_induk_pegawai LIKE :search)";
        }

        $db->prepareQuery($sql);
        if (!empty($search)) {
            $db->bind(':search', "%$search%");
        }

        $row = $db->single();
        return $row ? (int)$row['total'] : 0;
    }

}