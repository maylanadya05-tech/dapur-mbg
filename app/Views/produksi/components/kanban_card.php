<?php
/**
 * @var array $batch
 */
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

$pct = 0;
if ($status === 'selesai') {
  $pct = 100;
} elseif ($status === 'memasak') {
  $pct = 50; // Progress indicator in cooking
}
?>

<div class="kanban-card">
  <!-- Card Header Meta -->
  <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
    <span style="font-family:monospace; font-size:0.7rem; color:var(--text-muted); font-weight:600;">
      <?= esc($batch['nomor_batch']) ?>
    </span>
    <span class="badge <?= $badgeClass ?>" style="font-size:0.6rem; padding:0.1rem 0.3rem;">
      <?= esc($kategori) ?>
    </span>
  </div>

  <!-- Menu Title -->
  <div style="margin-top: 0.125rem;">
    <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--text-primary); line-height: 1.3;">
      <?= esc($batch['nama_menu']) ?>
    </h4>
  </div>

  <!-- Batch Info -->
  <div style="display:flex; flex-direction:column; gap:0.25rem; font-size:0.75rem; color:var(--text-secondary);">
    <div style="display:flex; align-items:center; gap:0.375rem;">
      <i data-lucide="users" style="width:13px; height:13px; color:var(--emerald);"></i>
      <span>Tim: <strong><?= esc($batch['tim_produksi']) ?></strong></span>
    </div>
    <div style="display:flex; align-items:center; gap:0.375rem;">
      <i data-lucide="target" style="width:13px; height:13px; color:var(--text-muted);"></i>
      <span>Target: <strong><?= number_format($batch['target_porsi']) ?></strong> porsi</span>
    </div>
    <?php if ($status === 'selesai'): ?>
    <div style="display:flex; align-items:center; gap:0.375rem; color:var(--status-success);">
      <i data-lucide="check" style="width:13px; height:13px;"></i>
      <span>Selesai: <strong><?= number_format($batch['porsi_selesai']) ?></strong> porsi</span>
    </div>
    <?php endif; ?>
  </div>

  <!-- Progress Bar (Only for Cooking or Completed) -->
  <?php if (in_array($status, ['memasak', 'selesai'])): ?>
  <div style="margin-top: 0.25rem;">
    <div style="display:flex; justify-content:space-between; font-size:0.65rem; color:var(--text-muted); margin-bottom: 0.125rem;">
      <span>Progress Masak</span>
      <span><?= $pct ?>%</span>
    </div>
    <div class="progress-bar" style="height: 6px; width: 100%; background: var(--border-subtle); border-radius: 99px; overflow: hidden;">
      <div
        class="progress-fill"
        style="width: <?= $pct ?>%; height: 100%; background: <?= $status === 'selesai' ? 'var(--status-success)' : 'var(--status-warning)' ?>; transition: width 0.4s ease;"
      ></div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Cancelled Reason -->
  <?php if ($status === 'dibatalkan'): ?>
  <div style="background: var(--danger-dim); border: 1px solid hsla(0, 84%, 60%, 0.15); padding: 0.5rem; border-radius: var(--border-radius-sm); font-size: 0.72rem; color: var(--status-danger); line-height: 1.3;">
    <strong>Batal:</strong> <?= esc($batch['catatan'] ?? 'Tanpa alasan') ?>
  </div>
  <?php endif; ?>

  <!-- Quick Action Buttons -->
  <div class="quick-actions">
    <!-- View detail is always present -->
    <a
      href="<?= base_url('/produksi/show/' . $batch['id']) ?>"
      class="btn btn-ghost btn-sm"
      style="padding: 0.25rem 0.5rem; font-size: 0.72rem; flex: 1; justify-content: center;"
      title="Lihat Detail Logs & Bahan"
    >
      <i data-lucide="eye" style="width:13px; height:13px; margin-right:3px;"></i>
      Detail
    </a>

    <?php if ($status === 'persiapan'): ?>
      <!-- Form to update to memasak -->
      <form action="<?= base_url('/produksi/update-status/' . $batch['id']) ?>" method="POST" style="flex: 1; display:flex;">
        <?= csrf_field() ?>
        <input type="hidden" name="status" value="memasak">
        <button
          type="submit"
          class="btn btn-primary btn-sm"
          style="padding: 0.25rem 0.5rem; font-size: 0.72rem; width: 100%; justify-content: center; background: var(--status-warning); border-color: var(--status-warning);"
        >
          <i data-lucide="flame" style="width:13px; height:13px; margin-right:3px;"></i>
          Masak
        </button>
      </form>
      <!-- Cancel Button -->
      <button
        type="button"
        class="btn btn-danger btn-icon btn-sm"
        style="width: 28px; height: 28px; padding: 0; justify-content: center; align-items: center; display: inline-flex;"
        @click="openCancelModal('<?= base_url('/produksi/update-status/' . $batch['id']) ?>', '<?= esc($batch['nomor_batch']) ?>')"
        title="Batalkan Batch"
      >
        <i data-lucide="ban" style="width:13px; height:13px;"></i>
      </button>
    <?php elseif ($status === 'memasak'): ?>
      <!-- Button to trigger finish modal -->
      <button
        type="button"
        class="btn btn-primary btn-sm"
        style="padding: 0.25rem 0.5rem; font-size: 0.72rem; flex: 1; justify-content: center;"
        @click="openFinishModal('<?= base_url('/produksi/update-status/' . $batch['id']) ?>', '<?= esc($batch['nomor_batch']) ?>', <?= $batch['target_porsi'] ?>)"
      >
        <i data-lucide="check" style="width:13px; height:13px; margin-right:3px;"></i>
        Selesai
      </button>
      <!-- Cancel Button -->
      <button
        type="button"
        class="btn btn-danger btn-icon btn-sm"
        style="width: 28px; height: 28px; padding: 0; justify-content: center; align-items: center; display: inline-flex;"
        @click="openCancelModal('<?= base_url('/produksi/update-status/' . $batch['id']) ?>', '<?= esc($batch['nomor_batch']) ?>')"
        title="Batalkan Batch"
      >
        <i data-lucide="ban" style="width:13px; height:13px;"></i>
      </button>
    <?php endif; ?>
  </div>
</div>
