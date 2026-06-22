<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
// Fallback batch detail
$batch = $batch ?? [
  'id' => 2,
  'nomor_batch' => 'BATCH-20260621-002',
  'tanggal_produksi' => date('Y-m-d'),
  'target_porsi' => 350,
  'porsi_selesai' => 0,
  'status' => 'memasak',
  'tim_produksi' => 'Tim Dahlia',
  'mulai_masak' => date('Y-m-d H:i:s', strtotime('-1 hours')),
  'selesai_masak' => null,
  'catatan' => 'Pastikan ayam matang merata dengan api sedang.',
  'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours')),
  'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hours')),
  'nama_menu' => 'Ayam Suwir Rica',
  'kategori_menu' => 'Lauk Pauk',
  'dibuat_oleh_name' => 'Ahmad Gunawan',
];

// Use actual ingredients from database if passed
$ingredients = $bahanEstimasi ?? [];

// Calculate status metrics
$status = $batch['status'];
$kategori = $batch['kategori_menu'] ?? 'Makanan';
$badgeClass = match($kategori) {
  'Makanan Pokok' => 'badge-info',
  'Lauk Pauk'     => 'badge-danger',
  'Sayuran'       => 'badge-success',
  'Buah'          => 'badge-warning',
  'Minuman'       => 'badge-neutral',
  default         => 'badge-neutral'
};

$statusBadge = match($status) {
  'persiapan' => 'badge-neutral',
  'memasak'   => 'badge-warning',
  'selesai'   => 'badge-success',
  'dibatalkan'=> 'badge-danger',
  default     => 'badge-neutral'
};

$statusLabel = match($status) {
  'persiapan' => 'Dalam Persiapan',
  'memasak'   => 'Sedang Dimasak',
  'selesai'   => 'Selesai Masak',
  'dibatalkan'=> 'Dibatalkan',
  default     => ucfirst($status)
};

