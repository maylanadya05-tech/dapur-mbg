<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
// Bahan baku list for select dropdown (from controller or demo)
$bahanList = $bahanList ?? [
  ['id'=>1,'kode'=>'BH-001','nama_bahan'=>'Beras Premium','satuan'=>'kg'],
  ['id'=>2,'kode'=>'BH-002','nama_bahan'=>'Ayam Fillet','satuan'=>'kg'],
  ['id'=>3,'kode'=>'BH-003','nama_bahan'=>'Minyak Goreng','satuan'=>'liter'],
  ['id'=>4,'kode'=>'BH-004','nama_bahan'=>'Sayur Bayam','satuan'=>'kg'],
  ['id'=>5,'kode'=>'BH-005','nama_bahan'=>'Gula Pasir','satuan'=>'kg'],
  ['id'=>6,'kode'=>'BH-006','nama_bahan'=>'Telur Ayam','satuan'=>'butir'],
  ['id'=>7,'kode'=>'BH-007','nama_bahan'=>'Garam Dapur','satuan'=>'kg'],
  ['id'=>8,'kode'=>'BH-008','nama_bahan'=>'Kacang Panjang','satuan'=>'kg'],
];
$supplierList = $supplierList ?? [
  ['id'=>1,'nama'=>'PT Beras Cianjur'],
  ['id'=>2,'nama'=>'CV Protein Prima'],
  ['id'=>3,'nama'=>'CV Minyak Murni'],
  ['id'=>4,'nama'=>'UD Agro Segar'],
  ['id'=>5,'nama'=>'PT Gulaku'],
  ['id'=>6,'nama'=>'UD Telur Segar'],
  ['id'=>7,'nama'=>'CV Bumbu Nusantara'],
];
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Tambah Pergerakan Stok</h1>
    <p class="page-subtitle">Catat pemasukan atau pengeluaran bahan baku gudang</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/stok') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali ke Stok
    </a>
  </div>
</div>

