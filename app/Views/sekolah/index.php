<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$session = session();
$role    = $session->get('user_role') ?? 'viewer';
$isAdmin = in_array($role, ['admin', 'superadmin']);

$sekolahList = $sekolahList ?? [];
if (empty($sekolahList)) {
  $sekolahList = [
    ['id' => 1, 'kode' => 'SCH-001', 'nama' => 'SDN Merdeka 01', 'jenjang' => 'SD', 'alamat' => 'Jl. Merdeka No. 10', 'kelurahan' => 'Babakan', 'kecamatan' => 'Bogor Tengah', 'kota' => 'Bogor', 'kepala_sekolah' => 'Drs. H. Ahmad Sunarya', 'no_telp' => '081234567890', 'jumlah_siswa' => 450, 'status' => 'aktif'],
    ['id' => 2, 'kode' => 'SCH-002', 'nama' => 'SMP Negeri 1 Bogor', 'jenjang' => 'SMP', 'alamat' => 'Jl. Cikuray No. 5', 'kelurahan' => 'Babakan Pasar', 'kecamatan' => 'Bogor Tengah', 'kota' => 'Bogor', 'kepala_sekolah' => 'Hj. Endang Wahyuni, M.Pd.', 'no_telp' => '081398765432', 'jumlah_siswa' => 820, 'status' => 'aktif'],
    ['id' => 3, 'kode' => 'SCH-003', 'nama' => 'SMA Negeri 2 Bogor', 'jenjang' => 'SMA', 'alamat' => 'Jl. Keranji Ujung No. 1', 'kelurahan' => 'Budi Agung', 'kecamatan' => 'Tanah Sareal', 'kota' => 'Bogor', 'kepala_sekolah' => 'Dr. Cecep Ridwan', 'no_telp' => '081122334455', 'jumlah_siswa' => 960, 'status' => 'aktif'],
    ['id' => 4, 'kode' => 'SCH-004', 'nama' => 'SMK Negeri 1 Bogor', 'jenjang' => 'SMK', 'alamat' => 'Jl. Heulang No. 6', 'kelurahan' => 'Tanah Sareal', 'kecamatan' => 'Tanah Sareal', 'kota' => 'Bogor', 'kepala_sekolah' => 'Drs. Uus Suryana', 'no_telp' => '085677889900', 'jumlah_siswa' => 1200, 'status' => 'aktif'],
    ['id' => 5, 'kode' => 'SCH-005', 'nama' => 'SD Al-Azhar Bogor', 'jenjang' => 'SD', 'alamat' => 'Jl. Pajajaran No. 23', 'kelurahan' => 'Baranangsiang', 'kecamatan' => 'Bogor Timur', 'kota' => 'Bogor', 'kepala_sekolah' => 'Siti Aminah, S.Pd.I.', 'no_telp' => '087899001122', 'jumlah_siswa' => 380, 'status' => 'nonaktif'],
  ];
}

$totalSiswa = array_sum(array_column($sekolahList, 'jumlah_siswa'));
$totalSekolah = count($sekolahList);
$aktifSekolah = count(array_filter($sekolahList, fn($s) => $s['status'] === 'aktif'));
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Manajemen Sekolah</h1>
    <p class="page-subtitle">Daftar sekolah penerima manfaat Makan Bergizi Gratis (MBG)</p>
  </div>
  <div class="page-header-actions">
    <?php if ($isAdmin): ?>
    <a href="<?= base_url('/sekolah/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="plus"></i>
      Tambah Sekolah
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ══ SUMMARY STAT CARDS ══ -->
<div class="stats-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;margin-bottom:1.5rem;">
  
  <div class="stat-card accent-success">
    <div class="stat-card-header">
      <span class="stat-card-label">Total Sekolah</span>
      <div class="stat-card-icon"><i data-lucide="school"></i></div>
    </div>
    <div class="stat-card-value"><?= number_format($totalSekolah) ?></div>
    <div class="stat-card-footer">
      <i data-lucide="map-pin" style="width:13px;height:13px;"></i>
      sekolah penerima terdaftar
    </div>
  </div>

  <div class="stat-card accent-info">
    <div class="stat-card-header">
      <span class="stat-card-label">Total Siswa (Porsi)</span>
      <div class="stat-card-icon"><i data-lucide="users"></i></div>
    </div>
    <div class="stat-card-value"><?= number_format($totalSiswa) ?></div>
    <div class="stat-card-footer">
      <i data-lucide="utensils" style="width:13px;height:13px;"></i>
      porsi disiapkan per siklus
    </div>
  </div>

  <div class="stat-card accent-success">
    <div class="stat-card-header">
      <span class="stat-card-label">Sekolah Aktif</span>
      <div class="stat-card-icon"><i data-lucide="check-circle"></i></div>
    </div>
    <div class="stat-card-value"><?= $aktifSekolah ?></div>
    <div class="stat-card-footer">
      <span style="color:var(--status-success);"><i data-lucide="activity" style="width:13px;height:13px;display:inline;vertical-align:middle;"></i> <?= number_format($totalSekolah > 0 ? ($aktifSekolah/$totalSekolah)*100 : 0, 1) ?>% aktif menerima distribusi</span>
    </div>
  </div>

