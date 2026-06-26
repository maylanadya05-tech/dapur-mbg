# Design — Peningkatan Aplikasi Dapur MBG

## Ringkasan

Dokumen desain ini menjelaskan pendekatan teknis untuk mewujudkan 13 kebutuhan pada `requirements.md`. Desain dibuat agar **selaras dengan arsitektur yang sudah ada** (CodeIgniter 4, MVC, role-based filter, design system Impeccable) sehingga peningkatan dilakukan secara aditif dan minim risiko terhadap modul yang berjalan.

### Prinsip Desain
- **Aditif, bukan disruptif** — menambah kolom/tabel/service baru tanpa membongkar skema inti.
- **Konsisten dengan design system** — gunakan token, komponen, dan utilitas yang sudah ada di `app.css` (toast, skeleton, empty-state, focus-visible, reduced-motion).
- **Service layer untuk logika bisnis** — perhitungan kompleks (kebutuhan bahan, FIFO) ditempatkan di kelas Service agar dapat diuji dan dipakai ulang.
- **Progressive enhancement** — fitur offline/PWA tidak boleh merusak pengalaman pengguna online.

---

## Arsitektur

### Diagram Lapisan

```
┌─────────────────────────────────────────────────────────────┐
│  Presentation: Views (PHP) + Alpine.js + Chart.js + app.css   │
│  + Service Worker / Manifest (PWA)                            │
├─────────────────────────────────────────────────────────────┤
│  HTTP: Controllers + Filters (auth, role, permission)         │
├─────────────────────────────────────────────────────────────┤
│  Domain: Services (StockAlert, ExpiryFifo, MaterialPlanner,   │
│  ReportExport, BackupService, NotificationService)            │
├─────────────────────────────────────────────────────────────┤
│  Data: Models (Eloquent-like CI4 Models) + Entities           │
├─────────────────────────────────────────────────────────────┤
│  Database: MySQL (migrasi aditif)                             │
└─────────────────────────────────────────────────────────────┘
```

### Struktur Direktori Baru (Usulan)

```
app/
├── Services/
│   ├── StockAlertService.php        # Keb. 1
│   ├── ExpiryFifoService.php        # Keb. 2
│   ├── MaterialPlannerService.php   # Keb. 3
│   ├── DistributionService.php      # Keb. 4
│   ├── ReportExportService.php      # Keb. 5
│   ├── DashboardAnalyticsService.php# Keb. 6
│   ├── PermissionService.php        # Keb. 7
│   └── BackupService.php            # Keb. 13
├── Filters/
│   └── PermissionFilter.php         # Keb. 7 (selain role filter)
├── Commands/
│   ├── CheckStockAlerts.php         # Keb. 1, 2 (cron)
│   ├── SendDailyDigest.php          # Keb. 1
│   └── BackupDatabase.php           # Keb. 13 (cron)
├── Libraries/
│   └── ExportXlsx.php / ExportPdf.php
public/
├── manifest.json                    # Keb. 10
├── sw.js                            # Service worker (Keb. 10)
└── assets/js/offline-sync.js        # Keb. 10
```

---

## Desain per Kebutuhan

### Keb. 1 — Notifikasi & Peringatan Stok Menipis

**Komponen:** `StockAlertService`, `NotifikasiModel` (sudah ada), `CheckStockAlerts` command.

- `StockAlertService::evaluate()` memeriksa `stok_gudang` terhadap `bahan_baku.stok_minimum`.
- Klasifikasi: `aman` (> minimum + buffer), `menipis` (antara minimum dan minimum + buffer), `kritis` (≤ minimum).
- Saat status berubah ke menipis/kritis → buat entri `notifikasi` (hindari duplikasi: cek notifikasi serupa belum dibaca).
- UI: badge jumlah belum dibaca pada ikon notifikasi (Alpine store), baris tabel stok diberi kelas `.row-warning` / `.row-danger`.
- Email digest harian via `SendDailyDigest` command + PHPMailer (toggle di `pengaturan`).

**Perubahan data:** tambah kolom `tipe` & `prioritas` pada tabel `notifikasi` bila belum ada; tambah `stok_minimum` & `buffer_minimum` pada `bahan_baku` bila belum ada.

---

### Keb. 2 — Kedaluwarsa & FIFO

**Komponen:** `ExpiryFifoService`.

- Pelacakan per lot: tabel baru `stok_lot` (id, bahan_baku_id, qty, tanggal_masuk, tanggal_kadaluarsa, sumber_po_id).
- `ExpiryFifoService::suggestConsumption(bahanId, qty)` mengembalikan urutan lot berdasarkan `tanggal_kadaluarsa` ASC.
- Status kedaluwarsa: `aman`, `mendekati` (≤ ambang hari, default 7), `kadaluarsa` (< hari ini).
- Pemakaian bahan kadaluarsa pada batch produksi diblokir kecuali flag `override_admin`.
- Notifikasi otomatis untuk lot mendekati kedaluwarsa (digabung dengan command Keb. 1).

