<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Login') ?> — Dapur MBG SPPG</title>
  <meta name="description" content="Login Sistem Manajemen SPPG Dapur MBG">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

  <!-- App CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">

  <?= $this->renderSection('styles') ?>
</head>
<body style="background: var(--bg-primary);">

  <?= $this->renderSection('content') ?>

  <!-- Toast Container -->
  <div id="toast-container" class="toast-container"></div>

  <!-- Lucide Icons -->
  <script defer src="<?= base_url('assets/vendor/lucide.min.js') ?>"></script>
  <!-- Alpine.js -->
  <script defer src="<?= base_url('assets/vendor/alpine.min.js') ?>"></script>
  <!-- App JS -->
  <script defer src="<?= base_url('assets/js/app.js') ?>"></script>

  <!-- Initialize Lucide -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof lucide !== 'undefined') lucide.createIcons();
    });
  </script>

  <?= $this->renderSection('scripts') ?>
</body>
</html>
