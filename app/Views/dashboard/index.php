<?= $this->extend('layouts/app') ?>

<?= $this->section('styles') ?>
<style>
  .greeting-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.3rem 0.75rem;
    background: var(--emerald-dim);
    border: 1px solid hsla(38, 92%, 50%, 0.25);
    border-radius: var(--border-radius-full);
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--emerald);
    margin-bottom: 0.5rem;
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$session    = session();
$userName   = $session->get('user_name') ?? 'Pengguna';
$firstName  = explode(' ', $userName)[0];
$hour       = (int) date('H');
$greeting   = $hour < 11 ? '🌅 Selamat Pagi' : ($hour < 15 ? '☀️ Selamat Siang' : ($hour < 18 ? '🌤️ Selamat Sore' : '🌙 Selamat Malam'));

$role       = $session->get('user_role') ?? 'viewer';

// Data from controller
$totalProduksi  = $totalPorsiHariIni ?? 0;
$sekolahCount   = $sekolahTerlayani ?? 0;
$stokKritis     = $totalStokKritis ?? 0;
$poPending      = $poPending ?? 0;
$distribusiList = $listDistribusiHariIni ?? [];
$recentPO       = $recentPO ?? [];
$batchList      = $batchHariIni ?? [];

// Layout visibility rules based on role
$showTotalProduksi = in_array($role, ['admin', 'superadmin', 'produksi']);
$showSekolahTerlayani = in_array($role, ['admin', 'superadmin', 'distribusi']);
$showStokKritis = in_array($role, ['admin', 'superadmin', 'gudang', 'produksi']);
$showPoPending = in_array($role, ['admin', 'superadmin', 'pembelian', 'gudang']);

$showChart = in_array($role, ['admin', 'superadmin', 'produksi', 'gudang']);
$showDist = in_array($role, ['admin', 'superadmin', 'distribusi']);
$showPO = in_array($role, ['admin', 'superadmin', 'pembelian', 'gudang']);
$showBatch = in_array($role, ['admin', 'superadmin', 'produksi']);
?>

<!-- ══════════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════════ -->
<div class="page-header">
  <div class="page-header-left">
    <div class="greeting-badge">
      <i data-lucide="leaf" style="width:13px;height:13px;"></i>
      <?= $greeting ?>, <?= esc($firstName) ?>!
    </div>
    <h1 class="page-title">Dashboard SPPG</h1>
    <p class="page-subtitle">
      <i data-lucide="calendar" style="width:13px;height:13px;display:inline;vertical-align:middle;"></i>
      <?= date('l, d F Y') ?> · Ringkasan operasional dapur hari ini
    </p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/laporan') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="download"></i>
      Unduh Laporan
    </a>
    <a href="<?= base_url('/produksi/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="plus"></i>
      Input Produksi
    </a>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     STAT CARDS
