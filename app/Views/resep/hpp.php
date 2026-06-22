<?php $this->extend('layouts/app'); ?>
<?php $this->section('content'); ?>

<div class="page-header">
  <div class="page-header-left">
    <a href="<?= base_url('/resep/show/' . $resep['id']) ?>" class="btn btn-outline btn-sm" id="btnBackHpp">
      <i data-lucide="arrow-left"></i> Kembali
    </a>
    <h1 class="page-title mt-2">
      <i data-lucide="calculator" style="color:var(--color-primary);"></i>
      Kalkulasi HPP – <?= esc($resep['nama_menu']) ?>
    </h1>
    <p class="page-subtitle">Harga Pokok Produksi per porsi berdasarkan BOM (Bill of Materials)</p>
  </div>
</div>

<!-- Porsi Selector -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="<?= base_url('/resep/hpp/' . $resep['id']) ?>" class="filter-form" style="align-items:flex-end;">
      <div class="form-group">
        <label class="form-label">Hitung untuk berapa porsi?</label>
        <input type="number" name="porsi" class="form-control" id="inputPorsiHpp"
               value="<?= $porsi ?>" min="1" max="99999" style="width:150px;">
      </div>
      <button type="submit" class="btn btn-primary" id="btnHitungHpp" style="margin-bottom:1px;">
        <i data-lucide="refresh-cw"></i> Hitung Ulang
      </button>
    </form>
  </div>
</div>

<!-- HPP Result -->
<?php if ($hpp['has_missing_price']): ?>
<div class="alert alert-warning" style="margin-bottom:1rem;">
  <i data-lucide="alert-triangle"></i>
  <strong>Peringatan:</strong> Beberapa bahan baku belum memiliki harga satuan. HPP yang ditampilkan mungkin tidak akurat.
  Perbarui harga satuan di menu <a href="<?= base_url('/bahan-baku') ?>">Bahan Baku</a>.
</div>
<?php endif; ?>

<div class="grid grid-3 mb-4">
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(var(--hsl-primary)/.15);">
      <i data-lucide="utensils" style="color:var(--color-primary);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($porsi) ?></div>
      <div class="stat-label">Jumlah Porsi</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(140 60% 50%/.15);">
      <i data-lucide="coins" style="color:hsl(140 60% 50%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value" style="font-size:1.2rem;">Rp <?= number_format($hpp['hpp_per_porsi'], 0, ',', '.') ?></div>
      <div class="stat-label">HPP per Porsi</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(45 90% 55%/.15);">
      <i data-lucide="wallet" style="color:hsl(45 90% 55%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value" style="font-size:1.1rem;">Rp <?= number_format($hpp['total_hpp'], 0, ',', '.') ?></div>
      <div class="stat-label">Total HPP (<?= number_format($porsi) ?> porsi)</div>
    </div>
  </div>
</div>

<!-- Detail BOM -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Rincian Bahan Baku (BOM)</h3>
    <span class="badge badge-info"><?= count($hpp['items']) ?> bahan</span>
  </div>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>Bahan Baku</th>
          <th class="text-right">Qty/Porsi</th>
          <th>Satuan</th>
          <th class="text-right">Total Qty (<?= number_format($porsi) ?> porsi)</th>
          <th class="text-right">Harga Satuan</th>
          <th class="text-right">Subtotal</th>
          <th class="text-right">% dari Total</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($hpp['items'])): ?>
        <tr>
          <td colspan="7" class="text-center" style="padding:2rem;opacity:.6;">
            Resep ini belum memiliki detail bahan baku (BOM).
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($hpp['items'] as $item): ?>
        <?php
          $pct = $hpp['total_hpp'] > 0 ? round($item['subtotal'] / $hpp['total_hpp'] * 100, 1) : 0;
        ?>
        <tr>
          <td>
            <strong><?= esc($item['nama_bahan']) ?></strong>
            <?php if ($item['harga_satuan'] <= 0): ?>
            <span class="badge badge-warning" style="font-size:.7rem;margin-left:4px;">Harga Belum Diset</span>
            <?php endif; ?>
          </td>
          <td class="text-right"><?= number_format($item['qty_per_porsi'], 3) ?></td>
          <td><?= esc($item['satuan']) ?></td>
          <td class="text-right"><?= number_format($item['total_qty'], 3) ?></td>
          <td class="text-right">
            <?php if ($item['harga_satuan'] > 0): ?>
              Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?>
            <?php else: ?>
              <span style="opacity:.4;">-</span>
            <?php endif; ?>
          </td>
          <td class="text-right"><strong>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong></td>
          <td class="text-right">
            <div style="display:flex;align-items:center;gap:8px;justify-content:flex-end;">
              <div style="width:60px;height:6px;background:#e5e7eb;border-radius:3px;overflow:hidden;">
                <div style="width:<?= $pct ?>%;height:100%;background:var(--color-primary);"></div>
              </div>
              <span style="font-size:.85rem;"><?= $pct ?>%</span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <?php if (!empty($hpp['items'])): ?>
      <tfoot>
        <tr style="font-weight:700;background:hsl(var(--hsl-primary)/.05);">
          <td colspan="5" style="text-align:right;padding:.875rem 1rem;">
            <strong>TOTAL HPP untuk <?= number_format($porsi) ?> porsi:</strong>
          </td>
          <td class="text-right" style="color:var(--color-primary);font-size:1.1rem;">
            Rp <?= number_format($hpp['total_hpp'], 0, ',', '.') ?>
          </td>
          <td class="text-right">100%</td>
        </tr>
        <tr style="font-weight:700;background:hsl(var(--hsl-primary)/.1);">
          <td colspan="5" style="text-align:right;padding:.875rem 1rem;">
            <strong>HPP per Porsi:</strong>
          </td>
          <td class="text-right" style="color:var(--color-primary);font-size:1.2rem;">
            <strong>Rp <?= number_format($hpp['hpp_per_porsi'], 0, ',', '.') ?></strong>
          </td>
          <td></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>

<?php $this->endSection(); ?>
