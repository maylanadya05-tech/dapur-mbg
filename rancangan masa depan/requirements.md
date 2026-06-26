# Requirements — Peningkatan Aplikasi Dapur MBG

## Pendahuluan

Dokumen ini mendefinisikan kebutuhan untuk peningkatan aplikasi **Dapur MBG** (Satuan Pelayanan Pemenuhan Gizi / program Makan Bergizi Gratis). Aplikasi sudah memiliki fondasi yang matang: manajemen stok gudang, resep/BOM, pembelian, produksi, distribusi ke sekolah, armada, food waste, feedback, invoice, jadwal siklus menu, notifikasi, audit log, supplier, dan manajemen pengguna berbasis peran.

Peningkatan ini berfokus pada **melengkapi dan menyempurnakan** fitur yang ada agar aplikasi lebih andal secara operasional, lebih informatif, lebih aman, dan dapat digunakan di lapangan (termasuk kondisi sinyal terbatas).

### Stack Saat Ini
- **Backend:** CodeIgniter 4 (PHP), MySQL
- **Frontend:** Alpine.js, Lucide Icons, Chart.js, CSS design system "Impeccable" (dark/light theme)
- **Autentikasi:** Filter `auth` + `role:` (peran: admin, gudang, pembelian, produksi, dll.)
- **Library:** dompdf (PDF), PHPMailer (email), PhpSpreadsheet (Excel)

### Peran Pengguna (Aktor)
- **Admin** — akses penuh, konfigurasi, manajemen pengguna
- **Kepala Dapur / Manajer** — perencanaan, persetujuan, laporan
- **Operator Gudang** — stok, penerimaan barang, kadaluarsa
- **Staf Pembelian** — purchase order, supplier
- **Staf Produksi** — batch produksi, status masak
- **Operator Distribusi / Driver** — pengiriman, konfirmasi terima
- **Pihak Sekolah** — konfirmasi penerimaan, feedback

---

## Catatan Tambahan untuk Pengembangan Masa Depan

- **Audit a11y otomatis** (mis. Lighthouse/axe) untuk validasi aksesibilitas menyeluruh
- **Sorting/search/pagination interaktif** di tabel yang datanya banyak
- **Keyboard shortcut** untuk aksi yang sering dipakai operator dapur

---

## Kebutuhan

### Kebutuhan 1 — Notifikasi & Peringatan Stok Menipis

**User Story:** Sebagai operator gudang, saya ingin mendapat peringatan otomatis ketika stok bahan baku mendekati atau di bawah batas minimum, agar dapur tidak kehabisan bahan saat produksi.

#### Kriteria Penerimaan
1. KETIKA stok bahan baku turun ke atau di bawah `stok_minimum`, SISTEM HARUS membuat notifikasi peringatan stok dan menampilkannya di pusat notifikasi.
2. KETIKA pengguna membuka aplikasi, SISTEM HARUS menampilkan indikator jumlah notifikasi yang belum dibaca pada ikon notifikasi.
3. SISTEM HARUS mengelompokkan tingkat urgensi stok menjadi: aman, menipis (mendekati minimum), dan kritis (di bawah minimum) dengan kode warna yang berbeda.
4. KETIKA daftar stok ditampilkan, SISTEM HARUS menandai baris bahan yang menipis/kritis secara visual.
5. JIKA pengguna memiliki peran admin atau gudang, MAKA SISTEM HARUS mengirim ringkasan stok kritis melalui email harian (opsional, dapat dinonaktifkan di pengaturan).

---

### Kebutuhan 2 — Pelacakan Kedaluwarsa Bahan & Metode FIFO

**User Story:** Sebagai operator gudang, saya ingin memantau tanggal kedaluwarsa bahan baku dan menggunakan bahan tertua lebih dulu, agar meminimalkan pemborosan dan menjaga keamanan pangan.