$pct = 0;
if ($status === 'selesai') {
  $pct = 100;
} elseif ($status === 'memasak') {
  $pct = 50;
}
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem;">
      <span style="font-family: monospace; font-size: 0.8rem; background: var(--bg-card); border: 1px solid var(--border-subtle); padding: 0.25rem 0.6rem; border-radius: 4px; color: var(--text-primary); font-weight: 600;">
        <?= esc($batch['nomor_batch']) ?>
      </span>
      <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
    </div>
    <h1 class="page-title">Batch Produksi: <?= esc($batch['nama_menu']) ?></h1>
    <p class="page-subtitle">Detail status produksi, alokasi bahan baku, dan logs pelaksana</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/produksi') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 1.5rem; align-items: start;" x-data="batchShow()">

  <!-- ══ LEFT COLUMN: BATCH DETAILS & PROGRESS & LOGS ══ -->
  <div style="display: flex; flex-direction: column; gap: 1.5rem;">
    
    <!-- General Info Card -->
    <div class="card" style="display: flex; flex-direction: column; gap: 1.25rem;">
      <div class="card-header" style="padding: 0; margin-bottom: 0.25rem; display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">Informasi Produksi</h3>
        <span class="badge <?= $badgeClass ?>"><?= esc($kategori) ?></span>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.875rem;">
        <div>
          <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Tim Pelaksana</span>
          <strong style="color:var(--text-primary);"><?= esc($batch['tim_produksi']) ?></strong>
        </div>
        <div>
          <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Tanggal Produksi</span>
          <strong style="color:var(--text-primary);"><?= date('d F Y', strtotime($batch['tanggal_produksi'])) ?></strong>
        </div>
        <div>
          <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Target Porsi</span>
          <strong style="color:var(--text-primary);"><?= number_format($batch['target_porsi']) ?> Porsi</strong>
        </div>
        <div>
          <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Porsi Selesai</span>
          <strong :style="status === 'selesai' ? 'color:var(--status-success)' : 'color:var(--text-secondary)'">
            <?= $status === 'selesai' ? number_format($batch['porsi_selesai']) . ' Porsi' : '-' ?>
          </strong>
        </div>
        <div>
          <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Dibuat Oleh</span>
          <strong style="color:var(--text-primary);"><?= esc($batch['dibuat_oleh_name'] ?? 'Sistem') ?></strong>
        </div>
        <div>
          <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">Waktu Mulai Masak</span>
          <strong style="color:var(--text-primary);">
            <?= $batch['mulai_masak'] ? date('H:i', strtotime($batch['mulai_masak'])) : '-' ?>
          </strong>
        </div>
      </div>

      <?php if (!empty($batch['catatan'])): ?>
      <div style="background: var(--bg-primary); border: 1px solid var(--border-subtle); padding: 0.75rem; border-radius: var(--border-radius-sm); font-size: 0.8rem; line-height: 1.4;">
        <span style="display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.25rem;">Catatan Batch:</span>
        <span style="color: var(--text-secondary);"><?= esc($batch['catatan']) ?></span>
      </div>
      <?php endif; ?>

      <!-- Interactive Progress Bar -->
      <div style="border-top: 1px solid var(--border-subtle); padding-top: 1rem; margin-top: 0.25rem;">
        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.375rem;">
          <span>Progress Produksi</span>
          <strong style="color:var(--text-primary);"><?= $pct ?>% selesai</strong>
        </div>
        <div class="progress-bar" style="height: 8px; width: 100%; background: var(--bg-primary); border-radius: 99px; overflow: hidden; border: 1px solid var(--border-subtle);">
          <div
            class="progress-fill"
            style="width: <?= $pct ?>%; height: 100%; background: <?= $status === 'selesai' ? 'var(--status-success)' : ($status === 'dibatalkan' ? 'var(--status-danger)' : 'var(--status-warning)') ?>; transition: width 0.4s ease;"
          ></div>
        </div>
      </div>

      <!-- Action Buttons -->
      <?php if (in_array($status, ['persiapan', 'memasak'])): ?>
      <div style="display: flex; gap: 0.75rem; justify-content: flex-end; border-top: 1px solid var(--border-subtle); padding-top: 1rem; margin-top: 0.25rem;">
        <button
          type="button"
          class="btn btn-danger btn-sm"
          @click="openCancelModal('<?= base_url('/produksi/update-status/' . $batch['id']) ?>', '<?= esc($batch['nomor_batch']) ?>')"
        >
          <i data-lucide="ban" style="width:14px; height:14px; margin-right:4px;"></i>
          Batalkan Batch
        </button>

        <?php if ($status === 'persiapan'): ?>
        <form action="<?= base_url('/produksi/update-status/' . $batch['id']) ?>" method="POST" style="display:inline;">
          <?= csrf_field() ?>
          <input type="hidden" name="status" value="memasak">
          <button type="submit" class="btn btn-primary btn-sm" style="background:var(--status-warning); border-color:var(--status-warning);">
            <i data-lucide="flame" style="width:14px; height:14px; margin-right:4px;"></i>
            Mulai Memasak
          </button>
        </form>
        <?php elseif ($status === 'memasak'): ?>
        <button
          type="button"
          class="btn btn-primary btn-sm"
          @click="openFinishModal('<?= base_url('/produksi/update-status/' . $batch['id']) ?>', '<?= esc($batch['nomor_batch']) ?>', <?= $batch['target_porsi'] ?>)"
        >
          <i data-lucide="check" style="width:14px; height:14px; margin-right:4px;"></i>
          Selesaikan Batch
        </button>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Timeline / Logs Card -->
    <div class="card" style="display: flex; flex-direction: column; gap: 1rem;">
      <h3 class="card-title">Timeline & Logs Status</h3>
      
      <div style="display: flex; flex-direction: column; gap: 1.25rem; position: relative; padding-left: 1.5rem; border-left: 2px solid var(--border-subtle); margin-left: 0.75rem; margin-top: 0.5rem;">
        
        <!-- Timeline Item: Persiapan -->
        <div style="position: relative;">
          <!-- Circle Dot -->
          <div style="position: absolute; left: -1.95rem; top: 0.2rem; width: 12px; height: 12px; border-radius: 50%; background: var(--text-muted); border: 2px solid var(--bg-card);"></div>
          <div>
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary);">Batch Dibuat (Persiapan)</div>
            <div style="font-size: 0.72rem; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($batch['created_at'])) ?></div>
            <p style="font-size: 0.78rem; color: var(--text-secondary); margin-top: 2px;">Target porsi awal diset setinggi <strong><?= number_format($batch['target_porsi']) ?></strong> porsi.</p>
          </div>
        </div>

        <!-- Timeline Item: Memasak -->
        <?php if ($batch['mulai_masak']): ?>
        <div style="position: relative;">
          <div style="position: absolute; left: -1.95rem; top: 0.2rem; width: 12px; height: 12px; border-radius: 50%; background: var(--status-warning); border: 2px solid var(--bg-card); box-shadow: 0 0 8px var(--status-warning);"></div>
          <div>
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--status-warning);">Proses Memasak Dimulai</div>
            <div style="font-size: 0.72rem; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($batch['mulai_masak'])) ?></div>
            <p style="font-size: 0.78rem; color: var(--text-secondary); margin-top: 2px;">Tim produksi mulai mengolah bahan makanan di dapur.</p>
          </div>
        </div>
        <?php endif; ?>

        <!-- Timeline Item: Selesai / Batal -->
        <?php if ($status === 'selesai' && $batch['selesai_masak']): ?>
        <div style="position: relative;">
          <div style="position: absolute; left: -1.95rem; top: 0.2rem; width: 12px; height: 12px; border-radius: 50%; background: var(--status-success); border: 2px solid var(--bg-card); box-shadow: 0 0 8px var(--status-success);"></div>
          <div>
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--status-success);">Selesai Masak & Packing</div>
            <div style="font-size: 0.72rem; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($batch['selesai_masak'])) ?></div>
            <p style="font-size: 0.78rem; color: var(--text-secondary); margin-top: 2px;">Batch ditutup dengan total <strong><?= number_format($batch['porsi_selesai']) ?></strong> porsi selesai. Siap didistribusikan ke sekolah.</p>
          </div>
        </div>
        <?php elseif ($status === 'dibatalkan'): ?>
        <div style="position: relative;">
          <div style="position: absolute; left: -1.95rem; top: 0.2rem; width: 12px; height: 12px; border-radius: 50%; background: var(--status-danger); border: 2px solid var(--bg-card);"></div>
          <div>
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--status-danger);">Batch Dibatalkan</div>
            <div style="font-size: 0.72rem; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($batch['updated_at'])) ?></div>
            <p style="font-size: 0.78rem; color: var(--status-danger); margin-top: 2px; font-weight: 600;">Alasan: <?= esc($batch['catatan'] ?: 'Tidak ada alasan khusus') ?></p>
          </div>
        </div>
        <?php endif; ?>

      </div>
    </div>

  </div>

  <!-- ══ RIGHT COLUMN: RAW MATERIAL CONSUMPTION PANEL ══ -->
  <div class="card" style="padding: 0;">
    <div class="card-header" style="padding: var(--card-padding) var(--card-padding) 0; display:flex; justify-content:space-between; align-items:center;">
      <div>
        <h3 class="card-title">Alokasi & Pemakaian Bahan</h3>
        <span style="font-size:0.8rem; color:var(--text-muted);">Detail kuantitas bahan yang berkurang dari stok</span>
      </div>
      <div>
        <?php if ($status === 'dibatalkan'): ?>
          <span class="badge badge-danger" style="font-size:0.75rem;">Stok Dikembalikan</span>
        <?php elseif ($status === 'selesai'): ?>
          <span class="badge badge-success" style="font-size:0.75rem;">Stok Terpotong</span>
        <?php else: ?>
          <span class="badge badge-warning" style="font-size:0.75rem;">Stok Dicadangkan</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Consumption Table -->
    <div class="table-wrapper" style="border:none; border-radius:0; margin-top:1.25rem;">
      <table class="data-table">
        <thead>
          <tr>
            <th width="40">No</th>
            <th>Kode</th>
            <th>Bahan Baku</th>
            <th style="text-align: right;">Porsi Target</th>
            <th style="text-align: right;">BOM / Porsi</th>
            <th style="text-align: right;">Total Estimasi</th>
            <th>Satuan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ingredients as $i => $ing): ?>
          <?php
            $qtyPerPorsi = $ing['qty_per_porsi'];
            $totalEst = $qtyPerPorsi * $batch['target_porsi'];
          ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td>
              <span style="font-family:monospace; font-size:0.78rem; background:var(--bg-card-hover); padding:0.15rem 0.4rem; border-radius:4px; color:var(--text-muted);">
                <?= esc($ing['kode_bahan']) ?>
              </span>
            </td>
            <td>
              <strong><?= esc($ing['nama_bahan']) ?></strong>
            </td>
            <td style="text-align: right; color: var(--text-secondary); font-size: 0.85rem;">
              <?= number_format($batch['target_porsi']) ?>
            </td>
            <td style="text-align: right; color: var(--text-secondary); font-size: 0.85rem; font-family:monospace;">
              <?= number_format($qtyPerPorsi, 3, ',', '.') ?>
            </td>
            <td style="text-align: right; font-weight: 800; color: var(--emerald);">
              <?= number_format($totalEst, 3, ',', '.') ?>
            </td>
            <td>
              <span style="color:var(--text-muted); font-size:0.875rem;"><?= esc($ing['satuan']) ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div style="padding: 1.25rem; background: var(--bg-card-hover); border-top: 1px solid var(--border-subtle); border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg); font-size: 0.78rem; color: var(--text-secondary); line-height: 1.4;">
      <div style="display:flex; gap:0.5rem; align-items:flex-start;">
        <i data-lucide="info" style="width:15px; height:15px; color:var(--emerald); flex-shrink:0; margin-top:2px;"></i>
        <span>Kalkulasi di atas didasarkan pada target porsi batch (<strong><?= number_format($batch['target_porsi']) ?> porsi</strong>) dikalikan dengan formula BOM resep standar yang tersimpan. Pengurangan stok fisik gudang dilakukan secara otomatis saat status batch berubah menjadi <strong>Memasak</strong>.</span>
      </div>
    </div>
  </div>

