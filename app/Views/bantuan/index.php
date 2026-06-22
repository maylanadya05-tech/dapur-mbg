<?= $this->extend('layouts/app') ?>

<?= $this->section('styles') ?>
<style>
  .help-grid {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 1.5rem;
    align-items: start;
    margin-top: 1rem;
  }

  /* Left Tab Navigation */
  .help-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--border-radius);
    padding: 0.75rem;
  }

  .help-nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-secondary);
    border-radius: var(--border-radius-sm);
    background: transparent;
    border: none;
    width: 100%;
    text-align: left;
    transition: var(--transition-fast);
    cursor: pointer;
  }

  .help-nav-item:hover {
    color: var(--text-primary);
    background: var(--bg-card-hover);
  }

  .help-nav-item.active {
    color: var(--emerald);
    background: var(--emerald-dim);
    border-left: 3px solid var(--emerald);
  }

  .help-nav-item i {
    width: 16px;
    height: 16px;
  }

  /* Right Content Card */
  .help-content {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: var(--border-radius-lg);
    padding: 2rem;
    min-height: 500px;
    box-shadow: var(--shadow-sm);
  }

  .help-section-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.625rem;
    border-bottom: 1px solid var(--border-subtle);
    padding-bottom: 0.75rem;
  }

  .help-section-title i {
    color: var(--emerald);
  }

  .help-paragraph {
    font-size: 0.9rem;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 1.25rem;
  }

  .help-guide-list {
    margin-bottom: 1.5rem;
    padding-left: 1.25rem;
  }

  .help-guide-list li {
    font-size: 0.9rem;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 0.5rem;
    position: relative;
    list-style-type: decimal;
  }

  /* Step Workflow */
  .flow-steps {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin: 1.5rem 0;
  }

  .flow-step {
    display: flex;
    gap: 1rem;
    background: var(--bg-input);
    border: 1px solid var(--border-subtle);
    border-radius: var(--border-radius-sm);
    padding: 1rem;
    align-items: flex-start;
  }

  .flow-step-num {
    background: var(--emerald);
    color: var(--bg-primary);
    width: 24px;
    height: 24px;
    border-radius: var(--border-radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.8rem;
    flex-shrink: 0;
    margin-top: 0.15rem;
  }

  .flow-step-content {
    flex: 1;
  }

  .flow-step-title {
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
  }

  .flow-step-desc {
    font-size: 0.825rem;
    color: var(--text-muted);
    line-height: 1.4;
  }

  /* Code Block Styling */
  .code-block {
    background: var(--bg-primary);
    border: 1px solid var(--border-medium);
    border-radius: var(--border-radius-sm);
    padding: 1rem;
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.85rem;
    color: var(--text-primary);
    overflow-x: auto;
    margin: 1rem 0;
    line-height: 1.4;
  }

  /* Highlights */
  .help-alert {
    background: var(--warning-dim);
    border-left: 4px solid var(--status-warning);
    color: var(--text-secondary);
    padding: 1rem;
    border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0;
    font-size: 0.85rem;
    margin: 1.25rem 0;
    line-height: 1.5;
  }

  .help-alert.alert-info {
    background: var(--info-dim);
    border-left-color: var(--status-info);
  }

  @media (max-width: 768px) {
    .help-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $lang = session()->get('pref_lang') ?? 'id'; ?>

<div class="notif-container" x-data="{ activeTab: 'overview' }">

  <!-- ══ PAGE HEADER ══ -->
  <div class="page-header" style="margin-bottom: 1rem;">
    <div class="page-header-left">
      <h1 class="page-title"><?= lang('App.panduan_penggunaan') ?></h1>
      <p class="page-subtitle"><?= lang('App.bantuan_deskripsi') ?></p>
    </div>
  </div>

  <!-- ══ HELP GRID ══ -->
  <div class="help-grid">
    
    <!-- Sidebar Navigation Tabs -->
    <nav class="help-nav" aria-label="Help Navigation">
      <button class="help-nav-item" :class="{ 'active': activeTab === 'overview' }" @click="activeTab = 'overview'">
        <i data-lucide="book-open"></i>
        <span><?= $lang === 'en' ? 'Overview' : 'Ringkasan Sistem' ?></span>
      </button>
      
      <button class="help-nav-item" :class="{ 'active': activeTab === 'stock' }" @click="activeTab = 'stock'">
        <i data-lucide="package"></i>
        <span><?= $lang === 'en' ? 'Stock & Inventory' : 'Stok & Gudang' ?></span>
      </button>

      <button class="help-nav-item" :class="{ 'active': activeTab === 'po' }" @click="activeTab = 'po'">
        <i data-lucide="shopping-bag"></i>
        <span><?= $lang === 'en' ? 'Purchase Order (PO)' : 'Pembelian & PO' ?></span>
      </button>

      <button class="help-nav-item" :class="{ 'active': activeTab === 'kitchen' }" @click="activeTab = 'kitchen'">
        <i data-lucide="chef-hat"></i>
        <span><?= $lang === 'en' ? 'Kitchen Production' : 'Produksi Dapur' ?></span>
      </button>

      <button class="help-nav-item" :class="{ 'active': activeTab === 'distribution' }" @click="activeTab = 'distribution'">
        <i data-lucide="truck"></i>
        <span><?= $lang === 'en' ? 'Distribution Logs' : 'Log Distribusi' ?></span>
      </button>

      <button class="help-nav-item" :class="{ 'active': activeTab === 'whatsapp' }" @click="activeTab = 'whatsapp'">
        <i data-lucide="message-square"></i>
        <span>WhatsApp Gateway</span>
      </button>
    </nav>

    <!-- Main Content Panel -->
    <div class="help-content">
      
      <!-- ── TAB: OVERVIEW ── -->
      <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200">
        <h2 class="help-section-title">
          <i data-lucide="book-open"></i>
          <span><?= $lang === 'en' ? 'System Overview' : 'Ringkasan Sistem' ?></span>
        </h2>
        
        <?php if ($lang === 'en'): ?>
          <p class="help-paragraph">
            Welcome to the <strong>Dapur MBG SPPG Management System</strong>. This platform is designed to optimize the planning, purchasing, storage, production, and distribution of Nutritious Free Meals (Makan Bergizi Gratis).
          </p>
          <p class="help-paragraph">
            The application operates with Role-Based Access Control (RBAC) to ensure security and operational accountability. Please note your assigned role on the system:
          </p>
          <div class="flow-steps">
            <div class="flow-step">
              <div class="flow-step-num">A</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Admin</div>
                <div class="flow-step-desc">Full access to users, settings, reporting, and Purchase Order approvals.</div>
              </div>
            </div>
            <div class="flow-step">
              <div class="flow-step-num">G</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Warehouse Staff (Gudang)</div>
                <div class="flow-step-desc">Manages raw ingredient inventory, logs stock updates, and views critical stock limits.</div>
              </div>
            </div>
            <div class="flow-step">
              <div class="flow-step-num">P</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Purchasing Staff (Pembelian)</div>
                <div class="flow-step-desc">Creates suppliers and proposes Purchase Orders (POs) for ingredient replenishment.</div>
              </div>
            </div>
          </div>
        <?php else: ?>
          <p class="help-paragraph">
            Selamat datang di <strong>Sistem Manajemen Dapur MBG SPPG</strong>. Platform ini didesain untuk merampingkan seluruh rangkaian proses perencanaan, pengadaan bahan baku, penyimpanan stok, masak di dapur, hingga logistik pengiriman makanan sehat program Makan Bergizi Gratis.
          </p>
          <p class="help-paragraph">
            Aplikasi beroperasi berdasarkan hak akses peran (*Role-Based Access Control*) demi keamanan data dan transparansi operasional. Berikut adalah ringkasan pembagian peran:
          </p>
          <div class="flow-steps">
            <div class="flow-step">
              <div class="flow-step-num">A</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Admin</div>
                <div class="flow-step-desc">Akses penuh ke manajemen pengguna, jadwal siklus, laporan keuangan, dan persetujuan (approval) Purchase Order.</div>
              </div>
            </div>
            <div class="flow-step">
              <div class="flow-step-num">G</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Petugas Gudang (Gudang)</div>
                <div class="flow-step-desc">Mengontrol persediaan bahan baku, menginput mutasi stok masuk/keluar, dan memantau stok kritis.</div>
              </div>
            </div>
            <div class="flow-step">
              <div class="flow-step-num">P</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Petugas Pembelian (Pembelian)</div>
                <div class="flow-step-desc">Mengelola supplier, mengajukan Purchase Order baru, dan memperbarui data invoice tagihan.</div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- ── TAB: STOCK ── -->
      <div x-show="activeTab === 'stock'" x-transition:enter="transition ease-out duration-200" style="display:none;">
        <h2 class="help-section-title">
          <i data-lucide="package"></i>
          <span><?= $lang === 'en' ? 'Stock & Inventory Management' : 'Manajemen Stok & Gudang' ?></span>
        </h2>
        
        <?php if ($lang === 'en'): ?>
          <p class="help-paragraph">
            The warehouse module ensures all ingredients are recorded accurately. Here is how you manage your stocks:
          </p>
          <ol class="help-guide-list">
            <li><strong>Ingredient Registry:</strong> Access <em>Warehouse Stock</em> menu to register raw materials, including their units (kg, liters, boxes) and minimum safety stock.</li>
            <li><strong>Stock Movement:</strong> Click <em>Stock In/Out</em> to record adjustments. Select the ingredient, input the quantity, and write a clear transaction note.</li>
            <li><strong>Stock Card (Kartu Stok):</strong> Click the search icon next to any ingredient to view its audit history showing chronological ins, outs, and running balances.</li>
          </ol>
          <div class="help-alert">
            <strong>Critical Stock Alert:</strong> When an ingredient's current stock drops below its set <em>Minimum Stock</em>, the system automatically marks it in red and dispatches WhatsApp alerts to the Admin and Warehouse team.
          </div>
        <?php else: ?>
          <p class="help-paragraph">
            Modul stok gudang memastikan ketersediaan bahan makanan selalu terpantau. Berikut alur pengelolaan persediaan:
          </p>
          <ol class="help-guide-list">
            <li><strong>Mendaftarkan Bahan Baku:</strong> Melalui menu *Stok Gudang*, daftarkan nama bahan makanan baru beserta satuan (kg, liter, butir, pack) dan set batas stok minimumnya.</li>
            <li><strong>Mencatat Mutasi Stok:</strong> Masuk ke halaman *Tambah Transaksi Stok* untuk merekam stok masuk (pembelian baru/donasi) atau stok keluar (bahan rusak/terbuang diluar produksi).</li>
            <li><strong>Kartu Stok:</strong> Klik ikon kaca pembesar di samping baris bahan makanan untuk mencetak/melihat riwayat debit-kredit mutasi stok secara auditabel.</li>
          </ol>
          <div class="help-alert">
            <strong>Peringatan Stok Kritis:</strong> Jika stok suatu bahan baku berada di bawah *Stok Minimum*, sistem akan menampilkan indikasi merah di dasbor dan mengirimkan notifikasi WA otomatis untuk pengadaan ulang.
          </div>
        <?php endif; ?>
      </div>

      <!-- ── TAB: PO ── -->
      <div x-show="activeTab === 'po'" x-transition:enter="transition ease-out duration-200" style="display:none;">
        <h2 class="help-section-title">
          <i data-lucide="shopping-bag"></i>
          <span>Purchase Order (PO) Workflow</span>
        </h2>
        
        <?php if ($lang === 'en'): ?>
          <p class="help-paragraph">
            Purchase Orders ensure formal transactions with suppliers. The workflow follows these steps:
          </p>
          <div class="flow-steps">
            <div class="flow-step">
              <div class="flow-step-num">1</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Submission (Diajukan)</div>
                <div class="flow-step-desc">Purchasing staff inputs a PO request detailing requested items, quantities, pricing, and required delivery date.</div>
              </div>
            </div>
            <div class="flow-step">
              <div class="flow-step-num">2</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Admin Review</div>
                <div class="flow-step-desc">The Admin receives a system and WhatsApp notification to inspect the PO request details.</div>
              </div>
            </div>
            <div class="flow-step">
              <div class="flow-step-num">3</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Decision (Approved / Rejected)</div>
                <div class="flow-step-desc">Admin approves the PO (notifies the supplier) or rejects it with a reason (notifies purchasing team to modify).</div>
              </div>
            </div>
          </div>
        <?php else: ?>
          <p class="help-paragraph">
            Sistem pengadaan bahan baku berbasis Purchase Order (PO) dirancang untuk mencegah kecurangan (*fraud*) dan menjaga akurasi harga:
          </p>
          <div class="flow-steps">
            <div class="flow-step">
              <div class="flow-step-num">1</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Pengajuan (Staf Pembelian)</div>
                <div class="flow-step-desc">Petugas Pembelian menginput data PO baru, memilih supplier, mencatat detail bahan baku, harga satuan, dan batas waktu pengiriman.</div>
              </div>
            </div>
            <div class="flow-step">
              <div class="flow-step-num">2</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Verifikasi (Admin)</div>
                <div class="flow-step-desc">Admin menerima peringatan sistem & WA untuk meninjau nilai PO dan kelayakan supplier.</div>
              </div>
            </div>
            <div class="flow-step">
              <div class="flow-step-num">3</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Persetujuan / Penolakan</div>
                <div class="flow-step-desc">Admin menyetujui PO (mengirim PDF resmi) atau menolak pengajuan disertai alasan penolakan tertulis.</div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- ── TAB: KITCHEN ── -->
      <div x-show="activeTab === 'kitchen'" x-transition:enter="transition ease-out duration-200" style="display:none;">
        <h2 class="help-section-title">
          <i data-lucide="chef-hat"></i>
          <span><?= $lang === 'en' ? 'Kitchen & Recipe Production' : 'Produksi Dapur & Resep BOM' ?></span>
        </h2>
        
        <?php if ($lang === 'en'): ?>
          <p class="help-paragraph">
            The kitchen module translates schedule menus into bulk ingredients usage:
          </p>
          <ol class="help-guide-list">
            <li><strong>Recipe Definition (BOM):</strong> Set the default ingredients needed per portion of any menu item (e.g., Rice, Chicken Breast, Eggs).</li>
            <li><strong>Batch Production:</strong> Select a menu and enter the target portions. The system automatically computes required raw ingredients using the BOM.</li>
            <li><strong>Stock Deduction:</strong> When you complete a production batch, the system automatically deducts corresponding ingredients from warehouse stock.</li>
          </ol>
        <?php else: ?>
          <p class="help-paragraph">
            Modul produksi menerjemahkan rencana porsi menu menjadi pemakaian bahan baku nyata:
          </p>
          <ol class="help-guide-list">
            <li><strong>Definisi Resep (BOM):</strong> Tentukan resep per 1 porsi menu (misalnya: 80g beras, 100g dada ayam, 50g sayur) di halaman *Resep & BOM*.</li>
            <li><strong>Input Batch Produksi:</strong> Petugas dapur memasukkan menu yang akan dimasak dan jumlah porsi target. Sistem otomatis mengkalkulasi kebutuhan total bahan baku.</li>
            <li><strong>Pemotongan Stok Otomatis:</strong> Saat status batch produksi diubah menjadi *Selesai*, sistem langsung memotong saldo stok bahan baku terkait di gudang.</li>
          </ol>
        <?php endif; ?>
      </div>

      <!-- ── TAB: DISTRIBUTION ── -->
      <div x-show="activeTab === 'distribution'" x-transition:enter="transition ease-out duration-200" style="display:none;">
        <h2 class="help-section-title">
          <i data-lucide="truck"></i>
          <span><?= $lang === 'en' ? 'Distribution & Food Waste' : 'Distribusi & Food Waste' ?></span>
        </h2>
        
        <?php if ($lang === 'en'): ?>
          <p class="help-paragraph">
            Tracking shipping ensuring meals reach students fresh and on time:
          </p>
          <ol class="help-guide-list">
            <li><strong>Delivery Logging:</strong> Record school destination, shipped portion count, vehicle details, and departure time.</li>
            <li><strong>Receiving Confirmation:</strong> Update status once the school headmaster confirms arrival with a signature or photo.</li>
            <li><strong>Food Waste (Sisa Makanan):</strong> Record left-overs at the end of the day. Evaluating high-waste menu items helps customize future menus.</li>
          </ol>
        <?php else: ?>
          <p class="help-paragraph">
            Pencatatan logistik menjamin makanan bergizi tiba dalam kondisi prima di sekolah:
          </p>
          <ol class="help-guide-list">
            <li><strong>Input Pengiriman:</strong> Catat nama kurir/supir, pelat kendaraan, nomor batch, waktu keberangkatan, dan jumlah porsi boks yang dibawa.</li>
            <li><strong>Konfirmasi Diterima:</strong> Perbarui status pengiriman saat petugas sekolah mengonfirmasi penerimaan box makanan.</li>
            <li><strong>Food Waste (Sisa Makanan):</strong> Catat porsi yang tersisa/tidak termakan. Analisis data sampah ini penting untuk merevisi menu yang kurang disukai siswa.</li>
          </ol>
        <?php endif; ?>
      </div>

      <!-- ── TAB: WHATSAPP ── -->
      <div x-show="activeTab === 'whatsapp'" x-transition:enter="transition ease-out duration-200" style="display:none;">
        <h2 class="help-section-title">
          <i data-lucide="message-square"></i>
          <span>WhatsApp Gateway Setup</span>
        </h2>
        
        <?php if ($lang === 'en'): ?>
          <p class="help-paragraph">
            The application includes a built-in, 100% free Node.js WhatsApp gateway using <code>whatsapp-web.js</code>. Follow these instructions to run it locally:
          </p>
          
          <div class="flow-steps">
            <div class="flow-step">
              <div class="flow-step-num">1</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Install Dependencies</div>
                <div class="flow-step-desc">Open your terminal, navigate to the gateway folder, and run npm install:</div>
                <div class="code-block">
                  cd c:\xampp\htdocs\dapur-mbg\wa-gateway<br>
                  npm install
                </div>
              </div>
            </div>
            
            <div class="flow-step">
              <div class="flow-step-num">2</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Start the Gateway Server</div>
                <div class="flow-step-desc">Run the start script to boot the API and launch Puppeteer:</div>
                <div class="code-block">
                  npm start
                </div>
              </div>
            </div>

            <div class="flow-step">
              <div class="flow-step-num">3</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Link your WhatsApp Device</div>
                <div class="flow-step-desc">A QR Code will generate directly inside the terminal. Open WhatsApp on your phone -> Linked Devices -> Scan the QR Code.</div>
              </div>
            </div>
          </div>

          <div class="help-alert alert-info">
            <strong>Configuration Check:</strong> Ensure that <code>app/Config/WaGateway.php</code> has the <code>$activeProvider</code> variable set to <code>'self_hosted'</code> to forward live system notifications to the gateway.
          </div>
        <?php else: ?>
          <p class="help-paragraph">
            Sistem dilengkapi dengan server integrasi WhatsApp mandiri berbasis Node.js yang gratis tanpa biaya langganan API pihak ketiga. Ikuti petunjuk berikut untuk menjalankannya:
          </p>
          
          <div class="flow-steps">
            <div class="flow-step">
              <div class="flow-step-num">1</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Instalasi Dependensi</div>
                <div class="flow-step-desc">Buka terminal Anda, masuk ke folder wa-gateway, lalu instal modul pendukung:</div>
                <div class="code-block">
                  cd c:\xampp\htdocs\dapur-mbg\wa-gateway<br>
                  npm install
                </div>
              </div>
            </div>
            
            <div class="flow-step">
              <div class="flow-step-num">2</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Menjalankan Server Gateway</div>
                <div class="flow-step-desc">Jalankan server API lokal agar siap menerima perintah pengiriman dari PHP:</div>
                <div class="code-block">
                  npm start
                </div>
              </div>
            </div>

            <div class="flow-step">
              <div class="flow-step-num">3</div>
              <div class="flow-step-content">
                <div class="flow-step-title">Menghubungkan Perangkat (Scan QR)</div>
                <div class="flow-step-desc">QR Code akan muncul di terminal Anda. Buka WhatsApp HP Anda -> Perangkat Tertaut -> Tautkan Perangkat, lalu scan QR Code di layar.</div>
              </div>
            </div>
          </div>

          <div class="help-alert alert-info">
            <strong>Verifikasi Pengaturan:</strong> Pastikan berkas <code>app/Config/WaGateway.php</code> memiliki nilai variabel <code>$activeProvider</code> yang diatur ke <code>'self_hosted'</code> agar notifikasi stok kritis dan PO otomatis dialihkan ke gateway lokal.
          </div>
        <?php endif; ?>
      </div>

    </div><!-- /.help-content -->
  </div><!-- /.help-grid -->

</div>
<?= $this->endSection() ?>
