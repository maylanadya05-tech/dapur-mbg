<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$initials = '';
$words = explode(' ', $user['name'] ?? 'Pengguna');
foreach($words as $w) {
    if (!empty($w)) {
        $initials .= strtoupper(substr($w, 0, 1));
    }
}
$initials = substr($initials, 0, 2);
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Profil Saya</h1>
    <p class="page-subtitle">Kelola informasi pribadi dan pengaturan kata sandi akun Anda</p>
  </div>
</div>

<div style="max-width: 800px;">
  <!-- Alert Error List -->
  <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-error" role="alert" style="margin-bottom: 1.5rem;">
      <ul style="margin: 0; padding-left: 1.25rem;">
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
          <li><?= esc($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card" x-data="profileForm()">
    <div class="card-header" style="margin-bottom:1.5rem; border-bottom:1px solid var(--border-subtle); padding-bottom:1rem;">
      <h3 class="card-title">Informasi Pribadi</h3>
      <p class="card-subtitle">Perubahan email atau password akan membutuhkan login ulang dengan kredensial baru.</p>
    </div>

    <form action="<?= base_url('/profil/update') ?>" method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <div style="display:grid;grid-template-columns:220px 1fr;gap:2rem;align-items:start;margin-bottom:2rem;">
        
        <!-- Left Column: Avatar Upload -->
        <div style="display:flex;flex-direction:column;align-items:center;text-align:center;gap:1rem;">
          <div style="font-size:0.8rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Foto Profil</div>
          
          <!-- Avatar Preview Circle -->
          <div style="position:relative;width:120px;height:120px;border-radius:50%;background:var(--bg-card-hover);border:2px dashed var(--border-subtle);display:flex;align-items:center;justify-content:center;overflow:hidden;box-shadow: var(--shadow-md);">
            <template x-if="!avatarPreview">
              <?php if($user['avatar']): ?>
                <img src="<?= base_url($user['avatar']) ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                <div style="width:100%;height:100%;background:linear-gradient(135deg, var(--emerald), hsl(180,60%,35%));display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:white;">
                  <?= $initials ?>
                </div>
              <?php endif; ?>
            </template>
            <template x-if="avatarPreview">
              <img :src="avatarPreview" alt="Preview" style="width:100%;height:100%;object-fit:cover;">
            </template>
          </div>

          <!-- Upload Trigger Button -->
          <label class="btn btn-secondary btn-sm" style="cursor:pointer;position:relative;">
            <i data-lucide="upload" style="width:14px;height:14px;margin-right:6px;display:inline-block;vertical-align:middle;"></i>
            Ganti Foto
            <input type="file" name="avatar" @change="onAvatarChange" accept="image/*" style="opacity:0;position:absolute;left:0;top:0;width:100%;height:100%;cursor:pointer;">
          </label>
          <div style="font-size:0.7rem;color:var(--text-muted);">Maks: 2MB (JPG, PNG, WEBP)</div>
        </div>

        <!-- Right Column: Account Form Fields -->
        <div style="display:flex;flex-direction:column;gap:1.25rem;flex:1;">
          
          <div class="form-group">
            <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Nama Lengkap <span style="color:var(--status-danger);">*</span></label>
            <input
              type="text"
              name="name"
              class="form-control"
              placeholder="Contoh: Rian Hidayat"
              x-model="name"
              required
              style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
            >
          </div>

          <div class="form-row" style="display:grid;grid-template-columns:1.2fr 1fr;gap:1.25rem;">
            <div class="form-group">
              <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Alamat Email <span style="color:var(--status-danger);">*</span></label>
              <input
                type="email"
                name="email"
                class="form-control"
                placeholder="email@dapurmbg.id"
                x-model="email"
                required
                style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
              >
            </div>

            <div class="form-group">
              <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">No. Telepon / WA</label>
              <input
                type="text"
                name="phone"
                class="form-control"
                placeholder="0812XXXXXXXX"
                x-model="phone"
                style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
              >
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-muted);">Peran Sistem (Role)</label>
            <input
              type="text"
              class="form-control"
              value="<?= match($user['role']) {
                  'admin'      => '⚡ Administrator',
                  'gudang'     => '📦 Gudang',
                  'produksi'   => '👨‍🍳 Produksi',
                  'pembelian'  => '💰 Pembelian',
                  default      => '👁️ Viewer',
              } ?>"
              readonly
              style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-card-hover);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-muted);cursor:not-allowed;"
            >
            <span style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem;display:block;">Hubungi Administrator utama untuk mengubah hak akses Anda.</span>
          </div>

          <!-- Password block (Optional) -->
          <div style="border:1px dashed var(--border-subtle);padding:1.25rem;border-radius:var(--border-radius-sm);background:var(--bg-card-hover);margin-top:0.5rem;">
            <strong style="font-size:0.8rem;color:var(--emerald);display:block;margin-bottom:0.5rem;text-transform:uppercase;">Ganti Kata Sandi (Opsional)</strong>
            <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem;">Biarkan kolom kosong jika Anda tidak berniat mengubah kata sandi.</p>
            
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
              <div class="form-group">
                <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Kata Sandi Baru</label>
                <input
                  type="password"
                  name="password"
                  class="form-control"
                  placeholder="Min. 6 Karakter"
                  x-model="password"
                  style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
                >
              </div>

              <div class="form-group">
                <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Konfirmasi Sandi Baru</label>
                <input
                  type="password"
                  name="password_confirm"
                  class="form-control"
                  placeholder="Ulangi Sandi Baru"
                  x-model="passwordConfirm"
                  style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
                >
              </div>
            </div>
            <div style="font-size:0.8rem;color:var(--status-danger);margin-top:0.5rem;" x-show="password && passwordConfirm && password !== passwordConfirm">
              ⚠ Kata sandi konfirmasi tidak cocok.
            </div>
          </div>

        </div>

      </div>

      <!-- ── Actions ── -->
      <div style="display:flex;justify-content:flex-end;gap:0.75rem;border-top:1px solid var(--border-subtle);padding-top:1.5rem;">
        <a href="<?= base_url('/dashboard') ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary" :disabled="password !== passwordConfirm">
          <i data-lucide="save" style="width:18px;height:18px;margin-right:6px;display:inline-block;vertical-align:middle;"></i>
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function profileForm() {
    return {
      name: '<?= esc($user['name'], 'js') ?>',
      email: '<?= esc($user['email'], 'js') ?>',
      phone: '<?= esc($user['phone'] ?? '', 'js') ?>',
      password: '',
      passwordConfirm: '',
      avatarPreview: null,

      onAvatarChange(e) {
        const file = e.target.files[0];
        if (file) {
          this.avatarPreview = URL.createObjectURL(file);
        } else {
          this.avatarPreview = null;
        }
      }
    };
  }
</script>
<?= $this->endSection() ?>
