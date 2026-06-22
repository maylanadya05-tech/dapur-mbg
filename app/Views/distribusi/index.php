<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$session = session();
$role    = $session->get('user_role') ?? 'viewer';
$isAdmin = in_array($role, ['admin', 'superadmin', 'produksi', 'gudang']);

$distribusiList = [];
$rawList = $riwayat ?? [];
foreach ($rawList as $d) {
  $distribusiList[] = [
    'id'              => $d['id'],
    'no_surat_jalan'  => 'SJ-' . date('dmy', strtotime($d['tanggal_distribusi'])) . '-' . str_pad($d['id'], 3, '0', STR_PAD_LEFT),
    'batch_no'        => $d['batch_no'] ?? '-',
    'nama_sekolah'    => $d['nama_sekolah'] ?? '-',
    'porsi'           => $d['jumlah_porsi'] ?? 0,
    'kurir'           => $d['kurir'] ?? '-',
    'kendaraan'       => ($d['kendaraan'] ?? '-') . ($d['no_polisi'] ? ' (' . $d['no_polisi'] . ')' : ''),
    'status'          => $d['status'],
    'tanggal'         => $d['tanggal_distribusi'],
    'waktu_berangkat' => $d['waktu_kirim'] ? date('H:i', strtotime($d['waktu_kirim'])) : null,
    'waktu_tiba'      => $d['waktu_terima'] ? date('H:i', strtotime($d['waktu_terima'])) : null,
  ];
}

$totalShipments = count($distribusiList);
$scheduled = count(array_filter($distribusiList, fn($d) => $d['status'] === 'jadwal'));
$intransit = count(array_filter($distribusiList, fn($d) => $d['status'] === 'dikirim'));
$completed = count(array_filter($distribusiList, fn($d) => $d['status'] === 'selesai'));
$failed = count(array_filter($distribusiList, fn($d) => $d['status'] === 'bermasalah'));
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Logistik & Distribusi</h1>
    <p class="page-subtitle">Pelacakan pengiriman makanan gratis ke sekolah tujuan secara real-time</p>
  </div>
  <div class="page-header-actions">
    <?php if ($isAdmin): ?>
    <a href="<?= base_url('/distribusi/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="plus"></i>
      Jadwalkan Pengiriman
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ══ SUMMARY CARDS ══ -->
<div class="stats-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;margin-bottom:1.5rem;">
  
  <div class="stat-card accent-info">
    <div class="stat-card-header">
      <span class="stat-card-label">Dalam Antrean</span>
      <div class="stat-card-icon"><i data-lucide="clock"></i></div>
    </div>
    <div class="stat-card-value"><?= $scheduled ?></div>
    <div class="stat-card-footer">Siap diberangkatkan</div>
  </div>

  <div class="stat-card accent-warning">
    <div class="stat-card-header">
      <span class="stat-card-label">Sedang Dikirim</span>
      <div class="stat-card-icon"><i data-lucide="truck"></i></div>
    </div>
    <div class="stat-card-value"><?= $intransit ?></div>
    <div class="stat-card-footer">Armada sedang di jalan</div>
  </div>

  <div class="stat-card accent-success">
    <div class="stat-card-header">
      <span class="stat-card-label">Selesai Diterima</span>
      <div class="stat-card-icon"><i data-lucide="check-square"></i></div>
    </div>
    <div class="stat-card-value"><?= $completed ?></div>
    <div class="stat-card-footer">Porsi sukses terdistribusi</div>
  </div>

  <div class="stat-card accent-danger">
    <div class="stat-card-header">
      <span class="stat-card-label">Ada Masalah</span>
      <div class="stat-card-icon"><i data-lucide="alert-octagon"></i></div>
    </div>
    <div class="stat-card-value"><?= $failed ?></div>
    <div class="stat-card-footer">Perlu penanganan/reschedule</div>
  </div>

</div>

