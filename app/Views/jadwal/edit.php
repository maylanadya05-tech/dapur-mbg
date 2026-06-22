<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
// Connect to variables passed from JadwalSiklus controller
$jadwal = $schedule ?? [
  'id' => 0,
  'nama_siklus' => '',
  'tanggal_mulai' => '',
  'durasi_hari' => 5,
  'tanggal_selesai' => '',
  'is_active' => 0
];

$recipeList = $recipes ?? [
  ['id' => 1, 'nama_menu' => 'Nasi Putih + Daging Semur + Sop Sayur'],
  ['id' => 2, 'nama_menu' => 'Nasi Kuning + Ayam Goreng + Sayur Asem'],
];

$detailMenu = [];
if (isset($mappedDetails) && is_array($mappedDetails)) {
    foreach ($mappedDetails as $hariKe => $detail) {
        $detailMenu[$hariKe] = (int)$detail['resep_id'];
    }
}
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Edit Siklus Menu</h1>
    <p class="page-subtitle">Ubah rancangan siklus menu <?= esc($jadwal['nama_siklus']) ?></p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/jadwal') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div style="max-width: 800px;">
  <div class="card" x-data="jadwalForm()">
    <div class="card-header" style="margin-bottom:1.5rem; border-bottom:1px solid var(--border-subtle); padding-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
      <div>
        <h3 class="card-title">Form Ubah Siklus Menu</h3>
        <p class="card-subtitle">Perubahan menu pada siklus aktif akan langsung berimbas pada modul produksi hari ini.</p>
      </div>
      <div>
        <select name="status" class="form-select" x-model="status" style="background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--border-radius-sm); color:var(--text-primary); padding: 0.375rem 0.75rem; font-size: 0.875rem;">
          <option value="aktif">Aktif</option>
          <option value="terjadwal">Terjadwal</option>
          <option value="selesai">Selesai</option>
        </select>
      </div>
    </div>

    <form action="<?= base_url('/jadwal/update/' . $jadwal['id']) ?>" method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="is_active" :value="status === 'aktif' ? 1 : 0">

      <!-- ── Cycle Name ── -->
      <div class="form-group" style="margin-bottom: 1.25rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Nama Siklus Menu <span style="color:var(--status-danger);">*</span></label>
        <input
          type="text"
          name="nama_siklus"
          class="form-control"
          placeholder="Contoh: Siklus Gizi Utama"
          x-model="namaSiklus"
          required
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
        >
      </div>

      <!-- ── Start Date & Duration ── -->
      <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Tanggal Mulai <span style="color:var(--status-danger);">*</span></label>
          <input
            type="date"
            name="tanggal_mulai"
            class="form-control"
            x-model="tanggalMulai"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Durasi Siklus (Hari) <span style="color:var(--status-danger);">*</span></label>
          <input
            type="number"
            name="durasi_hari"
            class="form-control"
            min="1"
            max="14"
            x-model.number="durasiHari"
            @input="generateRows"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>
      </div>

      <!-- ── Day to Day Menu Mapper ── -->
      <h4 style="font-size:0.9rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--emerald);margin-bottom:1rem;border-bottom:1px dashed var(--border-subtle);padding-bottom:0.5rem;margin-top:2rem;">II. Pemetaan Menu Harian</h4>

      <!-- Dynmamic Day Rows container -->
      <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:2rem;">
        <template x-for="(day, index) in dayRows" :key="index">
          <div style="display:grid;grid-template-columns:100px 1fr;align-items:center;gap:1rem;background:var(--bg-card-hover);padding:0.875rem 1.25rem;border-radius:var(--border-radius-sm);border:1px solid var(--border-subtle);">
            
            <strong style="color:var(--emerald);font-size:0.875rem;" x-text="'Hari ' + day.dayNum"></strong>
            
            <div class="form-group" style="margin:0;">
              <select
                :name="'resep_hari[' + day.dayNum + ']'"
                class="form-select"
                x-model="day.recipeId"
                required
                style="width:100%;padding:0.5rem 0.75rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
              >
                <option value="">-- Pilih Resep Menu Dapur --</option>
                <?php foreach ($recipeList as $recipe): ?>
                <option value="<?= $recipe['id'] ?>"><?= esc($recipe['nama_menu']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

          </div>
        </template>
      </div>

      <!-- ── Actions ── -->
      <div style="display:flex;justify-content:flex-end;gap:0.75rem;border-top:1px solid var(--border-subtle);padding-top:1.5rem;">
        <a href="<?= base_url('/jadwal') ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i data-lucide="save" style="width:18px;height:18px;margin-right:6px;display:inline-block;vertical-align:middle;"></i>
          Perbarui Siklus Menu
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function jadwalForm() {
    return {
      namaSiklus: '<?= esc($jadwal['nama_siklus']) ?>',
      tanggalMulai: '<?= esc($jadwal['tanggal_mulai']) ?>',
      durasiHari: <?= $jadwal['durasi_hari'] ?>,
      status: '<?= ($jadwal['is_active'] == 1) ? 'aktif' : ((!empty($jadwal['tanggal_selesai']) && $jadwal['tanggal_selesai'] < date('Y-m-d')) ? 'selesai' : 'terjadwal') ?>',
      dayRows: [],
      // Existing mapping parsed from PHP
      existingMappings: <?= json_encode($detailMenu) ?>,

      init() {
        this.generateRows();
      },

      generateRows() {
        let count = parseInt(this.durasiHari || 0);
        if (count < 1) count = 1;
        if (count > 14) count = 14;

        this.dayRows = [];
        for (let i = 1; i <= count; i++) {
          // Pre-populate recipeId if it was already selected in existingMappings
          const existingRecipeId = this.existingMappings[i] || '';
          this.dayRows.push({ dayNum: i, recipeId: existingRecipeId });
        }
      },

      submitForm(e) {
        e.target.submit();
      }
    };
  }
</script>
<?= $this->endSection() ?>