══════════════════════════════════════════════════════════════ -->
<div class="stats-grid">

  <!-- Total Produksi -->
  <?php if ($showTotalProduksi): ?>
  <div class="stat-card accent-success">
    <div class="stat-card-header">
      <span class="stat-card-label">Total Produksi Hari Ini</span>
      <div class="stat-card-icon">
        <i data-lucide="chef-hat"></i>
      </div>
    </div>
    <div class="stat-card-value"><?= number_format($totalProduksi) ?></div>
    <div class="stat-card-footer">
      <span class="stat-trend up">
        <i data-lucide="trending-up"></i>
        +8.2%
      </span>
      porsi dari kemarin
    </div>
  </div>
  <?php endif; ?>

  <!-- Sekolah Terlayani -->
  <?php if ($showSekolahTerlayani): ?>
  <div class="stat-card accent-info">
    <div class="stat-card-header">
      <span class="stat-card-label">Sekolah Terlayani</span>
      <div class="stat-card-icon">
        <i data-lucide="school"></i>
      </div>
    </div>
    <div class="stat-card-value"><?= $sekolahCount ?></div>
    <div class="stat-card-footer">
      <span class="stat-trend up">
        <i data-lucide="check-circle"></i>
        Semua terjadwal
      </span>
    </div>
  </div>
  <?php endif; ?>

  <!-- Stok Kritis -->
  <?php if ($showStokKritis): ?>
  <div class="stat-card <?= $stokKritis > 0 ? 'accent-danger' : 'accent-success' ?>">
    <div class="stat-card-header">
      <span class="stat-card-label">Stok Kritis</span>
      <div class="stat-card-icon">
        <i data-lucide="alert-triangle"></i>
      </div>
    </div>
    <div class="stat-card-value"><?= $stokKritis ?></div>
    <div class="stat-card-footer">
      <?php if ($stokKritis > 0): ?>
        <span class="stat-trend down">
          <i data-lucide="alert-circle"></i>
          Perlu perhatian segera
        </span>
      <?php else: ?>
        <span class="stat-trend up">
          <i data-lucide="check-circle"></i>
          Semua stok aman
        </span>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- PO Pending -->
  <?php if ($showPoPending): ?>
  <div class="stat-card accent-warning">
    <div class="stat-card-header">
      <span class="stat-card-label">PO Pending</span>
      <div class="stat-card-icon">
        <i data-lucide="shopping-cart"></i>
      </div>
    </div>
    <div class="stat-card-value"><?= $poPending ?></div>
    <div class="stat-card-footer">
      <span class="stat-trend">
        <i data-lucide="clock"></i>
        Menunggu approval
      </span>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /.stats-grid -->

<!-- ══════════════════════════════════════════════════════════════
     MAIN CONTENT GRID (Chart + Distribution)
