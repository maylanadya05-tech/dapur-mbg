<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$session  = session();
$role     = $session->get('user_role') ?? 'viewer';
$isAdmin  = in_array($role, ['admin', 'superadmin', 'pembelian']);

// Fallback PO detail
$po = $po ?? [
  'id' => 1,
  'nomor_po' => 'PO-2026-001',
  'supplier_id' => 1,
  'supplier_name' => 'PT Beras Cianjur',
  'contact_person' => 'Bapak Haji Sofyan',
  'supplier_phone' => '0812-3456-7890',
  'supplier_address' => 'Jl. Raya Cianjur No. 45, Cianjur, Jawa Barat',
  'tanggal_po' => '2026-06-18',
  'tanggal_dibutuhkan' => '2026-06-23',
  'status' => 'diajukan',
  'total_nilai' => 15000000.00,
  'catatan' => 'Beras premium jenis Pandan Wangi kualitas I untuk konsumsi dapur utama. Pengiriman harap dikemas dalam karung 25kg.',
  'alasan_tolak' => null,
  'dibuat_oleh_name' => 'Budi Santoso',
  'disetujui_oleh_name' => null,
  'tanggal_disetujui' => null,
];

// Fallback PO items details
$poItems = $poItems ?? [
  ['nama_bahan' => 'Beras Premium', 'kode_bahan' => 'BH-001', 'qty' => 1200.00, 'satuan' => 'kg', 'harga_satuan' => 12500.00, 'subtotal' => 15000000.00],
];

