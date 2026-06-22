<?php
/**
 * Sidebar Component — Dapur MBG SPPG
 * Collapsible sidebar with role-based navigation
 */

// Use the Router service to get the current controller name.
// This is the most reliable method in CI4 regardless of baseURL,
// index.php presence, or mod_rewrite configuration.
$router        = service('router');
$rawController = strtolower($router->controllerName());
// Strip PHP namespace (e.g. 'App\Controllers\Pembelian' → 'pembelian')
$currentUri = str_contains($rawController, '\\')
    ? strtolower(substr($rawController, strrpos($rawController, '\\') + 1))
    : $rawController;

$session     = session();
$role        = $session->get('user_role') ?? 'viewer';
$userName    = $session->get('user_name') ?? 'Pengguna';
$userInitial = strtoupper(substr($userName, 0, 1));
$userEmail   = $session->get('user_email') ?? '';

// isActive: compare nav path segment against current controller name.
// The second argument is the actual controller class name (lowercase).
// Pass the correct controller name when the URL path differs from class name.
function isActive(string $controllerName, string $currentUri): bool {
    return strtolower($controllerName) === strtolower($currentUri);
}

// Role check helpers
$isAdmin     = ($role === 'admin');
$isGudang    = in_array($role, ['admin', 'gudang']);
$isProduksi  = in_array($role, ['admin', 'produksi']);
$isPembelian = in_array($role, ['admin', 'pembelian']);
?>

<!-- ╔══════════════════════════════════════════════════════════╗
     ║  SIDEBAR                                                ║
     ╚══════════════════════════════════════════════════════════╝ -->
<aside
  class="sidebar"
  :class="{ 'collapsed': !sidebarOpen }"
  id="sidebar"
  aria-label="Sidebar Navigasi"
