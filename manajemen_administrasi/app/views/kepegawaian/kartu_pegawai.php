<?php
$pegawai   = $data['pegawai'];
$institusi = $data['institusi'];
$jbt       = $data['jbt'];

function purl($path){
  return BASE_URL . '/public/uploads/' . $path;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kartu Pegawai</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
    }

    .card {
      width: 6cm;
      height: 9cm;
      border: 2px solid #004080;
      background: #fff;
      box-shadow: 0 0 5px rgba(0,0,0,0.2);
      padding: 10px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .logo {
      text-align: center;
      margin-bottom: 5px;
    }

    .logo img {
      height: 40px;
    }

    .header {
      text-align: center;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
      color: #004080;
      margin-bottom: 5px;
    }

    .photo {
      text-align: center;
    }

    .photo img {
      height: 80px;
      border-radius: 6px;
      border: 1px solid #333;
    }

    .name {
      text-align: center;
      font-size: 14px;
      font-weight: bold;
      margin-top: 8px;
      color: #000;
    }

    .position {
      text-align: center;
      font-size: 12px;
      color: #555;
      margin-bottom: 8px;
    }

    .info {
      font-size: 10px;
      line-height: 1.4;
      padding: 0 5px;
      text-align: left;
    }

    .info div {
      display: flex;
      justify-content: space-between;
      margin-bottom: 4px;
    }

    .info div strong {
      width: 60px;
      display: inline-block;
    }

    .footer {
      font-size: 9px;
      text-align: center;
      color: #666;
      border-top: 1px solid #ccc;
      padding-top: 4px;
    }

    @media print {
      body { margin: 0; }
    }
  </style>
</head>
<body>

<div class="card">
  <div class="logo">
    <?php if (!empty($institusi['logo'])): ?>
      <img src="<?= purl("institusi_logo/" . $institusi['logo']) ?>" alt="Logo">
    <?php endif; ?>
  </div>

  <div class="header">
    <?= strtoupper($institusi['nama_institusi']) ?><br>
    <?= strtoupper($institusi['sub_institusi']) ?>
  </div>

  <div class="photo">
    <?php if (!empty($pegawai['photo'])): ?>
      <img src="<?= purl("pegawai/foto/" . $pegawai['photo']) ?>" alt="Foto">
    <?php else: ?>
      <div style="height:80px;line-height:80px;">No Photo</div>
    <?php endif; ?>
  </div>

  <div class="name"><?= strtoupper($pegawai['nama_lengkap']) ?></div>
  <div class="position"><?= strtoupper($jbt['keterangan'] ?? 'KARYAWAN') ?></div>

  <div class="info" style="font-size:10px; line-height:1.6; font-family:Arial, sans-serif;">
    <pre style="margin:0;">
  NIP       : <?= $pegawai['nomor_induk_pegawai'] ?>

  Email     : <?= $pegawai['email'] ?>

  Telepon   : <?= $pegawai['nomor_telepon'] ?>

  Unit      : <?= !empty($pegawai['unit_nama']) ? $pegawai['unit_nama'] : '-' ?>
    </pre>
  </div>


  <div class="footer">
    <?= $institusi['alamat'] ?>
  </div>
</div>

<script>
  window.onload = function() { window.print(); }
</script>
</body>
</html>