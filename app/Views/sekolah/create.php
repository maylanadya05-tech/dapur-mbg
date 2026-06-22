<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Tambah Sekolah</h1>
    <p class="page-subtitle">Daftarkan sekolah baru penerima program Makan Bergizi Gratis</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/sekolah') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div style="max-width: 800px;">
  <div class="card" x-data="sekolahForm()">
    <div class="card-header" style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
      <h3 class="card-title">Form Profil Sekolah Baru</h3>
      <p class="card-subtitle">Pastikan data yang dimasukkan akurat untuk keperluan perhitungan porsi makan.</p>
    </div>

    <form action="<?= base_url('/sekolah/store') ?>" method="POST" @submit.prevent="submitForm">
      <?= csrf_field() ?>

      <!-- ── SECTION 1: Informasi Identitas ── -->
      <h4 style="font-size:0.9rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--emerald);margin-bottom:1rem;">I. Identitas Sekolah</h4>
      
      <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Kode Sekolah <span style="color:var(--status-danger);">*</span></label>
          <input
            type="text"
            name="kode"
            class="form-control"
            placeholder="Contoh: SCH-006"
            x-model="kode"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
          <p class="form-hint" style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem;">Gunakan format SCH-XXX atau sejenisnya.</p>
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Nama Sekolah <span style="color:var(--status-danger);">*</span></label>
          <input
            type="text"
            name="nama"
            class="form-control"
            placeholder="Contoh: SDN Pajajaran 02"
            x-model="nama"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>
      </div>

      <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Jenjang Pendidikan <span style="color:var(--status-danger);">*</span></label>
          <select
            name="jenjang"
            class="form-select"
            x-model="jenjang"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
            <option value="SD">SD (Sekolah Dasar)</option>
            <option value="SMP">SMP (Sekolah Menengah Pertama)</option>
            <option value="SMA">SMA (Sekolah Menengah Atas)</option>
            <option value="SMK">SMK (Sekolah Menengah Kejuruan)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Jumlah Siswa (Portion Count) <span style="color:var(--status-danger);">*</span></label>
          <input
            type="number"
            name="jumlah_siswa"
            class="form-control"
            placeholder="0"
            min="1"
            x-model="jumlahSiswa"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>
      </div>

      <!-- ── SECTION 2: Kontak & Kepala Sekolah ── -->
      <h4 style="font-size:0.9rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--emerald);margin-bottom:1rem;margin-top:2rem;">II. Kontak & Kepemimpinan</h4>

      <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Nama Kepala Sekolah</label>
          <input
            type="text"
            name="kepala_sekolah"
            class="form-control"
            placeholder="Nama lengkap beserta gelar"
            x-model="kepalaSekolah"
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">No. Telepon / WhatsApp</label>
          <input
            type="text"
            name="no_telp"
            class="form-control"
            placeholder="Contoh: 0812XXXXXXXX"
            x-model="noTelp"
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>
      </div>

      <!-- ── SECTION 3: Detail Alamat ── -->
      <h4 style="font-size:0.9rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--emerald);margin-bottom:1rem;margin-top:2rem;">III. Alamat & Lokasi</h4>

      <div class="form-group" style="margin-bottom:1.25rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Alamat Jalan / Gedung <span style="color:var(--status-danger);">*</span></label>
        <textarea
          name="alamat"
          class="form-control"
          placeholder="Jl. Raya Pajajaran No. XX, Blok B"
          rows="2"
          x-model="alamat"
          required
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);font-family:inherit;resize:none;"
        ></textarea>
      </div>

      <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;margin-bottom:2rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Kelurahan <span style="color:var(--status-danger);">*</span></label>
          <input
            type="text"
            name="kelurahan"
            class="form-control"
            placeholder="Babakan"
            x-model="kelurahan"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Kecamatan <span style="color:var(--status-danger);">*</span></label>
          <input
            type="text"
            name="kecamatan"
            class="form-control"
            placeholder="Bogor Tengah"
            x-model="kecamatan"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Kota / Kabupaten <span style="color:var(--status-danger);">*</span></label>
          <input
            type="text"
            name="kota"
            class="form-control"
            placeholder="Kota Bogor"
            x-model="kota"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>
      </div>

      <!-- ── Actions ── -->
      <div style="display:flex;justify-content:flex-end;gap:0.75rem;border-top:1px solid var(--border-subtle);padding-top:1.5rem;">
        <a href="<?= base_url('/sekolah') ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i data-lucide="save" style="width:18px;height:18px;margin-right:6px;display:inline-block;vertical-align:middle;"></i>
          Simpan Sekolah
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function sekolahForm() {
    return {
      kode: '',
      nama: '',
      jenjang: 'SD',
      jumlahSiswa: '',
      kepalaSekolah: '',
      noTelp: '',
      alamat: '',
      kelurahan: '',
      kecamatan: '',
      kota: 'Kota Bogor',

      submitForm(e) {
        // Perform standard HTML5 form submission
        e.target.submit();
      }
    };
  }
</script>
<?= $this->endSection() ?>
