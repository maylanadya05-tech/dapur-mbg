<?php $this->extend('layouts/app'); ?>
<?php $this->section('content'); ?>

<div class="page-header">
  <div class="page-header-left">
    <a href="<?= base_url('/jadwal') ?>" class="btn btn-outline btn-sm" id="btnBackEstimasi">
      <i data-lucide="arrow-left"></i> Kembali
    </a>
    <h1 class="page-title mt-2">
      <i data-lucide="package" style="color:var(--color-primary);"></i>
      Estimasi Bahan Baku – <?= esc($siklus['nama_siklus']) ?>
    </h1>
    <p class="page-subtitle">Kebutuhan bahan baku harian untuk <?= number_format($porsiPerHari) ?> porsi</p>
  </div>
</div>

<?php if (empty($estimasi)): ?>
<div class="card">
  <div class="card-body text-center" style="padding:3rem;">
    <i data-lucide="package" style="font-size:3rem;opacity:.4;display:block;margin-bottom:1rem;"></i>
    <p>Siklus ini belum memiliki detail menu/resep. Tambahkan menu ke siklus terlebih dahulu.</p>
    <a href="<?= base_url('/jadwal') ?>" class="btn btn-primary mt-3">Kelola Jadwal Siklus</a>
  </div>
</div>
<?php else: ?>

<!-- Aggregate bahan baku across all days -->
<?php
  $aggregateBahan = [];
  foreach ($estimasi as $hari) {
    foreach ($hari['hpp']['items'] as $item) {
      $id = $item['bahan_baku_id'];
      if (!isset($aggregateBahan[$id])) {
        $aggregateBahan[$id] = [
          'nama_bahan'   => $item['nama_bahan'],
          'satuan'       => $item['satuan'],
          'total_qty'    => 0,
          'total_biaya'  => 0,
          'hari_pakai'   => 0,
        ];
      }
      $aggregateBahan[$id]['total_qty']   += $item['total_qty'];
      $aggregateBahan[$id]['total_biaya'] += $item['subtotal'];
      $aggregateBahan[$id]['hari_pakai']++;
    }
  }
  arsort($aggregateBahan);
?>

<!-- Summary -->
<div class="stats-grid stats-grid-3 mb-4">
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(var(--hsl-primary)/.15);">
      <i data-lucide="calendar-days" style="color:var(--color-primary);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= count($estimasi) ?></div>
      <div class="stat-label">Hari dalam Siklus</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(140 60% 50%/.15);">
      <i data-lucide="layers" style="color:hsl(140 60% 50%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= count($aggregateBahan) ?></div>
      <div class="stat-label">Jenis Bahan Baku</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(45 90% 55%/.15);">
      <i data-lucide="coins" style="color:hsl(45 90% 55%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value" style="font-size:1rem;">
        Rp <?= number_format(array_sum(array_column($estimasi, 'hpp') ? array_map(fn($e) => $e['hpp']['total_hpp'], $estimasi) : []), 0, ',', '.') ?>
      </div>
      <div class="stat-label">Estimasi Total Biaya (Satu Siklus)</div>
    </div>
  </div>
</div>

<!-- Per Hari -->
<div class="card mb-4">
  <div class="card-header">
    <h3 class="card-title">Rincian Per Hari</h3>
  </div>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>Hari</th>
          <th>Menu</th>
          <th class="text-right">Porsi</th>
          <th class="text-right">HPP per Porsi</th>
          <th class="text-right">Total Biaya</th>
          <th>Jml Bahan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($estimasi as $e): ?>
        <tr>
          <td><span class="badge badge-secondary">Hari <?= $e['hari_ke'] ?></span></td>
          <td><strong><?= esc($e['nama_menu'] ?? '-') ?></strong></td>
          <td class="text-right"><?= number_format($e['porsi']) ?></td>
          <td class="text-right">Rp <?= number_format($e['hpp']['hpp_per_porsi'], 0, ',', '.') ?></td>
          <td class="text-right"><strong>Rp <?= number_format($e['hpp']['total_hpp'], 0, ',', '.') ?></strong></td>
          <td><?= count($e['hpp']['items']) ?> bahan</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Total Bahan Baku per Siklus -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Rekap Total Kebutuhan Bahan (Per Siklus)</h3>
    <span class="badge badge-info">Untuk <?= number_format($porsiPerHari) ?> porsi/hari</span>
  </div>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>Bahan Baku</th>
          <th class="text-right">Total Qty (Satu Siklus)</th>
          <th>Satuan</th>
          <th class="text-right">Estimasi Biaya</th>
          <th class="text-right">Dipakai (hari)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($aggregateBahan as $bahan): ?>
        <tr>
          <td><strong><?= esc($bahan['nama_bahan']) ?></strong></td>
          <td class="text-right"><?= number_format($bahan['total_qty'], 2) ?></td>
          <td><?= esc($bahan['satuan']) ?></td>
          <td class="text-right">Rp <?= number_format($bahan['total_biaya'], 0, ',', '.') ?></td>
          <td class="text-right"><?= $bahan['hari_pakai'] ?> hari</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php endif; ?>

<?php $this->endSection(); ?>
