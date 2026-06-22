<!DOCTYPE html>
<html lang="<?= esc(session()->get('pref_lang') ?? 'id') ?>" data-theme="<?= esc(session()->get('pref_theme') ?? 'dark') ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Dashboard') ?> — Dapur MBG SPPG</title>
  <meta name="description" content="Sistem Manajemen SPPG Dapur MBG - Makan Bergizi Gratis">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">

  <!-- Preconnect Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

  <!-- App CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . filemtime(FCPATH . 'assets/css/app.css')) ?>">

  <?= $this->renderSection('styles') ?>
</head>
<body>
  <!-- Alpine.js App Wrapper -->
  <div
    class="app-wrapper"
    x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false' }"
    :class="{ 'sidebar-collapsed': !sidebarOpen }"
    x-init="$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val))"
  >

    <!-- ═══════════════════════════════════════════════════
         SIDEBAR
    ═══════════════════════════════════════════════════ -->
    <?= $this->include('components/sidebar') ?>

    <!-- ═══════════════════════════════════════════════════
         MAIN CONTENT AREA
    ═══════════════════════════════════════════════════ -->
    <div class="main-content" id="mainContent">

      <!-- TOPBAR -->
      <?= $this->include('components/navbar') ?>

      <!-- PAGE CONTENT -->
      <div class="page-content">

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success" id="flashAlert" role="alert">
          <span><?= esc(session()->getFlashdata('success')) ?></span>
          <button onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error" id="flashAlert" role="alert">
          <span><?= esc(session()->getFlashdata('error')) ?></span>
          <button onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert alert-warning" role="alert">
          <span><?= esc(session()->getFlashdata('warning')) ?></span>
          <button onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-info" role="alert">
          <span><?= esc(session()->getFlashdata('info')) ?></span>
          <button onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
        </div>
        <?php endif; ?>

        <!-- ── Rendered Page Content ── -->
        <?= $this->renderSection('content') ?>

      </div><!-- /.page-content -->
    </div><!-- /.main-content -->
  </div><!-- /.app-wrapper -->

  <!-- ═══════════════════════════════════════════════════
       SCRIPTS (deferred)
  ═══════════════════════════════════════════════════ -->
  <!-- Alpine.js -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <!-- App JS -->
  <script src="<?= base_url('assets/js/app.js') ?>"></script>
  <!-- Initialize Lucide -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof lucide !== 'undefined') lucide.createIcons();
    });
  </script>

  <?= $this->renderSection('scripts') ?>
</body>
</html>
