<?php
function uploadDokumen($file, $jenis, $nip, $tanggal_akhir = null)
{
  if (!isset($file['tmp_name']) || $file['error'] !== 0) return null;

  $allowedExt = ['pdf', 'png', 'jpg', 'jpeg'];
  $validJenis = ['foto', 'ijazah', 'str', 'sip', 'sk'];

  if (!in_array($jenis, $validJenis)) {
    throw new Exception("Jenis folder tidak valid: $jenis");
  }

  $originalName = preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', basename($file['name']));
  $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
  if (!in_array($ext, $allowedExt)) {
    throw new Exception("Format file tidak diizinkan: $ext");
  }

  $folder = __DIR__ . "/../public/uploads/pegawai/$jenis";
  if (!is_dir($folder)) mkdir($folder, 0777, true);

  // === Penamaan file ===
  switch ($jenis) {
    case 'foto':
    case 'ijazah':
    case 'str':
      // Hapus versi lama
      foreach (glob("$folder/{$jenis}_{$nip}.*") as $old) {
        if (file_exists($old)) unlink($old);
      }
      $filename = "{$jenis}_{$nip}.{$ext}";
      break;

    case 'sip':
    case 'sk':
      if (!$tanggal_akhir) throw new Exception("Tanggal akhir wajib untuk jenis: $jenis");
      $stamp = date('Ymd', strtotime($tanggal_akhir));
      $filename = "{$jenis}_{$nip}_{$stamp}.{$ext}";
      break;
  }

  $targetPath = "$folder/$filename";
  if (!is_uploaded_file($file['tmp_name']) || !move_uploaded_file($file['tmp_name'], $targetPath)) {
    throw new Exception("Gagal mengunggah file: $filename");
  }

  return $filename;

  function uploadSurat($file, $jenis, $tanggalSurat, $urut)
  {
      if (!isset($file['tmp_name']) || $file['error'] !== 0) return null;

      $allowedExt = ['pdf'];
      $validJenis = ['surat_keluar', 'surat_masuk'];

      if (!in_array($jenis, $validJenis)) {
          throw new Exception("Jenis surat tidak valid: $jenis");
      }

      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, $allowedExt)) {
          throw new Exception("Format file tidak diizinkan: $ext");
      }

      $tahun   = date('Y', strtotime($tanggalSurat));
      $tanggal = date('Ymd', strtotime($tanggalSurat));

      // Folder upload
      $folder = __DIR__ . "/../public/uploads/dokumen/$jenis/$tahun";
      if (!is_dir($folder)) mkdir($folder, 0777, true);

      // === Penamaan file ===
      if ($jenis === 'surat_keluar') {
          $filename = "sk_{$tanggal}-{$urut}.{$ext}";
      } else {
          $filename = "sm_{$tanggal}-{$urut}.{$ext}";
      }

      $targetPath = "$folder/$filename";

      if (!is_uploaded_file($file['tmp_name']) || !move_uploaded_file($file['tmp_name'], $targetPath)) {
          throw new Exception("Gagal mengunggah file: $filename");
      }

      // Path relatif untuk simpan ke DB
      return "dokumen/$jenis/$tahun/$filename";
  }

}