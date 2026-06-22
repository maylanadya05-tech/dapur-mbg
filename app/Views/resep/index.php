<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$session  = session();
$role     = $session->get('user_role') ?? 'viewer';
$isAdmin  = in_array($role, ['admin', 'superadmin', 'gudang']);

// Recipes data from controller (with fallback demo data)
$resepList = $resepList ?? [];

if (empty($resepList)) {
  $resepList = [
    [
      'id' => 1,
      'kode' => 'RSP-2026-001',
      'nama_menu' => 'Nasi Kuning Harum',
      'deskripsi' => 'Nasi kuning wangi kelapa dan pandan, menu pokok untuk Makan Bergizi Gratis.',
      'kategori' => 'Makanan Pokok',
      'total_kalori' => 350.5,
      'total_protein' => 6.2,
      'total_karbohidrat' => 78.4,
      'porsi_standar' => 1,
      'foto' => '',
      'is_active' => 1,
      'bom_count' => 5
    ],
    [
      'id' => 2,
      'kode' => 'RSP-2026-002',
      'nama_menu' => 'Ayam Suwir Rica',
      'deskripsi' => 'Lauk pauk ayam suwir bumbu rica pedas sedang, tinggi protein.',
      'kategori' => 'Lauk Pauk',
      'total_kalori' => 220.0,
      'total_protein' => 24.5,
      'total_karbohidrat' => 4.1,
      'porsi_standar' => 1,
      'foto' => '',
      'is_active' => 1,
      'bom_count' => 8
    ],
    [
      'id' => 3,
      'kode' => 'RSP-2026-003',
      'nama_menu' => 'Sayur Tumis Kacang Panjang',
      'deskripsi' => 'Tumisan kacang panjang segar dengan bawang putih dan cabai merah tipis.',
      'kategori' => 'Sayuran',
      'total_kalori' => 85.0,
      'total_protein' => 2.8,
      'total_karbohidrat' => 12.0,
      'porsi_standar' => 1,
      'foto' => '',
      'is_active' => 1,
      'bom_count' => 6
    ],
    [
      'id' => 4,
      'kode' => 'RSP-2026-004',
      'nama_menu' => 'Pisang Ambon',
      'deskripsi' => 'Buah cuci mulut sehat pendamping menu harian sekolah.',
      'kategori' => 'Buah',
      'total_kalori' => 105.0,
      'total_protein' => 1.3,
      'total_karbohidrat' => 27.0,
      'porsi_standar' => 1,
      'foto' => '',
      'is_active' => 1,
      'bom_count' => 1
    ],
    [
      'id' => 5,
      'kode' => 'RSP-2026-005',
      'nama_menu' => 'Susu Segar UHT 200ml',
      'deskripsi' => 'Minuman pelengkap gizi harian siswa.',
      'kategori' => 'Minuman',
      'total_kalori' => 120.0,
      'total_protein' => 7.0,
      'total_karbohidrat' => 11.0,
      'porsi_standar' => 1,
      'foto' => '',
      'is_active' => 1,
      'bom_count' => 1
    ]
  ];
}
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Daftar Resep & Menu</h1>
    <p class="page-subtitle">Kelola data gizi dan komposisi menu makanan</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/resep/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="plus"></i>
      Tambah Resep
    </a>
  </div>
</div>