---

### Keb. 3 — Perhitungan Kebutuhan Bahan Otomatis

**Komponen:** `MaterialPlannerService`.

- Input: periode/tanggal produksi.
- Langkah:
  1. Ambil menu dari `jadwal_siklus` untuk tanggal tsb.
  2. Hitung total porsi = Σ `sekolah.jumlah_penerima` (sekolah aktif).
  3. Untuk tiap menu → ambil `resep_detail` (BOM) → kebutuhan_bahan = porsi × qty_per_porsi.
  4. Agregasi kebutuhan per `bahan_baku`.
  5. Bandingkan dengan stok tersedia → hitung `kekurangan`.
- Output: tabel rincian (bahan, kebutuhan, stok, selisih) + tombol "Buat Draf PO" yang memetakan kekurangan ke `purchase_order` + `po_detail`.
- UI: halaman "Perencanaan Produksi" baru di bawah modul Produksi/Jadwal.

**Formula:**
```
porsi_dibutuhkan = Σ(jumlah_penerima sekolah aktif)
kebutuhan[bahan] = Σ_menu( porsi_dibutuhkan × resep_detail.qty_per_porsi )
kekurangan[bahan] = max(0, kebutuhan[bahan] − stok_tersedia[bahan])
```

---

### Keb. 4 — Status Distribusi & Konfirmasi Penerimaan

**Komponen:** `DistributionService`, `DistribusiModel` (sudah ada), tabel baru `distribusi_status_log`.

- Status enum: `disiapkan`, `dimuat`, `dalam_perjalanan`, `diterima` (+ `bermasalah`).
- Setiap `updateStatus` menulis entri `distribusi_status_log` (distribusi_id, status, user_id, catatan, created_at).
- Konfirmasi penerimaan: tambah kolom pada `distribusi` → `penerima_nama`, `bukti_foto_path`, `qty_diterima`, `selisih_alasan`.
- UI: komponen timeline vertikal (CSS, reuse pola card), form unggah foto/tanda tangan (canvas signature opsional).

---

### Keb. 5 — Ekspor Laporan

**Komponen:** `ReportExportService` (membungkus PhpSpreadsheet & dompdf).

- Filter periode dengan preset (Alpine) → query param `start`, `end`, `preset`.
- `exportXlsx(reportType, filters)` & `exportPdf(reportType, filters)`.
- Template PDF dengan kop SPPG (partial view khusus cetak).
- Jenis laporan: stok, pembelian, produksi, distribusi, food waste.
- Empty state bila tidak ada data.

---

### Keb. 6 — Dasbor Analitik

**Komponen:** `DashboardAnalyticsService`, endpoint JSON untuk Chart.js.

- Endpoint: `GET /dashboard/analytics?metric=...&start=...&end=...` → JSON.
- Kartu ringkasan: porsi hari ini, sekolah terlayani, stok kritis, biaya periode.
- Grafik: tren porsi terdistribusi (line), biaya pembelian (bar), food waste vs produksi (doughnut/line).
- Skeleton loading saat fetch; animasi hormati `prefers-reduced-motion`.

---

### Keb. 7 — Peran & Hak Akses Halus

**Komponen:** `PermissionService`, `PermissionFilter`, tabel `role_permissions`.

- Matriks `role_permissions` (role, module, action[view|create|update|delete|approve], allowed bool).
- `PermissionService::can(user, module, action)` dipakai di controller & view (helper `can()`).
- View menyembunyikan menu/aksi via `can()`.
- Halaman admin untuk mengelola matriks (checkbox grid).
- Perubahan dicatat ke audit log.

> **Catatan:** tetap mempertahankan `role:` filter yang ada sebagai lapisan pertama; PermissionFilter sebagai lapisan granular.

---

### Keb. 8 — Audit Log Komprehensif

**Komponen:** `AuditLogModel` (sudah ada), trait/observer `Auditable`.

- Trait `AuditableTrait` pada model kritis: hook `afterInsert/afterUpdate/afterDelete` menulis audit dengan diff (`old_values`, `new_values` JSON).
- Halaman audit dengan filter (user, modul, tanggal) + pagination server-side.
- Entri audit read-only (tanpa route edit/delete).

---

### Keb. 9 — Keamanan Akun

**Komponen:** `Auth` controller (sudah ada) + tabel `password_resets`.

- Alur reset: minta → token (hash, expiry 60 menit) → email PHPMailer → form set password → invalidasi token.
- Rate limiting login & reset via CI4 Throttler.
- Password hashing `password_hash()` (bcrypt/argon2).

---

