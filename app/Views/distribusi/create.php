<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
// Variabel disuplai dari controller Distribusi::create()
// $batches, $sekolah, $armada
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Jadwalkan Distribusi</h1>
    <p class="page-subtitle">Atur pengiriman porsi makanan dari batch produksi ke sekolah penerima</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/distribusi') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div style="max-width: 720px;">
  <div class="card" x-data="distribusiForm()">
    <div class="card-header" style="margin-bottom:1.5rem; border-bottom:1px solid var(--border-subtle); padding-bottom:1rem;">
      <h3 class="card-title">Form Jadwal Pengiriman</h3>
      <p class="card-subtitle">Isi data di bawah ini untuk mencetak Surat Jalan pengiriman.</p>
    </div>

    <form action="<?= base_url('/distribusi/store') ?>" method="POST" @submit.prevent="submitForm">
      <?= csrf_field() ?>

      <!-- ── Select Batch Produksi ── -->
      <div class="form-group" style="margin-bottom: 1.25rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Batch Masakan / Menu <span style="color:var(--status-danger);">*</span></label>
        <select name="batch_id" class="form-select" x-model="batchId" required
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);">
          <option value="">-- Pilih Batch Produksi --</option>
          <?php foreach ($batches as $batch): ?>
          <option value="<?= $batch['id'] ?>"><?= esc($batch['nomor_batch']) ?> — <?= esc($batch['nama_menu']) ?> (<?= esc($batch['status']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- ── Select Sekolah (Auto-fill Portions) ── -->
      <div class="form-row" style="display:grid;grid-template-columns:1.5fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Sekolah Tujuan <span style="color:var(--status-danger);">*</span></label>
          <select name="sekolah_id" class="form-select" x-model="sekolahId" @change="onSekolahChange" required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);">
            <option value="">-- Pilih Sekolah Tujuan --</option>
            <?php foreach ($sekolah as $sek): ?>
            <option value="<?= $sek['id'] ?>" data-siswa="<?= $sek['jumlah_siswa'] ?>"><?= esc($sek['nama']) ?> (<?= $sek['jumlah_siswa'] ?> Siswa)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Porsi Dikirim <span style="color:var(--status-danger);">*</span></label>
          <input
            type="number"
            name="jumlah_porsi"
            class="form-control"
            placeholder="Jumlah porsi"
            x-model="porsi"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
        </div>
      </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Nama Pengirim <span style="color:var(--status-danger);">*</span></label>
          <input type="text" name="pengirim" class="form-control" required
            placeholder="Nama kurir pengantar"
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);">
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Kendaraan (Armada)</label>
          <select name="armada_id" class="form-select"
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);">
            <option value="">-- Tanpa Kendaraan Khusus --</option>
            <?php foreach ($armada as $k): ?>
            <option value="<?= $k['id'] ?>"><?= esc($k['no_polisi']) ?> – <?= esc($k['jenis']) ?> (<?= esc($k['pengemudi'] ?: 'belum ada pengemudi') ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

      <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:2rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Tanggal Pengiriman <span style="color:var(--status-danger);">*</span></label>
          <input type="date" name="tanggal_distribusi" class="form-control" value="<?= date('Y-m-d') ?>" required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);">
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Catatan</label>
          <input type="text" name="catatan" class="form-control" placeholder="Catatan tambahan..."
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);">
        </div>
      </div>

      <!-- Preview Summary Box -->
      <div style="background:var(--bg-card-hover); border:1px solid var(--border-subtle); border-radius:var(--border-radius); padding:1.25rem; margin-bottom:1.5rem;" x-show="batchNo && porsi > 0 && kurir">
        <h4 style="font-size:0.8rem;text-transform:uppercase;color:var(--text-muted);letter-spacing:0.08em;margin-bottom:0.5rem;">Konfirmasi Surat Jalan</h4>
        <div style="font-size:0.875rem;color:var(--text-primary);line-height:1.6;">
          • Pengiriman batch <strong x-text="batchNo"></strong> sebanyak <strong x-text="porsi"></strong> porsi.<br>
          • Kurir pengantar: <strong x-text="kurir"></strong> menggunakan <span x-text="kendaraan"></span>.
        </div>
      </div>

      <!-- ── Actions ── -->
      <div style="display:flex;justify-content:flex-end;gap:0.75rem;border-top:1px solid var(--border-subtle);padding-top:1.5rem;">
        <a href="<?= base_url('/distribusi') ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i data-lucide="truck" style="width:18px;height:18px;margin-right:6px;display:inline-block;vertical-align:middle;"></i>
          Jadwalkan & Cetak Surat Jalan
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function distribusiForm() {
    return {
      batchNo: '',
      sekolahId: '',
      porsi: '',
      kurir: '',
      kendaraan: '',

      onSekolahChange(e) {
        const select = e.target;
        const selectedOpt = select.options[select.selectedIndex];
        if (selectedOpt) {
          const siswa = selectedOpt.dataset.siswa;
          this.porsi = siswa || '';
        }
      },

      onCourierChange(e) {
        const select = e.target;
        const selectedOpt = select.options[select.selectedIndex];
        if (selectedOpt) {
          this.kendaraan = selectedOpt.dataset.vehicle || '';
        }
      },

      submitForm(e) {
        e.target.submit();
      }
    };
  }
</script>
<?= $this->endSection() ?>
