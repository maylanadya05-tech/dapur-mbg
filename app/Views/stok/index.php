<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$session  = session();
$role     = $session->get('user_role') ?? 'viewer';
$isAdmin  = in_array($role, ['admin', 'superadmin', 'gudang']);

// Data from controller (with fallback demo)
$stokList   = $stokList   ?? [];
$pager      = $pager      ?? null;
$totalItem  = $summary['total_item']  ?? 0;
$kritisCount = $summary['kritis']    ?? 0;
$nilaiTotal  = $summary['nilai_total'] ?? 0;
$filters    = $filters    ?? [];

// Demo data if no controller data
if (empty($stokList)) {
  $stokList = [
    ['kode'=>'BH-001','nama_bahan'=>'Beras Premium','kategori'=>'Karbohidrat','stok_saat_ini'=>250,'satuan'=>'kg','min_stok'=>100,'status'=>'normal','nama_supplier'=>'PT Beras Cianjur','harga_per_unit'=>12500],
    ['kode'=>'BH-002','nama_bahan'=>'Ayam Fillet','kategori'=>'Protein','stok_saat_ini'=>30,'satuan'=>'kg','min_stok'=>50,'status'=>'kritis','nama_supplier'=>'CV Protein Prima','harga_per_unit'=>45000],
    ['kode'=>'BH-003','nama_bahan'=>'Minyak Goreng','kategori'=>'Lemak','stok_saat_ini'=>80,'satuan'=>'liter','min_stok'=>60,'status'=>'normal','nama_supplier'=>'CV Minyak Murni','harga_per_unit'=>18000],
    ['kode'=>'BH-004','nama_bahan'=>'Sayur Bayam','kategori'=>'Sayuran','stok_saat_ini'=>15,'satuan'=>'kg','min_stok'=>30,'status'=>'kritis','nama_supplier'=>'UD Agro Segar','harga_per_unit'=>8000],
    ['kode'=>'BH-005','nama_bahan'=>'Gula Pasir','kategori'=>'Bumbu','stok_saat_ini'=>45,'satuan'=>'kg','min_stok'=>20,'status'=>'normal','nama_supplier'=>'PT Gulaku','harga_per_unit'=>14000],
    ['kode'=>'BH-006','nama_bahan'=>'Telur Ayam','kategori'=>'Protein','stok_saat_ini'=>500,'satuan'=>'butir','min_stok'=>200,'status'=>'normal','nama_supplier'=>'UD Telur Segar','harga_per_unit'=>1800],
    ['kode'=>'BH-007','nama_bahan'=>'Garam Dapur','kategori'=>'Bumbu','stok_saat_ini'=>12,'satuan'=>'kg','min_stok'=>15,'status'=>'kritis','nama_supplier'=>'CV Bumbu Nusantara','harga_per_unit'=>3500],
    ['kode'=>'BH-008','nama_bahan'=>'Kacang Panjang','kategori'=>'Sayuran','stok_saat_ini'=>25,'satuan'=>'kg','min_stok'=>20,'status'=>'normal','nama_supplier'=>'UD Agro Segar','harga_per_unit'=>9000],
  ];
  $totalItem   = count($stokList);
  $kritisCount = count(array_filter($stokList, fn($s) => $s['status'] === 'kritis'));
  $nilaiTotal  = array_sum(array_map(fn($s) => ($s['stok_saat_ini'] ?? 0) * ($s['harga_per_unit'] ?? 0), $stokList));
}
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Stok Gudang</h1>
    <p class="page-subtitle">Manajemen bahan baku dan inventaris dapur</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/stok/export') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="download"></i>
      Export
    </a>
    <?php if ($isAdmin): ?>
    <a href="<?= base_url('/stok/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="plus"></i>
      Tambah Stok
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ══ SUMMARY STAT CARDS ══ -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.5rem;">

  <div class="stat-card accent-success">
    <div class="stat-card-header">
      <span class="stat-card-label">Total Item Bahan</span>
      <div class="stat-card-icon"><i data-lucide="package"></i></div>
    </div>
    <div class="stat-card-value"><?= number_format($totalItem) ?></div>
    <div class="stat-card-footer">
      <i data-lucide="layers" style="width:13px;height:13px;"></i>
      jenis bahan baku terdaftar
    </div>
  </div>

  <div class="stat-card <?= $kritisCount > 0 ? 'accent-danger' : 'accent-success' ?>">
    <div class="stat-card-header">
      <span class="stat-card-label">Stok Kritis</span>
      <div class="stat-card-icon"><i data-lucide="alert-triangle"></i></div>
    </div>
    <div class="stat-card-value"><?= $kritisCount ?></div>
    <div class="stat-card-footer">
      <?php if ($kritisCount > 0): ?>
        <span class="stat-trend down"><i data-lucide="alert-circle"></i> Perlu restock segera</span>
      <?php else: ?>
        <span class="stat-trend up"><i data-lucide="check-circle"></i> Semua aman</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="stat-card accent-info">
    <div class="stat-card-header">
      <span class="stat-card-label">Nilai Total Stok</span>
      <div class="stat-card-icon"><i data-lucide="banknote"></i></div>
    </div>
    <div class="stat-card-value" style="font-size:1.5rem;">Rp <?= number_format($nilaiTotal / 1000000, 1) ?>jt</div>
    <div class="stat-card-footer">
      <i data-lucide="trending-up" style="width:13px;height:13px;"></i>
      estimasi nilai inventaris
    </div>
  </div>

