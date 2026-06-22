<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$session  = session();
$role     = $session->get('user_role') ?? 'viewer';
$isAdmin  = in_array($role, ['admin', 'superadmin', 'pembelian']);

// Purchase orders data from controller (with fallback demo data)
$poList = $poList ?? [];
$supplierList = $supplierList ?? [
  ['id' => 1, 'nama' => 'PT Beras Cianjur'],
  ['id' => 2, 'nama' => 'CV Protein Prima'],
  ['id' => 3, 'nama' => 'CV Minyak Murni'],
  ['id' => 4, 'nama' => 'UD Agro Segar'],
  ['id' => 5, 'nama' => 'PT Gulaku'],
];

if (empty($poList)) {
  $poList = [
    [
      'id' => 1,
      'nomor_po' => 'PO-2026-001',
      'supplier_id' => 1,
      'supplier_name' => 'PT Beras Cianjur',
      'tanggal_po' => '2026-06-18',
      'tanggal_dibutuhkan' => '2026-06-23',
      'status' => 'diajukan',
      'total_nilai' => 15000000.00,
      'catatan' => 'Kebutuhan beras premium untuk minggu depan.',
      'dibuat_oleh_name' => 'Budi Santoso',
    ],
    [
      'id' => 2,
      'nomor_po' => 'PO-2026-002',
      'supplier_id' => 2,
      'supplier_name' => 'CV Protein Prima',
      'tanggal_po' => '2026-06-19',
      'tanggal_dibutuhkan' => '2026-06-22',
      'status' => 'disetujui',
      'total_nilai' => 8400000.00,
      'catatan' => 'Ayam fillet segar tanpa lemak.',
      'dibuat_oleh_name' => 'Budi Santoso',
    ],
    [
      'id' => 3,
      'nomor_po' => 'PO-2026-003',
      'supplier_id' => 3,
      'supplier_name' => 'CV Minyak Murni',
      'tanggal_po' => '2026-06-19',
      'tanggal_dibutuhkan' => '2026-06-21',
      'status' => 'diterima',
      'total_nilai' => 3600000.00,
      'catatan' => 'Minyak kelapa kemasan jerigen.',
      'dibuat_oleh_name' => 'Budi Santoso',
    ],
    [
      'id' => 4,
      'nomor_po' => 'PO-2026-004',
      'supplier_id' => 4,
      'supplier_name' => 'UD Agro Segar',
      'tanggal_po' => '2026-06-20',
      'tanggal_dibutuhkan' => '2026-06-21',
      'status' => 'ditolak',
      'total_nilai' => 1200000.00,
      'catatan' => 'Sayuran darurat untuk besok pagi.',
      'alasan_tolak' => 'Harga melonjak di atas anggaran bulanan.',
      'dibuat_oleh_name' => 'Budi Santoso',
    ],
  ];
}

// KPI Calculations
$totalCount = count($poList);
$pendingCount = count(array_filter($poList, fn($po) => $po['status'] === 'diajukan'));
$approvedValue = array_sum(array_map(fn($po) => in_array($po['status'], ['disetujui', 'diterima']) ? $po['total_nilai'] : 0, $poList));
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Purchase Orders (PO)</h1>
    <p class="page-subtitle">Kelola pengajuan pembelian bahan baku ke supplier</p>
  </div>
  <div class="page-header-actions">
    <?php if ($role !== 'viewer'): ?>
    <a href="<?= base_url('/pembelian/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="plus"></i>
      Buat PO Baru
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ══ KPI STAT CARDS ══ -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 1.5rem;">
  <div class="stat-card accent-info">
    <div class="stat-card-header">
      <span class="stat-card-label">Total Pengajuan PO</span>
      <div class="stat-card-icon"><i data-lucide="shopping-cart"></i></div>
    </div>
    <div class="stat-card-value"><?= $totalCount ?></div>
    <div class="stat-card-footer">
      <i data-lucide="hash" style="width:13px;height:13px;"></i>
      transaksi PO tercatat di sistem
    </div>
  </div>

  <div class="stat-card accent-warning">
    <div class="stat-card-header">
      <span class="stat-card-label">PO Pending Approval</span>
      <div class="stat-card-icon"><i data-lucide="clock"></i></div>
    </div>
    <div class="stat-card-value"><?= $pendingCount ?></div>
    <div class="stat-card-footer">
      <?php if ($pendingCount > 0): ?>
        <span class="stat-trend down"><i data-lucide="alert-circle"></i> Butuh persetujuan segera</span>
      <?php else: ?>
        <span class="stat-trend up"><i data-lucide="check-circle"></i> Semua PO diproses</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="stat-card accent-success">
    <div class="stat-card-header">
      <span class="stat-card-label">Total Nilai PO Disetujui</span>
      <div class="stat-card-icon"><i data-lucide="banknote"></i></div>
    </div>
    <div class="stat-card-value">Rp <?= number_format($approvedValue / 1000000, 1, ',', '.') ?>jt</div>
    <div class="stat-card-footer">
      <i data-lucide="trending-up" style="width:13px;height:13px;"></i>
      Akumulasi PO disetujui & diterima
    </div>
  </div>
