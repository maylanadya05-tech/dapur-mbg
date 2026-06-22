<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$session = session();
$role    = $session->get('user_role') ?? 'viewer';
$isAdmin = in_array($role, ['admin', 'superadmin']);

$invoiceList = $invoiceList ?? [];
if (empty($invoiceList)) {
  $invoiceList = [
    ['id' => 1, 'no_invoice' => 'INV-2026-001', 'tanggal' => '2026-06-21', 'sekolah_nama' => 'SDN Merdeka 01', 'periode' => '1 Juni - 15 Juni 2026', 'total_porsi' => 6750, 'harga_porsi' => 15000, 'grand_total' => 101250000, 'status' => 'dibayar'],
    ['id' => 2, 'no_invoice' => 'INV-2026-002', 'tanggal' => '2026-06-21', 'sekolah_nama' => 'SMP Negeri 1 Bogor', 'periode' => '1 Juni - 15 Juni 2026', 'total_porsi' => 12300, 'harga_porsi' => 15000, 'grand_total' => 184500000, 'status' => 'dikirim'],
    ['id' => 3, 'no_invoice' => 'INV-2026-003', 'tanggal' => '2026-06-21', 'sekolah_nama' => 'SMA Negeri 2 Bogor', 'periode' => '1 Juni - 15 Juni 2026', 'total_porsi' => 14400, 'harga_porsi' => 15000, 'grand_total' => 216000000, 'status' => 'jatuh_tempo'],
    ['id' => 4, 'no_invoice' => 'INV-2026-004', 'tanggal' => '2026-06-21', 'sekolah_nama' => 'SMK Negeri 1 Bogor', 'periode' => '1 Juni - 15 Juni 2026', 'total_porsi' => 18000, 'harga_porsi' => 15000, 'grand_total' => 270000000, 'status' => 'draft'],
  ];
}

$totalBilling = array_sum(array_column($invoiceList, 'grand_total'));
$paidBilling = array_sum(array_map(fn($i) => $i['status'] === 'dibayar' ? $i['grand_total'] : 0, $invoiceList));
$outstandingBilling = $totalBilling - $paidBilling;
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Penagihan & Invoice</h1>
    <p class="page-subtitle">Pencatatan invoice program Makan Bergizi Gratis (MBG) per sekolah</p>
  </div>
  <div class="page-header-actions">
    <?php if ($isAdmin): ?>
    <a href="<?= base_url('/invoice/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="plus"></i>
      Buat Invoice Baru
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ══ SUMMARY CARDS ══ -->
<div class="stats-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;margin-bottom:1.5rem;">
  
  <div class="stat-card accent-info">
    <div class="stat-card-header">
      <span class="stat-card-label">Total Tagihan</span>
      <div class="stat-card-icon"><i data-lucide="banknote"></i></div>
    </div>
    <div class="stat-card-value">Rp <?= number_format($totalBilling / 1000000, 1) ?>jt</div>
    <div class="stat-card-footer">Akumulasi nilai seluruh invoice</div>
  </div>

  <div class="stat-card accent-success">
    <div class="stat-card-header">
      <span class="stat-card-label">Sudah Dibayar</span>
      <div class="stat-card-icon"><i data-lucide="check-circle"></i></div>
    </div>
    <div class="stat-card-value">Rp <?= number_format($paidBilling / 1000000, 1) ?>jt</div>
    <div class="stat-card-footer">Dana masuk terkonfirmasi</div>
  </div>

  <div class="stat-card accent-danger">
    <div class="stat-card-header">
      <span class="stat-card-label">Belum Tertagih</span>
      <div class="stat-card-icon" style="background:var(--danger-dim);"><i data-lucide="clock" style="color:var(--status-danger);"></i></div>
    </div>
    <div class="stat-card-value">Rp <?= number_format($outstandingBilling / 1000000, 1) ?>jt</div>
    <div class="stat-card-footer">Outstanding draft/dikirim/jatuh tempo</div>
  </div>

</div>

