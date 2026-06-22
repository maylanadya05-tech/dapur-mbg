<?php

namespace App\Libraries;

use Config\WaGateway as WaConfig;

class WaGateway
{
    /**
     * Send WhatsApp Message
     *
     * @param string $to Recipient phone number (e.g. 628123456789)
     * @param string $message Message content
     * @return array [success => bool, message => string, response => mixed]
     */
    public static function send(string $to, string $message): array
    {
        $config = config('WaGateway');
        $provider = $config->activeProvider;
        
        // Clean phone number (remove +, spaces, leading 0 to 62)
        $to = preg_replace('/[^0-9]/', '', $to);
        if (str_starts_with($to, '08')) {
            $to = '628' . substr($to, 2);
        }

        if ($provider === 'log') {
            return self::logMessage($to, $message);
        }

        if ($provider === 'fonnte') {
            return self::sendFonnte($to, $message, $config->apiUrl, $config->apiKey);
        }

        if ($provider === 'self_hosted') {
            return self::sendSelfHosted($to, $message, $config->apiUrl, $config->apiKey);
        }

        return [
            'success' => false,
            'message' => 'Provider not supported: ' . $provider
        ];
    }

    /**
     * Log WhatsApp notification (useful for testing & development)
     */
    private static function logMessage(string $to, string $message): array
    {
        $logPath = WRITEPATH . 'logs/wa_notifications.log';
        $timestamp = date('Y-m-d H:i:s');
        $logContent = "[{$timestamp}] TO: {$to} | MESSAGE: {$message}\n";

        if (!file_exists(dirname($logPath))) {
            mkdir(dirname($logPath), 0777, true);
        }

        file_put_contents($logPath, $logContent, FILE_APPEND);

        return [
            'success' => true,
            'message' => 'Message simulated and saved to log file: ' . $logPath,
        ];
    }

    /**
     * Send via Fonnte (Free tier available)
     */
    private static function sendFonnte(string $to, string $message, string $url, string $token): array
    {
        if (empty($url)) {
            $url = 'https://api.fonnte.com/send';
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $to,
                'message' => $message,
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $token
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $err,
            ];
        }

        $result = json_decode($response, true);
        $success = isset($result['status']) && $result['status'] == true;

        return [
            'success' => $success,
            'message' => $success ? 'Message sent via Fonnte.' : ($result['reason'] ?? 'Failed to send via Fonnte.'),
            'response' => $result,
        ];
    }

    /**
     * Send via a completely free self-hosted Node.js API (e.g. baileys / whatsapp-web.js based)
     */
    private static function sendSelfHosted(string $to, string $message, string $url, string $token): array
    {
        $curl = curl_init();

        $postData = json_encode([
            'number'  => $to,
            'message' => $message
        ]);

        $headers = [
            'Content-Type: application/json',
        ];

        if (!empty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $err,
            ];
        }

        $result = json_decode($response, true);
        
        // A typical custom Node.js api returns { success: true } or { status: "sent" }
        $success = (isset($result['success']) && $result['success'] == true) || 
                   (isset($result['status']) && ($result['status'] == 'sent' || $result['status'] == 'success'));

        return [
            'success' => $success,
            'message' => $success ? 'Message sent via Self-Hosted API.' : 'Failed to send via Self-Hosted API.',
            'response' => $result,
        ];
    }
}
