<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$session = session();
$role    = $session->get('user_role') ?? 'viewer';
$isAdmin = in_array($role, ['admin', 'superadmin', 'pembelian']);

$supplierList = $supplierList ?? [];
if (empty($supplierList)) {
  $supplierList = [
    ['id' => 1, 'nama' => 'PT Beras Cianjur Jaya', 'kategori' => 'Bahan Pokok / Karbohidrat', 'kontak_nama' => 'H. Endang', 'no_telp' => '081234567890', 'email' => 'contact@berascianjur.com', 'rating' => 5, 'status' => 'aktif', 'alamat' => 'Cianjur, Jawa Barat'],
    ['id' => 2, 'nama' => 'CV Protein Prima', 'kategori' => 'Daging / Protein', 'kontak_nama' => 'Agus Hariyadi', 'no_telp' => '081398765432', 'email' => 'sales@proteinprima.co.id', 'rating' => 4, 'status' => 'aktif', 'alamat' => 'Bogor Timur, Kota Bogor'],
    ['id' => 3, 'nama' => 'UD Agro Segar Semesta', 'kategori' => 'Sayuran & Buah', 'kontak_nama' => 'Siti Aminah', 'no_telp' => '087811223344', 'email' => 'info@agrosegar.com', 'rating' => 4, 'status' => 'aktif', 'alamat' => 'Ciawi, Kabupaten Bogor'],
    ['id' => 4, 'nama' => 'CV Bumbu Nusantara', 'kategori' => 'Rempah & Bumbu', 'kontak_nama' => 'Rian Prabowo', 'no_telp' => '085677889900', 'email' => 'bumbu.nusantara@gmail.com', 'rating' => 3, 'status' => 'nonaktif', 'alamat' => 'Tanah Sareal, Kota Bogor'],
  ];
}

$totalSuppliers = count($supplierList);
$activeSuppliers = count(array_filter($supplierList, fn($s) => $s['status'] === 'aktif'));
$avgRating = $totalSuppliers > 0 ? array_sum(array_column($supplierList, 'rating')) / $totalSuppliers : 0;
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Daftar Supplier</h1>
    <p class="page-subtitle">Mitra penyedia bahan baku berkualitas untuk Dapur MBG</p>
  </div>
  <div class="page-header-actions">
    <?php if ($isAdmin): ?>
    <a href="<?= base_url('/supplier/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="plus"></i>
      Tambah Supplier
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ══ SUMMARY STATS ══ -->
<div class="stats-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;margin-bottom:1.5rem;">
  
  <div class="stat-card accent-success">
    <div class="stat-card-header">
      <span class="stat-card-label">Total Mitra</span>
      <div class="stat-card-icon"><i data-lucide="users"></i></div>
    </div>
    <div class="stat-card-value"><?= $totalSuppliers ?></div>
    <div class="stat-card-footer">Supplier terdaftar resmi</div>
  </div>

  <div class="stat-card accent-success">
    <div class="stat-card-header">
      <span class="stat-card-label">Supplier Aktif</span>
      <div class="stat-card-icon"><i data-lucide="check-circle"></i></div>
    </div>
    <div class="stat-card-value"><?= $activeSuppliers ?></div>
    <div class="stat-card-footer">Siap mengirimkan supply pasokan</div>
  </div>

  <div class="stat-card accent-warning">
    <div class="stat-card-header">
      <span class="stat-card-label">Rata-Rata Rating</span>
      <div class="stat-card-icon"><i data-lucide="star"></i></div>
    </div>
    <div class="stat-card-value"><?= number_format($avgRating, 1) ?>/5.0</div>
    <div class="stat-card-footer">Skor performa ketepatan & mutu</div>
  </div>

</div>

<!-- ── Filter Row ── -->
<div class="card" style="padding:1.25rem 1.5rem;margin-bottom:1.5rem;" x-data="supplierFilters()">
  <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
    <div class="input-group search-input" style="position:relative;flex:1;min-width:240px;">
      <span class="input-group-icon" style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted);"><i data-lucide="search" style="width:18px;height:18px;"></i></span>
      <input
        type="text"
        class="form-control"
        placeholder="Cari nama supplier, kontak, alamat..."
        x-model="search"
        @input="filterCards"
        style="padding-left:2.5rem;width:100%;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding-top:0.5rem;padding-bottom:0.5rem;"
      >
    </div>

    <select class="form-select" x-model="filterStatus" @change="filterCards" style="min-width:150px;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding:0.5rem;">
      <option value="">Semua Status</option>
      <option value="aktif">Aktif</option>
      <option value="nonaktif">Non-Aktif</option>
    </select>
  </div>
</div>

