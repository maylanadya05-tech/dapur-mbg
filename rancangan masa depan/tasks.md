# Implementation Plan — Peningkatan Aplikasi Dapur MBG

> Setiap tugas bersifat inkremental dan dapat dieksekusi bertahap. Centang `[x]` saat selesai. Referensi `_Requirements:_` mengacu ke nomor kebutuhan di `requirements.md`.

---

## Fase 0 — Fondasi & Komponen UI Bersama

- [ ] 0.1 Buat komponen UI reusable
  - Tambah `app/Views/components/breadcrumb.php`, `confirm_modal.php`, `period_filter.php`, `connection_indicator.php`
  - Pastikan memakai token & utilitas design system yang ada
  - _Requirements: 12.1, 12.2, 5.1_

- [ ] 0.2 Buat komponen tabel data reusable `components/data_table.php`
  - Parametrik: kolom, aksi baris, bulk action, empty-state, sticky header
  - Dukung search/sort/pagination via query param
  - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6_

- [ ] 0.3 Terapkan loading state & modal konfirmasi global
  - Pasang `.btn-loading` pada semua form submit
  - Alpine store untuk modal konfirmasi (atribut `data-confirm`) pada aksi destruktif
  - _Requirements: 12.2, 12.3, 12.6_

---

## Fase 1 — Operasional Inti (Prioritas Tinggi)

### Stok Menipis (Keb. 1)

- [ ] 1.1 Migrasi: tambah `buffer_minimum` di `bahan_baku`; tambah `tipe`, `prioritas`, `ref_id` di `notifikasi`
  - _Requirements: 1.1, 1.3_
- [ ] 1.2 Implementasi `StockAlertService::evaluate()` + klasifikasi aman/menipis/kritis (anti-duplikasi notifikasi)
  - _Requirements: 1.1, 1.3_
- [ ] 1.3 Badge notifikasi belum dibaca (Alpine store) + penanda baris stok menipis/kritis di tabel
  - _Requirements: 1.2, 1.4_
- [ ] 1.4 Command `CheckStockAlerts` (cron) + `SendDailyDigest` (PHPMailer, toggle di Pengaturan)
  - _Requirements: 1.1, 1.5_

### Kedaluwarsa & FIFO (Keb. 2)

- [ ] 2.1 Migrasi tabel `stok_lot` (bahan_baku_id, qty, tanggal_masuk, tanggal_kadaluarsa, sumber_po_id) + model
  - _Requirements: 2.1_
- [ ] 2.2 Catat lot saat penerimaan barang dari PO
  - _Requirements: 2.1_
- [ ] 2.3 `ExpiryFifoService` (suggestConsumption FIFO + klasifikasi status kedaluwarsa)
  - _Requirements: 2.3, 2.4_
- [ ] 2.4 Peringatan kedaluwarsa (gabung ke command Keb. 1) + blokir pemakaian bahan kadaluarsa (override admin)
  - _Requirements: 2.2, 2.5_
- [ ] 2.5 Tampilkan kode warna status kedaluwarsa di UI stok
  - _Requirements: 2.4_

### Perencanaan Kebutuhan Bahan (Keb. 3)

- [ ] 3.1 `MaterialPlannerService`: hitung porsi dari sekolah aktif × BOM jadwal siklus
  - _Requirements: 3.1, 3.2_
- [ ] 3.2 Hitung selisih kebutuhan vs stok tersedia + rincian telusur
  - _Requirements: 3.3, 3.5_
- [ ] 3.3 Halaman "Perencanaan Produksi" + tombol "Buat Draf PO" dari kekurangan (transaksi DB)
  - _Requirements: 3.4_

### Distribusi Real-Time (Keb. 4)

- [ ] 4.1 Migrasi: kolom `penerima_nama`, `bukti_foto_path`, `qty_diterima`, `selisih_alasan` di `distribusi`; tabel `distribusi_status_log`
  - _Requirements: 4.2, 4.3, 4.5_
- [ ] 4.2 `DistributionService` + tulis status log pada setiap perubahan status
  - _Requirements: 4.1, 4.2_
- [ ] 4.3 Form konfirmasi penerimaan (nama, unggah foto/tanda tangan, qty diterima, selisih)
  - _Requirements: 4.3, 4.5_
- [ ] 4.4 Komponen timeline status di halaman detail distribusi
  - _Requirements: 4.4_

---

## Fase 2 — Pelaporan & Transparansi (Prioritas Menengah)

### Ekspor Laporan (Keb. 5)

- [ ] 5.1 `ReportExportService` (bungkus PhpSpreadsheet & dompdf) + filter periode preset
  - _Requirements: 5.1_
- [ ] 5.2 Ekspor Excel & PDF (kop SPPG) untuk stok, pembelian, produksi, distribusi, food waste
  - _Requirements: 5.2, 5.3, 5.4_
- [ ] 5.3 Empty state saat tidak ada data pada periode terpilih
  - _Requirements: 5.5_

### Dasbor Analitik (Keb. 6)

- [ ] 6.1 `DashboardAnalyticsService` + endpoint JSON analytics
  - _Requirements: 6.1_
- [ ] 6.2 Kartu ringkasan harian (porsi, sekolah terlayani, stok kritis, biaya)
  - _Requirements: 6.1_
- [ ] 6.3 Grafik tren (porsi terdistribusi, biaya pembelian, food waste vs produksi) + skeleton
  - _Requirements: 6.2, 6.3, 6.4, 6.5_

