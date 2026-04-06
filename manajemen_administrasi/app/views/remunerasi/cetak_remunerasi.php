<?php
function purl($path){
  return '/manajemen_administrasi/public/uploads/' . $path;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Remunerasi</title>
<style>
  @page { margin: 15mm; }
  body { font-family: Arial, sans-serif; font-size: 12px; }
  .garis-header { border-bottom: 2px solid #000; margin: 5px 0 15px; }
  .judul { font-size: 18px; font-weight: bold; text-align: center; margin: 10px 0 15px; }
  .tabel-bordered { border-collapse: collapse; width: 100%; margin-bottom:20px; }
  .tabel-bordered th, .tabel-bordered td { border: 1px solid #000; padding: 5px; }
  .tabel-bordered .angka { text-align: right; } /* kolom angka rata kanan */
</style>
</head>
<body onload="window.print()"><!-- inilah kuncinya -->

<!-- HEADER -->
<table style="margin-bottom:5px;">
  <tr>
    <td style="width:80px; vertical-align:top;">
      <?php if (!empty($institusi['logo'])): ?>
        <img src="<?= purl('institusi_logo/' . $institusi['logo']) ?>" alt="Logo" style="height:60px;">
      <?php endif; ?>
    </td>
    <td style="padding-left:10px;">
      <div style="font-size:18px; font-weight:bold; text-transform:uppercase;">
        <?= $institusi['nama_institusi'] ?? '-' ?>
      </div>
      <div style="font-size:16px; font-weight:bold; text-transform:uppercase;">
        <?= $institusi['sub_institusi'] ?? '-' ?>
      </div>
      <div style="font-size:14px;">
        <?= $institusi['alamat'] ?? '-' ?> - <?= $institusi['telepon'] ?? '-' ?>
      </div>
    </td>
  </tr>
</table>
<div class="garis-header"></div>

<!-- JUDUL -->
<div class="judul">
  REMUNERASI <?= strtoupper($tiket['mode']) ?><br>
  Periode <?= htmlspecialchars($tiket['periode']) ?>
</div>

<!-- ISI TABEL -->
<?php
$currentBank = null;
$totalBank   = 0;
$totalAll    = 0;
$no = 1;

foreach ($hasil as $row) {
    if ($currentBank !== $row['nama_bank']) {
        if ($currentBank !== null) {
            echo "<tr><td colspan='3' align='right'><b>Total $currentBank</b></td>
                      <td class='angka'><b>".number_format($totalBank,0,',','.')."</b></td></tr>";
            echo "</table>";
            $totalBank = 0;
        }
        echo "<h4>Bank: ".$row['nama_bank']."</h4>";
        echo "<table class='tabel-bordered'>
                <tr><th>NO</th><th>NIP</th><th>NAMA PEGAWAI</th><th class='angka'>JUMLAH</th></tr>";
        $currentBank = $row['nama_bank'];
        $no = 1;
    }

    if ($row['remunerasi_net'] > 0) {
        echo "<tr>
                <td>".$no++."</td>
                <td>".$row['nip']."</td>
                <td>".$row['nama']."</td>
                <td class='angka'>".number_format($row['remunerasi_net'],0,',','.')."</td>
              </tr>";

        $totalBank += $row['remunerasi_net'];
        $totalAll  += $row['remunerasi_net'];
    }

}

if ($currentBank !== null) {
    echo "<tr><td colspan='3' align='right'><b>Total $currentBank</b></td>
              <td class='angka'><b>".number_format($totalBank,0,',','.')."</b></td></tr>";
    echo "</table>";
}
?>

<!-- TOTAL AKHIR -->
<table class="tabel-bordered" style="margin-top:10px; width:100%;">
  <tr>
    <td colspan="3" align="right" style="font-size:16px; font-weight:bold; border-top:2px solid #000;">
      TOTAL AKHIR
    </td>
    <td class="angka" style="font-size:16px; font-weight:bold; border-top:2px solid #000;">
      <?= number_format($totalAll,0,',','.') ?>
    </td>
  </tr>
</table>

<!-- FOOTER -->
<div style="margin-top:40px; text-align:right;">
  Malang, <?= date('d-m-Y') ?><br><br>
  Petugas,<br>
  <?php if (!empty($qrcode['qr_file'])): ?>
    <img src="<?= purl('qrcode/pegawai/' . $qrcode['qr_file']) ?>" alt="QR Code" style="height:80px;"><br>
  <?php endif; ?>
  <?= htmlspecialchars($tiket['dibuat_oleh']) ?>
</div>

</body>
</html>
