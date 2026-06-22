<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$session = session();
$role    = $session->get('user_role') ?? 'viewer';
$isAdmin = in_array($role, ['admin', 'superadmin']);

$userList = $userList ?? [];
if (empty($userList)) {
  $userList = [
    ['id' => 1, 'nama' => 'Rian Hidayat', 'email' => 'rian.hidayat@dapurmbg.go.id', 'no_telp' => '081234567890', 'role' => 'admin', 'status' => 'aktif', 'avatar' => null],
    ['id' => 2, 'nama' => 'Lilik Herawati', 'email' => 'lilik.h@dapurmbg.go.id', 'no_telp' => '081398765432', 'role' => 'pembelian', 'status' => 'aktif', 'avatar' => null],
    ['id' => 3, 'nama' => 'Agus Budiman', 'email' => 'agus.b@dapurmbg.go.id', 'no_telp' => '087811223344', 'role' => 'gudang', 'status' => 'aktif', 'avatar' => null],
    ['id' => 4, 'nama' => 'Siti Aisyah', 'email' => 'siti.aisyah@dapurmbg.go.id', 'no_telp' => '085677889900', 'role' => 'produksi', 'status' => 'aktif', 'avatar' => null],
    ['id' => 5, 'nama' => 'Dewi Lestari', 'email' => 'dewi.lestari@dapurmbg.go.id', 'no_telp' => '089988776655', 'role' => 'produksi', 'status' => 'nonaktif', 'avatar' => null],
  ];
}
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Manajemen Tim</h1>
    <p class="page-subtitle">Kelola hak akses, peranan staf, dan akun pengguna sistem Dapur MBG</p>
  </div>
  <div class="page-header-actions">
    <?php if ($isAdmin): ?>
    <a href="<?= base_url('/users/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="user-plus"></i>
      Tambah Anggota Tim
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ══ TEAM MEMBERS CARD ══ -->
<div class="card" style="padding:0;" x-data="usersManager()">
  
  <!-- Filter Row -->
  <div class="filter-row" style="display:flex;gap:1rem;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border-subtle);flex-wrap:wrap;">
    <div class="input-group search-input" style="position:relative;flex:1;min-width:240px;">
      <span class="input-group-icon" style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted);"><i data-lucide="search" style="width:18px;height:18px;"></i></span>
      <input
        type="text"
        class="form-control"
        placeholder="Cari nama, email, telepon..."
        x-model="search"
        @input="filterTable"
        style="padding-left:2.5rem;width:100%;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding-top:0.5rem;padding-bottom:0.5rem;"
      >
    </div>

    <select class="form-select" x-model="filterRole" @change="filterTable" style="min-width:150px;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding:0.5rem;">
      <option value="">Semua Peran</option>
      <option value="admin">Admin</option>
      <option value="pembelian">Pembelian</option>
      <option value="gudang">Gudang</option>
      <option value="produksi">Produksi</option>
    </select>

    <div style="flex:1;"></div>

    <span class="text-secondary text-sm" style="font-size:0.875rem;color:var(--text-secondary);">
      Menampilkan <strong x-text="filteredCount" style="color:var(--text-primary);"></strong> staf terdaftar
    </span>
  </div>

  <!-- Table -->
  <div class="table-wrapper" style="overflow-x:auto;">
    <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;">
      <thead>
        <tr style="border-bottom:1px solid var(--border-subtle);">
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;width:70px;">Staf</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Nama Lengkap</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Email</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">No. Telepon</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Peran Sistem</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;width:120px;">Status</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;width:120px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($userList as $item): ?>
        <?php
          $roleBadge = match($item['role']) {
            'admin' => 'badge-danger',
            'pembelian' => 'badge-warning',
            'gudang' => 'badge-info',
            'produksi' => 'badge-success',
            default => 'badge-neutral',
          };
          
          $initials = '';
          $words = explode(' ', $item['nama']);
          foreach($words as $w) {
            $initials .= strtoupper(substr($w, 0, 1));
          }
          $initials = substr($initials, 0, 2);
        ?>
        <tr
          class="user-row"
          data-nama="<?= esc(strtolower($item['nama'])) ?>"
          data-email="<?= esc(strtolower($item['email'])) ?>"
          data-role="<?= esc($item['role']) ?>"
          style="border-bottom:1px solid var(--border-subtle);transition:background-color 0.2s;"
          onmouseover="this.style.backgroundColor='var(--bg-card-hover)'"
          onmouseout="this.style.backgroundColor='transparent'"
        >
          <!-- Avatar Column -->
          <td style="padding:1rem 1.5rem;">
            <?php if($item['avatar']): ?>
              <img src="<?= base_url('uploads/avatars/' . $item['avatar']) ?>" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
            <?php else: ?>
              <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg, var(--emerald), hsl(180,60%,35%));display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:white;">
                <?= $initials ?>
              </div>
            <?php endif; ?>
          </td>
          
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <strong style="color:var(--text-primary);"><?= esc($item['nama']) ?></strong>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-secondary);"><?= esc($item['email']) ?></td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-secondary);"><?= esc($item['no_telp']) ?></td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <span class="badge <?= $roleBadge ?>"><?= ucfirst(esc($item['role'])) ?></span>
          </td>
          
          <!-- Status Toggle (Simulated Switch) -->
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <form action="<?= base_url('/users/toggle-status/' . $item['id']) ?>" method="POST" style="margin:0;">
              <?= csrf_field() ?>
              <label class="switch" style="position:relative;display:inline-block;width:40px;height:22px;margin:0;cursor:pointer;">
                <input
                  type="checkbox"
                  name="status"
                  value="1"
                  <?= $item['status'] === 'aktif' ? 'checked' : '' ?>
                  onchange="this.form.submit()"
                  style="opacity:0;width:0;height:0;"
                >
                <span class="slider" style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:var(--border-subtle);transition:.3s;border-radius:34px;"></span>
              </label>
            </form>
          </td>

          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <div style="display:flex;gap:0.5rem;">
              <a href="<?= base_url('/users/edit/' . $item['id']) ?>"
                 class="btn btn-secondary btn-icon btn-sm" style="padding:4px;" title="Edit Pengguna">
                <i data-lucide="pencil" style="width:16px;height:16px;"></i>
              </a>
              <?php if ($isAdmin): ?>
              <button
                onclick="confirmDelete('<?= base_url('/users/delete/' . $item['id']) ?>', '<?= esc($item['nama']) ?>')"
                class="btn btn-danger btn-icon btn-sm" style="padding:4px;" title="Hapus Pengguna">
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
      <i data-lucide="users" style="width:30px;height:30px;"></i>
    </div>
    <h3 style="color:var(--text-primary);font-size:1.1rem;margin-bottom:0.5rem;">Staf Tidak Ditemukan</h3>
    <p style="color:var(--text-muted);font-size:0.875rem;max-width:320px;margin-bottom:1.5rem;">Coba gunakan kata pencarian lain atau saring dengan peran yang sesuai.</p>
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
        Apakah Anda yakin ingin menghapus akun pengguna <strong id="deleteItemName" style="color:var(--text-primary);"></strong> dari tim?
        Tindakan ini akan mencabut seluruh akses masuk sistem.
      </p>
    </div>
    <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:0.75rem;">
      <button class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
      <a id="deleteConfirmBtn" href="#" class="btn btn-danger">Hapus Akun</a>
    </div>
  </div>
</div>

<!-- Switch Slider Style Overrides -->
<style>
  input:checked + .slider {
    background-color: var(--emerald) !important;
  }
  .slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
  }
  input:checked + .slider:before {
    transform: translateX(18px);
  }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function usersManager() {
    return {
      search: '',
      filterRole: '',
      filteredCount: <?= count($userList) ?>,

      filterTable() {
        const rows = document.querySelectorAll('.user-row');
        let count = 0;
        const s = this.search.toLowerCase().trim();
        const r = this.filterRole;

        rows.forEach(row => {
          const nama  = row.dataset.nama  || '';
          const email = row.dataset.email || '';
          const role  = row.dataset.role  || '';

          const matchSearch = !s || nama.includes(s) || email.includes(s);
          const matchRole = !r || role === r;

          if (matchSearch && matchRole) {
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

  document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });
</script>
<?= $this->endSection() ?>