══════════════════════════════════════════════════════════════ -->
<?php if ($showChart || $showDist): ?>
<div class="<?= ($showChart && $showDist) ? 'content-grid' : '' ?>" style="margin-bottom:1.75rem; <?= (!$showChart || !$showDist) ? 'display:block;' : '' ?>">

  <!-- ── Left: Bar Chart Tren Produksi ── -->
  <?php if ($showChart): ?>
  <div class="card" style="margin-bottom: <?= ($showChart && !$showDist) ? '0' : '1.75rem' ?>;">
    <div class="card-header">
      <div>
        <div class="card-title">Tren Produksi 7 Hari Terakhir</div>
        <div class="card-subtitle">Jumlah porsi yang diproduksi per hari</div>
      </div>
      <div style="display:flex;gap:0.5rem;">
        <span class="badge badge-success">
          <i data-lucide="trending-up"></i>
          +8.2% minggu ini
        </span>
      </div>
    </div>
    <div class="chart-container" style="height:260px;">
      <canvas id="produksiChart"></canvas>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Right: Distribusi Hari Ini ── -->
  <?php if ($showDist): ?>
  <div class="card" style="margin-bottom: <?= (!$showChart && $showDist) ? '0' : '1.75rem' ?>;">
    <div class="card-header">
      <div>
        <div class="card-title">Distribusi Hari Ini</div>
        <div class="card-subtitle">Status pengiriman ke sekolah sasaran</div>
      </div>
      <a href="<?= base_url('/distribusi') ?>" class="btn btn-ghost btn-sm">
        Lihat Semua <i data-lucide="arrow-right"></i>
      </a>
    </div>

    <div class="dist-list">
      <?php if (!empty($distribusiList)): ?>
        <?php foreach ($distribusiList as $i => $dist): ?>
        <div class="dist-item">
          <div class="dist-item-rank"><?= $i + 1 ?></div>
          <div class="dist-item-info">
            <div class="dist-item-name"><?= esc($dist['nama_sekolah'] ?? 'Sekolah -') ?></div>
            <div class="dist-item-sub">
              <i data-lucide="map-pin" style="width:11px;height:11px;display:inline;"></i>
              <?= esc($dist['kelurahan'] ?? '-') ?>
            </div>
          </div>
          <div class="dist-item-right">
            <div class="dist-item-value"><?= number_format($dist['jumlah_porsi'] ?? 0) ?></div>
            <div class="dist-item-unit">porsi</div>
          </div>
          <div>
            <?php
              $status = $dist['status'] ?? 'pending';
              $badgeClass = match($status) {
                'terkirim'   => 'badge-success',
                'dalam_kirim'=> 'badge-info',
                'pending'    => 'badge-warning',
                'gagal'      => 'badge-danger',
                default      => 'badge-neutral',
              };
              $statusLabel = match($status) {
                'terkirim'   => 'Terkirim',
                'dalam_kirim'=> 'Dikirim',
                'pending'    => 'Pending',
                'gagal'      => 'Gagal',
                default      => ucfirst($status),
              };
            ?>
            <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Demo data if no data from controller -->
        <?php
        $demoSekolah = [
          ['nama' => 'SDN Surabaya 01', 'kel' => 'Wonokromo', 'porsi' => 280, 'status' => 'terkirim'],
          ['nama' => 'SDN Ketintang 02', 'kel' => 'Ketintang', 'porsi' => 240, 'status' => 'terkirim'],
          ['nama' => 'SMPN 5 Surabaya', 'kel' => 'Dukuh Pakis', 'porsi' => 350, 'status' => 'dalam_kirim'],
          ['nama' => 'SDN Benowo 03', 'kel' => 'Benowo', 'porsi' => 200, 'status' => 'pending'],
          ['nama' => 'MI Al Falah', 'kel' => 'Simokerto', 'porsi' => 180, 'status' => 'terkirim'],
        ];
        foreach ($demoSekolah as $i => $s):
          $badgeClass = match($s['status']) {
            'terkirim'    => 'badge-success',
            'dalam_kirim' => 'badge-info',
            'pending'     => 'badge-warning',
            default       => 'badge-neutral',
          };
          $statusLabel = match($s['status']) {
            'terkirim'    => 'Terkirim',
            'dalam_kirim' => 'Dikirim',
            'pending'     => 'Pending',
            default       => $s['status'],
          };
        ?>
        <div class="dist-item">
          <div class="dist-item-rank"><?= $i + 1 ?></div>
          <div class="dist-item-info">
            <div class="dist-item-name"><?= $s['nama'] ?></div>
            <div class="dist-item-sub">
              <i data-lucide="map-pin" style="width:11px;height:11px;display:inline;"></i>
              <?= $s['kel'] ?>
            </div>
          </div>
          <div class="dist-item-right">
            <div class="dist-item-value"><?= number_format($s['porsi']) ?></div>
            <div class="dist-item-unit">porsi</div>
          </div>
          <div>
            <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div><!-- /.dist-list -->

  </div><!-- /.card (distribusi) -->
  <?php endif; ?>