<!-- ══ GRID OF SUPPLIER CARDS ══ -->
<div class="supplier-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:1.5rem;">
  <?php foreach ($supplierList as $item): ?>
  <?php
    $statusClass = $item['status'] === 'aktif' ? 'badge-success' : 'badge-danger';
  ?>
  <div
    class="card supplier-card"
    data-nama="<?= esc(strtolower($item['nama'])) ?>"
    data-kontak="<?= esc(strtolower($item['kontak_nama'])) ?>"
    data-alamat="<?= esc(strtolower($item['alamat'])) ?>"
    data-status="<?= esc($item['status']) ?>"
    style="display:flex;flex-direction:column;justify-content:space-between;height:100%;position:relative;"
  >
    <!-- Top info -->
    <div>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.75rem;">
        <span class="badge badge-neutral" style="font-size:0.7rem;text-transform:uppercase;font-weight:700;"><?= esc($item['kategori']) ?></span>
        <span class="badge <?= $statusClass ?>"><?= ucfirst(esc($item['status'])) ?></span>
      </div>

      <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-primary);margin-bottom:0.5rem;line-height:1.3;"><?= esc($item['nama']) ?></h3>
      
      <p style="font-size:0.8rem;color:var(--text-muted);display:flex;align-items:center;gap:0.375rem;margin-bottom:1rem;">
        <i data-lucide="map-pin" style="width:13px;height:13px;"></i>
        <?= esc($item['alamat']) ?>
      </p>

      <!-- Contact block -->
      <div style="font-size:0.875rem;background:var(--bg-card-hover);padding:0.75rem 1rem;border-radius:var(--border-radius-sm);margin-bottom:1rem;display:flex;flex-direction:column;gap:0.375rem;">
        <div style="color:var(--text-muted);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.04em;">Kontak Person</div>
        <strong style="color:var(--text-primary);"><?= esc($item['kontak_nama']) ?></strong>
        <div style="display:flex;align-items:center;gap:0.5rem;color:var(--text-secondary);font-size:0.8rem;margin-top:0.25rem;">
          <i data-lucide="phone" style="width:13px;height:13px;"></i>
          <?= esc($item['no_telp']) ?>
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem;color:var(--text-secondary);font-size:0.8rem;">
          <i data-lucide="mail" style="width:13px;height:13px;"></i>
          <?= esc($item['email']) ?>
        </div>
      </div>
    </div>

    <!-- Rating Scorecard Footer -->
    <div style="border-top:1px solid var(--border-subtle);padding-top:0.875rem;margin-top:auto;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:0.25rem;">Performa Mitra</div>
        <div style="display:flex;gap:0.125rem;color:var(--status-warning);">
          <?php for($i=1; $i<=5; $i++): ?>
            <i data-lucide="star" style="width:14px;height:14px;<?= $i <= $item['rating'] ? 'fill:var(--status-warning);' : 'opacity:0.3;' ?>"></i>
          <?php endfor; ?>
        </div>
      </div>
      <div>
        <div style="display:flex;gap:0.375rem;">
          <a href="<?= base_url('/supplier/edit/' . $item['id']) ?>" class="btn btn-secondary btn-icon btn-sm" style="padding:4px;" title="Edit Supplier">
            <i data-lucide="pencil" style="width:16px;height:16px;"></i>
          </a>
          <?php if ($isAdmin): ?>
          <button
            onclick="confirmDelete('<?= base_url('/supplier/delete/' . $item['id']) ?>', '<?= esc($item['nama']) ?>')"
            class="btn btn-danger btn-icon btn-sm" style="padding:4px;" title="Hapus Supplier">
            <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Empty State -->
<div id="emptyState" style="display:none;flex-direction:column;align-items:center;justify-content:center;padding:3rem;text-align:center;">
  <div style="width:60px;height:60px;border-radius:50%;background:var(--border-subtle);display:flex;align-items:center;justify-content:center;color:var(--text-muted);margin-bottom:1rem;">
    <i data-lucide="store" style="width:30px;height:30px;"></i>
  </div>
  <h3 style="color:var(--text-primary);font-size:1.1rem;margin-bottom:0.5rem;">Tidak Ada Supplier</h3>
  <p style="color:var(--text-muted);font-size:0.875rem;max-width:320px;margin-bottom:1.5rem;">Coba ubah status filter atau kata kunci pencarian Anda.</p>
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
        Apakah Anda yakin ingin menghapus supplier <strong id="deleteItemName" style="color:var(--text-primary);"></strong> dari database?
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
  function supplierFilters() {
    return {
      search: '',
      filterStatus: '',

      filterCards() {
        const cards = document.querySelectorAll('.supplier-card');
        let count = 0;
        const s = this.search.toLowerCase().trim();
        const st = this.filterStatus;

        cards.forEach(card => {
          const nama    = card.dataset.nama    || '';
          const kontak  = card.dataset.kontak  || '';
          const alamat  = card.dataset.alamat  || '';
          const status  = card.dataset.status  || '';

          const matchSearch = !s || nama.includes(s) || kontak.includes(s) || alamat.includes(s);
          const matchStatus = !st || status === st;

          if (matchSearch && matchStatus) {
            card.style.display = 'flex';
            count++;
          } else {
            card.style.display = 'none';
          }
        });

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

  document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });
</script>
<?= $this->endSection() ?>
