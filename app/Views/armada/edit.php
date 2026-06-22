<?php $this->extend('layouts/app'); ?>
<?php $this->section('content'); ?>

<div class="page-header">
  <div class="page-header-left">
    <a href="<?= base_url('/armada') ?>" class="btn btn-outline btn-sm" id="btnBackArmadaEdit">
      <i data-lucide="arrow-left"></i> Kembali
    </a>
    <h1 class="page-title mt-2">Edit Kendaraan – <?= esc($kendaraan['no_polisi']) ?></h1>
  </div>
</div>

<div class="card" style="max-width:700px;margin:0 auto;">
  <div class="card-header">
    <h3 class="card-title"><i data-lucide="truck"></i> Edit Data Kendaraan</h3>
  </div>
  <div class="card-body">
    <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-error">
      <?php foreach ((array)session()->getFlashdata('errors') as $e): ?>
        <div><?= esc($e) ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('/armada/update/' . $kendaraan['id']) ?>">
      <?= csrf_field() ?>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label required">No. Polisi</label>
          <input type="text" name="no_polisi" class="form-control" id="editNoPol"
                 value="<?= esc(old('no_polisi', $kendaraan['no_polisi'])) ?>"
                 style="text-transform:uppercase;" required>
        </div>
        <div class="form-group">
          <label class="form-label required">Jenis Kendaraan</label>
          <select name="jenis" class="form-control form-select" id="editJenis" required>
            <?php foreach (['Motor','Mobil Pick-Up','Mobil Box','Van','Truk'] as $j): ?>
            <option value="<?= $j ?>" <?= old('jenis', $kendaraan['jenis']) === $j ? 'selected' : '' ?>><?= $j ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label required">Kapasitas (porsi)</label>
          <input type="number" name="kapasitas_porsi" class="form-control" id="editKapasitas"
                 value="<?= esc(old('kapasitas_porsi', $kendaraan['kapasitas_porsi'])) ?>"
                 min="0" required>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control form-select" id="editStatusArmada">
            <?php foreach (['tersedia','digunakan','servis','tidak_aktif'] as $st): ?>
            <option value="<?= $st ?>" <?= old('status', $kendaraan['status']) === $st ? 'selected' : '' ?>>
              <?= ucfirst(str_replace('_', ' ', $st)) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nama Pengemudi</label>
          <input type="text" name="pengemudi" class="form-control" id="editPengemudi"
                 value="<?= esc(old('pengemudi', $kendaraan['pengemudi'])) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">No. HP Pengemudi</label>
          <input type="text" name="phone_pengemudi" class="form-control" id="editPhonePengemudi"
                 value="<?= esc(old('phone_pengemudi', $kendaraan['phone_pengemudi'])) ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control" id="editKeteranganArmada" rows="3"><?= esc(old('keterangan', $kendaraan['keterangan'])) ?></textarea>
      </div>

      <div style="display:flex;gap:1rem;justify-content:flex-end;">
        <a href="<?= base_url('/armada') ?>" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary" id="btnUpdateArmada">
          <i data-lucide="save"></i> Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

<?php $this->endSection(); ?>