// Status styling
$status = $po['status'];
$statusBadge = match($status) {
  'diajukan'  => 'badge-warning',
  'disetujui' => 'badge-info',
  'diterima'  => 'badge-success',
  'ditolak'   => 'badge-danger',
  default     => 'badge-neutral',
};
$statusLabel = match($status) {
  'diajukan'  => 'Menunggu Persetujuan',
  'disetujui' => 'Disetujui (PO Terkirim)',
  'diterima'  => 'Diterima & Selesai',
  'ditolak'   => 'Ditolak',
  default     => ucfirst($status),
};
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem;">
      <span style="font-family: monospace; font-size: 0.8rem; background: var(--bg-card); border: 1px solid var(--border-subtle); padding: 0.25rem 0.6rem; border-radius: 4px; color: var(--emerald); font-weight: 600;">
        <?= esc($po['nomor_po']) ?>
      </span>
      <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
    </div>
    <h1 class="page-title">Purchase Order Supplier</h1>
    <p class="page-subtitle">Detail pengajuan pesanan, invoice pengadaan, dan status persetujuan</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/pembelian') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 1.5rem; align-items: start;" x-data="poWorkflow()">
  
  <!-- ══ LEFT COLUMN: SUPPLIER PROFILE & WORKFLOW ACTIONS ══ -->
  <div style="display: flex; flex-direction: column; gap: 1.5rem;">
    
    <!-- PO Header Card -->
    <div class="card" style="display: flex; flex-direction: column; gap: 1rem;">
      <h3 class="card-title">Profil Transaksi</h3>
      
      <div style="display:flex; flex-direction:column; gap:0.75rem; font-size:0.85rem;">
        <div>
          <span style="color:var(--text-muted); font-size:0.7rem; text-transform:uppercase; display:block;">Supplier</span>
          <strong style="color:var(--text-primary); font-size:0.95rem;"><?= esc($po['supplier_name']) ?></strong>
          <span style="color:var(--text-secondary); font-size:0.78rem; display:block; margin-top:2px;">Attn: <?= esc($po['contact_person'] ?? '-') ?></span>
          <span style="color:var(--text-secondary); font-size:0.78rem; display:block;">Telp: <?= esc($po['supplier_phone'] ?? '-') ?></span>
          <span style="color:var(--text-muted); font-size:0.75rem; display:block; margin-top:4px; line-height:1.3;"><?= esc($po['supplier_address'] ?? '-') ?></span>
        </div>

        <div style="height:1px; background:var(--border-subtle); margin:0.25rem 0;"></div>

        <div>
          <span style="color:var(--text-muted); font-size:0.7rem; text-transform:uppercase; display:block;">Tanggal PO</span>
          <strong style="color:var(--text-primary);"><?= date('d F Y', strtotime($po['tanggal_po'])) ?></strong>
        </div>

        <div>
          <span style="color:var(--text-muted); font-size:0.7rem; text-transform:uppercase; display:block;">Tanggal Dibutuhkan</span>
          <strong style="color:var(--status-warning);"><?= date('d F Y', strtotime($po['tanggal_dibutuhkan'])) ?></strong>
        </div>

        <div>
          <span style="color:var(--text-muted); font-size:0.7rem; text-transform:uppercase; display:block;">Diajukan Oleh</span>
          <strong style="color:var(--text-primary);"><?= esc($po['dibuat_oleh_name']) ?></strong>
        </div>

        <?php if ($po['disetujui_oleh_name']): ?>
        <div>
          <span style="color:var(--text-muted); font-size:0.7rem; text-transform:uppercase; display:block;">Penyetuju</span>
          <strong style="color:var(--emerald);"><?= esc($po['disetujui_oleh_name']) ?></strong>
          <span style="color:var(--text-muted); font-size:0.7rem; display:block;"><?= date('d M Y H:i', strtotime($po['tanggal_disetujui'])) ?></span>
        </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($po['catatan'])): ?>
      <div style="background:var(--bg-primary); border:1px solid var(--border-subtle); padding:0.75rem; border-radius:var(--border-radius-sm); font-size:0.8rem; line-height:1.4; margin-top:0.25rem;">
        <span style="display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.25rem;">Catatan Pembelian:</span>
        <span style="color: var(--text-secondary);"><?= esc($po['catatan']) ?></span>
      </div>
      <?php endif; ?>

      <?php if ($status === 'ditolak' && !empty($po['alasan_tolak'])): ?>
      <div style="background:var(--danger-dim); border:1px solid hsla(0, 84%, 60%, 0.2); padding:0.75rem; border-radius:var(--border-radius-sm); font-size:0.8rem; line-height:1.4; color:var(--status-danger);">
        <span style="display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; margin-bottom:0.25rem;">Alasan Penolakan:</span>
        <strong><?= esc($po['alasan_tolak']) ?></strong>
      </div>
      <?php endif; ?>
    </div>

    <!-- PO Total Card -->
    <div class="card" style="background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-card-hover) 100%); text-align: center; padding: 1.25rem;">
      <span style="font-size:0.72rem; text-transform:uppercase; color:var(--text-muted); display:block; margin-bottom:0.25rem;">Total Nilai Pembelian</span>
      <strong style="font-size: 1.8rem; color: var(--emerald); display: block; line-height: 1;">
        Rp <?= number_format($po['total_nilai'], 0, ',', '.') ?>
      </strong>
    </div>

    <!-- Admin Approval Actions Card -->
    <?php if ($status === 'diajukan' && $isAdmin): ?>
    <div class="card" style="display: flex; flex-direction: column; gap: 1rem; border-color: var(--status-warning);">
      <h3 class="card-title" style="color:var(--status-warning); display:flex; align-items:center; gap:0.5rem;">
        <i data-lucide="shield-alert" style="width:18px; height:18px;"></i>
        Persetujuan Dokumen PO
      </h3>
      <p style="font-size:0.8rem; color:var(--text-secondary); line-height:1.4; margin-bottom:0.25rem;">
        Sebagai admin/tim pembelian, Anda wajib meninjau kesesuaian harga dan kebutuhan bahan sebelum menyetujui PO ini.
      </p>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">
        <!-- Reject Trigger -->
        <button
          type="button"
          class="btn btn-danger btn-sm"
          @click="openRejectModal('<?= base_url('/pembelian/reject/' . $po['id']) ?>')"
          style="justify-content:center;"
        >
          <i data-lucide="x-circle" style="width:14px; height:14px; margin-right:4px;"></i>
          Tolak PO
        </button>

        <!-- Approve Form -->
        <form action="<?= base_url('/pembelian/approve/' . $po['id']) ?>" method="POST" style="display:flex;">
          <?= csrf_field() ?>
          <button
            type="submit"
            class="btn btn-primary btn-sm"
            style="width:100%; justify-content:center; background:var(--status-success); border-color:var(--status-success);"
          >
            <i data-lucide="check-circle" style="width:14px; height:14px; margin-right:4px;"></i>
            Setujui PO
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <!-- ══ RIGHT COLUMN: PO ITEMS LIST ══ -->
  <div class="card" style="padding: 0;">
    <div class="card-header" style="padding: var(--card-padding) var(--card-padding) 0;">
      <h3 class="card-title">Rincian Barang yang Dipesan</h3>
      <span class="badge badge-neutral" style="font-family:monospace;"><?= count($poItems) ?> Rincian</span>
    </div>

    <!-- Items Table -->
    <div class="table-wrapper" style="border: none; border-radius: 0; margin-top: 1.25rem;">
      <table class="data-table">
        <thead>
          <tr>
            <th width="40">No</th>
            <th width="120">Kode Bahan</th>
            <th>Deskripsi Bahan Baku</th>
            <th style="text-align: right;">Kuantitas</th>
            <th>Satuan</th>
            <th style="text-align: right;">Harga Satuan</th>
            <th style="text-align: right;">Subtotal Biaya</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($poItems as $i => $item): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td>
              <span style="font-family:monospace; font-size:0.78rem; background:var(--bg-card-hover); padding:0.15rem 0.4rem; border-radius:4px; color:var(--text-muted);">
                <?= esc($item['kode_bahan']) ?>
              </span>
            </td>
            <td>
              <strong><?= esc($item['nama_bahan']) ?></strong>
            </td>
            <td style="text-align: right; font-weight: 700; color: var(--text-primary);">
              <?= number_format($item['qty'], 2, ',', '.') ?>
            </td>
            <td>
              <span style="color:var(--text-muted); font-size:0.875rem;"><?= esc($item['satuan']) ?></span>
            </td>
            <td style="text-align: right; color: var(--text-secondary); font-size: 0.85rem;">
              Rp <?= number_format($item['harga_satuan'] ?? 0, 0, ',', '.') ?>
            </td>
            <td style="text-align: right; font-weight: 700; color: var(--emerald);">
              Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div style="padding: 1.25rem; background: var(--bg-card-hover); border-top: 1px solid var(--border-subtle); border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg); font-size: 0.78rem; color: var(--text-secondary); display:flex; justify-content:space-between; align-items:center;">
      <span>Status Transaksi: <strong style="color:var(--text-primary);"><?= esc($statusLabel) ?></strong></span>
      <span>Total Tagihan PO: <strong style="color:var(--emerald); font-size:0.9rem;">Rp <?= number_format($po['total_nilai'], 0, ',', '.') ?></strong></span>
    </div>
  </div>