<div x-data="recipeList()">
  <!-- ══ FILTER & SEARCH ROW ══ -->
  <div class="card" style="padding: 1rem; margin-bottom: 1.5rem;">
    <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:center;">
      <div class="input-group search-input" style="flex: 1; min-width: 250px;">
        <span class="input-group-icon"><i data-lucide="search"></i></span>
        <input
          type="text"
          class="form-control"
          placeholder="Cari menu, kode resep..."
          x-model="search"
          @input="filterRecipes"
          style="padding-left: 2.5rem;"
        >
      </div>

      <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <button
          class="btn btn-sm"
          :class="categoryFilter === '' ? 'btn-primary' : 'btn-secondary'"
          @click="setCategory('')"
        >
          Semua
        </button>
        <button
          class="btn btn-sm"
          :class="categoryFilter === 'Makanan Pokok' ? 'btn-primary' : 'btn-secondary'"
          @click="setCategory('Makanan Pokok')"
        >
          Makanan Pokok
        </button>
        <button
          class="btn btn-sm"
          :class="categoryFilter === 'Lauk Pauk' ? 'btn-primary' : 'btn-secondary'"
          @click="setCategory('Lauk Pauk')"
        >
          Lauk Pauk
        </button>
        <button
          class="btn btn-sm"
          :class="categoryFilter === 'Sayuran' ? 'btn-primary' : 'btn-secondary'"
          @click="setCategory('Sayuran')"
        >
          Sayuran
        </button>
        <button
          class="btn btn-sm"
          :class="categoryFilter === 'Buah' ? 'btn-primary' : 'btn-secondary'"
          @click="setCategory('Buah')"
        >
          Buah
        </button>
        <button
          class="btn btn-sm"
          :class="categoryFilter === 'Minuman' ? 'btn-primary' : 'btn-secondary'"
          @click="setCategory('Minuman')"
        >
          Minuman
        </button>
      </div>
      
      <div style="margin-left: auto;">
        <span class="text-secondary text-sm">
          Menampilkan <strong x-text="filteredCount" style="color:var(--text-primary);"></strong> menu
        </span>
      </div>
    </div>
  </div>

  <!-- ══ RECIPES GRID ══ -->
  <div class="recipes-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
    <?php foreach ($resepList as $resep): ?>
    <?php
      $kategori = $resep['kategori'];
      $badgeClass = match($kategori) {
        'Makanan Pokok' => 'badge-info',
        'Lauk Pauk'     => 'badge-danger',
        'Sayuran'       => 'badge-success',
        'Buah'          => 'badge-warning',
        'Minuman'       => 'badge-neutral',
        default         => 'badge-neutral'
      };
    ?>
    <div
      class="card recipe-card"
      data-nama="<?= esc(strtolower($resep['nama_menu'])) ?>"
      data-kode="<?= esc(strtolower($resep['kode'])) ?>"
      data-kategori="<?= esc($resep['kategori']) ?>"
      style="padding: 0; overflow: hidden; display: flex; flex-direction: column; position: relative;"
    >
      <!-- Recipe Image / Icon Banner -->
      <div style="height: 140px; background: linear-gradient(135deg, var(--bg-card-hover) 0%, var(--bg-sidebar) 100%); position: relative; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid var(--border-subtle);">
        <?php if (!empty($resep['foto'])): ?>
          <img src="<?= base_url('uploads/resep/' . $resep['foto']) ?>" alt="<?= esc($resep['nama_menu']) ?>" style="width:100%; height:100%; object-fit:cover;">
        <?php else: ?>
          <div style="text-align: center; color: var(--text-muted);">
            <?php
              $icon = match($kategori) {
                'Makanan Pokok' => 'soup',
                'Lauk Pauk'     => 'drumstick',
                'Sayuran'       => 'salad',
                'Buah'          => 'apple',
                'Minuman'       => 'cup-soda',
                default         => 'cooking-pot'
              };
            ?>
            <i data-lucide="<?= $icon ?>" style="width: 48px; height: 48px; opacity: 0.35; stroke-width: 1.5; color: var(--emerald);"></i>
          </div>
        <?php endif; ?>
        
        <!-- Category Badge floating -->
        <span class="badge <?= $badgeClass ?>" style="position: absolute; top: 12px; right: 12px;">
          <?= esc($resep['kategori']) ?>
        </span>

        <!-- Recipe Code floating -->
        <span style="position: absolute; bottom: 8px; left: 12px; font-family: monospace; font-size: 0.72rem; background: rgba(0,0,0,0.5); padding: 0.15rem 0.4rem; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1); color: var(--text-secondary);">
          <?= esc($resep['kode']) ?>
        </span>
      </div>

      <!-- Recipe Info -->
      <div style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; line-height: 1.3;">
          <?= esc($resep['nama_menu']) ?>
        </h3>
        
        <p style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.4; margin-bottom: 1.25rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; flex: 1;">
          <?= esc($resep['deskripsi']) ?>
        </p>

        <!-- BOM Info -->
        <div style="display:flex; align-items:center; gap:0.375rem; margin-bottom: 1rem; font-size:0.8rem; color: var(--text-secondary);">
          <i data-lucide="layers" style="width: 15px; height: 15px; color: var(--emerald);"></i>
          <span>BOM: <strong><?= $resep['bom_count'] ?></strong> jenis bahan baku</span>
        </div>

        <!-- Nutrition Profiles (Row) -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; background: var(--bg-primary); padding: 0.75rem; border-radius: var(--border-radius-sm); margin-bottom: 1.25rem; border: 1px solid var(--border-subtle);">
          <div style="text-align: center;">
            <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Kalori</div>
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); margin-top: 2px;">
              <?= number_format($resep['total_kalori'], 1) ?> <span style="font-size:0.6rem; font-weight:normal; color:var(--text-muted);">kcal</span>
            </div>
          </div>
          <div style="text-align: center; border-left: 1px solid var(--border-subtle); border-right: 1px solid var(--border-subtle);">
            <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Protein</div>
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--emerald); margin-top: 2px;">
              <?= number_format($resep['total_protein'], 1) ?> <span style="font-size:0.6rem; font-weight:normal; color:var(--text-muted);">g</span>
            </div>
          </div>
          <div style="text-align: center;">
            <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Karbo</div>
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--status-info); margin-top: 2px;">
              <?= number_format($resep['total_karbohidrat'], 1) ?> <span style="font-size:0.6rem; font-weight:normal; color:var(--text-muted);">g</span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 0.5rem; justify-content: space-between; border-top: 1px solid var(--border-subtle); padding-top: 1rem;">
          <a href="<?= base_url('/resep/show/' . $resep['id']) ?>" class="btn btn-ghost btn-sm" style="flex: 1; justify-content: center; text-align: center;">
            <i data-lucide="eye" style="width: 14px; height: 14px; margin-right:4px;"></i>
            Detail
          </a>
          <a href="<?= base_url('/resep/edit/' . $resep['id']) ?>" class="btn btn-secondary btn-sm" style="flex: 1; justify-content: center; text-align: center;">
            <i data-lucide="pencil" style="width: 14px; height: 14px; margin-right:4px;"></i>
            Edit
          </a>
          <button
            onclick="confirmDelete('<?= base_url('/resep/delete/' . $resep['id']) ?>', '<?= esc($resep['nama_menu']) ?>')"
            class="btn btn-danger btn-sm"
            style="flex: 0 0 auto; width: 36px; height: 36px; padding: 0; justify-content: center; align-items: center; display: inline-flex;"
          >
            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
          </button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ══ EMPTY STATE ══ -->
  <div id="emptyState" class="empty-state" style="display:none; margin-top: 2rem;">
    <div class="empty-state-icon icon-success">
      <i data-lucide="salad"></i>
    </div>
    <h3>Resep Tidak Ditemukan</h3>
    <p>Tidak ada resep yang cocok dengan pencarian Anda. Coba kata kunci lain atau reset filter.</p>
    <div class="empty-state-action" style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;">
      <button class="btn btn-secondary btn-sm" @click="resetFilters()">
        <i data-lucide="refresh-cw"></i>
        Reset Filter
      </button>
      <a href="<?= base_url('/resep/create') ?>" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i>
        Tambah Resep Baru
      </a>
    </div>
  </div>
