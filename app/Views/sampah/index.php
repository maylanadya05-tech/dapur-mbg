<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$session = session();
$role    = $session->get('user_role') ?? 'viewer';
$isAdmin = in_array($role, ['admin', 'superadmin', 'produksi', 'gudang']);

$wasteList = $wasteList ?? [];
if (empty($wasteList)) {
  $wasteList = [
    ['id' => 1, 'tanggal' => '2026-06-20', 'batch_no' => 'BCH-2606-A', 'kategori' => 'sisa makanan', 'bahan_baku' => 'Ayam Fillet', 'qty' => 5.2, 'satuan' => 'kg', 'estimasi_kerugian' => 234000, 'pencatat' => 'Rian Hidayat'],
    ['id' => 2, 'tanggal' => '2026-06-20', 'batch_no' => null, 'kategori' => 'kadaluarsa', 'bahan_baku' => 'Bayam Segar', 'qty' => 12.0, 'satuan' => 'kg', 'estimasi_kerugian' => 96000, 'pencatat' => 'Siti Aisyah'],
    ['id' => 3, 'tanggal' => '2026-06-21', 'batch_no' => 'BCH-2606-B', 'kategori' => 'portioning error', 'bahan_baku' => 'Beras Premium', 'qty' => 8.5, 'satuan' => 'kg', 'estimasi_kerugian' => 106250, 'pencatat' => 'Rian Hidayat'],
    ['id' => 4, 'tanggal' => '2026-06-21', 'batch_no' => null, 'kategori' => 'lainnya', 'bahan_baku' => 'Telur Ayam', 'qty' => 3.0, 'satuan' => 'kg', 'estimasi_kerugian' => 75000, 'pencatat' => 'Agus Budiman'],
  ];
}

$totalWasteKg = array_sum(array_column($wasteList, 'qty'));
$totalLossRp = array_sum(array_column($wasteList, 'estimasi_kerugian'));
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Pencatatan Food Waste</h1>
    <p class="page-subtitle">Monitoring limbah makanan (waste) dan estimasi kerugian finansial dapur</p>
  </div>
  <div class="page-header-actions">
    <?php if ($isAdmin): ?>
    <a href="<?= base_url('/sisa/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="plus"></i>
      Catat Limbah / Waste
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ══ SUMMARY STAT CARDS ══ -->
<div class="stats-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;margin-bottom:1.5rem;">
  
  <div class="stat-card accent-danger">
    <div class="stat-card-header">
      <span class="stat-card-label">Total Limbah (Kg)</span>
      <div class="stat-card-icon" style="background:var(--danger-dim);"><i data-lucide="trash-2" style="color:var(--status-danger)"></i></div>
    </div>
    <div class="stat-card-value"><?= number_format($totalWasteKg, 1) ?> kg</div>
    <div class="stat-card-footer">
      <i data-lucide="scale" style="width:13px;height:13px;"></i>
      berat akumulasi limbah terdata
    </div>
  </div>

  <div class="stat-card accent-danger">
    <div class="stat-card-header">
      <span class="stat-card-label">Estimasi Kerugian</span>
      <div class="stat-card-icon" style="background:var(--danger-dim);"><i data-lucide="trending-down" style="color:var(--status-danger)"></i></div>
    </div>
    <div class="stat-card-value">Rp <?= number_format($totalLossRp, 0, ',', '.') ?></div>
    <div class="stat-card-footer">
      <i data-lucide="banknote" style="width:13px;height:13px;"></i>
      berdasarkan harga beli bahan baku
    </div>
  </div>

  <div class="stat-card accent-warning">
    <div class="stat-card-header">
      <span class="stat-card-label">Rasio Efisiensi</span>
      <div class="stat-card-icon"><i data-lucide="activity"></i></div>
    </div>
    <div class="stat-card-value">98.4%</div>
    <div class="stat-card-footer">
      <span style="color:var(--status-success);"><i data-lucide="trending-up" style="width:13px;height:13px;display:inline;vertical-align:middle;"></i> Dalam batas toleransi (< 3%)</span>
    </div>
  </div>

</div>

