<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$batchList = $batchList ?? [
  ['batch_no' => 'BCH-2606-A', 'menu' => 'Nasi Kuning + Ayam Goreng + Sayur Asem'],
  ['batch_no' => 'BCH-2606-B', 'menu' => 'Nasi Putih + Daging Semur + Sop Sayur'],
];

$ingredientList = $ingredientList ?? [
  ['id' => 1, 'kode' => 'BH-001', 'nama' => 'Beras Premium', 'harga_per_unit' => 12500, 'satuan' => 'kg'],
  ['id' => 2, 'kode' => 'BH-002', 'nama' => 'Ayam Fillet', 'harga_per_unit' => 45000, 'satuan' => 'kg'],
  ['id' => 3, 'kode' => 'BH-003', 'nama' => 'Minyak Goreng', 'harga_per_unit' => 18000, 'satuan' => 'liter'],
  ['id' => 4, 'kode' => 'BH-004', 'nama' => 'Sayur Bayam', 'harga_per_unit' => 8000, 'satuan' => 'kg'],
  ['id' => 5, 'kode' => 'BH-005', 'nama' => 'Gula Pasir', 'harga_per_unit' => 14000, 'satuan' => 'kg'],
  ['id' => 6, 'kode' => 'BH-006', 'nama' => 'Telur Ayam', 'harga_per_unit' => 25000, 'satuan' => 'kg'],
];
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Catat Limbah / Food Waste</h1>
    <p class="page-subtitle">Laporkan bahan baku terbuang atau sisa porsi produksi</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/sisa') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div style="max-width: 720px;">
  <div class="card" x-data="wasteForm()">
    <div class="card-header" style="margin-bottom:1.5rem; border-bottom:1px solid var(--border-subtle); padding-bottom:1rem;">
      <h3 class="card-title">Form Laporan Food Waste</h3>
      <p class="card-subtitle">Kerugian finansial akan dihitung secara otomatis berdasarkan harga dasar bahan baku.</p>
    </div>

    <form action="<?= base_url('/sisa/store') ?>" method="POST" @submit.prevent="submitForm">
      <?= csrf_field() ?>

      <!-- ── Category Select ── -->
      <div class="form-group" style="margin-bottom: 1.25rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Kategori Penyebab <span style="color:var(--status-danger);">*</span></label>
        <select
          name="kategori"
          class="form-select"
          x-model="kategori"
          required
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
        >
          <option value="sisa makanan">Sisa Makanan (Porsi berlebih / sisa sekolah)</option>
          <option value="kadaluarsa">Kadaluarsa (Bahan baku membusuk/rusak di gudang)</option>
          <option value="portioning error">Portioning Error (Kesalahan takaran/tumpah saat masak)</option>
          <option value="lainnya">Lainnya</option>
        </select>
      </div>

      <!-- ── Optional Batch No ── -->
      <div class="form-group" style="margin-bottom: 1.25rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Batch Produksi (Opsional)</label>
        <select
          name="batch_no"
          class="form-select"
          x-model="batchNo"
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
        >
          <option value="">-- Tidak Terikat Batch (Limbah Gudang) --</option>
          <?php foreach ($batchList as $batch): ?>
          <option value="<?= $batch['batch_no'] ?>"><?= esc($batch['batch_no']) ?> — <?= esc($batch['menu']) ?></option>
          <?php endforeach; ?>
        </select>
        <p class="form-hint" style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem;">Isi jika limbah terjadi saat proses pengolahan batch tertentu.</p>
      </div>

      <!-- ── Ingredient & Qty ── -->
      <div class="form-row" style="display:grid;grid-template-columns:1.5fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Bahan Baku Terbuang <span style="color:var(--status-danger);">*</span></label>
          <select
            name="bahan_id"
            class="form-select"
            x-model="bahanId"
            @change="onIngredientChange"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
            <option value="">-- Pilih Bahan Baku --</option>
            <?php foreach ($ingredientList as $ing): ?>
            <option value="<?= $ing['id'] ?>" data-price="<?= $ing['harga_per_unit'] ?>" data-unit="<?= esc($ing['satuan']) ?>">[<?= esc($ing['kode']) ?>] <?= esc($ing['nama']) ?> (Rp <?= number_format($ing['harga_per_unit']) ?>/<?= esc($ing['satuan']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Jumlah (Kuantitas)</label>
          <div style="position:relative;">
            <input
              type="number"
              name="qty"
              class="form-control"
              placeholder="0.00"
              step="0.01"
              min="0.01"
              x-model="qty"
              required
              style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding-right:2.5rem;"
            >
            <span style="position:absolute;right:0.875rem;top:50%;transform:translateY(-50%);font-size:0.875rem;color:var(--emerald);font-weight:700;" x-text="satuan">kg</span>
          </div>
        </div>
      </div>

      <!-- ── Explanation / Notes ── -->
      <div class="form-group" style="margin-bottom:1.5rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Penjelasan Penyebab Waste <span style="color:var(--status-danger);">*</span></label>
        <textarea
          name="penjelasan"
          class="form-control"
          placeholder="Tulis alasan detail (contoh: sayur bayam layu karena kulkas mati semalaman)"
          rows="3"
          x-model="penjelasan"
          required
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);font-family:inherit;resize:none;"
        ></textarea>
      </div>

      <!-- Live Loss Preview Box -->
      <div style="background:var(--danger-dim); border:1px solid var(--status-danger); border-radius:var(--border-radius); padding:1.25rem; margin-bottom:1.5rem;" x-show="bahanId && qty > 0">
        <h4 style="font-size:0.8rem;text-transform:uppercase;color:var(--status-danger);letter-spacing:0.08em;margin-bottom:0.25rem;font-weight:700;">Live Estimasi Kerugian Finansial</h4>
        <div style="font-size:1.5rem;color:var(--text-primary);font-weight:800;">
          Rp <span x-text="formatLoss()"></span>
        </div>
        <p style="font-size:0.75rem;color:var(--text-secondary);margin-top:0.25rem;">Perhitungan: <span x-text="qty"></span> <span x-text="satuan"></span> × Rp <span x-text="formatPrice()"></span></p>
      </div>

      <!-- ── Actions ── -->
      <div style="display:flex;justify-content:flex-end;gap:0.75rem;border-top:1px solid var(--border-subtle);padding-top:1.5rem;">
        <a href="<?= base_url('/sampah') ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i data-lucide="trash-2" style="width:18px;height:18px;margin-right:6px;display:inline-block;vertical-align:middle;"></i>
          Simpan Laporan Waste
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function wasteForm() {
    return {
      kategori: 'sisa makanan',
      batchNo: '',
      bahanId: '',
      qty: '',
      price: 0,
      satuan: 'kg',
      penjelasan: '',

      onIngredientChange(e) {
        const select = e.target;
        const opt = select.options[select.selectedIndex];
        if (opt) {
          this.price = parseFloat(opt.dataset.price || 0);
          this.satuan = opt.dataset.unit || 'kg';
        } else {
          this.price = 0;
          this.satuan = 'kg';
        }
      },

      formatPrice() {
        return this.price.toLocaleString('id-ID');
      },

      formatLoss() {
        const total = (parseFloat(this.qty || 0) * this.price);
        return total.toLocaleString('id-ID');
      },

      submitForm(e) {
        e.target.submit();
      }
    };
  }
</script>
<?= $this->endSection() ?>
