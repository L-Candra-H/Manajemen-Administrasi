<?php
require_once __DIR__ . '/../../helpers/version_helper.php';
$ver = getAppVersion($pdo);
?>
<small>
  Versi Aplikasi: <?= htmlspecialchars($ver['version']) ?>
  (<?= htmlspecialchars($ver['release_date']) ?>)
</small>
