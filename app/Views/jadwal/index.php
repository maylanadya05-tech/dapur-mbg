<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$session = session();
$role    = $session->get('user_role') ?? 'viewer';
$isAdmin = in_array($role, ['admin', 'superadmin', 'produksi']);

$jadwalList = $jadwalList ?? [];
if (empty($jadwalList)) {
  $jadwalList = [
    [
      'id' => 1,
      'nama_siklus' => 'Siklus Gizi Utama - Juni A',
      'tanggal_mulai' => '2026-06-01',
      'durasi_hari' => 5,
      'status' => 'selesai',
      'detail_menu' => [
        1 => 'Nasi Putih + Ayam Semur + Sop Sayur',
        2 => 'Nasi Kuning + Ayam Goreng + Tempe Orek',
        3 => 'Nasi Uduk + Telur Balado + Bihun Goreng',
        4 => 'Nasi Putih + Daging Rendang + Sayur Nangka',
        5 => 'Nasi Goreng Kampung + Telur Ceplok + Kerupuk'
      ]
    ],
    [
      'id' => 2,
      'nama_siklus' => 'Siklus Gizi Utama - Juni B',
      'tanggal_mulai' => '2026-06-15',
      'durasi_hari' => 5,
      'status' => 'aktif',
      'detail_menu' => [
        1 => 'Nasi Putih + Daging Semur + Sop Sayur',
        2 => 'Nasi Kuning + Ayam Goreng + Sayur Asem',
        3 => 'Nasi Goreng Merah + Telur Dadar + Capcay',
        4 => 'Nasi Putih + Ayam Bakar + Tumis Kangkung',
        5 => 'Nasi Kuning + Empal Daging + Sayur Lodeh'
      ]
    ],
    [
      'id' => 3,
      'nama_siklus' => 'Siklus Variasi Karbo - Akhir Juni',
      'tanggal_mulai' => '2026-06-29',
      'durasi_hari' => 3,
      'status' => 'terjadwal',
      'detail_menu' => [
        1 => 'Mie Goreng Special + Telur Mata Sapi + Buah Pisang',
        2 => 'Nasi Uduk + Ayam Goreng Lengkuas + Tahu Goreng',
        3 => 'Nasi Putih + Ikan Kembung Goreng + Tumis Buncis'
      ]
    ]
  ];
}
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Jadwal Siklus Menu</h1>
    <p class="page-subtitle">Kalender dan siklus menu masakan Makan Bergizi Gratis (MBG) Dapur Utama</p>
  </div>
  <div class="page-header-actions">
    <?php if ($isAdmin): ?>
    <a href="<?= base_url('/jadwal/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="plus"></i>
      Buat Siklus Baru
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ══ CYCLES GRID ══ -->
<div style="display:flex;flex-direction:column;gap:1.5rem;" x-data="jadwalManager()">
  
  <?php foreach ($jadwalList as $item): ?>
  <?php
    $statusClass = match($item['status']) {
      'aktif' => 'badge-success',
      'terjadwal' => 'badge-info',
      'selesai' => 'badge-neutral',
      default => 'badge-neutral'
    };
    $statusLabel = match($item['status']) {
      'aktif' => 'Siklus Aktif',
      'terjadwal' => 'Terjadwal',
      'selesai' => 'Selesai',
      default => ucfirst($item['status'])
    };
  ?>
  <div class="card" style="border: 1px solid <?= $item['status'] === 'aktif' ? 'var(--border-accent)' : 'var(--border-subtle)' ?>;">
    
    <!-- Cycle Header Info -->
    <div style="display:flex;justify-content:space-between;align-items:start;border-bottom:1px solid var(--border-subtle);padding-bottom:1rem;margin-bottom:1.25rem;flex-wrap:wrap;gap:1rem;">
      <div>
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.25rem;">
          <h3 style="font-size:1.15rem;font-weight:700;color:var(--text-primary);"><?= esc($item['nama_siklus']) ?></h3>
          <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
        </div>
        <p style="font-size:0.8rem;color:var(--text-muted);display:flex;align-items:center;gap:0.375rem;">
          <i data-lucide="calendar" style="width:13px;height:13px;"></i>
          Tanggal Mulai: <strong><?= date('d M Y', strtotime($item['tanggal_mulai'])) ?></strong> &nbsp;•&nbsp; Durasi: <strong><?= $item['durasi_hari'] ?> Hari Kerja</strong>
        </p>
      </div>

      <div style="display:flex;gap:0.5rem;align-items:center;">
        <?php if ($item['status'] === 'aktif'): ?>
        <a href="<?= base_url('/jadwal/estimasi-bahan/' . $item['id']) ?>" class="btn btn-info btn-sm" style="display:inline-flex;align-items:center;gap:0.375rem;">
          <i data-lucide="calculator" style="width:14px;height:14px;"></i> Estimasi Bahan
        </a>
        <?php if ($isAdmin): ?>
        <form action="<?= base_url('/jadwal/generate-batch/' . $item['id']) ?>" method="POST" style="display:inline;margin:0;">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-primary btn-sm" style="display:inline-flex;align-items:center;gap:0.375rem;">
            <i data-lucide="play-circle" style="width:14px;height:14px;"></i> Generate Batch Hari Ini
          </button>
        </form>
        <?php endif; ?>
        <?php endif; ?>

        <a href="<?= base_url('/jadwal/edit/' . $item['id']) ?>" class="btn btn-secondary btn-sm" style="display:inline-flex;align-items:center;gap:0.375rem;">
          <i data-lucide="pencil" style="width:14px;height:14px;"></i> Edit Siklus
        </a>
        <?php if ($isAdmin): ?>
        <button
          onclick="confirmDelete('<?= base_url('/jadwal/delete/' . $item['id']) ?>', '<?= esc($item['nama_siklus']) ?>')"
          class="btn btn-danger btn-icon btn-sm" style="padding:6px;" title="Hapus Siklus">
          <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
        </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Cycle Row Blocks (Day by Day) -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:1rem;">
      <?php for($d=1; $d<=$item['durasi_hari']; $d++): ?>
      <div style="background:var(--bg-card-hover);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);padding:1rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;border-bottom:1px dashed var(--border-subtle);padding-bottom:0.25rem;">
          <span style="font-size:0.75rem;font-weight:700;color:var(--emerald);text-transform:uppercase;">Hari <?= $d ?></span>
          <i data-lucide="utensils" style="width:12px;height:12px;color:var(--text-muted);"></i>
        </div>
        <div style="font-size:0.875rem;font-weight:600;color:var(--text-primary);line-height:1.4;">
          <?= esc($item['detail_menu'][$d] ?? 'Belum ada menu') ?>
        </div>
      </div>
      <?php endfor; ?>
    </div>

  </div>
  <?php endforeach; ?>

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
        Apakah Anda yakin ingin menghapus jadwal siklus <strong id="deleteItemName" style="color:var(--text-primary);"></strong>?
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
  function jadwalManager() {
    return {
      init() {
        // init
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
