<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Tambah Supplier</h1>
    <p class="page-subtitle">Daftarkan mitra pemasok bahan makanan baru</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/supplier') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div style="max-width: 720px;">
  <div class="card" x-data="supplierForm()">
    <div class="card-header" style="margin-bottom:1.5rem; border-bottom:1px solid var(--border-subtle); padding-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
      <div>
        <h3 class="card-title">Profil Supplier Baru</h3>
        <p class="card-subtitle">Lengkapi detail untuk kelancaran pemesanan Purchase Order (PO).</p>
      </div>
      <div>
        <span class="badge" :class="status === 'aktif' ? 'badge-success' : 'badge-danger'" x-text="status === 'aktif' ? 'Status: Aktif' : 'Status: Nonaktif'"></span>
      </div>
    </div>

    <form action="<?= base_url('/supplier/store') ?>" method="POST" @submit.prevent="submitForm">
      <?= csrf_field() ?>

      <!-- ── Supplier Identity ── -->
      <div class="form-row" style="display:grid;grid-template-columns:1.5fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Nama Perusahaan / Supplier <span style="color:var(--status-danger);">*</span></label>
          <input
            type="text"
            name="nama"
            class="form-control"
            placeholder="Contoh: PT Beras Cianjur"
            x-model="nama"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Kategori Supply <span style="color:var(--status-danger);">*</span></label>
          <select
            name="kategori"
            class="form-select"
            x-model="kategori"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
            <option value="Bahan Pokok / Karbohidrat">Bahan Pokok / Karbohidrat</option>
            <option value="Daging / Protein">Daging / Protein</option>
            <option value="Sayuran & Buah">Sayuran & Buah</option>
            <option value="Rempah & Bumbu">Rempah & Bumbu</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>
      </div>

      <!-- ── Contact Person ── -->
      <div class="form-group" style="margin-bottom:1.25rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Nama Kontak Person (PIC) <span style="color:var(--status-danger);">*</span></label>
        <input
          type="text"
          name="kontak_nama"
          class="form-control"
          placeholder="Nama lengkap perwakilan supplier"
          x-model="kontakNama"
          required
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
        >
      </div>

      <!-- ── Phone & Email ── -->
      <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">No. Telepon / HP <span style="color:var(--status-danger);">*</span></label>
          <input
            type="text"
            name="no_telp"
            class="form-control"
            placeholder="Contoh: 0812XXXXXXXX"
            x-model="noTelp"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Email Resmi <span style="color:var(--status-danger);">*</span></label>
          <input
            type="email"
            name="email"
            class="form-control"
            placeholder="supplier@email.com"
            x-model="email"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>
      </div>

      <!-- ── Address ── -->
      <div class="form-group" style="margin-bottom:1.25rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Alamat Kantor / Gudang <span style="color:var(--status-danger);">*</span></label>
        <textarea
          name="alamat"
          class="form-control"
          placeholder="Tuliskan alamat lengkap pengiriman pasokan"
          rows="2"
          x-model="alamat"
          required
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);font-family:inherit;resize:none;"
        ></textarea>
      </div>

      <!-- ── Rating & Status ── -->
      <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:2rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Rating Awal Performa</label>
          <select
            name="rating"
            class="form-select"
            x-model="rating"
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
            <option value="5">⭐⭐⭐⭐⭐ (Sempurna / 5 Stars)</option>
            <option value="4">⭐⭐⭐⭐ (Bagus / 4 Stars)</option>
            <option value="3">⭐⭐⭐ (Cukup / 3 Stars)</option>
            <option value="2">⭐⭐ (Kurang / 2 Stars)</option>
            <option value="1">⭐ (Buruk / 1 Star)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Status Kemitraan</label>
          <select
            name="status"
            class="form-select"
            x-model="status"
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
            <option value="aktif">Aktif (Dapat Menerima PO)</option>
            <option value="nonaktif">Non-Aktif (Ditangguhkan Sementara)</option>
          </select>
        </div>
      </div>

      <!-- ── Actions ── -->
      <div style="display:flex;justify-content:flex-end;gap:0.75rem;border-top:1px solid var(--border-subtle);padding-top:1.5rem;">
        <a href="<?= base_url('/supplier') ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i data-lucide="save" style="width:18px;height:18px;margin-right:6px;display:inline-block;vertical-align:middle;"></i>
          Simpan Supplier
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function supplierForm() {
    return {
      nama: '',
      kategori: 'Bahan Pokok / Karbohidrat',
      kontakNama: '',
      noTelp: '',
      email: '',
      alamat: '',
      rating: '5',
      status: 'aktif',

      submitForm(e) {
        e.target.submit();
      }
    };
  }
</script>
<?= $this->endSection() ?>
