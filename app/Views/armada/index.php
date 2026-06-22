<?php $this->extend('layouts/app'); ?>
<?php $this->section('content'); ?>

<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">
      <i data-lucide="truck" style="color:var(--color-primary);"></i>
      Manajemen Armada Kendaraan
    </h1>
    <p class="page-subtitle">Daftar kendaraan distribusi dan status penggunaannya</p>
  </div>
  <div class="page-header-right">
    <a href="<?= base_url('/armada/create') ?>" class="btn btn-primary" id="btnTambahArmada">
      <i data-lucide="plus"></i> Tambah Kendaraan
    </a>
  </div>
</div>

<!-- Stats -->
<?php
  $tersedia = count(array_filter($armada, fn($a) => $a['status'] === 'tersedia'));
  $digunakan = count(array_filter($armada, fn($a) => $a['status'] === 'digunakan'));
  $servis = count(array_filter($armada, fn($a) => $a['status'] === 'servis'));
?>
<div class="stats-grid stats-grid-3 mb-4">
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(140 60% 50%/.15);">
      <i data-lucide="check-circle-2" style="color:hsl(140 60% 50%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $tersedia ?></div>
      <div class="stat-label">Tersedia</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(var(--hsl-primary)/.15);">
      <i data-lucide="truck" style="color:var(--color-primary);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $digunakan ?></div>
      <div class="stat-label">Sedang Digunakan</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:hsl(25 90% 55%/.15);">
      <i data-lucide="wrench" style="color:hsl(25 90% 55%);"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $servis ?></div>
      <div class="stat-label">Servis/Tidak Aktif</div>
    </div>
  </div>
</div>

<!-- Table -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Daftar Armada</h3>
    <span class="badge badge-info"><?= count($armada) ?> kendaraan</span>
  </div>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>No. Polisi</th>
          <th>Jenis</th>
          <th>Kapasitas</th>
          <th>Pengemudi</th>
          <th>No. HP Pengemudi</th>
          <th>Pengiriman Hari Ini</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($armada)): ?>
        <tr>
          <td colspan="8" class="text-center" style="padding:2rem;opacity:.6;">
            <i data-lucide="truck" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
            Belum ada kendaraan terdaftar.
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($armada as $k): ?>
        <?php
          $statusMap = [
            'tersedia'     => ['label' => 'Tersedia',     'class' => 'success'],
            'digunakan'    => ['label' => 'Digunakan',    'class' => 'info'],
            'servis'       => ['label' => 'Servis',       'class' => 'warning'],
            'tidak_aktif'  => ['label' => 'Tidak Aktif',  'class' => 'danger'],
          ];
          $s = $statusMap[$k['status']] ?? ['label' => $k['status'], 'class' => 'secondary'];
        ?>
        <tr>
          <td><strong><?= esc($k['no_polisi']) ?></strong></td>
          <td><?= esc($k['jenis']) ?></td>
          <td>
            <span class="badge badge-secondary"><?= number_format($k['kapasitas_porsi']) ?> porsi</span>
          </td>
          <td><?= esc($k['pengemudi'] ?: '-') ?></td>
          <td>
            <?php if ($k['phone_pengemudi']): ?>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $k['phone_pengemudi']) ?>" target="_blank" class="btn btn-sm btn-outline" style="font-size:.8rem;">
              <i data-lucide="message-circle"></i> <?= esc($k['phone_pengemudi']) ?>
            </a>
            <?php else: ?>
            <span style="opacity:.5;">-</span>
            <?php endif; ?>
          </td>
          <td>
            <span class="badge badge-<?= $k['pengiriman_hari_ini'] > 0 ? 'primary' : 'secondary' ?>">
              <?= $k['pengiriman_hari_ini'] ?> pengiriman
            </span>
          </td>
          <td><span class="badge badge-<?= $s['class'] ?>"><?= $s['label'] ?></span></td>
          <td>
            <div class="action-buttons">
              <a href="<?= base_url('/armada/edit/' . $k['id']) ?>" class="btn btn-sm btn-outline" id="btnEditArmada<?= $k['id'] ?>">
                <i data-lucide="edit-2"></i>
              </a>
              <form method="POST" action="<?= base_url('/armada/delete/' . $k['id']) ?>" style="display:inline;"
                    onsubmit="return confirm('Hapus kendaraan <?= esc($k['no_polisi']) ?>?')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-danger" id="btnDeleteArmada<?= $k['id'] ?>">
                  <i data-lucide="trash-2"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php $this->endSection(); ?>
