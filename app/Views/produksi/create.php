<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
// Fallback active recipes list with BOM details for live calculation
$resepList = $resepList ?? [
  [
    'id' => 1,
    'kode' => 'RSP-2026-001',
    'nama_menu' => 'Nasi Kuning Harum',
    'kategori' => 'Makanan Pokok',
    'porsi_standar' => 1,
    'ingredients' => [
      ['nama' => 'Beras Premium', 'qty_per_porsi' => 0.12, 'satuan' => 'kg'],
      ['nama' => 'Minyak Goreng', 'qty_per_porsi' => 0.015, 'satuan' => 'liter'],
      ['nama' => 'Garam Dapur', 'qty_per_porsi' => 0.002, 'satuan' => 'kg'],
      ['nama' => 'Kunyit Bubuk', 'qty_per_porsi' => 0.005, 'satuan' => 'kg'],
      ['nama' => 'Bawang Putih', 'qty_per_porsi' => 0.003, 'satuan' => 'kg'],
    ]
  ],
  [
    'id' => 2,
    'kode' => 'RSP-2026-002',
    'nama_menu' => 'Ayam Suwir Rica',
    'kategori' => 'Lauk Pauk',
    'porsi_standar' => 1,
    'ingredients' => [
      ['nama' => 'Ayam Fillet', 'qty_per_porsi' => 0.10, 'satuan' => 'kg'],
      ['nama' => 'Minyak Goreng', 'qty_per_porsi' => 0.02, 'satuan' => 'liter'],
      ['nama' => 'Cabai Merah', 'qty_per_porsi' => 0.025, 'satuan' => 'kg'],
      ['nama' => 'Bawang Merah', 'qty_per_porsi' => 0.01, 'satuan' => 'kg'],
      ['nama' => 'Bawang Putih', 'qty_per_porsi' => 0.008, 'satuan' => 'kg'],
    ]
  ],
  [
    'id' => 3,
    'kode' => 'RSP-2026-003',
    'nama_menu' => 'Sayur Tumis Kacang Panjang',
    'kategori' => 'Sayuran',
    'porsi_standar' => 1,
    'ingredients' => [
      ['nama' => 'Kacang Panjang', 'qty_per_porsi' => 0.08, 'satuan' => 'kg'],
      ['nama' => 'Tempe Segar', 'qty_per_porsi' => 0.04, 'satuan' => 'kg'],
      ['nama' => 'Minyak Goreng', 'qty_per_porsi' => 0.01, 'satuan' => 'liter'],
      ['nama' => 'Bawang Putih', 'qty_per_porsi' => 0.002, 'satuan' => 'kg'],
      ['nama' => 'Garam Dapur', 'qty_per_porsi' => 0.001, 'satuan' => 'kg'],
    ]
  ]
];

