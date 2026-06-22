<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Pengaturan Preferensi</h1>
    <p class="page-subtitle">Sesuaikan preferensi tampilan dan saluran notifikasi akun Anda</p>
  </div>
</div>

<div style="max-width: 800px;">
  <div class="card">
    <div class="card-header" style="margin-bottom:1.5rem; border-bottom:1px solid var(--border-subtle); padding-bottom:1rem;">
      <h3 class="card-title">Pengaturan Sistem &amp; Akun</h3>
      <p class="card-subtitle">Pengaturan ini disimpan khusus untuk kenyamanan operasional harian Anda.</p>
    </div>

    <form action="<?= base_url('/pengaturan/update') ?>" method="POST">
      <?= csrf_field() ?>

      <!-- Section: Tampilan & Lokalisasi -->
      <div style="margin-bottom: 2rem;">
        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem;">
          <i data-lucide="palette" style="width: 16px; height: 16px; color: var(--emerald);"></i>
          Tampilan &amp; Bahasa
        </h4>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; background: var(--bg-card-hover); padding: 1.25rem; border-radius: var(--border-radius-sm); border: 1px solid var(--border-subtle);">
          <div class="form-group">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; font-size:0.875rem; color:var(--text-secondary);">Tema Antarmuka (Theme)</label>
            <select
              name="theme"
              class="form-select"
              style="width:100%; padding:0.625rem 0.875rem; background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--border-radius-sm); color:var(--text-primary);"
            >
              <option value="dark" <?= ($preferences['theme'] === 'dark') ? 'selected' : '' ?>>🌑 Gelap (Dark Mode)</option>
              <option value="light" <?= ($preferences['theme'] === 'light') ? 'selected' : '' ?>>☀️ Terang (Light Mode)</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; font-size:0.875rem; color:var(--text-secondary);">Bahasa Sistem (Language)</label>
            <select
              name="language"
              class="form-select"
              style="width:100%; padding:0.625rem 0.875rem; background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--border-radius-sm); color:var(--text-primary);"
            >
              <option value="id" <?= ($preferences['language'] === 'id') ? 'selected' : '' ?>>🇮🇩 Bahasa Indonesia</option>
              <option value="en" <?= ($preferences['language'] === 'en') ? 'selected' : '' ?>>🇬🇧 English</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Section: Saluran Notifikasi -->
      <div style="margin-bottom: 2rem;">
        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem;">
          <i data-lucide="bell-ring" style="width: 16px; height: 16px; color: var(--emerald);"></i>
          Preferensi Notifikasi
        </h4>
        
        <div style="display: flex; flex-direction: column; gap: 1.25rem; background: var(--bg-card-hover); padding: 1.25rem; border-radius: var(--border-radius-sm); border: 1px solid var(--border-subtle);">
          
          <!-- Notif 1: Stok Kritis -->
          <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
            <div style="flex: 1; padding-right: 1.5rem;">
              <div style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary);">Pemberitahuan Stok Kritis</div>
              <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Tampilkan notifikasi penting segera setelah bahan baku di gudang mencapai batas minimum.</div>
            </div>
            <div>
              <label class="switch" style="position:relative; display:inline-block; width:44px; height:24px; margin:0; cursor:pointer;">
                <input
                  type="checkbox"
                  name="notif_stock"
                  value="1"
                  <?= $preferences['notif_stock'] ? 'checked' : '' ?>
                  style="opacity:0; width:0; height:0;"
                  class="switch-input"
                >
                <span class="slider" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:var(--border-subtle); transition:.3s; border-radius:34px;"></span>
              </label>
            </div>
          </div>

          <!-- Notif 2: PO Pembelian -->
          <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
            <div style="flex: 1; padding-right: 1.5rem;">
              <div style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary);">Persetujuan &amp; Status PO</div>
              <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Kirim peringatan sistem ketika ada Purchase Order baru yang diajukan, disetujui, atau ditolak.</div>
            </div>
            <div>
              <label class="switch" style="position:relative; display:inline-block; width:44px; height:24px; margin:0; cursor:pointer;">
                <input
                  type="checkbox"
                  name="notif_order"
                  value="1"
                  <?= $preferences['notif_order'] ? 'checked' : '' ?>
                  style="opacity:0; width:0; height:0;"
                  class="switch-input"
                >
                <span class="slider" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:var(--border-subtle); transition:.3s; border-radius:34px;"></span>
              </label>
            </div>
          </div>

          <!-- Notif 3: Laporan Harian -->
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="flex: 1; padding-right: 1.5rem;">
              <div style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary);">Ringkasan Analitik Harian</div>
              <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Dapatkan email rekapitulasi harian terkait total produksi porsi makanan dan log pengiriman distribusi.</div>
            </div>
            <div>
              <label class="switch" style="position:relative; display:inline-block; width:44px; height:24px; margin:0; cursor:pointer;">
                <input
                  type="checkbox"
                  name="notif_daily_report"
                  value="1"
                  <?= $preferences['notif_daily_report'] ? 'checked' : '' ?>
                  style="opacity:0; width:0; height:0;"
                  class="switch-input"
                >
                <span class="slider" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:var(--border-subtle); transition:.3s; border-radius:34px;"></span>
              </label>
            </div>
          </div>

        </div>
      </div>

      <!-- ── Actions ── -->
      <div style="display:flex; justify-content:flex-end; gap:0.75rem; border-top:1px solid var(--border-subtle); padding-top:1.5rem;">
        <a href="<?= base_url('/dashboard') ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i data-lucide="save" style="width:18px; height:18px; margin-right:6px; display:inline-block; vertical-align:middle;"></i>
          Simpan Preferensi
        </button>
      </div>
    </form>
  </div>
</div>

<style>
  .switch-input:checked + .slider {
    background-color: var(--emerald) !important;
  }
  .slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
  }
  .switch-input:checked + .slider:before {
    transform: translateX(20px);
  }
</style>

<?= $this->endSection() ?>
