<?php
// app/helpers/QrHelper.php
require_once __DIR__ . '/../libraries/phpqrcode/qrlib.php';

function generatePegawaiQr(string $nip, string $data, string $format = 'png'): string
{
    // folder output
    $folder = __DIR__ . '/../../public/uploads/qrcode/pegawai/';
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    // nama file: qr_NIP.png (atau svg)
    $format = strtolower($format);
    $ext = ($format === 'svg') ? 'svg' : 'png'; // phpqrcode klasik umumnya png (SVG butuh implementasi lain)
    $filename = "qr_{$nip}.{$ext}";
    $filepath = $folder . $filename;

    // level koreksi & ukuran
    $eccLevel = QR_ECLEVEL_L; // L, M, Q, H
    $matrixSize = 4;          // 1–10 (semakin besar, semakin tajam)

    // generate
    QRcode::png($data, $filepath, $eccLevel, $matrixSize);

    return $filename; // kembalikan nama file untuk disimpan di DB/ditampilkan
}

function getPegawaiQrUrl(string $nip): ?string
{
    $relative = "/uploads/qrcode/pegawai/qr_{$nip}.png";
    $absolute = __DIR__ . "/../../public{$relative}";
    if (!file_exists($absolute)) return null;
    return BASE_URL . $relative;
}