</div>

<!-- ══ MODALS (REUSED FROM INDEX) ══ -->
<div id="finishModal" class="modal-overlay" style="display:none;" x-data="{}" x-show="showFinish">
  <div class="modal-content" style="max-width: 420px;">
    <div class="modal-header">
      <h3 class="modal-title" style="display: flex; align-items: center; gap: 0.5rem; color: var(--status-success);">
        <i data-lucide="check-square"></i>
        Konfirmasi Batch Selesai
      </h3>
      <button type="button" class="modal-close" onclick="closeFinishModal()">
        <i data-lucide="x"></i>
      </button>
    </div>
    <form id="finishForm" method="POST" action="">
      <?= csrf_field() ?>
      <input type="hidden" name="status" value="selesai">
      
      <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
        <p style="color:var(--text-secondary); font-size: 0.875rem;">
          Masukkan jumlah porsi yang selesai dimasak untuk batch <strong id="finishBatchNo" style="color:var(--text-primary);"></strong>.
        </p>

        <div class="form-group">
          <label class="form-label" for="porsi_selesai">Jumlah Porsi Selesai <span class="required">*</span></label>
          <div class="input-group">
            <input
              type="number"
              id="porsi_selesai"
              name="porsi_selesai"
              class="form-control"
              required
              min="1"
              style="text-align: right; padding-right: 3.5rem;"
            >
            <div style="position:absolute; right:1rem; font-size:0.8rem; font-weight:600; color:var(--text-secondary);">Porsi</div>
          </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label" for="catatan">Catatan Produksi</label>
          <textarea name="catatan" id="catatan" class="form-textarea" rows="2" placeholder="Catatan opsional (misal: rasa pas, tekstur bagus)"></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeFinishModal()">Batal</button>
        <button type="submit" class="btn btn-primary btn-sm">
          Selesaikan Batch
        </button>
      </div>
    </form>
  </div>