>

  <!-- ── Logo & Brand ── -->
  <a href="<?= base_url('/dashboard') ?>" class="sidebar-logo">
    <div class="sidebar-logo-icon" aria-hidden="true">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
        <path d="M8 12c0-2.21 1.79-4 4-4s4 1.79 4 4"/>
        <path d="M9 16c0-1.1.9-2 2-2h2c1.1 0 2 .9 2 2"/>
        <path d="M12 6v2"/>
      </svg>
    </div>
    <div class="sidebar-logo-text">
      <h1>Dapur MBG</h1>
      <span>SPPG Management</span>
    </div>
  </a>

  <!-- ── Navigation ── -->
  <nav class="sidebar-nav" role="navigation" id="sidebarNav">

    <!-- ═══ GROUP: UTAMA ═══ -->
    <div class="nav-group">
      <div class="nav-group-label"><?= lang('App.group_utama') ?></div>

      <a href="<?= base_url('/dashboard') ?>"
         class="nav-item <?= isActive('dashboard', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="layout-dashboard"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.dashboard') ?></span>
      </a>

      <?php if ($isGudang): ?>
      <a href="<?= base_url('/stok') ?>"
         class="nav-item <?= isActive('stok', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="warehouse"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.stok_gudang') ?></span>
      </a>
      <?php endif; ?>

      <a href="<?= base_url('/resep') ?>"
         class="nav-item <?= isActive('resep', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="book-open"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.resep_bom') ?></span>
      </a>

    </div><!-- /.nav-group -->

    <!-- ═══ GROUP: OPERASIONAL ═══ -->
    <div class="nav-group">
      <div class="nav-group-label"><?= lang('App.group_operasional') ?></div>

      <?php if ($isGudang || $isPembelian): ?>
      <a href="<?= base_url('/pembelian') ?>"
         class="nav-item <?= isActive('pembelian', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="shopping-cart"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.pembelian_po') ?></span>
      </a>
      <?php endif; ?>

      <?php if ($isProduksi): ?>
      <a href="<?= base_url('/produksi') ?>"
         class="nav-item <?= isActive('produksi', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="chef-hat"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.produksi_dapur') ?></span>
      </a>
      <?php endif; ?>

      <a href="<?= base_url('/sekolah') ?>"
         class="nav-item <?= isActive('sekolah', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="school"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.sekolah_sasaran') ?></span>
      </a>

      <a href="<?= base_url('/distribusi') ?>"
         class="nav-item <?= isActive('distribusi', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="truck"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.log_distribusi') ?></span>
      </a>

      <!-- Armada (kendaraan) -->
      <a href="<?= base_url('/armada') ?>"
         class="nav-item <?= isActive('armada', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="car"></i>
        </span>
        <span class="nav-item-text">Armada Kendaraan</span>
      </a>

    </div><!-- /.nav-group -->

    <!-- ═══ GROUP: ANALITIK ═══ -->
    <div class="nav-group">
      <div class="nav-group-label"><?= lang('App.group_analitik') ?></div>

      <!-- sisa → controller: FoodWaste -->
      <a href="<?= base_url('/sisa') ?>"
         class="nav-item <?= isActive('foodwaste', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="trash-2"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.sisa_sampah') ?></span>
      </a>

      <!-- feedback → controller: Feedback -->
      <a href="<?= base_url('/feedback') ?>"
         class="nav-item <?= isActive('feedback', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="message-circle"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.umpan_balik') ?></span>
      </a>

      <?php if ($isPembelian): ?>
      <!-- invoice → controller: Invoice -->
      <a href="<?= base_url('/invoice') ?>"
         class="nav-item <?= isActive('invoice', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="file-text"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.invoice') ?></span>
      </a>
      <?php endif; ?>

      <!-- supplier → controller: Supplier -->
      <a href="<?= base_url('/supplier') ?>"
         class="nav-item <?= isActive('supplier', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="star"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.kinerja_supplier') ?></span>
      </a>

    </div><!-- /.nav-group -->

    <!-- ═══ GROUP: MANAJEMEN ═══ -->
    <?php if ($isAdmin): ?>
    <div class="nav-group">
      <div class="nav-group-label"><?= lang('App.group_manajemen') ?></div>

      <!-- jadwal → controller: JadwalSiklus -->
      <a href="<?= base_url('/jadwal') ?>"
         class="nav-item <?= isActive('jadwalsiklus', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="calendar-days"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.jadwal_siklus') ?></span>
      </a>

      <?php if ($isAdmin): ?>
      <!-- pengguna → route: /users, controller: Users -->
      <a href="<?= base_url('/users') ?>"
         class="nav-item <?= isActive('users', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="users"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.tim_pengguna') ?></span>
      </a>
      <?php endif; ?>

      <!-- laporan → controller: Laporan -->
      <a href="<?= base_url('/laporan') ?>"
         class="nav-item <?= isActive('laporan', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="bar-chart-3"></i>
        </span>
        <span class="nav-item-text"><?= lang('App.laporan_analitis') ?></span>
      </a>

      <!-- laporan keuangan -->
      <a href="<?= base_url('/laporan/keuangan') ?>"
         class="nav-item <?= (isActive('laporan', $currentUri) && str_contains(service('uri')->getPath(), 'keuangan')) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="trending-up"></i>
        </span>
        <span class="nav-item-text">Laporan Keuangan</span>
      </a>

      <!-- audit log -->
      <a href="<?= base_url('/audit-log') ?>"
         class="nav-item <?= isActive('auditlog', $currentUri) ? 'active' : '' ?>">
        <span class="nav-item-icon">
          <i data-lucide="shield-check"></i>
        </span>
        <span class="nav-item-text">Audit Log</span>
      </a>

    </div><!-- /.nav-group -->
    <?php endif; ?>

  </nav><!-- /.sidebar-nav -->

  <script>
    // Preserve sidebar scroll position across page navigations
    // to prevent the jarring jump-to-top effect when clicking nav items
    (function () {
      const nav = document.getElementById('sidebarNav');
      if (!nav) return;

      // Restore scroll position IMMEDIATELY (synchronous, before paint)
      const saved = sessionStorage.getItem('sidebar_scroll');
      if (saved !== null) nav.scrollTop = parseInt(saved, 10);

      // Save scroll position before navigating away
      document.addEventListener('click', function (e) {
        const link = e.target.closest('a.nav-item');
        if (link) sessionStorage.setItem('sidebar_scroll', nav.scrollTop);
      });

      // Also save on page unload as a fallback
      window.addEventListener('pagehide', function () {
        sessionStorage.setItem('sidebar_scroll', nav.scrollTop);
      });
    })();
  </script>

  <!-- ── User Footer ── -->
  <div class="sidebar-user">
    <div class="sidebar-user-avatar" aria-hidden="true">
      <?= $userInitial ?>
    </div>
    <div class="sidebar-user-info">
      <div class="user-name"><?= esc($userName) ?></div>
      <div class="user-role">
        <?= match($role) {
            'admin'      => '⚡ Administrator',
            'gudang'     => '📦 Gudang',
            'produksi'   => '👨‍🍳 Produksi',
            'pembelian'  => '💰 Pembelian',
            default      => '👁️ Viewer',
        } ?>
      </div>
    </div>
  </div><!-- /.sidebar-user -->

</aside><!-- /.sidebar -->
