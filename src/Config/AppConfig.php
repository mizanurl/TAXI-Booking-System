<?php

namespace App\Config;

class AppConfig
{
    public readonly string $env;
    public readonly bool $debug;
    public readonly string $corsAllowedOrigins;

    public readonly string $googleMapsApiKey;
    public readonly array $paypalCredentials;
    public readonly array $squareCredentials;

    public function __construct()
    {
        // General App Settings
        $this->env = $_ENV['APP_ENV'] ?? 'production';
        $this->debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
        $this->corsAllowedOrigins = $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*'; // Default to all for simplicity, restrict in production

        // API Keys and Credentials
        $this->googleMapsApiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';

        $this->paypalCredentials = [
            'client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
            'secret' => $_ENV['PAYPAL_SECRET'] ?? '',
            // Add other PayPal specific settings like mode (sandbox/live)
            'mode' => $_ENV['PAYPAL_MODE'] ?? 'sandbox'
        ];

        $this->squareCredentials = [
            'app_id' => $_ENV['SQUARE_APP_ID'] ?? '',
            'access_token' => $_ENV['SQUARE_ACCESS_TOKEN'] ?? '',
            // Add other Square specific settings like environment
            'environment' => $_ENV['SQUARE_ENVIRONMENT'] ?? 'sandbox'
        ];
    }
}