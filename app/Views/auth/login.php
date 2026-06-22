<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-wrapper">

  <!-- ╔══════════════════════════════════════════════════════════╗
       ║  LEFT PANEL — Brand & Illustration                     ║
       ╚══════════════════════════════════════════════════════════╝ -->
  <div class="auth-left">

    <!-- Decorative floating dots -->
    <div aria-hidden="true" style="position:absolute;top:10%;left:8%;width:6px;height:6px;border-radius:50%;background:var(--emerald);opacity:0.4;animation:float-dot 4s ease-in-out infinite;"></div>
    <div aria-hidden="true" style="position:absolute;top:35%;right:12%;width:4px;height:4px;border-radius:50%;background:var(--emerald);opacity:0.3;animation:float-dot 5s ease-in-out infinite 1s;"></div>
    <div aria-hidden="true" style="position:absolute;bottom:25%;left:15%;width:8px;height:8px;border-radius:50%;background:var(--emerald);opacity:0.25;animation:float-dot 6s ease-in-out infinite 0.5s;"></div>

    <div class="auth-brand">

      <!-- Food Bowl Illustration SVG -->
      <div class="auth-illustration" aria-hidden="true">
        <svg width="160" height="160" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <radialGradient id="bowlGrad" cx="50%" cy="50%" r="50%">
              <stop offset="0%" stop-color="hsl(38,92%,50%)" stop-opacity="0.25"/>
              <stop offset="100%" stop-color="hsl(38,92%,25%)" stop-opacity="0.05"/>
            </radialGradient>
            <radialGradient id="plateGrad" cx="50%" cy="40%" r="60%">
              <stop offset="0%" stop-color="hsl(218,30%,20%)"/>
              <stop offset="100%" stop-color="hsl(218,30%,13%)"/>
            </radialGradient>
          </defs>

          <!-- Background circle glow -->
          <circle cx="80" cy="80" r="76" fill="url(#bowlGrad)" stroke="hsl(38,92%,50%)" stroke-width="1" stroke-opacity="0.3"/>

          <!-- Plate / Bowl base -->
          <ellipse cx="80" cy="95" rx="52" ry="12" fill="hsl(218,30%,10%)" opacity="0.6"/>
          <ellipse cx="80" cy="90" rx="56" ry="22" fill="url(#plateGrad)" stroke="hsl(215,28%,22%)" stroke-width="1.5"/>

          <!-- Rice mound -->
          <ellipse cx="80" cy="75" rx="38" ry="18" fill="hsl(43,60%,85%)" opacity="0.9"/>
          <ellipse cx="80" cy="68" rx="30" ry="14" fill="hsl(43,60%,90%)"/>
          <ellipse cx="80" cy="63" rx="20" ry="9" fill="hsl(43,60%,93%)"/>

          <!-- Vegetable greens -->
          <ellipse cx="58" cy="70" rx="10" ry="6" fill="hsl(120,60%,38%)" transform="rotate(-20 58 70)"/>
          <ellipse cx="55" cy="67" rx="8" ry="5" fill="hsl(120,60%,48%)" transform="rotate(-20 55 67)"/>

          <!-- Protein (chicken) -->
          <ellipse cx="103" cy="72" rx="12" ry="7" fill="hsl(25,70%,55%)" transform="rotate(15 103 72)"/>
          <ellipse cx="104" cy="70" rx="10" ry="6" fill="hsl(25,70%,62%)" transform="rotate(15 104 70)"/>

          <!-- Steam lines -->
          <path d="M70 52 C70 48 72 46 70 42" stroke="hsl(38,92%,75%)" stroke-width="2" stroke-linecap="round" opacity="0.5">
            <animate attributeName="opacity" values="0.5;0.1;0.5" dur="2s" repeatCount="indefinite"/>
          </path>
          <path d="M80 48 C80 43 82 41 80 36" stroke="hsl(38,92%,75%)" stroke-width="2" stroke-linecap="round" opacity="0.4">
            <animate attributeName="opacity" values="0.4;0.1;0.4" dur="2.5s" repeatCount="indefinite"/>
          </path>
          <path d="M90 52 C90 47 92 45 90 40" stroke="hsl(38,92%,75%)" stroke-width="2" stroke-linecap="round" opacity="0.5">
            <animate attributeName="opacity" values="0.5;0.1;0.5" dur="2.2s" repeatCount="indefinite"/>
          </path>

          <!-- Fork & Spoon -->
          <line x1="32" y1="55" x2="32" y2="100" stroke="hsl(215,16%,50%)" stroke-width="2.5" stroke-linecap="round"/>
          <path d="M29 60 C29 56 35 56 35 60 L35 68 L32 68 L32 60" fill="none" stroke="hsl(215,16%,50%)" stroke-width="1.5"/>

          <path d="M128 55 C128 62 124 66 126 70 L126 100" stroke="hsl(215,16%,50%)" stroke-width="2.5" stroke-linecap="round" fill="none"/>
          <circle cx="128" cy="60" r="6" fill="none" stroke="hsl(215,16%,50%)" stroke-width="1.5"/>

          <!-- Leaf accent top right -->
          <path d="M118 28 C118 20 130 18 130 28 C130 36 118 38 118 28Z" fill="hsl(38,92%,50%)" opacity="0.7"/>
          <line x1="124" y1="28" x2="124" y2="42" stroke="hsl(38,92%,50%)" stroke-width="1.5" opacity="0.7"/>
        </svg>
      </div>

      <!-- Brand Logo Emblem -->
      <div class="auth-brand-logo" aria-label="Logo Dapur MBG">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
      </div>

      <h1>Dapur MBG</h1>
      <h2>Sistem Manajemen SPPG</h2>
      <p>Makan Bergizi, Indonesia Maju 🌿</p>

      <!-- Quick Stats -->
      <div class="auth-stats">
        <div class="auth-stat-card">
          <span class="stat-val">4,200+</span>
          <span class="stat-lbl">Porsi / Hari</span>
        </div>
        <div class="auth-stat-card">
          <span class="stat-val">18</span>
          <span class="stat-lbl">Sekolah Sasaran</span>
        </div>
      </div>

    </div><!-- /.auth-brand -->

  </div><!-- /.auth-left -->

  <!-- ╔══════════════════════════════════════════════════════════╗
       ║  RIGHT PANEL — Login Form                              ║
       ╚══════════════════════════════════════════════════════════╝ -->
  <div class="auth-right">

    <div class="auth-form-box" x-data="loginForm()">

      <!-- Header -->
      <div style="margin-bottom:2.25rem;">
        <h2 class="auth-form-title">Selamat Datang</h2>
        <p class="auth-form-subtitle">Masuk ke sistem manajemen Dapur MBG SPPG</p>
      </div>

      <!-- Error Message -->
      <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-error" style="margin-bottom:1.5rem;">
        <span><?= esc(session()->getFlashdata('error')) ?></span>
        <button onclick="this.parentElement.remove()">&times;</button>
      </div>
      <?php endif; ?>

      <?php if (isset($validation)): ?>
      <div class="alert alert-error" style="margin-bottom:1.5rem;">
        <span><?= $validation->listErrors() ?></span>
        <button onclick="this.parentElement.remove()">&times;</button>
      </div>
      <?php endif; ?>

      <!-- Login Form -->
      <form
        action="<?= base_url('/auth/login') ?>"
        method="POST"
        @submit="handleSubmit"
        novalidate
      >
        <?= csrf_field() ?>

        <!-- Email Field -->
        <div class="form-group">
          <label class="form-label" for="email">
            Email <span class="required">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-icon">
              <i data-lucide="mail"></i>
            </span>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control"
              :class="{ 'is-invalid': errors.email }"
              placeholder="nama@dapurmbg.id"
              value="<?= old('email') ?>"
              autocomplete="email"
              required
              x-model="form.email"
              @input="clearError('email')"
            >
          </div>
          <div class="form-error" x-show="errors.email" x-text="errors.email"></div>
        </div>

        <!-- Password Field -->
        <div class="form-group" style="margin-bottom:1.5rem;">
          <label class="form-label" for="password">
            Password <span class="required">*</span>
          </label>
          <div class="input-group" x-data="{ showPass: false }">
            <span class="input-group-icon">
              <i data-lucide="lock"></i>
            </span>
            <input
              :type="showPass ? 'text' : 'password'"
              id="password"
              name="password"
              class="form-control"
              :class="{ 'is-invalid': errors.password }"
              placeholder="Masukkan password"
              autocomplete="current-password"
              required
              x-model="form.password"
              @input="clearError('password')"
              style="padding-right:3rem;"
            >
            <button
              type="button"
              @click="showPass = !showPass"
              style="position:absolute;right:0.75rem;background:transparent;border:none;color:var(--text-muted);display:flex;align-items:center;cursor:pointer;padding:0.25rem;transition:color 0.15s;"
              :style="showPass ? 'color:var(--emerald)' : ''"
              aria-label="Toggle password visibility"
            >
              <i :data-lucide="showPass ? 'eye-off' : 'eye'" style="width:16px;height:16px;"></i>
            </button>
          </div>
          <div class="form-error" x-show="errors.password" x-text="errors.password"></div>
        </div>

        <!-- Remember Me + Forgot -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem;">
          <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.8rem;color:var(--text-secondary);">
            <input
              type="checkbox"
              name="remember"
              style="accent-color:var(--emerald);width:15px;height:15px;"
            >
            Ingat saya
          </label>
          <a href="<?= base_url('/auth/forgot') ?>"
             style="font-size:0.8rem;color:var(--emerald);font-weight:600;">
            Lupa password?
          </a>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="btn btn-primary btn-lg w-full"
          :class="{ 'loading': isLoading }"
          :disabled="isLoading"
          style="width:100%;justify-content:center;"
        >
          <span x-show="!isLoading">
            <i data-lucide="log-in" style="width:18px;height:18px;"></i>
            Masuk ke Sistem
          </span>
          <span x-show="isLoading">Memproses...</span>
        </button>

      </form>

      <!-- Footer -->
      <div style="margin-top:2.5rem;padding-top:1.5rem;border-top:1px solid var(--border-subtle);text-align:center;">
        <p style="font-size:0.75rem;color:var(--text-muted);line-height:1.7;">
          &copy; <?= date('Y') ?> Dapur MBG · Sistem Informasi SPPG<br>
          <span style="color:var(--text-muted);opacity:0.6;">Powered by Badan Gizi Nasional</span>
        </p>
      </div>

    </div><!-- /.auth-form-box -->

  </div><!-- /.auth-right -->

</div><!-- /.auth-wrapper -->

<style>
  @keyframes float-dot {
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.4; }
    50% { transform: translateY(-12px) scale(1.2); opacity: 0.6; }
  }
</style>

<?= $this->section('scripts') ?>
<script>
  function loginForm() {
    return {
      form: { email: '', password: '' },
      errors: {},
      isLoading: false,

      handleSubmit(e) {
        this.errors = {};
        let valid = true;

        if (!this.form.email || !this.form.email.includes('@')) {
          this.errors.email = 'Email tidak valid.';
          valid = false;
        }

        if (!this.form.password || this.form.password.length < 6) {
          this.errors.password = 'Password minimal 6 karakter.';
          valid = false;
        }

        if (!valid) {
          e.preventDefault();
          return;
        }

        this.isLoading = true;
        // Let form submit naturally
      },

      clearError(field) {
        delete this.errors[field];
      }
    };
  }

  // Reinitialize Lucide after Alpine renders
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
  });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
