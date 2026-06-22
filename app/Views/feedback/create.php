<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$sekolahList = $sekolahList ?? [
  ['id' => 1, 'nama' => 'SDN Merdeka 01'],
  ['id' => 2, 'nama' => 'SMP Negeri 1 Bogor'],
  ['id' => 3, 'nama' => 'SMA Negeri 2 Bogor'],
];

$batchList = $batchList ?? [
  ['batch_no' => 'BCH-2606-A', 'menu' => 'Nasi Kuning + Ayam Goreng + Sayur Asem'],
  ['batch_no' => 'BCH-2606-B', 'menu' => 'Nasi Putih + Daging Semur + Sop Sayur'],
];
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Kirim Feedback Sekolah</h1>
    <p class="page-subtitle">Laporkan penilaian rasa, porsi, kebersihan, dan ketepatan pengantaran makanan gratis</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/feedback') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div style="max-width: 720px;">
  <div class="card" x-data="feedbackForm()">
    <div class="card-header" style="margin-bottom:1.5rem; border-bottom:1px solid var(--border-subtle); padding-bottom:1rem;">
      <h3 class="card-title">Form Kuesioner Kepuasan Sekolah</h3>
      <p class="card-subtitle">Ulasan Anda sangat berharga bagi tim Dapur Utama untuk evaluasi menu harian.</p>
    </div>

    <form action="<?= base_url('/feedback/store') ?>" method="POST" @submit.prevent="submitForm">
      <?= csrf_field() ?>

      <!-- ── Sekolah & Batch ── -->
      <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">
        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Sekolah Anda <span style="color:var(--status-danger);">*</span></label>
          <select
            name="sekolah_id"
            class="form-select"
            x-model="sekolahId"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
            <option value="">-- Pilih Sekolah --</option>
            <?php foreach ($sekolahList as $sek): ?>
            <option value="<?= $sek['id'] ?>"><?= esc($sek['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Batch Masakan / Hari <span style="color:var(--status-danger);">*</span></label>
          <select
            name="batch_no"
            class="form-select"
            x-model="batchNo"
            required
            style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
          >
            <option value="">-- Pilih Batch Menu --</option>
            <?php foreach ($batchList as $b): ?>
            <option value="<?= esc($b['batch_no']) ?>"><?= esc($b['batch_no']) ?> — <?= esc($b['menu']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- ── Representative Name ── -->
      <div class="form-group" style="margin-bottom:2rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Nama Perwakilan Sekolah <span style="color:var(--status-danger);">*</span></label>
        <input
          type="text"
          name="nama_perwakilan"
          class="form-control"
          placeholder="Nama lengkap pengisi ulasan"
          x-model="namaPerwakilan"
          required
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
        >
      </div>

      <!-- ── Star Rating Widgets ── -->
      <h4 style="font-size:0.9rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--emerald);margin-bottom:1.25rem;border-bottom:1px dashed var(--border-subtle);padding-bottom:0.5rem;">Penilaian Berdasarkan Aspek</h4>

      <!-- Aspect Rating Loop -->
      <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:2rem;">
        
        <!-- Rasa -->
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-weight:600;font-size:0.875rem;color:var(--text-primary);">1. Citarasa & Kematangan Masakan</span>
          <div style="display:flex;gap:0.375rem;">
            <input type="hidden" name="rasa" :value="rasa">
            <template x-for="i in 5">
              <button type="button" @click="rasa = i" style="background:transparent;border:none;outline:none;color:var(--status-warning);">
                <i data-lucide="star" style="width:24px;height:24px;" :style="i <= rasa ? 'fill:var(--status-warning)' : 'opacity:0.3'"></i>
              </button>
            </template>
          </div>
        </div>

        <!-- Porsi -->
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-weight:600;font-size:0.875rem;color:var(--text-primary);">2. Ukuran Porsi (Kecukupan)</span>
          <div style="display:flex;gap:0.375rem;">
            <input type="hidden" name="porsi" :value="porsi">
            <template x-for="i in 5">
              <button type="button" @click="porsi = i" style="background:transparent;border:none;outline:none;color:var(--status-warning);">
                <i data-lucide="star" style="width:24px;height:24px;" :style="i <= porsi ? 'fill:var(--status-warning)' : 'opacity:0.3'"></i>
              </button>
            </template>
          </div>
        </div>

        <!-- Kebersihan -->
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-weight:600;font-size:0.875rem;color:var(--text-primary);">3. Kebersihan Wadah & Makanan</span>
          <div style="display:flex;gap:0.375rem;">
            <input type="hidden" name="kebersihan" :value="kebersihan">
            <template x-for="i in 5">
              <button type="button" @click="kebersihan = i" style="background:transparent;border:none;outline:none;color:var(--status-warning);">
                <i data-lucide="star" style="width:24px;height:24px;" :style="i <= kebersihan ? 'fill:var(--status-warning)' : 'opacity:0.3'"></i>
              </button>
            </template>
          </div>
        </div>

        <!-- Ketepatan Waktu -->
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-weight:600;font-size:0.875rem;color:var(--text-primary);">4. Ketepatan Waktu Pengantaran</span>
          <div style="display:flex;gap:0.375rem;">
            <input type="hidden" name="ketepatan_waktu" :value="ketepatanWaktu">
            <template x-for="i in 5">
              <button type="button" @click="ketepatanWaktu = i" style="background:transparent;border:none;outline:none;color:var(--status-warning);">
                <i data-lucide="star" style="width:24px;height:24px;" :style="i <= ketepatanWaktu ? 'fill:var(--status-warning)' : 'opacity:0.3'"></i>
              </button>
            </template>
          </div>
        </div>

        <!-- Keseluruhan -->
        <div style="display:flex;justify-content:space-between;align-items:center;background:var(--emerald-dim);padding:0.75rem 1rem;border-radius:var(--border-radius-sm);border:1px solid var(--border-accent);">
          <span style="font-weight:700;font-size:0.875rem;color:var(--text-primary);">5. Skor Kepuasan Keseluruhan</span>
          <div style="display:flex;gap:0.375rem;">
            <input type="hidden" name="rating" :value="rating">
            <template x-for="i in 5">
              <button type="button" @click="rating = i" style="background:transparent;border:none;outline:none;color:var(--status-warning);">
                <i data-lucide="star" style="width:24px;height:24px;" :style="i <= rating ? 'fill:var(--status-warning)' : 'opacity:0.3'"></i>
              </button>
            </template>
          </div>
        </div>

      </div>

      <!-- ── Comment Text ── -->
      <div class="form-group" style="margin-bottom:2rem;">
        <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Komentar / Saran Tambahan</label>
        <textarea
          name="komentar"
          class="form-control"
          placeholder="Berikan masukan berupa rasa sayur, kematangan lauk pauk, atau wadah box."
          rows="4"
          x-model="komentar"
          style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);font-family:inherit;resize:none;"
        ></textarea>
      </div>

      <!-- ── Actions ── -->
      <div style="display:flex;justify-content:flex-end;gap:0.75rem;border-top:1px solid var(--border-subtle);padding-top:1.5rem;">
        <a href="<?= base_url('/feedback') ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i data-lucide="send" style="width:18px;height:18px;margin-right:6px;display:inline-block;vertical-align:middle;"></i>
          Kirim Ulasan Anda
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function feedbackForm() {
    return {
      sekolahId: '',
      batchNo: '',
      namaPerwakilan: '',
      rasa: 5,
      porsi: 5,
      kebersihan: 5,
      ketepatanWaktu: 5,
      rating: 5,
      komentar: '',

      submitForm(e) {
        e.target.submit();
      }
    };
  }
</script>
<?= $this->endSection() ?>
