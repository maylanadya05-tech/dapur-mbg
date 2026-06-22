<?= $this->extend('layouts/app') ?>

<?= $this->section('styles') ?>
<style>
  .kanban-board {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    align-items: start;
    margin-top: 1.5rem;
  }
  .kanban-column {
    background: var(--bg-sidebar);
    border: 1px solid var(--border-subtle);
    border-radius: var(--border-radius-lg);
    display: flex;
    flex-direction: column;
    max-height: 75vh;
  }
  .kanban-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border-subtle);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .kanban-title {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .kanban-body {
    padding: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    overflow-y: auto;
    scrollbar-width: thin;
  }
  .kanban-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--border-radius);
    padding: 1rem;
    transition: var(--transition-base);
    cursor: grab;
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
  }
  .kanban-card:hover {
    border-color: var(--border-medium);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
  }
  .quick-actions {
    display: flex;
    gap: 0.375rem;
    margin-top: 0.25rem;
    border-top: 1px solid var(--border-subtle);
    padding-top: 0.625rem;
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// Use actual database records passed from controller
$batchList = $todayBatches ?? [];

// Calculations for KPI Cards
$activeCount = count(array_filter($batchList, fn($b) => in_array($b['status'], ['persiapan', 'memasak'])));
$totalTarget = array_sum(array_map(fn($b) => $b['status'] !== 'dibatalkan' ? $b['target_porsi'] : 0, $batchList));
$totalFinished = array_sum(array_map(fn($b) => $b['porsi_selesai'], $batchList));
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Monitoring Produksi Harian</h1>
    <p class="page-subtitle">Pantau progress persiapan dan proses memasak batch makanan hari ini</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/produksi/create') ?>" class="btn btn-primary btn-sm">
      <i data-lucide="plus"></i>
      Mulai Batch Baru
    </a>
  </div>
</div>

<!-- ══ KPI STAT CARDS ══ -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 1.5rem;">
  <div class="stat-card accent-info">
    <div class="stat-card-header">
      <span class="stat-card-label">Batch Aktif</span>
      <div class="stat-card-icon"><i data-lucide="activity"></i></div>
    </div>
    <div class="stat-card-value"><?= $activeCount ?></div>
    <div class="stat-card-footer">
      <i data-lucide="clock" style="width:13px;height:13px;"></i>
      Dalam persiapan / sedang dimasak
    </div>
  </div>

  <div class="stat-card accent-warning">
    <div class="stat-card-header">
      <span class="stat-card-label">Target Total Porsi</span>
      <div class="stat-card-icon"><i data-lucide="target"></i></div>
    </div>
    <div class="stat-card-value"><?= number_format($totalTarget) ?></div>
    <div class="stat-card-footer">
      <i data-lucide="soup" style="width:13px;height:13px;"></i>
      Porsi sasaran distribusi hari ini
    </div>
  </div>

  <div class="stat-card accent-success">
    <div class="stat-card-header">
      <span class="stat-card-label">Porsi Selesai</span>
      <div class="stat-card-icon"><i data-lucide="check-circle"></i></div>
    </div>
    <div class="stat-card-value"><?= number_format($totalFinished) ?></div>
    <div class="stat-card-footer">
      <span class="stat-trend up">
        <i data-lucide="trending-up" style="width:13px;height:13px;"></i>
        <?= $totalTarget > 0 ? round(($totalFinished / $totalTarget) * 100, 1) : 0 ?>% dari total target
      </span>
    </div>
  </div>
</div>

<!-- ══ KANBAN BOARD ══ -->
<div class="kanban-board" x-data="kanbanBoard()">
  
  <!-- ── 1. COLUMN: PREPARATION ── -->
  <div class="kanban-column">
    <div class="kanban-header">
      <div class="kanban-title" style="color: var(--text-primary);">
        <span style="width:8px; height:8px; border-radius:50%; background:var(--status-neutral);"></span>
        Persiapan
      </div>
      <span class="badge badge-neutral" style="font-size:0.7rem; font-weight:700;"><?= count(array_filter($batchList, fn($b) => $b['status'] === 'persiapan')) ?></span>
    </div>
    <div class="kanban-body">
      <?php foreach (array_filter($batchList, fn($b) => $b['status'] === 'persiapan') as $batch): ?>
        <?= view('produksi/components/kanban_card', ['batch' => $batch]) ?>
      <?php endforeach; ?>
      <?php if (empty(array_filter($batchList, fn($b) => $b['status'] === 'persiapan'))): ?>
        <div style="text-align:center; padding: 2rem 0; color: var(--text-muted); font-size: 0.8rem;">Tidak ada batch</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── 2. COLUMN: COOKING ── -->
  <div class="kanban-column">
    <div class="kanban-header">
      <div class="kanban-title" style="color: var(--status-warning);">
        <span style="width:8px; height:8px; border-radius:50%; background:var(--status-warning); box-shadow: 0 0 8px var(--status-warning);"></span>
        Memasak
      </div>
      <span class="badge badge-warning" style="font-size:0.7rem; font-weight:700;"><?= count(array_filter($batchList, fn($b) => $b['status'] === 'memasak')) ?></span>
    </div>
    <div class="kanban-body">
      <?php foreach (array_filter($batchList, fn($b) => $b['status'] === 'memasak') as $batch): ?>
        <?= view('produksi/components/kanban_card', ['batch' => $batch]) ?>
      <?php endforeach; ?>
      <?php if (empty(array_filter($batchList, fn($b) => $b['status'] === 'memasak'))): ?>
        <div style="text-align:center; padding: 2rem 0; color: var(--text-muted); font-size: 0.8rem;">Tidak ada proses masak</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── 3. COLUMN: COMPLETED ── -->
  <div class="kanban-column">
    <div class="kanban-header">
      <div class="kanban-title" style="color: var(--status-success);">
        <span style="width:8px; height:8px; border-radius:50%; background:var(--status-success); box-shadow: 0 0 8px var(--status-success);"></span>
        Selesai
      </div>
      <span class="badge badge-success" style="font-size:0.7rem; font-weight:700;"><?= count(array_filter($batchList, fn($b) => $b['status'] === 'selesai')) ?></span>
    </div>
    <div class="kanban-body">
      <?php foreach (array_filter($batchList, fn($b) => $b['status'] === 'selesai') as $batch): ?>
        <?= view('produksi/components/kanban_card', ['batch' => $batch]) ?>
      <?php endforeach; ?>
      <?php if (empty(array_filter($batchList, fn($b) => $b['status'] === 'selesai'))): ?>
        <div style="text-align:center; padding: 2rem 0; color: var(--text-muted); font-size: 0.8rem;">Belum ada batch selesai</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── 4. COLUMN: CANCELLED ── -->
  <div class="kanban-column">
    <div class="kanban-header">
      <div class="kanban-title" style="color: var(--status-danger);">
        <span style="width:8px; height:8px; border-radius:50%; background:var(--status-danger);"></span>
        Dibatalkan
      </div>
      <span class="badge badge-danger" style="font-size:0.7rem; font-weight:700;"><?= count(array_filter($batchList, fn($b) => $b['status'] === 'dibatalkan')) ?></span>
    </div>
    <div class="kanban-body">
      <?php foreach (array_filter($batchList, fn($b) => $b['status'] === 'dibatalkan') as $batch): ?>
        <?= view('produksi/components/kanban_card', ['batch' => $batch]) ?>
      <?php endforeach; ?>
      <?php if (empty(array_filter($batchList, fn($b) => $b['status'] === 'dibatalkan'))): ?>
        <div style="text-align:center; padding: 2rem 0; color: var(--text-muted); font-size: 0.8rem;">Tidak ada pembatalan</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ══ COMPLETED INPUT MODAL ══ -->
  <div id="finishModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width: 420px;">
      <div class="modal-header">
        <h3 class="modal-title" style="display: flex; align-items: center; gap: 0.5rem; color: var(--status-success);">
          <i data-lucide="check-square"></i>
          Konfirmasi Batch Selesai
        </h3>
        <button type="button" class="modal-close" @click="closeFinishModal()">
          <i data-lucide="x"></i>
        </button>
      </div>
      <form :action="finishActionUrl" method="POST">
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
                :value="finishDefaultPorsi"
                style="text-align: right; padding-right: 3.5rem;"
              >
              <div style="position:absolute; right:1rem; font-size:0.8rem; font-weight:600; color:var(--text-secondary);">Porsi</div>
            </div>
            <div class="form-hint">Default terisi sesuai target porsi awal: <span x-text="finishDefaultPorsi"></span> porsi.</div>
          </div>

          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="catatan">Catatan Produksi</label>
            <textarea name="catatan" id="catatan" class="form-textarea" rows="2" placeholder="Catatan opsional (misal: rasa pas, tekstur bagus)"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" @click="closeFinishModal()">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i data-lucide="check"></i>
            Selesaikan Batch
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ══ CANCEL CONFIRM MODAL ══ -->
  <div id="cancelModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width: 420px;">
      <div class="modal-header">
        <h3 class="modal-title" style="display: flex; align-items: center; gap: 0.5rem; color: var(--status-danger);">
          <i data-lucide="alert-triangle"></i>
          Batalkan Batch Produksi
        </h3>
        <button type="button" class="modal-close" @click="closeCancelModal()">
          <i data-lucide="x"></i>
        </button>
      </div>
      <form :action="cancelActionUrl" method="POST">
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
          <button type="button" class="btn btn-secondary btn-sm" @click="closeCancelModal()">Tutup</button>
          <button type="submit" class="btn btn-danger btn-sm">
            Batalkan Batch
          </button>
        </div>
      </form>
    </div>
  </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function kanbanBoard() {
    return {
      finishActionUrl: '',
      finishBatchNo: '',
      finishDefaultPorsi: 0,
      
      cancelActionUrl: '',
      cancelBatchNo: '',

      openFinishModal(url, batchNo, targetPorsi) {
        this.finishActionUrl = url;
        this.finishDefaultPorsi = targetPorsi;
        document.getElementById('finishBatchNo').textContent = batchNo;
        document.getElementById('porsi_selesai').value = targetPorsi;
        document.getElementById('finishModal').style.display = 'flex';
      },

      closeFinishModal() {
        document.getElementById('finishModal').style.display = 'none';
      },

      openCancelModal(url, batchNo) {
        this.cancelActionUrl = url;
        document.getElementById('cancelBatchNo').textContent = batchNo;
        document.getElementById('cancelModal').style.display = 'flex';
      },

      closeCancelModal() {
        document.getElementById('cancelModal').style.display = 'none';
      }
    };
  }

  // Ensure modals close when overlay is clicked
  document.getElementById('finishModal')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
  });
  document.getElementById('cancelModal')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
  });
</script>
<?= $this->endSection() ?>
