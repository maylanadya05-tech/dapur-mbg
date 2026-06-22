<?= $this->extend('layouts/app') ?>

<?= $this->section('styles') ?>
<style>
  .notif-container {
    max-width: 860px;
    margin: 0 auto;
    padding-bottom: 3rem;
  }

  /* ── Page Header ── */
  .notif-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .notif-total-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--border-radius-full);
    padding: 0.3rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text-secondary);
  }

  .notif-total-badge .dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--emerald);
  }

  /* ── Category Accordion ── */
  .notif-category {
    border: 1px solid var(--border-subtle);
    border-radius: var(--border-radius-lg);
    background: var(--bg-card);
    overflow: hidden;
    margin-bottom: 1rem;
    transition: box-shadow var(--transition-base);
  }

  .notif-category:has(.notif-category-body.open) {
    box-shadow: var(--shadow-md);
  }

  .notif-category-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    cursor: pointer;
    user-select: none;
    gap: 0.75rem;
    transition: background var(--transition-fast);
  }

  .notif-category-header:hover {
    background: var(--bg-card-hover);
  }

  .notif-category-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    min-width: 0;
  }

  .notif-cat-icon {
    width: 36px; height: 36px;
    border-radius: var(--border-radius);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .notif-cat-icon i { width: 18px; height: 18px; }

  .cat-danger  .notif-cat-icon { background: var(--danger-dim);  color: var(--status-danger); }
  .cat-warning .notif-cat-icon { background: var(--warning-dim); color: var(--status-warning); }
  .cat-success .notif-cat-icon { background: var(--success-dim); color: var(--status-success); }
  .cat-info    .notif-cat-icon { background: var(--info-dim);    color: var(--status-info); }

  .notif-cat-info { flex: 1; min-width: 0; }

  .notif-cat-title {
    font-size: 0.925rem;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  .notif-cat-desc {
    font-size: 0.78rem;
    color: var(--text-muted);
    margin-top: 0.1rem;
  }

  .notif-cat-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    border-radius: var(--border-radius-full);
    font-size: 0.72rem;
    font-weight: 700;
  }

  .cat-danger  .notif-cat-count { background: var(--danger-dim);  color: var(--status-danger); }
  .cat-warning .notif-cat-count { background: var(--warning-dim); color: var(--status-warning); }
  .cat-success .notif-cat-count { background: var(--success-dim); color: var(--status-success); }
  .cat-info    .notif-cat-count { background: var(--info-dim);    color: var(--status-info); }

  .notif-cat-urgent-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.1rem 0.45rem;
    background: var(--danger-dim);
    color: var(--status-danger);
    border-radius: var(--border-radius-full);
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    animation: blinker 2s linear infinite;
  }

  @keyframes blinker { 50% { opacity: 0.7; } }

  .notif-toggle-icon {
    color: var(--text-muted);
    flex-shrink: 0;
    transition: transform var(--transition-fast);
    display: flex;
    align-items: center;
  }
  .notif-toggle-icon i { width: 18px; height: 18px; }
  .notif-category-body.open ~ * .notif-toggle-icon,
  .notif-category-header[aria-expanded="true"] .notif-toggle-icon {
    transform: rotate(180deg);
  }

  /* ── Category Body (accordion panel) ── */
  .notif-category-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    border-top: 1px solid transparent;
  }

  .notif-category-body.open {
    max-height: 2000px;
    border-top-color: var(--border-subtle);
  }

  /* ── Notification Row (inside accordion) ── */
  .notif-row {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid var(--border-subtle);
    transition: background var(--transition-fast);
  }

  .notif-row:last-child { border-bottom: none; }
  .notif-row:hover { background: var(--bg-card-hover); }

  .notif-row-icon {
    width: 32px; height: 32px;
    border-radius: var(--border-radius);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    margin-top: 0.1rem;
  }
  .notif-row-icon i { width: 15px; height: 15px; }

  .severity-danger  .notif-row-icon { background: var(--danger-dim);  color: var(--status-danger); }
  .severity-warning .notif-row-icon { background: var(--warning-dim); color: var(--status-warning); }
  .severity-success .notif-row-icon { background: var(--success-dim); color: var(--status-success); }
  .severity-info    .notif-row-icon { background: var(--info-dim);    color: var(--status-info); }

  .notif-row-body { flex: 1; min-width: 0; }

  .notif-row-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.2rem;
    line-height: 1.3;
  }

  .notif-row-msg {
    font-size: 0.8rem;
    color: var(--text-secondary);
    line-height: 1.45;
    margin-bottom: 0.35rem;
  }

  .notif-row-meta {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.72rem;
    color: var(--text-muted);
  }
  .notif-row-meta i { width: 11px; height: 11px; }

  .notif-row-action {
    flex-shrink: 0;
    align-self: center;
  }

  .btn-notif-detail {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.4rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: var(--border-radius-sm);
    border: 1px solid var(--border-subtle);
    background: var(--bg-input);
    color: var(--text-secondary);
    text-decoration: none;
    transition: all var(--transition-fast);
    white-space: nowrap;
  }
  .btn-notif-detail i { width: 12px; height: 12px; transition: transform 0.2s; }
  .btn-notif-detail:hover {
    border-color: var(--emerald);
    color: var(--emerald);
    background: var(--bg-card-hover);
    transform: translateX(2px);
  }
  .btn-notif-detail:hover i { transform: translateX(2px); }

  /* ── Urgent top border strip ── */
  .notif-category.cat-urgent {
    border-color: hsla(0, 84%, 60%, 0.2);
  }
  .notif-category.cat-urgent .notif-category-header {
    border-left: 3px solid var(--status-danger);
  }

  /* ── Summary strip (collapsed preview) ── */
  .notif-preview-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-left: auto;
    padding-right: 0.5rem;
  }

  .notif-preview-chip {
    font-size: 0.68rem;
    font-weight: 500;
    color: var(--text-muted);
    background: var(--bg-input);
    border-radius: var(--border-radius-full);
    padding: 0.1rem 0.5rem;
    border: 1px solid var(--border-subtle);
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  /* ── Empty State ── */
  .notif-empty {
    text-align: center;
    padding: 5rem 2rem;
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--border-radius-lg);
  }

  .notif-empty-icon {
    width: 72px; height: 72px;
    border-radius: var(--border-radius-full);
    background: var(--bg-input);
    display: inline-flex;
    align-items: center; justify-content: center;
    margin-bottom: 1.25rem;
    color: var(--text-muted);
    border: 1px solid var(--border-subtle);
  }
  .notif-empty-icon i { width: 32px; height: 32px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
  $totalCount = count($urgentNotifications) + count($infoNotifications);

  // Group urgentNotifications by type
  $groupedUrgent = [];
  foreach ($urgentNotifications as $n) {
      $groupedUrgent[$n['type']][] = $n;
  }
  // Group infoNotifications by type
  $groupedInfo = [];
  foreach ($infoNotifications as $n) {
      $groupedInfo[$n['type']][] = $n;
  }

  // Category meta
  $catMeta = [
      'stok' => [
          'label'   => 'Stok Kritis',
          'desc'    => 'Bahan baku di bawah batas minimum',
          'icon'    => 'alert-triangle',
          'color'   => 'danger',
      ],
      'po' => [
          'label'   => 'Purchase Order',
          'desc'    => 'Status dan persetujuan PO',
          'icon'    => 'shopping-cart',
          'color'   => 'warning',
      ],
  ];
?>

<div class="notif-container">

  <!-- ══ PAGE HEADER ══ -->
  <div class="notif-page-header">
    <div>
      <h1 class="page-title">Notifikasi Sistem</h1>
      <p class="page-subtitle" style="margin-top:0.25rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="notif-total-badge">
          <span class="dot"></span>
          <?= $totalCount ?> notifikasi aktif
        </span>
        <span class="badge badge-success" style="font-size:0.7rem; padding:0.2rem 0.6rem;">
          <i data-lucide="shield" style="width:10px;height:10px;display:inline;margin-right:2px;vertical-align:middle;"></i>
          <?= ucfirst($userRole) ?>
        </span>
      </p>
    </div>
  </div>

  <?php if ($totalCount > 0): ?>

    <!-- ══ URGENT SECTION ══ -->
    <?php if (!empty($groupedUrgent)): ?>
    <div style="margin-bottom: 0.5rem;">
      <div style="font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--status-danger); margin-bottom:0.75rem; display:flex; align-items:center; gap:0.4rem;">
        <i data-lucide="alert-octagon" style="width:13px;height:13px;"></i>
        Peringatan Prioritas Utama
      </div>

      <?php foreach ($groupedUrgent as $type => $items): ?>
        <?php $meta = $catMeta[$type] ?? ['label'=>ucfirst($type),'desc'=>'','icon'=>'bell','color'=>'info']; ?>
        <?php $isOpen = count($items) <= 3; /* auto-open if few items */ ?>
        <?php
          $stokCategories = [];
          if ($type === 'stok') {
              foreach ($items as $item) {
                  if (!empty($item['kategori'])) {
                      $stokCategories[] = $item['kategori'];
                  }
              }
              $stokCategories = array_unique($stokCategories);
          }
        ?>
        <div class="notif-category cat-<?= $meta['color'] ?> cat-urgent" x-data="{ open: <?= $isOpen ? 'true' : 'false' ?> }">

          <!-- Header -->
          <div class="notif-category-header" @click="open = !open" :aria-expanded="open">
            <div class="notif-category-header-left">
              <div class="notif-cat-icon">
                <i data-lucide="<?= $meta['icon'] ?>"></i>
              </div>
              <div class="notif-cat-info">
                <div class="notif-cat-title">
                  <?= $meta['label'] ?>
                  <span class="notif-cat-count"><?= count($items) ?></span>
                  <span class="notif-cat-urgent-pill">Mendesak</span>
                </div>
                <div class="notif-cat-desc">
                  <?= $meta['desc'] ?>
                  <?php if (!empty($stokCategories)): ?>
                    <span style="opacity: 0.9; margin-left: 0.4rem; color: var(--status-danger); font-weight: 600;">
                      (Kategori: <?= implode(', ', $stokCategories) ?>)
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Preview chips (shown when collapsed) -->
            <div class="notif-preview-chips" x-show="!open" x-cloak>
              <?php foreach (array_slice($items, 0, 3) as $chip): ?>
                <?php
                  $chipText = str_replace(['Stok Kritis: ','PO Diajukan: ','PO Disetujui: ','PO Ditolak: '], '', $chip['title']);
                  if ($chip['type'] === 'stok' && !empty($chip['kategori'])) {
                      $chipText = esc($chip['nama_bahan']) . ' (' . esc($chip['kategori']) . ')';
                  }
                ?>
                <span class="notif-preview-chip" title="<?= esc($chip['title']) ?>">
                  <?= esc(substr($chipText, 0, 25)) ?>
                </span>
              <?php endforeach; ?>
              <?php if (count($items) > 3): ?>
                <span class="notif-preview-chip">+<?= count($items) - 3 ?> lagi</span>
              <?php endif; ?>
            </div>

            <div class="notif-toggle-icon" :style="open ? 'transform:rotate(180deg)' : ''">
              <i data-lucide="chevron-down"></i>
            </div>
          </div>

          <!-- Body -->
          <div class="notif-category-body" :class="{ 'open': open }">
            <?php foreach ($items as $notif): ?>
            <div class="notif-row severity-<?= $notif['severity'] ?>">
              <div class="notif-row-icon">
                <i data-lucide="<?= $notif['icon'] ?>"></i>
              </div>
              <div class="notif-row-body">
                <div class="notif-row-title">
                  <?= $notif['title'] ?>
                  <?php if (!empty($notif['kategori'])): ?>
                    <span class="badge" style="font-size:0.68rem; margin-left:0.5rem; padding:0.1rem 0.45rem; border-radius:4px; border:1px solid rgba(239,68,68,0.2); background:var(--danger-dim); color:var(--status-danger); font-weight:600;">
                      <?= esc($notif['kategori']) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="notif-row-msg"><?= $notif['message'] ?></div>
                <div class="notif-row-meta">
                  <i data-lucide="clock"></i>
                  <?= $notif['friendly_time'] ?>
                </div>
              </div>
              <div class="notif-row-action">
                <a href="<?= $notif['link'] ?>" class="btn-notif-detail">
                  Detail <i data-lucide="arrow-right"></i>
                </a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ══ INFO SECTION ══ -->
    <?php if (!empty($groupedInfo)): ?>
    <div style="margin-top: 1.5rem;">
      <div style="font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--text-muted); margin-bottom:0.75rem; display:flex; align-items:center; gap:0.4rem;">
        <i data-lucide="history" style="width:13px;height:13px;"></i>
        Pemberitahuan Lainnya
      </div>

      <?php foreach ($groupedInfo as $type => $items): ?>
        <?php $meta = $catMeta[$type] ?? ['label'=>ucfirst($type),'desc'=>'','icon'=>'bell','color'=>'info']; ?>
        <?php
          // Determine color based on first item's severity
          $firstSeverity = $items[0]['severity'] ?? 'info';
          $displayColor = $firstSeverity;
          $stokCategories = [];
          if ($type === 'stok') {
              foreach ($items as $item) {
                  if (!empty($item['kategori'])) {
                      $stokCategories[] = $item['kategori'];
                  }
              }
              $stokCategories = array_unique($stokCategories);
          }
        ?>
        <div class="notif-category cat-<?= $displayColor ?>" x-data="{ open: false }">

          <!-- Header -->
          <div class="notif-category-header" @click="open = !open" :aria-expanded="open">
            <div class="notif-category-header-left">
              <div class="notif-cat-icon">
                <i data-lucide="<?= $meta['icon'] ?>"></i>
              </div>
              <div class="notif-cat-info">
                <div class="notif-cat-title">
                  <?= $meta['label'] ?>
                  <span class="notif-cat-count"><?= count($items) ?></span>
                </div>
                <div class="notif-cat-desc">
                  <?= $meta['desc'] ?>
                  <?php if (!empty($stokCategories)): ?>
                    <span style="opacity: 0.9; margin-left: 0.4rem; color: var(--status-danger); font-weight: 600;">
                      (Kategori: <?= implode(', ', $stokCategories) ?>)
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Preview chips -->
            <div class="notif-preview-chips" x-show="!open" x-cloak>
              <?php foreach (array_slice($items, 0, 3) as $chip): ?>
                <?php
                  $chipText = str_replace(['Stok Kritis: ','PO Diajukan: ','PO Disetujui: ','PO Ditolak: '], '', $chip['title']);
                  if ($chip['type'] === 'stok' && !empty($chip['kategori'])) {
                      $chipText = esc($chip['nama_bahan']) . ' (' . esc($chip['kategori']) . ')';
                  }
                ?>
                <span class="notif-preview-chip" title="<?= esc($chip['title']) ?>">
                  <?= esc(substr($chipText, 0, 25)) ?>
                </span>
              <?php endforeach; ?>
              <?php if (count($items) > 3): ?>
                <span class="notif-preview-chip">+<?= count($items) - 3 ?> lagi</span>
              <?php endif; ?>
            </div>

            <div class="notif-toggle-icon" :style="open ? 'transform:rotate(180deg)' : ''">
              <i data-lucide="chevron-down"></i>
            </div>
          </div>

          <!-- Body -->
          <div class="notif-category-body" :class="{ 'open': open }">
            <?php foreach ($items as $notif): ?>
            <div class="notif-row severity-<?= $notif['severity'] ?>">
              <div class="notif-row-icon">
                <i data-lucide="<?= $notif['icon'] ?>"></i>
              </div>
              <div class="notif-row-body">
                <div class="notif-row-title">
                  <?= $notif['title'] ?>
                  <?php if (!empty($notif['kategori'])): ?>
                    <span class="badge" style="font-size:0.68rem; margin-left:0.5rem; padding:0.1rem 0.45rem; border-radius:4px; border:1px solid rgba(239,68,68,0.2); background:var(--danger-dim); color:var(--status-danger); font-weight:600;">
                      <?= esc($notif['kategori']) ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="notif-row-msg"><?= $notif['message'] ?></div>
                <div class="notif-row-meta">
                  <i data-lucide="clock"></i>
                  <?= $notif['friendly_time'] ?>
                </div>
              </div>
              <div class="notif-row-action">
                <a href="<?= $notif['link'] ?>" class="btn-notif-detail">
                  Detail <i data-lucide="arrow-right"></i>
                </a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  <?php else: ?>
    <!-- Empty State -->
    <div class="notif-empty">
      <div class="notif-empty-icon">
        <i data-lucide="bell-off"></i>
      </div>
      <h3 style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-bottom:0.5rem;">Semua Beres!</h3>
      <p style="font-size:0.875rem; color:var(--text-secondary); margin-bottom:1.5rem;">Tidak ada notifikasi aktif saat ini.</p>
      <a href="<?= base_url('/dashboard') ?>" class="btn btn-primary btn-sm">
        <i data-lucide="home"></i>
        Kembali ke Dashboard
      </a>
    </div>
  <?php endif; ?>

</div>
<?= $this->endSection() ?>
