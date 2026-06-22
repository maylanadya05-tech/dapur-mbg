<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
// Fallback suppliers list
$supplierList = $supplierList ?? [
  ['id' => 1, 'nama' => 'PT Beras Cianjur'],
  ['id' => 2, 'nama' => 'CV Protein Prima'],
  ['id' => 3, 'nama' => 'CV Minyak Murni'],
  ['id' => 4, 'nama' => 'UD Agro Segar'],
  ['id' => 5, 'nama' => 'PT Gulaku'],
];

// Fallback ingredients list
$bahanBakuList = $bahanBakuList ?? [
  ['id' => 1, 'kode' => 'BH-001', 'nama' => 'Beras Premium', 'satuan' => 'kg', 'harga_per_satuan' => 12500, 'supplier_id' => 1],
  ['id' => 2, 'kode' => 'BH-002', 'nama' => 'Ayam Fillet', 'satuan' => 'kg', 'harga_per_satuan' => 45000, 'supplier_id' => 2],
  ['id' => 3, 'kode' => 'BH-003', 'nama' => 'Minyak Goreng', 'satuan' => 'liter', 'harga_per_satuan' => 18000, 'supplier_id' => 3],
  ['id' => 4, 'kode' => 'BH-004', 'nama' => 'Sayur Bayam', 'satuan' => 'kg', 'harga_per_satuan' => 8000, 'supplier_id' => 4],
  ['id' => 5, 'kode' => 'BH-005', 'nama' => 'Gula Pasir', 'satuan' => 'kg', 'harga_per_satuan' => 14000, 'supplier_id' => 5],
  ['id' => 6, 'kode' => 'BH-006', 'nama' => 'Telur Ayam', 'satuan' => 'butir', 'harga_per_satuan' => 1800, 'supplier_id' => 2],
  ['id' => 7, 'kode' => 'BH-007', 'nama' => 'Garam Dapur', 'satuan' => 'kg', 'harga_per_satuan' => 3500, 'supplier_id' => 5],
  ['id' => 8, 'kode' => 'BH-008', 'nama' => 'Kacang Panjang', 'satuan' => 'kg', 'harga_per_satuan' => 9000, 'supplier_id' => 4],
];
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Buat Purchase Order Baru</h1>
    <p class="page-subtitle">Buat pengajuan pembelian bahan baku ke supplier dengan perhitungan harga otomatis</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/pembelian') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<form
  action="<?= base_url('/pembelian/store') ?>"
  method="POST"
  x-data="poForm()"
  @submit.prevent="submitForm"
  novalidate
