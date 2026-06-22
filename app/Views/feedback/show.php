<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$feedback = $feedback ?? [
  'id' => 1,
  'sekolah_nama' => 'SDN Merdeka 01',
  'batch_no' => 'BCH-2606-A',
  'rating' => 5,
  'komentar' => 'Makanannya sangat disukai siswa. Porsi nasi pas, ayam goreng sangat gurih dan matang merata. Kebersihan wadah luar biasa bersih. Terima kasih tim Dapur MBG!',
  'tanggal' => '2026-06-21',
  'nama_perwakilan' => 'H. Mulyadi, S.Pd.',
  'rasa' => 5,
  'porsi' => 5,
  'kebersihan' => 5,
  'ketepatan_waktu' => 4,
  'keseluruhan' => 5
];
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Detail Feedback</h1>
    <p class="page-subtitle">Penilaian lengkap dari perwakilan <?= esc($feedback['sekolah_nama']) ?></p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/feedback') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1.2fr;gap:2rem;align-items:start;">

  <!-- LEFT: Core Details and Comment -->
  <div style="display:flex;flex-direction:column;gap:1.5rem;">
    
    <div class="card">
      <h3 class="card-title" style="margin-bottom:1.25rem;border-bottom:1px solid var(--border-subtle);padding-bottom:0.5rem;color:var(--text-primary);">
        Detail Pengirim & Menu
      </h3>
      
      <div style="display:flex;flex-direction:column;gap:1rem;font-size:0.9rem;">
        <div>
          <div style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;margin-bottom:0.25rem;">Sekolah Pengirim</div>
          <div style="font-weight:700;color:var(--text-primary);font-size:1rem;"><?= esc($feedback['sekolah_nama']) ?></div>
        </div>

        <div>
          <div style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;margin-bottom:0.25rem;">Batch Menu Makanan</div>
          <div style="font-weight:600;color:var(--text-primary);"><?= esc($feedback['batch_no']) ?></div>
        </div>

        <div>
          <div style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;margin-bottom:0.25rem;">Perwakilan Pengisi</div>
          <div style="font-weight:600;color:var(--text-primary);"><?= esc($feedback['nama_perwakilan']) ?></div>
          <div style="color:var(--text-secondary);font-size:0.8rem;">Tanggal Ulasan: <?= date('d M Y', strtotime($feedback['tanggal'])) ?></div>
        </div>
      </div>
    </div>

    <!-- Comments Box -->
    <div class="card">
      <h3 class="card-title" style="margin-bottom:1rem;color:var(--text-primary);">Tanggapan / Komentar Kualitatif</h3>
      <div style="padding:1.25rem;background:var(--bg-card-hover);border-left:4px solid var(--emerald);border-radius:0 var(--border-radius) var(--border-radius) 0;font-style:italic;color:var(--text-primary);line-height:1.6;font-size:0.92rem;">
        "<?= esc($feedback['komentar'] ? $feedback['komentar'] : 'Tidak ada ulasan tertulis yang diserahkan.') ?>"
      </div>
    </div>

  </div>

  <!-- RIGHT: Detailed Rating Indicators -->
  <div class="card">
    <h3 class="card-title" style="margin-bottom:1.5rem;border-bottom:1px solid var(--border-subtle);padding-bottom:0.5rem;color:var(--text-primary);">Scorecard Aspek Masakan</h3>

    <div style="display:flex;flex-direction:column;gap:1.5rem;">
      
      <!-- Rasa -->
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
          <strong style="font-size:0.875rem;color:var(--text-primary);">1. Citarasa & Kematangan</strong>
          <span style="font-weight:700;color:var(--emerald);"><?= $feedback['rasa'] ?> / 5</span>
        </div>
        <div style="display:flex;gap:0.25rem;color:var(--status-warning);">
          <?php for($i=1; $i<=5; $i++): ?>
            <i data-lucide="star" style="width:16px;height:16px;<?= $i <= $feedback['rasa'] ? 'fill:var(--status-warning);' : 'opacity:0.3;' ?>"></i>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Porsi -->
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
          <strong style="font-size:0.875rem;color:var(--text-primary);">2. Ukuran Porsi (Kecukupan)</strong>
          <span style="font-weight:700;color:var(--emerald);"><?= $feedback['porsi'] ?> / 5</span>
        </div>
        <div style="display:flex;gap:0.25rem;color:var(--status-warning);">
          <?php for($i=1; $i<=5; $i++): ?>
            <i data-lucide="star" style="width:16px;height:16px;<?= $i <= $feedback['porsi'] ? 'fill:var(--status-warning);' : 'opacity:0.3;' ?>"></i>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Kebersihan -->
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
          <strong style="font-size:0.875rem;color:var(--text-primary);">3. Kebersihan Wadah & Makanan</strong>
          <span style="font-weight:700;color:var(--emerald);"><?= $feedback['kebersihan'] ?> / 5</span>
        </div>
        <div style="display:flex;gap:0.25rem;color:var(--status-warning);">
          <?php for($i=1; $i<=5; $i++): ?>
            <i data-lucide="star" style="width:16px;height:16px;<?= $i <= $feedback['kebersihan'] ? 'fill:var(--status-warning);' : 'opacity:0.3;' ?>"></i>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Ketepatan Waktu -->
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
          <strong style="font-size:0.875rem;color:var(--text-primary);">4. Ketepatan Waktu Pengantaran</strong>
          <span style="font-weight:700;color:var(--emerald);"><?= $feedback['ketepatan_waktu'] ?> / 5</span>
        </div>
        <div style="display:flex;gap:0.25rem;color:var(--status-warning);">
          <?php for($i=1; $i<=5; $i++): ?>
            <i data-lucide="star" style="width:16px;height:16px;<?= $i <= $feedback['ketepatan_waktu'] ? 'fill:var(--status-warning);' : 'opacity:0.3;' ?>"></i>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Overall -->
      <div style="background:var(--bg-card-hover);padding:1rem;border-radius:var(--border-radius);border:1px solid var(--border-subtle);margin-top:0.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
          <strong style="font-size:0.9rem;color:var(--emerald-light);">5. Skor Kepuasan Keseluruhan</strong>
          <span style="font-weight:800;color:var(--emerald-light);font-size:1.1rem;"><?= $feedback['keseluruhan'] ?? $feedback['rating'] ?> / 5</span>
        </div>
        <div style="display:flex;gap:0.25rem;color:var(--status-warning);">
          <?php for($i=1; $i<=5; $i++): ?>
            <i data-lucide="star" style="width:20px;height:20px;<?= $i <= ($feedback['keseluruhan'] ?? $feedback['rating']) ? 'fill:var(--status-warning);' : 'opacity:0.3;' ?>"></i>
          <?php endfor; ?>
        </div>
      </div>

    </div>
  </div>

</div>

<?= $this->endSection() ?>
