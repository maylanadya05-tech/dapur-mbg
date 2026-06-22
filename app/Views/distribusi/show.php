<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
// Variabel disuplai dari controller Distribusi::show()
// $distribusi (dari join dengan batch, resep, sekolah, armada)
?>

<?php
$badgeClass = match($distribusi['status']) {
  'diterima'  => 'badge-success',
  'dikirim'   => 'badge-warning',
  'bermasalah'=> 'badge-danger',
  default     => 'badge-neutral'
};
$timeline = [
  ['label' => 'Dijadwalkan', 'time' => date('d/m/Y', strtotime($distribusi['tanggal_distribusi'])), 'done' => true, 'desc' => 'Pengiriman dijadwalkan ke sekolah.'],
  ['label' => 'Dikirim',     'time' => $distribusi['waktu_kirim'] ? date('d/m H:i', strtotime($distribusi['waktu_kirim'])) : '-', 'done' => in_array($distribusi['status'], ['dikirim','diterima']), 'desc' => 'Makanan dalam perjalanan bersama kurir.'],
  ['label' => 'Diterima',   'time' => $distribusi['waktu_terima'] ? date('d/m H:i', strtotime($distribusi['waktu_terima'])) : '-', 'done' => $distribusi['status'] === 'diterima', 'desc' => 'Dikonfirmasi diterima oleh pihak sekolah.'],
];
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.25rem;">
      <span class="badge badge-neutral" style="font-family:monospace;font-size:0.8rem;">Distribusi #<?= $distribusi['id'] ?></span>
      <span class="badge <?= $badgeClass ?>"><?= ucfirst($distribusi['status']) ?></span>
    </div>
    <h1 class="page-title">Surat Jalan – <?= esc($distribusi['nama_sekolah']) ?></h1>
    <p class="page-subtitle"><?= esc($distribusi['nama_menu']) ?> | Batch: <?= esc($distribusi['nomor_batch']) ?></p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/distribusi') ?>" class="btn btn-secondary btn-sm" id="btnBackDistribusiShow">
      <i data-lucide="arrow-left"></i> Kembali
    </a>
    <a href="<?= base_url('/distribusi/surat-jalan/' . $distribusi['id']) ?>" class="btn btn-primary btn-sm" id="btnSuratJalan" target="_blank">
      <i data-lucide="file-text"></i> Cetak Surat Jalan
    </a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:2rem;align-items:start;">

  <!-- LEFT: Shipment Details & Signatures -->
  <div style="display:flex;flex-direction:column;gap:1.5rem;">
    
    <!-- Info Card -->
    <div class="card">
      <h3 class="card-title" style="margin-bottom:1.25rem;border-bottom:1px solid var(--border-subtle);padding-bottom:0.5rem;color:var(--text-primary);display:flex;align-items:center;gap:0.5rem;">
        <i data-lucide="info" style="color:var(--emerald);"></i> Detail Distribusi
      </h3>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;font-size:0.9rem;">
        <div>
          <div style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;margin-bottom:0.25rem;">Sekolah Penerima</div>
          <div style="font-weight:700;color:var(--text-primary);"><?= esc($distribusi['nama_sekolah']) ?> (<?= esc($distribusi['jenjang'] ?? '') ?>)</div>
          <div style="color:var(--text-secondary);font-size:0.8rem;margin-top:0.25rem;">Porsi: <?= number_format($distribusi['jumlah_porsi']) ?> porsi</div>
        </div>
        <div>
          <div style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;margin-bottom:0.25rem;">Batch & Menu</div>
          <div style="font-weight:700;color:var(--text-primary);"><?= esc($distribusi['nomor_batch']) ?></div>
          <div style="color:var(--text-secondary);font-size:0.8rem;margin-top:0.25rem;"><?= esc($distribusi['nama_menu']) ?></div>
        </div>
        <div>
          <div style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;margin-bottom:0.25rem;">Pengirim</div>
          <div style="font-weight:600;color:var(--text-primary);"><?= esc($distribusi['pengirim']) ?></div>
          <?php if ($distribusi['no_polisi']): ?>
          <div style="color:var(--text-secondary);font-size:0.8rem;"><?= esc($distribusi['no_polisi']) ?> – <?= esc($distribusi['jenis_kendaraan'] ?? '') ?></div>
          <?php endif; ?>
        </div>
        <div>
          <div style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;margin-bottom:0.25rem;">Jadwal Pengiriman</div>
          <div style="font-weight:600;color:var(--text-primary);"><?= date('d F Y', strtotime($distribusi['tanggal_distribusi'])) ?></div>
          <?php if ($distribusi['waktu_kirim']): ?>
          <div style="color:var(--text-secondary);font-size:0.8rem;">Dikirim: <?= date('H:i', strtotime($distribusi['waktu_kirim'])) ?> WIB</div>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($distribusi['catatan']): ?>
      <div style="margin-top:1rem;padding:0.75rem;background:var(--bg-card-hover);border-radius:6px;font-size:.875rem;">
        <strong>Catatan:</strong> <?= esc($distribusi['catatan']) ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Receiver & Signature Card -->
    <div class="card">
      <h3 class="card-title" style="margin-bottom:1.25rem;border-bottom:1px solid var(--border-subtle);padding-bottom:0.5rem;color:var(--text-primary);display:flex;align-items:center;gap:0.5rem;">
        <i data-lucide="pen-tool" style="color:var(--emerald);"></i> Tanda Terima & Foto Bukti
      </h3>

      <?php if ($distribusi['status'] === 'diterima'): ?>
      <div style="font-size:0.9rem;line-height:1.6;">
        <div style="display:grid;grid-template-columns:120px 1fr;gap:0.5rem;margin-bottom:0.75rem;">
          <div style="color:var(--text-muted);">Diterima oleh:</div>
          <div style="font-weight:600;color:var(--text-primary);"><?= esc($distribusi['penerima'] ?? '-') ?></div>
        </div>
        <div style="display:grid;grid-template-columns:120px 1fr;gap:0.5rem;margin-bottom:0.75rem;">
          <div style="color:var(--text-muted);">Waktu Terima:</div>
          <div style="font-weight:600;color:var(--text-primary);"><?= $distribusi['waktu_terima'] ? date('d/m/Y H:i', strtotime($distribusi['waktu_terima'])) : '-' ?></div>
        </div>

        <?php if ($distribusi['foto_bukti']): ?>
        <div style="margin-top:1rem;">
          <div style="color:var(--text-muted);font-size:.8rem;margin-bottom:.5rem;">📷 Foto Bukti Penerimaan:</div>
          <img src="<?= base_url($distribusi['foto_bukti']) ?>" alt="Foto Bukti"
               style="max-width:100%;border-radius:8px;border:1px solid var(--border-subtle);">
        </div>
        <?php else: ?>
        <div style="padding:.75rem;background:var(--bg-card-hover);border-radius:6px;font-size:.8rem;color:var(--text-muted);">
          Tidak ada foto bukti.
        </div>
        <?php endif; ?>
      </div>
      <?php elseif (in_array($distribusi['status'], ['dijadwalkan', 'dikirim'])): ?>
      <!-- Update Status Form -->
      <form method="POST" action="<?= base_url('/distribusi/update-status/' . $distribusi['id']) ?>" enctype="multipart/form-data" id="formUpdateStatusDistribusi">
        <?= csrf_field() ?>
        <div class="form-group">
          <label class="form-label">Ubah Status</label>
          <select name="status" class="form-control form-select" id="selStatusDistribusi" onchange="toggleDiterimaFields(this)">
            <option value="dikirim" <?= $distribusi['status'] === 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
            <option value="diterima">Diterima</option>
            <option value="bermasalah">Bermasalah</option>
          </select>
        </div>
        <div id="diterimaFields" style="display:none;">
          <div class="form-group">
            <label class="form-label">Nama Penerima</label>
            <input type="text" name="penerima" class="form-control" id="inputPenerimaDistribusi" placeholder="Nama pihak penerima di sekolah">
          </div>
          <div class="form-group">
            <label class="form-label">📷 Foto Bukti Penerimaan</label>
            <input type="file" name="foto_bukti" class="form-control" id="inputFotoDistribusi" accept="image/*">
          </div>
        </div>
        <div id="bermasalahFields" style="display:none;">
          <div class="form-group">
            <label class="form-label">Keterangan Masalah</label>
            <textarea name="catatan" class="form-control" id="inputCatatanMasalah" rows="2"></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" id="btnUpdateStatusDistribusi">
          <i data-lucide="check"></i> Update Status
        </button>
      </form>
      <script>
      function toggleDiterimaFields(sel) {
        document.getElementById('diterimaFields').style.display = sel.value === 'diterima' ? 'block' : 'none';
        document.getElementById('bermasalahFields').style.display = sel.value === 'bermasalah' ? 'block' : 'none';
      }
      </script>
      <?php else: ?>
      <div style="text-align:center;padding:2rem;color:var(--text-muted);">
        <p style="font-size:0.9rem;">Status: <?= esc($distribusi['status']) ?></p>
      </div>
      <?php endif; ?>
    </div>

  </div>

  <!-- RIGHT: Timeline Status Tracking -->
  <div class="card">
    <h3 class="card-title" style="margin-bottom:1.5rem;color:var(--text-primary);display:flex;align-items:center;gap:0.5rem;">
      <i data-lucide="activity" style="color:var(--emerald);"></i> Status Timeline
    </h3>

    <!-- Vertical Timeline -->
    <div style="position:relative;padding-left:2.5rem;">
      <!-- vertical line -->
      <div style="position:absolute;left:13px;top:8px;bottom:8px;width:2px;background:var(--border-subtle);"></div>

      <div style="display:flex;flex-direction:column;gap:2rem;">
        <?php foreach ($timeline as $t): ?>
        <div style="position:relative;">
          <!-- status indicator dot -->
          <div style="position:absolute;left:-35px;top:4px;width:18px;height:18px;border-radius:50%;border:3px solid var(--bg-card);display:flex;align-items:center;justify-content:center;
               background: <?= $t['done'] ? 'var(--emerald)' : 'var(--border-subtle)' ?>;
               box-shadow: <?= $t['done'] ? '0 0 10px var(--emerald)' : 'none' ?>;">
          </div>
          <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.25rem;">
              <span style="font-size:0.9rem;font-weight:700;color:<?= $t['done'] ? 'var(--text-primary)' : 'var(--text-muted)' ?>;"><?= esc($t['label']) ?></span>
              <span style="font-size:0.75rem;color:var(--text-muted);font-family:monospace;"><?= esc($t['time']) ?></span>
            </div>
            <p style="font-size:0.8rem;color:var(--text-secondary);"><?= esc($t['desc']) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<?= $this->endSection() ?>