<!-- ══ DATA TABLE CARD ══ -->
<div class="card" style="padding:0;" x-data="distribusiTable()">
  
  <!-- Filter Row -->
  <div class="filter-row" style="display:flex;gap:1rem;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border-subtle);flex-wrap:wrap;">
    <div class="input-group search-input" style="position:relative;flex:1;min-width:240px;">
      <span class="input-group-icon" style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted);"><i data-lucide="search" style="width:18px;height:18px;"></i></span>
      <input
        type="text"
        class="form-control"
        placeholder="Cari surat jalan, sekolah, kurir..."
        x-model="search"
        @input="filterTable"
        style="padding-left:2.5rem;width:100%;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding-top:0.5rem;padding-bottom:0.5rem;"
      >
    </div>

    <select class="form-select" x-model="filterStatus" @change="filterTable" style="min-width:150px;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding:0.5rem;">
      <option value="">Semua Status</option>
      <option value="jadwal">Jadwal</option>
      <option value="dikirim">Sedang Dikirim</option>
      <option value="selesai">Selesai</option>
      <option value="bermasalah">Bermasalah</option>
    </select>

    <div style="flex:1;"></div>

    <span class="text-secondary text-sm" style="font-size:0.875rem;color:var(--text-secondary);">
      Menampilkan <strong x-text="filteredCount" style="color:var(--text-primary);"></strong> dari <?= count($distribusiList) ?> log
    </span>
  </div>

  <!-- Table wrapper -->
  <div class="table-wrapper" style="overflow-x:auto;">
    <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;">
      <thead>
        <tr style="border-bottom:1px solid var(--border-subtle);">
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">No Surat Jalan</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Batch Produksi</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Sekolah Penerima</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Total Porsi</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Kurir / Pengantar</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Jadwal / Waktu</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Status</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;width:80px;">Detail</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($distribusiList as $item): ?>
        <?php
          $badge = match($item['status']) {
            'jadwal' => 'badge-neutral',
            'dikirim' => 'badge-warning',
            'selesai' => 'badge-success',
            'bermasalah' => 'badge-danger',
            default => 'badge-neutral',
          };
          $statusLabel = match($item['status']) {
            'jadwal' => 'Jadwal',
            'dikirim' => 'Sedang Dikirim',
            'selesai' => 'Selesai',
            'bermasalah' => 'Ada Masalah',
            default => ucfirst($item['status']),
          };
        ?>
        <tr
          class="distribusi-row"
          data-surat="<?= esc(strtolower($item['no_surat_jalan'])) ?>"
          data-batch="<?= esc(strtolower($item['batch_no'])) ?>"
          data-sekolah="<?= esc(strtolower($item['nama_sekolah'])) ?>"
          data-kurir="<?= esc(strtolower($item['kurir'])) ?>"
          data-status="<?= esc($item['status']) ?>"
          style="border-bottom:1px solid var(--border-subtle);transition:background-color 0.2s;"
          onmouseover="this.style.backgroundColor='var(--bg-card-hover)'"
          onmouseout="this.style.backgroundColor='transparent'"
        >
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <span style="font-family:monospace;font-weight:600;color:var(--emerald);">
              <?= esc($item['no_surat_jalan']) ?>
            </span>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-primary);">
            <i data-lucide="layers" style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-right:4px;color:var(--text-muted);"></i>
            <?= esc($item['batch_no']) ?>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <strong style="color:var(--text-primary);"><?= esc($item['nama_sekolah']) ?></strong>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;font-weight:600;color:var(--text-primary);">
            <?= number_format($item['porsi']) ?> porsi
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-primary);">
            <div><?= esc($item['kurir']) ?></div>
            <div style="font-size:0.75rem;color:var(--text-muted);"><?= esc($item['kendaraan']) ?></div>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-secondary);">
            <div><?= esc($item['tanggal']) ?></div>
            <div style="font-size:0.75rem;color:var(--text-muted);">
              <?php if($item['waktu_berangkat']): ?>
                Departure: <?= $item['waktu_berangkat'] ?>
              <?php else: ?>
                Belum berangkat
              <?php endif; ?>
            </div>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <span class="badge <?= $badge ?>"><?= $statusLabel ?></span>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <a href="<?= base_url('/distribusi/show/' . $item['id']) ?>"
               class="btn btn-secondary btn-icon btn-sm" style="padding:4px;" title="Lihat Timeline & Log">
              <i data-lucide="eye" style="width:16px;height:16px;"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Empty State -->
  <div id="emptyState" style="display:none;flex-direction:column;align-items:center;justify-content:center;padding:3rem;text-align:center;">
    <div style="width:60px;height:60px;border-radius:50%;background:var(--border-subtle);display:flex;align-items:center;justify-content:center;color:var(--text-muted);margin-bottom:1rem;">
      <i data-lucide="truck" style="width:30px;height:30px;"></i>
    </div>
    <h3 style="color:var(--text-primary);font-size:1.1rem;margin-bottom:0.5rem;">Tidak Ada Pengiriman Ditemukan</h3>
    <p style="color:var(--text-muted);font-size:0.875rem;max-width:320px;margin-bottom:1.5rem;">Silakan coba sesuaikan pencarian atau filter status Anda.</p>
    <button class="btn btn-secondary btn-sm" onclick="resetFilters()">
      <i data-lucide="refresh-cw"></i> Reset Filter
    </button>
  </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function distribusiTable() {
    return {
      search: '',
      filterStatus: '',
      filteredCount: <?= count($distribusiList) ?>,

      filterTable() {
        const rows = document.querySelectorAll('.distribusi-row');
        let count = 0;
        const s = this.search.toLowerCase().trim();
        const st = this.filterStatus;

        rows.forEach(row => {
          const surat   = row.dataset.surat   || '';
          const batch   = row.dataset.batch   || '';
          const sekolah = row.dataset.sekolah || '';
          const kurir   = row.dataset.kurir   || '';
          const status  = row.dataset.status  || '';

          const matchSearch = !s || surat.includes(s) || batch.includes(s) || sekolah.includes(s) || kurir.includes(s);
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

  function resetFilters() {
    const tableEl = document.querySelector('[x-data="distribusiTable()"]');
    if (tableEl && tableEl.__x) {
      tableEl.__x.$data.search = '';
      tableEl.__x.$data.filterStatus = '';
      tableEl.__x.$data.filterTable();
    }
  }
</script>
<?= $this->endSection() ?>