</div>

<!-- ══ DATA TABLE CARD ══ -->
<div class="card" style="padding:0;" x-data="poTable()">
  
  <!-- ── Filters ── -->
  <div class="filter-row" style="flex-wrap: wrap; gap: 1rem; align-items: center; padding: 1.25rem;">
    <!-- Tabs for Status -->
    <div style="display:flex; gap:0.5rem; background: var(--bg-primary); padding:0.25rem; border-radius: var(--border-radius-sm); border:1px solid var(--border-subtle);">
      <button
        type="button"
        class="btn btn-sm"
        :class="statusTab === '' ? 'btn-primary' : 'btn-ghost'"
        @click="setStatusTab('')"
        style="padding: 0.35rem 0.75rem; font-size: 0.8rem;"
      >
        Semua
      </button>
      <button
        type="button"
        class="btn btn-sm"
        :class="statusTab === 'diajukan' ? 'btn-primary' : 'btn-ghost'"
        @click="setStatusTab('diajukan')"
        style="padding: 0.35rem 0.75rem; font-size: 0.8rem;"
      >
        Diajukan
      </button>
      <button
        type="button"
        class="btn btn-sm"
        :class="statusTab === 'disetujui' ? 'btn-primary' : 'btn-ghost'"
        @click="setStatusTab('disetujui')"
        style="padding: 0.35rem 0.75rem; font-size: 0.8rem;"
      >
        Disetujui
      </button>
      <button
        type="button"
        class="btn btn-sm"
        :class="statusTab === 'diterima' ? 'btn-primary' : 'btn-ghost'"
        @click="setStatusTab('diterima')"
        style="padding: 0.35rem 0.75rem; font-size: 0.8rem;"
      >
        Diterima
      </button>
      <button
        type="button"
        class="btn btn-sm"
        :class="statusTab === 'ditolak' ? 'btn-primary' : 'btn-ghost'"
        @click="setStatusTab('ditolak')"
        style="padding: 0.35rem 0.75rem; font-size: 0.8rem;"
      >
        Ditolak
      </button>
    </div>

    <!-- Search Input -->
    <div class="input-group search-input" style="width:220px; margin-bottom:0;">
      <span class="input-group-icon"><i data-lucide="search"></i></span>
      <input
        type="text"
        class="form-control"
        placeholder="Cari nomor PO..."
        x-model="search"
        @input="filterTable"
        style="padding-left:2.5rem;"
      >
    </div>

    <!-- Supplier Dropdown -->
    <select class="form-select" x-model="supplierId" @change="filterTable" style="min-width:180px;">
      <option value="">Semua Supplier</option>
      <?php foreach ($supplierList as $sup): ?>
      <option value="<?= $sup['id'] ?>"><?= esc($sup['nama']) ?></option>
      <?php endforeach; ?>
    </select>

    <div class="filter-row-spacer"></div>

    <span class="text-secondary text-sm">
      Menampilkan <strong x-text="filteredCount" style="color:var(--text-primary);"></strong> dari <?= count($poList) ?> PO
    </span>
  </div>

  <!-- ── Table ── -->
  <div class="table-wrapper" style="border:none; border-radius:0;">
    <table class="data-table" id="poTable">
      <thead>
        <tr>
          <th width="40">No</th>
          <th>Nomor PO</th>
          <th>Nama Supplier</th>
          <th>Tanggal PO</th>
          <th>Tanggal Dibutuhkan</th>
          <th style="text-align: right;">Total Nilai</th>
          <th>Status</th>
          <th>Pembuat</th>
          <th width="140">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($poList as $i => $po): ?>
        <?php
          $status = $po['status'];
          $badgeClass = match($status) {
            'diajukan'  => 'badge-warning',
            'disetujui' => 'badge-info',
            'diterima'  => 'badge-success',
            'ditolak'   => 'badge-danger',
            default     => 'badge-neutral',
          };
          $statusLabel = match($status) {
            'diajukan'  => 'Diajukan',
            'disetujui' => 'Disetujui',
            'diterima'  => 'Diterima',
            'ditolak'   => 'Ditolak',
            default     => ucfirst($status),
          };
        ?>
        <tr
          class="po-row"
          data-po-no="<?= esc(strtolower($po['nomor_po'])) ?>"
          data-supplier-id="<?= esc($po['supplier_id']) ?>"
          data-status="<?= esc($status) ?>"
        >
          <td><?= $i + 1 ?></td>
          <td>
            <span style="font-family:monospace; font-size:0.8rem; background:var(--bg-card-hover); padding:0.2rem 0.5rem; border-radius:4px; color:var(--text-muted); font-weight:600;">
              <?= esc($po['nomor_po']) ?>
            </span>
          </td>
          <td>
            <strong><?= esc($po['supplier_name']) ?></strong>
          </td>
          <td style="color:var(--text-secondary);"><?= date('d M Y', strtotime($po['tanggal_po'])) ?></td>
          <td style="color:var(--text-secondary);">
            <strong><?= date('d M Y', strtotime($po['tanggal_dibutuhkan'])) ?></strong>
          </td>
          <td style="text-align: right; font-weight: 700; color: var(--emerald);">
            Rp <?= number_format($po['total_nilai'], 0, ',', '.') ?>
          </td>
          <td><span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
          <td style="color:var(--text-muted); font-size: 0.8rem;"><?= esc($po['dibuat_oleh_name'] ?? 'Sistem') ?></td>
          <td>
            <div class="table-actions">
              <a href="<?= base_url('/pembelian/show/' . $po['id']) ?>"
                 class="btn btn-ghost btn-icon btn-sm" title="Lihat Detail & Workflow">
                <i data-lucide="eye"></i>
              </a>
              <?php if ($po['status'] === 'diajukan' && $isAdmin): ?>
              <a href="<?= base_url('/pembelian/edit/' . $po['id']) ?>"
                 class="btn btn-secondary btn-icon btn-sm" title="Edit PO">
                <i data-lucide="pencil"></i>
              </a>
              <button
                onclick="confirmDelete('<?= base_url('/pembelian/delete/' . $po['id']) ?>', '<?= esc($po['nomor_po']) ?>')"
                class="btn btn-danger btn-icon btn-sm" title="Hapus PO">
                <i data-lucide="trash-2"></i>
              </button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ── Empty State ── -->
  <div id="emptyState" class="empty-state" style="display:none;">
    <div class="empty-state-icon icon-warning">
      <i data-lucide="shopping-cart-x"></i>
    </div>
    <h3>Purchase Order Tidak Ditemukan</h3>
    <p>Tidak ada PO yang cocok dengan filter pencarian Anda. Coba ubah status, supplier, atau cari dengan nomor PO lain.</p>
    <div class="empty-state-action" style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;">
      <button class="btn btn-secondary btn-sm" onclick="resetFilters()">
        <i data-lucide="refresh-cw"></i>
        Reset Filter
      </button>
      <?php if ($role !== 'viewer'): ?>
      <a href="<?= base_url('/pembelian/create') ?>" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i>
        Buat PO Baru
      </a>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay" style="display:none;">
  <div class="modal-content" style="max-width: 420px;">
    <div class="modal-header">
      <h3 class="modal-title" style="display: flex; align-items: center; gap: 0.5rem; color: var(--status-danger);">
        <i data-lucide="alert-triangle"></i>
        Konfirmasi Hapus PO
      </h3>
      <button class="modal-close" onclick="closeDeleteModal()">
        <i data-lucide="x"></i>
      </button>
    </div>
    <div class="modal-body">
      <p style="color:var(--text-secondary); font-size: 0.875rem;">
        Apakah Anda yakin ingin menghapus Purchase Order <strong id="deleteItemName" style="color:var(--text-primary);"></strong>?
        Tindakan ini tidak dapat dibatalkan dan data PO akan terhapus permanen.
      </p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary btn-sm" onclick="closeDeleteModal()">Batal</button>
      <form id="deleteForm" method="POST" action="" style="display: inline-block;">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger btn-sm">
          <i data-lucide="trash-2"></i>
          Ya, Hapus PO
        </button>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function poTable() {
    return {
      search: '',
      supplierId: '',
      statusTab: '',
      filteredCount: <?= count($poList) ?>,

      setStatusTab(status) {
        this.statusTab = status;
        this.filterTable();
      },

      filterTable() {
        const rows = document.querySelectorAll('.po-row');
        let count = 0;
        const s = this.search.toLowerCase().trim();
        const sup = this.supplierId;
        const st = this.statusTab;

        rows.forEach(row => {
          const poNo     = row.dataset.poNo       ?? '';
          const supplier = row.dataset.supplierId ?? '';
          const status   = row.dataset.status     ?? '';

          const matchSearch   = !s || poNo.includes(s);
          const matchSupplier = !sup || supplier === sup;
          const matchStatus   = !st || status === st;

          if (matchSearch && matchSupplier && matchStatus) {
            row.style.display = '';
            count++;
          } else {
            row.style.display = 'none';
          }
        });

        this.filteredCount = count;
        document.getElementById('emptyState').style.display = count === 0 ? 'flex' : 'none';
        if (typeof lucide !== 'undefined') lucide.createIcons();
      }
    };
  }

  function confirmDelete(url, name) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteModal').style.display = 'flex';
  }

  function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
  }

  function resetFilters() {
    window.location.reload();
  }

  // Close modal on overlay click
  document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });
</script>
<?= $this->endSection() ?>
