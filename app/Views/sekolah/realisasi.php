<?php $this->extend('layouts/app'); ?>
<?php $this->section('content'); ?>

<div class="page-header">
  <div class="page-header-left">
    <a href="<?= base_url('/sekolah') ?>" class="btn btn-outline btn-sm" id="btnBackSekolahRealisasi">
      <i data-lucide="arrow-left"></i> Kembali ke Daftar
    </a>
    <h1 class="page-title mt-2">
      <i data-lucide="bar-chart-2" style="color:var(--color-primary);"></i>
      Realisasi – <?= esc($sekolah['nama']) ?>
    </h1>
    <p class="page-subtitle"><?= esc($sekolah['jenjang'] ?? '') ?> | <?= esc($sekolah['kota'] ?? '') ?></p>
  </div>
</div>

<!-- KPI Cards -->
<div class="stats-grid stats-grid-4 mb-4">
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(var(--hsl-primary)/.15);">
      <i data-lucide="package-check" style="color:var(--color-primary);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($totalDiterima) ?></div>
      <div class="stat-label">Total Porsi Diterima</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(140 60% 50%/.15);">
      <i data-lucide="truck" style="color:hsl(140 60% 50%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $totalPengiriman ?></div>
      <div class="stat-label">Total Pengiriman</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(45 90% 55%/.15);">
      <i data-lucide="star" style="color:hsl(45 90% 55%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $avgRating > 0 ? $avgRating . '/5' : '-' ?></div>
      <div class="stat-label">Rating Rata-rata</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(0 70% 55%/.15);">
      <i data-lucide="alert-triangle" style="color:hsl(0 70% 55%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $bermasalah ?></div>
      <div class="stat-label">Pengiriman Bermasalah</div>
    </div>
  </div>
</div>

<!-- Chart: Target vs Realisasi -->
<?php
  $bulanLabels = [];
  $realisasiData = [];
  $targetData = [];

  $distribusiMap = [];
  foreach ($distribusiData as $d) {
    $distribusiMap[$d['bulan']] = (int) $d['total_diterima'];
  }

  $feedbackMap = [];
  foreach ($feedbackData as $f) {
    $feedbackMap[$f['bulan']] = round((float) $f['avg_rating'], 1);
  }

  for ($i = 5; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("-{$i} months"));
    $label = date('M Y', strtotime("-{$i} months"));
    $bulanLabels[] = $label;
    $realisasiData[] = $distribusiMap[$key] ?? 0;
    $targetData[] = $targetPerBulan;
  }
?>

<div class="grid grid-2 mb-4">
  <!-- Target vs Realisasi Chart -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Target vs Realisasi Porsi (6 Bulan)</h3>
    </div>
    <div class="card-body">
      <canvas id="chartTargetRealisasi" height="200"></canvas>
    </div>
  </div>

  <!-- Rating Trend Chart -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Tren Rating Kepuasan</h3>
    </div>
    <div class="card-body">
      <canvas id="chartRating" height="200"></canvas>
    </div>
  </div>
</div>

<!-- Detail Table -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Rincian Distribusi per Bulan</h3>
  </div>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>Bulan</th>
          <th class="text-right">Target Porsi</th>
          <th class="text-right">Realisasi Porsi</th>
          <th class="text-right">Pencapaian</th>
          <th class="text-right">Rating Rata-rata</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($distribusiData)): ?>
        <tr><td colspan="5" class="text-center" style="padding:2rem;opacity:.6;">Belum ada data distribusi.</td></tr>
        <?php else: ?>
        <?php foreach ($distribusiData as $row): ?>
        <?php
          $pct = $targetPerBulan > 0 ? round($row['total_diterima'] / $targetPerBulan * 100) : 0;
          $pctClass = $pct >= 90 ? 'success' : ($pct >= 70 ? 'warning' : 'danger');
          $rating = $feedbackMap[$row['bulan']] ?? null;
        ?>
        <tr>
          <td><?= date('F Y', strtotime($row['bulan'] . '-01')) ?></td>
          <td class="text-right"><?= number_format($targetPerBulan) ?></td>
          <td class="text-right"><?= number_format($row['total_diterima']) ?></td>
          <td class="text-right">
            <span class="badge badge-<?= $pctClass ?>"><?= $pct ?>%</span>
          </td>
          <td class="text-right">
            <?php if ($rating !== null): ?>
            <span style="color:hsl(45 90% 45%);">
              <?php for ($s = 1; $s <= 5; $s++): ?>
                <?= $s <= round($rating) ? '★' : '☆' ?>
              <?php endfor; ?>
            </span>
            <?= $rating ?>
            <?php else: ?>
            <span style="opacity:.4;">N/A</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php $this->section('scripts'); ?>
<script>
  // Chart: Target vs Realisasi
  new Chart(document.getElementById('chartTargetRealisasi'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($bulanLabels) ?>,
      datasets: [
        {
          label: 'Target Porsi',
          data: <?= json_encode($targetData) ?>,
          backgroundColor: 'rgba(13, 148, 136, 0.2)',
          borderColor: 'rgba(13, 148, 136, 1)',
          borderWidth: 2,
          borderRadius: 4,
        },
        {
          label: 'Realisasi Porsi',
          data: <?= json_encode($realisasiData) ?>,
          backgroundColor: 'rgba(16, 185, 129, 0.7)',
          borderColor: 'rgba(16, 185, 129, 1)',
          borderWidth: 2,
          borderRadius: 4,
        }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'top' } },
      scales: {
        y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('id') } }
      }
    }
  });

  // Chart: Rating Trend
  const ratingLabels = <?= json_encode($bulanLabels) ?>;
  const ratingVals   = <?= json_encode(array_map(fn($l) => $feedbackMap[date('Y-m', strtotime($l))] ?? null, $bulanLabels)) ?>;

  new Chart(document.getElementById('chartRating'), {
    type: 'line',
    data: {
      labels: ratingLabels,
      datasets: [{
        label: 'Rating Rata-rata',
        data: ratingVals,
        borderColor: 'hsl(45, 90%, 55%)',
        backgroundColor: 'hsl(45, 90%, 55%, .15)',
        borderWidth: 2.5,
        pointRadius: 5,
        tension: 0.4,
        fill: true,
        spanGaps: true,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'top' } },
      scales: {
        y: { min: 0, max: 5, ticks: { stepSize: 1 } }
      }
    }
  });
</script>
<?php $this->endSection(); ?>

<?php $this->endSection(); ?>