### Keb. 10 — PWA & Offline

**Komponen:** `manifest.json`, `sw.js`, `offline-sync.js`.

- Manifest: nama, ikon, theme color (selaras Obsidian Gold), display `standalone`.
- Service worker: cache app shell + aset statis (cache-first untuk aset, network-first untuk data).
- Offline draft: IndexedDB menyimpan entri form yang gagal kirim → background sync saat online → toast hasil.
- Indikator status koneksi pada topbar (Alpine listener `online`/`offline`).

> **Catatan:** cakupan offline awal difokuskan pada **pencatatan distribusi & food waste lapangan** (paling butuh), bukan seluruh modul.

---

### Keb. 11 — UX Tabel Data

**Komponen:** komponen tabel reusable + endpoint datatable.

- Server-side: query param `q` (search), `sort`, `order`, `page`, `per_page`.
- Komponen `components/data_table.php` parametrik (kolom, aksi, bulk).
- Bulk action: checkbox header + Alpine state, kirim array id ke endpoint aksi massal.
- Sticky header (CSS `position: sticky`), empty-state reuse.

---

### Keb. 12 — Navigasi & Interaksi UI

**Komponen:** komponen `breadcrumb.php`, `confirm_modal.php` (reuse modal yang ada), enhancement Alpine.

- Breadcrumb dari konfigurasi rute/judul halaman.
- Modal konfirmasi global (Alpine store) untuk aksi destruktif (data-confirm attribute).
- Tombol loading state (sudah ada `.btn-loading`) diterapkan pada semua submit.
- Validasi inline (Alpine + atribut HTML5 + pesan server).

---

### Keb. 13 — Cadangan Basis Data

**Komponen:** `BackupService`, `BackupDatabase` command.

- `BackupDatabase` (CLI/cron) → `mysqldump` atau ekspor via CI4 → simpan `writable/backups/backup-YYYYMMDD-HHMMSS.sql.gz`.
- Retensi: simpan N terakhir (default 14), hapus sisanya.
- Log hasil ke file & audit.
- Halaman admin: daftar + unduh cadangan.

---

## Model Data (Perubahan Skema)

| Tabel | Perubahan | Kebutuhan |
|-------|-----------|-----------|
| `bahan_baku` | pastikan `stok_minimum`, tambah `buffer_minimum` | #1 |
| `notifikasi` | tambah `tipe`, `prioritas`, `ref_id` | #1, #2 |
| `stok_lot` (baru) | id, bahan_baku_id, qty, tanggal_masuk, tanggal_kadaluarsa, sumber_po_id | #2 |
| `distribusi` | tambah `penerima_nama`, `bukti_foto_path`, `qty_diterima`, `selisih_alasan` | #4 |
| `distribusi_status_log` (baru) | id, distribusi_id, status, user_id, catatan, created_at | #4 |
| `role_permissions` (baru) | id, role, module, action, allowed | #7 |
| `audit_log` | pastikan `old_values`, `new_values` (JSON) | #8 |
| `password_resets` (baru) | id, user_id, token_hash, expires_at, used_at | #9 |

> Seluruh perubahan diterapkan via migrasi CI4 baru (aditif). Tidak mengubah kolom existing yang dipakai modul lain.

---

## Strategi UI/Komponen

- **Reuse design system Impeccable**: token warna, `.toast`, `.skeleton`, `.empty-state`, `.btn-loading`, `:focus-visible`, `prefers-reduced-motion`.
- **Komponen baru** (PHP partial + Alpine): `breadcrumb`, `confirm_modal`, `data_table`, `timeline`, `connection_indicator`, `period_filter`.
- **Aksesibilitas**: pertahankan kontras WCAG AA (gunakan `--accent-text` untuk teks aksen), aria-label pada aksi ikon.
- **Audit a11y**: gunakan Lighthouse/axe untuk validasi aksesibilitas menyeluruh secara otomatis (roadmap masa depan).

---

## Penanganan Error

- Validasi server CI4 + tampilan pesan inline.
- Operasi berisiko dalam transaksi DB (buat PO dari kekurangan, update status + log).
- Kegagalan email/ekspor/backup ditangkap dan dilaporkan via toast/log, tidak menggagalkan alur utama.
- Offline: kegagalan sync di-retry, beri tahu pengguna.

---

## Strategi Pengujian

- **Unit test** untuk Service (MaterialPlanner, ExpiryFifo, StockAlert) — logika perhitungan deterministik.
- **Feature test** CI4 untuk alur kritis (approve PO, update status distribusi, reset password).
- **Manual/QA**: PWA install & offline (Lighthouse), audit aksesibilitas (axe/Lighthouse), uji peran (matriks akses).
- Pengujian ditambahkan hanya untuk logika baru yang berdampak tinggi; tidak wajib untuk perubahan UI murni.