</div>

<!-- ══ DATA TABLE CARD ══ -->
<div class="card" style="padding:0;" x-data="stokTable()">

  <!-- ── Filter Row ── -->
  <div class="filter-row">
    <div class="input-group search-input">
      <span class="input-group-icon"><i data-lucide="search"></i></span>
      <input
        type="text"
        class="form-control"
        placeholder="Cari nama bahan, kode..."
        x-model="search"
        @input="filterTable"
        style="padding-left:2.5rem;"
      >
    </div>

    <select class="form-select" x-model="filterKategori" @change="filterTable" style="min-width:160px;">
      <option value="">Semua Kategori</option>
      <option value="Karbohidrat">Karbohidrat</option>
      <option value="Protein">Protein</option>
      <option value="Sayuran">Sayuran</option>
      <option value="Lemak">Lemak</option>
      <option value="Bumbu">Bumbu</option>
      <option value="Minuman">Minuman</option>
    </select>

    <select class="form-select" x-model="filterStatus" @change="filterTable" style="min-width:140px;">
      <option value="">Semua Status</option>
      <option value="normal">Normal</option>
      <option value="kritis">Kritis</option>
      <option value="aman">Aman</option>
    </select>

    <div class="filter-row-spacer"></div>

    <span class="text-secondary text-sm">
      Menampilkan <strong x-text="filteredCount" style="color:var(--text-primary);"></strong> dari <?= count($stokList) ?> item
    </span>
  </div>

  <!-- ── Table ── -->
  <div class="table-wrapper" style="border:none;border-radius:0;">
    <table class="data-table" id="stokTable">
      <thead>
        <tr>
          <th width="40">No</th>
          <th>Kode</th>
          <th>Nama Bahan</th>
          <th>Kategori</th>
          <th>Stok Saat Ini</th>
          <th>Min. Stok</th>
          <th>Status</th>
          <th>Supplier</th>
          <?php if ($isAdmin): ?>
          <th width="120">Aksi</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody id="stokTableBody">
        <?php foreach ($stokList as $i => $item): ?>
        <?php
          $status = $item['status'] ?? 'normal';
          $badgeClass = match($status) {
            'kritis'  => 'badge-danger',
            'normal'  => 'badge-success',
            'aman'    => 'badge-info',
            default   => 'badge-neutral',
          };
          $statusLabel = match($status) {
            'kritis' => 'Kritis',
            'normal' => 'Normal',
            'aman'   => 'Aman',
            default  => ucfirst($status),
          };
          $stokRatio = $item['min_stok'] > 0
            ? ($item['stok_saat_ini'] / $item['min_stok']) * 100
            : 100;
          $progressClass = $stokRatio < 50 ? 'danger' : ($stokRatio < 80 ? 'warning' : '');
          $progressPct   = min(100, $stokRatio);
        ?>
        <tr
          class="stok-row"
          data-nama="<?= esc(strtolower($item['nama_bahan'])) ?>"
          data-kode="<?= esc(strtolower($item['kode'])) ?>"
          data-kategori="<?= esc($item['kategori']) ?>"
          data-status="<?= esc($status) ?>"
        >
          <td><?= $i + 1 ?></td>
          <td>
            <span style="font-family:monospace;font-size:0.8rem;background:var(--bg-card-hover);padding:0.2rem 0.5rem;border-radius:4px;color:var(--text-muted);">
              <?= esc($item['kode']) ?>
            </span>
          </td>
          <td>
            <strong><?= esc($item['nama_bahan']) ?></strong>
          </td>
          <td>
            <span class="badge badge-neutral"><?= esc($item['kategori']) ?></span>
          </td>
          <td>
            <div class="stok-level">
              <div class="stok-level-text">
                <strong><?= number_format($item['stok_saat_ini']) ?></strong>
                <span style="font-size:0.78rem;color:var(--text-muted);"> <?= esc($item['satuan']) ?></span>
              </div>
              <div class="progress-bar" style="height:4px;width:80px;">
                <div class="progress-fill <?= $progressClass ?>" style="width:<?= min(100,$progressPct) ?>%;"></div>
              </div>
            </div>
          </td>
          <td>
            <span style="color:var(--text-muted);font-size:0.875rem;">
              <?= number_format($item['min_stok']) ?> <?= esc($item['satuan']) ?>
            </span>
          </td>
          <td><span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
          <td style="color:var(--text-secondary);"><?= esc($item['nama_supplier'] ?? '-') ?></td>
          <?php if ($isAdmin): ?>
          <td>
            <div class="table-actions">
              <a href="<?= base_url('/stok/kartu-stok/' . ($item['id'] ?? $i + 1)) ?>"
                 class="btn btn-ghost btn-icon btn-sm" title="Lihat Detail">
                <i data-lucide="eye"></i>
              </a>
              <a href="<?= base_url('/stok/edit/' . ($item['id'] ?? $i + 1)) ?>"
                 class="btn btn-secondary btn-icon btn-sm" title="Edit">
                <i data-lucide="pencil"></i>
              </a>
              <button
                onclick="confirmDelete('<?= base_url('/stok/delete/' . ($item['id'] ?? $i + 1)) ?>', '<?= esc($item['nama_bahan']) ?>')"
                class="btn btn-danger btn-icon btn-sm" title="Hapus">
                <i data-lucide="trash-2"></i>
              </button>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ── Empty State ── -->
  <div id="emptyState" class="empty-state" style="display:none;">
    <div class="empty-state-icon icon-warning">
      <i data-lucide="package-x"></i>
    </div>
    <h3>Tidak Ada Hasil</h3>
    <p>Tidak ditemukan bahan baku yang sesuai dengan filter pencarian Anda. Coba ubah kriteria atau reset filter.</p>
    <div class="empty-state-action" style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;">
      <button class="btn btn-secondary btn-sm" onclick="resetFilters()">
        <i data-lucide="refresh-cw"></i>
        Reset Filter
      </button>
      <?php if ($isAdmin): ?>
      <a href="<?= base_url('/stok/create') ?>" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i>
        Tambah Bahan Baku
      </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Pagination ── -->
  <?php if ($pager): ?>
  <div class="pagination">
    <span class="pagination-info">
      Halaman <?= $pager->getCurrentPage() ?> dari <?= $pager->getPageCount() ?>
    </span>
    <div class="pagination-controls">
      <?= $pager->links('default', 'default_full') ?>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /.card -->

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay" style="display:none;" x-data="{ show: false }">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">
        <i data-lucide="alert-triangle" style="width:18px;height:18px;color:var(--status-danger);display:inline;vertical-align:middle;margin-right:6px;"></i>
        Konfirmasi Hapus
      </h3>
      <button class="modal-close" onclick="closeDeleteModal()">
        <i data-lucide="x"></i>
      </button>
    </div>
    <div class="modal-body">
      <p style="color:var(--text-secondary);">
        Apakah Anda yakin ingin menghapus bahan baku
        <strong id="deleteItemName" style="color:var(--text-primary);"></strong>?
        Tindakan ini tidak dapat dibatalkan.
      </p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
      <a id="deleteConfirmBtn" href="#" class="btn btn-danger">
        <i data-lucide="trash-2"></i>
        Ya, Hapus
      </a>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function stokTable() {
    return {
      search: '',
      filterKategori: '',
      filterStatus: '',
      filteredCount: <?= count($stokList) ?>,

      filterTable() {
        const rows = document.querySelectorAll('.stok-row');
        let count = 0;
        const s = this.search.toLowerCase().trim();
        const k = this.filterKategori;
        const st = this.filterStatus;

        rows.forEach(row => {
          const nama     = row.dataset.nama     ?? '';
          const kode     = row.dataset.kode     ?? '';
          const kategori = row.dataset.kategori ?? '';
          const status   = row.dataset.status   ?? '';

          const matchSearch   = !s || nama.includes(s) || kode.includes(s);
          const matchKategori = !k || kategori === k;
          const matchStatus   = !st || status === st;

          if (matchSearch && matchKategori && matchStatus) {
            row.style.display = '';
            count++;
          } else {
            row.style.display = 'none';
          }
        });

        this.filteredCount = count;
        document.getElementById('emptyState').style.display = count === 0 ? 'flex' : 'none';
      },
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
    document.querySelectorAll('.stok-row').forEach(r => r.style.display = '');
    document.getElementById('emptyState').style.display = 'none';
  }

  // Close modal on overlay click
  document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });
</script>
<?= $this->endSection() ?>
