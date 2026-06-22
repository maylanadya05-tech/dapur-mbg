<?php

namespace App\Libraries;

use Config\WaGateway;

/**
 * WhatsAppService — Kirim pesan WhatsApp via WA Gateway
 *
 * Mendukung dua provider:
 * - 'self_hosted': Node.js WA Gateway di http://localhost:8000
 * - 'fonnte': API Fonnte
 * - 'log': Hanya catat ke log file (untuk testing)
 */
class WhatsAppService
{
    private WaGateway $config;

    public function __construct()
    {
        $this->config = config('WaGateway');
    }

    /**
     * Kirim pesan ke satu nomor
     */
    public function send(string $phone, string $message): bool
    {
        if (empty($phone)) return false;

        try {
            switch ($this->config->activeProvider) {
                case 'self_hosted':
                    return $this->sendViaSelfHosted($phone, $message);
                case 'fonnte':
                    return $this->sendViaFonnte($phone, $message);
                case 'log':
                default:
                    log_message('info', "[WA-LOG] To: {$phone} | Msg: {$message}");
                    return true;
            }
        } catch (\Throwable $e) {
            log_message('error', "[WhatsAppService] Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim ke beberapa nomor sekaligus
     */
    public function sendBulk(array $phones, string $message): array
    {
        $results = [];
        foreach ($phones as $phone) {
            $results[$phone] = $this->send($phone, $message);
        }
        return $results;
    }

    // ─── Notifikasi Stok Kritis ────────────────────────────────────
    public function sendStokKritis(array $items, string $recipientPhone): bool
    {
        if (empty($items) || empty($recipientPhone)) return false;

        $list = '';
        foreach (array_slice($items, 0, 5) as $item) {
            $list .= "• {$item['nama']}: {$item['stok_saat_ini']} {$item['satuan']} (min: {$item['stok_minimum']})\n";
        }
        $total = count($items);
        $extra = $total > 5 ? "\n...dan " . ($total - 5) . " bahan lainnya." : '';

        $message = "🚨 *PERINGATAN STOK KRITIS - Dapur MBG*\n\n"
            . "Terdapat *{$total} bahan baku* dengan stok di bawah minimum:\n\n"
            . $list . $extra . "\n\n"
            . "Segera buat Purchase Order! ⚡\n"
            . "_" . date('d/m/Y H:i') . "_";

        return $this->send($recipientPhone, $message);
    }

    // ─── Notifikasi PO Disetujui ──────────────────────────────────
    public function sendPoApproved(array $po, string $recipientPhone): bool
    {
        if (empty($recipientPhone)) return false;
        $nilai = 'Rp ' . number_format((float)($po['total_nilai'] ?? 0), 0, ',', '.');
        $message = "✅ *PO DISETUJUI - Dapur MBG*\n\n"
            . "PO *{$po['nomor_po']}* telah disetujui.\n"
            . "Supplier: {$po['supplier_name']}\n"
            . "Nilai: {$nilai}\n\n"
            . "Mohon siapkan penerimaan barang. 📦\n"
            . "_" . date('d/m/Y H:i') . "_";
        return $this->send($recipientPhone, $message);
    }

    // ─── Notifikasi PO Ditolak ────────────────────────────────────
    public function sendPoRejected(array $po, string $recipientPhone): bool
    {
        if (empty($recipientPhone)) return false;
        $alasan = $po['alasan_tolak'] ?: 'Tidak ada catatan';
        $message = "❌ *PO DITOLAK - Dapur MBG*\n\n"
            . "PO *{$po['nomor_po']}* telah ditolak.\n"
            . "Alasan: {$alasan}\n\n"
            . "Silakan revisi dan ajukan ulang.\n"
            . "_" . date('d/m/Y H:i') . "_";
        return $this->send($recipientPhone, $message);
    }

    // ─── Notifikasi Distribusi Dikirim ───────────────────────────
    public function sendDistribusiDikirim(array $distribusi, string $recipientPhone): bool
    {
        if (empty($recipientPhone)) return false;
        $message = "🚚 *DISTRIBUSI DIKIRIM - Dapur MBG*\n\n"
            . "Makanan untuk *{$distribusi['nama_sekolah']}* sedang dalam perjalanan!\n"
            . "Batch: {$distribusi['nomor_batch']}\n"
            . "Porsi: " . number_format((int)($distribusi['jumlah_porsi'] ?? 0)) . " porsi\n"
            . "Pengirim: {$distribusi['pengirim']}\n\n"
            . "Mohon bersiap menerima. 🍱\n"
            . "_" . date('d/m/Y H:i') . "_";
        return $this->send($recipientPhone, $message);
    }

    // ─── Notifikasi Batch Selesai ─────────────────────────────────
    public function sendBatchSelesai(array $batch, string $recipientPhone): bool
    {
        if (empty($recipientPhone)) return false;
        $message = "🍳 *BATCH PRODUKSI SELESAI - Dapur MBG*\n\n"
            . "Batch *{$batch['nomor_batch']}* telah selesai diproduksi!\n"
            . "Menu: {$batch['nama_menu']}\n"
            . "Porsi: " . number_format((int)($batch['porsi_selesai'] ?? 0)) . " porsi\n\n"
            . "Siap untuk distribusi! 🚚\n"
            . "_" . date('d/m/Y H:i') . "_";
        return $this->send($recipientPhone, $message);
    }

    // ─── Internal: Self-hosted WA Gateway ────────────────────────
    private function sendViaSelfHosted(string $phone, string $message): bool
    {
        $curl = curl_init($this->config->apiUrl);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_POSTFIELDS     => json_encode(['number' => $phone, 'message' => $message]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200) {
            $body = json_decode($response, true);
            return !empty($body['success']);
        }

        log_message('error', "[WA-SelfHosted] HTTP {$httpCode}: {$response}");
        return false;
    }

    // ─── Internal: Fonnte API ─────────────────────────────────────
    private function sendViaFonnte(string $phone, string $message): bool
    {
        $curl = curl_init($this->config->apiUrl);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_POSTFIELDS     => ['target' => $phone, 'message' => $message],
            CURLOPT_HTTPHEADER     => ['Authorization: ' . $this->config->apiKey],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $httpCode === 200;
    }
}
