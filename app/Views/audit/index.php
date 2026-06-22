<?php $this->extend('layouts/app'); ?>
<?php $this->section('content'); ?>

<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">
      <i data-lucide="shield-check" style="color:var(--color-primary);"></i>
      Audit Log Aktivitas
    </h1>
    <p class="page-subtitle">Rekam jejak semua aksi yang dilakukan pengguna di sistem</p>
  </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="<?= base_url('/audit-log') ?>" class="filter-form">
      <div class="filter-grid">
        <div class="form-group">
          <label class="form-label">Modul</label>
          <select name="module" class="form-control form-select" id="filterModule">
            <option value="">Semua Modul</option>
            <?php foreach ($modules as $mod): ?>
              <option value="<?= esc($mod) ?>" <?= ($filters['module'] === $mod) ? 'selected' : '' ?>><?= esc($mod) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Aksi</label>
          <select name="action" class="form-control form-select" id="filterAction">
            <option value="">Semua Aksi</option>
            <?php foreach ($actions as $act): ?>
              <option value="<?= $act ?>" <?= ($filters['action'] === $act) ? 'selected' : '' ?>><?= $act ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Pengguna</label>
          <select name="user_id" class="form-control form-select" id="filterUser">
            <option value="">Semua Pengguna</option>
            <?php foreach ($users as $u): ?>
              <option value="<?= $u['user_id'] ?>" <?= ((string)$filters['userId'] === (string)$u['user_id']) ? 'selected' : '' ?>><?= esc($u['user_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Dari Tanggal</label>
          <input type="date" name="start_date" class="form-control" value="<?= $filters['startDate'] ?>" id="filterStartDate">
        </div>
        <div class="form-group">
          <label class="form-label">Sampai Tanggal</label>
          <input type="date" name="end_date" class="form-control" value="<?= $filters['endDate'] ?>" id="filterEndDate">
        </div>
        <div class="form-group" style="display:flex;align-items:flex-end;gap:.5rem;">
          <button type="submit" class="btn btn-primary" id="btnFilterAudit">
            <i data-lucide="search"></i> Filter
          </button>
          <a href="<?= base_url('/audit-log') ?>" class="btn btn-outline" id="btnResetAudit">Reset</a>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Stats Row -->
<div class="stats-grid stats-grid-3 mb-4">
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(var(--hsl-primary)/.15);">
      <i data-lucide="list" style="color:var(--color-primary);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($totalRows) ?></div>
      <div class="stat-label">Total Log (Filter Aktif)</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(140 60% 50%/.15);">
      <i data-lucide="check-circle" style="color:hsl(140 60% 50%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $page ?> / <?= $totalPages ?: 1 ?></div>
      <div class="stat-label">Halaman</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(45 90% 55%/.15);">
      <i data-lucide="users" style="color:hsl(45 90% 55%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= count($users) ?></div>
      <div class="stat-label">Pengguna Aktif</div>
    </div>
  </div>
</div>

<!-- Table -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Daftar Log Aktivitas</h3>
    <span class="badge badge-info"><?= number_format($totalRows) ?> entri</span>
  </div>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>Waktu</th>
          <th>Pengguna</th>
          <th>Role</th>
          <th>Aksi</th>
          <th>Modul</th>
          <th>Deskripsi</th>
          <th>IP Address</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($logs)): ?>
        <tr>
          <td colspan="7" class="text-center" style="padding:2rem;opacity:.6;">
            <i data-lucide="inbox" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
            Tidak ada log aktivitas untuk filter ini.
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($logs as $log): ?>
        <?php
          $actionColors = [
            'CREATE'  => 'success',
            'UPDATE'  => 'warning',
            'DELETE'  => 'danger',
            'LOGIN'   => 'info',
            'LOGOUT'  => 'secondary',
            'APPROVE' => 'success',
            'REJECT'  => 'danger',
            'EXPORT'  => 'info',
          ];
          $badgeClass = $actionColors[$log['action']] ?? 'secondary';
        ?>
        <tr>
          <td style="white-space:nowrap;font-size:.85rem;">
            <?= date('d/m/Y', strtotime($log['created_at'])) ?><br>
            <small style="opacity:.7;"><?= date('H:i:s', strtotime($log['created_at'])) ?></small>
          </td>
          <td>
            <strong><?= esc($log['user_name'] ?? 'System') ?></strong>
          </td>
          <td>
            <span class="badge badge-secondary" style="font-size:.75rem;"><?= esc($log['user_role'] ?? '-') ?></span>
          </td>
          <td>
            <span class="badge badge-<?= $badgeClass ?>"><?= esc($log['action']) ?></span>
          </td>
          <td><?= esc($log['module'] ?? '-') ?></td>
          <td style="max-width:300px;font-size:.875rem;"><?= esc($log['description'] ?? '-') ?></td>
          <td><code style="font-size:.8rem;"><?= esc($log['ip_address'] ?? '-') ?></code></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="card-footer" style="display:flex;justify-content:center;gap:.5rem;flex-wrap:wrap;">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <?php
      $params = array_merge($filters, ['page' => $p]);
      // Remove null values
      $params = array_filter($params, fn($v) => $v !== null && $v !== '');
      $query = http_build_query($params);
    ?>
    <a href="?<?= $query ?>"
       class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-outline' ?>"
       id="auditPage<?= $p ?>">
      <?= $p ?>
    </a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<?php $this->endSection(); ?>