#### Kriteria Penerimaan
1. SISTEM HARUS menyimpan tanggal kedaluwarsa untuk setiap lot/batch penerimaan bahan baku.
2. KETIKA bahan mendekati tanggal kedaluwarsa dalam ambang yang ditentukan (mis. 7 hari), SISTEM HARUS membuat notifikasi peringatan.
3. KETIKA menampilkan stok, SISTEM HARUS mengurutkan dan menyarankan pemakaian bahan berdasarkan tanggal kedaluwarsa terdekat (FIFO).
4. SISTEM HARUS menampilkan status kedaluwarsa dengan kode warna: aman, mendekati kedaluwarsa, dan kedaluwarsa.
5. KETIKA bahan telah melewati tanggal kedaluwarsa, SISTEM HARUS menandainya dan mencegah pemakaiannya pada batch produksi baru kecuali ada override oleh admin.

---

### Kebutuhan 3 — Perhitungan Kebutuhan Bahan Otomatis

**User Story:** Sebagai kepala dapur, saya ingin sistem menghitung kebutuhan bahan baku secara otomatis dari jadwal siklus menu dikalikan jumlah penerima per sekolah, agar perencanaan pembelian dan produksi akurat.

#### Kriteria Penerimaan
1. KETIKA pengguna memilih tanggal/periode produksi, SISTEM HARUS menghitung total porsi yang dibutuhkan dari jumlah penerima seluruh sekolah aktif.
2. SISTEM HARUS mengalikan kebutuhan porsi dengan BOM (resep detail) untuk menghasilkan total kebutuhan tiap bahan baku.
3. KETIKA kebutuhan bahan dihitung, SISTEM HARUS membandingkannya dengan stok tersedia dan menampilkan selisih (kekurangan) per bahan.
4. KETIKA terdapat kekurangan bahan, SISTEM HARUS menyediakan opsi membuat draf purchase order dari daftar kekurangan tersebut.
5. SISTEM HARUS menampilkan rincian perhitungan agar dapat ditelusuri (porsi × kebutuhan per porsi = total).

---

### Kebutuhan 4 — Status Distribusi Real-Time & Konfirmasi Penerimaan Sekolah

**User Story:** Sebagai operator distribusi, saya ingin melacak status pengiriman dari disiapkan hingga diterima sekolah beserta bukti, agar distribusi terdokumentasi dan akuntabel.

#### Kriteria Penerimaan
1. SISTEM HARUS merepresentasikan status distribusi dalam alur: disiapkan → dimuat → dalam perjalanan → diterima.
2. KETIKA status distribusi berubah, SISTEM HARUS mencatat waktu (timestamp) dan pengguna yang mengubah pada sebuah timeline.
3. KETIKA pengiriman tiba, SISTEM HARUS memungkinkan pencatatan konfirmasi penerimaan berupa nama penerima dan unggahan foto/tanda tangan bukti.
4. SISTEM HARUS menampilkan timeline status pada halaman detail distribusi.
5. JIKA jumlah porsi diterima berbeda dari yang dikirim, MAKA SISTEM HARUS mencatat selisih beserta alasannya.

---

### Kebutuhan 5 — Ekspor Laporan (Excel & PDF)

**User Story:** Sebagai kepala dapur, saya ingin mengekspor laporan ke Excel dan PDF dengan filter periode, agar dapat dilaporkan ke pemangku kepentingan program.

#### Kriteria Penerimaan
1. SISTEM HARUS menyediakan filter rentang tanggal dengan preset cepat (hari ini, minggu ini, bulan ini, kustom) pada halaman laporan.
2. KETIKA pengguna menekan ekspor Excel, SISTEM HARUS menghasilkan berkas `.xlsx` berisi data sesuai filter aktif.
3. KETIKA pengguna menekan ekspor PDF, SISTEM HARUS menghasilkan berkas PDF dengan kop/identitas SPPG sesuai filter aktif.
4. SISTEM HARUS menyediakan ekspor untuk laporan stok, pembelian, produksi, distribusi, dan food waste.
5. KETIKA tidak ada data pada periode terpilih, SISTEM HARUS menampilkan pesan kondisi kosong yang informatif alih-alih berkas kosong.

---

### Kebutuhan 6 — Dasbor Analitik & Grafik Tren