### Audit Log (Keb. 8)

- [ ] 8.1 Migrasi: pastikan `old_values`/`new_values` (JSON) di `audit_log`
  - _Requirements: 8.2_
- [ ] 8.2 `AuditableTrait` (afterInsert/Update/Delete) pada model kritis dengan diff
  - _Requirements: 8.1, 8.2_
- [ ] 8.3 Halaman audit: filter (user, modul, tanggal) + pagination server-side, read-only
  - _Requirements: 8.3, 8.4, 8.5_

---

## Fase 3 — Kegunaan Lapangan & UX (Prioritas Menengah)

### UX Tabel (Keb. 11)

- [ ] 11.1 Endpoint datatable server-side (q/sort/order/page/per_page) untuk tabel besar
  - _Requirements: 11.1, 11.2, 11.3_
- [ ] 11.2 Terapkan `data_table` + bulk action pada modul stok, sekolah, distribusi, pembelian
  - _Requirements: 11.4, 11.5, 11.6_

### Navigasi & Interaksi (Keb. 12)

- [ ] 12.1 Pasang breadcrumb pada halaman level dalam
  - _Requirements: 12.1_
- [ ] 12.2 Validasi inline form (Alpine + HTML5 + pesan server)
  - _Requirements: 12.4_
- [ ] 12.3 Skeleton loading pada halaman pengambilan data berat
  - _Requirements: 12.5_

### PWA & Offline (Keb. 10)

- [ ] 10.1 `manifest.json` + ikon + theme color (Obsidian Gold), daftarkan service worker
  - _Requirements: 10.1_
- [ ] 10.2 `sw.js`: cache app shell & aset (cache-first aset, network-first data)
  - _Requirements: 10.2_
- [ ] 10.3 Offline draft (IndexedDB) untuk pencatatan distribusi & food waste + background sync
  - _Requirements: 10.3, 10.4_
- [ ] 10.4 Indikator status koneksi online/offline di topbar
  - _Requirements: 10.5_

---

## Fase 4 — Keamanan & Keberlangsungan (Standar)

### Peran & Hak Akses (Keb. 7)

- [ ] 7.1 Migrasi tabel `role_permissions` + seed default matriks
  - _Requirements: 7.1_
- [ ] 7.2 `PermissionService::can()` + helper `can()` + `PermissionFilter`
  - _Requirements: 7.1, 7.2_
- [ ] 7.3 Sembunyikan menu/aksi di view sesuai izin; halaman admin kelola matriks
  - _Requirements: 7.3, 7.4_
- [ ] 7.4 Catat perubahan hak akses ke audit log
  - _Requirements: 7.5_

### Keamanan Akun (Keb. 9)

- [ ] 9.1 Migrasi tabel `password_resets` (token_hash, expires_at, used_at)
  - _Requirements: 9.1_
- [ ] 9.2 Alur lupa/reset password via PHPMailer (token berbatas waktu, invalidasi setelah pakai)
  - _Requirements: 9.1, 9.2, 9.3_
- [ ] 9.3 Rate limiting login & reset (CI4 Throttler); pastikan hashing aman
  - _Requirements: 9.4, 9.5_

### Cadangan DB (Keb. 13)

- [ ] 13.1 `BackupService` + command `BackupDatabase` (gzip, penamaan bertanggal, retensi)
  - _Requirements: 13.1, 13.2, 13.3_
- [ ] 13.2 Log hasil backup + halaman admin daftar & unduh cadangan
  - _Requirements: 13.4, 13.5_

---

## Fase 5 — Pengujian & Pemolesan

- [ ] 14.1 Unit test Service inti (MaterialPlanner, ExpiryFifo, StockAlert)
  - _Requirements: 1, 2, 3_
- [ ] 14.2 Feature test alur kritis (approve PO, update status distribusi, reset password)
  - _Requirements: 4, 9_
- [ ] 14.3 QA: Lighthouse PWA & aksesibilitas (axe), uji matriks peran, audit kontras WCAG
  - _Requirements: 6.5, 7, 10, 12_

---

## Catatan Eksekusi

- Mulai dari **Fase 0** (fondasi UI) lalu **Fase 1** (operasional inti) untuk dampak tertinggi.
- Semua migrasi bersifat **aditif**; jangan ubah kolom yang dipakai modul existing.
- Setiap fitur baru dipasang di belakang peran/izin yang sesuai.
- Pengujian (Fase 5) ditambahkan untuk logika berdampak tinggi; perubahan UI murni tidak wajib diuji otomatis.

---

## Ringkasan Prioritas

| Fase | Prioritas | Kebutuhan yang Diimplementasi |
|------|-----------|-------------------------------|
| 0 | Fondasi | Komponen UI bersama (breadcrumb, modal, tabel) |
| 1 | Tinggi | #1 Stok menipis, #2 FIFO/Kedaluwarsa, #3 Perencanaan bahan, #4 Distribusi |
| 2 | Menengah | #5 Ekspor laporan, #6 Dasbor analitik, #8 Audit log |
| 3 | Menengah | #10 PWA/Offline, #11 UX Tabel, #12 Navigasi |
| 4 | Standar | #7 Hak akses, #9 Keamanan akun, #13 Backup DB |
| 5 | QA | Unit test, feature test, audit aksesibilitas |
