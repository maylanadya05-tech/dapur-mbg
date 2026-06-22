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

        <!-- Toast Container -->
        <div id="toast-container" class="toast-container"></div>

        <!-- Flash Messages via Toast -->
        <?php if (session()->getFlashdata('success')): ?>
          <script>
            document.addEventListener('DOMContentLoaded', () => {
              showToast(<?= json_encode(session()->getFlashdata('success')) ?>, 'success');
            });
          </script>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
          <script>
            document.addEventListener('DOMContentLoaded', () => {
              showToast(<?= json_encode(session()->getFlashdata('error')) ?>, 'error');
            });
          </script>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
          <script>
            document.addEventListener('DOMContentLoaded', () => {
              showToast(<?= json_encode(session()->getFlashdata('warning')) ?>, 'warning');
            });
          </script>
        <?php endif; ?>

        <?php if (session()->getFlashdata('info')): ?>
          <script>
            document.addEventListener('DOMContentLoaded', () => {
              showToast(<?= json_encode(session()->getFlashdata('info')) ?>, 'info');
            });
          </script>
        <?php endif; ?>

        <!-- ── Rendered Page Content ── -->
        <?= $this->renderSection('content') ?>

      </div><!-- /.page-content -->
    </div><!-- /.main-content -->
  </div><!-- /.app-wrapper -->

  <!-- ═══════════════════════════════════════════════════
       SCRIPTS (deferred)
  ═══════════════════════════════════════════════════ -->
  <!-- Chart.js -->
  <script defer src="<?= base_url('assets/vendor/chart.umd.min.js') ?>"></script>
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