</div><!-- /.content-grid -->
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     BOTTOM ROW (Recent PO + Batch Produksi)
══════════════════════════════════════════════════════════════ -->
<?php if ($showPO || $showBatch): ?>
<div class="<?= ($showPO && $showBatch) ? 'content-grid' : '' ?>" style="margin-bottom:0; <?= (!$showPO || !$showBatch) ? 'display:block;' : '' ?>">

  <!-- ── Recent PO Table ── -->
  <?php if ($showPO): ?>
  <div class="card" style="padding:0; margin-bottom: <?= ($showPO && !$showBatch) ? '0' : '1.75rem' ?>;">
    <div class="card-header" style="padding:1.25rem 1.5rem;">
      <div>
        <div class="card-title">Purchase Order Terbaru</div>
        <div class="card-subtitle">5 PO terakhir yang masuk</div>
      </div>
      <a href="<?= base_url('/pembelian') ?>" class="btn btn-ghost btn-sm">
        Semua PO <i data-lucide="arrow-right"></i>
      </a>
    </div>
    <div class="table-wrapper" style="border:none;border-radius:0 0 16px 16px;">
      <table class="data-table">
        <thead>
          <tr>
            <th>No. PO</th>
            <th>Supplier</th>
            <th>Tgl. PO</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($recentPO)): ?>
            <?php foreach ($recentPO as $po): ?>
            <tr>
              <td><strong><?= esc($po['no_po'] ?? '-') ?></strong></td>
              <td><?= esc($po['supplier'] ?? '-') ?></td>
              <td><?= date('d/m/Y', strtotime($po['tgl_po'] ?? 'now')) ?></td>
              <td><strong>Rp <?= number_format($po['total'] ?? 0) ?></strong></td>
              <td>
                <?php
                  $s = $po['status'] ?? 'pending';
                  $bc = match($s) {
                    'disetujui', 'approved' => 'badge-success',
                    'diajukan', 'pending'   => 'badge-warning',
                    'ditolak', 'rejected'   => 'badge-danger',
                    default                 => 'badge-neutral',
                  };
                ?>
                <span class="badge <?= $bc ?>"><?= ucfirst($s) ?></span>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <?php
            $demoPO = [
              ['no_po'=>'PO-2024-001','supplier'=>'CV Agro Segar',   'tgl'=>'2024-01-15','total'=>4500000,'status'=>'approved'],
              ['no_po'=>'PO-2024-002','supplier'=>'PT Protein Prima', 'tgl'=>'2024-01-15','total'=>7200000,'status'=>'approved'],
              ['no_po'=>'PO-2024-003','supplier'=>'UD Bumbu Nusantara','tgl'=>'2024-01-16','total'=>1800000,'status'=>'pending'],
              ['no_po'=>'PO-2024-004','supplier'=>'CV Minyak Murni',  'tgl'=>'2024-01-16','total'=>3600000,'status'=>'pending'],
              ['no_po'=>'PO-2024-005','supplier'=>'PT Beras Cianjur', 'tgl'=>'2024-01-17','total'=>9100000,'status'=>'approved'],
            ];
            foreach ($demoPO as $po):
              $bc = match($po['status']) {
                'approved' => 'badge-success', 'pending' => 'badge-warning',
                'rejected' => 'badge-danger',  default   => 'badge-neutral',
              };
            ?>
            <tr>
              <td><strong><?= $po['no_po'] ?></strong></td>
              <td><?= $po['supplier'] ?></td>
              <td><?= date('d/m/Y', strtotime($po['tgl'])) ?></td>
              <td><strong>Rp <?= number_format($po['total']) ?></strong></td>
              <td><span class="badge <?= $bc ?>"><?= ucfirst($po['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div><!-- /.card recent PO -->
  <?php endif; ?>

  <!-- ── Batch Produksi Cards ── -->
  <?php if ($showBatch): ?>
  <div class="card" style="margin-bottom: <?= (!$showPO && $showBatch) ? '0' : '1.75rem' ?>;">
    <div class="card-header">
      <div>
        <div class="card-title">Batch Produksi Aktif</div>
        <div class="batch-card-subtitle" style="font-size:0.8rem;color:var(--text-muted);">Sedang dan baru selesai dimasak</div>
      </div>
      <a href="<?= base_url('/produksi') ?>" class="btn btn-ghost btn-sm">
        Semua <i data-lucide="arrow-right"></i>
      </a>
    </div>

    <div style="display:flex;flex-direction:column;gap:0.75rem;">
      <?php
      $demoBatch = [
        ['id'=>'BCH-001','menu'=>'Nasi Ayam Semur + Sayur Bayam','porsi'=>840,'status'=>'selesai','pct'=>100],
        ['id'=>'BCH-002','menu'=>'Nasi Ikan Goreng + Tumis Kangkung','porsi'=>720,'status'=>'proses','pct'=>65],
        ['id'=>'BCH-003','menu'=>'Nasi Telur Balado + Sup Sayur','porsi'=>560,'status'=>'persiapan','pct'=>20],
      ];
      $batchItems = !empty($batchList) ? $batchList : $demoBatch;
      foreach ($batchItems as $batch):
        $status = $batch['status'] ?? 'proses';
        $pct    = $batch['pct'] ?? $batch['persentase'] ?? 50;
        $badgeClass  = match($status) {
          'selesai'    => 'badge-success',
          'proses'     => 'badge-info',
          'persiapan'  => 'badge-warning',
          default      => 'badge-neutral',
        };
        $progressClass = match($status) {
          'selesai' => '', 'proses' => 'info', 'persiapan' => 'warning', default => '',
        };
      ?>
      <div class="batch-card">
        <div class="batch-card-header">
          <div>
            <div class="batch-card-title"><?= esc($batch['menu'] ?? $batch['nama_menu'] ?? '-') ?></div>
            <div class="batch-card-id"><?= esc($batch['id'] ?? $batch['kode_batch'] ?? '-') ?></div>
          </div>
          <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
        </div>

        <!-- Progress Bar -->
        <div>
          <div style="display:flex;justify-content:space-between;margin-bottom:0.375rem;">
            <span style="font-size:0.72rem;color:var(--text-muted);">Progress Masak</span>
            <span style="font-size:0.72rem;font-weight:700;color:var(--text-primary);"><?= $pct ?>%</span>
          </div>
          <div class="progress-bar">
            <div class="progress-fill <?= $progressClass ?>" style="width:<?= $pct ?>%;"></div>
          </div>
        </div>

        <div class="batch-card-stats">
          <div class="batch-stat">
            <span class="batch-stat-value"><?= number_format($batch['porsi'] ?? $batch['jumlah_porsi'] ?? 0) ?></span>
            <span class="batch-stat-label">Porsi</span>
          </div>
          <div class="batch-stat">
            <span class="batch-stat-value" style="font-size:0.9rem;"><?= date('H:i') ?></span>
            <span class="batch-stat-label">Waktu Update</span>
          </div>
        </div>

      </div><!-- /.batch-card -->
      <?php endforeach; ?>
    </div>

  <?php endif; ?>

</div><!-- /.content-grid -->
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {

  // ── Produksi Chart ──────────────────────────────────────────
  const chartEl = document.getElementById('produksiChart');
  if (!chartEl) return;

  // Data from PHP
  const labelsRaw = <?= $trenLabels ?? '[]' ?>;
  const valuesRaw = <?= $trenData ?? '[]' ?>;

  // Gunakan data demo jika semua nilai 0 (database kosong)
  const isDemoMode = valuesRaw.length === 0 || valuesRaw.every(v => v === 0);
  const demoLabels = ['15 Jun', '16 Jun', '17 Jun', '18 Jun', '19 Jun', '20 Jun', '21 Jun'];
  const demoValues = [3800, 4250, 3950, 4600, 4200, 3700, 4450];
  const labels = isDemoMode ? demoLabels : labelsRaw;
  const values = isDemoMode ? demoValues : valuesRaw;

  Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
  Chart.defaults.color = 'hsl(215, 16%, 60%)';

  new Chart(chartEl, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Porsi Diproduksi',
        data: values,
        backgroundColor: (ctx) => {
          const max = Math.max(...values);
          return ctx.raw === max
            ? 'hsl(38, 92%, 50%)'
            : 'hsla(38, 92%, 50%, 0.25)';
        },
        borderColor: 'hsl(38, 92%, 50%)',
        borderWidth: 1,
        borderRadius: 8,
        borderSkipped: false,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'hsl(218, 30%, 13%)',
          borderColor: 'hsl(215, 28%, 22%)',
          borderWidth: 1,
          titleColor: 'hsl(210, 40%, 98%)',
          bodyColor: 'hsl(215, 16%, 60%)',
          cornerRadius: 8,
          padding: 12,
          callbacks: {
            label: (ctx) => ` ${ctx.raw.toLocaleString('id-ID')} porsi`,
          }
        },
      },
      scales: {
        x: {
          grid: { color: 'hsla(215, 28%, 18%, 0.5)', drawBorder: false },
          ticks: { font: { size: 12, weight: '600' } },
          border: { display: false },
        },
        y: {
          grid: { color: 'hsla(215, 28%, 18%, 0.5)', drawBorder: false },
          ticks: {
            font: { size: 11 },
            callback: (v) => (v / 1000).toFixed(1) + 'k',
          },
          border: { display: false },
          beginAtZero: true,
        },
      },
      animation: {
        duration: 900,
        easing: 'easeOutQuart',
      },
    },
  });

});
</script>
<?= $this->endSection() ?>
