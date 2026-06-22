<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$sekolahList = $sekolahList ?? [
  ['id' => 1, 'nama' => 'SDN Merdeka 01', 'porsi_harian' => 450],
  ['id' => 2, 'nama' => 'SMP Negeri 1 Bogor', 'porsi_harian' => 820],
  ['id' => 3, 'nama' => 'SMA Negeri 2 Bogor', 'porsi_harian' => 960],
  ['id' => 4, 'nama' => 'SMK Negeri 1 Bogor', 'porsi_harian' => 1200],
];
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Buat Invoice</h1>
    <p class="page-subtitle">Hitung otomatis porsi yang terkirim pada periode tertentu untuk penagihan sekolah</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/invoice') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1.1fr;gap:2rem;align-items:start;">

  <!-- LEFT: Form Configuration -->
  <div class="card" x-data="invoiceForm()">
    <div class="card-header" style="margin-bottom:1.5rem; border-bottom:1px solid var(--border-subtle); padding-bottom:1rem;">
      <h3 class="card-title">Parameter Penagihan</h3>
      <p class="card-subtitle">Sistem akan menjumlahkan porsi sukses dari surat jalan berstatus 'Selesai'.</p>
    </div>

    <form action="<?= base_url('/invoice/store') ?>" method="POST" @submit.prevent="submitForm">
      <?= csrf_field() ?>

      <!-- ── Select Sekolah ── -->
      <div class="form-group" style="margin-bottom: 1.25rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Sekolah Penerima <span style="color:var(--status-danger);">*</span></label>
        <select
          name="sekolah_id"
          class="form-select"
          x-model="sekolahId"
          @change="calculatePortions"
          required
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
        >
          <option value="">-- Pilih Sekolah --</option>
          <?php foreach ($sekolahList as $sek): ?>
          <option value="<?= $sek['id'] ?>" data-porsi="<?= $sek['porsi_harian'] ?>"><?= esc($sek['nama']) ?> (Harian: <?= $sek['porsi_harian'] ?> porsi)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- ── Date Range ── -->
      <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Tanggal Mulai <span style="color:var(--status-danger);">*</span></label>
          <input
            type="date"
            name="tanggal_mulai"
            class="form-control"
            x-model="startDate"
            @change="calculatePortions"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Tanggal Selesai <span style="color:var(--status-danger);">*</span></label>
          <input
            type="date"
            name="tanggal_selesai"
            class="form-control"
            x-model="endDate"
            @change="calculatePortions"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>
      </div>

      <!-- ── Price per portion ── -->
      <div class="form-group" style="margin-bottom:1.25rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Harga Satuan Per Porsi (Rp) <span style="color:var(--status-danger);">*</span></label>
        <div style="position:relative;">
          <span style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:600;font-size:0.875rem;">Rp</span>
          <input
            type="number"
            name="harga_porsi"
            class="form-control"
            x-model="price"
            @input="calculatePortions"
            required
            style="width:100%;padding:0.625rem 0.875rem 0.625rem 2.5rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>
      </div>

      <!-- ── Notes ── -->
      <div class="form-group" style="margin-bottom:2rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Catatan Invoice</label>
        <textarea
          name="catatan"
          class="form-control"
          placeholder="Syarat pembayaran, nomor rekening bank, atau memo tambahan."
          rows="3"
          x-model="notes"
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);font-family:inherit;resize:none;"
        ></textarea>
      </div>

      <!-- ── Actions ── -->
      <div style="display:flex;justify-content:flex-end;gap:0.75rem;border-top:1px solid var(--border-subtle);padding-top:1.5rem;">
        <a href="<?= base_url('/invoice') ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i data-lucide="file-plus" style="width:18px;height:18px;margin-right:6px;display:inline-block;vertical-align:middle;"></i>
          Simpan & Terbitkan Invoice
        </button>
      </div>
    </form>
  </div>

  <!-- RIGHT: Real-time Invoice Preview Sheet -->
  <div class="card" style="background:var(--bg-card);border:1px solid var(--border-accent);position:sticky;top:80px;" x-data="invoicePreview()">
    <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border-subtle);padding-bottom:1rem;margin-bottom:1rem;">
      <h3 style="font-size:1rem;font-weight:700;color:var(--emerald-light);text-transform:uppercase;letter-spacing:0.05em;">Preview Lembar Tagihan</h3>
      <span class="badge badge-neutral" style="font-size:0.75rem;">DRAFT</span>
    </div>

    <!-- SPPG Letterhead -->
    <div style="border-bottom:2px solid var(--border-subtle);padding-bottom:1rem;margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:start;">
      <div>
        <h4 style="font-size:1.1rem;font-weight:800;color:var(--text-primary);line-height:1.2;">SPPG DAPUR MBG BOGOR</h4>
        <p style="font-size:0.7rem;color:var(--text-muted);max-width:240px;margin-top:0.125rem;">Unit Pelaksana Makan Bergizi Gratis - Jl. Merdeka No. 12, Kota Bogor</p>
      </div>
      <div style="text-align:right;font-size:0.75rem;color:var(--text-muted);">
        <div>Invoice No: <strong>INV-PREVIEW</strong></div>
        <div>Tanggal: <strong><?= date('d/m/Y') ?></strong></div>
      </div>
    </div>

    <!-- Bill to Section -->
    <div style="margin-bottom:1.5rem;font-size:0.8rem;display:grid;grid-template-columns:1fr 1fr;">
      <div>
        <div style="color:var(--text-muted);text-transform:uppercase;font-size:0.68rem;margin-bottom:0.125rem;">Ditagihkan Kepada:</div>
        <div style="font-weight:700;color:var(--text-primary);font-size:0.875rem;" x-text="schoolName || 'Pilih Sekolah...'"></div>
        <div style="color:var(--text-secondary);margin-top:0.25rem;">Kota Bogor, Jawa Barat</div>
      </div>
      <div style="text-align:right;">
        <div style="color:var(--text-muted);text-transform:uppercase;font-size:0.68rem;margin-bottom:0.125rem;">Periode Penagihan:</div>
        <div style="font-weight:600;color:var(--text-primary);" x-text="formatPeriod()"></div>
      </div>
    </div>

    <!-- Breakdown Table -->
    <table style="width:100%;border-collapse:collapse;font-size:0.8rem;text-align:left;margin-bottom:1.5rem;">
      <thead>
        <tr style="border-bottom:1px solid var(--border-subtle);color:var(--text-muted);">
          <th style="padding:0.5rem 0;font-weight:600;">Deskripsi Layanan</th>
          <th style="padding:0.5rem 0;text-align:right;font-weight:600;">Kuantitas</th>
          <th style="padding:0.5rem 0;text-align:right;font-weight:600;">Harga Satuan</th>
          <th style="padding:0.5rem 0;text-align:right;font-weight:600;">Total</th>
        </tr>
      </thead>
      <tbody>
        <tr style="border-bottom:1px solid var(--border-subtle);color:var(--text-primary);">
          <td style="padding:0.75rem 0;">
            <strong>Distribusi Program MBG</strong><br>
            <span style="font-size:0.7rem;color:var(--text-secondary);">Penyediaan porsi masakan bergizi lengkap</span>
          </td>
          <td style="padding:0.75rem 0;text-align:right;" x-text="formatNumber(portions) + ' porsi'">0 porsi</td>
          <td style="padding:0.75rem 0;text-align:right;" x-text="'Rp ' + formatNumber(price)">Rp 15.000</td>
          <td style="padding:0.75rem 0;text-align:right;font-weight:700;" x-text="'Rp ' + formatNumber(subtotal)">Rp 0</td>
        </tr>
      </tbody>
    </table>

    <!-- Grand Totals -->
    <div style="border-top:1px solid var(--border-subtle);padding-top:1rem;margin-left:auto;width:60%;font-size:0.875rem;">
      <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;">
        <span style="color:var(--text-secondary);">Subtotal:</span>
        <strong style="color:var(--text-primary);" x-text="'Rp ' + formatNumber(subtotal)">Rp 0</strong>
      </div>
      <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;">
        <span style="color:var(--text-secondary);">Pajak (0%):</span>
        <strong style="color:var(--text-primary);">Rp 0</strong>
      </div>
      <div style="display:flex;justify-content:space-between;border-top:2px solid var(--border-accent);padding-top:0.75rem;margin-top:0.5rem;font-size:1rem;">
        <span style="color:var(--emerald-light);font-weight:700;">Grand Total:</span>
        <strong style="color:var(--emerald-light);font-weight:800;" x-text="'Rp ' + formatNumber(subtotal)">Rp 0</strong>
      </div>
    </div>

    <!-- Notes Preview -->
    <div style="margin-top:2rem;font-size:0.75rem;color:var(--text-muted);border-top:1px dashed var(--border-subtle);padding-top:1rem;">
      <strong>Catatan Ketentuan:</strong>
      <p style="margin-top:0.25rem;" x-text="notes || 'Belum ada catatan...'"></p>
    </div>
  </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  // Shared state via events or directly
  let invoiceState = {
    schoolName: '',
    startDate: '',
    endDate: '',
    portions: 0,
    price: 15000,
    subtotal: 0,
    notes: 'Pembayaran paling lambat 14 hari kalender setelah invoice ini diterima via transfer Bank Pembangunan Daerah.'
  };

  function invoiceForm() {
    return {
      sekolahId: '',
      startDate: '2026-06-01',
      endDate: '2026-06-15',
      price: 15000,
      notes: invoiceState.notes,

      init() {
        invoiceState.startDate = this.startDate;
        invoiceState.endDate = this.endDate;
        invoiceState.price = this.price;
        this.calculatePortions();
      },

      calculatePortions() {
        const select = document.querySelector('select[name="sekolah_id"]');
        const opt = select.options[select.selectedIndex];
        
        if (opt && opt.value) {
          invoiceState.schoolName = opt.text.split('(')[0].trim();
          const porsiHarian = parseInt(opt.dataset.porsi || 0);
          
          // Let's compute business days between startDate and endDate
          const start = new Date(this.startDate);
          const end = new Date(this.endDate);
          let workingDays = 0;
          
          let current = new Date(start);
          while (current <= end) {
            const day = current.getDay();
            if (day !== 0 && day !== 6) { // Exclude weekends
              workingDays++;
            }
            current.setDate(current.getDate() + 1);
          }
          
          invoiceState.portions = workingDays * porsiHarian;
        } else {
          invoiceState.schoolName = '';
          invoiceState.portions = 0;
        }

        invoiceState.startDate = this.startDate;
        invoiceState.endDate = this.endDate;
        invoiceState.price = parseFloat(this.price || 0);
        invoiceState.subtotal = invoiceState.portions * invoiceState.price;
        invoiceState.notes = this.notes;

        // Dispatch custom event to sync with preview card
        window.dispatchEvent(new CustomEvent('invoice-updated', { detail: invoiceState }));
      },

      submitForm(e) {
        e.target.submit();
      }
    };
  }

  function invoicePreview() {
    return {
      schoolName: '',
      startDate: '',
      endDate: '',
      portions: 0,
      price: 15000,
      subtotal: 0,
      notes: '',

      init() {
        this.schoolName = invoiceState.schoolName;
        this.startDate = invoiceState.startDate;
        this.endDate = invoiceState.endDate;
        this.portions = invoiceState.portions;
        this.price = invoiceState.price;
        this.subtotal = invoiceState.subtotal;
        this.notes = invoiceState.notes;

        window.addEventListener('invoice-updated', (e) => {
          this.schoolName = e.detail.schoolName;
          this.startDate = e.detail.startDate;
          this.endDate = e.detail.endDate;
          this.portions = e.detail.portions;
          this.price = e.detail.price;
          this.subtotal = e.detail.subtotal;
          this.notes = e.detail.notes;
        });
      },

      formatPeriod() {
        if (!this.startDate || !this.endDate) return '-';
        const start = new Date(this.startDate);
        const end = new Date(this.endDate);
        const formatOpt = { day: '2-digit', month: 'short', year: 'numeric' };
        return start.toLocaleDateString('id-ID', formatOpt) + ' - ' + end.toLocaleDateString('id-ID', formatOpt);
      },

      formatNumber(n) {
        return Math.round(n).toLocaleString('id-ID');
      }
    };
  }
</script>
<?= $this->endSection() ?>