>
  <?= csrf_field() ?>

  <div style="display: flex; flex-direction: column; gap: 1.5rem;">
    
    <!-- ══ SECTION 1: PO HEADER METADATA ══ -->
    <div class="card" style="display: flex; flex-direction: column; gap: 1.25rem;">
      <div class="card-header" style="padding:0; margin-bottom:0.25rem;">
        <h3 class="card-title">Informasi Pengajuan & Supplier</h3>
        <span style="font-size:0.8rem; color:var(--text-muted);">Tentukan supplier tujuan dan batas tanggal pengiriman</span>
      </div>

      <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 1.5rem; align-items: start;">
        
        <!-- Left Sub-column -->
        <div style="display:flex; flex-direction:column; gap:1.25rem; width:100%;">
          <div class="form-row">
            <!-- Supplier Select -->
            <div class="form-group">
              <label class="form-label" for="supplier_id">Supplier Tujuan <span class="required">*</span></label>
              <select
                id="supplier_id"
                name="supplier_id"
                class="form-select"
                :class="{ 'is-invalid': errors.supplier_id }"
                x-model="supplierId"
                @change="onSupplierChange"
                required
              >
                <option value="">-- Pilih Supplier --</option>
                <?php foreach ($supplierList as $sup): ?>
                <option value="<?= $sup['id'] ?>"><?= esc($sup['nama']) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-error" x-show="errors.supplier_id" x-text="errors.supplier_id"></div>
            </div>

            <!-- Required Date -->
            <div class="form-group">
              <label class="form-label" for="tanggal_dibutuhkan">Tanggal Dibutuhkan <span class="required">*</span></label>
              <input
                type="date"
                id="tanggal_dibutuhkan"
                name="tanggal_dibutuhkan"
                class="form-control"
                :class="{ 'is-invalid': errors.tanggal_dibutuhkan }"
                x-model="tanggalDibutuhkan"
                required
              >
              <div class="form-error" x-show="errors.tanggal_dibutuhkan" x-text="errors.tanggal_dibutuhkan"></div>
            </div>
          </div>
        </div>

        <!-- Right Sub-column -->
        <div class="form-group" style="width:100%;">
          <label class="form-label" for="catatan">Catatan Tambahan / Keterangan</label>
          <textarea
            id="catatan"
            name="catatan"
            class="form-textarea"
            placeholder="Catatan porsi pengiriman, instruksi packing, atau no. penawaran harga..."
            rows="3"
            x-model="catatan"
          ></textarea>
        </div>

      </div>
    </div>

    <!-- ══ SECTION 2: DYNAMIC PO ITEMS TABLE ══ -->
    <div class="card" style="padding: 0;">
      
      <div class="card-header" style="padding: var(--card-padding) var(--card-padding) 0; display:flex; justify-content:space-between; align-items:center;">
        <div>
          <h3 class="card-title">Daftar Bahan Baku yang Dipesan</h3>
          <span style="font-size:0.8rem; color:var(--text-muted);">Pilih item bahan baku, masukkan kuantitas dan harga satuan</span>
        </div>
        <button
          type="button"
          class="btn btn-secondary btn-sm"
          @click="addItem"
          style="border-color: var(--emerald-dim); color: var(--emerald);"
        >
          <i data-lucide="plus" style="width:14px; height:14px; margin-right:4px;"></i>
          Tambah Item
        </button>
      </div>

      <!-- Table Wrapper -->
      <div class="table-wrapper" style="border:none; border-radius:0; margin-top:1.25rem;">
        <table class="data-table">
          <thead>
            <tr>
              <th width="40">No</th>
              <th>Item Bahan Baku <span class="required">*</span></th>
              <th width="100">Satuan</th>
              <th width="150" style="text-align: right;">Kuantitas <span class="required">*</span></th>
              <th width="180" style="text-align: right;">Harga Satuan (Rp) <span class="required">*</span></th>
              <th width="180" style="text-align: right;">Subtotal (Rp)</th>
              <th width="50" style="text-align: center;">Hapus</th>
            </tr>
          </thead>
          <tbody>
            <!-- Empty State -->
            <template x-if="items.length === 0">
              <tr>
                <td colspan="7" style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                  <i data-lucide="shopping-basket" style="width:32px; height:32px; opacity:0.3; margin-bottom:0.5rem; display:inline-block;"></i>
                  <p style="font-size:0.85rem; margin-bottom:0.75rem;">Belum ada item pesanan ditambahkan.</p>
                  <button type="button" class="btn btn-secondary btn-sm" @click="addItem">
                    Tambahkan Item Pertama
                  </button>
                </td>
              </tr>
            </template>

            <!-- Repeatable Rows -->
            <template x-for="(item, index) in items" :key="index">
              <tr>
                <td><span x-text="index + 1"></span></td>
                
                <!-- Select Bahan Baku -->
                <td>
                  <select
                    :name="'items['+index+'][bahan_baku_id]'"
                    class="form-select form-select-sm"
                    x-model="item.bahan_baku_id"
                    @change="onItemChange(index)"
                    required
                  >
                    <option value="">-- Pilih Bahan --</option>
                    <template x-for="bb in filteredBahanBaku" :key="bb.id">
                      <option :value="bb.id" x-text="'[' + bb.kode + '] ' + bb.nama"></option>
                    </template>
                  </select>
                </td>

                <!-- Satuan -->
                <td>
                  <div style="font-size:0.85rem; color:var(--text-secondary); padding: 0.375rem 0.5rem;" x-text="item.satuan || '-'"></div>
                  <input type="hidden" :name="'items['+index+'][satuan]'" x-model="item.satuan">
                </td>

                <!-- Qty -->
                <td>
                  <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    class="form-control form-control-sm"
                    x-model="item.qty"
                    :name="'items['+index+'][qty]'"
                    @input="calculateSubtotal(index)"
                    required
                    style="text-align: right;"
                    placeholder="0.00"
                  >
                </td>

                <!-- Harga Satuan -->
                <td>
                  <div style="position:relative;">
                    <span style="position:absolute; left:0.6rem; top:50%; transform:translateY(-50%); font-size:0.78rem; color:var(--text-muted);">Rp</span>
                    <input
                      type="number"
                      step="1"
                      min="1"
                      class="form-control form-control-sm"
                      x-model="item.harga_satuan"
                      :name="'items['+index+'][harga_satuan]'"
                      @input="calculateSubtotal(index)"
                      required
                      style="text-align: right; padding-left: 1.8rem;"
                      placeholder="0"
                    >
                  </div>
                </td>

                <!-- Subtotal -->
                <td style="text-align: right; font-weight: 700; color: var(--text-primary); font-size:0.9rem;">
                  Rp <span x-text="formatCurrency(item.subtotal)"></span>
                  <input type="hidden" :name="'items['+index+'][subtotal]'" :value="item.subtotal">
                </td>

                <!-- Remove Row -->
                <td style="text-align: center;">
                  <button
                    type="button"
                    class="btn btn-danger btn-icon btn-sm"
                    @click="removeItem(index)"
                    style="width:30px; height:30px; padding:0; justify-content:center; align-items:center; display:inline-flex;"
                  >
                    <i data-lucide="trash-2" style="width:14px; height:14px;"></i>
                  </button>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Grand Total Bar -->
      <div style="display:flex; justify-content:space-between; align-items:center; padding: 1.25rem 2rem; background: var(--bg-card-hover); border-top: 1px solid var(--border-subtle); border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);">
        <div>
          <span style="font-size:0.8rem; color:var(--text-muted);">PENTING: Pastikan item yang diinput sesuai dengan penawaran harga supplier tertera.</span>
        </div>
        <div style="display:flex; align-items:center; gap:1.5rem; text-align:right;">
          <div>
            <span style="font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); display:block; margin-bottom:0.125rem;">Total Nilai PO</span>
            <strong style="font-size:1.5rem; color:var(--emerald);">Rp <span x-text="formatCurrency(grandTotal)"></span></strong>
            <input type="hidden" name="total_nilai" :value="grandTotal">
          </div>
        </div>
      </div>

    </div>
    <div class="form-error" x-show="errors.items" x-text="errors.items" style="margin-top: -0.5rem; padding-left: 0.5rem;"></div>

    <!-- Submit row -->
    <div style="display:flex; gap:0.75rem; justify-content:flex-end; padding-top:0.5rem;">
      <a href="<?= base_url('/pembelian') ?>" class="btn btn-secondary">Batal</a>
      <button
        type="submit"
        class="btn btn-primary"
        :class="{ 'loading': isLoading }"
        :disabled="isLoading"
      >
        <span x-show="!isLoading">
          <i data-lucide="send" style="width:14px; height:14px; margin-right:4px;"></i>
          Kirim Pengajuan PO
        </span>
        <span x-show="isLoading">Mengirim...</span>
      </button>
    </div>

  </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function poForm() {
    return {
      supplierId: '',
      tanggalDibutuhkan: '',
      catatan: '',
      items: [],
      
      bahanBakuList: <?= json_encode($bahanBakuList) ?>,
      filteredBahanBaku: [],
      grandTotal: 0,
      isLoading: false,
      errors: {},

      init() {
        this.addItem();
      },

      onSupplierChange() {
        const id = parseInt(this.supplierId);
        if (!id) {
          this.filteredBahanBaku = [];
          this.items = [];
          this.calculateGrandTotal();
          return;
        }

        // Filter bahan baku by selected supplier. If bahan baku's supplier_id is null or matches the supplier, show it.
        this.filteredBahanBaku = this.bahanBakuList.filter(b => !b.supplier_id || b.supplier_id === id);

        // Reset items to prevent cross-supplier item submission
        this.items = [];
        this.addItem();
        this.calculateGrandTotal();
      },

      addItem() {
        this.items.push({
          bahan_baku_id: '',
          satuan: '',
          qty: '',
          harga_satuan: '',
          subtotal: 0
        });
        this.$nextTick(() => {
          if (typeof lucide !== 'undefined') lucide.createIcons();
        });
      },

      removeItem(index) {
        this.items.splice(index, 1);
        this.calculateGrandTotal();
      },

      onItemChange(index) {
        const item = this.items[index];
        const selectedId = parseInt(item.bahan_baku_id);
        const match = this.bahanBakuList.find(b => b.id === selectedId);
        
        if (match) {
          item.satuan = match.satuan;
          item.harga_satuan = match.harga_per_satuan;
          // default quantity as 1 or empty
          if (!item.qty) item.qty = 1;
        } else {
          item.satuan = '';
          item.harga_satuan = '';
          item.qty = '';
        }
        this.calculateSubtotal(index);
      },

      calculateSubtotal(index) {
        const item = this.items[index];
        const qty = parseFloat(item.qty || 0);
        const price = parseFloat(item.harga_satuan || 0);
        item.subtotal = qty * price;
        this.calculateGrandTotal();
      },

      calculateGrandTotal() {
        this.grandTotal = this.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
      },

      formatCurrency(val) {
        return parseFloat(val || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
      },

      submitForm(e) {
        this.errors = {};
        let valid = true;

        if (!this.supplierId) {
          this.errors.supplier_id = 'Pilih supplier tujuan.';
          valid = false;
        }
        if (!this.tanggalDibutuhkan) {
          this.errors.tanggal_dibutuhkan = 'Tentukan batas tanggal pengiriman PO.';
          valid = false;
        }

        if (this.items.length === 0) {
          this.errors.items = 'Masukkan minimal satu item bahan baku dalam PO.';
          valid = false;
        } else {
          let hasIncomplete = false;
          this.items.forEach(item => {
            if (!item.bahan_baku_id || !item.qty || parseFloat(item.qty) <= 0 || !item.harga_satuan || parseFloat(item.harga_satuan) <= 0) {
              hasIncomplete = true;
            }
          });
          if (hasIncomplete) {
            this.errors.items = 'Semua item, kuantitas, dan harga satuan wajib diisi dengan benar.';
            valid = false;
          }
        }

        if (!valid) return;

        this.isLoading = true;
        e.target.submit();
      }
    };
  }
</script>
<?= $this->endSection() ?>
