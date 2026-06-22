<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
// Fallback recipe
$resep = $resep ?? [
  'id' => 1,
  'kode' => 'RSP-2026-001',
  'nama_menu' => 'Nasi Kuning Harum',
  'deskripsi' => 'Nasi kuning wangi kelapa dan pandan, menu pokok untuk Makan Bergizi Gratis. Sangat disukai oleh anak-anak sekolah dasar karena harum kunyit yang khas dan gurih kelapa yang seimbang.',
  'kategori' => 'Makanan Pokok',
  'total_kalori' => 350.5,
  'total_protein' => 6.2,
  'total_karbohidrat' => 78.4,
  'porsi_standar' => 1,
  'foto' => '',
  'is_active' => 1,
  'created_at' => '2026-06-20 08:00:00'
];

// Fallback ingredients (BOM items)
$resepDetail = $resepDetail ?? [
  ['id' => 1, 'kode_bahan' => 'BH-001', 'nama_bahan' => 'Beras Premium', 'qty_per_porsi' => 0.120, 'satuan' => 'kg', 'harga_per_satuan' => 12500, 'keterangan' => 'Beras pulen dicuci bersih'],
  ['id' => 2, 'kode_bahan' => 'BH-003', 'nama_bahan' => 'Minyak Goreng', 'qty_per_porsi' => 0.015, 'satuan' => 'liter', 'harga_per_satuan' => 18000, 'keterangan' => 'Minyak kelapa sawit'],
  ['id' => 3, 'kode_bahan' => 'BH-007', 'nama_bahan' => 'Garam Dapur', 'qty_per_porsi' => 0.002, 'satuan' => 'kg', 'harga_per_satuan' => 3500, 'keterangan' => 'Garam halus beryodium'],
  ['id' => 4, 'kode_bahan' => 'BH-009', 'nama_bahan' => 'Bawang Putih', 'qty_per_porsi' => 0.003, 'satuan' => 'kg', 'harga_per_satuan' => 32000, 'keterangan' => 'Cincang halus'],
  ['id' => 5, 'kode_bahan' => 'BH-010', 'nama_bahan' => 'Bawang Merah', 'qty_per_porsi' => 0.005, 'satuan' => 'kg', 'harga_per_satuan' => 35000, 'keterangan' => 'Iris tipis, ditumis'],
];

// Calculate costs
$totalCost = 0;
foreach ($resepDetail as &$item) {
  $item['cost'] = $item['qty_per_porsi'] * ($item['harga_per_satuan'] ?? 0);
  $totalCost += $item['cost'];
}
unset($item);