<!-- ══ INVOICE TABLE CARD ══ -->
<div class="card" style="padding:0;" x-data="invoiceTable()">
  
  <!-- Filter Row -->
  <div class="filter-row" style="display:flex;gap:1rem;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border-subtle);flex-wrap:wrap;">
    <div class="input-group search-input" style="position:relative;flex:1;min-width:240px;">
      <span class="input-group-icon" style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted);"><i data-lucide="search" style="width:18px;height:18px;"></i></span>
      <input
        type="text"
        class="form-control"
        placeholder="Cari invoice #, sekolah, periode..."
        x-model="search"
        @input="filterTable"
        style="padding-left:2.5rem;width:100%;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding-top:0.5rem;padding-bottom:0.5rem;"
      >
    </div>

    <select class="form-select" x-model="filterStatus" @change="filterTable" style="min-width:150px;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding:0.5rem;">
      <option value="">Semua Status</option>
      <option value="draft">Draft</option>
      <option value="dikirim">Dikirim</option>
      <option value="dibayar">Dibayar</option>
      <option value="jatuh_tempo">Jatuh Tempo</option>
    </select>

    <div style="flex:1;"></div>

    <span class="text-secondary text-sm" style="font-size:0.875rem;color:var(--text-secondary);">
      Menampilkan <strong x-text="filteredCount" style="color:var(--text-primary);"></strong> dari <?= count($invoiceList) ?> invoice
    </span>
  </div>

  <!-- Table -->
  <div class="table-wrapper" style="overflow-x:auto;">
    <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;">
      <thead>
        <tr style="border-bottom:1px solid var(--border-subtle);">
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">No Invoice</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Sekolah Penerima</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Periode</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Total Porsi</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Harga Porsi</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Grand Total</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Tanggal</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Status</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;width:120px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($invoiceList as $item): ?>
        <?php
          $statusClass = match($item['status']) {
            'draft' => 'badge-neutral',
            'dikirim' => 'badge-info',
            'dibayar' => 'badge-success',
            'jatuh_tempo' => 'badge-danger',
            default => 'badge-neutral',
          };
          $statusLabel = match($item['status']) {
            'draft' => 'Draft',
            'dikirim' => 'Dikirim',
            'dibayar' => 'Dibayar',
            'jatuh_tempo' => 'Jatuh Tempo',
            default => ucfirst($item['status']),
          };
        ?>
        <tr
          class="invoice-row"
          data-no="<?= esc(strtolower($item['no_invoice'])) ?>"
          data-sekolah="<?= esc(strtolower($item['sekolah_nama'])) ?>"
          data-status="<?= esc($item['status']) ?>"
          data-periode="<?= esc(strtolower($item['periode'])) ?>"
          style="border-bottom:1px solid var(--border-subtle);transition:background-color 0.2s;"
          onmouseover="this.style.backgroundColor='var(--bg-card-hover)'"
          onmouseout="this.style.backgroundColor='transparent'"
        >
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <span style="font-family:monospace;font-weight:600;color:var(--emerald);">
              <?= esc($item['no_invoice']) ?>
            </span>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <strong style="color:var(--text-primary);"><?= esc($item['sekolah_nama']) ?></strong>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-secondary);"><?= esc($item['periode']) ?></td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;font-weight:600;color:var(--text-primary);"><?= number_format($item['total_porsi']) ?> porsi</td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-secondary);">Rp <?= number_format($item['harga_porsi'], 0, ',', '.') ?></td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;font-weight:700;color:var(--emerald-light);">Rp <?= number_format($item['grand_total'], 0, ',', '.') ?></td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-secondary);"><?= date('d/m/Y', strtotime($item['tanggal'])) ?></td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <div style="display:flex;gap:0.5rem;">
              <a href="<?= base_url('/invoice/' . $item['id']) ?>"
                 class="btn btn-secondary btn-icon btn-sm" style="padding:4px;" title="Lihat Detail & SPPG Letterhead">
                <i data-lucide="eye" style="width:16px;height:16px;"></i>
              </a>
              <?php if ($isAdmin): ?>
              <button
                onclick="confirmDelete('<?= base_url('/invoice/delete/' . $item['id']) ?>', '<?= esc($item['no_invoice']) ?>')"
                class="btn btn-danger btn-icon btn-sm" style="padding:4px;" title="Hapus">
                <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
              </button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Empty State -->
  <div id="emptyState" style="display:none;flex-direction:column;align-items:center;justify-content:center;padding:3rem;text-align:center;">
    <div style="width:60px;height:60px;border-radius:50%;background:var(--border-subtle);display:flex;align-items:center;justify-content:center;color:var(--text-muted);margin-bottom:1rem;">
      <i data-lucide="file-minus" style="width:30px;height:30px;"></i>
    </div>
    <h3 style="color:var(--text-primary);font-size:1.1rem;margin-bottom:0.5rem;">Tidak Ada Invoice Ditemukan</h3>
    <p style="color:var(--text-muted);font-size:0.875rem;max-width:320px;margin-bottom:1.5rem;">Sesuaikan filter status pencarian Anda.</p>
    <button class="btn btn-secondary btn-sm" onclick="resetFilters()">
      <i data-lucide="refresh-cw"></i> Reset Filter
    </button>
  </div>

</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:var(--bg-overlay);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;">
  <div class="card" style="width:100%;max-width:480px;padding:2rem;">
    <div class="modal-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
      <h3 class="modal-title" style="font-size:1.1rem;font-weight:700;color:var(--status-danger);display:flex;align-items:center;gap:0.5rem;">
        <i data-lucide="alert-triangle"></i> Konfirmasi Hapus
      </h3>
    </div>
    <div class="modal-body" style="margin-bottom:1.5rem;">
      <p style="color:var(--text-secondary);font-size:0.9rem;">
        Apakah Anda yakin ingin menghapus invoice <strong id="deleteItemName" style="color:var(--text-primary);"></strong> dari pencatatan?
      </p>
    </div>
    <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:0.75rem;">
      <button class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
      <a id="deleteConfirmBtn" href="#" class="btn btn-danger">Hapus</a>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function invoiceTable() {
    return {
      search: '',
      filterStatus: '',
      filteredCount: <?= count($invoiceList) ?>,

      filterTable() {
        const rows = document.querySelectorAll('.invoice-row');
        let count = 0;
        const s = this.search.toLowerCase().trim();
        const st = this.filterStatus;

        rows.forEach(row => {
          const no       = row.dataset.no       || '';
          const sekolah  = row.dataset.sekolah  || '';
          const status   = row.dataset.status   || '';
          const periode  = row.dataset.periode  || '';

          const matchSearch = !s || no.includes(s) || sekolah.includes(s) || periode.includes(s);
          const matchStatus = !st || status === st;

          if (matchSearch && matchStatus) {
            row.style.display = '';
            count++;
          } else {
            row.style.display = 'none';
          }
        });

        this.filteredCount = count;
        document.getElementById('emptyState').style.display = count === 0 ? 'flex' : 'none';
      }
    };
  }

  function confirmDelete(url, name) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('deleteConfirmBtn').href = url;
    document.getElementById('deleteModal').style.display = 'flex';
  }

  function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
  }

  function resetFilters() {
    const tableEl = document.querySelector('[x-data="invoiceTable()"]');
    if (tableEl && tableEl.__x) {
      tableEl.__x.$data.search = '';
      tableEl.__x.$data.filterStatus = '';
      tableEl.__x.$data.filterTable();
    }
  }

  document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });
</script>
<?= $this->endSection() ?>