**User Story:** Sebagai kepala dapur, saya ingin melihat ringkasan dan tren operasional dalam bentuk grafik, agar dapat mengambil keputusan berbasis data.

#### Kriteria Penerimaan
1. SISTEM HARUS menampilkan kartu ringkasan harian: total porsi hari ini, jumlah sekolah terlayani, jumlah bahan stok kritis, dan biaya pembelian periode berjalan.
2. SISTEM HARUS menampilkan grafik tren porsi terdistribusi per hari untuk periode terpilih.
3. SISTEM HARUS menampilkan grafik tren biaya pembelian per periode.
4. SISTEM HARUS menampilkan grafik food waste terhadap total produksi.
5. KETIKA data grafik sedang dimuat, SISTEM HARUS menampilkan skeleton loading dan menghormati preferensi `prefers-reduced-motion`.

---

### Kebutuhan 7 — Manajemen Peran & Hak Akses yang Lebih Halus

**User Story:** Sebagai admin, saya ingin mengatur hak akses per modul untuk setiap peran, agar pengguna hanya dapat mengakses fungsi sesuai tanggung jawabnya.

#### Kriteria Penerimaan
1. SISTEM HARUS mendefinisikan matriks peran terhadap modul (lihat, buat, ubah, hapus, setujui).
2. KETIKA pengguna mencoba mengakses fungsi tanpa hak, SISTEM HARUS menolak akses dan menampilkan pesan yang jelas.
3. SISTEM HARUS menyembunyikan menu/aksi pada antarmuka yang tidak diizinkan untuk peran pengguna.
4. KETIKA admin mengubah hak akses peran, SISTEM HARUS menerapkannya tanpa perlu mengubah kode.
5. SISTEM HARUS mencatat perubahan hak akses pada audit log.

---

### Kebutuhan 8 — Audit Log yang Komprehensif

**User Story:** Sebagai admin, saya ingin setiap perubahan data penting tercatat beserta pelaku dan waktunya, agar dana dan operasional program dapat dipertanggungjawabkan.

#### Kriteria Penerimaan
1. KETIKA data dibuat, diubah, atau dihapus pada modul kritis (stok, pembelian, produksi, distribusi, pengguna), SISTEM HARUS mencatat entri audit berisi pengguna, aksi, modul, dan waktu.
2. SISTEM HARUS menyimpan nilai sebelum dan sesudah perubahan untuk aksi ubah.
3. SISTEM HARUS menyediakan halaman audit log dengan filter berdasarkan pengguna, modul, dan rentang tanggal.
4. SISTEM HARUS mencegah penyuntingan atau penghapusan entri audit log oleh pengguna mana pun.
5. SISTEM HARUS menampilkan audit log dengan pagination server-side.

---

### Kebutuhan 9 — Keamanan Akun (Reset Password & Verifikasi Email)

**User Story:** Sebagai pengguna, saya ingin dapat mereset kata sandi melalui email, agar tetap dapat mengakses akun jika lupa kata sandi.

#### Kriteria Penerimaan
1. KETIKA pengguna meminta reset kata sandi, SISTEM HARUS mengirim tautan reset berbatas waktu ke email terdaftar melalui PHPMailer.
2. KETIKA tautan reset diklik dalam masa berlaku, SISTEM HARUS memungkinkan pengguna menetapkan kata sandi baru.
3. JIKA tautan reset kedaluwarsa atau telah dipakai, MAKA SISTEM HARUS menolak dan meminta permintaan ulang.
4. SISTEM HARUS menyimpan kata sandi dalam bentuk hash yang aman.
5. SISTEM HARUS membatasi jumlah percobaan login dan permintaan reset untuk mencegah penyalahgunaan (rate limiting).

---

### Kebutuhan 10 — PWA & Mode Offline

**User Story:** Sebagai operator lapangan, saya ingin aplikasi dapat dipasang di ponsel dan tetap bisa mencatat data saat sinyal terbatas, agar pekerjaan tidak terhambat koneksi.

