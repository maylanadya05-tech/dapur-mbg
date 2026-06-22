<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Surat Jalan Distribusi #<?= $distribusi['id'] ?> – Dapur MBG</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #1a1a1a; background: white; }

    .page { max-width: 800px; margin: 0 auto; padding: 30px; }

    /* Header */
    .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 3px solid #0d9488; }
    .header-brand h1 { font-size: 22px; color: #0d9488; font-weight: 800; }
    .header-brand p { font-size: 11px; color: #6b7280; margin-top: 3px; }
    .header-doc h2 { font-size: 18px; font-weight: 700; color: #1a1a1a; text-align: right; }
    .header-doc p { font-size: 11px; color: #6b7280; text-align: right; }

    /* Info Grid */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .info-box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; }
    .info-box h3 { font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 10px; }
    .info-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 12px; }
    .info-label { color: #6b7280; }
    .info-value { font-weight: 600; color: #1a1a1a; }

    /* Porsi highlight */
    .porsi-highlight { background: #f0fdf9; border: 2px solid #0d9488; border-radius: 10px; padding: 16px; text-align: center; margin-bottom: 20px; }
    .porsi-highlight .number { font-size: 48px; font-weight: 900; color: #0d9488; line-height: 1; }
    .porsi-highlight .label { font-size: 13px; color: #6b7280; margin-top: 5px; }

    /* QR Section */
    .qr-section { display: flex; gap: 24px; align-items: flex-start; margin-bottom: 20px; }
    .qr-box { border: 2px solid #0d9488; border-radius: 10px; padding: 16px; text-align: center; min-width: 200px; }
    .qr-box h3 { font-size: 12px; font-weight: 700; color: #0d9488; margin-bottom: 12px; text-transform: uppercase; }
    .qr-box canvas { display: block; margin: 0 auto; }
    .qr-box p { font-size: 10px; color: #6b7280; margin-top: 10px; line-height: 1.4; }

    .instructions { flex: 1; }
    .instructions h3 { font-size: 13px; font-weight: 700; color: #1a1a1a; margin-bottom: 10px; }
    .instructions ol { padding-left: 18px; }
    .instructions li { font-size: 12px; color: #374151; margin-bottom: 8px; line-height: 1.5; }
    .instructions li strong { color: #0d9488; }

    /* Signature */
    .signature-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 20px; }
    .signature-box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; }
    .signature-box h3 { font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 8px; }
    .signature-box p { font-size: 12px; font-weight: 600; color: #1a1a1a; margin-bottom: 2px; }
    .signature-box .sub { font-size: 11px; color: #9ca3af; }
    .sig-space { height: 60px; border-bottom: 1px dashed #d1d5db; margin-bottom: 6px; }

    /* Footer */
    .footer { margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
    .footer p { font-size: 10px; color: #9ca3af; }

    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: #f0fdf9; color: #0d9488; border: 1px solid #0d9488; }

    @media print {
      body { background: white; }
      .page { padding: 15px; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>
  <div class="page">
    <!-- Print Button (no print) -->
    <div class="no-print" style="margin-bottom:20px;display:flex;gap:10px;">
      <button onclick="window.print()" style="background:#0d9488;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:14px;display:flex;align-items:center;gap:8px;">
        🖨️ Cetak Surat Jalan
      </button>
      <a href="<?= base_url('/distribusi/show/' . $distribusi['id']) ?>" style="background:#f3f4f6;color:#374151;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:14px;display:flex;align-items:center;gap:8px;text-decoration:none;">
        ← Kembali
      </a>
    </div>

    <!-- Header -->
    <div class="header">
      <div class="header-brand">
        <h1>🍱 Dapur MBG</h1>
        <p>Sistem Pengelolaan Program Gizi – SPPG</p>
        <p>Makan Bergizi Gratis</p>
      </div>
      <div class="header-doc">
        <h2>SURAT JALAN</h2>
        <p>No. Distribusi: <strong>#<?= $distribusi['id'] ?></strong></p>
        <p>Batch: <strong><?= esc($distribusi['nomor_batch']) ?></strong></p>
        <p>Tanggal: <strong><?= date('d F Y', strtotime($distribusi['tanggal_distribusi'])) ?></strong></p>
        <span class="status-badge"><?= strtoupper($distribusi['status']) ?></span>
      </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
      <div class="info-box">
        <h3>📦 Informasi Makanan</h3>
        <div class="info-row">
          <span class="info-label">Menu:</span>
          <span class="info-value"><?= esc($distribusi['nama_menu']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">No. Batch:</span>
          <span class="info-value"><?= esc($distribusi['nomor_batch']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">Tgl. Produksi:</span>
          <span class="info-value"><?= date('d/m/Y', strtotime($distribusi['tanggal_produksi'])) ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">Pengirim:</span>
          <span class="info-value"><?= esc($distribusi['pengirim']) ?></span>
        </div>
        <?php if ($distribusi['no_polisi']): ?>
        <div class="info-row">
          <span class="info-label">Kendaraan:</span>
          <span class="info-value"><?= esc($distribusi['no_polisi']) ?> (<?= esc($distribusi['jenis_kendaraan']) ?>)</span>
        </div>
        <?php endif; ?>
      </div>
      <div class="info-box">
        <h3>🏫 Sekolah Penerima</h3>
        <div class="info-row">
          <span class="info-label">Nama:</span>
          <span class="info-value"><?= esc($distribusi['nama_sekolah']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">Jenjang:</span>
          <span class="info-value"><?= esc($distribusi['jenjang'] ?? '-') ?></span>
        </div>
        <div class="info-row" style="align-items:flex-start;">
          <span class="info-label">Alamat:</span>
          <span class="info-value" style="text-align:right;max-width:200px;"><?= esc($distribusi['alamat'] ?? '-') ?></span>
        </div>
        <?php if ($distribusi['phone']): ?>
        <div class="info-row">
          <span class="info-label">No. HP:</span>
          <span class="info-value"><?= esc($distribusi['phone']) ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Porsi Highlight -->
    <div class="porsi-highlight">
      <div class="number"><?= number_format($distribusi['jumlah_porsi']) ?></div>
      <div class="label">PORSI YANG DISERAHKAN</div>
    </div>

    <!-- QR & Instructions -->
    <div class="qr-section">
      <div class="qr-box">
        <h3>📱 Konfirmasi Penerimaan</h3>
        <canvas id="qrCanvas" width="160" height="160"></canvas>
        <p>Scan QR ini untuk mengkonfirmasi penerimaan makanan secara digital</p>
      </div>
      <div class="instructions">
        <h3>📋 Petunjuk Penerimaan</h3>
        <ol>
          <li>Terima makanan dan <strong>hitung jumlah porsi</strong> sesuai surat ini</li>
          <li>Periksa <strong>kondisi makanan</strong> (tidak tumpah, bau, atau rusak)</li>
          <li>Jika sesuai, <strong>scan QR Code</strong> di samping menggunakan kamera ponsel</li>
          <li>Atau konfirmasi melalui sistem di halaman distribusi</li>
          <li>Simpan surat ini sebagai <strong>bukti serah terima</strong></li>
        </ol>
        <?php if ($distribusi['catatan']): ?>
        <div style="margin-top:12px;padding:10px;background:#fffbeb;border:1px solid #fcd34d;border-radius:6px;">
          <strong style="font-size:12px;">📝 Catatan:</strong>
          <p style="font-size:12px;margin-top:4px;"><?= esc($distribusi['catatan']) ?></p>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Signatures -->
    <div class="signature-row">
      <div class="signature-box">
        <h3>Disiapkan oleh</h3>
        <div class="sig-space"></div>
        <p><?= esc($distribusi['pengirim']) ?></p>
        <div class="sub">Dapur MBG</div>
      </div>
      <div class="signature-box">
        <h3>Pengemudi / Kurir</h3>
        <div class="sig-space"></div>
        <p><?= esc($distribusi['pengemudi'] ?: '_______________') ?></p>
        <div class="sub"><?= esc($distribusi['no_polisi'] ?: 'No. Polisi') ?></div>
      </div>
      <div class="signature-box">
        <h3>Diterima oleh</h3>
        <div class="sig-space"></div>
        <?php if ($distribusi['penerima']): ?>
        <p><?= esc($distribusi['penerima']) ?></p>
        <div class="sub"><?= $distribusi['waktu_terima'] ? date('d/m/Y H:i', strtotime($distribusi['waktu_terima'])) : '' ?></div>
        <?php else: ?>
        <p>_______________</p>
        <div class="sub">Perwakilan Sekolah</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Footer -->
    <div class="footer">
      <p>Dokumen ini digenerate otomatis oleh Sistem SPPG Dapur MBG pada <?= date('d/m/Y H:i:s') ?></p>
      <p>Batch #<?= esc($distribusi['nomor_batch']) ?> | Distribusi #<?= $distribusi['id'] ?></p>
    </div>
  </div>

  <!-- QR Code JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var qrContainer = document.createElement('div');
      qrContainer.id = 'qrContainer';
      var canvas = document.getElementById('qrCanvas');
      canvas.parentNode.insertBefore(qrContainer, canvas);
      canvas.remove();

      new QRCode(qrContainer, {
        text: <?= json_encode($qrContent) ?>,
        width: 160,
        height: 160,
        colorDark: '#0d9488',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
      });
    });
  </script>
</body>
</html>