</div>

<!-- ══ DELETE CONFIRMATION MODAL ══ -->
<div id="deleteModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title" style="display: flex; align-items: center; gap: 0.5rem; color: var(--status-danger);">
        <i data-lucide="alert-triangle"></i>
        Hapus Resep Menu
      </h3>
      <button class="modal-close" onclick="closeDeleteModal()">
        <i data-lucide="x"></i>
      </button>
    </div>
    <div class="modal-body">
      <p style="color:var(--text-secondary); font-size: 0.9rem;">
        Apakah Anda yakin ingin menghapus resep menu
        <strong id="deleteItemName" style="color:var(--text-primary);"></strong>?
        Menghapus resep akan menghapus komposisi bahan baku (BOM) terkait. Tindakan ini tidak dapat dibatalkan.
      </p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary btn-sm" onclick="closeDeleteModal()">Batal</button>
      <form id="deleteForm" method="POST" action="" style="display: inline-block;">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger btn-sm">
          <i data-lucide="trash-2"></i>
          Ya, Hapus Resep
        </button>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function recipeList() {
    return {
      search: '',
      categoryFilter: '',
      filteredCount: <?= count($resepList) ?>,

      setCategory(cat) {
        this.categoryFilter = cat;
        this.filterRecipes();
      },

      filterRecipes() {
        const cards = document.querySelectorAll('.recipe-card');
        let count = 0;
        const s = this.search.toLowerCase().trim();
        const k = this.categoryFilter;

        cards.forEach(card => {
          const nama     = card.dataset.nama     ?? '';
          const kode     = card.dataset.kode     ?? '';
          const kategori = card.dataset.kategori ?? '';

          const matchSearch   = !s || nama.includes(s) || kode.includes(s);
          const matchKategori = !k || kategori === k;

          if (matchSearch && matchKategori) {
            card.style.display = 'flex';
            count++;
          } else {
            card.style.display = 'none';
          }
        });

        this.filteredCount = count;
        document.getElementById('emptyState').style.display = count === 0 ? 'flex' : 'none';
        if (typeof lucide !== 'undefined') lucide.createIcons();
      },

      resetFilters() {
        this.search = '';
        this.categoryFilter = '';
        this.filterRecipes();
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

  // Close modal on overlay click
  document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });
</script>
<?= $this->endSection() ?>