#### Kriteria Penerimaan
1. SISTEM HARUS menyediakan manifest dan service worker sehingga aplikasi dapat dipasang (installable) di perangkat.
2. KETIKA perangkat offline, SISTEM HARUS tetap menampilkan halaman yang sudah di-cache (app shell, aset statis).
3. KETIKA pencatatan dilakukan saat offline, SISTEM HARUS menyimpannya secara lokal dan menyinkronkan ke server saat koneksi kembali.
4. KETIKA sinkronisasi berhasil/gagal, SISTEM HARUS memberi tahu pengguna melalui notifikasi/toast.
5. SISTEM HARUS menampilkan indikator status koneksi (online/offline) pada antarmuka.

---

### Kebutuhan 11 — Penyempurnaan UX Tabel Data

**User Story:** Sebagai pengguna, saya ingin tabel data dapat dicari, diurutkan, dan dibagi halaman secara konsisten, agar mudah menemukan informasi pada data yang banyak.

#### Kriteria Penerimaan
1. SISTEM HARUS menyediakan pencarian (search) pada tabel data utama.
2. SISTEM HARUS memungkinkan pengurutan (sort) berdasarkan kolom yang relevan.
3. SISTEM HARUS menerapkan pagination server-side ketika jumlah baris melebihi ambang tertentu.
4. SISTEM HARUS menyediakan aksi massal (bulk action) dengan pemilihan banyak baris untuk aksi yang relevan.
5. KETIKA tabel kosong, SISTEM HARUS menampilkan empty state informatif dengan ajakan tindakan.
6. SISTEM HARUS mempertahankan header tabel tetap terlihat (sticky header) saat menggulir daftar panjang.

---

### Kebutuhan 12 — Penyempurnaan Navigasi & Interaksi UI

**User Story:** Sebagai pengguna, saya ingin navigasi yang jelas dan konfirmasi pada aksi berisiko, agar tidak tersesat di menu dan tidak salah melakukan aksi destruktif.

#### Kriteria Penerimaan
1. SISTEM HARUS menampilkan breadcrumb pada halaman yang berada di level dalam.
2. KETIKA pengguna melakukan aksi destruktif (hapus, batal), SISTEM HARUS menampilkan modal konfirmasi yang konsisten sebelum mengeksekusi.
3. KETIKA pengguna mengirim form, SISTEM HARUS menampilkan state loading pada tombol dan mencegah pengiriman ganda.
4. SISTEM HARUS menampilkan validasi form secara inline sebelum pengiriman bila memungkinkan.
5. SISTEM HARUS menampilkan skeleton loading pada halaman yang mengambil data berat.
6. SISTEM HARUS menerapkan toast untuk umpan balik aksi (sukses/gagal) secara konsisten.

---

### Kebutuhan 13 — Cadangan Basis Data Otomatis

**User Story:** Sebagai admin, saya ingin basis data dicadangkan secara terjadwal, agar data operasional dan keuangan program tidak hilang.

#### Kriteria Penerimaan
1. SISTEM HARUS menyediakan perintah pencadangan basis data yang dapat dijalankan terjadwal (cron/CLI).
2. SISTEM HARUS menyimpan berkas cadangan dengan penamaan bertanggal.
3. SISTEM HARUS menyimpan sejumlah cadangan terakhir dan menghapus yang lebih lama dari kebijakan retensi.
4. KETIKA pencadangan berhasil/gagal, SISTEM HARUS mencatatnya pada log.
5. SISTEM HARUS memungkinkan admin mengunduh berkas cadangan dari antarmuka.

---

## Catatan Prioritas

| Prioritas | Kebutuhan | Alasan |
|-----------|-----------|--------|
| Tinggi | #1, #2, #3 | Inti operasional dapur — mencegah kehabisan/pemborosan bahan & akurasi perencanaan |
| Tinggi | #4 | Akuntabilitas distribusi (penting untuk program berbasis dana publik) |
| Menengah | #5, #6, #8 | Pelaporan & transparansi |
| Menengah | #10, #11, #12 | Kegunaan lapangan & pengalaman pengguna |
| Standar | #7, #9, #13 | Keamanan & keberlangsungan |
