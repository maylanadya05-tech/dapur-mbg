<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$invoice = $invoice ?? [
  'id' => 1,
  'no_invoice' => 'INV-2026-001',
  'tanggal' => '2026-06-21',
  'sekolah_nama' => 'SDN Merdeka 01',
  'periode' => '1 Juni - 15 Juni 2026',
  'total_porsi' => 6750,
  'harga_porsi' => 15000,
  'grand_total' => 101250000,
  'status' => 'dibayar', // draft, dikirim, dibayar, jatuh_tempo
  'catatan' => 'Pembayaran ditransfer langsung ke Rekening BJB SPPG Dapur MBG No. 00921-2311-209. Harap kirimkan bukti transfer kepada admin Dapur MBG setelah pembayaran selesai.'
];
?>

<!-- ══ PAGE HEADER (Hidden on Print) ══ -->
<div class="page-header no-print">
  <div class="page-header-left">
    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.25rem;">
      <span class="badge badge-neutral" style="font-family:monospace;font-size:0.8rem;"><?= esc($invoice['no_invoice']) ?></span>
      <?php
        $badgeClass = match($invoice['status']) {
          'dibayar' => 'badge-success',
          'dikirim' => 'badge-info',
          'jatuh_tempo' => 'badge-danger',
          default => 'badge-neutral'
        };
      ?>
      <span class="badge <?= $badgeClass ?>"><?= ucfirst($invoice['status']) ?></span>
    </div>
    <h1 class="page-title">Lembar Invoice Penagihan</h1>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/invoice') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
    
    <!-- Status Toggle Form -->
    <form action="<?= base_url('/invoice/update-status/' . $invoice['id']) ?>" method="POST" style="display:inline-flex;align-items:center;gap:0.5rem;">
      <?= csrf_field() ?>
      <select name="status" class="form-select" onchange="this.form.submit()" style="background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--border-radius-sm); color:var(--text-primary); padding: 0.375rem 1.5rem 0.375rem 0.75rem; font-size: 0.8125rem;">
        <option value="draft" <?= $invoice['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
        <option value="dikirim" <?= $invoice['status'] === 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
        <option value="dibayar" <?= $invoice['status'] === 'dibayar' ? 'selected' : '' ?>>Dibayar (Lunas)</option>
        <option value="jatuh_tempo" <?= $invoice['status'] === 'jatuh_tempo' ? 'selected' : '' ?>>Jatuh Tempo</option>
      </select>
    </form>

    <button onclick="window.print()" class="btn btn-primary btn-sm">
      <i data-lucide="printer"></i>
      Cetak / Simpan PDF
    </button>
  </div>
</div>

<!-- ══ PRINTABLE INVOICE CONTAINER ══ -->
<div class="print-container">
  <div class="card invoice-sheet" style="padding: 3rem; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--border-radius-lg);">
    
    <!-- SPPG Official Letterhead -->
    <div style="display:flex;justify-content:space-between;align-items:start;border-bottom:2px solid var(--border-subtle);padding-bottom:2rem;margin-bottom:2rem;">
      <div>
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem;">
          <div style="width:40px;height:40px;background:var(--emerald);border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:1.2rem;box-shadow:var(--emerald-glow);">M</div>
          <div>
            <h2 style="font-size:1.25rem;font-weight:800;color:var(--text-primary);letter-spacing:-0.02em;">DAPUR MBG BOGOR</h2>
            <span style="font-size:0.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Layanan Makan Bergizi Gratis</span>
          </div>
        </div>
        <p style="font-size:0.8rem;color:var(--text-secondary);max-width:320px;line-height:1.5;margin-top:0.5rem;">
          Jl. Merdeka No. 12, Kel. Babakan, Kec. Bogor Tengah, Kota Bogor, Jawa Barat 16121<br>
          Email: keuangan@dapurmbgbogor.go.id | Telp: (0251) 833-9090
        </p>
      </div>
      <div style="text-align:right;">
        <h1 style="font-size:1.75rem;font-weight:800;color:var(--emerald-light);letter-spacing:-0.03em;margin-bottom:0.5rem;">INVOICE</h1>
        <div style="font-size:0.875rem;color:var(--text-secondary);line-height:1.6;">
          <div>No Invoice: <strong style="color:var(--text-primary);font-family:monospace;"><?= esc($invoice['no_invoice']) ?></strong></div>
          <div>Tanggal Terbit: <strong style="color:var(--text-primary);"><?= date('d F Y', strtotime($invoice['tanggal'])) ?></strong></div>
          <div>Status Tagihan: <strong style="color:var(--emerald);text-transform:uppercase;"><?= esc($invoice['status']) ?></strong></div>
        </div>
      </div>
    </div>

    <!-- Client & Date Details -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-bottom:2.5rem;font-size:0.875rem;line-height:1.6;">
      <div>
        <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;letter-spacing:0.05em;margin-bottom:0.375rem;">Ditagihkan Kepada:</div>
        <div style="font-size:1.05rem;font-weight:800;color:var(--text-primary);"><?= esc($invoice['sekolah_nama']) ?></div>
        <div style="color:var(--text-secondary);margin-top:0.25rem;">
          Penerima Manfaat Program MBG Wilayah Bogor<br>
          Jawa Barat, Indonesia
        </div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;letter-spacing:0.05em;margin-bottom:0.375rem;">Periode Distribusi:</div>
        <div style="font-size:1.05rem;font-weight:700;color:var(--text-primary);"><?= esc($invoice['periode']) ?></div>
        <div style="color:var(--text-secondary);margin-top:0.25rem;">Jatuh Tempo: 14 Hari Kalender Sejak Diterbitkan</div>
      </div>
    </div>

    <!-- Pricing Calculation Table -->
    <table style="width:100%;border-collapse:collapse;margin-bottom:2rem;font-size:0.875rem;text-align:left;">
      <thead>
        <tr style="border-bottom:2px solid var(--border-medium);color:var(--text-muted);">
          <th style="padding:0.75rem 0;font-weight:600;">Deskripsi Produk / Layanan</th>
          <th style="padding:0.75rem 0;text-align:right;font-weight:600;width:120px;">Jumlah Porsi</th>
          <th style="padding:0.75rem 0;text-align:right;font-weight:600;width:140px;">Harga Satuan</th>
          <th style="padding:0.75rem 0;text-align:right;font-weight:600;width:180px;">Total Tagihan</th>
        </tr>
      </thead>
      <tbody>
        <tr style="border-bottom:1px solid var(--border-subtle);color:var(--text-primary);">
          <td style="padding:1.25rem 0;">
            <strong style="color:var(--text-primary);font-size:0.95rem;">Penyediaan Makan Bergizi Gratis (MBG)</strong><br>
            <span style="font-size:0.78rem;color:var(--text-muted);display:block;margin-top:0.25rem;">Porsi makanan sehat berkalori seimbang untuk seluruh siswa terdaftar berdasarkan akumulasi log pengiriman sukses.</span>
          </td>
          <td style="padding:1.25rem 0;text-align:right;font-weight:600;"><?= number_format($invoice['total_porsi']) ?> porsi</td>
          <td style="padding:1.25rem 0;text-align:right;color:var(--text-secondary);">Rp <?= number_format($invoice['harga_porsi'], 0, ',', '.') ?></td>
          <td style="padding:1.25rem 0;text-align:right;font-weight:700;color:var(--text-primary);">Rp <?= number_format($invoice['grand_total'], 0, ',', '.') ?></td>
        </tr>
      </tbody>
    </table>

    <!-- Totals Area -->
    <div style="display:flex;justify-content:space-between;align-items:start;margin-top:2rem;">
      <div style="max-width:360px;font-size:0.8rem;color:var(--text-secondary);line-height:1.6;">
        <strong style="color:var(--text-primary);display:block;margin-bottom:0.25rem;">Ketentuan Pembayaran:</strong>
        <?= esc($invoice['catatan']) ?>
      </div>
      
      <div style="width:280px;font-size:0.875rem;">
        <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;color:var(--text-secondary);">
          <span>Subtotal:</span>
          <span style="color:var(--text-primary);font-weight:600;">Rp <?= number_format($invoice['grand_total'], 0, ',', '.') ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;color:var(--text-secondary);">
          <span>Pajak PPN (0%):</span>
          <span style="color:var(--text-primary);font-weight:600;">Rp 0</span>
        </div>
        <div style="display:flex;justify-content:space-between;border-top:2px solid var(--border-accent);padding-top:0.75rem;margin-top:0.5rem;font-size:1.05rem;font-weight:800;">
          <span style="color:var(--emerald-light);">Jumlah Tagihan:</span>
          <span style="color:var(--emerald-light);">Rp <?= number_format($invoice['grand_total'], 0, ',', '.') ?></span>
        </div>
      </div>
    </div>

    <!-- Letter Signatures (Traditional Style) -->
    <div style="margin-top:4rem;display:flex;justify-content:space-between;align-items:center;font-size:0.85rem;line-height:1.6;text-align:center;">
      <div>
        <div style="color:var(--text-muted);margin-bottom:3rem;">Kepala Sekolah Penerima,</div>
        <div style="font-weight:700;color:var(--text-primary);">( ________________________ )</div>
      </div>
      <div>
        <div style="color:var(--text-muted);margin-bottom:3rem;">Bogor, <?= date('d F Y', strtotime($invoice['tanggal'])) ?><br>Keuangan Dapur MBG,</div>
        <div style="font-weight:700;color:var(--text-primary);">( Ibu Lilik Herawati )</div>
      </div>
    </div>

  </div>
</div>

<!-- Print-specific styles -->
<style>
  @media print {
    /* Hide dashboard controls */
    .no-print,
    .sidebar,
    .topbar,
    .topbar-toggle,
    #mainContent {
      display: none !important;
    }
    body {
      background: #fff !important;
      color: #000 !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    .print-container {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      z-index: 9999;
    }
    .invoice-sheet {
      border: none !important;
      background: transparent !important;
      box-shadow: none !important;
      padding: 0 !important;
      color: #000 !important;
    }
    .invoice-sheet * {
      color: #000 !important;
      text-shadow: none !important;
      box-shadow: none !important;
    }
    /* Set borders to dark for readability in print */
    div, table, tr, th, td {
      border-color: #000 !important;
    }
  }
</style>

<?= $this->endSection() ?>