// Category styling
$kategori = $resep['kategori'];
$badgeClass = match($kategori) {
  'Makanan Pokok' => 'badge-info',
  'Lauk Pauk'     => 'badge-danger',
  'Sayuran'       => 'badge-success',
  'Buah'          => 'badge-warning',
  'Minuman'       => 'badge-neutral',
  default         => 'badge-neutral'
};
$categoryIcon = match($kategori) {
  'Makanan Pokok' => 'soup',
  'Lauk Pauk'     => 'drumstick',
  'Sayuran'       => 'salad',
  'Buah'          => 'apple',
  'Minuman'       => 'cup-soda',
  default         => 'cooking-pot'
};
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem;">
      <span style="font-family: monospace; font-size: 0.8rem; background: var(--bg-card); border: 1px solid var(--border-subtle); padding: 0.25rem 0.6rem; border-radius: 4px; color: var(--emerald); font-weight: 600;">
        <?= esc($resep['kode']) ?>
      </span>
      <span class="badge <?= $badgeClass ?>"><?= esc($resep['kategori']) ?></span>
    </div>
    <h1 class="page-title"><?= esc($resep['nama_menu']) ?></h1>
    <p class="page-subtitle">Detail komposisi bahan baku dan nilai gizi per porsi standar</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/resep/hpp/' . $resep['id']) ?>" class="btn btn-info btn-sm">
      <i data-lucide="calculator"></i>
      Kalkulasi HPP
    </a>
    <a href="<?= base_url('/resep/edit/' . $resep['id']) ?>" class="btn btn-primary btn-sm">
      <i data-lucide="pencil"></i>
      Edit Resep
    </a>
    <a href="<?= base_url('/resep') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div style="display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; align-items: start;">
  
  <!-- ══ LEFT COLUMN: PHOTO & NUTRITION ══ -->
  <div style="display: flex; flex-direction: column; gap: 1.5rem;">
    
    <!-- Photo Card -->
    <div class="card" style="padding: 0; overflow: hidden; position: relative;">
      <div style="height: 200px; background: linear-gradient(135deg, var(--bg-card-hover) 0%, var(--bg-sidebar) 100%); display: flex; align-items: center; justify-content: center; position: relative;">
        <?php if (!empty($resep['foto'])): ?>
          <img src="<?= base_url('uploads/resep/' . $resep['foto']) ?>" alt="<?= esc($resep['nama_menu']) ?>" style="width:100%; height:100%; object-fit:cover;">
        <?php else: ?>
          <div style="text-align: center; color: var(--text-muted);">
            <i data-lucide="<?= $categoryIcon ?>" style="width: 64px; height: 64px; opacity: 0.35; stroke-width: 1.5; color: var(--emerald);"></i>
            <div style="font-size:0.75rem; margin-top:0.5rem;">Tidak Ada Foto</div>
          </div>
        <?php endif; ?>
      </div>
      <div style="padding: 1.25rem;">
        <h4 style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.08em; margin-bottom: 0.5rem;">Deskripsi Menu</h4>
        <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 0;">
          <?= esc($resep['deskripsi'] ?: 'Tidak ada deskripsi untuk resep menu ini.') ?>
        </p>
      </div>
    </div>

    <!-- Nutrition Circular/Glow Panel -->
    <div class="card" style="display: flex; flex-direction: column; gap: 1.25rem;">
      <h3 class="card-title" style="margin-bottom: 0.25rem;">Nutrisi Per Porsi</h3>
      
      <div style="display: flex; flex-direction: column; gap: 1rem;">
        
        <!-- Kalori -->
        <div style="display:flex; align-items:center; gap:0.875rem;">
          <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--bg-primary); border: 2px solid var(--text-muted); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i data-lucide="zap" style="width:18px; height:18px; color:var(--text-primary);"></i>
          </div>
          <div>
            <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">Energi (Kalori)</div>
            <div style="font-size:1.1rem; font-weight:800; color:var(--text-primary);">
              <?= number_format($resep['total_kalori'], 1) ?> <span style="font-size:0.78rem; font-weight:normal; color:var(--text-secondary);">kcal</span>
            </div>
          </div>
        </div>

        <!-- Protein -->
        <div style="display:flex; align-items:center; gap:0.875rem;">
          <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--emerald-dim); border: 2px solid var(--emerald); display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow: var(--emerald-glow);">
            <i data-lucide="beef" style="width:18px; height:18px; color:var(--emerald);"></i>
          </div>
          <div>
            <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">Protein</div>
            <div style="font-size:1.1rem; font-weight:800; color:var(--emerald);">
              <?= number_format($resep['total_protein'], 1) ?> <span style="font-size:0.78rem; font-weight:normal; color:var(--text-secondary);">g</span>
            </div>
          </div>
        </div>

        <!-- Karbohidrat -->
        <div style="display:flex; align-items:center; gap:0.875rem;">
          <div style="width: 44px; height: 44px; border-radius: 50%; background: hsla(210, 100%, 56%, 0.15); border: 2px solid var(--status-info); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i data-lucide="wheat" style="width:18px; height:18px; color:var(--status-info);"></i>
          </div>
          <div>
            <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">Karbohidrat</div>
            <div style="font-size:1.1rem; font-weight:800; color:var(--status-info);">
              <?= number_format($resep['total_karbohidrat'], 1) ?> <span style="font-size:0.78rem; font-weight:normal; color:var(--text-secondary);">g</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Standard Serving Size Info -->
      <div style="margin-top:0.5rem; padding: 0.75rem; border-radius: var(--border-radius-sm); border: 1px dashed var(--border-subtle); font-size: 0.78rem; color: var(--text-secondary); text-align: center;">
        <i data-lucide="scale" style="width:13px; height:13px; margin-right:4px; vertical-align:middle;"></i>
        Porsi standar resep: <strong><?= $resep['porsi_standar'] ?> Porsi</strong>
      </div>
    </div>

  </div>

  <!-- ══ RIGHT COLUMN: BOM DETAILS ══ -->
  <div style="display: flex; flex-direction: column; gap: 1.5rem;">
    
    <!-- Cost Summary Card -->
    <div class="card" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: center; background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-card-hover) 100%);">
      <div>
        <h3 style="font-size:1rem; font-weight:700; color:var(--text-primary); margin-bottom:0.25rem;">Estimasi Biaya Bahan per Porsi</h3>
        <p style="font-size:0.8rem; color:var(--text-muted); margin-bottom:0;">Dihitung otomatis berdasarkan harga bahan baku terdaftar di gudang saat ini.</p>
      </div>
      <div style="text-align: right; background: var(--bg-primary); padding: 1rem; border-radius: var(--border-radius); border: 1px solid var(--border-subtle);">
        <div style="font-size:0.65rem; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.125rem;">Total Biaya</div>
        <div style="font-size:1.4rem; font-weight:800; color:var(--emerald);">Rp <?= number_format($totalCost, 0, ',', '.') ?></div>
      </div>
    </div>

    <!-- BOM Ingredients Table -->
    <div class="card" style="padding: 0;">
      <div class="card-header" style="padding: var(--card-padding) var(--card-padding) 0;">
        <h3 class="card-title">Daftar Kebutuhan Bahan (BOM)</h3>
        <span class="badge badge-neutral" style="font-family:monospace;"><?= count($resepDetail) ?> Item</span>
      </div>

      <div class="table-wrapper" style="border: none; border-radius: 0; margin-top: 1rem;">
        <table class="data-table">
          <thead>
            <tr>
              <th width="40">No</th>
              <th width="100">Kode</th>
              <th>Nama Bahan Baku</th>
              <th style="text-align: right;">Kebutuhan per Porsi</th>
              <th width="80">Satuan</th>
              <th style="text-align: right;">Estimasi Harga</th>
              <th style="text-align: right;">Subtotal Biaya</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($resepDetail as $i => $item): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td>
                <span style="font-family:monospace; font-size:0.78rem; background:var(--bg-card-hover); padding:0.15rem 0.4rem; border-radius:4px; color:var(--text-muted);">
                  <?= esc($item['kode_bahan']) ?>
                </span>
              </td>
              <td>
                <strong><?= esc($item['nama_bahan']) ?></strong>
              </td>
              <td style="text-align: right; font-weight: 700; color: var(--text-primary);">
                <?= number_format($item['qty_per_porsi'], 3, ',', '.') ?>
              </td>
              <td>
                <span style="color:var(--text-muted); font-size:0.875rem;"><?= esc($item['satuan']) ?></span>
              </td>
              <td style="text-align: right; color: var(--text-secondary); font-size: 0.85rem;">
                Rp <?= number_format($item['harga_per_satuan'] ?? 0, 0, ',', '.') ?>
              </td>
              <td style="text-align: right; font-weight: 700; color: var(--emerald);">
                Rp <?= number_format($item['cost'], 0, ',', '.') ?>
              </td>
              <td style="font-size: 0.8rem; color: var(--text-secondary); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= esc($item['keterangan']) ?>">
                <?= esc($item['keterangan'] ?: '-') ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div style="padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; background: var(--bg-card-hover); border-top: 1px solid var(--border-subtle); border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);">
        <span style="font-size: 0.8rem; color: var(--text-muted);">Dibuat tanggal: <strong><?= date('d M Y H:i', strtotime($resep['created_at'])) ?></strong></span>
        
        <div style="display: flex; gap: 0.5rem;">
          <a href="<?= base_url('/resep/edit/' . $resep['id']) ?>" class="btn btn-secondary btn-sm">
            <i data-lucide="pencil" style="width: 14px; height: 14px; margin-right:4px;"></i>
            Edit Formula
          </a>
        </div>
      </div>
    </div>

  </div>

</div>

<?= $this->endSection() ?>
