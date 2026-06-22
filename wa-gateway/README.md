# WhatsApp Gateway Server (Dapur MBG)

Layanan WhatsApp Gateway sederhana dan **100% gratis** menggunakan Node.js dan pustaka `whatsapp-web.js` (berbasis Puppeteer/WhatsApp Web).

## Prasyarat
Pastikan komputer Anda sudah terinstal **Node.js** (versi 16 ke atas direkomendasikan). Jika belum, Anda bisa mengunduhnya dari [nodejs.org](https://nodejs.org/).

## Cara Instalasi & Menjalankan

1. Buka terminal (PowerShell, Command Prompt, atau Git Bash).
2. Pindah ke direktori project ini:
   ```bash
   cd c:\xampp\htdocs\dapur-mbg\wa-gateway
   ```
3. Instal semua dependensi yang dibutuhkan:
   ```bash
   npm install
   ```
4. Jalankan server:
   ```bash
   npm start
   ```
5. Saat pertama kali dijalankan, server akan menampilkan sebuah **QR Code** di terminal Anda.
6. Buka aplikasi **WhatsApp** di ponsel Anda -> klik menu ikon tiga titik di kanan atas (Android) / Pengaturan (iOS) -> pilih **Perangkat Tertaut (Linked Devices)** -> scan QR Code tersebut.
7. Setelah berhasil discan, terminal akan menampilkan pesan:
   `WHATSAPP GATEWAY BERHASIL DIHUBUNGKAN DAN SIAP DIGUNAKAN!`

Sesi masuk Anda akan disimpan secara lokal di folder `wa_session`, sehingga Anda tidak perlu memindai QR Code lagi setiap kali menjalankan ulang server ini.

## Hubungkan ke Aplikasi Dapur MBG
Setelah server berjalan pada port `8000`, pastikan konfigurasi di aplikasi Dapur MBG Anda sudah diarahkan ke server ini:

1. Buka file `app/Config/WaGateway.php` di editor Anda.
2. Atur pengaturannya sebagai berikut:
   ```php
   public string $activeProvider = 'self_hosted';
   public string $apiUrl         = 'http://localhost:8000/send-message';
   public string $apiKey         = ''; // Bisa dikosongkan karena berjalan lokal
   ```

Sekarang, sistem Dapur MBG Anda akan langsung mengirimkan notifikasi stok kritis, pengajuan PO, dan persetujuan/penolakan PO secara instan ke nomor WhatsApp yang bersangkutan secara gratis!
