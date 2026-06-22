<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class WaGateway extends BaseConfig
{
    /**
     * Active WhatsApp Gateway Provider
     * Supported: 'log' (for testing), 'fonnte', 'self_hosted'
     */
    public string $activeProvider = 'self_hosted';

    /**
     * API URL for the gateway
     * For 'fonnte': 'https://api.fonnte.com/send'
     * For 'self_hosted': 'http://localhost:8000/send-message'
     */
    public string $apiUrl = 'http://localhost:8000/send-message';

    /**
     * API Token / Auth Key
     */
    public string $apiKey = '';

    /**
     * Default recipient phone number if no user has a phone number
     * Format: International code without '+' (e.g., '628123456789')
     */
    public string $fallbackRecipient = '628123456789';
}
