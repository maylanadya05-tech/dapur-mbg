<?php $this->extend('layouts/app'); ?>
<?php $this->section('content'); ?>

<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">
      <i data-lucide="trending-up" style="color:var(--color-primary);"></i>
      Laporan Keuangan
    </h1>
    <p class="page-subtitle">Ringkasan pembelian, produksi, waste, dan invoice</p>
  </div>
</div>

<!-- Date Range Filter -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="<?= base_url('/laporan/keuangan') ?>" class="filter-form" style="align-items:flex-end;">
      <div class="form-group">
        <label class="form-label">Dari Tanggal</label>
        <input type="date" name="start_date" class="form-control" id="lkStartDate" value="<?= $startDate ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Sampai Tanggal</label>
        <input type="date" name="end_date" class="form-control" id="lkEndDate" value="<?= $endDate ?>">
      </div>
      <button type="submit" class="btn btn-primary" id="btnFilterKeuangan">
        <i data-lucide="filter"></i> Terapkan
      </button>
    </form>
  </div>
</div>

<!-- KPI Cards -->
<div class="stats-grid stats-grid-4 mb-4">
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(var(--hsl-primary)/.15);">
      <i data-lucide="shopping-cart" style="color:var(--color-primary);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value" style="font-size:1rem;">Rp <?= number_format($totalPembelian, 0, ',', '.') ?></div>
      <div class="stat-label">Total Pembelian (PO Disetujui)</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(140 60% 50%/.15);">
      <i data-lucide="file-check" style="color:hsl(140 60% 50%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value" style="font-size:1rem;">Rp <?= number_format($totalInvoice, 0, ',', '.') ?></div>
      <div class="stat-label">Invoice Terbayar</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(0 70% 55%/.15);">
      <i data-lucide="trash-2" style="color:hsl(0 70% 55%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value" style="font-size:1rem;">Rp <?= number_format($totalWaste, 0, ',', '.') ?></div>
      <div class="stat-label">Estimasi Nilai Food Waste</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(45 90% 55%/.15);">
      <i data-lucide="utensils" style="color:hsl(45 90% 55%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($totalPorsi) ?></div>
      <div class="stat-label">Total Porsi Diproduksi</div>
    </div>
  </div>
</div>

<!-- Charts -->
<div class="grid grid-2 mb-4">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Tren Pembelian (6 Bulan)</h3>
    </div>
    <div class="card-body">
      <canvas id="chartPembelian" height="220"></canvas>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Tren Food Waste (6 Bulan)</h3>
    </div>
    <div class="card-body">
      <canvas id="chartWaste" height="220"></canvas>
    </div>
  </div>
</div>

<!-- Top Supplier -->
<?php if (!empty($topSupplier)): ?>
<div class="card mb-4">
  <div class="card-header">
    <h3 class="card-title">Top 5 Supplier Berdasarkan Nilai Pembelian</h3>
    <span class="badge badge-info"><?= date('d/m/Y', strtotime($startDate)) ?> – <?= date('d/m/Y', strtotime($endDate)) ?></span>
  </div>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Nama Supplier</th>
          <th class="text-right">Jumlah PO</th>
          <th class="text-right">Total Nilai Pembelian</th>
          <th>Proporsi</th>
        </tr>
      </thead>
      <tbody>
        <?php $grandTotal = array_sum(array_column($topSupplier, 'total_nilai')); ?>
        <?php foreach ($topSupplier as $i => $s): ?>
        <?php $pct = $grandTotal > 0 ? round($s['total_nilai'] / $grandTotal * 100) : 0; ?>
        <tr>
          <td><strong><?= $i + 1 ?></strong></td>
          <td><?= esc($s['nama_supplier']) ?></td>
          <td class="text-right"><?= $s['total_po'] ?> PO</td>
          <td class="text-right"><strong>Rp <?= number_format($s['total_nilai'], 0, ',', '.') ?></strong></td>
          <td>
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="flex:1;height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                <div style="width:<?= $pct ?>%;height:100%;background:var(--color-primary);border-radius:4px;"></div>
              </div>
              <span style="font-size:.85rem;width:40px;"><?= $pct ?>%</span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php $this->section('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const pembelianLabels = <?= json_encode(array_column($trendPembelian, 'bulan')) ?>;
  const pembelianData   = <?= json_encode(array_map(fn($r) => (float)$r['total'], $trendPembelian)) ?>;

  const wasteLabels = <?= json_encode(array_column($trendWaste, 'bulan')) ?>;
  const wasteData   = <?= json_encode(array_map(fn($r) => (float)$r['total'], $trendWaste)) ?>;

  const formatRp = v => 'Rp ' + v.toLocaleString('id');

  new Chart(document.getElementById('chartPembelian'), {
    type: 'bar',
    data: {
      labels: pembelianLabels,
      datasets: [{
        label: 'Total Pembelian',
        data: pembelianData,
        backgroundColor: 'rgba(13, 148, 136, 0.7)',
        borderColor: '#0d9488',
        borderWidth: 2,
        borderRadius: 6,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => formatRp(ctx.parsed.y) } } },
      scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + (v/1e6).toFixed(1) + 'jt' } } }
    }
  });

  new Chart(document.getElementById('chartWaste'), {
    type: 'line',
    data: {
      labels: wasteLabels,
      datasets: [{
        label: 'Food Waste',
        data: wasteData,
        borderColor: 'hsl(0, 70%, 55%)',
        backgroundColor: 'hsl(0, 70%, 55%, .15)',
        borderWidth: 2.5,
        tension: 0.4,
        fill: true,
        pointRadius: 5,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => formatRp(ctx.parsed.y) } } },
      scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + (v/1e6).toFixed(1) + 'jt' } } }
    }
  });
});
</script>
<?php $this->endSection(); ?>

<?php $this->endSection(); ?>
