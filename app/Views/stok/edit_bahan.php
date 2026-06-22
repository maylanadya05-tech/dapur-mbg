<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Edit Bahan Baku</h1>
    <p class="page-subtitle">Ubah rincian data bahan baku <?= esc($bahan['nama']) ?></p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/stok') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali ke Stok
    </a>
  </div>
</div>

<div style="max-width: 720px;">
  <!-- Alert Error List -->
  <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-error" role="alert" style="margin-bottom: 1.5rem;">
      <ul style="margin: 0; padding-left: 1.25rem;">
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
          <li><?= esc($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card" x-data="bahanForm()">
    <div class="card-header" style="margin-bottom:1.5rem; border-bottom:1px solid var(--border-subtle); padding-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
      <div>
        <h3 class="card-title">Informasi Bahan Baku</h3>
        <p class="card-subtitle">Perubahan data ini akan memengaruhi modul stok dan resep terkait.</p>
      </div>
      <div>
        <label class="switch" style="position:relative; display:inline-block; width:44px; height:24px; margin:0; cursor:pointer;">
          <input
            type="checkbox"
            name="is_active"
            value="1"
            x-model="isActive"
            style="opacity:0; width:0; height:0;"
            class="switch-input"
          >
          <span class="slider" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:var(--border-subtle); transition:.3s; border-radius:34px;"></span>
        </label>
        <span style="font-size:0.8rem; color:var(--text-secondary); margin-left:0.5rem; vertical-align:middle;" x-text="isActive ? 'Aktif' : 'Non-Aktif'"></span>
      </div>
    </div>

    <form action="<?= base_url('/stok/update/' . $bahan['id']) ?>" method="POST">
      <?= csrf_field() ?>
      
      <!-- Hidden input to submit the switch state -->
      <input type="hidden" name="is_active" :value="isActive ? '1' : '0'">

      <div style="display:flex; flex-direction:column; gap:1.25rem;">
        
        <!-- Kode & Nama Bahan -->
        <div class="form-row" style="display:grid; grid-template-columns:1fr 2fr; gap:1.25rem;">
          <div class="form-group">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; font-size:0.875rem; color:var(--text-secondary);">Kode Bahan <span style="color:var(--status-danger);">*</span></label>
            <input
              type="text"
              name="kode"
              class="form-control"
              placeholder="Contoh: BB-001"
              value="<?= esc($bahan['kode']) ?>"
              required
              style="width:100%; padding:0.625rem 0.875rem; background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--border-radius-sm); color:var(--text-primary);"
            >
          </div>

          <div class="form-group">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; font-size:0.875rem; color:var(--text-secondary);">Nama Bahan Baku <span style="color:var(--status-danger);">*</span></label>
            <input
              type="text"
              name="nama"
              class="form-control"
              placeholder="Contoh: Wortel Segar"
              value="<?= esc($bahan['nama']) ?>"
              required
              style="width:100%; padding:0.625rem 0.875rem; background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--border-radius-sm); color:var(--text-primary);"
            >
          </div>
        </div>

        <!-- Kategori & Satuan -->
        <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
          <div class="form-group">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; font-size:0.875rem; color:var(--text-secondary);">Kategori <span style="color:var(--status-danger);">*</span></label>
            <select
              name="kategori"
              class="form-select"
              required
              style="width:100%; padding:0.625rem 0.875rem; background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--border-radius-sm); color:var(--text-primary);"
            >
              <option value="Karbohidrat" <?= ($bahan['kategori'] === 'Karbohidrat') ? 'selected' : '' ?>>Karbohidrat</option>
              <option value="Protein" <?= ($bahan['kategori'] === 'Protein') ? 'selected' : '' ?>>Protein</option>
              <option value="Sayuran" <?= ($bahan['kategori'] === 'Sayuran') ? 'selected' : '' ?>>Sayuran</option>
              <option value="Minyak" <?= ($bahan['kategori'] === 'Minyak') ? 'selected' : '' ?>>Minyak</option>
              <option value="Bumbu" <?= ($bahan['kategori'] === 'Bumbu') ? 'selected' : '' ?>>Bumbu</option>
              <option value="Buah" <?= ($bahan['kategori'] === 'Buah') ? 'selected' : '' ?>>Buah</option>
              <option value="Susu" <?= ($bahan['kategori'] === 'Susu') ? 'selected' : '' ?>>Susu</option>
              <option value="Lainnya" <?= ($bahan['kategori'] === 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; font-size:0.875rem; color:var(--text-secondary);">Satuan Ukuran <span style="color:var(--status-danger);">*</span></label>
            <input
              type="text"
              name="satuan"
              class="form-control"
              placeholder="Contoh: kg, liter, butir"
              value="<?= esc($bahan['satuan']) ?>"
              required
              style="width:100%; padding:0.625rem 0.875rem; background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--border-radius-sm); color:var(--text-primary);"
            >
          </div>
        </div>

        <!-- Harga Satuan & Stok Minimum -->
        <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
          <div class="form-group">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; font-size:0.875rem; color:var(--text-secondary);">Harga per Satuan (Rp) <span style="color:var(--status-danger);">*</span></label>
            <input
              type="number"
              name="harga_per_satuan"
              class="form-control"
              placeholder="Contoh: 12000"
              value="<?= esc($bahan['harga_per_satuan']) ?>"
              required
              style="width:100%; padding:0.625rem 0.875rem; background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--border-radius-sm); color:var(--text-primary);"
            >
          </div>

          <div class="form-group">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; font-size:0.875rem; color:var(--text-secondary);">Ambang Stok Minimum <span style="color:var(--status-danger);">*</span></label>
            <input
              type="number"
              name="stok_minimum"
              class="form-control"
              placeholder="Contoh: 15"
              value="<?= esc($bahan['stok_minimum']) ?>"
              required
              style="width:100%; padding:0.625rem 0.875rem; background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--border-radius-sm); color:var(--text-primary);"
            >
            <span style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem; display:block;">WhatsApp alert akan terkirim jika stok saat ini berada di bawah batas ini.</span>
          </div>
        </div>

        <!-- Supplier Utama -->
        <div class="form-group">
          <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; font-size:0.875rem; color:var(--text-secondary);">Supplier Utama (Opsional)</label>
          <select
            name="supplier_id"
            class="form-select"
            style="width:100%; padding:0.625rem 0.875rem; background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--border-radius-sm); color:var(--text-primary);"
          >
            <option value="">-- Tanpa Supplier Utama --</option>
            <?php foreach ($suppliers as $supplier): ?>
              <option value="<?= $supplier['id'] ?>" <?= ($bahan['supplier_id'] == $supplier['id']) ? 'selected' : '' ?>>
                <?= esc($supplier['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

      </div>

      <!-- ── Actions ── -->
      <div style="display:flex; justify-content:flex-end; gap:0.75rem; border-top:1px solid var(--border-subtle); padding-top:1.5rem; margin-top:2rem;">
        <a href="<?= base_url('/stok') ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i data-lucide="save" style="width:18px; height:18px; margin-right:6px; display:inline-block; vertical-align:middle;"></i>
          Simpan Perubahan
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

<?= $this->section('scripts') ?>
<script>
  function bahanForm() {
    return {
      isActive: <?= ($bahan['is_active'] == 1) ? 'true' : 'false' ?>,
    };
  }
</script>
<?= $this->endSection() ?>