</div>

<div id="cancelModal" class="modal-overlay" style="display:none;">
  <div class="modal-content" style="max-width: 420px;">
    <div class="modal-header">
      <h3 class="modal-title" style="display: flex; align-items: center; gap: 0.5rem; color: var(--status-danger);">
        <i data-lucide="alert-triangle"></i>
        Batalkan Batch Produksi
      </h3>
      <button type="button" class="modal-close" onclick="closeCancelModal()">
        <i data-lucide="x"></i>
      </button>
    </div>
    <form id="cancelForm" method="POST" action="">
      <?= csrf_field() ?>
      <input type="hidden" name="status" value="dibatalkan">
      
      <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
        <p style="color:var(--text-secondary); font-size: 0.875rem;">
          Apakah Anda yakin ingin membatalkan batch produksi <strong id="cancelBatchNo" style="color:var(--text-primary);"></strong>?
          Tindakan ini akan menghentikan alokasi bahan baku.
        </p>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label" for="alasan_batal">Alasan Pembatalan <span class="required">*</span></label>
          <textarea name="catatan" id="alasan_batal" class="form-textarea" rows="3" placeholder="Wajib mengisi alasan pembatalan batch..." required></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeCancelModal()">Tutup</button>
        <button type="submit" class="btn btn-danger btn-sm">
          Batalkan Batch
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function batchShow() {
    return {
      status: '<?= esc($status) ?>',
      
      openFinishModal(url, batchNo, targetPorsi) {
        document.getElementById('finishForm').action = url;
        document.getElementById('finishBatchNo').textContent = batchNo;
        document.getElementById('porsi_selesai').value = targetPorsi;
        document.getElementById('finishModal').style.display = 'flex';
      },

      openCancelModal(url, batchNo) {
        document.getElementById('cancelForm').action = url;
        document.getElementById('cancelBatchNo').textContent = batchNo;
        document.getElementById('cancelModal').style.display = 'flex';
      }
    };
  }

  function closeFinishModal() {
    document.getElementById('finishModal').style.display = 'none';
  }

  function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
  }

  // Ensure modals close when overlay is clicked
  document.getElementById('finishModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeFinishModal();
  });
  document.getElementById('cancelModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeCancelModal();
  });
</script>
<?= $this->endSection() ?>
