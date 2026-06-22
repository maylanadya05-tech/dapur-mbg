<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
// Fallback bahan baku list for the BOM dropdown
$bahanBakuList = $bahanBakuList ?? [
  ['id' => 1, 'kode' => 'BH-001', 'nama' => 'Beras Premium', 'satuan' => 'kg', 'harga_per_satuan' => 12500],
  ['id' => 2, 'kode' => 'BH-002', 'nama' => 'Ayam Fillet', 'satuan' => 'kg', 'harga_per_satuan' => 45000],
  ['id' => 3, 'kode' => 'BH-003', 'nama' => 'Minyak Goreng', 'satuan' => 'liter', 'harga_per_satuan' => 18000],
  ['id' => 4, 'kode' => 'BH-004', 'nama' => 'Sayur Bayam', 'satuan' => 'kg', 'harga_per_satuan' => 8000],
  ['id' => 5, 'kode' => 'BH-005', 'nama' => 'Gula Pasir', 'satuan' => 'kg', 'harga_per_satuan' => 14000],
  ['id' => 6, 'kode' => 'BH-006', 'nama' => 'Telur Ayam', 'satuan' => 'butir', 'harga_per_satuan' => 1800],
  ['id' => 7, 'kode' => 'BH-007', 'nama' => 'Garam Dapur', 'satuan' => 'kg', 'harga_per_satuan' => 3500],
  ['id' => 8, 'kode' => 'BH-008', 'nama' => 'Kacang Panjang', 'satuan' => 'kg', 'harga_per_satuan' => 9000],
  ['id' => 9, 'kode' => 'BH-009', 'nama' => 'Bawang Putih', 'satuan' => 'kg', 'harga_per_satuan' => 32000],
  ['id' => 10, 'kode' => 'BH-010', 'nama' => 'Bawang Merah', 'satuan' => 'kg', 'harga_per_satuan' => 35000],
];

// Generate recipe code
$nextKode = $nextKode ?? 'RSP-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Tambah Resep Baru</h1>
    <p class="page-subtitle">Buat menu makanan baru dan tentukan formula bahan bakunya</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/resep') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<form
  action="<?= base_url('/resep/store') ?>"
  method="POST"
  enctype="multipart/form-data"
  x-data="resepForm()"
  @submit.prevent="submitForm"
  novalidate
