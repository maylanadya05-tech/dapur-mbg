<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$user = $user ?? [
  'id' => 2,
  'nama' => 'Lilik Herawati',
  'email' => 'lilik.h@dapurmbg.go.id',
  'no_telp' => '081398765432',
  'role' => 'pembelian',
  'status' => 'aktif',
  'avatar' => null
];

$initials = '';
$words = explode(' ', $user['nama']);
foreach($words as $w) {
  $initials .= strtoupper(substr($w, 0, 1));
}
$initials = substr($initials, 0, 2);
?>

<!-- ══ PAGE HEADER ══ -->
<div class="page-header">
  <div class="page-header-left">
    <h1 class="page-title">Edit Anggota Tim</h1>
    <p class="page-subtitle">Ubah informasi akun staf <?= esc($user['nama']) ?></p>
  </div>
  <div class="page-header-actions">
    <a href="<?= base_url('/users') ?>" class="btn btn-secondary btn-sm">
      <i data-lucide="arrow-left"></i>
      Kembali
    </a>
  </div>
</div>

<div style="max-width: 800px;">
  <div class="card" x-data="userForm()">
    <div class="card-header" style="margin-bottom:1.5rem; border-bottom:1px solid var(--border-subtle); padding-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
      <div>
        <h3 class="card-title">Profil Pengguna</h3>
        <p class="card-subtitle">Pembaruan hak akses (role) akan terupdate segera setelah disimpan.</p>
      </div>
      <div>
        <select name="status" class="form-select" x-model="status" style="background:var(--bg-input); border:1px solid var(--border-subtle); border-radius:var(--border-radius-sm); color:var(--text-primary); padding: 0.375rem 0.75rem; font-size: 0.875rem;">
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Non-Aktif</option>
        </select>
      </div>
    </div>

    <form action="<?= base_url('/users/update/' . $user['id']) ?>" method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <div style="display:grid;grid-template-columns:220px 1fr;gap:2rem;align-items:start;margin-bottom:2rem;">
        
        <!-- Left Column: Avatar Upload -->
        <div style="display:flex;flex-direction:column;align-items:center;text-align:center;gap:1rem;">
          <div style="font-size:0.8rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Foto Staf</div>
          
          <!-- Avatar Preview Circle -->
          <div style="position:relative;width:120px;height:120px;border-radius:50%;background:var(--bg-card-hover);border:2px dashed var(--border-subtle);display:flex;align-items:center;justify-content:center;overflow:hidden;">
            <template x-if="!avatarPreview">
              <?php if($user['avatar']): ?>
                <img src="<?= base_url('uploads/avatars/' . $user['avatar']) ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
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
          <div style="font-size:0.7rem;color:var(--text-muted);">Maks: 2MB (JPG, PNG)</div>
        </div>

        <!-- Right Column: Account Form Fields -->
        <div style="display:flex;flex-direction:column;gap:1.25rem;flex:1;">
          
          <div class="form-group">
            <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Nama Lengkap <span style="color:var(--status-danger);">*</span></label>
            <input
              type="text"
              name="nama"
              class="form-control"
              placeholder="Contoh: Lilik Herawati"
              x-model="nama"
              required
              style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
            >
          </div>

          <div class="form-row" style="display:grid;grid-template-columns:1.2fr 1fr;gap:1.25rem;">
            <div class="form-group">
              <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Alamat Email Staf <span style="color:var(--status-danger);">*</span></label>
              <input
                type="email"
                name="email"
                class="form-control"
                placeholder="staf@dapurmbg.go.id"
                x-model="email"
                required
                style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
              >
            </div>

            <div class="form-group">
              <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">No. Telepon / WA <span style="color:var(--status-danger);">*</span></label>
              <input
                type="text"
                name="no_telp"
                class="form-control"
                placeholder="0812XXXXXXXX"
                x-model="noTelp"
                required
                style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
              >
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Hak Akses (Role) <span style="color:var(--status-danger);">*</span></label>
            <select
              name="role"
              class="form-select"
              x-model="role"
              required
              style="width:100%;padding:0.625rem 0.875rem;background:var(--bg-input);border:1px solid var(--border-subtle);border-radius:var(--border-radius-sm);color:var(--text-primary);"
            >
              <option value="admin">Admin / Management</option>
              <option value="pembelian">Pembelian (Procurement / PO)</option>
              <option value="gudang">Gudang (Inventory)</option>
              <option value="produksi">Produksi (Chef / Dapur Utama)</option>
            </select>
          </div>

          <!-- Password block (Optional) -->
          <div style="border:1px dashed var(--border-subtle);padding:1rem;border-radius:var(--border-radius-sm);background:var(--bg-card-hover);margin-top:0.5rem;">
            <strong style="font-size:0.8rem;color:var(--emerald);display:block;margin-bottom:0.5rem;text-transform:uppercase;">Ganti Kata Sandi (Opsional)</strong>
            <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem;">Biarkan kosong jika tidak ingin mengubah kata sandi saat ini.</p>
            
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
              <div class="form-group">
                <label class="form-label" style="display:block;margin-bottom:0.5rem;font-weight:600;font-size:0.875rem;color:var(--text-secondary);">Kata Sandi Baru</label>
                <input
                  type="password"
                  name="password"
                  class="form-control"
                  placeholder="Min. 8 Karakter"
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
        <a href="<?= base_url('/users') ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary" :disabled="password !== passwordConfirm">
          <i data-lucide="save" style="width:18px;height:18px;margin-right:6px;display:inline-block;vertical-align:middle;"></i>
          Perbarui Akun
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function userForm() {
    return {
      nama: '<?= esc($user['nama']) ?>',
      email: '<?= esc($user['email']) ?>',
      noTelp: '<?= esc($user['no_telp']) ?>',
      role: '<?= esc($user['role']) ?>',
      status: '<?= esc($user['status']) ?>',
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
