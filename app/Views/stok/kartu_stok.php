<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Kartu Stok (Buku Mutasi)</h1>
    <p class="page-subtitle">Riwayat mutasi masuk/keluar untuk bahan baku <strong><?= esc($bahan['nama']) ?></strong></p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/stok') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali ke Stok
    </a>
  </div>
</div>

<!-- ══ INGREDIENT SUMMARY CARD ══ -->
<div class="card" style="margin-bottom:1.5rem; background:linear-gradient(135deg, var(--bg-card), var(--bg-card-hover));">
  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
    <div>
      <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Informasi Bahan</div>
      <div style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-top:0.25rem;">
        <?= esc($bahan['nama']) ?>
      </div>
      <div style="margin-top:0.375rem;">
        <span class="badge badge-neutral" style="font-size:0.7rem;"><?= esc($bahan['kode']) ?></span>
        <span class="badge badge-info" style="font-size:0.7rem; margin-left:0.25rem;"><?= esc($bahan['kategori']) ?></span>
      </div>
    </div>

    <div>
      <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Stok Saat Ini</div>
      <div style="font-size:1.8rem; font-weight:800; color:var(--emerald); margin-top:0.1rem; display:flex; align-items:baseline; gap:0.25rem;">
        <?= number_format($bahan['stok_saat_ini'] ?? 0) ?>
        <span style="font-size:0.9rem; font-weight:500; color:var(--text-secondary);"><?= esc($bahan['satuan']) ?></span>
      </div>
    </div>

    <div>
      <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Batas Minimum / Status</div>
      <div style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-top:0.25rem;">
        <?= number_format($bahan['stok_minimum']) ?> <?= esc($bahan['satuan']) ?>
      </div>
      <div style="margin-top:0.375rem;">
        <?php
        $stok = (float)($bahan['stok_saat_ini'] ?? 0);
        $min = (float)$bahan['stok_minimum'];
        if ($stok < $min): ?>
          <span class="badge badge-danger">🚨 Stok Kritis</span>
        <?php else: ?>
          <span class="badge badge-success">✅ Stok Aman</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ══ LEDGER TABLE CARD ══ -->
<div class="card" style="padding:0; overflow:hidden;">
  <div class="card-header" style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--border-subtle);">
    <h3 class="card-title">Buku Besar Mutasi Mutasi (Chronological Ledger)</h3>
    <p class="card-subtitle">Menampilkan mutasi berdasarkan urutan tanggal transaksi tercatat.</p>
  </div>

  <div class="table-wrapper" style="overflow-x:auto;">
    <table class="data-table" style="width:100%; border-collapse:collapse; text-align:left;">
      <thead>
        <tr style="border-bottom:1px solid var(--border-subtle);">
          <th style="padding:1rem 1.5rem; color:var(--text-muted); font-weight:600; font-size:0.8rem; text-transform:uppercase; width:60px;">No</th>
          <th style="padding:1rem 1.5rem; color:var(--text-muted); font-weight:600; font-size:0.8rem; text-transform:uppercase; width:150px;">Tanggal</th>
          <th style="padding:1rem 1.5rem; color:var(--text-muted); font-weight:600; font-size:0.8rem; text-transform:uppercase;">Keterangan / Ref</th>
          <th style="padding:1rem 1.5rem; color:var(--text-muted); font-weight:600; font-size:0.8rem; text-transform:uppercase; text-align:right; width:130px;">Masuk (+)</th>
          <th style="padding:1rem 1.5rem; color:var(--text-muted); font-weight:600; font-size:0.8rem; text-transform:uppercase; text-align:right; width:130px;">Keluar (-)</th>
          <th style="padding:1rem 1.5rem; color:var(--text-muted); font-weight:600; font-size:0.8rem; text-transform:uppercase; text-align:right; width:140px;">Saldo Stok</th>
          <th style="padding:1rem 1.5rem; color:var(--text-muted); font-weight:600; font-size:0.8rem; text-transform:uppercase; width:150px;">Petugas</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($history)): ?>
          <tr>
            <td colspan="7" style="padding:3rem 1.5rem; text-align:center; color:var(--text-muted);">
              <i data-lucide="package-open" style="width:36px; height:36px; display:block; margin:0 auto 1rem; color:var(--text-muted); opacity:0.5;"></i>
              Belum ada riwayat pergerakan stok untuk bahan baku ini.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($history as $index => $row): ?>
            <tr style="border-bottom:1px solid var(--border-subtle); transition:background-color 0.2s;" 
                onmouseover="this.style.backgroundColor='var(--bg-card-hover)'" 
                onmouseout="this.style.backgroundColor='transparent'">
              <td style="padding:1rem 1.5rem; font-size:0.875rem; color:var(--text-secondary);"><?= $index + 1 ?></td>
              <td style="padding:1rem 1.5rem; font-size:0.875rem; color:var(--text-secondary);">
                <?= date('d M Y · H:i', strtotime($row['tanggal'])) ?>
              </td>
              <td style="padding:1rem 1.5rem; font-size:0.875rem;">
                <span style="color:var(--text-primary); font-weight:500;"><?= esc($row['keterangan']) ?></span>
              </td>
              <td style="padding:1rem 1.5rem; font-size:0.875rem; text-align:right; color:var(--status-success); font-weight:600;">
                <?= ($row['stok_masuk'] > 0) ? '+' . number_format($row['stok_masuk']) . ' ' . esc($row['satuan']) : '-' ?>
              </td>
              <td style="padding:1rem 1.5rem; font-size:0.875rem; text-align:right; color:var(--status-danger); font-weight:600;">
                <?= ($row['stok_keluar'] > 0) ? '-' . number_format($row['stok_keluar']) . ' ' . esc($row['satuan']) : '-' ?>
              </td>
              <td style="padding:1rem 1.5rem; font-size:0.875rem; text-align:right; font-weight:700; color:var(--text-primary);">
                <?= number_format($row['stok_saat_ini']) ?> <?= esc($row['satuan']) ?>
              </td>
              <td style="padding:1rem 1.5rem; font-size:0.875rem; color:var(--text-secondary);">
                <span style="display:inline-flex; align-items:center; gap:0.375rem;">
                  <i data-lucide="user" style="width:13px; height:13px; color:var(--text-muted);"></i>
                  <?= esc($row['nama_user'] ?? 'Sistem') ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
