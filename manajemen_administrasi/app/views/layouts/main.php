<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?? 'Manajemen Administrasi' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/plugins/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
</head>

<?php
$bodyClass = ($layout ?? '') === 'login-page' ? 'login-page' : 'sidebar-mini layout-fixed';

// Tampilkan background hanya jika layout adalah dashboard
$bodyStyle = ($layout ?? '') === 'dashboard'
    ? "style=\"background-image: url('" . BASE_URL . "/public/assets/img/bg.jpg'); background-size: cover; background-position: center; background-attachment: fixed;\""
    : "";
?>

<body class="hold-transition <?= $bodyClass ?>" <?= $bodyStyle ?>>
  <div class="wrapper">
    <?php if (($layout ?? '') !== 'login-page'): ?>
      <?php include __DIR__ . '/navbar.php'; ?>
      <?php include __DIR__ . '/sidebar.php'; ?>
      <div class="content-wrapper" style="background-color: transparent; padding: 20px;">
        <?= $content ?>
      </div>

      <?php
      require_once __DIR__ . '/../../helpers/version_helper.php';
      $ver = getAppVersion();
      ?>
      <footer class="main-footer text-center text-muted">
        <strong>&copy; <?= date('Y') ?> Manajemen Administrasi</strong><br>
        <small>
          Versi Aplikasi: <?= htmlspecialchars($ver['version']) ?>
          (<?= htmlspecialchars($ver['release_date']) ?>)
        </small>
      </footer>
    <?php else: ?>
      <?= $content ?>
    <?php endif; ?>
  </div>

  <!-- JS Libraries -->
  <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/jquery/jquery.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/adminlte/dist/js/adminlte.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>

  <?php if (!empty($loadFormLogic)): ?>
    <?php if (!empty($pegawaiList)): ?>
      <script>
        window.pegawaiMap = <?= json_encode(
          array_column($pegawaiList, 'nama_lengkap', 'nomor_induk_pegawai'),
          JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        ) ?>;
      </script>
    <?php endif; ?>
    <script src="<?= BASE_URL ?>/public/assets/js/form_logic.js"></script>
  <?php endif; ?>

</body>
</html>