>
  <?= csrf_field() ?>

  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">
    
    <!-- ══ LEFT COLUMN: RECIPE METADATA ══ -->
    <div class="card" style="display: flex; flex-direction: column; gap: 1.25rem;">
      <div class="card-header" style="padding: 0; margin-bottom: 0.5rem;">
        <h3 class="card-title">Informasi Menu & Gizi</h3>
        <span style="font-size: 0.8rem; color: var(--text-muted);">Data dasar dan kandungan gizi</span>
      </div>

      <!-- Kode & Kategori -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="kode">Kode Resep <span class="required">*</span></label>
          <input
            type="text"
            id="kode"
            name="kode"
            class="form-control"
            value="<?= esc($nextKode) ?>"
            readonly
            style="background: var(--bg-primary); font-family: monospace; color: var(--emerald);"
          >
        </div>
        <div class="form-group">
          <label class="form-label" for="kategori">Kategori <span class="required">*</span></label>
          <select
            id="kategori"
            name="kategori"
            class="form-select"
            :class="{ 'is-invalid': errors.kategori }"
            x-model="kategori"
            required
          >
            <option value="">-- Pilih Kategori --</option>
            <option value="Makanan Pokok">Makanan Pokok</option>
            <option value="Lauk Pauk">Lauk Pauk</option>
            <option value="Sayuran">Sayuran</option>
            <option value="Buah">Buah</option>
            <option value="Minuman">Minuman</option>
          </select>
          <div class="form-error" x-show="errors.kategori" x-text="errors.kategori"></div>
        </div>
      </div>

      <!-- Nama Menu -->
      <div class="form-group">
        <label class="form-label" for="nama_menu">Nama Menu <span class="required">*</span></label>
        <input
          type="text"
          id="nama_menu"
          name="nama_menu"
          class="form-control"
          :class="{ 'is-invalid': errors.nama_menu }"
          placeholder="Contoh: Nasi Goreng Ayam Suwir"
          x-model="namaMenu"
          required
        >
        <div class="form-error" x-show="errors.nama_menu" x-text="errors.nama_menu"></div>
      </div>

      <!-- Deskripsi -->
      <div class="form-group">
        <label class="form-label" for="deskripsi">Deskripsi</label>
        <textarea
          id="deskripsi"
          name="deskripsi"
          class="form-textarea"
          placeholder="Tulis penjelasan singkat mengenai menu, porsi, atau cara penyajian..."
          rows="3"
          x-model="deskripsi"
        ></textarea>
      </div>

      <!-- Porsi Standar -->
      <div class="form-group">
        <label class="form-label" for="porsi_standar">Porsi Standar Resep (BOM untuk berapa porsi?) <span class="required">*</span></label>
        <div class="input-group">
          <input
            type="number"
            id="porsi_standar"
            name="porsi_standar"
            class="form-control"
            :class="{ 'is-invalid': errors.porsi_standar }"
            min="1"
            x-model="porsiStandar"
            required
          >
          <div style="position:absolute; right:1rem; font-size:0.8rem; font-weight:600; color:var(--text-secondary);">Porsi</div>
        </div>
        <div class="form-hint">Disarankan 1 porsi agar komposisi BOM diinput dalam satuan per porsi.</div>
        <div class="form-error" x-show="errors.porsi_standar" x-text="errors.porsi_standar"></div>
      </div>

      <!-- Gizi Row (Kalori, Protein, Karbohidrat) -->
      <div>
        <label class="form-label" style="margin-bottom: 0.5rem; display: block; font-weight: 600;">Kandungan Gizi (Per Porsi)</label>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;">
          <div class="form-group">
            <label class="form-label" for="total_kalori" style="font-size:0.75rem; color:var(--text-secondary);">Kalori (kcal)</label>
            <input
              type="number"
              step="0.1"
              id="total_kalori"
              name="total_kalori"
              class="form-control"
              placeholder="0.0"
              x-model="totalKalori"
            >
          </div>
          <div class="form-group">
            <label class="form-label" for="total_protein" style="font-size:0.75rem; color:var(--text-secondary);">Protein (gram)</label>
            <input
              type="number"
              step="0.1"
              id="total_protein"
              name="total_protein"
              class="form-control"
              placeholder="0.0"
              x-model="totalProtein"
            >
          </div>
          <div class="form-group">
            <label class="form-label" for="total_karbohidrat" style="font-size:0.75rem; color:var(--text-secondary);">Karbohidrat (g)</label>
            <input
              type="number"
              step="0.1"
              id="total_karbohidrat"
              name="total_karbohidrat"
              class="form-control"
              placeholder="0.0"
              x-model="totalKarbohidrat"
            >
          </div>
        </div>
      </div>

      <!-- Foto Upload -->
      <div class="form-group">
        <label class="form-label" for="foto">Foto Menu</label>
        <input
          type="file"
          id="foto"
          name="foto"
          class="form-control"
          accept="image/*"
          style="padding: 0.5rem;"
        >
        <div class="form-hint">Upload gambar berformat JPG, PNG atau WebP (Maks. 2MB).</div>
      </div>
    </div>

    <!-- ══ RIGHT COLUMN: DYNAMIC BOM / INGREDIENTS ══ -->
    <div class="card" style="display: flex; flex-direction: column; gap: 1.25rem;">
      <div class="card-header" style="padding: 0; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <h3 class="card-title">Formula Bahan Baku (BOM)</h3>
          <span style="font-size: 0.8rem; color: var(--text-muted);">Bahan baku yang dibutuhkan per <strong style="color:var(--text-primary);" x-text="porsiStandar"></strong> porsi</span>
        </div>
        <button
          type="button"
          class="btn btn-secondary btn-sm"
          @click="addIngredient"
          style="border-color: var(--emerald-dim); color: var(--emerald);"
        >
          <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
          Tambah Bahan
        </button>
      </div>

      <!-- BOM Rows Container -->
      <div style="display: flex; flex-direction: column; gap: 0.875rem; max-height: 520px; overflow-y: auto; padding-right: 0.25rem;">
        
        <!-- Empty State in BOM -->
        <template x-if="ingredients.length === 0">
          <div style="text-align: center; padding: 3rem 1.5rem; border: 2px dashed var(--border-subtle); border-radius: var(--border-radius); background: var(--bg-primary);">
            <i data-lucide="info" style="width: 32px; height: 32px; color: var(--text-muted); opacity: 0.5; margin-bottom: 0.5rem; display: inline-block;"></i>
            <h4 style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Belum ada bahan baku</h4>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1rem;">Resep harus memiliki minimal satu bahan baku.</p>
            <button type="button" class="btn btn-secondary btn-sm" @click="addIngredient">
              Tambahkan Bahan Pertama
            </button>
          </div>
        </template>

        <!-- Repeatable Row -->
        <template x-for="(item, index) in ingredients" :key="index">
          <div
            style="display: grid; grid-template-columns: 1fr auto 100px 36px; gap: 0.5rem; align-items: start; background: var(--bg-primary); padding: 0.75rem; border-radius: var(--border-radius-sm); border: 1px solid var(--border-subtle); position: relative;"
          >
            <!-- Select Bahan Baku -->
            <div class="form-group" style="margin-bottom: 0;">
              <select
                :name="'ingredients['+index+'][bahan_baku_id]'"
                class="form-select"
                x-model="item.bahan_baku_id"
                @change="updateSatuan(index)"
                required
              >
                <option value="">-- Bahan Baku --</option>
                <template x-for="bb in bahanBakuList" :key="bb.id">
                  <option :value="bb.id" x-text="'[' + bb.kode + '] ' + bb.nama"></option>
                </template>
              </select>
            </div>

            <!-- Qty -->
            <div class="form-group" style="margin-bottom: 0;">
              <input
                type="number"
                step="0.001"
                class="form-control"
                placeholder="Qty"
                x-model="item.qty_per_porsi"
                :name="'ingredients['+index+'][qty_per_porsi]'"
                required
                style="text-align: right;"
              >
            </div>

            <!-- Satuan (Auto Populated) -->
            <div style="display: flex; align-items: center; height: 38px; padding: 0 0.5rem; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--border-radius-sm); font-size: 0.8rem; color: var(--text-secondary); overflow: hidden; white-space: nowrap;">
              <span x-text="item.satuan || 'Satuan'"></span>
              <input type="hidden" :name="'ingredients['+index+'][satuan]'" x-model="item.satuan">
            </div>

            <!-- Action Delete Row -->
            <button
              type="button"
              class="btn btn-danger btn-icon"
              @click="removeIngredient(index)"
              style="width: 36px; height: 38px; border-radius: var(--border-radius-sm); display: flex; align-items: center; justify-content: center; padding:0;"
              title="Hapus"
            >
              <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
            </button>

            <!-- Keterangan (Spans full width on next row) -->
            <div style="grid-column: span 4; margin-top: 0.25rem;">
              <input
                type="text"
                class="form-control form-control-sm"
                placeholder="Catatan porsi / potongan (opsional) — contoh: potong dadu kecil"
                x-model="item.keterangan"
                :name="'ingredients['+index+'][keterangan]'"
                style="font-size: 0.78rem; padding: 0.25rem 0.75rem; background: var(--bg-card-hover);"
              >
            </div>
          </div>
        </template>
        
      </div>
      <div class="form-error" x-show="errors.ingredients" x-text="errors.ingredients" style="margin-top: -0.5rem;"></div>

      <!-- ── Form Actions ── -->
      <div style="display: flex; gap: 0.75rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--border-subtle); margin-top: auto;">
        <a href="<?= base_url('/resep') ?>" class="btn btn-secondary">
          <i data-lucide="x"></i>
          Batal
        </a>
        <button
          type="submit"
          class="btn btn-primary"
          :class="{ 'loading': isLoading }"
          :disabled="isLoading"
        >
          <span x-show="!isLoading">
            <i data-lucide="save"></i>
            Simpan Resep Menu
          </span>
          <span x-show="isLoading">Menyimpan...</span>
        </button>
      </div>

    </div>

  </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function resepForm() {
    return {
      kategori: '',
      namaMenu: '',
      deskripsi: '',
      porsiStandar: 1,
      totalKalori: '',
      totalProtein: '',
      totalKarbohidrat: '',
      
      ingredients: [],
      
      bahanBakuList: <?= json_encode($bahanBakuList) ?>,
      isLoading: false,
      errors: {},

      init() {
        // Add one default ingredient row
        this.addIngredient();
      },

      addIngredient() {
        this.ingredients.push({
          bahan_baku_id: '',
          qty_per_porsi: '',
          satuan: '',
          keterangan: ''
        });
        this.$nextTick(() => {
          if (typeof lucide !== 'undefined') lucide.createIcons();
        });
      },

      removeIngredient(index) {
        this.ingredients.splice(index, 1);
      },

      updateSatuan(index) {
        const item = this.ingredients[index];
        const selectedId = parseInt(item.bahan_baku_id);
        const match = this.bahanBakuList.find(b => b.id === selectedId);
        item.satuan = match ? match.satuan : '';
      },

      submitForm(e) {
        this.errors = {};
        let valid = true;

        if (!this.kategori) {
          this.errors.kategori = 'Kategori menu harus dipilih.';
          valid = false;
        }
        if (!this.namaMenu.trim()) {
          this.errors.nama_menu = 'Nama menu tidak boleh kosong.';
          valid = false;
        }
        if (!this.porsiStandar || parseInt(this.porsiStandar) < 1) {
          this.errors.porsi_standar = 'Porsi standar minimal 1.';
          valid = false;
        }

        // Validate ingredients
        if (this.ingredients.length === 0) {
          this.errors.ingredients = 'Resep harus memiliki minimal 1 komposisi bahan baku.';
          valid = false;
        } else {
          let hasIncomplete = false;
          this.ingredients.forEach(item => {
            if (!item.bahan_baku_id || !item.qty_per_porsi || parseFloat(item.qty_per_porsi) <= 0) {
              hasIncomplete = true;
            }
          });
          if (hasIncomplete) {
            this.errors.ingredients = 'Semua bahan baku dan jumlah kuantitas harus diisi dengan benar.';
            valid = false;
          }
        }

        if (!valid) {
          // Scroll to top of card or error
          window.scrollTo({ top: 100, behavior: 'smooth' });
          return;
        }

        this.isLoading = true;
        e.target.submit();
      }
    };
  }
</script>
<?= $this->endSection() ?>
