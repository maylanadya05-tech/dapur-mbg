<?php $this->extend('layouts/app'); ?>
<?php $this->section('content'); ?>

<div class="page-header">
  <div class="page-header-left">
    <a href="<?= base_url('/armada') ?>" class="btn btn-outline btn-sm" id="btnBackArmada">
      <i data-lucide="arrow-left"></i> Kembali
    </a>
    <h1 class="page-title mt-2">Tambah Kendaraan Baru</h1>
  </div>
</div>

<div class="card" style="max-width:700px;margin:0 auto;">
  <div class="card-header">
    <h3 class="card-title"><i data-lucide="truck"></i> Data Kendaraan</h3>
  </div>
  <div class="card-body">
    <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-error">
      <?php foreach ((array)session()->getFlashdata('errors') as $e): ?>
        <div><?= esc($e) ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('/armada/store') ?>">
      <?= csrf_field() ?>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label required">No. Polisi</label>
          <input type="text" name="no_polisi" class="form-control" id="inputNoPol"
                 value="<?= esc(old('no_polisi')) ?>"
                 placeholder="Contoh: B 1234 CD" style="text-transform:uppercase;" required>
        </div>
        <div class="form-group">
          <label class="form-label required">Jenis Kendaraan</label>
          <select name="jenis" class="form-control form-select" id="inputJenis" required>
            <?php foreach (['Motor','Mobil Pick-Up','Mobil Box','Van','Truk'] as $j): ?>
            <option value="<?= $j ?>" <?= old('jenis') === $j ? 'selected' : '' ?>><?= $j ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label required">Kapasitas (porsi)</label>
          <input type="number" name="kapasitas_porsi" class="form-control" id="inputKapasitas"
                 value="<?= esc(old('kapasitas_porsi', 0)) ?>"
                 min="0" required>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control form-select" id="inputStatusArmada">
            <option value="tersedia" <?= old('status') === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
            <option value="digunakan" <?= old('status') === 'digunakan' ? 'selected' : '' ?>>Digunakan</option>
            <option value="servis" <?= old('status') === 'servis' ? 'selected' : '' ?>>Servis</option>
            <option value="tidak_aktif" <?= old('status') === 'tidak_aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nama Pengemudi</label>
          <input type="text" name="pengemudi" class="form-control" id="inputPengemudi"
                 value="<?= esc(old('pengemudi')) ?>"
                 placeholder="Nama pengemudi">
        </div>
        <div class="form-group">
          <label class="form-label">No. HP Pengemudi</label>
          <input type="text" name="phone_pengemudi" class="form-control" id="inputPhonePengemudi"
                 value="<?= esc(old('phone_pengemudi')) ?>"
                 placeholder="628xxxxxxxxxx">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control" id="inputKeteranganArmada" rows="3"
                  placeholder="Catatan tambahan tentang kendaraan ini..."><?= esc(old('keterangan')) ?></textarea>
      </div>

      <div style="display:flex;gap:1rem;justify-content:flex-end;">
        <a href="<?= base_url('/armada') ?>" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary" id="btnSimpanArmada">
          <i data-lucide="save"></i> Simpan Kendaraan
        </button>
      </div>
    </form>
  </div>
</div>

<?php $this->endSection(); ?>