$teams = $teams ?? ['Tim Cempaka', 'Tim Dahlia', 'Tim Bougenville', 'Tim Tulip', 'Tim Logistik'];
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Mulai Batch Produksi</h1>
    <p class="page-subtitle">Buat batch baru dan perkirakan kebutuhan bahan baku untuk porsi target</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/produksi') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div x-data="produksiForm()">
  <form
    action="<?= base_url('/produksi/store') ?>"
    method="POST"
    @submit.prevent="submitForm"
    novalidate
  >
    <?= csrf_field() ?>

    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 1.5rem; align-items: start;">
      
      <!-- ══ LEFT COLUMN: FORM DETAILS ══ -->
      <div class="card" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <div class="card-header" style="padding: 0; margin-bottom: 0.5rem;">
          <h3 class="card-title">Form Batch Produksi</h3>
          <span style="font-size: 0.8rem; color: var(--text-muted);">Masukkan detail porsi dan tim pelaksana</span>
        </div>

        <!-- Resep Select -->
        <div class="form-group">
          <label class="form-label" for="resep_id">Resep Menu <span class="required">*</span></label>
          <select
            id="resep_id"
            name="resep_id"
            class="form-select"
            :class="{ 'is-invalid': errors.resep_id }"
            x-model="selectedResepId"
            @change="updateSelectedRecipe"
            required
          >
            <option value="">-- Pilih Resep Menu --</option>
            <?php foreach ($resepList as $resep): ?>
            <option value="<?= $resep['id'] ?>"><?= esc($resep['nama_menu']) ?> (<?= esc($resep['kategori']) ?>)</option>
            <?php endforeach; ?>
          </select>
          <div class="form-error" x-show="errors.resep_id" x-text="errors.resep_id"></div>
        </div>

        <!-- Target Porsi & Tanggal Produksi -->
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="target_porsi">Target Porsi <span class="required">*</span></label>
            <div class="input-group">
              <input
                type="number"
                id="target_porsi"
                name="target_porsi"
                class="form-control"
                :class="{ 'is-invalid': errors.target_porsi }"
                placeholder="0"
                min="1"
                x-model="targetPorsi"
                required
                style="padding-right: 3.5rem;"
              >
              <div style="position:absolute; right:1rem; font-size:0.8rem; font-weight:600; color:var(--text-secondary);">Porsi</div>
            </div>
            <div class="form-error" x-show="errors.target_porsi" x-text="errors.target_porsi"></div>
          </div>

          <div class="form-group">
            <label class="form-label" for="tanggal_produksi">Tanggal Produksi <span class="required">*</span></label>
            <input
              type="date"
              id="tanggal_produksi"
              name="tanggal_produksi"
              class="form-control"
              :class="{ 'is-invalid': errors.tanggal_produksi }"
              x-model="tanggalProduksi"
              required
            >
            <div class="form-error" x-show="errors.tanggal_produksi" x-text="errors.tanggal_produksi"></div>
          </div>
        </div>

        <!-- Tim Produksi & Catatan -->
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="tim_produksi">Tim Produksi <span class="required">*</span></label>
            <select
              id="tim_produksi"
              name="tim_produksi"
              class="form-select"
              :class="{ 'is-invalid': errors.tim_produksi }"
              x-model="timProduksi"
              required
            >
              <option value="">-- Pilih Tim Pelaksana --</option>
              <?php foreach ($teams as $team): ?>
              <option value="<?= esc($team) ?>"><?= esc($team) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-error" x-show="errors.tim_produksi" x-text="errors.tim_produksi"></div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="catatan">Catatan / Intruksi Khusus</label>
          <textarea
            id="catatan"
            name="catatan"
            class="form-textarea"
            placeholder="Contoh: Kurangi garam sedikit, pisahkan porsi guru..."
            rows="3"
            x-model="catatan"
          ></textarea>
        </div>

        <!-- Form Actions -->
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--border-subtle); margin-top: 0.5rem;">
          <a href="<?= base_url('/produksi') ?>" class="btn btn-secondary">Batal</a>
          <button
            type="submit"
            class="btn btn-primary"
            :class="{ 'loading': isLoading }"
            :disabled="isLoading"
          >
            <span x-show="!isLoading">
              <i data-lucide="play" style="width:14px; height:14px; margin-right:4px;"></i>
              Mulai Produksi (Persiapan)
            </span>
            <span x-show="isLoading">Memproses...</span>
          </button>
        </div>
      </div>

      <!-- ══ RIGHT COLUMN: LIVE MATERIAL CONSUMPTION PANEL ══ -->
      <div class="card" style="display: flex; flex-direction: column; gap: 1.25rem; background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-card-hover) 100%); min-height: 380px;">
        <div class="card-header" style="padding: 0; margin-bottom: 0.5rem;">
          <h3 class="card-title">Estimasi Kebutuhan Bahan</h3>
          <span style="font-size: 0.8rem; color: var(--text-muted);">Akumulasi bahan baku yang akan dikurangi dari stok</span>
        </div>

        <!-- Dynamic Content -->
        <div style="flex: 1; display: flex; flex-direction: column;">
          
          <!-- When No Recipe Selected -->
          <div
            x-show="!selectedResepId"
            style="margin: auto; text-align: center; padding: 2rem; color: var(--text-muted);"
          >
            <i data-lucide="calculator" style="width:48px; height:48px; opacity:0.3; margin-bottom:1rem; display:inline-block;"></i>
            <p style="font-size:0.85rem;">Pilih resep menu dan isi target porsi untuk menghitung kalkulasi bahan baku otomatis.</p>
          </div>

          <!-- When Recipe Selected -->
          <div x-show="selectedResepId" style="display:flex; flex-direction:column; gap:1rem;">
            
            <!-- Summary Info -->
            <div style="background:var(--bg-primary); border:1px solid var(--border-subtle); padding:0.75rem 1rem; border-radius:var(--border-radius); display:flex; justify-content:space-between; align-items:center;">
              <div>
                <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">Menu Terpilih</div>
                <div style="font-size:0.95rem; font-weight:700; color:var(--emerald);" x-text="recipeName"></div>
              </div>
              <div style="text-align:right;">
                <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">Total Volume</div>
                <div style="font-size:0.95rem; font-weight:700; color:var(--text-primary);">
                  <span x-text="targetPorsi || 0"></span> Porsi
                </div>
              </div>
            </div>

            <!-- Materials List -->
            <div style="display:flex; flex-direction:column; gap:0.5rem; max-height:280px; overflow-y:auto; padding-right:0.25rem;">
              <template x-for="(ing, idx) in estimatedIngredients" :key="idx">
                <div style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-primary); padding:0.625rem 0.875rem; border-radius:var(--border-radius-sm); border:1px solid var(--border-subtle);">
                  <div>
                    <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);" x-text="ing.nama"></span>
                    <span style="font-size:0.72rem; color:var(--text-muted); display:block;" x-text="'Standard: ' + ing.qty_per_porsi + ' ' + ing.satuan + ' / porsi'"></span>
                  </div>
                  <div style="text-align:right;">
                    <span style="font-size:1rem; font-weight:800; color:var(--emerald);" x-text="formatQty(ing.total_qty)"></span>
                    <span style="font-size:0.8rem; color:var(--text-muted);" x-text="' ' + ing.satuan"></span>
                  </div>
                </div>
              </template>
            </div>

            <!-- Caution Notice -->
            <div style="display:flex; gap:0.5rem; background:hsla(43, 96%, 56%, 0.1); border:1px solid hsla(43, 96%, 56%, 0.2); padding:0.75rem; border-radius:var(--border-radius); font-size:0.75rem; color:var(--status-warning); line-height:1.4;">
              <i data-lucide="alert-circle" style="width:16px; height:16px; flex-shrink:0;"></i>
              <span>PENTING: Pastikan stok bahan baku di atas mencukupi di gudang sebelum melanjutkan batch produksi.</span>
            </div>

          </div>

        </div>

      </div>

    </div>
  </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function produksiForm() {
    return {
      selectedResepId: '',
      recipeName: '',
      targetPorsi: 350, // default target porsi
      tanggalProduksi: '<?= date('Y-m-d') ?>',
      timProduksi: '',
      catatan: '',
      
      recipes: <?= json_encode($resepList) ?>,
      estimatedIngredients: [],
      isLoading: false,
      errors: {},

      updateSelectedRecipe() {
        const id = parseInt(this.selectedResepId);
        const match = this.recipes.find(r => r.id === id);
        if (match) {
          this.recipeName = match.nama_menu;
        } else {
          this.recipeName = '';
        }
        this.calculateIngredients();
      },

      calculateIngredients() {
        const id = parseInt(this.selectedResepId);
        const match = this.recipes.find(r => r.id === id);
        if (!match || !this.targetPorsi || parseFloat(this.targetPorsi) <= 0) {
          this.estimatedIngredients = [];
          return;
        }

        const portions = parseFloat(this.targetPorsi);
        this.estimatedIngredients = match.ingredients.map(ing => {
          // Calculation: qty per portion * portions target
          const total = ing.qty_per_porsi * portions;
          return {
            nama: ing.nama,
            qty_per_porsi: ing.qty_per_porsi,
            satuan: ing.satuan,
            total_qty: total
          };
        });
        
        this.$nextTick(() => {
          if (typeof lucide !== 'undefined') lucide.createIcons();
        });
      },

      formatQty(val) {
        return parseFloat(val).toLocaleString('id-ID', {
          minimumFractionDigits: 0,
          maximumFractionDigits: 3
        });
      },

      submitForm(e) {
        this.errors = {};
        let valid = true;

        if (!this.selectedResepId) {
          this.errors.resep_id = 'Pilih resep menu makanan.';
          valid = false;
        }
        if (!this.targetPorsi || parseInt(this.targetPorsi) < 1) {
          this.errors.target_porsi = 'Target porsi minimal 1.';
          valid = false;
        }
        if (!this.tanggalProduksi) {
          this.errors.tanggal_produksi = 'Tanggal produksi wajib diisi.';
          valid = false;
        }
        if (!this.timProduksi) {
          this.errors.tim_produksi = 'Pilih tim pelaksana produksi.';
          valid = false;
        }

        if (!valid) return;

        this.isLoading = true;
        e.target.submit();
      }
    };
  }

  // Watch for target porsi input changes manually to sync
  document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('target_porsi');
    if (input) {
      input.addEventListener('input', function() {
        const alpineEl = document.querySelector('[x-data="produksiForm()"]');
        if (alpineEl && alpineEl.__x) {
          alpineEl.__x.$data.targetPorsi = this.value;
          alpineEl.__x.$data.calculateIngredients();
        }
      });
    }
  });
</script>
<?= $this->endSection() ?>