<!-- ══ FORM CARD ══ -->
<div style="max-width:720px;">
  <div class="card" x-data="stokForm()">

    <div class="card-header" style="margin-bottom:1.5rem;">
      <div>
        <div class="card-title">Form Input Pergerakan Stok</div>
        <div class="card-subtitle">Isi data dengan benar. Tanda <span style="color:var(--status-danger);">*</span> wajib diisi.</div>
      </div>
      <!-- Movement Type Badge -->
      <div>
        <span class="badge" :class="jenis === 'masuk' ? 'badge-success' : 'badge-danger'" x-text="jenis === 'masuk' ? '📦 Stok Masuk' : '📤 Stok Keluar'"></span>
      </div>
    </div>

    <form
      action="<?= base_url('/stok/store') ?>"
      method="POST"
      @submit.prevent="submitForm"
      novalidate
    >
      <?= csrf_field() ?>

      <!-- ── Jenis Pergerakan ── -->
      <div class="form-group">
        <label class="form-label">Jenis Pergerakan <span class="required">*</span></label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">

          <label
            style="display:flex;align-items:center;gap:0.875rem;padding:0.875rem 1rem;border-radius:var(--border-radius);border:2px solid;cursor:pointer;transition:all 0.2s;"
            :style="jenis === 'masuk' ? 'border-color:var(--status-success);background:var(--success-dim);' : 'border-color:var(--border-subtle);background:var(--bg-input);'"
          >
            <input type="radio" name="jenis" value="masuk" x-model="jenis" style="display:none;">
            <div style="width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;"
                 :style="jenis === 'masuk' ? 'background:var(--status-success);' : 'background:var(--bg-card-hover);'">
              <i data-lucide="package-plus" style="width:18px;height:18px;color:white;"></i>
            </div>
            <div>
              <div style="font-size:0.9rem;font-weight:700;" :style="jenis === 'masuk' ? 'color:var(--status-success)' : 'color:var(--text-primary)'">Stok Masuk</div>
              <div style="font-size:0.72rem;color:var(--text-muted);">Penerimaan / pembelian</div>
            </div>
          </label>

          <label
            style="display:flex;align-items:center;gap:0.875rem;padding:0.875rem 1rem;border-radius:var(--border-radius);border:2px solid;cursor:pointer;transition:all 0.2s;"
            :style="jenis === 'keluar' ? 'border-color:var(--status-danger);background:var(--danger-dim);' : 'border-color:var(--border-subtle);background:var(--bg-input);'"
          >
            <input type="radio" name="jenis" value="keluar" x-model="jenis" style="display:none;">
            <div style="width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;"
                 :style="jenis === 'keluar' ? 'background:var(--status-danger);' : 'background:var(--bg-card-hover);'">
              <i data-lucide="package-minus" style="width:18px;height:18px;color:white;"></i>
            </div>
            <div>
              <div style="font-size:0.9rem;font-weight:700;" :style="jenis === 'keluar' ? 'color:var(--status-danger)' : 'color:var(--text-primary)'">Stok Keluar</div>
              <div style="font-size:0.72rem;color:var(--text-muted);">Pemakaian / produksi</div>
            </div>
          </label>

        </div>
        <div class="form-error" x-show="errors.jenis" x-text="errors.jenis"></div>
      </div>

      <!-- ── Divider ── -->
      <div class="divider"></div>

      <!-- ── Row: Bahan Baku + Qty ── -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="bahan_id">
            Bahan Baku <span class="required">*</span>
          </label>
          <select
            id="bahan_id"
            name="bahan_id"
            class="form-select"
            :class="{ 'is-invalid': errors.bahan_id }"
            x-model="selectedBahan"
            @change="updateSatuan"
            required
          >
            <option value="">-- Pilih Bahan Baku --</option>
            <?php foreach ($bahanList as $bahan): ?>
            <option
              value="<?= $bahan['id'] ?>"
              data-satuan="<?= esc($bahan['satuan']) ?>"
              data-kode="<?= esc($bahan['kode']) ?>"
            >
              [<?= esc($bahan['kode']) ?>] <?= esc($bahan['nama_bahan']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <div class="form-error" x-show="errors.bahan_id" x-text="errors.bahan_id"></div>
        </div>

        <div class="form-group">
          <label class="form-label" for="qty">
            Jumlah <span class="required">*</span>
          </label>
          <div class="input-group">
            <input
              type="number"
              id="qty"
              name="qty"
              class="form-control"
              :class="{ 'is-invalid': errors.qty }"
              placeholder="0"
              min="0.01"
              step="0.01"
              x-model="qty"
              @input="clearError('qty')"
              required
              style="padding-right:3.5rem;"
            >
            <div style="position:absolute;right:0.875rem;font-size:0.8rem;font-weight:600;color:var(--emerald);"
                 x-text="satuan || 'unit'"></div>
          </div>
          <div class="form-error" x-show="errors.qty" x-text="errors.qty"></div>
        </div>
      </div>

      <!-- ── Row: Tanggal + No. Referensi ── -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="tanggal">
            Tanggal <span class="required">*</span>
          </label>
          <input
            type="date"
            id="tanggal"
            name="tanggal"
            class="form-control"
            :class="{ 'is-invalid': errors.tanggal }"
            value="<?= date('Y-m-d') ?>"
            x-model="tanggal"
            required
          >
          <div class="form-error" x-show="errors.tanggal" x-text="errors.tanggal"></div>
        </div>

        <div class="form-group">
          <label class="form-label" for="no_referensi">No. Referensi / PO</label>
          <div class="input-group">
            <span class="input-group-icon"><i data-lucide="hash"></i></span>
            <input
              type="text"
              id="no_referensi"
              name="no_referensi"
              class="form-control"
              placeholder="PO-2024-001 (opsional)"
              x-model="noRef"
              style="padding-left:2.5rem;"
            >
          </div>
          <div class="form-hint">Biarkan kosong jika tidak ada referensi.</div>
        </div>
      </div>

      <!-- ── Supplier (hanya untuk stok masuk) ── -->
      <div class="form-group" x-show="jenis === 'masuk'">
        <label class="form-label" for="supplier_id">Supplier / Pemasok</label>
        <select id="supplier_id" name="supplier_id" class="form-select" x-model="supplierId">
          <option value="">-- Pilih Supplier (opsional) --</option>
          <?php foreach ($supplierList as $sup): ?>
          <option value="<?= $sup['id'] ?>"><?= esc($sup['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- ── Keterangan ── -->
      <div class="form-group">
        <label class="form-label" for="keterangan">Keterangan</label>
        <textarea
          id="keterangan"
          name="keterangan"
          class="form-textarea"
          placeholder="Catatan tambahan (opsional) — contoh: untuk produksi batch BCH-003"
          rows="3"
          x-model="keterangan"
        ></textarea>
        <div class="form-hint">
          <span x-text="keterangan.length"></span> / 500 karakter
        </div>
      </div>

      <!-- ── Summary Preview ── -->
      <div
        class="card"
        style="background:var(--bg-card-hover);margin-top:0.5rem;margin-bottom:1.5rem;"
        x-show="selectedBahan && qty > 0"
        x-transition
      >
        <div style="font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.75rem;">
          Ringkasan Pergerakan
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
          <div>
            <div style="font-size:0.72rem;color:var(--text-muted);">Jenis</div>
            <div style="font-weight:700;" :style="jenis === 'masuk' ? 'color:var(--status-success)' : 'color:var(--status-danger)'"
                 x-text="jenis === 'masuk' ? '▲ Masuk' : '▼ Keluar'"></div>
          </div>
          <div>
            <div style="font-size:0.72rem;color:var(--text-muted);">Jumlah</div>
            <div style="font-weight:700;color:var(--text-primary);">
              <span x-text="parseFloat(qty || 0).toLocaleString('id-ID')"></span>
              <span style="color:var(--text-muted);" x-text="' ' + (satuan || 'unit')"></span>
            </div>
          </div>
          <div>
            <div style="font-size:0.72rem;color:var(--text-muted);">Tanggal</div>
            <div style="font-weight:700;color:var(--text-primary);" x-text="formatDate(tanggal)"></div>
          </div>
        </div>
      </div>

      <!-- ── Form Actions ── -->
      <div style="display:flex;gap:0.75rem;justify-content:flex-end;padding-top:0.5rem;border-top:1px solid var(--border-subtle);">
        <a href="<?= base_url('/stok') ?>" class="btn btn-secondary">
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
            Simpan Pergerakan Stok
          </span>
          <span x-show="isLoading">Menyimpan...</span>
        </button>
      </div>

    </form>
  </div><!-- /.card -->
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function stokForm() {
    return {
      jenis: 'masuk',
      selectedBahan: '',
      qty: '',
      satuan: '',
      tanggal: '<?= date('Y-m-d') ?>',
      noRef: '',
      supplierId: '',
      keterangan: '',
      isLoading: false,
      errors: {},

      updateSatuan() {
        const sel = document.getElementById('bahan_id');
        const opt = sel.options[sel.selectedIndex];
        this.satuan = opt?.dataset?.satuan ?? '';
        this.clearError('bahan_id');
        if (typeof lucide !== 'undefined') lucide.createIcons();
      },

      formatDate(d) {
        if (!d) return '-';
        const dt = new Date(d);
        return dt.toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' });
      },

      clearError(field) {
        delete this.errors[field];
      },

      submitForm(e) {
        this.errors = {};
        let valid = true;

        if (!this.jenis) {
          this.errors.jenis = 'Pilih jenis pergerakan.';
          valid = false;
        }
        if (!this.selectedBahan) {
          this.errors.bahan_id = 'Pilih bahan baku.';
          valid = false;
        }
        if (!this.qty || parseFloat(this.qty) <= 0) {
          this.errors.qty = 'Masukkan jumlah yang valid (> 0).';
          valid = false;
        }
        if (!this.tanggal) {
          this.errors.tanggal = 'Tanggal wajib diisi.';
          valid = false;
        }

        if (!valid) return;

        this.isLoading = true;
        e.target.submit();
      },
    };
  }
</script>
<?= $this->endSection() ?>