<!-- ══ DATA TABLE CARD ══ -->
<div class="card" style="padding:0;" x-data="wasteTable()">
  
  <!-- Filter Row -->
  <div class="filter-row" style="display:flex;gap:1rem;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border-subtle);flex-wrap:wrap;">
    <div class="input-group search-input" style="position:relative;flex:1;min-width:240px;">
      <span class="input-group-icon" style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted);"><i data-lucide="search" style="width:18px;height:18px;"></i></span>
      <input
        type="text"
        class="form-control"
        placeholder="Cari bahan baku, batch, pencatat..."
        x-model="search"
        @input="filterTable"
        style="padding-left:2.5rem;width:100%;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding-top:0.5rem;padding-bottom:0.5rem;"
      >
    </div>

    <select class="form-select" x-model="filterKategori" @change="filterTable" style="min-width:165px;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding:0.5rem;">
      <option value="">Semua Kategori</option>
      <option value="sisa makanan">Sisa Makanan</option>
      <option value="kadaluarsa">Kadaluarsa</option>
      <option value="portioning error">Portioning Error</option>
      <option value="lainnya">Lainnya</option>
    </select>

    <div style="flex:1;"></div>

    <span class="text-secondary text-sm" style="font-size:0.875rem;color:var(--text-secondary);">
      Menampilkan <strong x-text="filteredCount" style="color:var(--text-primary);"></strong> dari <?= count($wasteList) ?> baris log
    </span>
  </div>

  <!-- Table -->
  <div class="table-wrapper" style="overflow-x:auto;">
    <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;">
      <thead>
        <tr style="border-bottom:1px solid var(--border-subtle);">
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Tanggal</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Batch No</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Kategori</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Bahan Baku</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Kuantitas (Kg)</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Estimasi Rugi</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Pencatat</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;width:120px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($wasteList as $item): ?>
        <?php
          $catBadge = match($item['kategori']) {
            'sisa makanan' => 'badge-danger',
            'kadaluarsa' => 'badge-warning',
            'portioning error' => 'badge-info',
            default => 'badge-neutral',
          };
        ?>
        <tr
          class="waste-row"
          data-bahan="<?= esc(strtolower($item['bahan_baku'])) ?>"
          data-batch="<?= esc(strtolower($item['batch_no'] ?? 'gudang')) ?>"
          data-pencatat="<?= esc(strtolower($item['pencatat'])) ?>"
          data-kategori="<?= esc($item['kategori']) ?>"
          style="border-bottom:1px solid var(--border-subtle);transition:background-color 0.2s;"
          onmouseover="this.style.backgroundColor='var(--bg-card-hover)'"
          onmouseout="this.style.backgroundColor='transparent'"
        >
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-secondary);"><?= date('d M Y', strtotime($item['tanggal'])) ?></td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <?php if ($item['batch_no']): ?>
              <span style="font-family:monospace;background:var(--border-subtle);padding:0.2rem 0.5rem;border-radius:4px;color:var(--text-primary);">
                <?= esc($item['batch_no']) ?>
              </span>
            <?php else: ?>
              <span style="color:var(--text-muted);font-style:italic;">Bukan Batch (Gudang)</span>
            <?php endif; ?>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <span class="badge <?= $catBadge ?>"><?= ucfirst(esc($item['kategori'])) ?></span>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <strong style="color:var(--text-primary);"><?= esc($item['bahan_baku']) ?></strong>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;font-weight:600;color:var(--text-primary);"><?= number_format($item['qty'], 2) ?> <?= esc($item['satuan']) ?></td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--status-danger);font-weight:600;">
            Rp <?= number_format($item['estimasi_kerugian'], 0, ',', '.') ?>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-secondary);"><?= esc($item['pencatat']) ?></td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <div style="display:flex;gap:0.5rem;">
              <a href="<?= base_url('/sisa/edit/' . $item['id']) ?>"
                 class="btn btn-secondary btn-icon btn-sm" style="padding:4px;" title="Edit">
                <i data-lucide="pencil" style="width:16px;height:16px;"></i>
              </a>
              <?php if ($isAdmin): ?>
              <button
                onclick="confirmDelete('<?= base_url('/sisa/delete/' . $item['id']) ?>', '<?= esc($item['bahan_baku']) ?> (<?= $item['qty'] ?> kg)')"
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
      <i data-lucide="search" style="width:30px;height:30px;"></i>
    </div>
    <h3 style="color:var(--text-primary);font-size:1.1rem;margin-bottom:0.5rem;">Tidak Ada Log Sampah</h3>
    <p style="color:var(--text-muted);font-size:0.875rem;max-width:320px;margin-bottom:1.5rem;">Sesuaikan filter kategori atau kata pencarian Anda.</p>
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
        Apakah Anda yakin ingin menghapus catatan limbah <strong id="deleteItemName" style="color:var(--text-primary);"></strong>?
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
  function wasteTable() {
    return {
      search: '',
      filterKategori: '',
      filteredCount: <?= count($wasteList) ?>,

      filterTable() {
        const rows = document.querySelectorAll('.waste-row');
        let count = 0;
        const s = this.search.toLowerCase().trim();
        const k = this.filterKategori;

        rows.forEach(row => {
          const bahan    = row.dataset.bahan    || '';
          const batch    = row.dataset.batch    || '';
          const pencatat = row.dataset.pencatat || '';
          const kategori = row.dataset.kategori || '';

          const matchSearch = !s || bahan.includes(s) || batch.includes(s) || pencatat.includes(s);
          const matchKategori = !k || kategori === k;

          if (matchSearch && matchKategori) {
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
    const tableEl = document.querySelector('[x-data="wasteTable()"]');
    if (tableEl && tableEl.__x) {
      tableEl.__x.$data.search = '';
      tableEl.__x.$data.filterKategori = '';
      tableEl.__x.$data.filterTable();
    }
  }

  document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });
</script>
<?= $this->endSection() ?>