</div>

<!-- ══ REJECTION REASON MODAL ══ -->
<div id="rejectModal" class="modal-overlay" style="display:none;">
  <div class="modal-content" style="max-width: 420px;">
    <div class="modal-header">
      <h3 class="modal-title" style="display: flex; align-items: center; gap: 0.5rem; color: var(--status-danger);">
        <i data-lucide="x-circle"></i>
        Tolak Purchase Order
      </h3>
      <button type="button" class="modal-close" onclick="closeRejectModal()">
        <i data-lucide="x"></i>
      </button>
    </div>
    <form id="rejectForm" method="POST" action="">
      <?= csrf_field() ?>
      
      <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
        <p style="color:var(--text-secondary); font-size: 0.875rem;">
          Berikan alasan resmi mengapa pengajuan PO ini ditolak. Alasan ini akan dikirimkan kembali ke pembuat dokumen.
        </p>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label" for="alasan_tolak">Alasan Penolakan <span class="required">*</span></label>
          <textarea
            name="alasan_tolak"
            id="alasan_tolak"
            class="form-textarea"
            rows="3"
            placeholder="Contoh: Harga satuan terlalu tinggi, melebihi plafon anggaran bulanan..."
            required
          ></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeRejectModal()">Tutup</button>
        <button type="submit" class="btn btn-danger btn-sm">
          Tolak Dokumen PO
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function poWorkflow() {
    return {
      openRejectModal(url) {
        document.getElementById('rejectForm').action = url;
        document.getElementById('rejectModal').style.display = 'flex';
      }
    };
  }

  function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
  }

  // Ensure modal closes on overlay click
  document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
  });
</script>
<?= $this->endSection() ?>