</div>

<!-- ══ DATA TABLE CARD ══ -->
<div class="card" style="padding:0;" x-data="sekolahTable()">
  
  <!-- ── Filter Row ── -->
  <div class="filter-row" style="display:flex;gap:1rem;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border-subtle);flex-wrap:wrap;">
    <div class="input-group search-input" style="position:relative;flex:1;min-width:240px;">
      <span class="input-group-icon" style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted);"><i data-lucide="search" style="width:18px;height:18px;"></i></span>
      <input
        type="text"
        class="form-control"
        placeholder="Cari kode, nama, alamat, kelurahan..."
        x-model="search"
        @input="filterTable"
        style="padding-left:2.5rem;width:100%;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding-top:0.5rem;padding-bottom:0.5rem;"
      >
    </div>

    <select class="form-select" x-model="filterJenjang" @change="filterTable" style="min-width:140px;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding:0.5rem;">
      <option value="">Semua Jenjang</option>
      <option value="SD">SD</option>
      <option value="SMP">SMP</option>
      <option value="SMA">SMA</option>
      <option value="SMK">SMK</option>
    </select>

    <select class="form-select" x-model="filterStatus" @change="filterTable" style="min-width:140px;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);padding:0.5rem;">
      <option value="">Semua Status</option>
      <option value="aktif">Aktif</option>
      <option value="nonaktif">Non-Aktif</option>
    </select>

    <div class="filter-row-spacer" style="flex:1;"></div>

    <span class="text-secondary text-sm" style="font-size:0.875rem;color:var(--text-secondary);">
      Menampilkan <strong x-text="filteredCount" style="color:var(--text-primary);"></strong> dari <?= count($sekolahList) ?> sekolah
    </span>
  </div>

  <!-- ── Table ── -->
  <div class="table-wrapper" style="overflow-x:auto;">
    <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;">
      <thead>
        <tr style="border-bottom:1px solid var(--border-subtle);">
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">No</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Kode</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Nama Sekolah</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Jenjang</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Alamat</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Jml Siswa</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Kepala Sekolah</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;">Status</th>
          <th style="padding:1rem 1.5rem;color:var(--text-muted);font-weight:600;font-size:0.8rem;text-transform:uppercase;width:160px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sekolahList as $i => $item): ?>
        <?php
          $statusClass = $item['status'] === 'aktif' ? 'badge-success' : 'badge-danger';
          $jenjangClass = match($item['jenjang']) {
            'SD' => 'badge-info',
            'SMP' => 'badge-warning',
            'SMA' => 'badge-success',
            'SMK' => 'badge-neutral',
            default => 'badge-neutral',
          };
        ?>
        <tr
          class="sekolah-row"
          data-nama="<?= esc(strtolower($item['nama'])) ?>"
          data-kode="<?= esc(strtolower($item['kode'])) ?>"
          data-jenjang="<?= esc($item['jenjang']) ?>"
          data-status="<?= esc($item['status']) ?>"
          data-alamat="<?= esc(strtolower($item['alamat'] . ' ' . $item['kelurahan'] . ' ' . $item['kecamatan'])) ?>"
          style="border-bottom:1px solid var(--border-subtle);transition:background-color 0.2s;"
          onmouseover="this.style.backgroundColor='var(--bg-card-hover)'"
          onmouseout="this.style.backgroundColor='transparent'"
        >
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-secondary);"><?= $i + 1 ?></td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <span style="font-family:monospace;font-weight:600;background:var(--border-subtle);padding:0.2rem 0.5rem;border-radius:4px;color:var(--text-primary);">
              <?= esc($item['kode']) ?>
            </span>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <strong style="color:var(--text-primary);"><?= esc($item['nama']) ?></strong>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <span class="badge <?= $jenjangClass ?>"><?= esc($item['jenjang']) ?></span>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-secondary);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
            <?= esc($item['alamat']) ?>, <?= esc($item['kelurahan']) ?>, <?= esc($item['kecamatan']) ?>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;font-weight:600;color:var(--text-primary);"><?= number_format($item['jumlah_siswa']) ?></td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;color:var(--text-secondary);"><?= esc($item['kepala_sekolah'] ?? '-') ?></td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <span class="badge <?= $statusClass ?>"><?= ucfirst(esc($item['status'])) ?></span>
          </td>
          <td style="padding:1rem 1.5rem;font-size:0.875rem;">
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
              <!-- Tombol Realisasi -->
              <a href="<?= base_url('/sekolah/realisasi/' . $item['id']) ?>"
                 class="btn btn-primary btn-icon btn-sm" style="padding:4px 8px;font-size:.75rem;" title="Lihat Realisasi" id="btnRealisasi<?= $item['id'] ?>">
                <i data-lucide="bar-chart-2" style="width:14px;height:14px;"></i>
              </a>
              <a href="<?= base_url('/sekolah/edit/' . $item['id']) ?>"
                 class="btn btn-secondary btn-icon btn-sm" style="padding:4px;" title="Edit">
                <i data-lucide="pencil" style="width:16px;height:16px;"></i>
              </a>
              <?php if ($isAdmin): ?>
              <button
                onclick="confirmDelete('<?= base_url('/sekolah/delete/' . $item['id']) ?>', '<?= esc($item['nama']) ?>')"
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

  <!-- ── Empty State ── -->
  <div id="emptyState" style="display:none;flex-direction:column;align-items:center;justify-content:center;padding:3rem;text-align:center;">
    <div style="width:60px;height:60px;border-radius:50%;background:var(--border-subtle);display:flex;align-items:center;justify-content:center;color:var(--text-muted);margin-bottom:1rem;">
      <i data-lucide="search-code" style="width:30px;height:30px;"></i>
    </div>
    <h3 style="color:var(--text-primary);font-size:1.1rem;margin-bottom:0.5rem;">Tidak Ada Sekolah Ditemukan</h3>
    <p style="color:var(--text-muted);font-size:0.875rem;max-width:320px;margin-bottom:1.5rem;">Coba ubah filter atau kata kunci pencarian Anda.</p>
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
        Apakah Anda yakin ingin menghapus sekolah <strong id="deleteItemName" style="color:var(--text-primary);"></strong> dari database?
        Semua data distribusi yang berkaitan juga akan terpengaruh.
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
  function sekolahTable() {
    return {
      search: '',
      filterJenjang: '',
      filterStatus: '',
      filteredCount: <?= count($sekolahList) ?>,

      filterTable() {
        const rows = document.querySelectorAll('.sekolah-row');
        let count = 0;
        const s = this.search.toLowerCase().trim();
        const j = this.filterJenjang;
        const st = this.filterStatus;

        rows.forEach(row => {
          const nama    = row.dataset.nama    || '';
          const kode    = row.dataset.kode    || '';
          const jenjang = row.dataset.jenjang || '';
          const status  = row.dataset.status  || '';
          const alamat  = row.dataset.alamat  || '';

          const matchSearch = !s || nama.includes(s) || kode.includes(s) || alamat.includes(s);
          const matchJenjang = !j || jenjang === j;
          const matchStatus = !st || status === st;

          if (matchSearch && matchJenjang && matchStatus) {
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
    const tableEl = document.querySelector('[x-data="sekolahTable()"]');
    if (tableEl && tableEl.__x) {
      tableEl.__x.$data.search = '';
      tableEl.__x.$data.filterJenjang = '';
      tableEl.__x.$data.filterStatus = '';
      tableEl.__x.$data.filterTable();
    }
  }

  document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });
</script>
<?= $this->endSection() ?>
