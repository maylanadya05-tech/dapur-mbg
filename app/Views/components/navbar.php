<?php
/**
 * Navbar / Topbar Component — Dapur MBG SPPG
 * Sticky top bar with sidebar toggle, breadcrumb, datetime, notifications, and user dropdown
 */
$session  = session();
$userName = $session->get('user_name') ?? $session->get('user_name') ?? 'Pengguna';
$userInitial = strtoupper(substr($userName, 0, 1));
$role     = $session->get('user_role') ?? 'viewer';

// Build breadcrumb from URI segments
$uri      = service('request')->getUri();
$segments = $uri->getSegments();
$pageMap  = [
    'dashboard'  => 'Dashboard',
    'stok'       => 'Stok Gudang',
    'resep'      => 'Resep & BOM',
    'pembelian'  => 'Pembelian (PO)',
    'produksi'   => 'Produksi Dapur',
    'sekolah'    => 'Sekolah Sasaran',
    'distribusi' => 'Log Distribusi',
    'sisa'       => 'Sisa & Sampah',
    'feedback'   => 'Umpan Balik',
    'invoice'    => 'Invoice',
    'supplier'   => 'Kinerja Supplier',
    'jadwal'     => 'Jadwal Siklus',
    'pengguna'   => 'Tim & Pengguna',
    'laporan'    => 'Laporan Analitis',
    'create'     => 'Tambah Baru',
    'edit'       => 'Edit',
    'show'       => 'Detail',
];
?>

<!-- ╔══════════════════════════════════════════════════════════╗
     ║  TOPBAR                                                 ║
     ╚══════════════════════════════════════════════════════════╝ -->
<header class="topbar" role="banner">

  <!-- Left: Toggle + Breadcrumb -->
  <div class="topbar-left">

    <!-- Sidebar Toggle Button -->
    <button
      class="sidebar-toggle"
      @click="sidebarOpen = !sidebarOpen"
      :title="sidebarOpen ? 'Ciutkan Sidebar' : 'Buka Sidebar'"
      aria-label="Toggle Sidebar"
    >
      <i data-lucide="menu"></i>
    </button>

    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <div class="breadcrumb-item">
        <a href="<?= base_url('/dashboard') ?>">
          <i data-lucide="home" style="width:13px;height:13px;"></i>
        </a>
      </div>

      <?php foreach ($segments as $i => $segment): ?>
        <?php $label = $pageMap[$segment] ?? ucfirst($segment); ?>
        <span class="breadcrumb-sep" aria-hidden="true">/</span>
        <?php if ($i === count($segments) - 1): ?>
          <div class="breadcrumb-item active" aria-current="page">
            <?= esc($label) ?>
          </div>
        <?php else: ?>
          <div class="breadcrumb-item">
            <a href="<?= base_url(implode('/', array_slice($segments, 0, $i + 1))) ?>">
              <?= esc($label) ?>
            </a>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>

  </div><!-- /.topbar-left -->

  <!-- Right: Datetime + Notifications + User -->
  <div class="topbar-right">

    <!-- Live Clock -->
    <div class="topbar-datetime" id="topbarClock" aria-live="polite" aria-atomic="true">
      <?= date('d M Y · H:i') ?>
    </div>

    <!-- Notification Bell -->
    <a href="<?= base_url('/notifikasi') ?>" class="topbar-icon-btn" title="Notifikasi" aria-label="Notifikasi">
      <i data-lucide="bell"></i>
      <?php /* Tampilkan dot jika ada notifikasi belum dibaca */ ?>
      <span class="notif-dot" aria-hidden="true"></span>
    </a>

    <!-- Quick Settings / Help -->
    <a href="<?= base_url('/bantuan') ?>" class="topbar-icon-btn" title="Bantuan" aria-label="Bantuan">
      <i data-lucide="help-circle"></i>
    </a>

    <!-- User Dropdown (Alpine.js) -->
    <div
      class="user-dropdown"
      x-data="{ open: false }"
      @click.outside="open = false"
    >
      <button
        class="user-dropdown-trigger"
        @click="open = !open"
        :aria-expanded="open"
        aria-haspopup="true"
      >
        <div class="user-dropdown-avatar" aria-hidden="true">
          <?= $userInitial ?>
        </div>
        <span class="user-dropdown-name"><?= esc($userName) ?></span>
        <i data-lucide="chevron-down" style="width:14px;height:14px;color:var(--text-muted);transition:transform 0.2s;" :style="open ? 'transform:rotate(180deg)' : ''"></i>
      </button>

      <!-- Dropdown Menu -->
      <div
        class="user-dropdown-menu"
        x-show="open"
        x-transition:enter="transition"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        role="menu"
      >
        <!-- User Info Header -->
        <div style="padding:0.5rem 0.75rem 0.75rem; border-bottom:1px solid var(--border-subtle);">
          <div style="font-size:0.8rem;font-weight:700;color:var(--text-primary);"><?= esc($userName) ?></div>
          <div style="font-size:0.72rem;color:var(--text-muted);"><?= esc($session->get('user_email') ?? '') ?></div>
          <div style="margin-top:0.375rem;">
            <span class="badge badge-success" style="font-size:0.65rem;"><?= esc(ucfirst($role)) ?></span>
          </div>
        </div>

        <a href="<?= base_url('/profil') ?>" role="menuitem">
          <i data-lucide="user" style="width:15px;height:15px;"></i>
          <?= lang('App.profil_saya') ?>
        </a>
        <a href="<?= base_url('/pengaturan') ?>" role="menuitem">
          <i data-lucide="settings" style="width:15px;height:15px;"></i>
          <?= lang('App.pengaturan') ?>
        </a>

        <div class="dropdown-divider"></div>

        <a href="<?= base_url('/auth/logout') ?>" class="logout-btn" role="menuitem"
           onclick="return confirm('<?= (session()->get('pref_lang') === 'en') ? 'Are you sure you want to logout?' : 'Yakin ingin keluar?' ?>')">
          <i data-lucide="log-out" style="width:15px;height:15px;"></i>
          <?= lang('App.keluar') ?>
        </a>

      </div><!-- /.user-dropdown-menu -->
    </div><!-- /.user-dropdown -->

  </div><!-- /.topbar-right -->

</header><!-- /.topbar -->
